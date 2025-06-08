<?php
// Iniciar sesión para manejar autenticación de usuarios
session_start();

// Incluir archivo de conexión a la base de datos
include('../conexion.php');

// Obtener el RUT del revisor desde los parámetros GET
$rut_revisor = $_GET['rut_revisor'] ?? null;
if (!$rut_revisor) {
    echo "Falta el rut del revisor."; // Mostrar mensaje de error si no se proporciona el RUT
    exit;
}

// Consultar los datos del revisor y sus tópicos
$stmt = $pdo->prepare("
    SELECT u.nombre, GROUP_CONCAT(DISTINCT t.nombre SEPARATOR ', ') AS topicos
    FROM Usuario u
    LEFT JOIN Revisor_Topico rt ON u.rut = rt.rut_revisor
    LEFT JOIN Topico t ON rt.id_topico = t.id_topico
    WHERE u.rut = ?
    GROUP BY u.rut, u.nombre
");
$stmt->execute([$rut_revisor]);
$revisor = $stmt->fetch();
if (!$revisor) {
    echo "Revisor no encontrado."; // Mostrar mensaje de error si el revisor no existe
    exit;
}

$msg = ''; // Variable para almacenar mensajes de éxito o error

// Procesar la eliminación de un artículo asignado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_articulo']) && isset($_POST['quitar'])) {
    $id_articulo = $_POST['id_articulo'];
    $stmt = $pdo->prepare("DELETE FROM Articulo_Revisor WHERE id_articulo = ? AND rut_revisor = ?");
    $stmt->execute([$id_articulo, $rut_revisor]);
    header("Location: asignar_articulo.php?rut_revisor=" . urlencode($rut_revisor) . "&msg=Artículo+quitado+correctamente");
    exit;
}

// Procesar la asignación de un artículo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_articulo']) && !isset($_POST['quitar'])) {
    $id_articulo = $_POST['id_articulo'];
   
    // Verificar si el artículo ya está asignado al revisor
    $stmt = $pdo->prepare("SELECT 1 FROM Articulo_Revisor WHERE id_articulo = ? AND rut_revisor = ?");
    $stmt->execute([$id_articulo, $rut_revisor]);
    if ($stmt->fetch()) {
        $msg = "El artículo ya está asignado a este revisor.";
    } else {
        // Verificar coincidencia de tópicos entre el artículo y el revisor
        $stmt = $pdo->prepare("
            SELECT GROUP_CONCAT(DISTINCT t.nombre SEPARATOR ', ') AS topicos
            FROM Articulo_Topico at
            LEFT JOIN Topico t ON at.id_topico = t.id_topico
            WHERE at.id_articulo = ?
        ");
        $stmt->execute([$id_articulo]);
        $row = $stmt->fetch();
        $topicos_articulo = array_filter(array_map('trim', preg_split('/,\s*/', $row['topicos'] ?? '')));
        
        $topicos_revisor = array_filter(array_map('trim', preg_split('/,\s*/', $revisor['topicos'] ?? '')));
        $hay_coincidencia = false;
        foreach ($topicos_revisor as $topico) {
            if ($topico !== '' && in_array($topico, $topicos_articulo, true)) {
                $hay_coincidencia = true;
                break;
            }
        }
        $stmt = $pdo->prepare("INSERT INTO Articulo_Revisor (id_articulo, rut_revisor) VALUES (?, ?)");
        $stmt->execute([$id_articulo, $rut_revisor]);
        if (!$hay_coincidencia) {
            $msg = "Artículo asignado correctamente, pero este revisor NO tiene especialidad en el tópico del artículo.";
        } else {
            $msg = "Artículo asignado correctamente.";
        }
    }
    header("Location: asignar_articulo.php?rut_revisor=" . urlencode($rut_revisor) . "&msg=" . urlencode($msg));
    exit;
}

// Consultar la lista de artículos y sus tópicos
$stmt = $pdo->query("
    SELECT a.id_articulo, a.titulo, GROUP_CONCAT(DISTINCT t.nombre SEPARATOR ', ') AS topicos
    FROM Articulo a
    LEFT JOIN Articulo_Topico at ON a.id_articulo = at.id_articulo
    LEFT JOIN Topico t ON at.id_topico = t.id_topico
    GROUP BY a.id_articulo, a.titulo
    ORDER BY a.id_articulo
");
$articulos = $stmt->fetchAll();

// Consultar los artículos asignados al revisor
$stmt = $pdo->prepare("SELECT id_articulo FROM Articulo_Revisor WHERE rut_revisor = ?");
$stmt->execute([$rut_revisor]);
$articulos_asignados = $stmt->fetchAll(PDO::FETCH_COLUMN);

if (isset($_GET['msg'])) {
    $msg = $_GET['msg'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"> <!-- Configuración de codificación de caracteres -->
    <title>Asignar artículo a revisor</title> <!-- Título de la página -->
    <link rel="stylesheet" href="../../css/estilos_articulos.css"> <!-- Enlace al archivo de estilos CSS -->
</head>
<body>
    <a href="../asignar_articulos.php" style="display:inline-block;padding:8px 16px;background:#007BFF;color:#fff;text-decoration:none;border-radius:4px;margin-bottom:16px;">Volver a artículos</a> <!-- Enlace para volver a la lista de artículos -->
    <h2>Asignar artículo a: <?= htmlspecialchars($revisor['nombre']) ?></h2> <!-- Título de la página con el nombre del revisor -->
    <p><strong>Tópicos del revisor:</strong><br>
    <?= $revisor['topicos'] ? nl2br(htmlspecialchars(str_replace(', ', "\n", $revisor['topicos']))) : 'Sin tópicos'; ?>
    </p>
    <?php if ($msg): ?>
        <?php
        if (strpos($msg, 'NO tiene especialidad') !== false) {
            $clase = 'mensaje-especialidad';
        } elseif (strpos($msg, 'quitado correctamente') !== false) {
            $clase = 'mensaje-peligro';
        } elseif (strpos($msg, 'ya está asignado') !== false) {
            $clase = 'mensaje-advertencia';
        } else {
            $clase = 'mensaje-exito';
        }
        ?>
        <p class="<?= $clase ?>"><?= htmlspecialchars($msg) ?></p> <!-- Mensaje de éxito o error -->
    <?php endif; ?>
    <table border="1" cellpadding="6" style="border-collapse:collapse;width:100%"> <!-- Tabla para mostrar los artículos -->
        <tr style="background:#f2f2f2">
            <th>ID</th> <!-- Encabezado de columna para el ID del artículo -->
            <th>Título</th> <!-- Encabezado de columna para el título del artículo -->
            <th>Tópicos</th> <!-- Encabezado de columna para los tópicos del artículo -->
            <th>Acción</th> <!-- Encabezado de columna para las acciones disponibles -->
        </tr>
        <?php foreach ($articulos as $articulo): 
            $topicos_articulo = array_filter(array_map('trim', preg_split('/,\s*/', $articulo['topicos'] ?? '')));
            $topicos_revisor = array_filter(array_map('trim', preg_split('/,\s*/', $revisor['topicos'] ?? '')));
            $hay_coincidencia = false;
            foreach ($topicos_revisor as $topico) {
                if ($topico !== '' && in_array($topico, $topicos_articulo, true)) {
                    $hay_coincidencia = true;
                    break;
                }
            }
            $clase_tr = $hay_coincidencia ? 'fila-coincide-topico' : '';
        ?>
        <tr class="<?= $clase_tr ?>"> <!-- Fila de la tabla con clase según coincidencia de tópicos -->
            <td><?= htmlspecialchars($articulo['id_articulo']) ?></td> <!-- Mostrar el ID del artículo -->
            <td><?= htmlspecialchars($articulo['titulo']) ?></td> <!-- Mostrar el título del artículo -->
            <td>
                <?= $articulo['topicos'] ? nl2br(htmlspecialchars(str_replace(', ', "\n", $articulo['topicos']))) : 'Sin tópicos'; ?> <!-- Mostrar los tópicos del artículo -->
            </td>
            <td>
                <?php if (in_array($articulo['id_articulo'], $articulos_asignados)): ?>
                    <form method="post" style="display:inline;"> <!-- Formulario para quitar la asignación del artículo -->
                        <input type="hidden" name="id_articulo" value="<?= htmlspecialchars($articulo['id_articulo']) ?>">
                        <input type="hidden" name="quitar" value="1">
                        <button type="submit" style="background:#dc3545;color:#fff;">Quitar</button> <!-- Botón para quitar la asignación -->
                    </form>
                <?php else: ?>
                    <form method="post" style="display:inline;"> <!-- Formulario para asignar el artículo -->
                        <input type="hidden" name="id_articulo" value="<?= htmlspecialchars($articulo['id_articulo']) ?>">
                        <button type="submit">Asignar</button> <!-- Botón para asignar el artículo -->
                    </form>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>