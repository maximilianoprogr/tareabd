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

// Procesar quitar revisor si se envió el formulario
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rut_revisor']) && isset($_POST['quitar'])) {
    $rut_revisor = $_POST['rut_revisor'];
    $stmt = $pdo->prepare("DELETE FROM Articulo_Revisor WHERE id_articulo = ? AND rut_revisor = ?");
    $stmt->execute([$id_articulo, $rut_revisor]);
    $msg = "Revisor quitado correctamente.";
}

// Procesar asignación si se envió el formulario (y no es quitar)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rut_revisor']) && !isset($_POST['quitar'])) {
    $rut_revisor = $_POST['rut_revisor'];
    // Verificar si ya está asignado
    $stmt = $pdo->prepare("SELECT 1 FROM Articulo_Revisor WHERE id_articulo = ? AND rut_revisor = ?");
    $stmt->execute([$id_articulo, $rut_revisor]);
    if ($stmt->fetch()) {
        $msg = "El revisor ya está asignado a este artículo.";
    } else {
        // Obtener tópicos del artículo y del revisor
        $topicos_articulo = array_filter(array_map('trim', preg_split('/,\s*/', $articulo['topicos'] ?? '')));
        $stmt = $pdo->prepare("SELECT GROUP_CONCAT(DISTINCT t.nombre SEPARATOR ', ') AS topicos
                               FROM Revisor_Topico rt
                               LEFT JOIN Topico t ON rt.id_topico = t.id_topico
                               WHERE rt.rut_revisor = ?");
        $stmt->execute([$rut_revisor]);
        $row = $stmt->fetch();
        $topicos_revisor = array_filter(array_map('trim', preg_split('/,\s*/', $row['topicos'] ?? '')));
        $hay_coincidencia = false;
        foreach ($topicos_revisor as $topico) {
            if ($topico !== '' && in_array($topico, $topicos_articulo, true)) {
                $hay_coincidencia = true;
                break;
            }
        }
        // Asignar revisor
        $stmt = $pdo->prepare("INSERT INTO Articulo_Revisor (id_articulo, rut_revisor) VALUES (?, ?)");
        $stmt->execute([$id_articulo, $rut_revisor]);
        if (!$hay_coincidencia) {
            $msg = "Revisor asignado correctamente, pero este revisor NO tiene especialidad en el tópico del artículo.";
        } else {
            $msg = "Revisor asignado correctamente.";
        }
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

// Obtener los revisores ya asignados a este artículo
$stmt_asignados = $pdo->prepare("SELECT rut_revisor FROM Articulo_Revisor WHERE id_articulo = ?");
$stmt_asignados->execute([$id_articulo]);
$revisores_asignados = $stmt_asignados->fetchAll(PDO::FETCH_COLUMN);
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
<h2>Asignar revisor a: <br> <span style="font-weight:normal;"><?= htmlspecialchars($articulo['titulo']) ?></span></h2>
<p><strong>Tópicos del artículo:</strong><br>
<?= $articulo['topicos'] ? nl2br(htmlspecialchars(str_replace(', ', "\n", $articulo['topicos']))) : 'Sin tópicos'; ?>
</p>
<?php if ($msg): ?>
    <?php
    if (strpos($msg, 'NO tiene especialidad') !== false) {
        $clase = 'mensaje-especialidad';
    } elseif (strpos($msg, 'quitado correctamente') !== false) {
        $clase = 'mensaje-peligro';
    } elseif (strpos($msg, 'ya está asignado') !== false) {
        $clase = 'mensaje-advertencia';
    } else {
        $clase = 'mensaje-exito';
    }
    ?>
    <p class="<?= $clase ?>"><?= htmlspecialchars($msg) ?></p>
<?php endif; ?>
<table border="1" cellpadding="6" style="border-collapse:collapse;width:100%">
    <tr style="background:#f2f2f2">
        <th>Nombre</th>
        <th>Tópicos</th>
        <th>Artículos asignados</th>
        <th>Acción</th>
    </tr>
    <?php
    $topicos_articulo = array_filter(array_map('trim', preg_split('/,\s*/', $articulo['topicos'] ?? '')));

    foreach ($revisores as $revisor): 
        $topicos_revisor = array_filter(array_map('trim', preg_split('/,\s*/', $revisor['topicos'] ?? '')));
        $hay_coincidencia = false;
        foreach ($topicos_revisor as $topico) {
            if ($topico !== '' && in_array($topico, $topicos_articulo, true)) {
                $hay_coincidencia = true;
                break;
            }
        }
        $clase_td = $hay_coincidencia ? 'coincide-topico-celda' : '';
        $ya_asignado = in_array($revisor['rut'], $revisores_asignados);

        // Resalta en verde si está asignado y hay coincidencia, en amarillo si está asignado y NO hay coincidencia
        if ($ya_asignado && $hay_coincidencia) {
            $clase_tr = 'revisor-ya-asignado';
        } elseif ($ya_asignado && !$hay_coincidencia) {
            $clase_tr = 'revisor-ya-asignado-sin-coincidencia';
        } else {
            $clase_tr = '';
        }
    ?>
    <tr class="<?= $clase_tr ?>">
        <td>
            <?= htmlspecialchars($revisor['nombre']) ?>
        </td>
        <td class="<?= $clase_td ?>">
            <?php
            if ($topicos_revisor) {
                foreach ($topicos_revisor as $topico) {
                    if ($topico !== '' && in_array($topico, $topicos_articulo, true)) {
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
            <?php if ($ya_asignado): ?>
                <form method="post" style="margin:0;">
                    <input type="hidden" name="rut_revisor" value="<?= htmlspecialchars($revisor['rut']) ?>">
                    <input type="hidden" name="quitar" value="1">
                    <button type="submit" style="background:#dc3545;color:#fff;">Quitar</button>
                </form>
            <?php else: ?>
                <form method="post" style="margin:0;">
                    <input type="hidden" name="rut_revisor" value="<?= htmlspecialchars($revisor['rut']) ?>">
                    <button type="submit">Asignar</button>
                </form>
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
</body>
</html>