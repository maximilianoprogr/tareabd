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
        <?php if ($resultados_publicados): ?>
            <h2 style="font-family: Arial, sans-serif; color: #555;">Formulario de Evaluación (Modo Consulta)</h2>
            <form style="border: 1px solid #ccc; padding: 15px;">
                <div style="margin-bottom: 15px;">
                    <label for="calidad_tecnica" style="font-size: 14px; display: block; margin-bottom: 5px;">Calidad Técnica:</label>
                    <input type="checkbox" id="calidad_tecnica" name="calidad_tecnica" disabled>
                </div>

                <div style="margin-bottom: 15px;">
                    <label for="originalidad" style="font-size: 14px; display: block; margin-bottom: 5px;">Originalidad:</label>
                    <input type="checkbox" id="originalidad" name="originalidad" disabled>
                </div>

                <div style="margin-bottom: 15px;">
                    <label for="valoracion_global" style="font-size: 14px; display: block; margin-bottom: 5px;">Valoración Global:</label>
                    <input type="checkbox" id="valoracion_global" name="valoracion_global" disabled>
                </div>

                <div style="margin-bottom: 15px;">
                    <label for="argumentos_valoracion" style="font-size: 14px; display: block; margin-bottom: 5px;">Argumentos de Valoración Global:</label>
                    <textarea id="argumentos_valoracion" name="argumentos_valoracion" rows="3" style="width: 100%; font-size: 12px; padding: 5px; border: 1px solid #ccc;" readonly></textarea>
                </div>

                <div style="margin-bottom: 15px;">
                    <label for="comentarios_autores" style="font-size: 14px; display: block; margin-bottom: 5px;">Comentarios a Autores:</label>
                    <textarea id="comentarios_autores" name="comentarios_autores" rows="3" style="width: 100%; font-size: 12px; padding: 5px; border: 1px solid #ccc;" readonly></textarea>
                </div>
            </form>
        <?php else: ?>
            <!-- Formulario editable si los resultados no están publicados -->
            <h2 style="font-family: Arial, sans-serif; color: #555;">Formulario de Evaluación</h2>
            <form style="border: 1px solid #ccc; padding: 15px;">
                <div style="margin-bottom: 15px;">
                    <label for="calidad_tecnica" style="font-size: 14px; display: block; margin-bottom: 5px;">Calidad Técnica:</label>
                    <input type="checkbox" id="calidad_tecnica" name="calidad_tecnica">
                </div>

                <div style="margin-bottom: 15px;">
                    <label for="originalidad" style="font-size: 14px; display: block; margin-bottom: 5px;">Originalidad:</label>
                    <input type="checkbox" id="originalidad" name="originalidad">
                </div>

                <div style="margin-bottom: 15px;">
                    <label for="valoracion_global" style="font-size: 14px; display: block; margin-bottom: 5px;">Valoración Global:</label>
                    <input type="checkbox" id="valoracion_global" name="valoracion_global">
                </div>

                <div style="margin-bottom: 15px;">
                    <label for="argumentos_valoracion" style="font-size: 14px; display: block; margin-bottom: 5px;">Argumentos de Valoración Global:</label>
                    <textarea id="argumentos_valoracion" name="argumentos_valoracion" rows="3" style="width: 100%; font-size: 12px; padding: 5px; border: 1px solid #ccc;"></textarea>
                </div>

                <div style="margin-bottom: 15px;">
                    <label for="comentarios_autores" style="font-size: 14px; display: block; margin-bottom: 5px;">Comentarios a Autores:</label>
                    <textarea id="comentarios_autores" name="comentarios_autores" rows="3" style="width: 100%; font-size: 12px; padding: 5px; border: 1px solid #ccc;"></textarea>
                </div>

                <button type="submit" style="font-size: 14px; padding: 10px 20px; background-color: #4CAF50; color: white; border: none; cursor: pointer;">Enviar Evaluación</button>
            </form>
        <?php endif; ?>
    <?php endif; ?>

    <br><br>
    <a href="acceso_articulo.php" style="font-family: Arial, sans-serif; font-size: 14px; color: #007BFF; text-decoration: none;">Volver</a>
    <br><br>
    <a href="dashboard.php" style="font-family: Arial, sans-serif; font-size: 14px; color: #007BFF; text-decoration: none;">Volver al inicio</a>
</body>
</html>
