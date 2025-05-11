<?php
include('php/conexion.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_articulo = $_POST['id_articulo'];
    $rut_revisor = $_POST['rut_revisor'];

    // Verificar que el revisor no sea autor del artículo
    $sql_check = "SELECT COUNT(*) FROM Autor_Articulo WHERE id_articulo = ? AND rut_autor = ?";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute([$id_articulo, $rut_revisor]);
    $es_autor = $stmt_check->fetchColumn() > 0;

    if ($es_autor) {
        echo "No se puede asignar un artículo a un revisor que sea autor.";
    } else {
        // Llamar al procedimiento almacenado
        $sql = "CALL AsignarArticuloRevisor(?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_articulo, $rut_revisor]);
        echo "Artículo asignado exitosamente.";
    }
}

// Obtener artículos y revisores
$articulos = $pdo->query("SELECT id_articulo, titulo FROM Articulo")->fetchAll();
$revisores = $pdo->query("SELECT rut, nombre FROM Revisor")->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Asignar Artículos</title>
</head>
<body>
    <h1>Asignar Artículos a Revisores</h1>
    <form method="POST">
        <label for="id_articulo">Artículo:</label>
        <select id="id_articulo" name="id_articulo" required>
            <?php foreach ($articulos as $articulo): ?>
            <option value="<?php echo $articulo['id_articulo']; ?>"><?php echo htmlspecialchars($articulo['titulo']); ?></option>
            <?php endforeach; ?>
        </select><br><br>

        <label for="rut_revisor">Revisor:</label>
        <select id="rut_revisor" name="rut_revisor" required>
            <?php foreach ($revisores as $revisor): ?>
            <option value="<?php echo $revisor['rut']; ?>"><?php echo htmlspecialchars($revisor['nombre']); ?></option>
            <?php endforeach; ?>
        </select><br><br>

        <input type="submit" value="Asignar">
    </form>
</body>
</html>
