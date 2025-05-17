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
// Mostrar artículos con menos de dos revisores
$query_articulos = "SELECT a.id, a.titulo, COUNT(asg.revisor_id) AS num_revisores
                    FROM articulos a
                    LEFT JOIN asignaciones asg ON a.id = asg.articulo_id
                    GROUP BY a.id
                    HAVING num_revisores < 2";
$result_articulos = $conn->query($query_articulos);

$articulos_menos_revisores = [];
while ($articulo = $result_articulos->fetch_assoc()) {
    $articulos_menos_revisores[] = $articulo;
}

// Mostrar número de artículos asignados a cada revisor
$query_revisores = "SELECT r.id, r.nombre, COUNT(asg.articulo_id) AS num_articulos
                    FROM revisores r
                    LEFT JOIN asignaciones asg ON r.id = asg.revisor_id
                    GROUP BY r.id";
$result_revisores = $conn->query($query_revisores);

$revisores_asignaciones = [];
while ($revisor = $result_revisores->fetch_assoc()) {
    $revisores_asignaciones[] = $revisor;
}

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
    <br>
    <a href="asignar_articulos.php" style="text-decoration: none; color: #007BFF;">Volver a Asignar Artículos</a>
</body>
</html>
