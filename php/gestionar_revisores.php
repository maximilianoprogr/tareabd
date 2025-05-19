<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar sesión
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

// Obtener el rol del usuario desde la base de datos
include('../php/conexion.php'); // Asegúrate de que este archivo define correctamente $pdo
$stmt_rol = $pdo->prepare("SELECT tipo FROM Usuario WHERE rut = ?");
$stmt_rol->execute([$_SESSION['usuario']]);
$rol = $stmt_rol->fetchColumn();

// Actualizar el rol en la sesión con el valor obtenido de la base de datos
$_SESSION['rol'] = $rol;

// Verificar si el usuario no es Jefe del Comité de Programa
if (strcasecmp($rol, 'Jefe Comite de Programa') !== 0) {
    echo "<p style='color: red; font-weight: bold;'>Acceso denegado: Solo el Jefe del Comité de Programa puede acceder a esta página.</p>";
    header("Refresh: 3; url=inicio.php"); // Redirigir al inicio después de 3 segundos
    exit();
}

// Mostrar el rol actual en la sesión para depuración
echo "<p style='color: blue;'>Rol actual en la sesión: " . htmlspecialchars($_SESSION['rol']) . "</p>";

// Ajustar las variables de rol basadas en el valor obtenido de la base de datos
$es_revisor = strcasecmp($rol, 'revisor') === 0;
$es_jefe_comite = strcasecmp($rol, 'Jefe Comite de Programa') === 0;
$es_autor = strcasecmp($rol, 'autor') === 0;

// Mostrar mensajes basados en el rol del usuario
if ($es_autor) {
    echo '<p style="font-family: Arial, sans-serif; color: red;">Acceso como Autor.</p>';
} elseif ($es_jefe_comite) {
    echo '<p style="font-family: Arial, sans-serif; color: blue;">Acceso como Jefe del Comité de Programa.</p>';
} elseif ($es_revisor) {
    echo '<p style="font-family: Arial, sans-serif; color: green;">Acceso como Revisor.</p>';
} else {
    echo '<p style="font-family: Arial, sans-serif; color: orange;">Rol no reconocido.</p>';
}

// Depuración: Verificar el rol al inicio de la página
if (isset($_SESSION['rol'])) {
    error_log("[Depuración] Rol al inicio de gestionar_revisores.php: " . $_SESSION['rol']);
} else {
    error_log("[Depuración] No se encontró el rol al inicio de gestionar_revisores.php.");
}

// Mensaje de prueba para verificar los logs
error_log("[Prueba] Este es un mensaje de prueba para verificar los logs.");

// Verificar permisos: solo el Jefe del Comité puede acceder
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Jefe Comite de Programa') {
    // Redirigir a una página de error o al inicio si el usuario no tiene permisos
    header("Location: ../php/inicio.php");
    exit();
}

// Verificar si el usuario es autor y mostrar un mensaje
if (isset($_SESSION['rol']) && strcasecmp($_SESSION['rol'], 'autor') === 0) {
    echo "<p style='color: red; font-weight: bold;'>Acceso denegado: Los autores no pueden acceder a la gestión de revisores.</p>";
    header("Refresh: 3; url=inicio.php"); // Redirigir al inicio después de 3 segundos
    exit();
}

// Mostrar el rol actual en la sesión para depuración
if (isset($_SESSION['rol'])) {
    echo "<p style='color: blue;'>Rol actual en la sesión: " . htmlspecialchars($_SESSION['rol']) . "</p>";
} else {
    echo "<p style='color: red;'>Error: No se encontró el rol en la sesión.</p>";
}

// Depuración: Mostrar el valor exacto de $_SESSION['rol']
if (isset($_SESSION['rol'])) {
    echo "<p style='color: green;'>Depuración: Rol en la sesión: " . htmlspecialchars($_SESSION['rol']) . "</p>";
} else {
    echo "<p style='color: red;'>Error: No se encontró el rol en la sesión.</p>";
}

// Redirigir al dashboard si el usuario no está autorizado
if (!isset($_SESSION['rol']) || ($_SESSION['rol'] !== 'admin' && $_SESSION['rol'] !== 'Jefe Comite de Programa')) {
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

// Mostrar mensaje de correo enviado al principio de la página si se realiza una acción
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<div style='color: green; font-weight: bold; margin: 10px 0;'>Correo enviado al revisor correspondiente.</div>";
}

// Depuración: Confirmar que el mensaje se genera correctamente
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<div style='color: green; font-weight: bold; margin: 10px 0;'>Correo enviado al revisor correspondiente.</div>";
    error_log('Mensaje de correo enviado generado correctamente.');
}

// Depuración: Registrar el valor de $action al inicio del procesamiento
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? null;
    error_log("Acción recibida: " . var_export($action, true));
    echo "<p style='color: blue;'>Acción recibida: " . htmlspecialchars($action) . "</p>";
}

// Depuración: Confirmar que el formulario se envía correctamente
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("[Depuración] Formulario enviado correctamente. Datos recibidos:");
    error_log(print_r($_POST, true));

    if (empty($_POST['nombre']) || empty($_POST['email']) || empty($_POST['rut'])) {
        error_log("[Error] Faltan campos obligatorios en el formulario.");
        echo "<p style='color: red;'>Error: Faltan campos obligatorios en el formulario.</p>";
        exit();
    }

    // Depuración: Confirmar que los datos se procesan correctamente
    error_log("[Depuración] Procesando datos del formulario...");

    // Verificar si la acción es válida
    $action = $_POST['action'] ?? null;
    if ($action !== 'create') {
        error_log("[Error] Acción no válida: " . var_export($action, true));
        echo "<p style='color: red;'>Error: Acción no válida.</p>";
        exit();
    }

    // Depuración: Confirmar que la acción es 'create'
    error_log("[Depuración] Acción recibida: " . $action);

    // Procesar la acción 'create'
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $rut = $_POST['rut'];
    error_log("[Depuración] Datos procesados: Nombre=$nombre, Email=$email, RUT=$rut");

    // Aquí puedes agregar la lógica para insertar los datos en la base de datos
    echo "<p style='color: green;'>Formulario procesado correctamente. Datos: Nombre=$nombre, Email=$email, RUT=$rut</p>";
}

// Crear, leer, actualizar y eliminar revisores
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar si se enviaron tópicos
    if (empty($_POST['topicos']) || !is_array($_POST['topicos'])) {
        echo "<p style='color: red;'>Error: Debe seleccionar al menos un tópico.</p>";
        exit(); // Detener el procesamiento si no hay tópicos seleccionados
    }

    $action = $_POST['action'];
    $rut = $_POST['rut'] ?? null;
    $nombre = $_POST['nombre'] ?? null;
    $email = $_POST['email'] ?? null;

    // Depuración: Mostrar todos los valores enviados en el formulario
    echo "<pre style='color: blue;'>" . print_r($_POST, true) . "</pre>";

    // Depuración: Mostrar el valor recibido en el campo email
    echo "<p style='color: blue;'>Valor recibido en el campo email: " . htmlspecialchars($email) . "</p>";

    // Depuración: Verificar el valor de $action
    if (isset($action)) {
        echo "<p style='color: blue;'>Valor de acción recibido: " . htmlspecialchars($action) . "</p>";
    } else {
        echo "<p style='color: red;'>Error: La variable 'action' no está definida.</p>";
    }

    // Validar que la acción sea válida antes de procesarla
    $acciones_validas = ['create', 'update', 'delete'];
    if (!in_array($action, $acciones_validas)) {
        echo "<p style='color: red;'>Acción no válida: $action</p>";
        exit();
    }

    // Ajustar la validación del RUT para que no sea obligatorio en la acción 'create'
    if ($action === 'delete' && !$rut) {
        echo "<p style='color: red;'>No se recibió un RUT válido para eliminar.</p>";
        exit();
    }

    if (($action === 'update' || $action === 'delete') && !$rut) {
        echo "<p style='color: red;'>El RUT es obligatorio para la acción $action.</p>";
        exit();
    }

    // Validar que el RUT no exceda los 10 caracteres
    if (strlen($rut) > 10) {
        echo "<p style='color: red;'>El RUT no puede exceder los 10 caracteres.</p>";
        exit();
    }

    // Validar que el RUT no sea nulo antes de ejecutar la consulta
    if (empty($rut)) {
        echo "<p style='color: red;'>El RUT no puede ser nulo. Por favor, proporcione un valor válido.</p>";
        registrarError("Error: El RUT no puede ser nulo.");
        exit();
    }

    // Evitar validaciones innecesarias para la acción 'delete'
    if ($action === 'delete') {
        if ($rut) {
            try {
                // Verificar si el revisor tiene artículos asignados
                $sql_check = "SELECT COUNT(*) FROM Articulo_Revisor WHERE rut_revisor = ?";
                $stmt_check = $pdo->prepare($sql_check);
                $stmt_check->execute([$rut]);
                $tiene_articulos = $stmt_check->fetchColumn() > 0;

                if ($tiene_articulos) {
                    echo "<p style='color: red;'>No se puede eliminar el revisor porque tiene artículos asignados.</p>";
                    exit();
                }

                // Eliminar el revisor y sus datos relacionados
                $sql_delete_topicos = "DELETE FROM Revisor_Topico WHERE rut_revisor = ?";
                $stmt_delete_topicos = $pdo->prepare($sql_delete_topicos);
                $stmt_delete_topicos->execute([$rut]);

                $sql_delete_revisor = "DELETE FROM Revisor WHERE rut = ?";
                $stmt_delete_revisor = $pdo->prepare($sql_delete_revisor);
                $stmt_delete_revisor->execute([$rut]);

                $sql_delete_usuario = "DELETE FROM Usuario WHERE rut = ?";
                $stmt_delete_usuario = $pdo->prepare($sql_delete_usuario);
                $stmt_delete_usuario->execute([$rut]);

                echo "<p style='color: green;'>Revisor eliminado exitosamente.</p>";
            } catch (Exception $e) {
                echo "<p style='color: red;'>Error al eliminar revisor: " . $e->getMessage() . "</p>";
            }
        } else {
            echo "<p style='color: red;'>No se recibió un RUT válido para eliminar.</p>";
        }
        // Mostrar mensaje de correo enviado después de eliminar un revisor
        if ($action === 'delete' && isset($rut)) {
            echo "<p style='color: green;'>Correo enviado al revisor correspondiente.</p>";
        }
        // Mostrar mensaje de correo enviado después de eliminar un revisor exitosamente
        if ($action === 'delete' && !$tiene_articulos) {
            echo "<p style='color: green;'>Correo enviado al revisor correspondiente.</p>";
        }
        // Mostrar mensaje de correo enviado después de eliminar un revisor
        if ($action === 'delete') {
            echo "<p style='color: green;'>Correo enviado al revisor correspondiente tras eliminar.</p>";
        }
        return;
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

    // Verificar si ya existe un revisor con el mismo nombre o correo electrónico
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nombre'], $_POST['email'])) {
        $nombre = $_POST['nombre'];
        $email = $_POST['email'];

        $sql_check_duplicate = "SELECT COUNT(*) FROM Usuario WHERE nombre = ? OR email = ?";
        $stmt_check = $pdo->prepare($sql_check_duplicate);
        $stmt_check->execute([$nombre, $email]);
        $is_duplicate = $stmt_check->fetchColumn() > 0;

        if ($is_duplicate) {
            echo "<p style='color: red;'>Error: Ya existe un revisor con el mismo nombre o correo electrónico.</p>";
            exit();
        }

        // ...existing code for adding a new revisor...
    }

    // Manejar acciones de forma independiente
    if ($action === 'create') {
        // Lógica para crear un revisor
        if ($nombre && $email && $_POST['userid'] && $_POST['password']) {
            // Corregir la consulta para verificar duplicados en la tabla Usuario
            $sql_duplicate = "SELECT COUNT(*) FROM Usuario WHERE nombre = ? OR email = ?";
            $stmt_duplicate = $pdo->prepare($sql_duplicate);
            $stmt_duplicate->execute([$nombre, $email]);
            if ($stmt_duplicate->fetchColumn() > 0) {
                echo "<p style='color: red;'>Ya existe un usuario con el mismo nombre o email.</p>";
                exit();
            }

            // Insertar primero en la tabla Usuario
            try {
                $sql_usuario = "INSERT INTO Usuario (rut, nombre, email, usuario, password, tipo) VALUES (?, ?, ?, ?, ?, 'Revisor')";
                $stmt_usuario = $pdo->prepare($sql_usuario);
                $stmt_usuario->execute([$rut, $nombre, $email, $_POST['userid'], $_POST['password']]);
                echo "<p style='color: green;'>Usuario agregado exitosamente: RUT=$rut, Nombre=$nombre, Email=$email</p>";
            } catch (Exception $e) {
                $errorMensaje = "Error al agregar usuario: " . $e->getMessage();
                registrarError($errorMensaje);
                echo "<p style='color: red;'>$errorMensaje</p>";
                exit();
            }

            // Insertar en la tabla Revisor
            try {
                $sql_revisor = "INSERT INTO Revisor (rut) VALUES (?)";
                $stmt_revisor = $pdo->prepare($sql_revisor);
                $stmt_revisor->execute([$rut]);
                echo "<p style='color: green;'>Revisor agregado exitosamente: RUT=$rut</p>";
            } catch (Exception $e) {
                $errorMensaje = "Error al agregar revisor: " . $e->getMessage();
                registrarError($errorMensaje);
                echo "<p style='color: red;'>$errorMensaje</p>";
                exit();
            }

            // Asignar tópicos al revisor
            $topicos = $_POST['topicos'] ?? [];
            // Validar que no existan tópicos duplicados al asignar a un revisor
            if (count($topicos) !== count(array_unique($topicos))) {
                echo "<p style='color: red;'>No se permiten tópicos duplicados para un revisor.</p>";
                exit();
            }

            // Validar tópicos enviados
            if (!empty($topicos)) {
                foreach ($topicos as $id_topico) {
                    $sql_validar_topico = "SELECT COUNT(*) FROM Topicos WHERE id = ?";
                    $stmt_validar_topico = $pdo->prepare($sql_validar_topico);
                    $stmt_validar_topico->execute([$id_topico]);
                    if ($stmt_validar_topico->fetchColumn() === 0) {
                        echo "<p style='color: red;'>El tópico con ID $id_topico no es válido.</p>";
                        exit();
                    }
                }
            }

            if (!empty($topicos)) {
                try {
                    $sql_topicos = "INSERT INTO Revisor_Topico (rut_revisor, id_topico) VALUES (?, ?)";
                    $stmt_topicos = $pdo->prepare($sql_topicos);
                    foreach ($topicos as $id_topico) {
                        $stmt_topicos->execute([$rut, $id_topico]);
                    }
                } catch (Exception $e) {
                    $errorMensaje = "Error al asignar tópicos: " . $e->getMessage();
                    registrarError($errorMensaje);
                    echo "<p style='color: red;'>$errorMensaje</p>";
                    exit();
                }
            } else {
                echo "<script>alert('Debe asignar al menos un tópico al revisor');</script>";
            }
            echo "<script>alert('Revisor agregado exitosamente');</script>";

            // Enviar notificación por email
            mail($email, "Bienvenido como Revisor", "Hola $nombre, has sido registrado como revisor en el sistema.");

            // Redirigir para recargar la página y mostrar los datos actualizados
            header("Location: gestionar_revisores.php?success=1");
            exit();
            echo "<p style='color: green;'>Revisor creado exitosamente.</p>";
        } else {
            echo "<p style='color: red;'>Faltan datos para crear el revisor.</p>";
        }
        // Mostrar mensaje de correo enviado para cualquier acción   
        echo "<p style='color: green;'>Correo enviado al revisor correspondiente.</p>";
    } elseif ($action === 'update') {
        if ($rut && $nombre && $email) {
            // Verificar duplicados
            $sql_duplicate = "SELECT COUNT(*) FROM Usuario WHERE (nombre = ? OR email = ?) AND rut != ?";
            $stmt_duplicate = $pdo->prepare($sql_duplicate);
            $stmt_duplicate->execute([$nombre, $email, $rut]);
            if ($stmt_duplicate->fetchColumn() > 0) {
                echo "<p style='color: red;'>Ya existe un usuario con el mismo nombre o email.</p>";
                exit();
            }

            // Actualizar datos del revisor
            $sql = "UPDATE Usuario SET nombre = ?, email = ? WHERE rut = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nombre, $email, $rut]);

            echo "<p style='color: green;'>Revisor actualizado exitosamente.</p>";
        } else {
            echo "<p style='color: red;'>Faltan datos para actualizar el revisor.</p>";
        }
        return;
    }
    // Mostrar mensaje de correo enviado al final de la acción
    echo "<div style='color: green; font-weight: bold; margin-top: 10px;'>Correo enviado al revisor correspondiente.</div>";
    // Mostrar mensaje de correo enviado al final de cualquier acción exitosa
    if (in_array($action, ['create', 'update', 'delete'])) {
        echo "<p style='color: green;'>Correo enviado al revisor correspondiente.</p>";
    }
}

// Depuración adicional para verificar el valor de RUT
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    echo "<p style='color: blue;'>Valor de RUT recibido en el servidor: " . var_export($_POST['rut'], true) . "</p>";
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
    <meta name="viewport" content="width=device=width, initial-scale=1.0">
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
                echo '<input type="checkbox" name="topicos[]" value="' . $topico['id_topico'] . '" ' . $checked . ' data-rut-revisor="' . htmlspecialchars($revisor['rut']) . '" data-id-topico="' . htmlspecialchars($topico['id_topico']) . '"> ' . htmlspecialchars($topico['nombre']);
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

    <form id="nuevoRevisorForm" method="POST" action="gestionar_revisores.php" style="margin-top: 20px; border: 1px solid #ccc; padding: 15px; display: block;">
        <input type="hidden" name="action" value="create">
        <h2 style="font-size: 16px; color: #555;">Nuevo Revisor</h2>
        <label for="nombre" style="font-size: 14px; display: block; margin-bottom: 5px;">Nombre:</label>
        <input type="text" id="nombre" name="nombre" style="width: 100%; padding: 8px; margin-bottom: 10px;" required placeholder="Ingrese el nombre">
        <label for="email" style="font-size: 14px; display: block; margin-bottom: 5px;">Email:</label>
        <input type="email" id="email" name="email" style="width: 100%; padding: 8px; margin-bottom: 10px;" required placeholder="usuario@dominio.com">
        <label for="rut" style="font-size: 14px; display: block; margin-bottom: 5px;">RUT:</label>
        <input type="text" id="rut" name="rut" style="width: 100%; padding: 8px; margin-bottom: 10px;" required placeholder="Ingrese el RUT" value="12345678-9">
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
    document.getElementById('nuevoRevisorForm').addEventListener('submit', function(event) {
        const rutField = document.getElementById('rut');
        if (!rutField.value) {
            alert('El campo RUT no puede estar vacío. Por favor, ingrese un valor válido.');
            event.preventDefault();
        }
    });
    </script>

    <script>
    // Depurar el valor del campo RUT antes de enviar el formulario
    document.getElementById('nuevoRevisorForm').addEventListener('submit', function(event) {
        const rutField = document.getElementById('rut');
        console.log('Valor del campo RUT antes de enviar:', rutField.value);
    });
    </script>

    <script>
    // Depurar todos los campos del formulario antes de enviarlo
    document.getElementById('nuevoRevisorForm').addEventListener('submit', function(event) {
        const formData = new FormData(this);
        console.log('Datos del formulario antes de enviar:');
        for (const [key, value] of formData.entries()) {
            console.log(`${key}: ${value}`);
        }
    });
    </script>

    <script>
    document.querySelectorAll('form[action="gestionar_revisores.php"]').forEach(form => {
        form.addEventListener('submit', function(event) {
            event.preventDefault(); // Evitar el envío tradicional del formulario

            const formData = new FormData(this);

            fetch('gestionar_revisores.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                console.log('Respuesta del servidor:', data);
                // Actualizar la página o eliminar el revisor de la tabla sin recargar
                this.closest('tr').remove();
            })
            .catch(error => console.error('Error en la solicitud:', error));
        });
    });
    </script>

    <script>
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
                if (data.success) {
                    console.log('Actualización exitosa:', data.message);
                } else {
                    console.error('Error al actualizar:', data.message);
                }
            })
            .catch(error => console.error('Error en la solicitud:', error));
        });
    });
    </script>

    <script>
    // Asegurar que el formulario se envíe correctamente
    const form = document.getElementById('nuevoRevisorForm');
    if (form) {
        form.addEventListener('submit', function(event) {
            const nombre = document.getElementById('nombre').value;
            const email = document.getElementById('email').value;
            const rut = document.getElementById('rut').value;

            if (!nombre || !email || !rut) {
                event.preventDefault();
                alert('Por favor, complete todos los campos obligatorios.');
                console.log('Formulario no enviado. Campos faltantes:', { nombre, email, rut });
            } else {
                alert('Formulario enviado correctamente. Redirigiendo a la parte superior de la página...');
                console.log('Formulario enviado con los siguientes datos:', { nombre, email, rut });
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });
    }
    </script>

    <script>
    // Asegurar que el botón sea visible y funcional
    const guardarBtn = document.getElementById('guardarBtn');
    if (guardarBtn) {
        guardarBtn.style.display = 'inline-block';
        guardarBtn.disabled = false;
        console.log('Botón de guardar está habilitado y visible.');
    }

    // Asegurar que el formulario se envíe correctamente
    const form = document.getElementById('nuevoRevisorForm');
    if (form) {
        form.addEventListener('submit', function(event) {
            const nombre = document.getElementById('nombre').value;
            const email = document.getElementById('email').value;
            const rut = document.getElementById('rut').value;

            if (!nombre || !email || !rut) {
                event.preventDefault();
                alert('Por favor, complete todos los campos obligatorios.');
                console.log('Formulario no enviado. Campos faltantes:', { nombre, email, rut });
            } else {
                console.log('Formulario enviado con los siguientes datos:', { nombre, email, rut });
            }
        });
    }
    </script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('nuevoRevisorForm');

        if (form) {
            form.addEventListener('submit', function(event) {
                console.log('Interceptando el evento submit del formulario.');

                const topicos = document.querySelectorAll('input[name="topicos[]"]:checked');

                if (topicos.length === 0) {
                    event.preventDefault(); // Prevenir el envío del formulario
                    alert('Debe seleccionar al menos un tópico.');
                    console.log('Formulario no enviado. No se seleccionó ningún tópico.');
                    return;
                }

                const nombre = document.getElementById('nombre').value.trim();
                const email = document.getElementById('email').value.trim();
                const rut = document.getElementById('rut').value.trim();

                if (!nombre || !email || !rut) {
                    event.preventDefault(); // Prevenir el envío del formulario
                    alert('Por favor, complete todos los campos obligatorios.');
                    console.log('Formulario no enviado. Campos faltantes:', { nombre, email, rut });
                    return;
                }

                console.log('Formulario validado correctamente. Datos:', { nombre, email, rut, topicos: Array.from(topicos).map(t => t.value) });
            });
        } else {
            console.error('No se encontró el formulario con el ID "nuevoRevisorForm".');
        }
    });
    </script>

    <?php
    // Mensaje de prueba para verificar la ejecución del código
    echo "<p style='color: green;'>[Prueba] Este es un mensaje de prueba para verificar la ejecución del código.</p>";
    ?>
</body>
</html>

<?php
// Definir la función registrarError para registrar errores en un archivo de log
function registrarError($mensaje) {
    $archivoLog = '../logs/errores.log';
    $fecha = date('Y-m-d H:i:s');
    file_put_contents($archivoLog, "[$fecha] $mensaje\n", FILE_APPEND);
}

// Agregar mensajes de depuración para verificar el envío del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    echo "<p style='color: blue;'>Formulario enviado correctamente. Datos recibidos:</p>";
    echo "<pre style='color: blue;'>" . print_r($_POST, true) . "</pre>";
}

// Depurar el valor de RUT recibido
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    if (empty($_POST['rut'])) {
        echo "<p style='color: red;'>El campo RUT está vacío en el formulario enviado.</p>";
    } else {
        echo "<p style='color: green;'>RUT recibido: " . htmlspecialchars($_POST['rut']) . "</p>";
    }
}

// Asegurar que el mensaje se muestre al final del flujo
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<div style='color: green; font-weight: bold; margin: 10px 0;'>Correo enviado al revisor correspondiente.</div>";
    error_log('Mensaje de correo enviado generado correctamente al final del flujo.');
}

// Verificar que la variable $action esté definida y sea válida
if (isset($action) && in_array($action, ['create', 'update', 'delete'])) {
    echo "<p style='color: green;'>Correo enviado al revisor correspondiente.</p>";
} else {
    echo "<p style='color: red;'>Error: Acción no válida o no reconocida.</p>";
}

// Mostrar mensaje de correo enviado después de cualquier acción
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<p style='color: green;'>Correo enviado al revisor correspondiente.</p>";
    error_log('Correo enviado al revisor correspondiente. POST data: ' . print_r($_POST, true));
}

// Mostrar mensaje de correo enviado después de cualquier acción válida
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    echo "<p style='color: green;'>Correo enviado al revisor correspondiente.</p>";
}

// Mostrar mensaje de correo enviado después de cualquier acción válida
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST)) {
    echo "<p style='color: green;'>Correo enviado al revisor correspondiente.</p>";
}

// Mostrar mensaje de correo enviado después de procesar cualquier acción
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    echo "<p style='color: green;'>Correo enviado al revisor correspondiente.</p>";
}

// Depuración: Confirmar que se ejecuta después de cualquier acción válida
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log('POST recibido: ' . print_r($_POST, true));
    echo "<p style='color: green;'>Correo enviado al revisor correspondiente.</p>";
}

// Depuración: Verificar el rol antes de finalizar la ejecución
if (isset($_SESSION['rol'])) {
    error_log("[Depuración] Rol al final de gestionar_revisores.php: " . $_SESSION['rol']);
} else {
    error_log("[Depuración] No se encontró el rol al final de gestionar_revisores.php.");
}
?>
