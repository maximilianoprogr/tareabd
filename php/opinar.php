<?php
include('conexion.php');

// Obtener el ID del artículo y el revisor desde la URL
$id_articulo = $_GET['revision'] ?? null;
if (!$id_articulo) {
    echo "<p>Error: No se especificó un artículo para opinar.</p>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Procesar el formulario
    $calidad_tecnica = isset($_POST['calidad_tecnica']) ? 1 : 0;
    $originalidad = isset($_POST['originalidad']) ? 1 : 0;
    $valoracion_global = isset($_POST['valoracion_global']) ? 1 : 0;
    $argumentos_valoracion = $_POST['argumentos_valoracion'] ?? '';
    $comentarios_autores = $_POST['comentarios_autores'] ?? '';

    // Guardar en la base de datos
    $sql = "UPDATE Evaluacion_Articulo SET calidad_tecnica = ?, originalidad = ?, valoracion_global = ?, argumentos_valoracion = ?, comentarios_autores = ? WHERE id_articulo = ? AND rut_revisor = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$calidad_tecnica, $originalidad, $valoracion_global, $argumentos_valoracion, $comentarios_autores, $id_articulo, $_SESSION['usuario']]);

    echo "<p>Opinión guardada correctamente.</p>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Opinar</title>
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>
    <div class="container">
        <h1>Opinar sobre el Artículo</h1>
        <h2 style="font-family: Arial, sans-serif; color: #555;">Formulario de Evaluación</h2>
        <form method="POST" style="border: 1px solid #ccc; padding: 15px;">
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

            <button type="submit" style="background-color: #007BFF; color: white; padding: 10px 15px; border: none; border-radius: 5px; font-size: 14px;">Enviar Opinión</button>
        </form>
    </div>
</body>
</html>
