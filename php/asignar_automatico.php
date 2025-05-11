<?php
include('../php/conexion.php');

// Asignar automáticamente artículos a revisores basados en tópicos
$sql = "INSERT INTO Articulo_Revisor (id_articulo, rut_revisor)
        SELECT at.id_articulo, rt.rut_revisor
        FROM Articulo_Topico at
        JOIN Revisor_Topico rt ON at.id_topico = rt.id_topico
        WHERE NOT EXISTS (
            SELECT 1 FROM Articulo_Revisor ar
            WHERE ar.id_articulo = at.id_articulo AND ar.rut_revisor = rt.rut_revisor
        )";
$pdo->exec($sql);

// Resaltar artículos con menos de dos revisores
$sql_resaltar = "SELECT id_articulo, COUNT(rut_revisor) AS num_revisores
                 FROM Articulo_Revisor
                 GROUP BY id_articulo
                 HAVING num_revisores < 2";
$stmt_resaltar = $pdo->query($sql_resaltar);
$articulos_pendientes = $stmt_resaltar->fetchAll();

if (!empty($articulos_pendientes)) {
    echo "<h3>Artículos con menos de dos revisores:</h3><ul>";
    foreach ($articulos_pendientes as $articulo) {
        echo "<li>Artículo ID: " . htmlspecialchars($articulo['id_articulo']) . "</li>";
    }
    echo "</ul>";
} else {
    echo "Todos los artículos tienen al menos dos revisores.";
}

echo "Asignación automática completada.";
?>
