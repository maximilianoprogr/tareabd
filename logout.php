<?php
session_start();
session_destroy(); // Destruye la sesión
header("Location: login.php"); // Redirige al login
exit();
?>
