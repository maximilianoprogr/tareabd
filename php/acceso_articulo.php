<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

// Verificar el rol del usuario
$es_revisor = isset($_SESSION['rol']) && $_SESSION['rol'] === 'revisor';

// Obtener todos los artículos enviados
include('conexion.php');
$sql_articulos = "SELECT id_articulo, titulo FROM Articulo";
$stmt_articulos = $pdo->query($sql_articulos);
$articulos = $stmt_articulos->fetchAll();

// Verificar si se seleccionó un artículo
$articulo_seleccionado = isset($_GET['id_articulo']) ? $_GET['id_articulo'] : null;
$detalles_articulo = null;
$revisores = [];

if ($articulo_seleccionado) {
    // Obtener detalles del artículo seleccionado
    $sql_detalles = "SELECT titulo, resumen FROM Articulo WHERE id_articulo = ?";
    $stmt_detalles = $pdo->prepare($sql_detalles);
    $stmt_detalles->execute([$articulo_seleccionado]);
    $detalles_articulo = $stmt_detalles->fetch();

    // Obtener revisores asignados al artículo
    $sql_revisores = "SELECT ar.rut_revisor FROM Articulo_Revisor ar WHERE ar.id_articulo = ?";
    $stmt_revisores = $pdo->prepare($sql_revisores);
    $stmt_revisores->execute([$articulo_seleccionado]);
    $revisores = $stmt_revisores->fetchAll(PDO::FETCH_COLUMN);
}

// Redirigir automáticamente si no se seleccionó un artículo
if (!$articulo_seleccionado) {
    header("Location: dashboard.php?error=seleccione_articulo");
    exit();
}

// Eliminar el bloque que detiene la ejecución si no hay revisores
if (empty($revisores)) {
    echo '<p style="font-family: Arial, sans-serif; color: red;">No hay revisores asignados a este artículo.</p>';
    echo '<a href="dashboard.php" style="font-family: Arial, sans-serif; font-size: 14px; color: #007BFF; text-decoration: none;">Volver al inicio</a>';
    // exit(); // Comentado para permitir que la página continúe cargando
}

// Verificar si los resultados de la revisión han sido publicados
$resultados_publicados = false;
if ($articulo_seleccionado) {
    $sql_resultados = "SELECT COUNT(*) FROM Evaluacion_Articulo WHERE id_articulo = ?";
    $stmt_resultados = $pdo->prepare($sql_resultados);
    $stmt_resultados->execute([$articulo_seleccionado]);
    $resultados_publicados = $stmt_resultados->fetchColumn() > 0;
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso al Artículo</title>
    <link rel="stylesheet" href="css/estilos.css">
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body style="font-family: Arial, sans-serif; margin: 20px;">
    <h1 style="font-size: 18px; color: #333;">Artículos Enviados</h1>
    <ul>
        <?php foreach ($articulos as $articulo): ?>
            <li><a href="acceso_articulo.php?id_articulo=<?php echo $articulo['id_articulo']; ?>" style="color: #007BFF; text-decoration: none;">Artículo: <?php echo htmlspecialchars($articulo['titulo']); ?></a></li>
        <?php endforeach; ?>
    </ul>

    <?php if ($detalles_articulo): ?>
        <h1 style="font-size: 18px; color: #333;">Datos del Artículo</h1>
        <div style="margin-bottom: 20px; border: 1px solid #ccc; padding: 10px;">
            <p><strong>ID:</strong> <?php echo htmlspecialchars($articulo_seleccionado); ?></p>
            <p><strong>Título:</strong> <?php echo htmlspecialchars($detalles_articulo['titulo']); ?></p>
            <p><strong>Resumen:</strong> <?php echo htmlspecialchars($detalles_articulo['resumen']); ?></p>
        </div>

        <h2 style="font-size: 16px; color: #555;">Revisiones</h2>
        <div style="margin-bottom: 20px;">
            <?php if (!empty($revisores)): ?>
                <?php foreach ($revisores as $revisor): ?>
                    <a href="revisiones.php?revision=<?php echo htmlspecialchars($revisor); ?>" style="font-size: 12px; padding: 5px 10px; margin-right: 10px; background-color: #007BFF; color: white; text-decoration: none; border-radius: 5px;">R<?php echo htmlspecialchars($revisor); ?> Consultar</a>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No hay revisores asignados a este artículo.</p>
            <?php endif; ?>
        </div>

        <!-- Mostrar detalles del artículo -->
        <div style="margin-top: 20px;">
            <h2 style="font-size: 16px; color: #555;">Detalles del Artículo</h2>
            <p><strong>Título:</strong> <?php echo htmlspecialchars($detalles_articulo['titulo']); ?></p>
            <p><strong>Resumen:</strong> <?php echo htmlspecialchars($detalles_articulo['resumen']); ?></p>

            <?php if ($es_revisor): ?>
                <a href="editar_articulo.php?id_articulo=<?php echo $articulo_seleccionado; ?>" style="padding: 10px 15px; background-color: #28a745; color: white; text-decoration: none; border-radius: 5px;">Editar</a>
                <?php if ($es_revisor && in_array($_SESSION['usuario'], $revisores) || $_SESSION['rol'] === 'admin' || $_SESSION['rol'] === 'Jefe Comite de Programa'): ?>
                    <a href="crear_articulo.php" style="padding: 10px 15px; background-color: #28a745; color: white; text-decoration: none; border-radius: 5px;">Crear</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Formulario de Evaluación se muestra solo si se accede a una revisión -->
    <?php if (isset($_GET['revision'])): ?>
        <?php
        // Depuración de valores
        echo "<p style='color: blue;'>Valor de revision: " . htmlspecialchars($_GET['revision']) . "</p>";
        echo "<p style='color: blue;'>Usuario actual: " . htmlspecialchars($_SESSION['usuario']) . "</p>";
        echo "<p style='color: blue;'>Rol actual: " . htmlspecialchars($_SESSION['rol']) . "</p>";
        echo "<p style='color: blue;'>Es revisor: " . ($es_revisor ? 'Sí' : 'No') . "</p>";
        echo "<p style='color: blue;'>Revisores asignados: " . implode(', ', $revisores) . "</p>";
        echo "<p style='color: blue;'>Resultados publicados: " . ($resultados_publicados ? 'Sí' : 'No') . "</p>";
        ?>
        <?php if (!$resultados_publicados && ($es_revisor && in_array($_SESSION['usuario'], $revisores) || $_SESSION['rol'] === 'admin' || $_SESSION['rol'] === 'Jefe Comite de Programa')): ?>
            <p style="font-size: 14px; color: #555;">Aún no está listo.</p>
            <table border="1" style="width: 100%; margin-top: 20px;">
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Resumen</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <form method="POST" action="crear_articulo.php">
                            <td>
                                <input type="text" id="titulo" name="titulo" required style="width: 100%;">
                            </td>
                            <td>
                                <textarea id="resumen" name="resumen" required style="width: 100%;"></textarea>
                            </td>
                            <td>
                                <button type="submit" style="padding: 10px 15px; background-color: #28a745; color: white; border: none; border-radius: 5px;">Crear Artículo</button>
                            </td>
                        </form>
                    </tr>
                </tbody>
            </table>
        <?php elseif ($resultados_publicados): ?>
            <p style="font-size: 14px; color: #555;">El formulario de evaluación no está disponible en esta página.</p>
        <?php endif; ?>
    <?php endif; ?>

    <a href="dashboard.php" style="font-family: Arial, sans-serif; font-size: 14px; color: #007BFF; text-decoration: none;">Volver al inicio</a>
</body>
</html>
