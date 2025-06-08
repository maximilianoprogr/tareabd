<?php
// Inicia la sesión para manejar autenticación de usuarios
session_start();

// Incluir el archivo de conexión a la base de datos
require_once 'conexion.php';

// Obtener todos los tópicos disponibles desde la base de datos
$topicos = $pdo->query("SELECT id_topico, nombre FROM Topico")->fetchAll();

// Inicializar mensaje vacío para mostrar errores o confirmaciones
$message = "";

// Verificar si el formulario fue enviado mediante POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? ''); // Obtener y limpiar el título del artículo
    $resumen = trim($_POST['resumen'] ?? ''); // Obtener y limpiar el resumen del artículo
    $ruts = $_POST['rut_autor'] ?? []; // Obtener los RUTs de los autores
    $es_contacto = $_POST['es_contacto'] ?? []; // Obtener los autores marcados como contacto
    $topicos_seleccionados = $_POST['topicos'] ?? []; // Obtener los tópicos seleccionados

    // Validar que el título no esté vacío
    if ($titulo === '') {
        $message = "El título es obligatorio.";
    } elseif (empty($ruts) || count(array_filter($ruts)) === 0) {
        // Validar que al menos un autor haya sido ingresado
        $message = "Debe ingresar al menos un autor.";
    } elseif (empty($es_contacto) || count($es_contacto) === 0) {
        // Validar que al menos un autor haya sido marcado como contacto
        $message = "Debe marcar al menos un autor como contacto.";
    } elseif (empty($topicos_seleccionados)) {
        // Validar que al menos un tópico haya sido seleccionado
        $message = "Debe seleccionar al menos un tópico.";
    } else {
        try {
            foreach ($ruts as $rut) {
                $rut = trim($rut);
                if ($rut === '') continue;

                // Verificar si el RUT está registrado en la tabla Usuario
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM Usuario WHERE rut = ?");
                $stmt->execute([$rut]);
                if ($stmt->fetchColumn() == 0) {
                    $message = "El RUT $rut no está registrado en el sistema. Por favor, regístrelo primero.";
                    break;
                }

                // Verificar si el RUT está registrado en la tabla Autor
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM Autor WHERE rut = ?");
                $stmt->execute([$rut]);
                if ($stmt->fetchColumn() == 0) {
                    // Si no está registrado, agregarlo a la tabla Autor
                    $pdo->prepare("INSERT INTO Autor (rut) VALUES (?)")->execute([$rut]);
                }
            }

            // Verificar si ya existe un artículo con el mismo título para los mismos autores
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM Articulo a JOIN Autor_Articulo aa ON a.id_articulo = aa.id_articulo WHERE a.titulo = ? AND aa.rut_autor IN (" . implode(',', array_fill(0, count($ruts), '?')) . ")");
            $stmt->execute(array_merge([$titulo], $ruts));
            if ($stmt->fetchColumn() > 0) {
                $message = "El título del artículo ya existe para uno de los autores.";
                throw new Exception($message);
            }

            // Verificar si hay RUTs de autores duplicados
            if (count($ruts) !== count(array_unique($ruts))) {
                $message = "No se pueden repetir nombres de autores.";
                throw new Exception($message);
            }

            // Elimina la verificación de autor principal en Articulo, ya no es necesario

            // Insertar el artículo SIN rut_autor
            $stmt = $pdo->prepare("INSERT INTO Articulo (titulo, resumen, fecha_envio, estado) VALUES (?, ?, NOW(), 'En revisión')");
            $stmt->execute([$titulo, $resumen]);
            $id_articulo = $pdo->lastInsertId();

            foreach ($ruts as $i => $rut) {
                $rut = trim($rut);
                if ($rut === '') continue;
                $contacto = in_array($i, $es_contacto) ? 1 : 0;
                // Asociar autores al artículo en la tabla Autor_Articulo
                $pdo->prepare("INSERT INTO Autor_Articulo (id_articulo, rut_autor, es_contacto) VALUES (?, ?, ?)")
                    ->execute([$id_articulo, $rut, $contacto]);
            }

            // Asociar tópicos al artículo en la tabla Articulo_Topico
            $stmt = $pdo->prepare("INSERT INTO Articulo_Topico (id_articulo, id_topico) VALUES (?, ?)");
            foreach ($topicos_seleccionados as $id_topico) {
                $stmt->execute([$id_articulo, $id_topico]);
            }

            // Redirigir a la página del dashboard con un mensaje de éxito
            $_SESSION['message'] = "Artículo enviado exitosamente.";
            header("Location: dashboard.php");
            exit();
        } catch (PDOException $e) {
            // Manejo de errores de base de datos
            if ($e->getCode() === '23000' && strpos($e->getMessage(), 'titulo') !== false) {
                $message = "Ya existe un artículo con ese título. Por favor, elige otro.";
            } else {
                $message = "Error al enviar el artículo: " . $e->getMessage();
            }
        } catch (Exception $e) {
            // Manejo de excepciones generales
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