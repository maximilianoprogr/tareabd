<?php
// Función para asignar revisores automáticamente a los artículos
function asignar_revisores_automaticamente($pdo) {
    $asignados = 0; // Contador de revisores asignados

    // Consultar los artículos con menos de 2 revisores asignados
    $sql_articulos = "
        SELECT a.id_articulo, GROUP_CONCAT(DISTINCT at.id_topico) AS topicos
        FROM Articulo a
        LEFT JOIN Articulo_Revisor ar ON a.id_articulo = ar.id_articulo
        LEFT JOIN Articulo_Topico at ON a.id_articulo = at.id_articulo
        GROUP BY a.id_articulo
        HAVING COUNT(DISTINCT ar.rut_revisor) < 2
    ";
    $stmt = $pdo->query($sql_articulos);
    $articulos = $stmt->fetchAll(PDO::FETCH_ASSOC); // Obtener los artículos

    foreach ($articulos as $articulo) {
        $id_articulo = $articulo['id_articulo']; // ID del artículo
        $topicos_articulo = array_filter(array_map('intval', explode(',', $articulo['topicos']))); // Tópicos del artículo

        // Consultar los revisores ya asignados al artículo
        $stmt = $pdo->prepare("SELECT rut_revisor FROM Articulo_Revisor WHERE id_articulo = ?");
        $stmt->execute([$id_articulo]);
        $revisores_asignados = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if ($topicos_articulo) {
            // Consultar revisores con coincidencia de tópicos
            $in = implode(',', array_fill(0, count($topicos_articulo), '?'));
            $sql_revisores = "
                SELECT DISTINCT r.rut
                FROM Revisor r
                JOIN Revisor_Topico rt ON r.rut = rt.rut_revisor
                WHERE rt.id_topico IN ($in)
                AND r.rut NOT IN (" . (count($revisores_asignados) ? implode(',', array_fill(0, count($revisores_asignados), '?')) : "''") . ")
                LIMIT 2
            ";
            $params = array_merge($topicos_articulo, $revisores_asignados);
            $stmt = $pdo->prepare($sql_revisores);
            $stmt->execute($params);
            $revisores = $stmt->fetchAll(PDO::FETCH_COLUMN);
        } else {
            $revisores = []; // Si no hay tópicos, no hay coincidencias
        }

        // Consultar revisores adicionales si faltan para completar 2
        if (count($revisores_asignados) + count($revisores) < 2) {
            $faltan = 2 - (count($revisores_asignados) + count($revisores));
            $sql_revisores_extra = "
                SELECT DISTINCT r.rut
                FROM Revisor r
                WHERE r.rut NOT IN (" . (count($revisores_asignados) + count($revisores) ? implode(',', array_fill(0, count($revisores_asignados) + count($revisores), '?')) : "''") . ")
                LIMIT $faltan
            ";
            $params_extra = array_merge($revisores_asignados, $revisores);
            $stmt = $pdo->prepare($sql_revisores_extra);
            $stmt->execute($params_extra);
            $revisores_extra = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $revisores = array_merge($revisores, $revisores_extra);
        }

        // Asignar revisores al artículo
        foreach ($revisores as $rut_revisor) {
            $stmt = $pdo->prepare("INSERT IGNORE INTO Articulo_Revisor (id_articulo, rut_revisor) VALUES (?, ?)");
            $stmt->execute([$id_articulo, $rut_revisor]);
            $asignados++; // Incrementar el contador de asignaciones
        }
    }

    return $asignados; // Retornar el número total de asignaciones realizadas
}
?>