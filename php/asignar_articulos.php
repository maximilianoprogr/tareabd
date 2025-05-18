<?php
session_start();
include('../php/conexion.php');

// Consulta para obtener artículos con autores, tópicos y revisores (como arrays)
$sql = "
SELECT 
    a.id_articulo,
    a.titulo,
    GROUP_CONCAT(DISTINCT u.nombre SEPARATOR '|||') AS autores,
    GROUP_CONCAT(DISTINCT t.nombre SEPARATOR '|||') AS topicos,
    GROUP_CONCAT(DISTINCT r.nombre SEPARATOR '|||') AS revisores
FROM Articulo a
LEFT JOIN Autor_Articulo aa ON a.id_articulo = aa.id_articulo
LEFT JOIN Usuario u ON aa.rut_autor = u.rut
LEFT JOIN Articulo_Topico at ON a.id_articulo = at.id_articulo
LEFT JOIN Topico t ON at.id_topico = t.id_topico
LEFT JOIN Articulo_Revisor ar ON a.id_articulo = ar.id_articulo
LEFT JOIN Usuario r ON ar.rut_revisor = r.rut
GROUP BY a.id_articulo, a.titulo
ORDER BY a.id_articulo ASC
";

$stmt = $pdo->query($sql);
$articulos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de Artículos</title>
    <style>
        .subtabla { border-collapse: collapse; width: 100%; }
        .subtabla td { border: none; padding: 2px 8px; background: #f9f9f9; }
    </style>
</head>
<body>
<h2>Listado de Artículos</h2>
<table border="1" cellpadding="6" style="border-collapse:collapse;width:100%">
    <tr style="background:#f2f2f2">
        <th>N°</th>
        <th>Título</th>
        <th>Autores</th>
        <th>Tópicos</th>
        <th>Revisores</th>
    </tr>
    <?php foreach ($articulos as $articulo): ?>
    <tr>
        <td><?= htmlspecialchars($articulo['id_articulo']) ?></td>
        <td><?= htmlspecialchars($articulo['titulo']) ?></td>
        <td>
            <?php
            $autores = array_filter(explode('|||', $articulo['autores'] ?? ''));
            echo $autores ? nl2br(htmlspecialchars(implode("\n", $autores))) : 'Sin autores';
            ?>
        </td>
        <td>
            <?php
            $topicos = array_filter(explode('|||', $articulo['topicos'] ?? ''));
            echo $topicos ? nl2br(htmlspecialchars(implode("\n", $topicos))) : 'Sin tópicos';
            ?>
        </td>
        <td>
            <?php
            $revisores = array_filter(explode('|||', $articulo['revisores'] ?? ''));
            echo $revisores ? nl2br(htmlspecialchars(implode("\n", $revisores))) : 'Sin revisores';
            ?>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
</body>
</html>