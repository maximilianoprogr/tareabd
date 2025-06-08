<?php
// Inicia la sesión para poder destruirla
session_start();

// Destruye todas las variables de sesión
session_destroy();

// Redirige al usuario a la página principal después de cerrar sesión
header('Location: ../php/index.php');

// Finaliza la ejecución del script para evitar que se ejecute código adicional
exit();
?>
