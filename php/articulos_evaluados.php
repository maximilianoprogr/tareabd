<?php
include('../php/conexion.php');

$sql = "SELECT * FROM ArticulosEvaluados";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$articulos = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Artículos Evaluados</title>
</head>
<body>
    <h1>Artículos Evaluados</h1>
    <table border="1">
        <tr>
            <th>Título</th>
            <th>Resumen</th>
            <th>Tópicos</th>
            <th>Autores</th>
        </tr>
        <?php foreach ($articulos as $articulo): ?>
        <tr>
            <td><?php echo htmlspecialchars($articulo['titulo']); ?></td>
            <td><?php echo htmlspecialchars($articulo['resumen']); ?></td>
            <td><?php echo htmlspecialchars($articulo['topicos']); ?></td>
            <td><?php echo htmlspecialchars($articulo['autores']); ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <a href="dashboard.php" style="font-family: Arial, sans-serif; font-size: 14px; color: #007BFF; text-decoration: none;">Volver al inicio</a>
</body>
</html>
