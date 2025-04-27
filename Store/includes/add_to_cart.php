<?php
session_start();
require_once("db.php"); 

if (!isset($_SESSION["user_id"])) {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$book_id = $_POST["book_id"];
$quantity = $_POST["quantity"];

$check = $conn->query("SELECT id FROM cart WHERE user_id=$user_id AND book_id=$book_id");
if ($check->num_rows > 0) {
    $conn->query("UPDATE cart SET quantity = quantity + $quantity WHERE user_id=$user_id AND book_id=$book_id");
} else {
    $conn->query("INSERT INTO cart (user_id, book_id, quantity) VALUES ($user_id, $book_id, $quantity)");
}

header("Location: ../pages/books.php?added=1");
exit;
