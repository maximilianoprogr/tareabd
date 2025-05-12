<?php
require_once 'conexion.php';

// Obtener los datos de la tabla de asignaciones
$sql_asignaciones = "SELECT a.id_articulo, a.titulo, 
                      GROUP_CONCAT(DISTINCT CONCAT(u.nombre, ' (', u.rut, ')') SEPARATOR ', ') AS autores,
                      GROUP_CONCAT(DISTINCT t.nombre SEPARATOR ', ') AS topicos,
                      GROUP_CONCAT(DISTINCT r.nombre SEPARATOR ', ') AS revisores
                      FROM Articulo a
                      LEFT JOIN Autor_Articulo aa ON a.id_articulo = aa.id_articulo
                      LEFT JOIN Usuario u ON aa.rut_autor = u.rut
                      LEFT JOIN Articulo_Topico at ON a.id_articulo = at.id_articulo
                      LEFT JOIN Topico t ON at.id_topico = t.id_topico
                      LEFT JOIN Articulo_Revisor ar ON a.id_articulo = ar.id_articulo
                      LEFT JOIN Usuario r ON ar.rut_revisor = r.rut
                      GROUP BY a.id_articulo";
$stmt_asignaciones = $pdo->query($sql_asignaciones);
$asignaciones = $stmt_asignaciones->fetchAll();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tabla de Asignaciones</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            text-align: center;
            font-family: Arial, sans-serif;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
    </style>
</head>
<body>
    <h1>Tabla de Asignaciones</h1>
    <table>
        <tr>
            <th>Número</th>
            <th>Título</th>
            <th>Autores</th>
            <th>Tópicos</th>
            <th>Revisores</th>
        </tr>
        <?php foreach ($asignaciones as $asignacion): ?>
            <tr>
                <td><?= htmlspecialchars($asignacion['id_articulo']) ?></td>
                <td><?= htmlspecialchars($asignacion['titulo']) ?></td>
                <td><?= htmlspecialchars($asignacion['autores'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($asignacion['topicos'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($asignacion['revisores'] ?? 'N/A') ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <br>
    <a href="asignar_articulos.php" style="text-decoration: none; color: #007BFF;">Volver a Asignar Artículos</a>
</body>
</html>
