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
$revisores = $pdo->query("SELECT r.rut, u.nombre FROM Revisor r JOIN Usuario u ON r.rut = u.rut")->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
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
    <a href="dashboard.php" class="btn-menu">Volver al Menú Principal</a>
</body>
</html>
