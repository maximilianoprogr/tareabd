<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

// Obtener todos los artículos enviados
include('conexion.php');
$sql_articulos = "SELECT id_articulo, titulo FROM Articulo";
$stmt_articulos = $pdo->query($sql_articulos);
$articulos = $stmt_articulos->fetchAll();

// Verificar si se seleccionó un artículo
$articulo_seleccionado = isset($_GET['id_articulo']) ? $_GET['id_articulo'] : null;
$detalles_articulo = null;
$revisores = [];

if ($articulo_seleccionado) {
    // Obtener detalles del artículo seleccionado
    $sql_detalles = "SELECT titulo, resumen FROM Articulo WHERE id_articulo = ?";
    $stmt_detalles = $pdo->prepare($sql_detalles);
    $stmt_detalles->execute([$articulo_seleccionado]);
    $detalles_articulo = $stmt_detalles->fetch();

    // Obtener revisores asignados al artículo
    $sql_revisores = "SELECT ar.rut_revisor FROM Articulo_Revisor ar WHERE ar.id_articulo = ?";
    $stmt_revisores = $pdo->prepare($sql_revisores);
    $stmt_revisores->execute([$articulo_seleccionado]);
    $revisores = $stmt_revisores->fetchAll(PDO::FETCH_COLUMN);
}

// Verificar si los resultados de la revisión han sido publicados
$resultados_publicados = false;
if ($articulo_seleccionado) {
    $sql_resultados = "SELECT COUNT(*) FROM Evaluacion_Articulo WHERE id_articulo = ?";
    $stmt_resultados = $pdo->prepare($sql_resultados);
    $stmt_resultados->execute([$articulo_seleccionado]);
    $resultados_publicados = $stmt_resultados->fetchColumn() > 0;
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
    <h1 style="font-size: 18px; color: #333;">Artículos Enviados</h1>
    <ul>
        <?php foreach ($articulos as $articulo): ?>
            <li><a href="acceso_articulo.php?id_articulo=<?php echo $articulo['id_articulo']; ?>" style="color: #007BFF; text-decoration: none;">Artículo: <?php echo htmlspecialchars($articulo['titulo']); ?></a></li>
        <?php endforeach; ?>
    </ul>

    <?php if ($detalles_articulo): ?>
        <h1 style="font-size: 18px; color: #333;">Datos del Artículo</h1>
        <div style="margin-bottom: 20px; border: 1px solid #ccc; padding: 10px;">
            <p><strong>ID:</strong> <?php echo htmlspecialchars($articulo_seleccionado); ?></p>
            <p><strong>Título:</strong> <?php echo htmlspecialchars($detalles_articulo['titulo']); ?></p>
            <p><strong>Resumen:</strong> <?php echo htmlspecialchars($detalles_articulo['resumen']); ?></p>
        </div>

        <h2 style="font-size: 16px; color: #555;">Revisiones</h2>
        <div style="margin-bottom: 20px;">
            <?php if (!empty($revisores)): ?>
                <?php foreach ($revisores as $revisor): ?>
                    <a href="revisiones.php?revision=<?php echo htmlspecialchars($revisor); ?>" style="font-size: 12px; padding: 5px 10px; margin-right: 10px; background-color: #007BFF; color: white; text-decoration: none; border-radius: 5px;">R<?php echo htmlspecialchars($revisor); ?> Consultar</a>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No hay revisores asignados a este artículo.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

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

    <!-- Formulario de Evaluación en modo consulta si los resultados han sido publicados -->
    <?php if ($resultados_publicados): ?>
        <h2 style="font-size: 16px; color: #555;">Formulario de Evaluación (Modo Consulta)</h2>
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
    <?php endif; ?>

    <a href="dashboard.php" style="font-family: Arial, sans-serif; font-size: 14px; color: #007BFF; text-decoration: none;">Volver al inicio</a>
</body>
</html>
