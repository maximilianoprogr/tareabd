<?php
// Incluye la conexión a la base de datos.
include('../php/conexion.php');

// Consulta para obtener todos los artículos evaluados.
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
    <!-- Tabla para mostrar los artículos evaluados -->
    <table border="1">
        <tr>
            <th>Título</th>
            <th>Resumen</th>
            <th>Tópicos</th>
            <th>Autores</th>
        </tr>
        <!-- Itera sobre los artículos y los muestra en la tabla -->
        <?php foreach ($articulos as $articulo): ?>
        <tr>
            <td><?php echo htmlspecialchars($articulo['titulo']); ?></td>
            <td><?php echo htmlspecialchars($articulo['resumen']); ?></td>
            <td><?php echo htmlspecialchars($articulo['topicos']); ?></td>
            <td><?php echo htmlspecialchars($articulo['autores']); ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <!-- Enlace para volver al inicio -->
    <a href="dashboard.php" style="font-family: Arial, sans-serif; font-size: 14px; color: #007BFF; text-decoration: none;">Volver al inicio</a>
</body>
</html>
