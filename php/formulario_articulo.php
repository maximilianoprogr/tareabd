<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

// Verificar si se proporcionó un ID de artículo
if (!isset($_GET['id_articulo'])) {
    echo "<p style='color: red;'>No se proporcionó un ID de artículo.</p>";
    exit();
}

$id_articulo = $_GET['id_articulo'];

// Obtener los datos del artículo desde la base de datos
include('conexion.php');
// Actualizar la consulta para incluir el contenido del artículo
$sql_articulo = "SELECT titulo, resumen, contenido FROM Articulo WHERE id_articulo = ?";
$stmt_articulo = $pdo->prepare($sql_articulo);
$stmt_articulo->execute([$id_articulo]);
$articulo = $stmt_articulo->fetch();

// Depuración: Verificar si se recuperaron datos del artículo
if (!$articulo) {
    echo "<p style='color: red;'>Error: No se encontró el artículo con ID: $id_articulo.</p>";
    exit();
} else {
    echo "<p style='color: green;'>Datos del artículo recuperados correctamente.</p>";
}

$titulo = htmlspecialchars($articulo['titulo']);
$resumen = htmlspecialchars($articulo['resumen']);
// Asignar el contenido del artículo a una variable
$contenido = htmlspecialchars($articulo['contenido']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Procesar los datos enviados por el formulario
    $titulo = $_POST['titulo'] ?? '';
    $resumen = $_POST['resumen'] ?? '';
    $contenido = $_POST['contenido'] ?? '';

    // Validar los datos
    if (empty($titulo) || empty($resumen)) {
        echo "<p style='color: red;'>Error: Todos los campos son obligatorios.</p>";
    } else {
        // Guardar los datos en la base de datos
        $sql_update = "UPDATE Articulo SET titulo = ?, resumen = ?, contenido = ? WHERE id_articulo = ?";
        $stmt_update = $pdo->prepare($sql_update);
        $stmt_update->execute([$titulo, $resumen, $contenido, $id_articulo]);

        // Redirigir a la misma página con los datos actualizados
        header("Location: formulario_articulo.php?id_articulo=$id_articulo");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario de Artículo</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <h1>Formulario de Artículo</h1>
    <form action="formulario_articulo.php?id_articulo=<?php echo htmlspecialchars($id_articulo); ?>" method="POST">
        <input type="hidden" name="id_articulo" value="<?php echo htmlspecialchars($id_articulo); ?>">
        <label for="titulo">Título del Artículo:</label>
        <input type="text" id="titulo" name="titulo" value="<?php echo $titulo; ?>" required>
        <br>
        <label for="resumen">Resumen del Artículo:</label>
        <textarea id="resumen" name="resumen" rows="10" cols="50" required><?php echo $resumen; ?></textarea>
        <br>
        <label for="contenido">Contenido del Artículo:</label>
        <textarea id="contenido" name="contenido" rows="10" cols="50"><?php echo $contenido; ?></textarea>
        <br>
        <button type="submit">Enviar</button>
    </form>
</body>
</html>
