<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include('../php/conexion.php'); // Asegúrate de que este archivo define correctamente $pdo

// Verificar permisos
if (!isset($_SESSION['usuario']) || $_SESSION['usuario'] !== 'admin') {
    header("Location: ../php/dashboard.php");
    exit();
}

// Crear, leer, actualizar y eliminar revisores
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $rut = $_POST['rut'] ?? null;
    $nombre = $_POST['nombre'] ?? null;
    $email = $_POST['email'] ?? null;

    // Validar que el RUT no exceda los 10 caracteres
    if (strlen($rut) > 10) {
        echo "<p style='color: red;'>El RUT no puede exceder los 10 caracteres.</p>";
        exit();
    }

    // Validar que el email tenga un formato válido
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<p style='color: red;'>El email no tiene un formato válido.</p>";
        exit();
    }

    if ($action === 'create' && $rut && $nombre && $email) {
        // Validar que el nombre y el email no excedan los límites permitidos
        if (strlen($nombre) > 255) {
            echo "<p style='color: red;'>El nombre no puede exceder los 255 caracteres.</p>";
            exit();
        }

        if (strlen($email) > 255) {
            echo "<p style='color: red;'>El email no puede exceder los 255 caracteres.</p>";
            exit();
        }

        // Usar sentencias preparadas para evitar inyecciones SQL
        $sql = "INSERT INTO Revisor (rut, nombre, email) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$rut, $nombre, $email]);

        // Asignar tópicos al revisor
        $topicos = $_POST['topicos'] ?? [];
        // Validar que no existan tópicos duplicados al asignar a un revisor
        if (count($topicos) !== count(array_unique($topicos))) {
            echo "<p style='color: red;'>No se permiten tópicos duplicados para un revisor.</p>";
            exit();
        }

        if (!empty($topicos)) {
            $sql_topicos = "INSERT INTO Revisor_Topico (rut_revisor, id_topico) VALUES (?, ?)";
            $stmt_topicos = $pdo->prepare($sql_topicos);
            foreach ($topicos as $id_topico) {
                $stmt_topicos->execute([$rut, $id_topico]);
            }
        } else {
            echo "<script>alert('Debe asignar al menos un tópico al revisor');</script>";
        }
        echo "<script>alert('Revisor agregado exitosamente');</script>";
    } elseif ($action === 'update' && $rut && $nombre && $email) {
        // Validar que el nombre y el email no excedan los límites permitidos
        if (strlen($nombre) > 255) {
            echo "<p style='color: red;'>El nombre no puede exceder los 255 caracteres.</p>";
            exit();
        }

        if (strlen($email) > 255) {
            echo "<p style='color: red;'>El email no puede exceder los 255 caracteres.</p>";
            exit();
        }

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
            echo "<p style='color: red;'>No se puede eliminar un revisor con artículos asignados.</p>";
        } else {
            $sql = "DELETE FROM Revisor WHERE rut = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$rut]);
            echo "<p style='color: green;'>Revisor eliminado exitosamente.</p>";
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Revisores</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Gestionar Revisores</h1>
        <form method="POST" class="mb-4">
            <div class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="rut" class="form-control" placeholder="RUT" required>
                </div>
                <div class="col-md-3">
                    <input type="text" name="nombre" class="form-control" placeholder="Nombre" required>
                </div>
                <div class="col-md-3">
                    <input type="email" name="email" class="form-control" placeholder="Email" required>
                </div>
                <div class="col-md-3">
                    <select name="topicos[]" class="form-control" multiple required>
                        <!-- Opciones de tópicos -->
                        <?php
                        $sql_topicos = "SELECT id_topico, nombre FROM Topico";
                        $stmt_topicos = $pdo->query($sql_topicos);
                        $topicos = $stmt_topicos->fetchAll();
                        foreach ($topicos as $topico) {
                            echo "<option value='{$topico['id_topico']}'>{$topico['nombre']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">Agregar Revisor</button>
                </div>
            </div>
        </form>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>RUT</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Tópicos</th>
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
                            <?php
                            $sql_revisor_topicos = "SELECT t.nombre FROM Revisor_Topico rt JOIN Topico t ON rt.id_topico = t.id_topico WHERE rt.rut_revisor = ?";
                            $stmt_revisor_topicos = $pdo->prepare($sql_revisor_topicos);
                            $stmt_revisor_topicos->execute([$revisor['rut']]);
                            $topicos_revisor = $stmt_revisor_topicos->fetchAll();
                            foreach ($topicos_revisor as $topico) {
                                echo htmlspecialchars($topico['nombre']) . '<br>';
                            }
                            ?>
                        </td>
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
                        <td colspan="5" class="text-center">No se encontraron revisores.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
