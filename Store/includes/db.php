<?php
$conn = new mysqli("localhost", "root", "", "bookstore");
if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
}
?>