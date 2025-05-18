<?php
session_start();
include('../conexion.php');

// Obtener el id del artículo
$id_articulo = $_GET['id_articulo'] ?? null;
if (!$id_articulo) {
    echo "Falta el ID del artículo.";
    exit;
}

// Obtener información del artículo y sus tópicos
$stmt = $pdo->prepare("SELECT a.titulo, GROUP_CONCAT(DISTINCT t.nombre SEPARATOR ', ') AS topicos
                      FROM Articulo a
                      LEFT JOIN Articulo_Topico at ON a.id_articulo = at.id_articulo
                      LEFT JOIN Topico t ON at.id_topico = t.id_topico
                      WHERE a.id_articulo = ?
                      GROUP BY a.id_articulo, a.titulo");
$stmt->execute([$id_articulo]);
$articulo = $stmt->fetch();
if (!$articulo) {
    echo "Artículo no encontrado.";
    exit;
}

// Procesar asignación si se envió el formulario
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rut_revisor'])) {
    $rut_revisor = $_POST['rut_revisor'];
    // Verificar si ya está asignado
    $stmt = $pdo->prepare("SELECT 1 FROM Articulo_Revisor WHERE id_articulo = ? AND rut_revisor = ?");
    $stmt->execute([$id_articulo, $rut_revisor]);
    if ($stmt->fetch()) {
        $msg = "El revisor ya está asignado a este artículo.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO Articulo_Revisor (id_articulo, rut_revisor) VALUES (?, ?)");
        $stmt->execute([$id_articulo, $rut_revisor]);
        $msg = "Revisor asignado correctamente.";
    }
}

// Obtener todos los revisores con sus tópicos y artículos asignados
$sql = "
SELECT 
    u.rut,
    u.nombre,
    GROUP_CONCAT(DISTINCT t.nombre SEPARATOR ', ') AS topicos,
    COUNT(DISTINCT ar2.id_articulo) AS articulos_asignados,
    GROUP_CONCAT(DISTINCT a2.titulo SEPARATOR ', ') AS titulos_asignados
FROM Revisor r
JOIN Usuario u ON r.rut = u.rut
LEFT JOIN Revisor_Topico rt ON r.rut = rt.rut_revisor
LEFT JOIN Topico t ON rt.id_topico = t.id_topico
LEFT JOIN Articulo_Revisor ar2 ON r.rut = ar2.rut_revisor
LEFT JOIN Articulo a2 ON ar2.id_articulo = a2.id_articulo
GROUP BY u.rut, u.nombre
ORDER BY u.nombre
";
$stmt = $pdo->query($sql);
$revisores = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Asignar revisor a artículo</title>
    <link rel="stylesheet" href="../../css/estilos_articulos.css">
</head>
<body>
<a href="../asignar_articulos.php">Volver a artículos</a>
<h2>Asignar revisor a: <br> <?= htmlspecialchars($articulo['titulo']) ?></h2>
<p><strong>Tópicos del artículo:</strong><br>
<?= $articulo['topicos'] ? nl2br(htmlspecialchars(str_replace(', ', "\n", $articulo['topicos']))) : 'Sin tópicos'; ?>
</p>
<?php if ($msg): ?>
    <p style="color:green;"><?= htmlspecialchars($msg) ?></p>
<?php endif; ?>
<table border="1" cellpadding="6" style="border-collapse:collapse;width:100%">
    <tr style="background:#f2f2f2">
        <th>Nombre</th>
        <th>Tópicos</th>
        <th>Artículos asignados</th>
        <th>Acción</th>
    </tr>
    <?php
    // Normaliza los tópicos del artículo
    $topicos_articulo = array_filter(array_map('trim', preg_split('/,\s*/', $articulo['topicos'] ?? '')));

    foreach ($revisores as $revisor): 
        // Normaliza los tópicos del revisor
        $topicos_revisor = array_filter(array_map('trim', preg_split('/,\s*/', $revisor['topicos'] ?? '')));
        $hay_coincidencia = false;
        foreach ($topicos_revisor as $topico) {
            if ($topico !== '' && in_array($topico, $topicos_articulo, true)) {
                $hay_coincidencia = true;
                break;
            }
        }
        $clase_td = $hay_coincidencia ? 'coincide-topico-celda' : '';
    ?>
    <tr>
        <td><?= htmlspecialchars($revisor['nombre']) ?></td>
        <td class="<?= $clase_td ?>">
            <?php
            if ($topicos_revisor) {
                foreach ($topicos_revisor as $topico) {
                    if ($topico !== '' && in_array($topico, $topicos_articulo, true)) {
                        // Solo resalta el color y agrega subrayado, sin negrita
                        echo '<span class="topico-coincidente">' . htmlspecialchars($topico) . '</span><br>';
                    } elseif ($topico !== '') {
                        echo htmlspecialchars($topico) . '<br>';
                    }
                }
            } else {
                echo 'Sin tópicos';
            }
            ?>
        </td>
        <td>
            <?php
            if ($revisor['titulos_asignados']) {
                echo nl2br(htmlspecialchars(str_replace(', ', "\n", $revisor['titulos_asignados'])));
            } else {
                echo 'Sin artículos';
            }
            ?>
        </td>
        <td>
            <form method="post" style="margin:0;">
                <input type="hidden" name="rut_revisor" value="<?= htmlspecialchars($revisor['rut']) ?>">
                <button type="submit">Asignar</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
</body>
</html>