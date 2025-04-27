<?php
session_start();
require_once("../includes/db.php");

if (
    !isset($_SESSION["user_id"]) ||
    !isset($_SESSION["is_admin"]) ||
    $_SESSION["is_admin"] != 1 ||
    isset($_SESSION["guest"])
) {
    header("Location: books.php");
    exit;
}

$edit_mode = false;
$book_data = [
    'id' => '',
    'title' => '',
    'author' => '',
    'category' => '',
    'price' => '',
    'description' => '',
    'image' => ''
];

// تحميل بيانات الكتاب للتعديل
if (isset($_GET["edit"])) {
    $edit_mode = true;
    $id = $_GET["edit"];
    $result = $conn->query("SELECT * FROM books WHERE id = $id");
    $book_data = $result->fetch_assoc();
}

// تحديث الكتاب
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update_book"])) {
    $id = $_POST["book_id"];
    $title = $_POST["title"];
    $author = $_POST["author"];
    $category = $_POST["category"];
    $price = $_POST["price"];
    $description = $_POST["description"];
    $image_path = $_POST["existing_image"];

    if (isset($_FILES["image_file"]) && $_FILES["image_file"]["error"] == 0) {
        $image_name = basename($_FILES["image_file"]["name"]);
        $target_path = "../assets/images/" . $image_name;
        move_uploaded_file($_FILES["image_file"]["tmp_name"], $target_path);
        $image_path = $target_path;
    }

    $stmt = $conn->prepare("UPDATE books SET title=?, author=?, category=?, price=?, description=?, image=? WHERE id=?");
    $stmt->bind_param("sssissi", $title, $author, $category, $price, $description, $image_path, $id);
    $stmt->execute();

    header("Location: admin.php");
    exit;
}

// حذف كتاب
if (isset($_GET["delete"])) {
    $id = $_GET["delete"];
    $conn->query("DELETE FROM books WHERE id = $id");
    header("Location: admin.php"); 
    exit;
}

// إضافة كتاب
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_book"])) {
    $title = $_POST["title"];
    $author = $_POST["author"];
    $category = $_POST["category"];
    $price = $_POST["price"];
    $description = $_POST["description"];
    $image_path = "";

    if (isset($_FILES["image_file"]) && $_FILES["image_file"]["error"] == 0) {
        $image_name = basename($_FILES["image_file"]["name"]);
        $target_path = "../assets/images/" . $image_name; 
        move_uploaded_file($_FILES["image_file"]["tmp_name"], $target_path);
        $image_path = $target_path;
    }

    $stmt = $conn->prepare("INSERT INTO books (title, author, category, price, description, image) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssiss", $title, $author, $category, $price, $description, $image_path);
    $stmt->execute();

    header("Location: admin.php");
    exit;
}

$books = $conn->query("SELECT * FROM books");
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>لوحة تحكم المشرف</title>
    <link rel="stylesheet" href="../assets/css/style.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body>
<div class="container">
    <h3><i class="fas fa-plus-circle"></i> إضافة كتاب جديد</h3>

    <form method="post" enctype="multipart/form-data">
        <?php if ($edit_mode): ?>
            <input type="hidden" name="book_id" value="<?= $book_data['id'] ?>">
            <input type="hidden" name="existing_image" value="<?= $book_data['image'] ?>">
        <?php endif; ?>
        <input type="text" name="title" placeholder="عنوان الكتاب" required value="<?= $book_data['title'] ?>">
        <input type="text" name="author" placeholder="المؤلف" value="<?= $book_data['author'] ?>">
        <input type="text" name="category" placeholder="التصنيف" value="<?= $book_data['category'] ?>">
        <input type="number" step="0.01" name="price" placeholder="السعر" required value="<?= $book_data['price'] ?>">
        <textarea name="description" placeholder="الوصف"><?= $book_data['description'] ?></textarea>
        <input type="file" name="image_file">
        <?php if ($edit_mode && $book_data['image']): ?>
            <p>الصورة الحالية: <img src="<?= $book_data['image'] ?>" style="max-width:60px;"></p>
        <?php endif; ?>
        <input type="submit" name="<?= $edit_mode ? 'update_book' : 'add_book' ?>" value="<?= $edit_mode ? 'تحديث الكتاب' : 'إضافة الكتاب' ?>">
    </form>

    <h3><i class="fas fa-book-open"></i> الكتب الموجودة</h3>
    <?php while($book = $books->fetch_assoc()): ?>
        <div class="card">
            <strong><?= $book['title'] ?></strong> (<?= $book['author'] ?>) - <?= $book['price'] ?> ريال
            <?php if ($book['image']): ?>
                <img src="<?= $book['image'] ?>" style="max-width:60px;">
            <?php endif; ?>
            <br>
            <a href="?edit=<?= $book['id'] ?>"><i class="fas fa-edit"></i> تعديل</a> |
            <a href="?delete=<?= $book['id'] ?>" onclick="return confirm('هل أنت متأكد من الحذف؟')"><i class="fas fa-trash-alt"></i> حذف</a>
        </div>
    <?php endwhile; ?>

    <a href="books.php"><i class="fas fa-arrow-left"></i> الرجوع للكتب</a>
</div>
</body>
</html>
