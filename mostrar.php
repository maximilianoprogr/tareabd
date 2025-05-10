<?php
include('conexion.php');

// Consultar todos los artículos
$sql = "SELECT * FROM Articulo";
$stmt = $pdo->query($sql);
$articulos = $stmt->fetchAll();
?>

<h1>Lista de Artículos</h1>
<table border="1">
    <tr>
        <th>ID</th>
        <th>Título</th>
        <th>Resumen</th>
        <th>Fecha de Envío</th>
        <th>Acciones</th>
    </tr>
    <?php foreach ($articulos as $articulo): ?>
    <tr>
        <td><?php echo $articulo['id_articulo']; ?></td>
        <td><?php echo $articulo['titulo']; ?></td>
        <td><?php echo $articulo['resumen']; ?></td>
        <td><?php echo $articulo['fecha_envio']; ?></td>
        <td>
            <a href="editar.php?id=<?php echo $articulo['id_articulo']; ?>">Editar</a> |
            <a href="eliminar.php?id=<?php echo $articulo['id_articulo']; ?>">Eliminar</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
