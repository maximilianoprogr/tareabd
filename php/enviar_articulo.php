<?php
session_start();
require_once 'conexion.php';

// Obtener tópicos
$topicos = $pdo->query("SELECT id_topico, nombre FROM Topico")->fetchAll();

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $resumen = trim($_POST['resumen'] ?? '');
    $ruts = $_POST['rut_autor'] ?? [];
    $es_contacto = $_POST['es_contacto'] ?? [];
    $topicos_seleccionados = $_POST['topicos'] ?? [];

    // Validaciones
    if ($titulo === '' || $resumen === '') {
        $message = "El título y el resumen son obligatorios.";
    } elseif (empty($ruts) || count(array_filter($ruts)) === 0) {
        $message = "Debe ingresar al menos un autor.";
    } elseif (empty($es_contacto) || count($es_contacto) === 0) {
        $message = "Debe marcar al menos un autor como contacto.";
    } elseif (empty($topicos_seleccionados)) {
        $message = "Debe seleccionar al menos un tópico.";
    } else {
        try {
            // Registrar autores en tabla Autor si no existen
            foreach ($ruts as $rut) {
                $rut = trim($rut);
                if ($rut === '') continue;
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM Autor WHERE rut = ?");
                $stmt->execute([$rut]);
                if ($stmt->fetchColumn() == 0) {
                    $pdo->prepare("INSERT INTO Autor (rut) VALUES (?)")->execute([$rut]);
                }
            }

            // Insertar artículo
            $stmt = $pdo->prepare("INSERT INTO Articulo (titulo, resumen, fecha_envio, rut_autor, estado) VALUES (?, ?, NOW(), ?, 'En revisión')");
            $stmt->execute([$titulo, $resumen, $ruts[0]]);
            $id_articulo = $pdo->lastInsertId();

            // Insertar autores del artículo
            foreach ($ruts as $i => $rut) {
                $rut = trim($rut);
                if ($rut === '') continue;
                $contacto = in_array($i, $es_contacto) ? 1 : 0;
                $pdo->prepare("INSERT INTO Autor_Articulo (id_articulo, rut_autor, es_contacto) VALUES (?, ?, ?)")
                    ->execute([$id_articulo, $rut, $contacto]);
            }

            // Insertar tópicos
            $stmt = $pdo->prepare("INSERT INTO Articulo_Topico (id_articulo, id_topico) VALUES (?, ?)");
            foreach ($topicos_seleccionados as $id_topico) {
                $stmt->execute([$id_articulo, $id_topico]);
            }

            $_SESSION['message'] = "Artículo enviado exitosamente.";
            header("Location: dashboard.php");
            exit();
        } catch (Exception $e) {
            $message = "Error al enviar el artículo: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Enviar Artículo</title>
    <link rel="stylesheet" href="">
</head>
<body>
<div class="container">
    <h1>Enviar Artículo</h1>
    <?php if ($message): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <form action="enviar_articulo.php" method="POST">
        <label for="titulo">Título:</label>
        <input type="text" id="titulo" name="titulo" required>

        <label for="resumen">Resumen:</label>
        <textarea id="resumen" name="resumen" required></textarea>

        <h2>Autores</h2>
        <div id="autores">
            <div class="autor-row">
                <input type="text" name="rut_autor[]" placeholder="RUT del autor" required>
                <label>
                    <input type="checkbox" name="es_contacto[]" value="0">
                    Es contacto
                </label>
            </div>
        </div>
        <button type="button" id="agregar-autor">Agregar otro autor</button>

        <h2>Tópicos</h2>
        <div>
            <?php foreach ($topicos as $topico): ?>
                <label>
                    <input type="checkbox" name="topicos[]" value="<?= $topico['id_topico'] ?>">
                    <?= htmlspecialchars($topico['nombre']) ?>
                </label><br>
            <?php endforeach; ?>
        </div>

        <button type="submit" class="btn">Enviar</button>
    </form>
    <a href="dashboard.php" class="back-link">Volver al inicio</a>
</div>

<script>
    document.getElementById('agregar-autor').onclick = function() {
        const autoresDiv = document.getElementById('autores');
        const index = autoresDiv.children.length;
        const div = document.createElement('div');
        div.className = 'autor-row';
        div.innerHTML = `
            <input type="text" name="rut_autor[]" placeholder="RUT del autor" required>
            <label>
                <input type="checkbox" name="es_contacto[]" value="${index}">
                Es contacto
            </label>
        `;
        autoresDiv.appendChild(div);
    };
</script>
</body>
</html>