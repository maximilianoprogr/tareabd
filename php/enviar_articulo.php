<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

require_once 'conexion.php';

// Obtener los tópicos desde la base de datos
$stmt = $pdo->query("SELECT id_topico, nombre FROM Topico");
$topicos = $stmt->fetchAll();

$message = ""; // Variable para mostrar mensajes
$autores = []; // Inicializar la variable para evitar errores

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener los datos del formulario
    $titulo = $_POST['titulo'] ?? '';
    $resumen = $_POST['resumen'] ?? '';
    $autores = $_POST['autor_nombre'] ?? [];
    $topicos = [
        $_POST['topico1'] ?? '',
        $_POST['topico2'] ?? '',
        $_POST['topico3'] ?? ''
    ];

    // Validar los datos
    if (empty($titulo) || empty($resumen) || empty($autores)) {
        $message = "<p class='error-message'>Error: Todos los campos son obligatorios.</p>";
    } else {
        // Conectar a la base de datos
        include('conexion.php');

        try {
            // Verificar si el usuario está registrado como autor
            $sql_check_autor = "SELECT COUNT(*) FROM Autor WHERE rut = ?";
            $stmt_check_autor = $pdo->prepare($sql_check_autor);
            $stmt_check_autor->execute([$_SESSION['usuario']]);
            if ($stmt_check_autor->fetchColumn() == 0) {
                // Registrar al usuario como autor si no existe
                $sql_insert_autor = "INSERT INTO Autor (rut) VALUES (?)";
                $stmt_insert_autor = $pdo->prepare($sql_insert_autor);
                $stmt_insert_autor->execute([$_SESSION['usuario']]);
            }

            // Insertar el artículo en la base de datos
            $sql_articulo = "INSERT INTO Articulo (titulo, resumen, fecha_envio, rut_autor, estado) VALUES (?, ?, NOW(), ?, 'En revisión')";
            $stmt_articulo = $pdo->prepare($sql_articulo);
            $stmt_articulo->execute([$titulo, $resumen, $_SESSION['usuario']]);

            $id_articulo = $pdo->lastInsertId();

            // Verificar y agregar los tópicos por nombre si no existen
            $sql_check_topico = "SELECT id_topico FROM Topico WHERE nombre = ?";
            $sql_insert_topico = "INSERT INTO Topico (nombre) VALUES (?)";
            $stmt_check_topico = $pdo->prepare($sql_check_topico);
            $stmt_insert_topico = $pdo->prepare($sql_insert_topico);

            $topico_ids = [];
            foreach ($topicos as $topico) {
                if (!empty($topico)) {
                    $stmt_check_topico->execute([$topico]);
                    $id_topico = $stmt_check_topico->fetchColumn();
                    if (!$id_topico) {
                        // Insertar el tópico si no existe
                        $stmt_insert_topico->execute([$topico]);
                        $id_topico = $pdo->lastInsertId();
                    }
                    $topico_ids[] = $id_topico;
                }
            }

            // Insertar los tópicos en Articulo_Topico
            $sql_topico = "INSERT INTO Articulo_Topico (id_articulo, id_topico) VALUES (?, ?)";
            $stmt_topico = $pdo->prepare($sql_topico);

            foreach ($topico_ids as $id_topico) {
                $stmt_topico->execute([$id_articulo, $id_topico]);
            }

            $message = "<p class='success-message'>Artículo enviado exitosamente.</p>";
        } catch (Exception $e) {
            $message = "<p class='error-message'>Error al enviar el artículo: " . $e->getMessage() . "</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enviar Artículo</title>
    <link rel="stylesheet" href="../css/enviar_articulo.css"> <!-- Archivo CSS externo -->
</head>
<body>
    <div class="container">
        <h1>Enviar Artículo</h1>
        <?php if (!empty($message)): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>
        <form action="enviar_articulo.php" method="POST" enctype="multipart/form-data">
            <label for="titulo">Título del artículo:</label>
            <input type="text" id="titulo" name="titulo" required>

            <label for="resumen">Resumen del artículo:</label>
            <textarea id="resumen" name="resumen" rows="4" required></textarea>

            <h2>Autores</h2>
            <div class="autores" id="autores-container">
                <?php foreach ($autores as $autor): ?>
                    <div>
                        <input type="checkbox" id="autor_<?php echo $autor['id_autor']; ?>" name="autores[]" value="<?php echo $autor['id_autor']; ?>">
                        <label for="autor_<?php echo $autor['id_autor']; ?>"> <?php echo htmlspecialchars($autor['nombre']); ?> (<?php echo htmlspecialchars($autor['email']); ?>, <?php echo htmlspecialchars($autor['contacto']); ?>) </label>
                    </div>
                <?php endforeach; ?>
            </div>

            <div>
                <h3>Crear Nuevo Autor</h3>
                <div id="nuevo-autor-template" style="display: none;">
                    <label for="nuevo_autor_nombre">Nombre:</label>
                    <input type="text" name="nuevo_autor_nombre[]" placeholder="Nombre">

                    <label for="nuevo_autor_email">Email:</label>
                    <input type="email" name="nuevo_autor_email[]" placeholder="Email">

                    <label for="nuevo_autor_contacto">Contacto:</label>
                    <input type="text" name="nuevo_autor_contacto[]" placeholder="Contacto">
                </div>
                <div id="nuevo-autor-container">
                    <div>
                        <label for="nuevo_autor_nombre">Nombre:</label>
                        <input type="text" name="nuevo_autor_nombre[]" placeholder="Nombre">

                        <label for="nuevo_autor_email">Email:</label>
                        <input type="email" name="nuevo_autor_email[]" placeholder="Email">

                        <label for="nuevo_autor_contacto">Contacto:</label>
                        <input type="text" name="nuevo_autor_contacto[]" placeholder="Contacto">
                    </div>
                </div>
                <button type="button" id="agregar-otro-autor" onclick="agregarOtroAutor(); return false;">Agregar Otro</button>
            </div>

            <h2>Tópicos del Artículo</h2>
            <div class="topicos">
                <?php foreach ($topicos as $topico): ?>
                    <div>
                        <input type="checkbox" id="topico_<?php echo $topico['id_topico']; ?>" name="topicos[]" value="<?php echo $topico['id_topico']; ?>">
                        <label for="topico_<?php echo $topico['id_topico']; ?>"> <?php echo htmlspecialchars($topico['nombre']); ?> </label>
                    </div>
                <?php endforeach; ?>
            </div>

            <button type="submit" class="btn">Enviar</button>
        </form>
        <a href="dashboard.php" class="back-link">Volver al inicio</a>
    </div>

    <script>
    let autorCount = 1; // Contador para los autores

    function agregarOtroAutor() {
        autorCount++;
        const container = document.getElementById('nuevo-autor-container');
        const template = document.getElementById('nuevo-autor-template').innerHTML;
        const div = document.createElement('div');
        div.innerHTML = template.replace(/Nuevo Autor/g, `Autor ${autorCount}`);
        div.querySelectorAll('label').forEach((label) => {
            const forAttribute = label.getAttribute('for');
            if (forAttribute) {
                label.setAttribute('for', `${forAttribute}_${autorCount}`);
            }
        });
        div.querySelectorAll('input').forEach((input) => {
            const idAttribute = input.getAttribute('id');
            if (idAttribute) {
                input.setAttribute('id', `${idAttribute}_${autorCount}`);
            }
        });
        container.appendChild(div);
    }

    // Asegurar que los campos de título y resumen mantengan sus valores
    const tituloInput = document.getElementById('titulo');
    const resumenTextarea = document.getElementById('resumen');

    document.getElementById('agregar-otro-autor').addEventListener('click', (event) => {
        event.preventDefault();
        agregarOtroAutor();

        // Mantener los valores de título y resumen
        tituloInput.value = tituloInput.value;
        resumenTextarea.value = resumenTextarea.value;
    });
    </script>
</body>
</html>