<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

include('../php/conexion.php');
$stmt_rol = $pdo->prepare("SELECT tipo FROM Usuario WHERE rut = ?");
$stmt_rol->execute([$_SESSION['usuario']]);
$rol = $stmt_rol->fetchColumn();
$_SESSION['rol'] = $rol;

if (strcasecmp($rol, 'Jefe Comite de Programa') !== 0) {
    header("Location: inicio.php");
    exit();
}

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Jefe Comite de Programa') {
    header("Location: ../php/inicio.php");
    exit();
}

$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $rut = $_POST['rut'] ?? null;
    $nombre = $_POST['nombre'] ?? null;
    $email = $_POST['email'] ?? null;
    $topicos = $_POST['topicos'] ?? [];

    // Validaciones básicas
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

    if ($mensaje === "") {
        if ($action === 'delete') {
            if (!$rut) {
                $mensaje = "<p style='color: red;'>No se recibió un RUT válido para eliminar.</p>";
            } else {
                try {
                    $sql_check = "SELECT COUNT(*) FROM Articulo_Revisor WHERE rut_revisor = ?";
                    $stmt_check = $pdo->prepare($sql_check);
                    $stmt_check->execute([$rut]);
                    $tiene_articulos = $stmt_check->fetchColumn() > 0;

                    if ($tiene_articulos) {
                        $mensaje = "<p style='color: red;'>No se puede eliminar el revisor porque tiene artículos asignados.</p>";
                    } else {
                        $pdo->prepare("DELETE FROM Revisor_Topico WHERE rut_revisor = ?")->execute([$rut]);
                        $pdo->prepare("DELETE FROM Revisor WHERE rut = ?")->execute([$rut]);
                        $pdo->prepare("DELETE FROM Usuario WHERE rut = ?")->execute([$rut]);
                        $mensaje = "<p style='color: green;'>Revisor eliminado exitosamente.</p>";
                    }
                } catch (Exception $e) {
                    $mensaje = "<p style='color: red;'>Error al eliminar revisor: " . $e->getMessage() . "</p>";
                }
            }
        } elseif ($action === 'create') {
            // Validar duplicados
            $sql_duplicate = "SELECT COUNT(*) FROM Usuario WHERE nombre = ? OR email = ?";
            $stmt_duplicate = $pdo->prepare($sql_duplicate);
            $stmt_duplicate->execute([$nombre, $email]);
            if ($stmt_duplicate->fetchColumn() > 0) {
                $mensaje = "<p style='color: red;'>Ya existe un usuario con el mismo nombre o email.</p>";
            } else {
                try {
                    $userid = $_POST['userid'] ?? '';
                    $password = $_POST['password'] ?? '';
                    if (!$userid || !$password) {
                        $mensaje = "<p style='color: red;'>Usuario ID y contraseña son obligatorios.</p>";
                    } else {
                        $pdo->prepare("INSERT INTO Usuario (rut, nombre, email, usuario, password, tipo) VALUES (?, ?, ?, ?, ?, 'Revisor')")
                            ->execute([$rut, $nombre, $email, $userid, $password]);
                        $pdo->prepare("INSERT INTO Revisor (rut) VALUES (?)")->execute([$rut]);
                        // Tópicos
                        if (count($topicos) !== count(array_unique($topicos))) {
                            $mensaje = "<p style='color: red;'>No se permiten tópicos duplicados para un revisor.</p>";
                        } else {
                            foreach ($topicos as $id_topico) {
                                $sql_validar_topico = "SELECT COUNT(*) FROM Topico WHERE id_topico = ?";
                                $stmt_validar_topico = $pdo->prepare($sql_validar_topico);
                                $stmt_validar_topico->execute([$id_topico]);
                                if ($stmt_validar_topico->fetchColumn() == 0) {
                                    $mensaje = "<p style='color: red;'>El tópico con ID $id_topico no es válido.</p>";
                                    break;
                                }
                            }
                            if ($mensaje === "") {
                                $stmt_topicos = $pdo->prepare("INSERT INTO Revisor_Topico (rut_revisor, id_topico) VALUES (?, ?)");
                                foreach ($topicos as $id_topico) {
                                    $stmt_topicos->execute([$rut, $id_topico]);
                                }
                                // mail($email, "Bienvenido como Revisor", "Hola $nombre, has sido registrado como revisor en el sistema.");
                                $mensaje = "<p style='color: green;'>Revisor creado exitosamente.</p>";
                            }
                        }
                    }
                } catch (Exception $e) {
                    $mensaje = "<p style='color: red;'>Error al crear revisor: " . $e->getMessage() . "</p>";
                }
            }
        } elseif ($action === 'update') {
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

// Leer revisores
try {
    $sql = "SELECT Usuario.rut, Usuario.nombre, Usuario.email, 
               GROUP_CONCAT(Topico.nombre SEPARATOR ', ') AS topicos
        FROM Revisor
        INNER JOIN Usuario ON Revisor.rut = Usuario.rut
        LEFT JOIN Revisor_Topico ON Revisor.rut = Revisor_Topico.rut_revisor
        LEFT JOIN Topico ON Revisor_Topico.id_topico = Topico.id_topico
        GROUP BY Usuario.rut, Usuario.nombre, Usuario.email";
    $stmt = $pdo->query($sql);
    $revisores = $stmt->fetchAll();
} catch (Exception $e) {
    $revisores = [];
}

$sql_topicos = "SELECT id_topico, nombre FROM Topico";
$stmt_topicos = $pdo->query($sql_topicos);
$topicos_disponibles = $stmt_topicos->fetchAll();
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
    // Mostrar revisores en una tabla con opciones para eliminar
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
            echo '<input type="checkbox" name="topicos[]" value="' . $topico['id_topico'] . '" ' . $checked . ' data-rut-revisor="' . htmlspecialchars($revisor['rut']) . '" data-id-topico="' . htmlspecialchars($topico['id_topico']) . '"> ' . htmlspecialchars($topico['nombre']);
            echo '</label>';
        }

        echo '</td>';
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
    ?>

    <form id="nuevoRevisorForm" method="POST" action="gestionar_revisores.php" style="margin-top: 20px; border: 1px solid #ccc; padding: 15px; display: block;">
        <input type="hidden" name="action" value="create">
        <h2 style="font-size: 16px; color: #555;">Nuevo Revisor</h2>
        <label for="nombre" style="font-size: 14px; display: block; margin-bottom: 5px;">Nombre:</label>
        <input type="text" id="nombre" name="nombre" style="width: 100%; padding: 8px; margin-bottom: 10px;" required placeholder="Ingrese el nombre">
        <label for="email" style="font-size: 14px; display: block; margin-bottom: 5px;">Email:</label>
        <input type="email" id="email" name="email" style="width: 100%; padding: 8px; margin-bottom: 10px;" required placeholder="usuario@dominio.com">
        <label for="rut" style="font-size: 14px; display: block; margin-bottom: 5px;">RUT:</label>
        <input type="text" id="rut" name="rut" style="width: 100%; padding: 8px; margin-bottom: 10px;" required placeholder="Ingrese el RUT" value="">
        <label for="userid" style="font-size: 14px; display: block; margin-bottom: 5px;">Usuario ID:</label>
        <input type="text" id="userid" name="userid" style="width: 100%; padding: 8px; margin-bottom: 10px;">
        <label for="password" style="font-size: 14px; display: block; margin-bottom: 5px;">Contraseña:</label>
        <input type="password" id="password" name="password" style="width: 100%; padding: 8px; margin-bottom: 10px;">

        <label style="font-size: 14px; display: block; margin-bottom: 5px;">Tópicos:</label>
        <div style="margin-bottom: 10px;">
            <?php foreach ($topicos_disponibles as $topico): ?>
                <label style="display: block;">
                    <input type="checkbox" name="topicos[]" value="<?php echo htmlspecialchars($topico['id_topico']); ?>">
                    <?php echo htmlspecialchars($topico['nombre']); ?>
                </label>
            <?php endforeach; ?>
        </div>

        <button type="submit" id="guardarBtn" style="font-size: 14px; padding: 10px 20px; background-color: #4CAF50; color: white; border: none; cursor: pointer;">Guardar</button>
    </form>

    <a href="dashboard.php" style="font-family: Arial, sans-serif; font-size: 14px; color: #007BFF; text-decoration: none; display: block; margin-top: 20px;">Volver al inicio</a>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Mensaje al guardar (crear revisor)
        const form = document.getElementById('nuevoRevisorForm');
        if (form) {
            form.addEventListener('submit', function(event) {
                const nombre = document.getElementById('nombre').value.trim();
                const email = document.getElementById('email').value.trim();
                const rut = document.getElementById('rut').value.trim();
                const topicos = document.querySelectorAll('input[name="topicos[]"]:checked');
                if (!nombre || !email || !rut) {
                    event.preventDefault();
                    alert('Por favor, complete todos los campos obligatorios.');
                    return;
                }
                if (topicos.length === 0) {
                    event.preventDefault();
                    alert('Debe seleccionar al menos un tópico.');
                    return;
                }
                // Mostrar mensaje de correo enviado (solo mensaje, no envía nada)
                setTimeout(function() {
                    alert('Correo enviado.');
                }, 100); // pequeño retraso para que el submit funcione normalmente
            });
        }

        // AJAX para eliminar revisor sin recargar la página
        document.querySelectorAll('form[action="gestionar_revisores.php"]').forEach(form => {
            form.addEventListener('submit', function(event) {
                event.preventDefault();
                const formData = new FormData(this);
                fetch('gestionar_revisores.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    this.closest('tr').remove();
                    alert('Correo enviado.');
                })
                .catch(error => console.error('Error en la solicitud:', error));
            });
        });

        // AJAX para actualizar tópicos de revisor (sin cambios)
        document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const rutRevisor = this.dataset.rutRevisor;
                const idTopico = this.dataset.idTopico;
                const action = this.checked ? 'add' : 'remove';
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
                .then(response => response.json())
                .then(data => {
                    // Opcional: mostrar mensaje de éxito o error
                })
                .catch(error => console.error('Error en la solicitud:', error));
            });
        });
    });
    </script>
</body>
</html>
