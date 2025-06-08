<?php
// Habilitar la visualización de errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar sesión para manejar autenticación de usuarios
session_start();

// Verificar si el usuario ha iniciado sesión, de lo contrario redirigir a la página de login
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php"); // Redirige al login si no hay sesión activa
    exit();
}

// Incluir archivo de conexión a la base de datos
include('../php/conexion.php');

// Obtener el rol del usuario actual desde la base de datos
$stmt_rol = $pdo->prepare("SELECT tipo FROM Usuario WHERE rut = ?");
$stmt_rol->execute([$_SESSION['usuario']]);
$rol = $stmt_rol->fetchColumn();
$_SESSION['rol'] = $rol; // Guardar el rol en la sesión

// Verificar si el usuario tiene el rol adecuado para acceder a esta página
if (strcasecmp($rol, 'Jefe Comite de Programa') !== 0) {
    header("Location: inicio.php"); // Redirige al inicio si el rol no es válido
    exit();
}

// Redirigir si el rol no está configurado correctamente
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Jefe Comite de Programa') {
    header("Location: ../php/inicio.php"); // Redirige al inicio si no se cumple la condición
    exit();
}

// Variable para almacenar mensajes de error o éxito
$mensaje = "";

// Manejar solicitudes POST para crear, actualizar o eliminar revisores
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? ''; // Acción enviada desde el formulario
    $rut = $_POST['rut'] ?? null; // RUT del revisor
    $nombre = $_POST['nombre'] ?? null; // Nombre del revisor
    $email = $_POST['email'] ?? null; // Email del revisor
    $topicos = $_POST['topicos'] ?? []; // Tópicos seleccionados

    // Validaciones comunes para las acciones de creación y actualización
    if (in_array($action, ['create', 'update'])) {
        if (empty($nombre) || empty($email) || empty($rut)) {
            $mensaje = "<p style='color: red;'>Faltan campos obligatorios en el formulario.</p>";
        } elseif (empty($topicos) || !is_array($topicos)) {
            $mensaje = "<p style='color: red;'>Debe seleccionar al menos un tópico.</p>";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $mensaje = "<p style='color: red;'>El email no tiene un formato válido.</p>";
        } elseif (strlen($nombre) > 255) {
            $mensaje = "<p style='color: red;'>El nombre no puede exceder los 255 caracteres.</p>";
        } elseif (strlen($email) > 255) {
            $mensaje = "<p style='color: red;'>El email no puede exceder los 255 caracteres.</p>";
        } elseif (strlen($rut) > 10) {
            $mensaje = "<p style='color: red;'>El RUT no puede exceder los 10 caracteres.</p>";
        }
    }

    // Procesar acciones específicas si no hay errores
    if ($mensaje === "") {
        if ($action === 'delete') {
            // Eliminar un revisor si no tiene artículos asignados
            if (!$rut) {
                $mensaje = "<p style='color: red;'>No se recibió un RUT válido para eliminar.</p>";
            } else {
                try {
                    $sql_check = "SELECT COUNT(*) FROM Articulo_Revisor WHERE rut_revisor = ?";
                    $stmt_check = $pdo->prepare($sql_check);
                    $stmt_check->execute([$rut]);
                    $tiene_articulos = $stmt_check->fetchColumn() > 0;

                    if ($tiene_articulos) {
                        // Si el revisor tiene artículos asignados, no se puede eliminar
                        $mensaje = "<p style='color: red;'>No se puede eliminar el revisor porque tiene artículos asignados.</p>";
                    } else {
                        // Eliminar registros relacionados en varias tablas
                        // Primero se eliminan los tópicos asociados al revisor
                        $pdo->prepare("DELETE FROM Revisor_Topico WHERE rut_revisor = ?")->execute([$rut]);
                        // Luego se elimina el revisor de la tabla Revisor
                        $pdo->prepare("DELETE FROM Revisor WHERE rut = ?")->execute([$rut]);
                        // Finalmente se elimina el usuario de la tabla Usuario
                        $pdo->prepare("DELETE FROM Usuario WHERE rut = ?")->execute([$rut]);
                        $mensaje = "<p style='color: green;'>Revisor eliminado exitosamente.</p>";
                    }
                // Manejo de errores al intentar eliminar un revisor
                } catch (Exception $e) {
                    $mensaje = "<p style='color: red;'>Error al eliminar revisor: " . $e->getMessage() . "</p>";
                }
            }
        } elseif ($action === 'create') {
            // Crear un nuevo revisor
            // Verificar si ya existe un usuario con el mismo nombre o email
            $sql_duplicate = "SELECT COUNT(*) FROM Usuario WHERE nombre = ? OR email = ?";
            $stmt_duplicate = $pdo->prepare($sql_duplicate);
            $stmt_duplicate->execute([$nombre, $email]);
            if ($stmt_duplicate->fetchColumn() > 0) {
                $mensaje = "<p style='color: red;'>Ya existe un usuario con el mismo nombre o email.</p>";
            } else {
                try {
                    $userid = $_POST['userid'] ?? ''; // ID de usuario único para el sistema
                    $password = $_POST['password'] ?? ''; // Contraseña del usuario
                    if (!$userid || !$password) {
                        // Validar que el ID de usuario y la contraseña no estén vacíos
                        $mensaje = "<p style='color: red;'>Usuario ID y contraseña son obligatorios.</p>";
                    } else {
                        // Insertar datos del nuevo usuario en la tabla Usuario
                        $pdo->prepare("INSERT INTO Usuario (rut, nombre, email, usuario, password, tipo) VALUES (?, ?, ?, ?, ?, 'Revisor')")
                            ->execute([$rut, $nombre, $email, $userid, $password]);
                        // Insertar el RUT del revisor en la tabla Revisor
                        $pdo->prepare("INSERT INTO Revisor (rut) VALUES (?)")->execute([$rut]);
                        if (count($topicos) !== count(array_unique($topicos))) {
                            // Validar que no haya tópicos duplicados seleccionados
                            $mensaje = "<p style='color: red;'>No se permiten tópicos duplicados para un revisor.</p>";
                        } else {
                            foreach ($topicos as $id_topico) {
                                // Validar que cada tópico seleccionado exista en la base de datos
                                $sql_validar_topico = "SELECT COUNT(*) FROM Topico WHERE id_topico = ?";
                                $stmt_validar_topico = $pdo->prepare($sql_validar_topico);
                                $stmt_validar_topico->execute([$id_topico]);
                                if ($stmt_validar_topico->fetchColumn() == 0) {
                                    $mensaje = "<p style='color: red;'>El tópico con ID $id_topico no es válido.</p>";
                                    break;
                                }
                            }
                            if ($mensaje === "") {
                                // Asociar los tópicos seleccionados al revisor en la tabla Revisor_Topico
                                $stmt_topicos = $pdo->prepare("INSERT INTO Revisor_Topico (rut_revisor, id_topico) VALUES (?, ?)");
                                foreach ($topicos as $id_topico) {
                                    $stmt_topicos->execute([$rut, $id_topico]);
                                }
                                $mensaje = "<p style='color: green;'>Revisor creado exitosamente.</p>";
                            }
                        }
                    }
                // Manejo de errores al intentar crear un nuevo revisor
                } catch (Exception $e) {
                    $mensaje = "<p style='color: red;'>Error al crear revisor: " . $e->getMessage() . "</p>";
                }
            }
        } elseif ($action === 'update') {
            // Actualizar datos de un revisor existente
            $sql_duplicate = "SELECT COUNT(*) FROM Usuario WHERE (nombre = ? OR email = ?) AND rut != ?";
            $stmt_duplicate = $pdo->prepare($sql_duplicate);
            $stmt_duplicate->execute([$nombre, $email, $rut]);
            if ($stmt_duplicate->fetchColumn() > 0) {
                $mensaje = "<p style='color: red;'>Ya existe un usuario con el mismo nombre o email.</p>";
            } else {
                try {
                    $pdo->prepare("UPDATE Usuario SET nombre = ?, email = ? WHERE rut = ?")
                        ->execute([$nombre, $email, $rut]);
                    $mensaje = "<p style='color: green;'>Revisor actualizado exitosamente.</p>";
                } catch (Exception $e) {
                    $mensaje = "<p style='color: red;'>Error al actualizar revisor: " . $e->getMessage() . "</p>";
                }
            }
        }
    }
}

// Consultar revisores y sus tópicos asociados para mostrarlos en la tabla
try {
    $sql = "SELECT Usuario.rut, Usuario.nombre, Usuario.email, 
               COALESCE(GROUP_CONCAT(Topico.nombre SEPARATOR ', '), 'Sin tópicos') AS topicos
        FROM Revisor
        INNER JOIN Usuario ON Revisor.rut = Usuario.rut
        LEFT JOIN Revisor_Topico ON Revisor.rut = Revisor_Topico.rut_revisor
        LEFT JOIN Topico ON Revisor_Topico.id_topico = Topico.id_topico
        GROUP BY Usuario.rut, Usuario.nombre, Usuario.email";
    $stmt = $pdo->query($sql);
    $revisores = $stmt->fetchAll(); // Obtener todos los revisores y sus tópicos
} catch (Exception $e) {
    $revisores = []; // En caso de error, inicializar como vacío
}

// Consultar tópicos disponibles para asignar a revisores
$sql_topicos = "SELECT id_topico, nombre FROM Topico";
$stmt_topicos = $pdo->query($sql_topicos);
$topicos_disponibles = $stmt_topicos->fetchAll(); // Obtener todos los tópicos
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device=width, initial-scale=1.0">
    <title>Gestionar Revisores</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body style="font-family: Arial, sans-serif; margin: 20px;">
    <h1 style="font-size: 18px; color: #333;">Gestión de Revisores</h1>

    <?php if ($mensaje) echo $mensaje; ?>

    <?php
    echo '<table style="width: 100%; border-collapse: collapse; font-size: 14px; margin-bottom: 20px;">';
    echo '<thead>';
    echo '<tr style="background-color: #f2f2f2;">';
    // Encabezados de la tabla
    echo '<th style="border: 1px solid #ccc; padding: 8px;">Nombre</th>';
    echo '<th style="border: 1px solid #ccc; padding: 8px;">Email</th>';
    echo '<th style="border: 1px solid #ccc; padding: 8px;">Tópicos Especialista</th>';
    echo '<th style="border: 1px solid #ccc; padding: 8px;">Acciones</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    // Iterar sobre los revisores obtenidos de la base de datos
    foreach ($revisores as $revisor) {
        $nombre = $revisor['nombre'] ?? 'N/A'; // Asigna el nombre del revisor o 'N/A' si no está disponible
        $email = $revisor['email'] ?? 'N/A'; // Asigna el email del revisor o 'N/A' si no está disponible
        $topicos = $revisor['topicos'] ?? 'Sin tópicos'; // Asigna los tópicos asociados al revisor o 'Sin tópicos' si no hay

        // Crear una fila en la tabla HTML para cada revisor
        echo '<tr>';
        echo '<td style="border: 1px solid #ccc; padding: 8px;">' . htmlspecialchars($nombre) . '</td>'; // Muestra el nombre del revisor en una celda
        echo '<td style="border: 1px solid #ccc; padding: 8px;">' . htmlspecialchars($email) . '</td>'; // Muestra el email del revisor en una celda
        echo '<td style="border: 1px solid #ccc; padding: 8px;">'; // Abre una celda para los tópicos

        // Mostrar los tópicos disponibles with checkboxes
        foreach ($topicos_disponibles as $topico) {
            $checked = strpos($topicos, $topico['nombre']) !== false ? 'checked' : ''; // Verifica si el tópico está asociado al revisor
            echo '<label style="display: block;">';
            echo '<input type="checkbox" name="topicos[]" value="' . $topico['id_topico'] . '" ' . $checked . ' data-rut-revisor="' . htmlspecialchars($revisor['rut']) . '" data-id-topico="' . htmlspecialchars($topico['id_topico']) . '"> ' . htmlspecialchars($topico['nombre']); // Checkbox para cada tópico
            echo '</label>';
        }

        echo '</td>'; // Cierra la celda de los tópicos
        echo '<td style="border: 1px solid #ccc; padding: 8px;">'; // Celda para las acciones

        // Formulario para eliminar un revisor
        echo '<form method="POST" action="gestionar_revisores.php" style="display:inline;">';
        echo '<input type="hidden" name="action" value="delete">'; // Acción de eliminar
        echo '<input type="hidden" name="rut" value="' . htmlspecialchars($revisor['rut']) . '">'; // RUT del revisor a eliminar
        echo '<button type="submit" style="color: red; border: none; background: none; cursor: pointer;">Eliminar</button>'; // Botón de eliminar
        echo '</form>';
        echo '</td>'; // Cierra la celda de acciones
        echo '</tr>'; // Cierra la fila del revisor
    }

    echo '</tbody>'; // Cierra el cuerpo de la tabla
    echo '</table>'; // Cierra la tabla

    // Formulario para agregar un nuevo revisor
    echo '<form id="nuevoRevisorForm" method="POST" action="gestionar_revisores.php" style="margin-top: 20px; border: 1px solid #ccc; padding: 15px; display: block;">';
    echo '<input type="hidden" name="action" value="create">'; // Acción de crear
    echo '<h2 style="font-size: 16px; color: #555;">Nuevo Revisor</h2>';
    echo '<label for="nombre" style="font-size: 14px; display: block; margin-bottom: 5px;">Nombre:</label>';
    echo '<input type="text" id="nombre" name="nombre" style="width: 100%; padding: 8px; margin-bottom: 10px;" required placeholder="Ingrese el nombre">';
    echo '<label for="email" style="font-size: 14px; display: block; margin-bottom: 5px;">Email:</label>';
    echo '<input type="email" id="email" name="email" style="width: 100%; padding: 8px; margin-bottom: 10px;" required placeholder="usuario@dominio.com">';
    echo '<label for="rut" style="font-size: 14px; display: block; margin-bottom: 5px;">RUT:</label>';
    echo '<input type="text" id="rut" name="rut" style="width: 100%; padding: 8px; margin-bottom: 10px;" required placeholder="Ingrese el RUT" value="">';
    echo '<label for="userid" style="font-size: 14px; display: block; margin-bottom: 5px;">Usuario ID:</label>';
    echo '<input type="text" id="userid" name="userid" style="width: 100%; padding: 8px; margin-bottom: 10px;">';
    echo '<label for="password" style="font-size: 14px; display: block; margin-bottom: 5px;">Contraseña:</label>';
    echo '<input type="password" id="password" name="password" style="width: 100%; padding: 8px; margin-bottom: 10px;">';

    // Mostrar los tópicos disponibles para seleccionar
    echo '<label style="font-size: 14px; display: block; margin-bottom: 5px;">Tópicos:</label>';
    echo '<div style="margin-bottom: 10px;">';
    foreach ($topicos_disponibles as $topico) {
        echo '<label style="display: block;">';
        echo '<input type="checkbox" name="topicos[]" value="' . htmlspecialchars($topico['id_topico']) . '">';
        echo htmlspecialchars($topico['nombre']);
        echo '</label>';
    }
    echo '</div>';

    echo '<button type="submit" id="guardarBtn" style="font-size: 14px; padding: 10px 20px; background-color: #4CAF50; color: white; border: none; cursor: pointer;">Guardar</button>';
    echo '</form>'; // Cierra el formulario de nuevo revisor

    echo '<a href="dashboard.php" style="font-family: Arial, sans-serif; font-size: 14px; color: #007BFF; text-decoration: none; display: block; margin-top: 20px;">Volver al inicio</a>'; // Enlace para volver al dashboard
    ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('nuevoRevisorForm');
        if (form) {
            // Validar el formulario antes de enviarlo
            form.addEventListener('submit', function(event) {
                const nombre = document.getElementById('nombre').value.trim(); // Obtener el valor del campo nombre
                const email = document.getElementById('email').value.trim(); // Obtener el valor del campo email
                const rut = document.getElementById('rut').value.trim(); // Obtener el valor del campo RUT
                const topicos = document.querySelectorAll('input[name="topicos[]"]:checked'); // Obtener los tópicos seleccionados

                // Validar que los campos obligatorios no estén vacíos
                if (!nombre || !email || !rut) {
                    event.preventDefault(); // Evitar el envío del formulario
                    alert('Por favor, complete todos los campos obligatorios.');
                    return;
                }

                // Validar que al menos un tópico esté seleccionado
                if (topicos.length === 0) {
                    event.preventDefault(); // Evitar el envío del formulario
                    alert('Debe seleccionar al menos un tópico.');
                    return;
                }

                // Simular un retraso para mostrar un mensaje de éxito
                setTimeout(function() {
                    alert('Correo enviado.');
                }, 100); 
            });
        }

        // Manejar el envío de formularios para eliminar revisores
        document.querySelectorAll('form[action="gestionar_revisores.php"]').forEach(form => {
            form.addEventListener('submit', function(event) {
                event.preventDefault(); // Evitar el envío del formulario por defecto
                const formData = new FormData(this); // Crear un objeto FormData con los datos del formulario

                // Enviar los datos del formulario mediante fetch
                fetch('gestionar_revisores.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text()) // Procesar la respuesta como texto
                .then(data => {
                    this.closest('tr').remove(); // Eliminar la fila del revisor en la tabla
                    alert('Correo enviado.');
                })
                .catch(error => console.error('Error en la solicitud:', error)); // Manejar errores
            });
        });

        // Manejar cambios en los checkboxes de tópicos
        document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const rutRevisor = this.dataset.rutRevisor; // Obtener el RUT del revisor asociado al checkbox
                const idTopico = this.dataset.idTopico; // Obtener el ID del tópico asociado al checkbox
                const action = this.checked ? 'add' : 'remove'; // Determinar la acción según el estado del checkbox

                // Enviar la acción al servidor mediante fetch
                fetch('procesar_quitar_revisores.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        rut_revisor: rutRevisor,
                        id_topico: idTopico,
                        action: action
                    })
                })
                .then(response => response.json()) // Procesar la respuesta como JSON
                .then(data => {
                    // Manejar la respuesta del servidor si es necesario
                })
                .catch(error => console.error('Error en la solicitud:', error)); // Manejar errores
            });
        });
    });
    </script>
</body>
</html>
