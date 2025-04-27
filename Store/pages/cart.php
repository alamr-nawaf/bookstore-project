<?php
session_start();
require_once("../includes/db.php");


if (!isset($_SESSION["user_id"])) {
    header("Location: ../index.php");  // التوجيه إلى صفحة تسجيل الدخول إذا لم يكن المستخدم مسجلاً
    exit;
}

$user_id = $_SESSION["user_id"];

// تحديث الكمية في السلة
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update_qty"])) {
    $cart_id = $_POST["cart_id"];
    $quantity = max(1, (int)$_POST["quantity"]);
    $conn->query("UPDATE cart SET quantity = $quantity WHERE id = $cart_id AND user_id = $user_id");
}

// حذف كتاب من السلة
if (isset($_GET["remove"])) {
    $cart_id = $_GET["remove"];
    $conn->query("DELETE FROM cart WHERE id = $cart_id AND user_id = $user_id");
}

$items = $conn->query("
    SELECT cart.id AS cart_id, books.title, books.image, books.price, cart.quantity 
    FROM cart 
    JOIN books ON cart.book_id = books.id 
    WHERE cart.user_id = $user_id
");

$total = 0;
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title> سلة التسوق</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body>
<div class="container">
    <h2><i class="fas fa-shopping-cart"></i> سلة التسوق</h2>

    <?php if ($items->num_rows === 0): ?>
        <p>السلة فارغة.</p>
    <?php else: ?>
        <?php while($row = $items->fetch_assoc()): 
            $subtotal = $row["price"] * $row["quantity"];
            $total += $subtotal;
        ?>
        <div class="card cart-item">
            <div style="display: flex; align-items: center; gap: 15px;">
                <?php if ($row["image"]): ?>
                    <img src="<?= htmlspecialchars($row["image"]) ?>" alt="صورة الكتاب" style="width: 100px;">
                <?php endif; ?>
                <div style="flex-grow: 1;">
                    <strong><?= htmlspecialchars($row["title"]) ?></strong>
                    <p>السعر: <?= $row["price"] ?> ريال</p>
                    <form method="post">
                        <input type="hidden" name="cart_id" value="<?= $row["cart_id"] ?>">
                        <input type="number" name="quantity" value="<?= $row["quantity"] ?>" min="1" style="width: 50px;">
                        <input type="submit" name="update_qty" value="تحديث">
                        <a href="?remove=<?= $row["cart_id"] ?>" onclick="return confirm('هل أنت متأكد من حذف هذا الكتاب؟')">🗑 حذف</a>
                    </form>
                    <p><strong>المجموع: <?= $subtotal ?> ريال</strong></p>
                </div>
            </div>
        </div>
        <?php endwhile; ?>

        <h3>الإجمالي: <?= $total ?> ريال</h3>
        <a href="checkout.php"><i class="fas fa-check-circle"></i>  تأكيد الطلب</a>
    <?php endif; ?>

    <br><a href="books.php"><i class="fas fa-arrow-left"></i>  الرجوع للكتب</a>
</div>
</body>
</html>
