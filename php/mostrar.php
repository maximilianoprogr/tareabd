<?php
// Incluir archivo de conexión a la base de datos
include('../php/conexion.php');

// Preparar la consulta SQL para obtener todos los artículos
$sql = "SELECT * FROM Articulo";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$articulos = $stmt->fetchAll(); // Obtener todos los artículos de la base de datos
?>

<h1>Lista de Artículos</h1> <!-- Título de la página -->
<table border="1"> <!-- Tabla para mostrar los artículos -->
    <tr>
        <th>ID</th> <!-- Encabezado de columna para el ID del artículo -->
        <th>Título</th> <!-- Encabezado de columna para el título del artículo -->
        <th>Resumen</th> <!-- Encabezado de columna para el resumen del artículo -->
        <th>Fecha de Envío</th> <!-- Encabezado de columna para la fecha de envío del artículo -->
        <th>Acciones</th> <!-- Encabezado de columna para las acciones disponibles -->
    </tr>
    <?php foreach ($articulos as $articulo): ?> <!-- Iterar sobre cada artículo -->
    <tr>
        <td><?php echo $articulo['id_articulo']; ?></td> <!-- Mostrar el ID del artículo -->
        <td><?php echo $articulo['titulo']; ?></td> <!-- Mostrar el título del artículo -->
        <td><?php echo $articulo['resumen']; ?></td> <!-- Mostrar el resumen del artículo -->
        <td><?php echo $articulo['fecha_envio']; ?></td> <!-- Mostrar la fecha de envío del artículo -->
        <td>
            <!-- Enlace para editar el artículo -->
            <a href="../php/editar.php?id=<?php echo $articulo['id_articulo']; ?>">Editar</a> |
            <!-- Enlace para eliminar el artículo -->
            <a href="../php/eliminar.php?id=<?php echo $articulo['id_articulo']; ?>">Eliminar</a>
        </td>
    </tr>
    <?php endforeach; ?> <!-- Fin de la iteración sobre los artículos -->
</table>
