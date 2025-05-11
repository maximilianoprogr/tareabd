<?php
// Incluir la conexión a la base de datos
include('../php/conexion.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recibir datos del formulario
    $titulo = trim($_POST['titulo']);
    $resumen = trim($_POST['resumen']);
    $fecha_envio = $_POST['fecha_envio'];
    $topicos = $_POST['topicos'] ?? []; // Array de tópicos seleccionados
    $autores = $_POST['autores'] ?? []; // Array de autores
    $autor_contacto = $_POST['autor_contacto'] ?? null;

    // Validaciones
    if (empty($titulo)) {
        echo "<p style='color: red;'>El título es obligatorio.</p>";
        exit();
    }

    if (empty($autores) || !$autor_contacto) {
        echo "<p style='color: red;'>Debe haber al menos un autor y uno marcado como autor de contacto.</p>";
        exit();
    }

    if (empty($topicos)) {
        echo "<p style='color: red;'>Debe seleccionar al menos un tópico.</p>";
        exit();
    }

    if (count($autores) !== count(array_unique($autores))) {
        echo "<p style='color: red;'>No se permiten autores duplicados.</p>";
        exit();
    }

    // Validar que no existan duplicados en los tópicos seleccionados
    if (count($topicos) !== count(array_unique($topicos))) {
        echo "<p style='color: red;'>No se permiten tópicos duplicados.</p>";
        exit();
    }

    // Validar que el título no exceda los 255 caracteres
    if (strlen($titulo) > 255) {
        echo "<p style='color: red;'>El título no puede exceder los 255 caracteres.</p>";
        exit();
    }

    // Validar que el resumen no exceda los 500 caracteres
    if (strlen($resumen) > 500) {
        echo "<p style='color: red;'>El resumen no puede exceder los 500 caracteres.</p>";
        exit();
    }

    // Verificar si el título ya existe para alguno de los autores
    $sql_check = "SELECT COUNT(*) FROM Articulo a
                  JOIN Autor_Articulo aa ON a.id_articulo = aa.id_articulo
                  WHERE a.titulo = ? AND aa.rut_autor IN (" . implode(',', array_fill(0, count($autores), '?')) . ")";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute(array_merge([$titulo], $autores));
    if ($stmt_check->fetchColumn() > 0) {
        echo "<p style='color: red;'>El título ya existe para uno de los autores.</p>";
        exit();
    }

    // Insertar el artículo
    $sql = "INSERT INTO Articulo (titulo, resumen, fecha_envio, rut_autor) VALUES (?, ?, NOW(), ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$titulo, $resumen, $autor_contacto]);
    $id_articulo = $pdo->lastInsertId();

    // Insertar tópicos
    $sql_topicos = "INSERT INTO Articulo_Topico (id_articulo, id_topico) VALUES (?, ?)";
    $stmt_topicos = $pdo->prepare($sql_topicos);
    foreach ($topicos as $id_topico) {
        $stmt_topicos->execute([$id_articulo, $id_topico]);
    }

    // Insertar autores
    $sql_autores = "INSERT INTO Autor_Articulo (rut_autor, id_articulo) VALUES (?, ?)";
    $stmt_autores = $pdo->prepare($sql_autores);
    foreach ($autores as $rut_autor) {
        $stmt_autores->execute([$rut_autor, $id_articulo]);
    }

    // Simular envío de correo
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
    <!-- Formulario para crear un artículo -->
    <form method="POST" action="crear.php">
        <label for="titulo">Título:</label>
        <input type="text" id="titulo" name="titulo" required><br><br>

        <label for="resumen">Resumen:</label>
        <textarea id="resumen" name="resumen" required></textarea><br><br>

        <label for="fecha_envio">Fecha de Envío:</label>
        <input type="date" id="fecha_envio" name="fecha_envio" required><br><br>

        <label for="topicos">Tópicos:</label>
        <select id="topicos" name="topicos[]" multiple required>
            <!-- Aquí se deben agregar las opciones de tópicos -->
        </select><br><br>

        <label for="autores">Autores:</label>
        <select id="autores" name="autores[]" multiple required>
            <!-- Aquí se deben agregar las opciones de autores -->
        </select><br><br>

        <label for="autor_contacto">Autor de Contacto:</label>
        <select id="autor_contacto" name="autor_contacto" required>
            <!-- Aquí se deben agregar las opciones de autores -->
        </select><br><br>

        <input type="submit" value="Crear Artículo">
    </form>
    <a href="dashboard.php" style="font-family: Arial, sans-serif; font-size: 14px; color: #007BFF; text-decoration: none;">Volver al inicio</a>
</body>
</html>
