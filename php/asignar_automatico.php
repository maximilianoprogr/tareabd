<?php
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener todos los artículos sin suficientes revisores
    $query = "SELECT a.id AS articulo_id FROM articulos a
              LEFT JOIN asignaciones asg ON a.id = asg.articulo_id
              GROUP BY a.id
              HAVING COUNT(asg.revisor_id) < 2";
    $result = $conn->query($query);

    while ($articulo = $result->fetch_assoc()) {
        $articulo_id = $articulo['articulo_id'];

        // Buscar revisores con tópicos coincidentes
        $query_revisores = "SELECT r.id AS revisor_id FROM revisores r
                            JOIN topicos_revisor tr ON r.id = tr.revisor_id
                            JOIN topicos_articulo ta ON ta.topico_id = tr.topico_id
                            WHERE ta.articulo_id = ? AND r.id NOT IN (
                                SELECT autor_id FROM autores WHERE articulo_id = ?
                            ) AND r.id NOT IN (
                                SELECT revisor_id FROM asignaciones WHERE articulo_id = ?
                            )
                            LIMIT 2";
        $stmt = $conn->prepare($query_revisores);
        $stmt->bind_param('iii', $articulo_id, $articulo_id, $articulo_id);
        $stmt->execute();
        $revisores = $stmt->get_result();

        while ($revisor = $revisores->fetch_assoc()) {
            $revisor_id = $revisor['revisor_id'];

            // Asignar automáticamente
            $query_asignar = "INSERT INTO asignaciones (articulo_id, revisor_id, tipo_asignacion) VALUES (?, ?, 'automatico')";
            $stmt_asignar = $conn->prepare($query_asignar);
            $stmt_asignar->bind_param('ii', $articulo_id, $revisor_id);
            $stmt_asignar->execute();
        }
    }

    echo "Asignaciones automáticas completadas.";
} else {
    echo "Método no permitido.";
}
?>
