<?php
// Conexión a la base de datos
include 'php/conexion.php';  // Asegúrate de que el archivo con la conexión esté incluido

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_articulo = $_POST['id_articulo'];

    // Verificar si el artículo está en revisión
    $sql_check = "SELECT COUNT(*) FROM Articulo_Revisor WHERE id_articulo = ?";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute([$id_articulo]);
    $en_revision = $stmt_check->fetchColumn() > 0;

    if ($en_revision) {
        echo "No se puede eliminar un artículo en revisión.";
    } else {
        // Prepara la consulta SQL para eliminar el artículo
        $sql = "DELETE FROM Articulo WHERE id_articulo = :id_articulo";
        $stmt = $pdo->prepare($sql);

        // Enlaza el parámetro y ejecuta la consulta
        $stmt->bindParam(':id_articulo', $id_articulo);

        // Ejecuta la consulta
        if ($stmt->execute()) {
            echo "Artículo eliminado exitosamente!";
        } else {
            echo "Hubo un error al eliminar el artículo.";
        }
    }
}
?>

<!-- Formulario para eliminar artículo -->
<form method="POST" action="">
    <label for="id_articulo">ID Artículo a Eliminar:</label>
    <input type="text" name="id_articulo" required><br>
    <input type="submit" value="Eliminar Artículo">
</form>
