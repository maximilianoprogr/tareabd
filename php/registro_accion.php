<?php
// Incluir el archivo de conexión a la base de datos
include('../php/conexion.php');

// Verificar si el formulario fue enviado mediante POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $rut = trim($_POST['rut']); // Obtener y limpiar el RUT del formulario
    $password = trim($_POST['password']); // Obtener y limpiar la contraseña del formulario

    // Validar que el RUT y la contraseña no estén vacíos
    if (empty($rut) || empty($password)) {
        echo "El RUT y la contraseña son obligatorios.";
        exit(); // Finalizar la ejecución del script
    }

    // Validar que el RUT tenga un formato válido
    if (!preg_match('/^[0-9]+[kK0-9]$/', $rut)) {
        echo "El RUT no tiene un formato válido.";
        exit(); // Finalizar la ejecución del script
    }

    // Consulta para insertar un nuevo usuario en la base de datos
    $sql = "INSERT INTO Usuario (rut, password) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("ss", $rut, $password); // Vincular parámetros a la consulta

        // Ejecutar la consulta y verificar si tuvo éxito
        if ($stmt->execute()) {
            echo "Usuario registrado exitosamente";
        } else {
            echo "Error al registrar usuario: " . $stmt->error;
        }

        $stmt->close(); // Cerrar la declaración
    } else {
        echo "Error al preparar la consulta: " . $conn->error;
    }
}

$conn->close(); // Cerrar la conexión a la base de datos
?>
