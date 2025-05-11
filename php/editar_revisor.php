<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include('../php/conexion.php');

if (!isset($_SESSION['rol']) || ($_SESSION['rol'] !== 'admin' && $_SESSION['rol'] !== 'Jefe Comite de Programa')) {
    header("Location: ../php/dashboard.php");
    exit();
}

$id = $_GET['id'] ?? null;
if (!$id) {
    echo '<p style="color: red;">ID de revisor no proporcionado.</p>';
    exit();
}

try {
    $sql = "SELECT * FROM Revisor WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $revisor = $stmt->fetch();

    if (!$revisor) {
        echo '<p style="color: red;">Revisor no encontrado.</p>';
        exit();
    }
} catch (Exception $e) {
    echo '<p style="color: red;">Error al cargar el revisor: ' . $e->getMessage() . '</p>';
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Revisor</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body style="font-family: Arial, sans-serif; margin: 20px;">
    <h1 style="font-size: 18px; color: #333;">Editar Revisor</h1>

    <form method="POST" action="gestionar_revisores.php" style="margin-top: 20px; border: 1px solid #ccc; padding: 15px;">
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($revisor['id']); ?>">
        <label for="nombre" style="font-size: 14px; display: block; margin-bottom: 5px;">Nombre:</label>
        <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($revisor['nombre']); ?>" style="width: 100%; padding: 8px; margin-bottom: 10px;" required>
        <label for="email" style="font-size: 14px; display: block; margin-bottom: 5px;">Email:</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($revisor['email']); ?>" style="width: 100%; padding: 8px; margin-bottom: 10px;" required>
        <button type="submit" style="font-size: 14px; padding: 10px 20px; background-color: #4CAF50; color: white; border: none; cursor: pointer;">Guardar Cambios</button>
    </form>

    <a href="gestionar_revisores.php" style="font-family: Arial, sans-serif; font-size: 14px; color: #007BFF; text-decoration: none; display: block; margin-top: 20px;">Volver</a>
</body>
</html>
