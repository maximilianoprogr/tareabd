<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    echo "<p>Error: Usuario no autenticado.</p>";
    exit();
}

echo "<p>Usuario actual: " . htmlspecialchars($_SESSION['usuario']) . "</p>";

include('conexion.php');

$sql_check_usuario = "SELECT COUNT(*) FROM Usuario WHERE rut = ?";
$stmt_check_usuario = $pdo->prepare($sql_check_usuario);
$stmt_check_usuario->execute([$_SESSION['usuario']]);
$is_usuario = $stmt_check_usuario->fetchColumn() > 0;

if (!$is_usuario) {
    $sql_insert_usuario = "INSERT INTO Usuario (rut, nombre, email, usuario, password, tipo) VALUES (?, 'Jefe Comite', 'jefe@comite.com', 'jefe_comite', 'password123', 'Jefe Comite de Programa')";
    $stmt_insert_usuario = $pdo->prepare($sql_insert_usuario);
    $stmt_insert_usuario->execute([$_SESSION['usuario']]);
}

$id_articulo = $_GET['revision'] ?? null;
if (!$id_articulo) {
    echo "<p>Error: No se especificó un artículo para opinar.</p>";
    exit();
}

$sql_validar_articulo = "SELECT COUNT(*) FROM Articulo WHERE id_articulo = ?";
$stmt_validar_articulo = $pdo->prepare($sql_validar_articulo);
$stmt_validar_articulo->execute([$id_articulo]);
$articulo_existe = $stmt_validar_articulo->fetchColumn() > 0;

if (!$articulo_existe) {
    echo "<p>Error: El artículo especificado no existe.</p>";
    exit();
}

$sql_validar_revisor = "SELECT COUNT(*) FROM Revisor WHERE rut = ?";
$stmt_validar_revisor = $pdo->prepare($sql_validar_revisor);
$stmt_validar_revisor->execute([$_SESSION['usuario']]);
$revisor_existe = $stmt_validar_revisor->fetchColumn() > 0;

if ($_SESSION['rol'] === 'Jefe Comite de Programa') {
    $revisor_existe = true; 
}

if ($_SESSION['rol'] === 'Jefe Comite de Programa' && !isset($_GET['revisor'])) {
    echo "<p>Error: No se especificó un revisor válido.</p>";
    exit();
}

if (isset($_GET['revisor'])) {
    $_GET['revisor'] = preg_replace('/^R/', '', $_GET['revisor']);
    echo "<p>Depuración: parámetro revisor limpio = " . htmlspecialchars($_GET['revisor']) . "</p>";
}

if (isset($_GET['revisor'])) {
    echo "<p>Depuración: parámetro revisor = " . htmlspecialchars($_GET['revisor']) . "</p>";
}

if ($_SESSION['rol'] === 'Jefe Comite de Programa') {
    $sql_check_revisor = "SELECT COUNT(*) FROM Revisor WHERE rut = ?";
    $stmt_check_revisor = $pdo->prepare($sql_check_revisor);
    $stmt_check_revisor->execute([$_SESSION['usuario']]);
    $is_revisor = $stmt_check_revisor->fetchColumn() > 0;

    if (!$is_revisor) {
        $sql_insert_revisor = "INSERT INTO Revisor (rut) VALUES (?)";
        $stmt_insert_revisor = $pdo->prepare($sql_insert_revisor);
        $stmt_insert_revisor->execute([$_SESSION['usuario']]);
    }
}

if (!$revisor_existe) {
    echo "<p>Error: El revisor especificado no existe.</p>";
    exit();
}

if (strcasecmp($_SESSION['rol'], 'autor') === 0) {
    echo "<p>Error: No puedes opinar porque no eres el revisor asignado para este artículo.</p>";
    exit();
}

if (strcasecmp($_SESSION['rol'], 'revisor') === 0 && $_SESSION['usuario'] !== $_GET['revisor']) {
    header("Location: revisor_incorrecto.php?revisor=" . urlencode($_GET['revisor']) . "&usuario=" . urlencode($_SESSION['usuario']));
    exit();
}

$rut_revisor = $_SESSION['usuario'];
if ($_SESSION['rol'] === 'Jefe Comite de Programa' && isset($_GET['revisor'])) {
    $rut_revisor = $_GET['revisor'];
}

echo "<p>Depuración: rut_revisor = " . htmlspecialchars($rut_revisor) . "</p>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $calidad_tecnica = isset($_POST['calidad_tecnica']) ? 1 : 0;
    $originalidad = isset($_POST['originalidad']) ? 1 : 0;
    $valoracion_global = isset($_POST['valoracion_global']) ? 1 : 0;
    $argumentos_valoracion = $_POST['argumentos_valoracion'] ?? '';
    $comentarios_autores = $_POST['comentarios_autores'] ?? '';

    echo "<p>Depuración: id_articulo = " . htmlspecialchars($id_articulo) . ", rut_revisor = " . htmlspecialchars($rut_revisor) . ", calidad_tecnica = " . htmlspecialchars($calidad_tecnica) . ", originalidad = " . htmlspecialchars($originalidad) . ", valoracion_global = " . htmlspecialchars($valoracion_global) . ", argumentos_valoracion = " . htmlspecialchars($argumentos_valoracion) . ", comentarios_autores = " . htmlspecialchars($comentarios_autores) . "</p>";

    $sql_check = "SELECT COUNT(*) FROM Evaluacion_Articulo WHERE id_articulo = ? AND rut_revisor = ?";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute([$id_articulo, $rut_revisor]);
    $exists = $stmt_check->fetchColumn() > 0;

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

    echo "<p>Consulta ejecutada: " . htmlspecialchars($sql) . "</p>";
    echo "<p>Parámetros: " . htmlspecialchars(json_encode([$calidad_tecnica, $originalidad, $valoracion_global, $argumentos_valoracion, $comentarios_autores, $id_articulo, $rut_revisor])) . "</p>";

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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale="1.0">
    <title>Opinar</title>
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>
    <div class="container">
        <h1>Opinar sobre el Artículo</h1>
        <h2 style="font-family: Arial, sans-serif; color: #555;">Formulario de Evaluación</h2>
        <form method="POST" style="border: 1px solid #ccc; padding: 15px;">
            <div style="margin-bottom: 15px;">
                <label for="calidad_tecnica" style="font-size: 14px; display: block; margin-bottom: 5px;">Calidad Técnica:</label>
                <input type="checkbox" id="calidad_tecnica" name="calidad_tecnica">
            </div>

            <div style="margin-bottom: 15px;">
                <label for="originalidad" style="font-size: 14px; display: block; margin-bottom: 5px;">Originalidad:</label>
                <input type="checkbox" id="originalidad" name="originalidad">
            </div>

            <div style="margin-bottom: 15px;">
                <label for="valoracion_global" style="font-size: 14px; display: block; margin-bottom: 5px;">Valoración Global:</label>
                <input type="checkbox" id="valoracion_global" name="valoracion_global">
            </div>

            <div style="margin-bottom: 15px;">
                <label for="argumentos_valoracion" style="font-size: 14px; display: block; margin-bottom: 5px;">Argumentos de Valoración Global:</label>
                <textarea id="argumentos_valoracion" name="argumentos_valoracion" rows="3" style="width: 100%; font-size: 12px; padding: 5px; border: 1px solid #ccc;"></textarea>
            </div>

            <div style="margin-bottom: 15px;">
                <label for="comentarios_autores" style="font-size: 14px; display: block; margin-bottom: 5px;">Comentarios a Autores:</label>
                <textarea id="comentarios_autores" name="comentarios_autores" rows="3" style="width: 100%; font-size: 12px; padding: 5px; border: 1px solid #ccc;"></textarea>
            </div>

            <button type="submit" style="background-color: #007BFF; color: white; padding: 10px 15px; border: none; border-radius: 5px; font-size: 14px;">Enviar Opinión</button>
        </form>
    </div>
</body>
</html>
