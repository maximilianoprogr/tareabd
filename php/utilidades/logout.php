<?php
session_start();
session_destroy(); // Destruye la sesión
header("Location: ../php/login.php"); // Redirige al login
exit();
?>
