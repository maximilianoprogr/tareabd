<?php
header('Content-Type: application/json');
include('conexion.php');

$data = json_decode(file_get_contents('php://input'), true);
$rut_revisor = $data['rut_revisor'] ?? null;
$id_topico = $data['id_topico'] ?? null;
$action = $data['action'] ?? null;

if (!$rut_revisor || !$id_topico || !$action) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
    exit;
}

try {
    if ($action === 'add') {
        $sql = "INSERT INTO Revisor_Topico (rut_revisor, id_topico) VALUES (?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$rut_revisor, $id_topico]);
        echo json_encode(['success' => true, 'message' => 'Tópico agregado correctamente.']);
    } elseif ($action === 'remove') {
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
