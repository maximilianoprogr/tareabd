<?php
// Verificar si el usuario tiene permisos de jefe del comité
session_start();

// Mensaje de depuración para verificar el rol en la sesión
if (!isset($_SESSION['rol'])) {
    echo "<p style='color: red;'>Error: No se encontró el rol en la sesión.</p>";
} else {
    echo "<p style='color: blue;'>Rol actual en la sesión: " . htmlspecialchars($_SESSION['rol']) . "</p>";
}

// Eliminar mensajes visibles antes de la redirección
if (!isset($_SESSION['rol']) || ($_SESSION['rol'] !== 'admin' && $_SESSION['rol'] !== 'Jefe Comite de Programa')) {
    header("Location: ../php/dashboard.php");
    exit();
}

include('../php/conexion.php');

// Asignación manual de artículos a revisores
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_articulo'], $_POST['rut_revisor'])) {
    $id_articulo = $_POST['id_articulo'];
    $rut_revisor = $_POST['rut_revisor'];

    // Verificar que el revisor no sea autor del artículo
    $sql_check = "SELECT COUNT(*) FROM Autor_Articulo WHERE id_articulo = ? AND rut_autor = ?";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute([$id_articulo, $rut_revisor]);
    if ($stmt_check->fetchColumn() > 0) {
        echo "<p style='color: red;'>No se puede asignar un artículo a un revisor que sea autor.</p>";
    } else {
        // Verificar si los tópicos del artículo coinciden con los del revisor
        $sql_topicos = "SELECT COUNT(*) FROM Articulo_Topico at
                        JOIN Revisor_Topico rt ON at.id_topico = rt.id_topico
                        WHERE at.id_articulo = ? AND rt.rut_revisor = ?";
        $stmt_topicos = $pdo->prepare($sql_topicos);
        $stmt_topicos->execute([$id_articulo, $rut_revisor]);
        $coincidencias = $stmt_topicos->fetchColumn();

        if ($coincidencias == 0) {
            echo "<p style='color: orange;'>Advertencia: Los tópicos del artículo no coinciden con los del revisor seleccionado.</p>";
        }

        // Verificar si ya existe la asignación antes de insertar
        $sql_check_duplicate = "SELECT COUNT(*) FROM Articulo_Revisor WHERE id_articulo = ? AND rut_revisor = ?";
        $stmt_check_duplicate = $pdo->prepare($sql_check_duplicate);
        $stmt_check_duplicate->execute([$id_articulo, $rut_revisor]);
        if ($stmt_check_duplicate->fetchColumn() > 0) {
            echo "<p style='color: red;'>Error: El artículo ya está asignado a este revisor.</p>";
        } else {
            // Asignar el artículo al revisor
            $sql_assign = "INSERT INTO Articulo_Revisor (id_articulo, rut_revisor) VALUES (?, ?)";
            $stmt_assign = $pdo->prepare($sql_assign);
            $stmt_assign->execute([$id_articulo, $rut_revisor]);
            echo "<p style='color: green;'>Artículo asignado exitosamente.</p>";
        }
    }
}

// Resaltar artículos con menos de dos revisores
$sql_resaltar = "SELECT id_articulo, COUNT(rut_revisor) AS num_revisores
                 FROM Articulo_Revisor
                 GROUP BY id_articulo
                 HAVING num_revisores < 2";
$stmt_resaltar = $pdo->query($sql_resaltar);
$articulos_pendientes = $stmt_resaltar->fetchAll();

// Asignación automática
if (isset($_POST['asignar_automatico'])) {
    $sql_auto = "INSERT INTO Articulo_Revisor (id_articulo, rut_revisor)
                 SELECT at.id_articulo, rt.rut_revisor
                 FROM Articulo_Topico at
                 JOIN Revisor_Topico rt ON at.id_topico = rt.id_topico
                 WHERE NOT EXISTS (
                     SELECT 1 FROM Articulo_Revisor ar
                     WHERE ar.id_articulo = at.id_articulo AND ar.rut_revisor = rt.rut_revisor
                 )";
    $pdo->exec($sql_auto);
    echo "<p style='color: green;'>Asignación automática completada.</p>";
}

// Obtener la cantidad de artículos asignados a cada revisor
$sql_revisores = "SELECT r.rut, u.nombre, COUNT(ar.id_articulo) AS num_articulos
                   FROM Revisor r
                   JOIN Usuario u ON r.rut = u.rut
                   LEFT JOIN Articulo_Revisor ar ON r.rut = ar.rut_revisor
                   GROUP BY r.rut, u.nombre";
$revisores_carga = $pdo->query($sql_revisores)->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asignar Artículos</title>
    <style>
        .btn-menu {
            display: inline-block;
            padding: 10px 20px;
            color: #fff;
            background-color: #28a745;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        .btn-menu:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <h1>Asignar Artículos a Revisores</h1>

    <!-- Formulario para asignación manual -->
    <form method="POST">
        <label for="id_articulo">Artículo:</label>
        <select id="id_articulo" name="id_articulo" required>
            <?php
            $articulos = $pdo->query("SELECT id_articulo, titulo FROM Articulo")->fetchAll();
            foreach ($articulos as $articulo) {
                echo "<option value='{$articulo['id_articulo']}'>{$articulo['titulo']}</option>";
            }
            ?>
        </select><br><br>

        <label for="rut_revisor">Revisor:</label>
        <select id="rut_revisor" name="rut_revisor" required>
            <?php
            $revisores = $pdo->query("SELECT r.rut, u.nombre FROM Revisor r JOIN Usuario u ON r.rut = u.rut")->fetchAll();
            foreach ($revisores as $revisor) {
                echo "<option value='{$revisor['rut']}'>{$revisor['nombre']}</option>";
            }
            ?>
        </select><br><br>

        <input type="submit" value="Asignar Manualmente">
    </form>

    <!-- Resaltar artículos con menos de dos revisores -->
    <h2>Artículos con menos de dos revisores</h2>
    <ul>
        <?php
        foreach ($articulos_pendientes as $articulo) {
            echo "<li>Artículo ID: {$articulo['id_articulo']} (Revisores asignados: {$articulo['num_revisores']})</li>";
        }
        ?>
    </ul>

    <!-- Botón para asignación automática -->
    <form method="POST">
        <input type="hidden" name="asignar_automatico" value="1">
        <input type="submit" value="Asignar Automáticamente">
    </form>

    <!-- Mostrar carga de trabajo de los revisores -->
    <h2>Carga de trabajo de los revisores</h2>
    <ul>
        <?php foreach ($revisores_carga as $revisor): ?>
            <li><?php echo htmlspecialchars($revisor['nombre']); ?>: <?php echo $revisor['num_articulos']; ?> artículo(s) asignado(s)</li>
        <?php endforeach; ?>
    </ul>

    <a href="../php/dashboard.php" class="btn-menu">Volver al Menú Principal</a>
    <a href="dashboard.php" style="font-family: Arial, sans-serif; font-size: 14px; color: #007BFF; text-decoration: none;">Volver al inicio</a>
</body>
</html>
