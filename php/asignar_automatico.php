<?php
include('../php/conexion.php');

// Log: Inicio del proceso de asignación automática
error_log("[INFO] Inicio del proceso de asignación automática de artículos a revisores.");

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

// Log: Finalización de la asignación
error_log("[INFO] Asignación automática completada.");

// Usar sentencias preparadas para resaltar artículos con menos de dos revisores
$sql_resaltar = "SELECT id_articulo, COUNT(rut_revisor) AS num_revisores
                 FROM Articulo_Revisor
                 GROUP BY id_articulo
                 HAVING num_revisores < 2";
$stmt_resaltar = $pdo->prepare($sql_resaltar);
$stmt_resaltar->execute();
$articulos_pendientes = $stmt_resaltar->fetchAll();

if (!empty($articulos_pendientes)) {
    echo "<h3>Artículos con menos de dos revisores:</h3><ul>";
    foreach ($articulos_pendientes as $articulo) {
        echo "<li>Artículo ID: " . htmlspecialchars($articulo['id_articulo']) . "</li>";
    }
    echo "</ul>";
    error_log("[WARNING] Hay artículos con menos de dos revisores.");
} else {
    echo "<p style='color: green;'>Todos los artículos tienen al menos dos revisores.</p>";
    error_log("[INFO] Todos los artículos tienen al menos dos revisores.");
}

// Log: Fin del proceso
error_log("[INFO] Proceso de asignación automática finalizado.");

echo "Asignación automática completada.";
?>
