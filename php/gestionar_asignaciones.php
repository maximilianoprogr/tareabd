<?php
require_once 'conexion.php';

// Manejar solicitudes POST para asignar artículos a revisores
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_articulo'], $_POST['rut_revisor'])) {
    $id_articulo = $_POST['id_articulo']; // ID del artículo a asignar
    $rut_revisor = $_POST['rut_revisor']; // RUT del revisor al que se asignará el artículo

    try {
        // Llamar al procedimiento almacenado para asignar el artículo al revisor
        $sql = "CALL AsignarArticuloRevisor(?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_articulo, $rut_revisor]);

        echo "<p style='color: green;'>Artículo asignado exitosamente al revisor.</p>";
    } catch (PDOException $e) {
        // Manejar errores en la asignación
        echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    }
}

// Consultar todos los artículos disponibles
$sql_articulos = "SELECT id_articulo, titulo FROM Articulo";
$articulos = $pdo->query($sql_articulos)->fetchAll(); // Obtener todos los artículos

// Consultar todos los revisores disponibles
$sql_revisores = "SELECT rut, nombre FROM Usuario WHERE tipo = 'Revisor'";
$revisores = $pdo->query($sql_revisores)->fetchAll(); // Obtener todos los revisores

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Asignaciones</title>
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>
    <h1>Gestión de Asignaciones</h1>

    <!-- Formulario para asignación manual de artículos -->
    <h2>Asignación Manual</h2>
    <form method="POST" action="asignar_articulos.php">
        <label for="id_articulo">Artículo:</label>
        <select name="id_articulo" id="id_articulo" required>
            <?php foreach ($articulos as $articulo): ?>
                <option value="<?= $articulo['id_articulo'] ?>"><?= $articulo['titulo'] ?></option>
            <?php endforeach; ?>
        </select>

        <label for="rut_revisor">Revisor:</label>
        <select name="rut_revisor" id="rut_revisor" required>
            <?php foreach ($revisores as $revisor): ?>
                <option value="<?= $revisor['rut'] ?>"><?= $revisor['nombre'] ?></option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Asignar</button>
    </form>

    <!-- Formulario para asignación automática de artículos -->
    <h2>Asignación Automática</h2>
    <form method="POST" action="asignar_articulos.php">
        <input type="hidden" name="asignacion_automatica" value="1">
        <button type="submit">Realizar Asignación Automática</button>
    </form>

    <!-- Formulario para reasignación automática de artículos -->
    <h2>Reasignación Automática</h2>
    <form method="POST" action="asignar_articulos.php">
        <input type="hidden" name="reasignacion_automatica" value="1">
        <button type="submit">Realizar Reasignación Automática</button>
    </form>

    <!-- Botón para ver artículos con menos de dos revisores -->
    <h2>Artículos con menos de dos revisores</h2>
    <form method="GET" action="asignar_articulos.php">
        <input type="hidden" name="articulos_pendientes" value="1">
        <button type="submit">Ver Artículos Pendientes</button>
    </form>

    <!-- Tabla para mostrar detalles de los artículos -->
    <h2>Artículos</h2>
    <table border="1" style="width: 100%; text-align: left;">
        <thead>
            <tr>
                <th>Número</th>
                <th>Título</th>
                <th>Autores</th>
                <th>Tópicos</th>
                <th>Revisores</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Consultar detalles de los artículos
            $sql_articulos_detalle = "SELECT a.id_articulo, a.titulo, GROUP_CONCAT(aa.rut_autor) AS autores, GROUP_CONCAT(at.id_topico) AS topicos, COUNT(ar.rut_revisor) AS num_revisores
                                    FROM Articulo a
                                    LEFT JOIN Autor_Articulo aa ON a.id_articulo = aa.id_articulo
                                    LEFT JOIN Articulo_Topico at ON a.id_articulo = at.id_articulo
                                    LEFT JOIN Articulo_Revisor ar ON a.id_articulo = ar.id_articulo
                                    GROUP BY a.id_articulo";
            $articulos_detalle = $pdo->query($sql_articulos_detalle)->fetchAll(); // Obtener detalles de los artículos

            foreach ($articulos_detalle as $articulo): ?>
                <tr style="<?php echo ($articulo['num_revisores'] < 2) ? 'background-color: #ffcccc;' : ''; ?>">
                    <td><?= $articulo['id_articulo'] ?></td> <!-- Mostrar ID del artículo -->
                    <td><?= $articulo['titulo'] ?></td> <!-- Mostrar título del artículo -->
                    <td><?= $articulo['autores'] ?></td> <!-- Mostrar autores del artículo -->
                    <td><?= $articulo['topicos'] ?></td> <!-- Mostrar tópicos del artículo -->
                    <td><?= $articulo['num_revisores'] ?></td> <!-- Mostrar número de revisores asignados -->
                    <td>
                        <!-- Formulario para asignar un revisor a un artículo -->
                        <form method="POST" action="asignar_articulos.php">
                            <input type="hidden" name="id_articulo" value="<?= $articulo['id_articulo'] ?>">
                            <select name="rut_revisor" required>
                                <?php foreach ($revisores as $revisor): ?>
                                    <option value="<?= $revisor['rut'] ?>"><?= $revisor['nombre'] ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit">Asignar Revisor</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Tabla para mostrar detalles de los revisores -->
    <h2>Miembros del Comité</h2>
    <table border="1" style="width: 100%; text-align: left;">
        <thead>
            <tr>
                <th>Miembro</th>
                <th>Tópicos</th>
                <th>Artículos Asignados</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Consultar detalles de los revisores
            $sql_revisores_detalle = "SELECT u.nombre, GROUP_CONCAT(rt.id_topico) AS topicos, COUNT(ar.id_articulo) AS articulos_asignados
                                    FROM Usuario u
                                    LEFT JOIN Revisor_Topico rt ON u.rut = rt.rut_revisor
                                    LEFT JOIN Articulo_Revisor ar ON u.rut = ar.rut_revisor
                                    WHERE u.tipo = 'Revisor'
                                    GROUP BY u.rut";
            $revisores_detalle = $pdo->query($sql_revisores_detalle)->fetchAll(); // Obtener detalles de los revisores

            foreach ($revisores_detalle as $revisor): ?>
                <tr>
                    <td><?= $revisor['nombre'] ?></td> <!-- Mostrar nombre del revisor -->
                    <td><?= $revisor['topicos'] ?></td> <!-- Mostrar tópicos del revisor -->
                    <td><?= $revisor['articulos_asignados'] ?></td> <!-- Mostrar número de artículos asignados -->
                    <td>
                        <!-- Formulario para asignar un artículo a un revisor -->
                        <form method="POST" action="asignar_articulos.php">
                            <input type="hidden" name="rut_revisor" value="<?= $revisor['rut'] ?>">
                            <select name="id_articulo" required>
                                <?php foreach ($articulos as $articulo): ?>
                                    <option value="<?= $articulo['id_articulo'] ?>"><?= $articulo['titulo'] ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit">Asignar Artículo</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
