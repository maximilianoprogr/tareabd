<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

$message = ""; // Variable para mostrar mensajes

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
            <div class="autores">
                <div class="autor">
                    <input type="text" name="autor_nombre[]" placeholder="Nombre" required>
                    <input type="email" name="autor_email[]" placeholder="Email" required>
                    <input type="text" name="autor_contacto[]" placeholder="Contacto" required>
                </div>
                <div class="autor">
                    <input type="text" name="autor_nombre[]" placeholder="Nombre">
                    <input type="email" name="autor_email[]" placeholder="Email">
                    <input type="text" name="autor_contacto[]" placeholder="Contacto">
                </div>
            </div>

            <h2>Tópicos del Artículo</h2>
            <div class="topicos">
                <input type="text" name="topico1" placeholder="Tópico 1" required>
                <input type="text" name="topico2" placeholder="Tópico 2" required>
                <input type="text" name="topico3" placeholder="Tópico 3" required>
            </div>

            <button type="submit" class="btn">Enviar</button>
        </form>
        <a href="dashboard.php" class="back-link">Volver al inicio</a>
    </div>
</body>
</html>