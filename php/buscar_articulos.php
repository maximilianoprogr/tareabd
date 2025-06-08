<?php
// Incluir el archivo de conexión a la base de datos
include('../php/conexion.php');

// Obtener los parámetros de búsqueda desde la URL o asignar valores predeterminados
$query = $_GET['query'] ?? ''; // Título del artículo
$autor = $_GET['autor'] ?? ''; // Autor del artículo
$fecha_envio = $_GET['fecha_envio'] ?? ''; // Fecha de envío del artículo
$topico = $_GET['topico'] ?? ''; // Tópico del artículo
$revisor = $_GET['revisor'] ?? ''; // Revisor del artículo

// Validar que la fecha de envío tenga el formato correcto
if (!empty($fecha_envio) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_envio)) {
    echo "<p style='color: red;'>La fecha de envío debe tener el formato AAAA-MM-DD.</p>";
    exit(); // Finalizar la ejecución del script
}

// Validar que el título no exceda los 255 caracteres
if (!empty($query) && strlen($query) > 255) {
    echo "<p style='color: red;'>El título no puede exceder los 255 caracteres.</p>";
    exit(); // Finalizar la ejecución del script
}

// Validar que el tópico no exceda los 255 caracteres
if (!empty($topico) && strlen($topico) > 255) {
    echo "<p style='color: red;'>El tópico no puede exceder los 255 caracteres.</p>";
    exit(); // Finalizar la ejecución del script
}

// Validar que el revisor no exceda los 255 caracteres
if (!empty($revisor) && strlen($revisor) > 255) {
    echo "<p style='color: red;'>El nombre del revisor no puede exceder los 255 caracteres.</p>";
    exit(); // Finalizar la ejecución del script
}

// Validar que ninguno de los campos de búsqueda exceda los 255 caracteres
if (strlen($query) > 255 || strlen($autor) > 255 || strlen($topico) > 255 || strlen($revisor) > 255) {
    echo "<p style='color: red;'>Los campos de búsqueda no pueden exceder los 255 caracteres.</p>";
    exit(); // Finalizar la ejecución del script
}

// Consulta SQL para buscar artículos según los parámetros proporcionados
$sql = "SELECT a.titulo, a.resumen, GROUP_CONCAT(DISTINCT t.nombre) AS topicos
        FROM Articulo a
        LEFT JOIN Articulo_Topico at ON a.id_articulo = at.id_articulo
        LEFT JOIN Topico t ON at.id_topico = t.id_topico
        LEFT JOIN Autor_Articulo aa ON a.id_articulo = aa.id_articulo
        LEFT JOIN Usuario u ON aa.rut_autor = u.rut
        LEFT JOIN Articulo_Revisor ar ON a.id_articulo = ar.id_articulo
        LEFT JOIN Usuario r ON ar.rut_revisor = r.rut
        WHERE (a.titulo LIKE ? OR ? = '')
          AND (u.nombre LIKE ? OR ? = '')
          AND (a.fecha_envio = ? OR ? = '')
          AND (t.nombre LIKE ? OR ? = '')
          AND (r.nombre LIKE ? OR ? = '')
        GROUP BY a.id_articulo";

// Preparar y ejecutar la consulta SQL
$stmt = $pdo->prepare($sql);
$stmt->execute([
    "%$query%", $query,
    "%$autor%", $autor,
    $fecha_envio, $fecha_envio,
    "%$topico%", $topico,
    "%$revisor%", $revisor
]);
$articulos = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Búsqueda Avanzada</title>
</head>
<body>
    <h1>Búsqueda Avanzada</h1>
    <form method="GET">
        <label for="query">Buscar por título:</label>
        <input type="text" id="query" name="query" value="<?php echo htmlspecialchars($query); ?>"><br>

        <label for="autor">Buscar por autor:</label>
        <input type="text" id="autor" name="autor" value="<?php echo htmlspecialchars($autor); ?>"><br>

        <label for="fecha_envio">Buscar por fecha de envío:</label>
        <input type="date" id="fecha_envio" name="fecha_envio" value="<?php echo htmlspecialchars($fecha_envio); ?>"><br>

        <label for="topico">Buscar por tópico:</label>
        <input type="text" id="topico" name="topico" value="<?php echo htmlspecialchars($topico); ?>"><br>

        <label for="revisor">Buscar por revisor:</label>
        <input type="text" id="revisor" name="revisor" value="<?php echo htmlspecialchars($revisor); ?>"><br>

        <input type="submit" value="Buscar">
    </form>
    <a href="dashboard.php" style="font-family: Arial, sans-serif; font-size: 14px; color: #007BFF; text-decoration: none;">Volver al inicio</a>
    <table border="1">
        <tr>
            <th>Título</th>
            <th>Resumen</th>
            <th>Tópicos</th>
        </tr>
        <?php foreach ($articulos as $articulo): ?>
        <tr>
            <td><?php echo htmlspecialchars($articulo['titulo']); ?></td>
            <td><?php echo htmlspecialchars($articulo['resumen']); ?></td>
            <td><?php echo htmlspecialchars($articulo['topicos']); ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
