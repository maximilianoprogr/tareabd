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
    <?php if (!$resultados_publicados && ($_SESSION['rol'] === 'admin' || $_SESSION['rol'] === 'Jefe Comite de Programa' || $_GET['revision'] === $_SESSION['usuario'])): ?>
        <p style="font-family: Arial, sans-serif; font-size: 18px; color: #333; margin-bottom: 20px;">Formulario de Evaluación</p>
        <form method="POST" action="procesar_evaluacion.php" style="margin-top: 20px; padding: 20px; border: 1px solid #ccc; border-radius: 10px; background-color: #f9f9f9;">
            <div style="margin-bottom: 15px;">
                <label for="calidad_tecnica" style="font-family: Arial, sans-serif; font-size: 14px; color: #555;">Calidad Técnica:</label>
                <input type="checkbox" id="calidad_tecnica" name="calidad_tecnica" value="1" style="margin-left: 10px;">
            </div>

            <div style="margin-bottom: 15px;">
                <label for="originalidad" style="font-family: Arial, sans-serif; font-size: 14px; color: #555;">Originalidad:</label>
                <input type="checkbox" id="originalidad" name="originalidad" value="1" style="margin-left: 10px;">
            </div>

            <div style="margin-bottom: 15px;">
                <label for="valoracion_global" style="font-family: Arial, sans-serif; font-size: 14px; color: #555;">Valoración Global:</label>
                <input type="checkbox" id="valoracion_global" name="valoracion_global" value="1" style="margin-left: 10px;">
            </div>

            <div style="margin-bottom: 15px;">
                <label for="argumentos_valoracion" style="font-family: Arial, sans-serif; font-size: 14px; color: #555;">Argumentos Valoración Global:</label>
                <textarea id="argumentos_valoracion" name="argumentos_valoracion" required style="width: 100%; height: 80px; padding: 10px; border: 1px solid #ccc; border-radius: 5px; font-family: Arial, sans-serif; font-size: 14px; color: #555;"></textarea>
            </div>

            <div style="margin-bottom: 15px;">
                <label for="comentarios_autores" style="font-family: Arial, sans-serif; font-size: 14px; color: #555;">Comentarios a Autores:</label>
                <textarea id="comentarios_autores" name="comentarios_autores" required style="width: 100%; height: 80px; padding: 10px; border: 1px solid #ccc; border-radius: 5px; font-family: Arial, sans-serif; font-size: 14px; color: #555;"></textarea>
            </div>

            <button type="submit" style="padding: 10px 20px; background-color: #28a745; color: white; border: none; border-radius: 5px; font-family: Arial, sans-serif; font-size: 14px; cursor: pointer;">Enviar Evaluación</button>
        </form>
    <?php elseif ($resultados_publicados): ?>
        <p style="font-family: Arial, sans-serif; color: #555;">El formulario de evaluación no está disponible en esta página.</p>
    <?php endif; ?>

    <br><br>
    <a href="acceso_articulo.php" style="font-family: Arial, sans-serif; font-size: 14px; color: #007BFF; text-decoration: none;">Volver</a>
    <br><br>
    <a href="dashboard.php" style="font-family: Arial, sans-serif; font-size: 14px; color: #007BFF; text-decoration: none;">Volver al inicio</a>
</body>
</html>
