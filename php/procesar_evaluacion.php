<?php
// Inicia la sesión para manejar autenticación de usuarios
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario']) || empty($_SESSION['usuario'])) {
    echo "<p style='font-family: Arial, sans-serif; color: red;'>Error: Usuario no autenticado.</p>";
    exit(); // Finalizar la ejecución del script
}

// Verificar si el formulario fue enviado mediante POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include('conexion.php'); // Incluir el archivo de conexión a la base de datos

    var_dump($_GET['revision']); // Depuración: Mostrar el valor de la revisión

    // Obtener los valores enviados desde el formulario
    $calidad_tecnica = isset($_POST['calidad_tecnica']) ? 1 : 0;
    $originalidad = isset($_POST['originalidad']) ? 1 : 0;
    $valoracion_global = isset($_POST['valoracion_global']) ? 1 : 0;
    $argumentos_valoracion = htmlspecialchars($_POST['argumentos_valoracion']);
    $comentarios_autores = htmlspecialchars($_POST['comentarios_autores']);
    $revision = isset($_GET['revision']) ? htmlspecialchars($_GET['revision']) : null;

    // Validar que los argumentos de valoración no estén vacíos
    if (empty($argumentos_valoracion)) {
        echo json_encode(["success" => false, "message" => "Los argumentos de valoración global son obligatorios."]);
        exit();
    }

    // Validar que los comentarios a los autores no estén vacíos
    if (empty($comentarios_autores)) {
        echo json_encode(["success" => false, "message" => "Los comentarios a los autores son obligatorios."]);
        exit();
    }

    // Verificar que al menos una opción de evaluación esté seleccionada
    if (!$calidad_tecnica && !$originalidad && !$valoracion_global) {
        echo json_encode(["success" => false, "message" => "Debe seleccionar al menos una opción de evaluación."]);
        exit();
    }

    header('Content-Type: application/json'); // Establecer el tipo de contenido a JSON

    // Si hay una revisión válida, proceder a guardar la evaluación
    if ($revision) {
        try {
            // Preparar la consulta SQL para insertar la evaluación en la base de datos
            $sql = "INSERT INTO Evaluacion_Articulo (id_articulo, rut_revisor, resena, calificacion) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            // Ejecutar la consulta con los parámetros correspondientes
            $stmt->execute([$revision, $_SESSION['usuario'], $argumentos_valoracion, $valoracion_global]);

            // Devolver respuesta exitosa en formato JSON
            echo json_encode(["success" => true, "message" => "Evaluación enviada correctamente."]);
        } catch (Exception $e) {
            // Registrar el error en el archivo de log
            error_log($e->getMessage(), 3, '../logs/errores.log');
            // Devolver mensaje de error en formato JSON
            echo json_encode(["success" => false, "message" => "Error al enviar la evaluación. Por favor, inténtelo de nuevo más tarde."]);
        }
    } else {
        // Devolver mensaje de error si no se especifica una revisión válida
        echo json_encode(["success" => false, "message" => "No se especificó una revisión válida."]);
    }
} else {
    // Mensaje de error si el método de solicitud no es válido
    echo "<p style='font-family: Arial, sans-serif; color: red;'>Método de solicitud no válido.</p>";
}

?>
