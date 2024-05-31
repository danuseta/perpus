<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if ($username === 'admin' && $password === 'ikancupang') {
        $_SESSION['admin'] = true;
        header("Location: admin.php");
        exit();
    } else {
        echo "Username atau password salah.";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login Admin</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/minjam.css">
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .masuk-button {
            background-color: #007BFF;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <h1>Login Admin</h1>
    <form action="loginadmin.php" method="post">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>

        <input type="submit" value="Login" class='masuk-button'>
    </form>
</body>
</html>
