<?php
// Inicia la sesión para verificar si el usuario está autenticado.
session_start();

// Redirige al usuario al login si no está autenticado.
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

// Incluye la conexión a la base de datos.
include('conexion.php');

// Obtiene el rol del usuario autenticado desde la base de datos.
$stmt_rol = $pdo->prepare("SELECT tipo FROM Usuario WHERE rut = ?");
$stmt_rol->execute([$_SESSION['usuario']]);
$rol = $stmt_rol->fetchColumn();

// Almacena el rol en la sesión para su uso posterior.
$_SESSION['rol'] = $rol;

// Determina el rol del usuario.
$es_revisor = strcasecmp($rol, 'revisor') === 0;
$es_jefe_comite = strcasecmp($rol, 'Jefe Comite de Programa') === 0;
$es_autor = strcasecmp($rol, 'autor') === 0;

// Muestra mensajes según el rol del usuario.
if ($es_autor) {
    echo '<p style="font-family: Arial, sans-serif; color: red;">No puedes opinar sobre este artículo porque no eres el revisor asignado.</p>';
} elseif ($es_jefe_comite) {
    echo '<p style="font-family: Arial, sans-serif; color: blue;">Acceso como Jefe del Comité de Programa.</p>';
} elseif ($es_revisor) {
    echo '<p style="font-family: Arial, sans-serif; color: green;">Acceso como Revisor.</p>';
} else {
    echo '<p style="font-family: Arial, sans-serif; color: orange;">Rol no reconocido.</p>';
}

// Obtiene la lista de artículos disponibles.
$sql_articulos = "SELECT id_articulo, titulo FROM Articulo";
$stmt_articulos = $pdo->query($sql_articulos);
$articulos = $stmt_articulos->fetchAll();

// Verifica si se seleccionó un artículo y obtiene sus detalles.
$articulo_seleccionado = isset($_GET['id_articulo']) ? $_GET['id_articulo'] : null;
$detalles_articulo = null;
$revisores = [];

if ($articulo_seleccionado) {
    // Obtiene los detalles del artículo seleccionado.
    $sql_detalles = "SELECT titulo, resumen FROM Articulo WHERE id_articulo = ?";
    $stmt_detalles = $pdo->prepare($sql_detalles);
    $stmt_detalles->execute([$articulo_seleccionado]);
    $detalles_articulo = $stmt_detalles->fetch();

    // Obtiene los revisores asignados al artículo.
    $sql_revisores = "SELECT ar.rut_revisor FROM Articulo_Revisor ar WHERE ar.id_articulo = ?";
    $stmt_revisores = $pdo->prepare($sql_revisores);
    $stmt_revisores->execute([$articulo_seleccionado]);
    $revisores = $stmt_revisores->fetchAll(PDO::FETCH_COLUMN);
}

// Redirige al usuario si no se seleccionó un artículo.
if (!$articulo_seleccionado) {
    header("Location: dashboard.php?error=seleccione_articulo");
    exit();
}

// Muestra un mensaje si no hay revisores asignados al artículo.
if (empty($revisores)) {
    echo '<p style="font-family: Arial, sans-serif; color: red;">No hay revisores asignados a este artículo.</p>';
    echo '<a href="dashboard.php" style="font-family: Arial, sans-serif; font-size: 14px; color: #007BFF; text-decoration: none;">Volver al inicio</a>';
}

// Verifica si los resultados del artículo han sido publicados.
$resultados_publicados = false;
if ($articulo_seleccionado) {
    $sql_resultados = "SELECT COUNT(*) FROM Evaluacion_Articulo WHERE id_articulo = ?";
    $stmt_resultados = $pdo->prepare($sql_resultados);
    $stmt_resultados->execute([$articulo_seleccionado]);
    $resultados_publicados = $stmt_resultados->fetchColumn() > 0;
}

// Muestra información de depuración sobre el usuario y su rol.
echo "<p style='color: blue;'>Rol en la sesión: " . htmlspecialchars($_SESSION['rol']) . "</p>";
echo "<p style='color: green;'>Valor de es_autor: " . (isset($es_autor) && $es_autor ? 'true' : 'false') . "</p>";
echo "<p style='color: green;'>Depuración: Rol en la sesión: " . htmlspecialchars($_SESSION['rol']) . "</p>";
echo "<p style='color: green;'>Depuración: Usuario en la sesión: " . htmlspecialchars($_SESSION['usuario']) . "</p>";
echo "<p style='color: green;'>Revisores obtenidos: " . (empty($revisores) ? 'Ninguno' : implode(', ', $revisores)) . "</p>";
echo "<p style='color: purple;'>Rol obtenido de la base de datos: " . htmlspecialchars($rol) . "</p>";
echo "<p style='color: orange;'>Es revisor: " . ($es_revisor ? 'true' : 'false') . "</p>";
echo "<p style='color: orange;'>Es jefe de comité: " . ($es_jefe_comite ? 'true' : 'false') . "</p>";
echo "<p style='color: orange;'>Es autor: " . ($es_autor ? 'true' : 'false') . "</p>";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <!-- Configuración básica del documento HTML -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso al Artículo</title>
    <!-- Enlaces a hojas de estilo -->
    <link rel="stylesheet" href="css/estilos.css">
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body style="font-family: Arial, sans-serif; margin: 20px;">
    <div class="container">
        <!-- Mensaje si no hay revisores asignados -->
        <?php if (empty($revisores)) { ?>
            <p class="message">No hay revisores asignados a este artículo.</p>
            <a href="dashboard.php" class="button">Volver al inicio</a>
        <?php } ?>

        <!-- Lista de artículos enviados -->
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
                            <?php if (!$es_autor): ?>
                                <a href="opinar.php?revision=<?php echo htmlspecialchars($articulo_seleccionado); ?>&revisor=<?php echo htmlspecialchars($revisor); ?>" class="button">R<?php echo htmlspecialchars($revisor); ?> Opinar</a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="message">No hay revisores asignados a este artículo.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php echo "<div style='position: fixed; top: 10px; left: 10px; font-weight: bold; background-color: #f0f0f0; padding: 5px; border: 1px solid #ccc;'>Rol: " . htmlspecialchars($_SESSION['rol']) . "</div>"; ?>

    <?php if (isset($_GET['revision'])): ?>
        <?php
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
    <div style="margin-top: 20px;">
        <a href="inicio.php" class="button" style="background-color: #007BFF; color: white; padding: 10px 15px; border: none; border-radius: 5px; text-decoration: none;">Volver</a>
    </div>
</body>
</html>
