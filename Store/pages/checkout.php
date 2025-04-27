<?php
session_start();
require_once("../includes/db.php");


if (!isset($_SESSION["user_id"])) {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION["user_id"];

// استرجاع السلة
$cart_items = $conn->query("
    SELECT cart.book_id, books.title, books.price, cart.quantity
    FROM cart
    JOIN books ON cart.book_id = books.id
    WHERE cart.user_id = $user_id
");

if ($cart_items->num_rows === 0) {
    echo "<p> سلتك فارغة.</p>";
    echo '<a href="books.php">⬅️ العودة للكتب</a>';
    exit;
}

$total_price = 0;
$order_items = [];

while ($item = $cart_items->fetch_assoc()) {
    $book_id = $item['book_id'];
    $quantity = $item['quantity'];
    $price = $item['price'];
    $total_price += $quantity * $price;

    $order_items[] = [
        'book_id' => $book_id,
        'quantity' => $quantity,
        'price' => $price
    ];
}

// إنشاء الطلب
$conn->query("INSERT INTO orders (user_id, total_price) VALUES ($user_id, $total_price)");
$order_id = $conn->insert_id;

// عناصر الطلب
foreach ($order_items as $item) {
    $conn->query("
        INSERT INTO order_items (order_id, book_id, quantity, price)
        VALUES ($order_id, {$item['book_id']}, {$item['quantity']}, {$item['price']})
    ");
}

// مسح السلة
$conn->query("DELETE FROM cart WHERE user_id = $user_id");
?>

<!DOCTYPE html>
<html lang="ar">
<head>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

    <meta charset="UTF-8">
    <title>تم تأكيد الطلب</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="container">
    <h2>✅ تم تأكيد طلبك!</h2>
    <p>رقم الطلب: <?= $order_id ?></p>
    <p>الإجمالي: <?= $total_price ?> ريال</p>
    <a href="books.php">⬅️ الرجوع للكتب</a>
</div>
</body>
</html>
