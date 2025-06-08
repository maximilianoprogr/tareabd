<?php
// Incluir el archivo de conexión a la base de datos
include('../php/conexion.php');

// Verificar si el formulario fue enviado mediante POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $titulo = trim($_POST['titulo']); // Obtener y limpiar el título del artículo
    $resumen = trim($_POST['resumen']); // Obtener y limpiar el resumen del artículo
    $fecha_envio = $_POST['fecha_envio']; // Obtener la fecha de envío
    $topicos = $_POST['topicos'] ?? []; // Obtener los tópicos seleccionados
    $autores = $_POST['autores'] ?? []; // Obtener los autores ingresados
    $autor_contacto = $_POST['autor_contacto'] ?? null; // Obtener el autor de contacto

    // Validar que el título no esté vacío
    if (empty($titulo)) {
        echo "<p style='color: red;'>El título es obligatorio.</p>";
        exit(); // Finalizar la ejecución del script
    }

    // Validar que haya al menos un autor y uno marcado como contacto
    if (empty($autores) || !$autor_contacto) {
        echo "<p style='color: red;'>Debe haber al menos un autor y uno marcado como autor de contacto.</p>";
        exit(); // Finalizar la ejecución del script
    }

    // Validar que se haya seleccionado al menos un tópico
    if (empty($topicos)) {
        echo "<p style='color: red;'>Debe seleccionar al menos un tópico.</p>";
        exit(); // Finalizar la ejecución del script
    }

    // Validar que no haya autores duplicados
    if (count($autores) !== count(array_unique($autores))) {
        echo "<p style='color: red;'>No se permiten autores duplicados.</p>";
        exit(); // Finalizar la ejecución del script
    }

    // Validar que no haya tópicos duplicados
    if (count($topicos) !== count(array_unique($topicos))) {
        echo "<p style='color: red;'>No se permiten tópicos duplicados.</p>";
        exit(); // Finalizar la ejecución del script
    }

    // Validar que el título no exceda los 255 caracteres
    if (strlen($titulo) > 255) {
        echo "<p style='color: red;'>El título no puede exceder los 255 caracteres.</p>";
        exit(); // Finalizar la ejecución del script
    }

    // Validar que el resumen no exceda los 500 caracteres
    if (strlen($resumen) > 500) {
        echo "<p style='color: red;'>El resumen no puede exceder los 500 caracteres.</p>";
        exit(); // Finalizar la ejecución del script
    }

    // Verificar si el título ya existe para uno de los autores
    $sql_check = "SELECT COUNT(*) FROM Articulo a
                  JOIN Autor_Articulo aa ON a.id_articulo = aa.id_articulo
                  WHERE a.titulo = ? AND aa.rut_autor IN (" . implode(',', array_fill(0, count($autores), '?')) . ")";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute(array_merge([$titulo], $autores));
    if ($stmt_check->fetchColumn() > 0) {
        echo "<p style='color: red;'>El título ya existe para uno de los autores.</p>";
        exit(); // Finalizar la ejecución del script
    }

    // Insertar el artículo en la base de datos
    $sql = "INSERT INTO Articulo (titulo, resumen, fecha_envio, rut_autor) VALUES (?, ?, NOW(), ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$titulo, $resumen, $autor_contacto]);
    $id_articulo = $pdo->lastInsertId(); // Obtener el ID del artículo recién insertado

    // Asociar los tópicos seleccionados con el artículo
    $sql_topicos = "INSERT INTO Articulo_Topico (id_articulo, id_topico) VALUES (?, ?)";
    $stmt_topicos = $pdo->prepare($sql_topicos);
    foreach ($topicos as $id_topico) {
        $stmt_topicos->execute([$id_articulo, $id_topico]);
    }

    // Asociar los autores con el artículo
    $sql_autores = "INSERT INTO Autor_Articulo (rut_autor, id_articulo) VALUES (?, ?)";
    $stmt_autores = $pdo->prepare($sql_autores);
    foreach ($autores as $rut_autor) {
        $stmt_autores->execute([$rut_autor, $id_articulo]);
    }

    // Mostrar un mensaje de éxito y enviar un correo al autor de contacto
    echo "<script>alert('Artículo enviado correctamente. Se ha enviado un correo al autor de contacto.');</script>";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Artículo</title>
</head>
<body>
    <h1>Crear Artículo</h1>
    <form method="POST" action="crear.php">
        <label for="titulo">Título:</label>
        <input type="text" id="titulo" name="titulo" required><br><br>

        <label for="resumen">Resumen:</label>
        <textarea id="resumen" name="resumen" required></textarea><br><br>

        <label for="fecha_envio">Fecha de Envío:</label>
        <input type="date" id="fecha_envio" name="fecha_envio" required><br><br>

        <label for="topicos">Tópicos:</label>
        <select id="topicos" name="topicos[]" multiple required>
            
        </select><br><br>

        <label for="autores">Autores:</label>
        <select id="autores" name="autores[]" multiple required>
        </select><br><br>

        <label for="autor_contacto">Autor de Contacto:</label>
        <select id="autor_contacto" name="autor_contacto" required>
        </select><br><br>

        <input type="submit" value="Crear Artículo">
    </form>
    <a href="dashboard.php" style="font-family: Arial, sans-serif; font-size: 14px; color: #007BFF; text-decoration: none;">Volver al inicio</a>
</body>
</html>
