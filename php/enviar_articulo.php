<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    // Si no hay sesión iniciada, redirige al login
    header("Location: login.php");
    exit();
}

require_once 'conexion.php';

// Obtener los tópicos desde la base de datos
$stmt = $pdo->query("SELECT id_topico, nombre FROM Topico");
$topicos = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enviar Artículo</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <h1 style="font-family: Arial, sans-serif; color: #333;">Enviar Artículo</h1>
    <form action="procesar_envio.php" method="POST" enctype="multipart/form-data">
        <label for="titulo" style="font-family: Arial, sans-serif; font-size: 14px;">Título del artículo:</label>
        <input type="text" id="titulo" name="titulo" required style="margin-bottom: 10px;">

        <label for="resumen" style="font-family: Arial, sans-serif; font-size: 14px;">Resumen del artículo:</label>
        <textarea id="resumen" name="resumen" rows="2" style="width: 50%; height: 50px; font-family: Arial, sans-serif; font-size: 12px; margin-bottom: 20px;" required></textarea>

        <h2 style="font-family: Arial, sans-serif; color: #555; margin-top: 30px;">Autores</h2>
        <table style="width: 50%; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 12px; margin-bottom: 20px;" border="1">
            <tr>
                <th>Nombre</th>
                <th>Email</th>
                <th>Contacto</th>
            </tr>
            <tr>
                <td><input type="text" name="autor_nombre[]" required></td>
                <td><input type="email" name="autor_email[]" required></td>
                <td><input type="checkbox" name="autor_contacto[]" style="width: 20px; height: 20px;"></td>
            </tr>
            <tr>
                <td><input type="text" name="autor_nombre[]"></td>
                <td><input type="email" name="autor_email[]"></td>
                <td><input type="checkbox" name="autor_contacto[]" style="width: 20px; height: 20px;"></td>
            </tr>
        </table>

        <h2 style="font-family: Arial, sans-serif; color: #555; margin-top: 30px;">Tópicos del Artículo</h2>
        <table style="width: 30%; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 12px; margin-bottom: 20px;" border="1">
            <tr>
                <?php foreach ($topicos as $topico): ?>
                    <td>
                        <label style="display: block;">
                            <input type="checkbox" name="topicos[]" value="<?= $topico['id_topico'] ?>"> <?= htmlspecialchars($topico['nombre']) ?>
                        </label>
                    </td>
                <?php endforeach; ?>
            </tr>
        </table>

        <button type="submit" style="font-family: Arial, sans-serif; font-size: 14px; background-color: #4CAF50; color: white; border: none; padding: 10px 20px; cursor: pointer;">Enviar</button>
    </form>
    <br>
    <a href="dashboard.php" style="font-family: Arial, sans-serif; font-size: 14px; color: #007BFF; text-decoration: none;">Volver al inicio</a>
</body>
</html>
