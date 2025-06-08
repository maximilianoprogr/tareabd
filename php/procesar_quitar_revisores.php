<?php
// Configurar el tipo de contenido de la respuesta como JSON
header('Content-Type: application/json');

// Incluir el archivo de conexión a la base de datos
include('conexion.php');

// Obtener los datos enviados en el cuerpo de la solicitud
$data = json_decode(file_get_contents('php://input'), true);
$rut_revisor = $data['rut_revisor'] ?? null; // RUT del revisor
$id_topico = $data['id_topico'] ?? null; // ID del tópico
$action = $data['action'] ?? null; // Acción a realizar (agregar o eliminar)

// Validar que todos los datos requeridos estén presentes
if (!$rut_revisor || !$id_topico || !$action) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
    exit(); // Finalizar la ejecución del script
}

try {
    if ($action === 'add') {
        // Consulta para agregar un tópico al revisor
        $sql = "INSERT INTO Revisor_Topico (rut_revisor, id_topico) VALUES (?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$rut_revisor, $id_topico]);
        echo json_encode(['success' => true, 'message' => 'Tópico agregado correctamente.']);
    } elseif ($action === 'remove') {
        // Consulta para eliminar un tópico del revisor
        $sql = "DELETE FROM Revisor_Topico WHERE rut_revisor = ? AND id_topico = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$rut_revisor, $id_topico]);
        echo json_encode(['success' => true, 'message' => 'Tópico eliminado correctamente.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Acción no válida.']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
