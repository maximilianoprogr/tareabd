<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include('conexion.php'); // Asegúrate de que este archivo define correctamente $pdo

// Verificar permisos
if (!isset($_SESSION['usuario']) || $_SESSION['usuario'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

// Crear, leer, actualizar y eliminar revisores
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $rut = $_POST['rut'] ?? null;
    $nombre = $_POST['nombre'] ?? null;
    $email = $_POST['email'] ?? null;

    if ($action === 'create' && $rut && $nombre && $email) {
        $userid = $_SESSION['usuario'] ?? null; // Obtener el usuario autenticado
        if ($userid) {
            $sql = "INSERT INTO Revisor (rut, nombre, email, userid) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$rut, $nombre, $email, $userid]);
            echo "<script>alert('Revisor agregado exitosamente');</script>";
        } else {
            echo "<script>alert('Error: Usuario no autenticado');</script>";
        }
    } elseif ($action === 'update' && $rut && $nombre && $email) {
        $sql = "UPDATE Revisor SET nombre = ?, email = ? WHERE rut = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombre, $email, $rut]);
        echo "<script>alert('Revisor actualizado exitosamente');</script>";
    } elseif ($action === 'delete' && $rut) {
        $sql_check = "SELECT COUNT(*) FROM Articulo_Revisor WHERE rut_revisor = ?";
        $stmt_check = $pdo->prepare($sql_check);
        $stmt_check->execute([$rut]);
        $tiene_articulos = $stmt_check->fetchColumn() > 0;

        if ($tiene_articulos) {
            echo "<script>alert('No se puede eliminar un revisor con artículos asignados');</script>";
        } else {
            $sql = "DELETE FROM Revisor WHERE rut = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$rut]);
            echo "<script>alert('Revisor eliminado exitosamente');</script>";
        }
    }
}

// Leer revisores
try {
    $sql = "SELECT * FROM Revisor";
    $stmt = $pdo->query($sql);
    $revisores = $stmt->fetchAll();

    // Depuración: Verificar si se obtuvieron datos
    if (!$revisores) {
        echo "<script>console.log('No se encontraron revisores en la base de datos');</script>";
    } else {
        echo "<script>console.log('Revisores obtenidos: " . json_encode($revisores) . "');</script>";
    }
} catch (Exception $e) {
    // Depuración: Mostrar errores de la consulta
    echo "<script>console.error('Error al consultar revisores: " . $e->getMessage() . "');</script>";
    $revisores = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestionar Revisores</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <h1>Gestionar Revisores</h1>
        <form method="POST">
            <input type="hidden" name="action" value="create">
            <input type="text" name="rut" placeholder="RUT" required>
            <input type="text" name="nombre" placeholder="Nombre" required>
            <input type="email" name="email" placeholder="Email" required>
            <button type="submit" class="btn btn-primary">Agregar Revisor</button>
        </form>
        <table class="table">
            <thead>
                <tr>
                    <th>RUT</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($revisores)): ?>
                    <?php foreach ($revisores as $revisor): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($revisor['rut']); ?></td>
                        <td><?php echo htmlspecialchars($revisor['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($revisor['email']); ?></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="rut" value="<?php echo $revisor['rut']; ?>">
                                <input type="text" name="nombre" value="<?php echo htmlspecialchars($revisor['nombre']); ?>" required>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($revisor['email']); ?>" required>
                                <button type="submit" class="btn btn-warning">Editar</button>
                            </form>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="rut" value="<?php echo $revisor['rut']; ?>">
                                <button type="submit" class="btn btn-danger">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">No se encontraron revisores.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
