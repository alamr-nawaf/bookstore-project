<?php
session_start();
require_once("../includes/db.php");


// التحقق من تسجيل الدخول أو كزائر
if (!isset($_SESSION["user_id"]) && !isset($_SESSION["guest"])) {
    header("Location: ../index.php");
    exit;
}

// التصنيفات
$category_result = $conn->query("SELECT DISTINCT category FROM books WHERE category IS NOT NULL AND category != ''");

// تصنيف مختار
$selected_category = isset($_GET['category']) ? $_GET['category'] : 'all';

// استعلام الكتب حسب الفلتر
if ($selected_category === 'all') {
    $books = $conn->query("SELECT * FROM books");
} else {
    $stmt = $conn->prepare("SELECT * FROM books WHERE category = ?");
    $stmt->bind_param("s", $selected_category);
    $stmt->execute();
    $books = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>موقع الكتب</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body>
<div class="container">
<?php if (isset($_GET['added'])): ?>
    <div id="success-message" style="background-color: #d0f0c0; color: #2e7d32; padding: 10px; border-radius: 5px; margin-bottom: 20px; text-align: center;">
        ✅ تم إضافة الكتاب للسلة!
    </div>
<?php endif; ?>

    <h2> أهلاً وسهلاً </h2>

    <p>
    <a href="../index.php?logout=1"><i class="fas fa-sign-out-alt"></i> تسجيل الخروج</a>
    <?php if (
        isset($_SESSION["user_id"]) &&
        isset($_SESSION["is_admin"]) &&
        $_SESSION["is_admin"] == 1 &&
        !isset($_SESSION["guest"])
    ): ?>
        | <a href="admin.php"><i class="fas fa-cogs"></i> لوحة تحكم المشرف</a>
    <?php endif; ?>
    | <a href="cart.php"><i class="fas fa-shopping-cart"></i> السلة</a>
</p>

<!-- فلتر التصنيفات -->
<form method="get" style="margin-bottom: 20px;">
    <label for="category"><i class="fas fa-filter"></i> التصنيف:</label>
    <select name="category" id="category" onchange="this.form.submit()">
        <option value="all" <?= $selected_category == 'all' ? 'selected' : '' ?>>الكل</option>
        <?php while ($cat = $category_result->fetch_assoc()): ?>
            <option value="<?= htmlspecialchars($cat['category']) ?>" <?= $selected_category == $cat['category'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($cat['category']) ?>
            </option>
        <?php endwhile; ?>
    </select>
</form>

    <h3 style="color:#6d4c41;"> التصنيف: <?= $selected_category === 'all' ? 'الكل' : htmlspecialchars($selected_category) ?></h3>

    <!-- عرض الكتب -->
    <?php while ($book = $books->fetch_assoc()): ?>
        <div class="card" style="display: flex; gap: 20px; align-items: flex-start;">
            <?php if ($book['image']): ?>
                <img src="<?= htmlspecialchars($book['image']) ?>" alt="صورة <?= htmlspecialchars($book['title']) ?>" style="width:100px;">
            <?php endif; ?>
            <div style="flex: 1;">
                <h3><?= htmlspecialchars($book['title']) ?></h3>
                <p><strong>المؤلف:</strong> <?= htmlspecialchars($book['author']) ?></p>
                <p><strong>السعر:</strong> <?= $book['price'] ?> ريال</p>
                <p><strong>التصنيف:</strong> <?= htmlspecialchars($book['category']) ?: 'غير مصنف' ?></p>
                <?php if (!empty($book['description'])): ?>
                    <p style="font-size: 14px; color: #6d4c41;"><?= nl2br(htmlspecialchars($book['description'])) ?></p>
                <?php endif; ?>

                <?php if (isset($_SESSION["user_id"])): ?>
                    <form method="post" action="../includes/add_to_cart.php" style="display: flex; gap: 5px; margin-top: 10px;">
                        <input type="hidden" name="book_id" value="<?= $book['id'] ?>">
                        <input type="number" name="quantity" value="1" min="1" style="width: 50px;">
                        <input type="submit" value="🛒اضافة" style="padding: 5px 10px;">
                    </form>
                <?php else: ?>
                    <p style="color: grey;"> تسجيل الدخول مطلوب لإضافة للسلة</p>
                <?php endif; ?>
            </div>
        </div>
    <?php endwhile; ?>

</div>
<script>
    setTimeout(function() {
        const msg = document.getElementById("success-message");
        if (msg) {
            msg.style.display = "none";
        }
    }, 2000); // 2 ثواني
</script>
</body>
</html>
