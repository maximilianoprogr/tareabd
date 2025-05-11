<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include('../php/conexion.php'); // Asegúrate de que este archivo define correctamente $pdo

// Verificar permisos
if (!isset($_SESSION['usuario']) || $_SESSION['usuario'] !== 'admin') {
    header("Location: ../php/dashboard.php");
    exit();
}

// Crear, leer, actualizar y eliminar revisores
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $rut = $_POST['rut'] ?? null;
    $nombre = $_POST['nombre'] ?? null;
    $email = $_POST['email'] ?? null;

    // Validar que el RUT no exceda los 10 caracteres
    if (strlen($rut) > 10) {
        echo "<p style='color: red;'>El RUT no puede exceder los 10 caracteres.</p>";
        exit();
    }

    // Validar que el email tenga un formato válido
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<p style='color: red;'>El email no tiene un formato válido.</p>";
        exit();
    }

    if ($action === 'create' && $rut && $nombre && $email) {
        // Verificar duplicados por nombre y email
        $sql_duplicate = "SELECT COUNT(*) FROM Revisor WHERE nombre = ? OR email = ?";
        $stmt_duplicate = $pdo->prepare($sql_duplicate);
        $stmt_duplicate->execute([$nombre, $email]);
        if ($stmt_duplicate->fetchColumn() > 0) {
            echo "<p style='color: red;'>Ya existe un revisor con el mismo nombre o email.</p>";
            exit();
        }

        // Validar que el nombre y el email no excedan los límites permitidos
        if (strlen($nombre) > 255) {
            echo "<p style='color: red;'>El nombre no puede exceder los 255 caracteres.</p>";
            exit();
        }

        if (strlen($email) > 255) {
            echo "<p style='color: red;'>El email no puede exceder los 255 caracteres.</p>";
            exit();
        }

        // Usar sentencias preparadas para evitar inyecciones SQL
        $sql = "INSERT INTO Revisor (rut, nombre, email) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$rut, $nombre, $email]);

        // Asignar tópicos al revisor
        $topicos = $_POST['topicos'] ?? [];
        // Validar que no existan tópicos duplicados al asignar a un revisor
        if (count($topicos) !== count(array_unique($topicos))) {
            echo "<p style='color: red;'>No se permiten tópicos duplicados para un revisor.</p>";
            exit();
        }

        if (!empty($topicos)) {
            $sql_topicos = "INSERT INTO Revisor_Topico (rut_revisor, id_topico) VALUES (?, ?)";
            $stmt_topicos = $pdo->prepare($sql_topicos);
            foreach ($topicos as $id_topico) {
                $stmt_topicos->execute([$rut, $id_topico]);
            }
        } else {
            echo "<script>alert('Debe asignar al menos un tópico al revisor');</script>";
        }
        echo "<script>alert('Revisor agregado exitosamente');</script>";

        // Enviar notificación por email
        mail($email, "Bienvenido como Revisor", "Hola $nombre, has sido registrado como revisor en el sistema.");
    } elseif ($action === 'update' && $rut && $nombre && $email) {
        // Validar que el nombre y el email no excedan los límites permitidos
        if (strlen($nombre) > 255) {
            echo "<p style='color: red;'>El nombre no puede exceder los 255 caracteres.</p>";
            exit();
        }

        if (strlen($email) > 255) {
            echo "<p style='color: red;'>El email no puede exceder los 255 caracteres.</p>";
            exit();
        }

        $sql = "UPDATE Revisor SET nombre = ?, email = ? WHERE rut = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombre, $email, $rut]);
        echo "<script>alert('Revisor actualizado exitosamente');</script>";
    } elseif ($action === 'delete' && $rut) {
        $sql_check = "SELECT COUNT(*) FROM Articulo_Revisor WHERE rut_revisor = ?";
        $stmt_check = $pdo->prepare($sql_check);
        $stmt_check->execute([$rut]);
        $tiene_articulos = $stmt_check->fetchColumn() > 0;

        if ($tiene_articulos) {
            echo "<p style='color: red;'>No se puede eliminar un revisor con artículos asignados.</p>";
        } else {
            // Enviar notificación por email
            $sql_email = "SELECT email FROM Revisor WHERE rut = ?";
            $stmt_email = $pdo->prepare($sql_email);
            $stmt_email->execute([$rut]);
            $email = $stmt_email->fetchColumn();
            if ($email) {
                mail($email, "Eliminación como Revisor", "Hola, has sido eliminado como revisor del sistema.");
            }

            $sql = "DELETE FROM Revisor WHERE rut = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$rut]);
            echo "<p style='color: green;'>Revisor eliminado exitosamente.</p>";
        }
    }
}

// Leer revisores
try {
    $sql = "SELECT * FROM Revisor";
    $stmt = $pdo->query($sql);
    $revisores = $stmt->fetchAll();

    // Depuración: Verificar si se obtuvieron datos
    if (!$revisores) {
        echo "<script>console.log('No se encontraron revisores en la base de datos');</script>";
    } else {
        echo "<script>console.log('Revisores obtenidos: " . json_encode($revisores) . "');</script>";
    }
} catch (Exception $e) {
    // Depuración: Mostrar errores de la consulta
    echo "<script>console.error('Error al consultar revisores: " . $e->getMessage() . "');</script>";
    $revisores = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Revisores</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body style="font-family: Arial, sans-serif; margin: 20px;">
    <h1 style="font-size: 18px; color: #333;">Gestión de Revisores</h1>

    <table style="width: 100%; border-collapse: collapse; font-size: 14px; margin-bottom: 20px;">
        <thead>
            <tr style="background-color: #f2f2f2;">
                <th style="border: 1px solid #ccc; padding: 8px;">Miembro Nombre</th>
                <th style="border: 1px solid #ccc; padding: 8px;">Email</th>
                <th style="border: 1px solid #ccc; padding: 8px;">Tópicos de Especialidad</th>
                <th style="border: 1px solid #ccc; padding: 8px;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="border: 1px solid #ccc; padding: 8px;">Nombre 1</td>
                <td style="border: 1px solid #ccc; padding: 8px;">email1@example.com</td>
                <td style="border: 1px solid #ccc; padding: 8px;">
                    <input type="checkbox" id="t1" name="t1"> T1
                    <input type="checkbox" id="t2" name="t2"> T2
                    <input type="checkbox" id="t3" name="t3"> T3
                </td>
                <td style="border: 1px solid #ccc; padding: 8px;">
                    <button style="font-size: 12px; padding: 5px 10px; background-color: #FF4C4C; color: white; border: none; cursor: pointer;">Quitar</button>
                </td>
            </tr>
            <!-- Más filas pueden ser añadidas dinámicamente -->
        </tbody>
    </table>

    <button style="font-size: 14px; padding: 10px 20px; background-color: #4CAF50; color: white; border: none; cursor: pointer;">Alta</button>

    <form style="margin-top: 20px; border: 1px solid #ccc; padding: 15px;">
        <h2 style="font-size: 16px; color: #555;">Nuevo Revisor</h2>
        <label for="nombre" style="font-size: 14px; display: block; margin-bottom: 5px;">Nombre:</label>
        <input type="text" id="nombre" name="nombre" style="width: 100%; padding: 8px; margin-bottom: 10px;">

        <label for="email" style="font-size: 14px; display: block; margin-bottom: 5px;">Email:</label>
        <input type="email" id="email" name="email" style="width: 100%; padding: 8px; margin-bottom: 10px;">

        <label for="userid" style="font-size: 14px; display: block; margin-bottom: 5px;">Usuario ID:</label>
        <input type="text" id="userid" name="userid" style="width: 100%; padding: 8px; margin-bottom: 10px;">

        <label for="password" style="font-size: 14px; display: block; margin-bottom: 5px;">Contraseña:</label>
        <input type="password" id="password" name="password" style="width: 100%; padding: 8px; margin-bottom: 10px;">

        <button type="submit" style="font-size: 14px; padding: 10px 20px; background-color: #4CAF50; color: white; border: none; cursor: pointer;">Guardar</button>
    </form>

    <a href="dashboard.php" style="font-family: Arial, sans-serif; font-size: 14px; color: #007BFF; text-decoration: none; display: block; margin-top: 20px;">Volver al inicio</a>
</body>
</html>
