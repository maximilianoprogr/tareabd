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
    <div class="container">
        <?php if (empty($revisores)) { ?>
            <p class="message">No hay revisores asignados a este artículo.</p>
            <a href="dashboard.php" class="button">Volver al inicio</a>
        <?php } ?>

        <h1>Artículos Enviados</h1>
        <ul>
            <?php foreach ($articulos as $articulo): ?>
                <li><a href="acceso_articulo.php?id_articulo=<?php echo $articulo['id_articulo']; ?>">Artículo: <?php echo htmlspecialchars($articulo['titulo']); ?></a></li>
            <?php endforeach; ?>
        </ul>

        <?php if ($detalles_articulo): ?>
            <h2>Datos del Artículo</h2>
            <div>
                <p><strong>ID:</strong> <?php echo htmlspecialchars($articulo_seleccionado); ?></p>
                <p><strong>Título:</strong> <?php echo htmlspecialchars($detalles_articulo['titulo']); ?></p>
                <p><strong>Resumen:</strong> <?php echo htmlspecialchars($detalles_articulo['resumen']); ?></p>
            </div>

            <h2>Revisiones</h2>
            <div>
                <?php if (!empty($revisores)): ?>
                    <?php foreach ($revisores as $revisor): ?>
                        <a href="revisiones.php?revision=<?php echo htmlspecialchars($revisor); ?>&id_articulo=<?php echo htmlspecialchars($articulo_seleccionado); ?>" class="button">R<?php echo htmlspecialchars($revisor); ?> Consultar</a>
                        <div style="margin-top: 5px;">
                            <a href="opinar.php?revision=<?php echo htmlspecialchars($articulo_seleccionado); ?>&revisor=<?php echo htmlspecialchars($revisor); ?>" class="button">R<?php echo htmlspecialchars($revisor); ?> Opinar</a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="message">No hay revisores asignados a este artículo.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

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
