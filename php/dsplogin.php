<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: php/index.php');
    exit();
}

include('php/db_connection.php');

$sql = "SELECT * FROM Usuario WHERE id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
</head>
<body>
    <h1>Welcome, <?php echo $user['userid']; ?>!</h1>
    <p>Email: <?php echo $user['email']; ?></p>
    <a href="php/logout.php">Logout</a>
</body>
</html>
