<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

// Verificar si se enviaron los datos del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        echo "<p style='color: red;'>Error: Todos los campos son obligatorios.</p>";
        exit();
    }

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
        
        // Verificar duplicados antes de insertar en Articulo_Topico
        $sql_check_articulo_topico = "SELECT COUNT(*) FROM Articulo_Topico WHERE id_articulo = ? AND id_topico = ?";
        $stmt_check_articulo_topico = $pdo->prepare($sql_check_articulo_topico);

        foreach ($topico_ids as $id_topico) {
            $stmt_check_articulo_topico->execute([$id_articulo, $id_topico]);
            if ($stmt_check_articulo_topico->fetchColumn() == 0) {
                // Insertar solo si no existe
                $stmt_topico->execute([$id_articulo, $id_topico]);
            }
        }

        echo "<p style='color: green;'>Artículo enviado exitosamente.</p>";
        
        // Redirigir al usuario de vuelta al formulario con los datos llenados
        if (!headers_sent()) {
            header("Location: formulario_articulo.php?id_articulo=$id_articulo");
            exit();
        } else {
            echo "<script>window.location.href='formulario_articulo.php?id_articulo=$id_articulo';</script>";
            exit();
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>Error al enviar el artículo: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color: red;'>Método de solicitud no válido.</p>";
}

?>
