<?php
require_once 'conexion.php';

// Obtener artículos y revisores para la interfaz
$sql_articulos = "SELECT id_articulo, titulo FROM Articulo";
$articulos = $pdo->query($sql_articulos)->fetchAll();

$sql_revisores = "SELECT rut, nombre FROM Usuario WHERE tipo = 'Revisor'";
$revisores = $pdo->query($sql_revisores)->fetchAll();

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

    <h2>Asignación Automática</h2>
    <form method="POST" action="asignar_articulos.php">
        <input type="hidden" name="asignacion_automatica" value="1">
        <button type="submit">Realizar Asignación Automática</button>
    </form>

    <h2>Reasignación Automática</h2>
    <form method="POST" action="asignar_articulos.php">
        <input type="hidden" name="reasignacion_automatica" value="1">
        <button type="submit">Realizar Reasignación Automática</button>
    </form>

    <h2>Artículos con menos de dos revisores</h2>
    <form method="GET" action="asignar_articulos.php">
        <input type="hidden" name="articulos_pendientes" value="1">
        <button type="submit">Ver Artículos Pendientes</button>
    </form>

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
            $sql_articulos_detalle = "SELECT a.id_articulo, a.titulo, GROUP_CONCAT(aa.rut_autor) AS autores, GROUP_CONCAT(at.id_topico) AS topicos, COUNT(ar.rut_revisor) AS num_revisores
                                    FROM Articulo a
                                    LEFT JOIN Autor_Articulo aa ON a.id_articulo = aa.id_articulo
                                    LEFT JOIN Articulo_Topico at ON a.id_articulo = at.id_articulo
                                    LEFT JOIN Articulo_Revisor ar ON a.id_articulo = ar.id_articulo
                                    GROUP BY a.id_articulo";
            $articulos_detalle = $pdo->query($sql_articulos_detalle)->fetchAll();

            foreach ($articulos_detalle as $articulo): ?>
                <tr style="<?php echo ($articulo['num_revisores'] < 2) ? 'background-color: #ffcccc;' : ''; ?>">
                    <td><?= $articulo['id_articulo'] ?></td>
                    <td><?= $articulo['titulo'] ?></td>
                    <td><?= $articulo['autores'] ?></td>
                    <td><?= $articulo['topicos'] ?></td>
                    <td><?= $articulo['num_revisores'] ?></td>
                    <td>
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
            $sql_revisores_detalle = "SELECT u.nombre, GROUP_CONCAT(rt.id_topico) AS topicos, COUNT(ar.id_articulo) AS articulos_asignados
                                    FROM Usuario u
                                    LEFT JOIN Revisor_Topico rt ON u.rut = rt.rut_revisor
                                    LEFT JOIN Articulo_Revisor ar ON u.rut = ar.rut_revisor
                                    WHERE u.tipo = 'Revisor'
                                    GROUP BY u.rut";
            $revisores_detalle = $pdo->query($sql_revisores_detalle)->fetchAll();

            foreach ($revisores_detalle as $revisor): ?>
                <tr>
                    <td><?= $revisor['nombre'] ?></td>
                    <td><?= $revisor['topicos'] ?></td>
                    <td><?= $revisor['articulos_asignados'] ?></td>
                    <td>
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
