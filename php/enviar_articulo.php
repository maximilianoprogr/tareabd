<?php
session_start();
require_once 'conexion.php';

$topicos = $pdo->query("SELECT id_topico, nombre FROM Topico")->fetchAll();

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $resumen = trim($_POST['resumen'] ?? '');
    $ruts = $_POST['rut_autor'] ?? [];
    $es_contacto = $_POST['es_contacto'] ?? [];
    $topicos_seleccionados = $_POST['topicos'] ?? [];

    if ($titulo === '') {
        $message = "El título es obligatorio.";
    } elseif (empty($ruts) || count(array_filter($ruts)) === 0) {
        $message = "Debe ingresar al menos un autor.";
    } elseif (empty($es_contacto) || count($es_contacto) === 0) {
        $message = "Debe marcar al menos un autor como contacto.";
    } elseif (empty($topicos_seleccionados)) {
        $message = "Debe seleccionar al menos un tópico.";
    } else {
        try {
            foreach ($ruts as $rut) {
                $rut = trim($rut);
                if ($rut === '') continue;

                $stmt = $pdo->prepare("SELECT COUNT(*) FROM Usuario WHERE rut = ?");
                $stmt->execute([$rut]);
                if ($stmt->fetchColumn() == 0) {
                    $message = "El RUT $rut no está registrado en el sistema. Por favor, regístrelo primero.";
                    break;
                }

                $stmt = $pdo->prepare("SELECT COUNT(*) FROM Autor WHERE rut = ?");
                $stmt->execute([$rut]);
                if ($stmt->fetchColumn() == 0) {
                    $pdo->prepare("INSERT INTO Autor (rut) VALUES (?)")->execute([$rut]);
                }
            }

            $stmt = $pdo->prepare("SELECT COUNT(*) FROM Articulo a JOIN Autor_Articulo aa ON a.id_articulo = aa.id_articulo WHERE a.titulo = ? AND aa.rut_autor IN (" . implode(',', array_fill(0, count($ruts), '?')) . ")");
            $stmt->execute(array_merge([$titulo], $ruts));
            if ($stmt->fetchColumn() > 0) {
                $message = "El título del artículo ya existe para uno de los autores.";
                throw new Exception($message);
            }

            if (count($ruts) !== count(array_unique($ruts))) {
                $message = "No se pueden repetir nombres de autores.";
                throw new Exception($message);
            }

            $stmt = $pdo->prepare("SELECT COUNT(*) FROM Autor WHERE rut = ?");
            $stmt->execute([$ruts[0]]);
            if ($stmt->fetchColumn() == 0) {
                $message = "El RUT del autor principal ($ruts[0]) no está registrado como Autor. Por favor, regístrelo primero.";
                throw new Exception($message);
            }

            $stmt = $pdo->prepare("INSERT INTO Articulo (titulo, resumen, fecha_envio, rut_autor, estado) VALUES (?, ?, NOW(), ?, 'En revisión')");
            $stmt->execute([$titulo, $resumen, $ruts[0]]);
            $id_articulo = $pdo->lastInsertId();

            foreach ($ruts as $i => $rut) {
                $rut = trim($rut);
                if ($rut === '') continue;
                $contacto = in_array($i, $es_contacto) ? 1 : 0;
                $pdo->prepare("INSERT INTO Autor_Articulo (id_articulo, rut_autor, es_contacto) VALUES (?, ?, ?)")
                    ->execute([$id_articulo, $rut, $contacto]);
            }

            $stmt = $pdo->prepare("INSERT INTO Articulo_Topico (id_articulo, id_topico) VALUES (?, ?)");
            foreach ($topicos_seleccionados as $id_topico) {
                $stmt->execute([$id_articulo, $id_topico]);
            }

            $_SESSION['message'] = "Artículo enviado exitosamente.";
            header("Location: dashboard.php");
            exit();
        } catch (PDOException $e) {
            if ($e->getCode() === '23000' && strpos($e->getMessage(), 'titulo') !== false) {
                $message = "Ya existe un artículo con ese título. Por favor, elige otro.";
            } else {
                $message = "Error al enviar el artículo: " . $e->getMessage();
            }
        } catch (Exception $e) {
            $message = $e->getMessage(); 
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
        <textarea id="resumen" name="resumen"></textarea>

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