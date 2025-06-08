<?php
// Iniciar sesión para manejar autenticación de usuarios
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    echo "<p>Error: Usuario no autenticado.</p>"; // Mostrar mensaje de error si no está autenticado
    exit();
}

// Mostrar información del usuario actual para depuración
echo "<p>Usuario actual: " . htmlspecialchars($_SESSION['usuario']) . "</p>";

// Incluir archivo de conexión a la base de datos
include('conexion.php');

// Verificar si el usuario existe en la base de datos
$sql_check_usuario = "SELECT COUNT(*) FROM Usuario WHERE rut = ?";
$stmt_check_usuario = $pdo->prepare($sql_check_usuario);
$stmt_check_usuario->execute([$_SESSION['usuario']]);
$is_usuario = $stmt_check_usuario->fetchColumn() > 0;

// Insertar usuario si no existe
if (!$is_usuario) {
    $sql_insert_usuario = "INSERT INTO Usuario (rut, nombre, email, usuario, password, tipo) VALUES (?, 'Jefe Comite', 'jefe@comite.com', 'jefe_comite', 'password123', 'Jefe Comite de Programa')";
    $stmt_insert_usuario = $pdo->prepare($sql_insert_usuario);
    $stmt_insert_usuario->execute([$_SESSION['usuario']]);
}

// Validar que se haya especificado un artículo para opinar
$id_articulo = $_GET['revision'] ?? null;
if (!$id_articulo) {
    echo "<p>Error: No se especificó un artículo para opinar.</p>"; // Mostrar mensaje de error si no se especifica un artículo
    exit();
}

// Validar que el artículo exista en la base de datos
$sql_validar_articulo = "SELECT COUNT(*) FROM Articulo WHERE id_articulo = ?";
$stmt_validar_articulo = $pdo->prepare($sql_validar_articulo);
$stmt_validar_articulo->execute([$id_articulo]);
$articulo_existe = $stmt_validar_articulo->fetchColumn() > 0;

if (!$articulo_existe) {
    echo "<p>Error: El artículo especificado no existe.</p>"; // Mostrar mensaje de error si el artículo no existe
    exit();
}

// Validar que el revisor exista en la base de datos
$sql_validar_revisor = "SELECT COUNT(*) FROM Revisor WHERE rut = ?";
$stmt_validar_revisor = $pdo->prepare($sql_validar_revisor);
$stmt_validar_revisor->execute([$_SESSION['usuario']]);
$revisor_existe = $stmt_validar_revisor->fetchColumn() > 0;

// Si el usuario es Jefe de Comité de Programa, se le considera revisor automáticamente
if ($_SESSION['rol'] === 'Jefe Comite de Programa') {
    $revisor_existe = true; 
}

// Validar que se haya especificado un revisor si el usuario es Jefe de Comité de Programa
if ($_SESSION['rol'] === 'Jefe Comite de Programa' && !isset($_GET['revisor'])) {
    echo "<p>Error: No se especificó un revisor válido.</p>";
    exit();
}

// Limpiar y mostrar el parámetro revisor para depuración
if (isset($_GET['revisor'])) {
    $_GET['revisor'] = preg_replace('/^R/', '', $_GET['revisor']);
    echo "<p>Depuración: parámetro revisor limpio = " . htmlspecialchars($_GET['revisor']) . "</p>";
}

// Mostrar el parámetro revisor original para depuración
if (isset($_GET['revisor'])) {
    echo "<p>Depuración: parámetro revisor = " . htmlspecialchars($_GET['revisor']) . "</p>";
}

// Si el usuario es Jefe de Comité de Programa, verificar si ya es revisor
if ($_SESSION['rol'] === 'Jefe Comite de Programa') {
    $sql_check_revisor = "SELECT COUNT(*) FROM Revisor WHERE rut = ?";
    $stmt_check_revisor = $pdo->prepare($sql_check_revisor);
    $stmt_check_revisor->execute([$_SESSION['usuario']]);
    $is_revisor = $stmt_check_revisor->fetchColumn() > 0;

    // Insertar al usuario como revisor si no existe
    if (!$is_revisor) {
        $sql_insert_revisor = "INSERT INTO Revisor (rut) VALUES (?)";
        $stmt_insert_revisor = $pdo->prepare($sql_insert_revisor);
        $stmt_insert_revisor->execute([$_SESSION['usuario']]);
    }
}

// Validar que el revisor especificado exista
if (!$revisor_existe) {
    echo "<p>Error: El revisor especificado no existe.</p>";
    exit();
}

// Verificar rol del usuario y redirigir si es necesario
if (strcasecmp($_SESSION['rol'], 'autor') === 0) {
    echo "<p>Error: No puedes opinar porque no eres el revisor asignado para este artículo.</p>";
    exit();
}

// Redirigir a página de revisor incorrecto si corresponde
if (strcasecmp($_SESSION['rol'], 'revisor') === 0 && $_SESSION['usuario'] !== $_GET['revisor']) {
    header("Location: revisor_incorrecto.php?revisor=" . urlencode($_GET['revisor']) . "&usuario=" . urlencode($_SESSION['usuario']));
    exit();
}

// Asignar rut del revisor según el rol del usuario
$rut_revisor = $_SESSION['usuario'];
if ($_SESSION['rol'] === 'Jefe Comite de Programa' && isset($_GET['revisor'])) {
    $rut_revisor = $_GET['revisor'];
}

// Mostrar rut del revisor para depuración
echo "<p>Depuración: rut_revisor = " . htmlspecialchars($rut_revisor) . "</p>";

// Procesar el formulario de opinión
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener y procesar los datos del formulario
    $calidad_tecnica = isset($_POST['calidad_tecnica']) ? 1 : 0;
    $originalidad = isset($_POST['originalidad']) ? 1 : 0;
    $valoracion_global = isset($_POST['valoracion_global']) ? 1 : 0;
    $argumentos_valoracion = $_POST['argumentos_valoracion'] ?? '';
    $comentarios_autores = $_POST['comentarios_autores'] ?? '';

    // Mostrar datos del formulario para depuración
    echo "<p>Depuración: id_articulo = " . htmlspecialchars($id_articulo) . ", rut_revisor = " . htmlspecialchars($rut_revisor) . ", calidad_tecnica = " . htmlspecialchars($calidad_tecnica) . ", originalidad = " . htmlspecialchars($originalidad) . ", valoracion_global = " . htmlspecialchars($valoracion_global) . ", argumentos_valoracion = " . htmlspecialchars($argumentos_valoracion) . ", comentarios_autores = " . htmlspecialchars($comentarios_autores) . "</p>";

    // Verificar si ya existe una evaluación para el artículo y revisor
    $sql_check = "SELECT COUNT(*) FROM Evaluacion_Articulo WHERE id_articulo = ? AND rut_revisor = ?";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute([$id_articulo, $rut_revisor]);
    $exists = $stmt_check->fetchColumn() > 0;

    // Actualizar o insertar la evaluación según corresponda
    if ($exists) {
        $sql = "UPDATE Evaluacion_Articulo SET calidad_tecnica = ?, originalidad = ?, valoracion_global = ?, argumentos_valoracion = ?, comentarios_autores = ? WHERE id_articulo = ? AND rut_revisor = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$calidad_tecnica, $originalidad, $valoracion_global, $argumentos_valoracion, $comentarios_autores, $id_articulo, $rut_revisor]);
        echo "<p>Registro actualizado correctamente.</p>";
    } else {
        $sql = "INSERT INTO Evaluacion_Articulo (calidad_tecnica, originalidad, valoracion_global, argumentos_valoracion, comentarios_autores, id_articulo, rut_revisor) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$calidad_tecnica, $originalidad, $valoracion_global, $argumentos_valoracion, $comentarios_autores, $id_articulo, $rut_revisor]);
        echo "<p>Nuevo registro insertado correctamente.</p>";
    }

    // Mostrar consulta ejecutada y parámetros para depuración
    echo "<p>Consulta ejecutada: " . htmlspecialchars($sql) . "</p>";
    echo "<p>Parámetros: " . htmlspecialchars(json_encode([$calidad_tecnica, $originalidad, $valoracion_global, $argumentos_valoracion, $comentarios_autores, $id_articulo, $rut_revisor])) . "</p>";

    // Verificar si se afectaron registros
    $affected_rows = $stmt->rowCount();
    if ($affected_rows === 0) {
        echo "<p>Advertencia: No se afectaron registros. Verifica los datos.</p>";
    }

    echo "<p>Opinión guardada correctamente.</p>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"> <!-- Configuración de codificación de caracteres -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Hacer que la página sea responsive -->
    <title>Opinar</title> <!-- Título de la página -->
    <link rel="stylesheet" href="../css/dashboard.css"> <!-- Enlace al archivo de estilos CSS -->
</head>
<body>
    <div class="container"> <!-- Contenedor principal de la página -->
        <h1>Opinar sobre el Artículo</h1> <!-- Título principal de la página -->
        <h2 style="font-family: Arial, sans-serif; color: #555;">Formulario de Evaluación</h2> <!-- Subtítulo del formulario -->
        <form method="POST" style="border: 1px solid #ccc; padding: 15px;"> <!-- Formulario para enviar la opinión -->
            <div style="margin-bottom: 15px;"> <!-- Sección para la calidad técnica -->
                <label for="calidad_tecnica" style="font-size: 14px; display: block; margin-bottom: 5px;">Calidad Técnica:</label>
                <input type="checkbox" id="calidad_tecnica" name="calidad_tecnica"> <!-- Checkbox para calidad técnica -->
            </div>

            <div style="margin-bottom: 15px;"> <!-- Sección para la originalidad -->
                <label for="originalidad" style="font-size: 14px; display: block; margin-bottom: 5px;">Originalidad:</label>
                <input type="checkbox" id="originalidad" name="originalidad"> <!-- Checkbox para originalidad -->
            </div>

            <div style="margin-bottom: 15px;"> <!-- Sección para la valoración global -->
                <label for="valoracion_global" style="font-size: 14px; display: block; margin-bottom: 5px;">Valoración Global:</label>
                <input type="checkbox" id="valoracion_global" name="valoracion_global"> <!-- Checkbox para valoración global -->
            </div>

            <div style="margin-bottom: 15px;"> <!-- Sección para los argumentos de valoración -->
                <label for="argumentos_valoracion" style="font-size: 14px; display: block; margin-bottom: 5px;">Argumentos de Valoración Global:</label>
                <textarea id="argumentos_valoracion" name="argumentos_valoracion" rows="3" style="width: 100%; font-size: 12px; padding: 5px; border: 1px solid #ccc;"></textarea> <!-- Campo de texto para argumentos de valoración -->
            </div>

            <div style="margin-bottom: 15px;"> <!-- Sección para los comentarios a los autores -->
                <label for="comentarios_autores" style="font-size: 14px; display: block; margin-bottom: 5px;">Comentarios a Autores:</label>
                <textarea id="comentarios_autores" name="comentarios_autores" rows="3" style="width: 100%; font-size: 12px; padding: 5px; border: 1px solid #ccc;"></textarea> <!-- Campo de texto para comentarios a los autores -->
            </div>

            <button type="submit" style="background-color: #007BFF; color: white; padding: 10px 15px; border: none; border-radius: 5px; font-size: 14px;">Enviar Opinión</button> <!-- Botón para enviar el formulario -->
        </form>
    </div>
</body>
</html>
