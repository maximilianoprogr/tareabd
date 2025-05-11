<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: ../php/dashboard.php'); // Redirigir al dashboard si ya está logueado
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
    <h1>Login</h1>
    <form action="../php/login_process.php" method="POST">
        <input type="text" name="userid" placeholder="User ID" required><br><br>
        <input type="password" name="password" placeholder="Password" required><br><br>
        <input type="submit" value="Login">
    </form>
    <br>
    <a href="../php/register.php">Register</a>
</body>
</html>