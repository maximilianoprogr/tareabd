<?php
ob_start(); // Iniciar el buffer de salida para evitar problemas con header()

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

// Manejar la acción de quitar revisores
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quitar'])) {
    $id_articulo = $_POST['id_articulo'];

    // Eliminar todos los revisores asignados al artículo
    $sql_quitar = "DELETE FROM Articulo_Revisor WHERE id_articulo = ?";
    $stmt_quitar = $pdo->prepare($sql_quitar);
    $stmt_quitar->execute([$id_articulo]);

    echo "<p style='color: green;'>Revisores eliminados del artículo ID: $id_articulo.</p>";
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

// Mostrar tabla de asignación de artículos a revisores
$sql_articulos = "SELECT a.id_articulo, a.titulo, 
                  GROUP_CONCAT(DISTINCT CONCAT(u.nombre, ' (', u.rut, ')') SEPARATOR ', ') AS autores,
                  GROUP_CONCAT(DISTINCT t.nombre SEPARATOR ', ') AS topicos,
                  GROUP_CONCAT(DISTINCT r.nombre SEPARATOR ', ') AS revisores
                  FROM Articulo a
                  LEFT JOIN Autor_Articulo aa ON a.id_articulo = aa.id_articulo
                  LEFT JOIN Usuario u ON aa.rut_autor = u.rut
                  LEFT JOIN Articulo_Topico at ON a.id_articulo = at.id_articulo
                  LEFT JOIN Topico t ON at.id_topico = t.id_topico
                  LEFT JOIN Articulo_Revisor ar ON a.id_articulo = ar.id_articulo
                  LEFT JOIN Usuario r ON ar.rut_revisor = r.rut
                  GROUP BY a.id_articulo";
$stmt_articulos = $pdo->query($sql_articulos);
$articulos = $stmt_articulos->fetchAll();

echo '<table border="1" style="width: 100%; border-collapse: collapse; text-align: center; font-family: Arial, sans-serif;">';
echo '<tr style="background-color: #f2f2f2; font-weight: bold;">';
echo '<th>Número</th><th>Título</th><th>Autores</th><th>Tópicos</th><th>Revisores</th><th>Acciones</th>';
echo '</tr>';
foreach ($articulos as $articulo) {
    $resaltado = in_array($articulo['id_articulo'], array_column($articulos_pendientes, 'id_articulo')) ? 'style="background-color: #ffcccc;"' : '';
    $autores = isset($articulo['autores']) ? htmlspecialchars($articulo['autores']) : 'N/A';
    $topicos = isset($articulo['topicos']) ? htmlspecialchars($articulo['topicos']) : 'N/A';
    $revisores = isset($articulo['revisores']) ? htmlspecialchars($articulo['revisores']) : 'N/A';
    echo "<tr $resaltado>";
    echo '<td>' . htmlspecialchars($articulo['id_articulo']) . '</td>';
    echo '<td>' . htmlspecialchars($articulo['titulo']) . '</td>';
    echo '<td>' . $autores . '</td>';
    echo '<td>' . $topicos . '</td>';
    echo '<td>' . $revisores . '</td>';
    echo '<td>';
    echo '<form method="POST" action="asignar_articulos.php" style="display:inline; margin-bottom: 5px;">';
    echo '<input type="hidden" name="id_articulo" value="' . htmlspecialchars($articulo['id_articulo']) . '">';
    echo '<select name="rut_revisor" style="margin-bottom: 5px;">';
    foreach ($revisores_disponibles as $revisor) {
        echo '<option value="' . htmlspecialchars($revisor['rut']) . '">' . htmlspecialchars($revisor['nombre']) . '</option>';
    }
    echo '</select><br>';
    echo '<button type="submit" name="asignar" style="background-color: #4CAF50; color: white; border: none; padding: 5px 10px; cursor: pointer;">Asignar</button>';
    echo '</form>';
    echo '<form method="POST" action="asignar_articulos.php" style="display:inline;">';
    echo '<input type="hidden" name="id_articulo" value="' . htmlspecialchars($articulo['id_articulo']) . '">';
    echo '<button type="submit" name="quitar" style="background-color: #f44336; color: white; border: none; padding: 5px 10px; cursor: pointer;">Quitar Revisor</button>';
    echo '</form>';
    echo '</td>';
    echo '</tr>';
}
echo '</table>';

// Función para obtener artículos con menos de dos revisores
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['articulos_pendientes'])) {
    $sql_pendientes = "SELECT a.id_articulo, a.titulo, COUNT(ar.rut_revisor) AS num_revisores
                       FROM Articulo a
                       LEFT JOIN Articulo_Revisor ar ON a.id_articulo = ar.id_articulo
                       GROUP BY a.id_articulo
                       HAVING num_revisores < 2";
    $stmt_pendientes = $pdo->query($sql_pendientes);
    $articulos_pendientes = $stmt_pendientes->fetchAll();

    echo "<h3>Artículos con menos de dos revisores:</h3>";
    foreach ($articulos_pendientes as $articulo) {
        echo "<p>ID: {$articulo['id_articulo']}, Título: {$articulo['titulo']}, Revisores: {$articulo['num_revisores']}</p>";
    }
}

// Función para asignación automática
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['asignacion_automatica'])) {
    $sql_articulos = "SELECT id_articulo, titulo FROM Articulo";
    $articulos = $pdo->query($sql_articulos)->fetchAll();

    $sql_revisores = "SELECT rut, GROUP_CONCAT(id_topico) AS topicos FROM Revisor_Topico GROUP BY rut";
    $revisores = $pdo->query($sql_revisores)->fetchAll();

    foreach ($articulos as $articulo) {
        foreach ($revisores as $revisor) {
            $topicos_articulo = explode(',', $articulo['id_topico']);
            $topicos_revisor = explode(',', $revisor['topicos']);

            if (array_intersect($topicos_articulo, $topicos_revisor)) {
                $sql_asignar = "INSERT IGNORE INTO Articulo_Revisor (id_articulo, rut_revisor) VALUES (?, ?)";
                $stmt_asignar = $pdo->prepare($sql_asignar);
                $stmt_asignar->execute([$articulo['id_articulo'], $revisor['rut']]);
            }
        }
    }

    echo "<p>Asignación automática completada.</p>";
}

// Función para reasignación automática sin perder asignaciones manuales
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reasignacion_automatica'])) {
    $sql_articulos = "SELECT id_articulo, titulo FROM Articulo";
    $articulos = $pdo->query($sql_articulos)->fetchAll();

    $sql_revisores = "SELECT rut, GROUP_CONCAT(id_topico) AS topicos FROM Revisor_Topico GROUP BY rut";
    $revisores = $pdo->query($sql_revisores)->fetchAll();

    foreach ($articulos as $articulo) {
        foreach ($revisores as $revisor) {
            $topicos_articulo = explode(',', $articulo['id_topico']);
            $topicos_revisor = explode(',', $revisor['topicos']);

            if (array_intersect($topicos_articulo, $topicos_revisor)) {
                $sql_asignar = "INSERT IGNORE INTO Articulo_Revisor (id_articulo, rut_revisor) VALUES (?, ?)";
                $stmt_asignar = $pdo->prepare($sql_asignar);
                $stmt_asignar->execute([$articulo['id_articulo'], $revisor['rut']]);
            }
        }
    }

    echo "<p>Reasignación automática completada sin perder asignaciones manuales.</p>";
}

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

    <!-- Botón para reasignación automática sin perder asignaciones manuales -->
    <form method="POST">
        <input type="hidden" name="reasignacion_automatica" value="1">
        <input type="submit" value="Reasignar Automáticamente sin perder asignaciones manuales">
    </form>

    <!-- Mostrar carga de trabajo de los revisores -->
    <h2>Carga de trabajo de los revisores</h2>
    <ul>
        <?php foreach ($revisores_carga as $revisor): ?>
            <li><?php echo htmlspecialchars($revisor['nombre']); ?>: <?php echo $revisor['num_articulos']; ?> artículo(s) asignado(s)</li>
        <?php endforeach; ?>
    </ul>

    <!-- Tabla de asignación de artículos a revisores -->
    <h2>Asignación de Artículos a Revisores</h2>
    <table border="1" style="width: 100%; border-collapse: collapse; text-align: center; font-family: Arial, sans-serif;">
        <tr style="background-color: #f2f2f2; font-weight: bold;">
            <th>Número</th><th>Título</th><th>Autores</th><th>Tópicos</th><th>Revisores</th><th>Acciones</th>
        </tr>
        <?php
        foreach ($articulos as $articulo) {
            $resaltado = in_array($articulo['id_articulo'], array_column($articulos_pendientes, 'id_articulo')) ? 'style="background-color: #ffcccc;"' : '';
            $autores = isset($articulo['autores']) ? htmlspecialchars($articulo['autores']) : 'N/A';
            $topicos = isset($articulo['topicos']) ? htmlspecialchars($articulo['topicos']) : 'N/A';
            $revisores = isset($articulo['revisores']) ? htmlspecialchars($articulo['revisores']) : 'N/A';
            echo "<tr $resaltado>";
            echo '<td>' . htmlspecialchars($articulo['id_articulo']) . '</td>';
            echo '<td>' . htmlspecialchars($articulo['titulo']) . '</td>';
            echo '<td>' . $autores . '</td>';
            echo '<td>' . $topicos . '</td>';
            echo '<td>' . $revisores . '</td>';
            echo '<td>';
            echo '<form method="POST" action="asignar_articulos.php" style="display:inline; margin-bottom: 5px;">';
            echo '<input type="hidden" name="id_articulo" value="' . htmlspecialchars($articulo['id_articulo']) . '">';
            echo '<select name="rut_revisor" style="margin-bottom: 5px;">';
            foreach ($revisores_disponibles as $revisor) {
                echo '<option value="' . htmlspecialchars($revisor['rut']) . '">' . htmlspecialchars($revisor['nombre']) . '</option>';
            }
            echo '</select><br>';
            echo '<button type="submit" name="asignar" style="background-color: #4CAF50; color: white; border: none; padding: 5px 10px; cursor: pointer;">Asignar</button>';
            echo '</form>';
            echo '<form method="POST" action="asignar_articulos.php" style="display:inline;">';
            echo '<input type="hidden" name="id_articulo" value="' . htmlspecialchars($articulo['id_articulo']) . '">';
            echo '<button type="submit" name="quitar" style="background-color: #f44336; color: white; border: none; padding: 5px 10px; cursor: pointer;">Quitar Revisor</button>';
            echo '</form>';
            echo '</td>';
            echo '</tr>';
        }
        ?>
    </table>

    <a href="../php/dashboard.php" class="btn-menu">Volver al Menú Principal</a>
    <a href="dashboard.php" style="font-family: Arial, sans-serif; font-size: 14px; color: #007BFF; text-decoration: none;">Volver al inicio</a>
</body>
</html>

<?php
// Asegurarse de que no haya salida previa
if (headers_sent()) {
    die("Error: No se puede redirigir porque ya se ha enviado salida al navegador.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['asignar'])) {
    $id_articulo = $_POST['id_articulo'];
    $rut_revisor = $_POST['rut_revisor'];

    // Asignar el revisor al artículo
    $sql_asignar = "INSERT INTO Articulo_Revisor (id_articulo, rut_revisor) VALUES (?, ?)";
    $stmt_asignar = $pdo->prepare($sql_asignar);
    $stmt_asignar->execute([$id_articulo, $rut_revisor]);

    // Redirigir a la nueva página con la tabla de asignaciones
    header("Location: tabla_asignaciones.php");
    exit();
}
?>
