<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include('../php/conexion.php'); // Asegúrate de que este archivo define correctamente $pdo

// Mensaje de depuración para verificar si el archivo se carga correctamente
echo "<p style='color: green;'>El archivo gestionar_revisores.php se ha cargado correctamente.</p>";

// Depuración: Verificar el rol del usuario en la sesión
if (isset($_SESSION['rol'])) {
    echo "<p style='color: blue;'>Rol actual en la sesión: " . htmlspecialchars($_SESSION['rol']) . "</p>";
} else {
    echo "<p style='color: red;'>Error: No se encontró el rol en la sesión.</p>";
}

// Asegurarse de que el rol 'Jefe Comite de Programa' sea reconocido correctamente
if (!isset($_SESSION['rol']) || ($_SESSION['rol'] !== 'admin' && $_SESSION['rol'] !== 'Jefe Comite de Programa')) {
    echo "<p style='color: red;'>Acceso denegado: Usuario no autorizado.</p>";
    header("Location: ../php/dashboard.php");
    exit();
}

// Depuración: Verificar si los campos del formulario están siendo enviados correctamente
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<p style='color: blue;'>Datos enviados desde el formulario:</p>";
    echo "<pre style='color: blue;'>" . print_r($_POST, true) . "</pre>";

    if (empty($_POST['email'])) {
        echo "<p style='color: red;'>El campo email está vacío.</p>";
    }
    if (empty($_POST['nombre'])) {
        echo "<p style='color: red;'>El campo nombre está vacío.</p>";
    }
}

// Crear, leer, actualizar y eliminar revisores
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $rut = $_POST['rut'] ?? null;
    $nombre = $_POST['nombre'] ?? null;
    $email = $_POST['email'] ?? null;

    // Depuración: Mostrar todos los valores enviados en el formulario
    echo "<pre style='color: blue;'>" . print_r($_POST, true) . "</pre>";

    // Depuración: Mostrar el valor recibido en el campo email
    echo "<p style='color: blue;'>Valor recibido en el campo email: " . htmlspecialchars($email) . "</p>";

    // Validar que el RUT no exceda los 10 caracteres
    if (strlen($rut) > 10) {
        echo "<p style='color: red;'>El RUT no puede exceder los 10 caracteres.</p>";
        exit();
    }

    // Mover las validaciones de email y nombre dentro de las acciones 'create' y 'update'
    if (($action === 'create' || $action === 'update') && $rut && $nombre && $email) {
        // Validar que el email tenga un formato válido
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo "<p style='color: red;'>El email no tiene un formato válido.</p>";
            exit();
        }

        // Validar que el nombre no exceda los límites permitidos
        if (strlen($nombre) > 255) {
            echo "<p style='color: red;'>El nombre no puede exceder los 255 caracteres.</p>";
            exit();
        }

        // Validar que el email no exceda los límites permitidos
        if (strlen($email) > 255) {
            echo "<p style='color: red;'>El email no puede exceder los 255 caracteres.</p>";
            exit();
        }
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

        // Insertar primero en la tabla Usuario
        try {
            $sql_usuario = "INSERT INTO Usuario (rut, nombre, email, usuario, password, tipo) VALUES (?, ?, ?, ?, ?, 'Revisor')";
            $stmt_usuario = $pdo->prepare($sql_usuario);
            $stmt_usuario->execute([$rut, $nombre, $email, $_POST['userid'], $_POST['password']]);
            echo "<p style='color: green;'>Usuario agregado exitosamente: RUT=$rut, Nombre=$nombre, Email=$email</p>";
        } catch (Exception $e) {
            echo "<p style='color: red;'>Error al agregar usuario: " . $e->getMessage() . "</p>";
            exit();
        }

        // Insertar en la tabla Revisor
        try {
            $sql_revisor = "INSERT INTO Revisor (rut) VALUES (?)";
            $stmt_revisor = $pdo->prepare($sql_revisor);
            $stmt_revisor->execute([$rut]);
            echo "<p style='color: green;'>Revisor agregado exitosamente: RUT=$rut</p>";
        } catch (Exception $e) {
            echo "<p style='color: red;'>Error al agregar revisor: " . $e->getMessage() . "</p>";
        }

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

        // Redirigir al usuario después de insertar los datos para actualizar la tabla
        echo "<script>window.location.href = 'gestionar_revisores.php';</script>";
    } elseif ($action === 'update' && $rut && $nombre && $email) {
        $sql = "UPDATE Revisor SET nombre = ?, email = ? WHERE rut = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombre, $email, $rut]);
        echo "<script>alert('Revisor actualizado exitosamente');</script>";
    } elseif ($action === 'delete' && $rut) {
        // Depuración: Verificar si la acción y el RUT se reciben correctamente
        echo "<p style='color: blue;'>Intentando eliminar revisor con RUT: $rut</p>";
        try {
            // Verificar si el revisor tiene artículos asignados
            $sql_check = "SELECT COUNT(*) FROM Articulo_Revisor WHERE rut_revisor = ?";
            $stmt_check = $pdo->prepare($sql_check);
            $stmt_check->execute([$rut]);
            $tiene_articulos = $stmt_check->fetchColumn() > 0;

            if ($tiene_articulos) {
                echo "<p style='color: red;'>No se puede eliminar un revisor con artículos asignados.</p>";
            } else {
                // Eliminar las evaluaciones relacionadas en evaluacion_articulo
                $sql_delete_evaluaciones = "DELETE FROM evaluacion_articulo WHERE rut_revisor = ?";
                $stmt_delete_evaluaciones = $pdo->prepare($sql_delete_evaluaciones);
                $stmt_delete_evaluaciones->execute([$rut]);

                // Depuración: Confirmar eliminación de evaluaciones
                echo "<p style='color: green;'>Evaluaciones del revisor eliminadas correctamente.</p>";

                // Eliminar el revisor de la tabla Revisor_Topico
                $sql_delete_topicos = "DELETE FROM Revisor_Topico WHERE rut_revisor = ?";
                $stmt_delete_topicos = $pdo->prepare($sql_delete_topicos);
                $stmt_delete_topicos->execute([$rut]);

                // Depuración: Confirmar eliminación de tópicos
                echo "<p style='color: green;'>Tópicos del revisor eliminados correctamente.</p>";

                // Eliminar el revisor de la tabla Revisor
                $sql_delete_revisor = "DELETE FROM Revisor WHERE rut = ?";
                $stmt_delete_revisor = $pdo->prepare($sql_delete_revisor);
                $stmt_delete_revisor->execute([$rut]);

                // Depuración: Confirmar eliminación de revisor
                echo "<p style='color: green;'>Revisor eliminado correctamente de la tabla Revisor.</p>";

                // Eliminar el usuario de la tabla Usuario
                $sql_delete_usuario = "DELETE FROM Usuario WHERE rut = ?";
                $stmt_delete_usuario = $pdo->prepare($sql_delete_usuario);
                $stmt_delete_usuario->execute([$rut]);

                // Depuración: Confirmar eliminación de usuario
                echo "<p style='color: green;'>Usuario eliminado correctamente de la tabla Usuario.</p>";
            }
        } catch (Exception $e) {
            echo "<p style='color: red;'>Error al eliminar revisor: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p style='color: red;'>No se recibió un RUT válido para eliminar.</p>";
    }
}

// Leer revisores
try {
    // Actualizar la consulta SQL para incluir los tópicos asociados a cada revisor
    $sql = "SELECT Usuario.rut, Usuario.nombre, Usuario.email, 
               GROUP_CONCAT(Topico.nombre SEPARATOR ', ') AS topicos
        FROM Revisor
        INNER JOIN Usuario ON Revisor.rut = Usuario.rut
        LEFT JOIN Revisor_Topico ON Revisor.rut = Revisor_Topico.rut_revisor
        LEFT JOIN Topico ON Revisor_Topico.id_topico = Topico.id_topico
        GROUP BY Usuario.rut, Usuario.nombre, Usuario.email";
    $stmt = $pdo->query($sql);
    $revisores = $stmt->fetchAll();

    // Depuración: Verificar si se obtuvieron datos
    if (!$revisores) {
        echo "<script>console.log('No se encontraron revisores en la base de datos');</script>";
    } else {
        echo "<script>console.log('Revisores obtenidos: " . json_encode($revisores) . "');</script>";
    }

    // Depuración: Mostrar resultados de la consulta SQL actualizada
    foreach ($revisores as $revisor) {
        echo '<p>Resultado de la consulta: ' . print_r($revisor, true) . '</p>';
    }
} catch (Exception $e) {
    // Depuración: Mostrar errores de la consulta
    echo "<script>console.error('Error al consultar revisores: " . $e->getMessage() . "');</script>";
    $revisores = [];
}

// Obtener todos los tópicos disponibles
$sql_topicos = "SELECT id_topico, nombre FROM Topico";
$stmt_topicos = $pdo->query($sql_topicos);
$topicos_disponibles = $stmt_topicos->fetchAll();
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

    <?php
    // Mostrar revisores en una tabla con opciones para editar y eliminar
    try {
        // La consulta correcta ya se ejecutó antes, no es necesario sobrescribir $revisores
        echo '<table style="width: 100%; border-collapse: collapse; font-size: 14px; margin-bottom: 20px;">';
        echo '<thead>';
        echo '<tr style="background-color: #f2f2f2;">';
        echo '<th style="border: 1px solid #ccc; padding: 8px;">Nombre</th>';
        echo '<th style="border: 1px solid #ccc; padding: 8px;">Email</th>';
        echo '<th style="border: 1px solid #ccc; padding: 8px;">Tópicos Especialista</th>';
        echo '<th style="border: 1px solid #ccc; padding: 8px;">Acciones</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        // Validar que las claves existan antes de usarlas
        foreach ($revisores as $revisor) {
            $nombre = $revisor['nombre'] ?? 'N/A';
            $email = $revisor['email'] ?? 'N/A';
            $topicos = $revisor['topicos'] ?? 'Sin tópicos';

            echo '<tr>';
            echo '<td style="border: 1px solid #ccc; padding: 8px;">' . htmlspecialchars($nombre) . '</td>';
            echo '<td style="border: 1px solid #ccc; padding: 8px;">' . htmlspecialchars($email) . '</td>';
            echo '<td style="border: 1px solid #ccc; padding: 8px;">';

            // Mostrar casillas de verificación para los tópicos
            foreach ($topicos_disponibles as $topico) {
                $checked = strpos($topicos, $topico['nombre']) !== false ? 'checked' : '';
                echo '<label style="display: block;">';
                echo '<input type="checkbox" name="topicos[]" value="' . $topico['id_topico'] . '" ' . $checked . '> ' . htmlspecialchars($topico['nombre']);
                echo '</label>';
            }

            echo '</td>';
            // Eliminar el botón de editar en la columna de acciones
            echo '<td style="border: 1px solid #ccc; padding: 8px;">';
            echo '<form method="POST" action="gestionar_revisores.php" style="display:inline;">';
            echo '<input type="hidden" name="action" value="delete">';
            echo '<input type="hidden" name="rut" value="' . htmlspecialchars($revisor['rut']) . '">';
            echo '<button type="submit" style="color: red; border: none; background: none; cursor: pointer;">Eliminar</button>';
            echo '</form>';
            echo '</td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';
    } catch (Exception $e) {
        echo '<p style="color: red;">Error al cargar los revisores: ' . $e->getMessage() . '</p>';
    }
    ?>

    <form method="GET" action="alta_revisor.php">
        <button type="submit" style="font-size: 14px; padding: 10px 20px; background-color: #4CAF50; color: white; border: none; cursor: pointer;">Alta</button>
    </form>

    <form id="nuevoRevisorForm" method="POST" action="gestionar_revisores.php" style="margin-top: 20px; border: 1px solid #ccc; padding: 15px; display: none;">
        <input type="hidden" name="action" value="create">
        <h2 style="font-size: 16px; color: #555;">Nuevo Revisor</h2>
        <label for="nombre" style="font-size: 14px; display: block; margin-bottom: 5px;">Nombre:</label>
        <input type="text" id="nombre" name="nombre" style="width: 100%; padding: 8px; margin-bottom: 10px;" required placeholder="Ingrese el nombre">
        <label for="email" style="font-size: 14px; display: block; margin-bottom: 5px;">Email:</label>
        <input type="email" id="email" name="email" style="width: 100%; padding: 8px; margin-bottom: 10px;" required placeholder="usuario@dominio.com">
        <label for="rut" style="font-size: 14px; display: block; margin-bottom: 5px;">RUT:</label>
        <input type="text" id="rut" name="rut" style="width: 100%; padding: 8px; margin-bottom: 10px;" required placeholder="Ingrese el RUT">
        <label for="userid" style="font-size: 14px; display: block; margin-bottom: 5px;">Usuario ID:</label>
        <input type="text" id="userid" name="userid" style="width: 100%; padding: 8px; margin-bottom: 10px;">
        <label for="password" style="font-size: 14px; display: block; margin-bottom: 5px;">Contraseña:</label>
        <input type="password" id="password" name="password" style="width: 100%; padding: 8px; margin-bottom: 10px;">
        <button type="submit" style="font-size: 14px; padding: 10px 20px; background-color: #4CAF50; color: white; border: none; cursor: pointer;">Guardar</button>
    </form>

    <a href="dashboard.php" style="font-family: Arial, sans-serif; font-size: 14px; color: #007BFF; text-decoration: none; display: block; margin-top: 20px;">Volver al inicio</a>

    <script>
    // Depuración: Verificar valores antes de enviar el formulario
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form[method="POST"]');
        if (form) {
            form.addEventListener('submit', function(event) {
                const email = document.getElementById('email').value;
                const nombre = document.getElementById('nombre').value;

                if (!email || !nombre) {
                    event.preventDefault();
                    alert('Por favor, complete todos los campos antes de enviar el formulario.');
                    console.log('Email:', email);
                    console.log('Nombre:', nombre);
                }
            });
        }
    });
    </script>

    <script>
    // Mostrar el formulario de "Nuevo Revisor" al hacer clic en "Alta"
    document.addEventListener('DOMContentLoaded', function() {
        const altaButton = document.querySelector('form[method="POST"] button[type="submit"]');
        const nuevoRevisorForm = document.getElementById('nuevoRevisorForm');

        if (altaButton && nuevoRevisorForm) {
            altaButton.addEventListener('click', function(event) {
                event.preventDefault();
                nuevoRevisorForm.style.display = 'block';
            });
        }
    });
    </script>
</body>
</html>
