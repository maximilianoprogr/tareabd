<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

$revisor = isset($_GET['revisor']) ? htmlspecialchars($_GET['revisor']) : 'Desconocido';
$id_articulo = isset($_GET['id_articulo']) ? htmlspecialchars($_GET['id_articulo']) : 'Desconocido';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evaluación Incompleta</title>
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>
    <div class="container">
        <h1>Evaluación Incompleta</h1>
        <p>El revisor <strong><?php echo $revisor; ?></strong> aún no ha completado la evaluación para el artículo con ID <strong><?php echo $id_articulo; ?></strong>.</p>
        <a href="acceso_articulo.php" class="button">Volver</a>
    </div>
</body>
</html>
