<?php
session_start();
include('php/conexion.php');

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Agregar una columna para mostrar los revisores asignados
$sql = "SELECT a.titulo, a.resumen, GROUP_CONCAT(t.nombre) AS topicos, GROUP_CONCAT(ar.rut_revisor) AS revisores
        FROM Articulo a
        LEFT JOIN Articulo_Topico at ON a.id_articulo = at.id_articulo
        LEFT JOIN Topico t ON at.id_topico = t.id_topico
        LEFT JOIN Autor_Articulo aa ON a.id_articulo = aa.id_articulo
        LEFT JOIN Articulo_Revisor ar ON a.id_articulo = ar.id_articulo
        WHERE aa.rut_autor = ? OR ar.rut_revisor = ?
        GROUP BY a.id_articulo";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id, $user_id]);
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
            <th>Revisores</th>
        </tr>
        <?php foreach ($articulos as $articulo): ?>
        <tr>
            <td><?php echo htmlspecialchars($articulo['titulo']); ?></td>
            <td><?php echo htmlspecialchars($articulo['resumen']); ?></td>
            <td><?php echo htmlspecialchars($articulo['topicos']); ?></td>
            <td>
                <?php if (!empty($articulo['revisores'])): ?>
                    <?php foreach (explode(',', $articulo['revisores']) as $revisor): ?>
                        <button>R<?php echo htmlspecialchars($revisor); ?> Consultar</button>
                    <?php endforeach; ?>
                <?php else: ?>
                    Sin revisores asignados
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    <a href="dashboard.php" style="font-family: Arial, sans-serif; font-size: 14px; color: #007BFF; text-decoration: none;">Volver al inicio</a>
</body>
</html>
