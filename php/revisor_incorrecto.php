<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

$revisor = isset($_GET['revisor']) ? htmlspecialchars($_GET['revisor']) : 'Desconocido';
$usuario = isset($_GET['usuario']) ? htmlspecialchars($_GET['usuario']) : 'Desconocido';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revisor Incorrecto</title>
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>
    <div class="container">
        <h1>Acceso Restringido</h1>
        <p>Hola <strong><?php echo $usuario; ?></strong>, solo el revisor <strong><?php echo $revisor; ?></strong> puede opinar sobre este artículo.</p>
        <a href="acceso_articulo.php" class="button">Volver</a>
    </div>
</body>
</html>
