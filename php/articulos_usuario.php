<?php
session_start();
include('php/conexion.php');

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Consultar los artículos del usuario como autor
$sql = "SELECT a.titulo, a.resumen, GROUP_CONCAT(t.nombre_topico) AS topicos
        FROM Articulo a
        JOIN Autor_Articulo aa ON a.id_articulo = aa.id_articulo
        JOIN Topico t ON a.id_articulo = t.id_articulo
        WHERE aa.rut_autor = ?
        GROUP BY a.id_articulo";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$articulos = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Artículos</title>
</head>
<body>
    <h1>Mis Artículos</h1>
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
    <a href="dashboard.php" style="font-family: Arial, sans-serif; font-size: 14px; color: #007BFF; text-decoration: none;">Volver al inicio</a>
</body>
</html>
