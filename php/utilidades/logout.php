<?php
// Iniciar sesión para manejar autenticación de usuarios
session_start();

// Destruir la sesión actual para cerrar la sesión del usuario
session_destroy();

// Redirigir al usuario a la página de inicio de sesión
header("Location: ../login.php");
exit();
?>
