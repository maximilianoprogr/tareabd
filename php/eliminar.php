<?php
// Incluir el archivo de conexión a la base de datos
include '../php/conexion.php'; 

// Verificar si el formulario fue enviado mediante POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_articulo = $_POST['id_articulo'];

    // Consulta para verificar si el artículo está en revisión
    $sql_check = "SELECT COUNT(*) FROM Articulo_Revisor WHERE id_articulo = ?";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute([$id_articulo]);
    $en_revision = $stmt_check->fetchColumn() > 0;

    // Si el artículo está en revisión, no se puede eliminar
    if ($en_revision) {
        echo "No se puede eliminar un artículo en revisión.";
    } else {
        // Consulta para eliminar el artículo por su ID
        $sql = "DELETE FROM Articulo WHERE id_articulo = :id_articulo";
        $stmt = $pdo->prepare($sql);

        // Asignar el valor del ID del artículo al parámetro de la consulta
        $stmt->bindParam(':id_articulo', $id_articulo);

        // Ejecutar la consulta y verificar si fue exitosa
        if ($stmt->execute()) {
            echo "Artículo eliminado exitosamente!";
        } else {
            echo "Hubo un error al eliminar el artículo.";
        }
    }
}
?>

<form method="POST" action="">
    <label for="id_articulo">ID Artículo a Eliminar:</label>
    <input type="text" name="id_articulo" required><br>
    <input type="submit" value="Eliminar Artículo">
</form>
