<?php
include('conexion.php');

$query = $_GET['query'] ?? '';

$sql = "SELECT a.titulo, a.resumen, GROUP_CONCAT(DISTINCT t.nombre_topico) AS topicos
        FROM Articulo a
        LEFT JOIN Articulo_Topico at ON a.id_articulo = at.id_articulo
        LEFT JOIN Topico t ON at.id_topico = t.id_topico
        WHERE a.titulo LIKE ? OR ? = ''
        GROUP BY a.id_articulo";

$stmt = $pdo->prepare($sql);
$stmt->execute(["%$query%", $query]);
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
        <label for="query">Buscar:</label>
        <input type="text" id="query" name="query" value="<?php echo htmlspecialchars($query); ?>"><br>
        <input type="submit" value="Buscar">
    </form>
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
