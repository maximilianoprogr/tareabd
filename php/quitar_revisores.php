<?php
// quitar_revisores.php

session_start();
include('conexion.php');

// Verificar si el usuario tiene permisos de jefe del comité
if (!isset($_SESSION['rol']) || ($_SESSION['rol'] !== 'admin' && $_SESSION['rol'] !== 'Jefe Comite de Programa')) {
    header("Location: dashboard.php");
    exit();
}

// Obtener el ID del artículo desde la solicitud
if (!isset($_GET['id_articulo'])) {
    echo "<p style='color: red;'>Error: No se proporcionó un ID de artículo.</p>";
    exit();
}
$id_articulo = $_GET['id_articulo'];

// Obtener información del artículo
$sql_articulo = "SELECT titulo FROM Articulo WHERE id_articulo = ?";
$stmt_articulo = $pdo->prepare($sql_articulo);
$stmt_articulo->execute([$id_articulo]);
$articulo = $stmt_articulo->fetch();
if (!$articulo) {
    echo "<p style='color: red;'>Error: El artículo no existe.</p>";
    exit();
}

// Obtener revisores asignados al artículo
$sql_revisores = "SELECT ar.rut_revisor, u.nombre FROM Articulo_Revisor ar JOIN Usuario u ON ar.rut_revisor = u.rut WHERE ar.id_articulo = ?";
$stmt_revisores = $pdo->prepare($sql_revisores);
$stmt_revisores->execute([$id_articulo]);
$revisores = $stmt_revisores->fetchAll();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quitar Revisores</title>
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>
    <h1>Quitar Revisores del Artículo: <?php echo htmlspecialchars($articulo['titulo']); ?></h1>
    <form method="POST" action="procesar_quitar_revisores.php">
        <input type="hidden" name="id_articulo" value="<?php echo htmlspecialchars($id_articulo); ?>">
        <table border="1" style="width: 100%; border-collapse: collapse; text-align: center; font-family: Arial, sans-serif;">
            <tr style="background-color: #f2f2f2; font-weight: bold;">
                <th>Seleccionar</th>
                <th>Nombre</th>
                <th>RUT</th>
            </tr>
            <?php foreach ($revisores as $revisor): ?>
                <tr>
                    <td><input type="checkbox" name="rut_revisores[]" value="<?php echo htmlspecialchars($revisor['rut_revisor']); ?>"></td>
                    <td><?php echo htmlspecialchars($revisor['nombre']); ?></td>
                    <td><?php echo htmlspecialchars($revisor['rut_revisor']); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
        <button type="submit" style="margin-top: 10px; background-color: #f44336; color: white; border: none; padding: 10px 20px; cursor: pointer;">Quitar Revisores</button>
    </form>
    <a href="asignar_articulos.php" style="display: inline-block; margin-top: 10px;">Volver</a>
</body>
</html>
