<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

$revision = isset($_GET['revision']) ? htmlspecialchars($_GET['revision']) : 'Desconocida';

// Verificar si los resultados de la revisión están publicados
include('conexion.php');
$resultados_publicados = false;
if (isset($_GET['revision'])) {
    $sql_resultados = "SELECT COUNT(*) FROM Evaluacion_Articulo WHERE rut_revisor = ?";
    $stmt_resultados = $pdo->prepare($sql_resultados);
    $stmt_resultados->execute([$_GET['revision']]);
    $resultados_publicados = $stmt_resultados->fetchColumn() > 0;
}

// Mostrar contenido solo si se seleccionó una revisión
if (!isset($_GET['revision'])) {
    echo '<p style="font-family: Arial, sans-serif; color: #555;">Por favor, seleccione una revisión para continuar.</p>';
    echo '<a href="acceso_articulo.php" style="font-family: Arial, sans-serif; font-size: 14px; color: #007BFF; text-decoration: none;">Volver</a>';
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revisión <?php echo $revision; ?></title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <h1 style="font-family: Arial, sans-serif; color: #333;">Revisión <?php echo $revision; ?></h1>

    <!-- Mostrar el formulario solo si se seleccionó una revisión -->
    <?php if (isset($_GET['revision'])): ?>
        <p style="font-size: 14px; color: #555;">El formulario de evaluación no está disponible en esta página.</p>
    <?php endif; ?>

    <br><br>
    <a href="acceso_articulo.php" style="font-family: Arial, sans-serif; font-size: 14px; color: #007BFF; text-decoration: none;">Volver</a>
    <br><br>
    <a href="dashboard.php" style="font-family: Arial, sans-serif; font-size: 14px; color: #007BFF; text-decoration: none;">Volver al inicio</a>
</body>
</html>
