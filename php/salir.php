<?php
session_start();
session_destroy(); // Destruir la sesión
header('Location: php/index.php'); // Redirigir a la página de login
exit();
?>
