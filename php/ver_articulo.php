<?php
// Iniciar sesión para manejar autenticación de usuarios
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php"); // Redirigir al login si no está autenticado
    exit();
}

// Validar que se haya proporcionado un ID de artículo
if (!isset($_GET['id_articulo'])) {
    echo "<p style='color: red;'>No se proporcionó un ID de artículo.</p>"; // Mostrar mensaje de error si no se especifica un ID
    exit();
}

// Obtener el ID del artículo desde los parámetros GET
$id_articulo = $_GET['id_articulo'];

// Inicializar variable para verificar si el artículo está listo
$articulo_listo = false; // Variable para verificar si el artículo está listo

if (!$articulo_listo) {
    echo "<p style='font-size: 18px; color: #555;'>Estamos trabajando para usted. El artículo no está listo.</p>"; // Mensaje si el artículo no está listo
}

$revisado = false; // Variable para verificar si el artículo ha sido revisado

if (!$revisado) {
    echo "<p style='font-size: 18px; color: #555;'>El artículo no ha sido revisado aún.</p>"; // Mensaje si el artículo no ha sido revisado
}

// Mostrar formulario de evaluación si el artículo no está listo o no ha sido revisado
if (!$articulo_listo || !$revisado) {
    echo '<h2 style="font-size: 16px; color: #555;">Formulario de Evaluación</h2>';
    echo '<form id="form-evaluacion" action="" method="post" style="border: 1px solid #ccc; padding: 15px;">';
    echo '<div style="margin-bottom: 15px;">';
    echo '<label for="calidad_tecnica" style="font-size: 14px; display: block; margin-bottom: 5px;">Calidad Técnica:</label>';
    echo '<input type="checkbox" id="calidad_tecnica" name="calidad_tecnica">';
    echo '</div>';

    echo '<div style="margin-bottom: 15px;">';
    echo '<label for="originalidad" style="font-size: 14px; display: block; margin-bottom: 5px;">Originalidad:</label>';
    echo '<input type="checkbox" id="originalidad" name="originalidad">';
    echo '</div>';

    echo '<div style="margin-bottom: 15px;">';
    echo '<label for="valoracion_global" style="font-size: 14px; display: block; margin-bottom: 5px;">Valoración Global:</label>';
    echo '<input type="checkbox" id="valoracion_global" name="valoracion_global">';
    echo '</div>';

    echo '<div style="margin-bottom: 15px;">';
    echo '<label for="argumentos_valoracion" style="font-size: 14px; display: block; margin-bottom: 5px;">Argumentos de Valoración Global:</label>';
    echo '<textarea id="argumentos_valoracion" name="argumentos_valoracion" rows="3" style="width: 100%; font-size: 12px; padding: 5px; border: 1px solid #ccc;"></textarea>';
    echo '</div>';

    echo '<div style="margin-bottom: 15px;">';
    echo '<label for="comentarios_autores" style="font-size: 14px; display: block; margin-bottom: 5px;">Comentarios a Autores:</label>';
    echo '<textarea id="comentarios_autores" name="comentarios_autores" rows="3" style="width: 100%; font-size: 12px; padding: 5px; border: 1px solid #ccc;"></textarea>';
    echo '</div>';

    echo '<button type="submit" style="padding: 10px 20px; background-color: #4CAF50; color: white; border: none; cursor: pointer;">Enviar Evaluación</button>';
    echo '</form>';
}

// Incluir archivo de conexión a la base de datos
include('conexion.php');

// Consultar la evaluación del artículo
$sql = "SELECT * FROM Evaluacion_Articulo WHERE id_articulo = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_articulo]);
$evaluacion = $stmt->fetch(PDO::FETCH_ASSOC);

// Mostrar la evaluación si existe
if ($evaluacion) {
    echo '<h2>Formulario de Evaluación (Modo Consulta)</h2>';
    echo '<form>';
    echo '<div>Calidad Técnica: ' . ($evaluacion['calidad_tecnica'] ? 'Sí' : 'No') . '</div>';
    echo '<div>Originalidad: ' . ($evaluacion['originalidad'] ? 'Sí' : 'No') . '</div>';
    echo '<div>Valoración Global: ' . ($evaluacion['valoracion_global'] ? 'Sí' : 'No') . '</div>';
    echo '<div>Argumentos de Valoración Global: ' . htmlspecialchars($evaluacion['resena']) . '</div>';
    echo '<div>Comentarios a Autores: ' . htmlspecialchars($evaluacion['comentarios_autores']) . '</div>';
    echo '</form>';
} else {
    echo '<p>No se encontró evaluación para este artículo.</p>'; // Mensaje si no se encuentra evaluación
}

// Botón para volver al inicio
echo '<button onclick="window.location.href=\'inicio.php\'">Volver al inicio</button>';


?>
