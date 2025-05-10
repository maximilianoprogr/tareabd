<?php
include('conexion.php');

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

echo "Asignación automática completada.";
?>
