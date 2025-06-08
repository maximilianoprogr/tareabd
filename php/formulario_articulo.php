<?php
// Inicia la sesión para manejar autenticación de usuarios
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php"); // Redirigir al login si no está autenticado
    exit(); // Finalizar la ejecución del script
}

// Verificar si se proporcionó un ID de artículo en la URL
if (!isset($_GET['id_articulo'])) {
    echo "<p style='color: red;'>No se proporcionó un ID de artículo.</p>"; // Mostrar mensaje de error
    exit(); // Finalizar la ejecución del script
}

$id_articulo = $_GET['id_articulo']; // Obtener el ID del artículo desde los parámetros GET

// Incluir el archivo de conexión a la base de datos
include('conexion.php');

// Consulta para obtener los datos del artículo por su ID
$sql_articulo = "SELECT titulo, resumen, contenido FROM Articulo WHERE id_articulo = ?";
$stmt_articulo = $pdo->prepare($sql_articulo);
$stmt_articulo->execute([$id_articulo]);
$articulo = $stmt_articulo->fetch(); // Obtener los datos del artículo

// Verificar si se encontraron datos del artículo
if (!$articulo) {
    echo "<p style='color: red;'>Error: No se encontró el artículo con ID: $id_articulo.</p>";
    exit();
} else {
    echo "<p style='color: green;'>Datos del artículo recuperados correctamente.</p>";
}

// Escapar los datos del artículo para prevenir XSS
$titulo = htmlspecialchars($articulo['titulo']);
$resumen = htmlspecialchars($articulo['resumen']);
$contenido = htmlspecialchars($articulo['contenido']);

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener los valores del formulario
    $titulo = $_POST['titulo'] ?? '';
    $resumen = $_POST['resumen'] ?? '';
    $contenido = $_POST['contenido'] ?? '';

    // Validar que los campos obligatorios no estén vacíos
    if (empty($titulo) || empty($resumen)) {
        echo "<p style='color: red;'>Error: Todos los campos son obligatorios.</p>";
    } else {
        // Actualizar los datos del artículo en la base de datos
        $sql_update = "UPDATE Articulo SET titulo = ?, resumen = ?, contenido = ? WHERE id_articulo = ?";
        $stmt_update = $pdo->prepare($sql_update);
        $stmt_update->execute([$titulo, $resumen, $contenido, $id_articulo]);

        // Redirigir a la misma página para mostrar los datos actualizados
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
