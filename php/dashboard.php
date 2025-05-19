<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="../css/dashboard.css"> <!-- Archivo CSS externo -->
</head>
<body>
<?php
session_start();

if (isset($_SESSION['message'])) {
    echo "<div class='message'>" . $_SESSION['message'] . "</div>";
    unset($_SESSION['message']);
}

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

include('conexion.php');

// Obtener el rol del usuario desde la base de datos
$stmt_rol = $pdo->prepare("SELECT tipo FROM Usuario WHERE rut = ?");
$stmt_rol->execute([$_SESSION['usuario']]);
$rol = $stmt_rol->fetchColumn();

echo "<div class='welcome'>Bienvenido, " . $_SESSION['usuario'] . "</div>";
echo "<div class='user-role'><strong>Rol:</strong> " . htmlspecialchars($rol) . "</div>";

// Mostrar totales según el rol
if (strcasecmp($rol, 'autor') === 0) {
    $stmt_func = $pdo->prepare("SELECT contar_articulos_usuario(?) AS total_articulos");
    $stmt_func->execute([$_SESSION['usuario']]);
    $total_articulos = $stmt_func->fetchColumn();
    echo "<div class='user-role'><strong>Total de artículos enviados:</strong> " . htmlspecialchars($total_articulos) . "</div>";
} elseif (strcasecmp($rol, 'revisor') === 0) {
    $stmt_func = $pdo->prepare("SELECT contar_articulos_a_revisar(?) AS total_a_revisar");
    $stmt_func->execute([$_SESSION['usuario']]);
    $total_a_revisar = $stmt_func->fetchColumn();
    echo "<div class='user-role'><strong>Total de artículos a revisar:</strong> " . htmlspecialchars($total_a_revisar) . "</div>";
}

// Obtener los últimos 10 artículos
$sql_ultimos = "SELECT id_articulo, titulo, fecha_envio FROM Articulo ORDER BY fecha_envio DESC LIMIT 10";
$stmt_ultimos = $pdo->query($sql_ultimos);
$ultimos_articulos = $stmt_ultimos->fetchAll();
?>

<div class="container">
    <a href="utilidades/logout.php" class="logout-btn">Cerrar sesión</a> <!-- Opción para cerrar sesión -->

    <form action="buscar_articulos.php" method="GET" class="search-form">
        <input type="text" name="query" placeholder="Buscar artículos..." class="search-input">
        <button type="submit" class="search-btn">Buscar</button>
    </form>

    <div class="acciones">
        <button onclick="location.href='enviar_articulo.php'" class="action-btn">Enviar Artículo</button>
        <button onclick="location.href='acceso_articulo.php?id_articulo=1'" class="action-btn">Acceso al Artículo</button>
        <button onclick="location.href='gestionar_revisores.php'" class="action-btn">Gestión de Revisores</button>
        <button onclick="location.href='asignar_articulos.php'" class="action-btn">Asignación de Artículos</button>
    </div>

    <!-- Mostrar los últimos 10 artículos -->
    <div class="ultimos-articulos" style="margin-top: 30px;">
        <h2>Articulos más recientes</h2>
        <table border="1" style="width:100%; border-collapse: collapse;">
            <tr>
                <th>Título</th>
                <th>Fecha de Envío</th>
            </tr>
            <?php foreach ($ultimos_articulos as $articulo): ?>
            <tr>
                <td><?php echo htmlspecialchars($articulo['titulo']); ?></td>
                <td><?php echo htmlspecialchars($articulo['fecha_envio']); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>
</body>
</html>
