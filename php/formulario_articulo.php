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

// Aquí puedes agregar la lógica para obtener los datos del artículo y mostrarlos en el formulario
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
    <form action="procesar_envio.php" method="POST">
        <input type="hidden" name="id_articulo" value="<?php echo htmlspecialchars($id_articulo); ?>">
        <label for="contenido">Contenido del Artículo:</label>
        <textarea id="contenido" name="contenido" rows="10" cols="50"></textarea>
        <br>
        <button type="submit">Enviar</button>
    </form>
</body>
</html>
