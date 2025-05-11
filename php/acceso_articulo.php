<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso al Artículo</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body style="font-family: Arial, sans-serif; margin: 20px;">
    <h1 style="font-size: 18px; color: #333;">Datos del Artículo</h1>
    <div style="margin-bottom: 20px; border: 1px solid #ccc; padding: 10px;">
        <p><strong>ID:</strong> HU.1</p>
        <p><strong>Título:</strong> Ejemplo de Artículo</p>
    </div>

    <h2 style="font-size: 16px; color: #555;">Revisiones</h2>
    <div style="margin-bottom: 20px;">
        <a href="revisiones.php?revision=R1" style="font-size: 12px; padding: 5px 10px; margin-right: 10px; background-color: #007BFF; color: white; text-decoration: none; border-radius: 5px;">R1 Consultar</a>
        <a href="revisiones.php?revision=R2" style="font-size: 12px; padding: 5px 10px; background-color: #007BFF; color: white; text-decoration: none; border-radius: 5px;">R2 Consultar</a>
    </div>

    <!-- Formulario de Evaluación se muestra solo si se accede a una revisión -->
    <?php if (isset($_GET['revision'])): ?>
        <h2 style="font-size: 16px; color: #555;">Formulario de Evaluación</h2>
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
    <a href="dashboard.php" style="font-family: Arial, sans-serif; font-size: 14px; color: #007BFF; text-decoration: none;">Volver al inicio</a>
</body>
</html>
