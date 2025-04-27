<?php
session_start();
require_once("includes/db.php");

// تسجيل خروج
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

// تسجيل دخول
if (isset($_POST["login"])) {
    $email = $_POST["email"];
    $password = md5($_POST["password"]);

    $sql = "SELECT * FROM users WHERE email = '$email' AND password = '$password'";
    $result = $conn->query($sql);

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $_SESSION["user_id"] = $user["id"];
        $_SESSION["name"] = $user["name"];
        $_SESSION["is_admin"] = $user["is_admin"];
        header("Location: pages/books.php");
        exit;
    } else {
        $login_error = "بيانات الدخول غير صحيحة";
    }
}

// إنشاء حساب
if (isset($_POST["register"])) {
    $name = $_POST["name"];
    $email = $_POST["email"];
    $password = md5($_POST["password"]);

// التحقق من صحة البريد الإلكتروني
    if (!preg_match("/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/", $email)) {
        $register_error = "البريد الإلكتروني غير صالح. يرجى إدخال بريد إلكتروني صحيح.";
    } else {
        $check = $conn->query("SELECT id FROM users WHERE email = '$email'");
        if ($check->num_rows > 0) {
            $register_error = "البريد الإلكتروني مستخدم بالفعل.";
        } else {
            $conn->query("INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$password')");
            header("Location: pages/books.php");  // تعديل المسار ليكون ضمن المجلد pages
            exit;
        }
    }
}

// دخول كزائر
if (isset($_POST["guest"])) {
    $_SESSION["guest"] = true;
    $_SESSION["name"] = "زائر";
    header("Location: pages/books.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>تسجيل الدخول</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body>
<div class="container">
    <h2><i class="fas fa-sign-in-alt"></i> تسجيل الدخول</h2>
    <form method="post">
        <input type="email" name="email" placeholder="البريد الإلكتروني" required>
        <input type="password" name="password" placeholder="كلمة المرور" required>
        <input type="submit" name="login" value="دخول">
        <?php if (!empty($login_error)) echo "<p style='color:red'>$login_error</p>"; ?>
    </form>

    <h2><i class="fas fa-user-plus"></i> إنشاء حساب جديد</h2>
    <form method="post">
        <input type="text" name="name" placeholder="الاسم الكامل" required>
        <input type="email" name="email" placeholder="البريد الإلكتروني" required>
        <input type="password" name="password" placeholder="كلمة المرور" required>
        <input type="submit" name="register" value="تسجيل">
        <?php 
            if (!empty($register_error)) echo "<p style='color:red'>$register_error</p>"; 
            if (!empty($register_success)) echo "<p style='color:green'>$register_success</p>";
        ?>
    </form>

    <h2><i class="fas fa-user"></i> دخول كزائر</h2>
    <form method="post">
        <input type="submit" name="guest" value="الدخول كزائر">
    </form>
</div>
</body>
</html>
