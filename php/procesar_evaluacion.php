<?php
session_start();

if (!isset($_SESSION['usuario']) || empty($_SESSION['usuario'])) {
    echo "<p style='font-family: Arial, sans-serif; color: red;'>Error: Usuario no autenticado.</p>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include('conexion.php');

    var_dump($_GET['revision']); 

    $calidad_tecnica = isset($_POST['calidad_tecnica']) ? 1 : 0;
    $originalidad = isset($_POST['originalidad']) ? 1 : 0;
    $valoracion_global = isset($_POST['valoracion_global']) ? 1 : 0;
    $argumentos_valoracion = htmlspecialchars($_POST['argumentos_valoracion']);
    $comentarios_autores = htmlspecialchars($_POST['comentarios_autores']);
    $revision = isset($_GET['revision']) ? htmlspecialchars($_GET['revision']) : null;

    if (empty($argumentos_valoracion)) {
        echo json_encode(["success" => false, "message" => "Los argumentos de valoración global son obligatorios."]);
        exit();
    }

    if (empty($comentarios_autores)) {
        echo json_encode(["success" => false, "message" => "Los comentarios a los autores son obligatorios."]);
        exit();
    }

    if (!$calidad_tecnica && !$originalidad && !$valoracion_global) {
        echo json_encode(["success" => false, "message" => "Debe seleccionar al menos una opción de evaluación."]);
        exit();
    }

    header('Content-Type: application/json');

    if ($revision) {
        try {
            $sql = "INSERT INTO Evaluacion_Articulo (id_articulo, rut_revisor, resena, calificacion) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$revision, $_SESSION['usuario'], $argumentos_valoracion, $valoracion_global]);

            echo json_encode(["success" => true, "message" => "Evaluación enviada correctamente."]);
        } catch (Exception $e) {
            error_log($e->getMessage(), 3, '../logs/errores.log');
            echo json_encode(["success" => false, "message" => "Error al enviar la evaluación. Por favor, inténtelo de nuevo más tarde."]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "No se especificó una revisión válida."]);
    }
} else {
    echo "<p style='font-family: Arial, sans-serif; color: red;'>Método de solicitud no válido.</p>";
}

?>
