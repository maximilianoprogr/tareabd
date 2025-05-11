<?php
// Editar artículo
if (isset($_GET['id'])) {
    $id_articulo = $_GET['id'];

    // Obtener los datos del artículo
    $sql = "SELECT * FROM Articulo WHERE id_articulo = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_articulo]);
    $articulo = $stmt->fetch();

    if ($articulo) {
        // Verificar si el artículo está en revisión
        $sql_check = "SELECT COUNT(*) FROM Articulo_Revisor WHERE id_articulo = ?";
        $stmt_check = $pdo->prepare($sql_check);
        $stmt_check->execute([$id_articulo]);
        $en_revision = $stmt_check->fetchColumn() > 0;

        if ($en_revision) {
            echo "No se puede modificar un artículo en revisión.";
            exit();
        }

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $titulo = $_POST['titulo'];
            $fecha_envio = $_POST['fecha_envio'];
            $resumen = $_POST['resumen'];

            // Actualizar el artículo
            $sql_update = "UPDATE Articulo SET titulo = ?, fecha_envio = ?, resumen = ? WHERE id_articulo = ?";
            $stmt_update = $pdo->prepare($sql_update);
            $stmt_update->execute([$titulo, $fecha_envio, $resumen, $id_articulo]);

            echo "Artículo actualizado exitosamente";
        }
    } else {
        echo "Artículo no encontrado";
        $articulo = ['titulo' => '', 'fecha_envio' => '', 'resumen' => '']; // Valores vacíos para evitar errores
    }
} else {
    echo "No se ha proporcionado un ID de artículo";
    $articulo = ['titulo' => '', 'fecha_envio' => '', 'resumen' => '']; // Valores vacíos para evitar errores
}
?>

<form method="post">
    Título: <input type="text" name="titulo" value="<?php echo htmlspecialchars($articulo['titulo']); ?>" required><br>
    Fecha de Envío: <input type="date" name="fecha_envio" value="<?php echo htmlspecialchars($articulo['fecha_envio']); ?>" required><br>
    Resumen: <textarea name="resumen" required><?php echo htmlspecialchars($articulo['resumen']); ?></textarea><br>
    <input type="submit" value="Actualizar Artículo">
</form>
