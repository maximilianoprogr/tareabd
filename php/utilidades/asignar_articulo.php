<?php
session_start();
include('../conexion.php');

$rut_revisor = $_GET['rut_revisor'] ?? null;
if (!$rut_revisor) {
    echo "Falta el rut del revisor.";
    exit;
}

// Obtener nombre y tópicos del revisor
$stmt = $pdo->prepare("
    SELECT u.nombre, GROUP_CONCAT(DISTINCT t.nombre SEPARATOR ', ') AS topicos
    FROM Usuario u
    LEFT JOIN Revisor_Topico rt ON u.rut = rt.rut_revisor
    LEFT JOIN Topico t ON rt.id_topico = t.id_topico
    WHERE u.rut = ?
    GROUP BY u.rut, u.nombre
");
$stmt->execute([$rut_revisor]);
$revisor = $stmt->fetch();
if (!$revisor) {
    echo "Revisor no encontrado.";
    exit;
}

$msg = '';

// --- Procesar quitar artículo primero ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_articulo']) && isset($_POST['quitar'])) {
    $id_articulo = $_POST['id_articulo'];
    $stmt = $pdo->prepare("DELETE FROM Articulo_Revisor WHERE id_articulo = ? AND rut_revisor = ?");
    $stmt->execute([$id_articulo, $rut_revisor]);
    header("Location: asignar_articulo.php?rut_revisor=" . urlencode($rut_revisor) . "&msg=Artículo+quitado+correctamente");
    exit;
}

// --- Procesar asignación si se envió el formulario ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_articulo']) && !isset($_POST['quitar'])) {
    $id_articulo = $_POST['id_articulo'];
    // Verificar si ya está asignado
    $stmt = $pdo->prepare("SELECT 1 FROM Articulo_Revisor WHERE id_articulo = ? AND rut_revisor = ?");
    $stmt->execute([$id_articulo, $rut_revisor]);
    if ($stmt->fetch()) {
        $msg = "El artículo ya está asignado a este revisor.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO Articulo_Revisor (id_articulo, rut_revisor) VALUES (?, ?)");
        $stmt->execute([$id_articulo, $rut_revisor]);
        $msg = "Artículo asignado correctamente.";
    }
    header("Location: asignar_articulo.php?rut_revisor=" . urlencode($rut_revisor) . "&msg=" . urlencode($msg));
    exit;
}

// Obtener todos los artículos y verificar si están asignados a este revisor
$stmt = $pdo->query("SELECT id_articulo, titulo FROM Articulo ORDER BY id_articulo");
$articulos = $stmt->fetchAll();

// Obtener los IDs de los artículos ya asignados a este revisor
$stmt = $pdo->prepare("SELECT id_articulo FROM Articulo_Revisor WHERE rut_revisor = ?");
$stmt->execute([$rut_revisor]);
$articulos_asignados = $stmt->fetchAll(PDO::FETCH_COLUMN);

if (isset($_GET['msg'])) {
    $msg = $_GET['msg'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Asignar artículo a revisor</title>
</head>
<body>
<a href="../asignar_articulos.php" style="display:inline-block;padding:8px 16px;background:#007BFF;color:#fff;text-decoration:none;border-radius:4px;margin-bottom:16px;">Volver a artículos</a>
<h2>Asignar artículo a: <?= htmlspecialchars($revisor['nombre']) ?></h2>
<p><strong>Tópicos del revisor:</strong><br>
<?= $revisor['topicos'] ? nl2br(htmlspecialchars(str_replace(', ', "\n", $revisor['topicos']))) : 'Sin tópicos'; ?>
</p>
<?php if ($msg): ?>
    <p style="color:green;"><?= htmlspecialchars($msg) ?></p>
<?php endif; ?>
<table border="1" cellpadding="6" style="border-collapse:collapse;width:100%">
    <tr style="background:#f2f2f2">
        <th>ID</th>
        <th>Título</th>
        <th>Acción</th>
    </tr>
    <?php foreach ($articulos as $articulo): ?>
    <tr>
        <td><?= htmlspecialchars($articulo['id_articulo']) ?></td>
        <td><?= htmlspecialchars($articulo['titulo']) ?></td>
        <td>
            <?php if (in_array($articulo['id_articulo'], $articulos_asignados)): ?>
                <!-- Botón para quitar artículo -->
                <form method="post" style="display:inline;">
                    <input type="hidden" name="id_articulo" value="<?= htmlspecialchars($articulo['id_articulo']) ?>">
                    <input type="hidden" name="quitar" value="1">
                    <button type="submit" style="background:#dc3545;color:#fff;">Quitar</button>
                </form>
            <?php else: ?>
                <!-- Botón para asignar artículo -->
                <form method="post" style="display:inline;">
                    <input type="hidden" name="id_articulo" value="<?= htmlspecialchars($articulo['id_articulo']) ?>">
                    <button type="submit">Asignar</button>
                </form>
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
</body>
</html>