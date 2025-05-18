<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

// Validar que id_articulo esté definido
if (!isset($_GET['id_articulo']) || empty($_GET['id_articulo'])) {
    echo "<p>Error: No se especificó un artículo válido.</p>";
    echo '<a href="acceso_articulo.php">Volver</a>';
    exit();
}

$revision = isset($_GET['revision']) ? htmlspecialchars($_GET['revision']) : 'Desconocida';

// Verificar si los resultados de la revisión están publicados
include('conexion.php');
$resultados_publicados = false;
if (isset($_GET['revision'])) {
    $sql_resultados = "SELECT COUNT(*) FROM Evaluacion_Articulo WHERE rut_revisor = ?";
    $stmt_resultados = $pdo->prepare($sql_resultados);
    $stmt_resultados->execute([$_GET['revision']]);
    $resultados_publicados = $stmt_resultados->fetchColumn() > 0;
}

// Verificar si se seleccionó una revisión, de lo contrario redirigir al usuario
if (!isset($_GET['revision'])) {
    header("Location: acceso_articulo.php?error=seleccione_revision");
    exit();
}

// Depurar los valores de entrada y la consulta SQL
echo "<p>Depuración: rut_revisor = " . htmlspecialchars($_GET['revision']) . ", id_articulo = " . htmlspecialchars($_GET['id_articulo']) . "</p>";

// Obtener las respuestas de la base de datos
$sql_respuestas = "SELECT calidad_tecnica, originalidad, valoracion_global, argumentos_valoracion, comentarios_autores FROM Evaluacion_Articulo WHERE rut_revisor = ? AND id_articulo = ?";
$stmt_respuestas = $pdo->prepare($sql_respuestas);
$stmt_respuestas->execute([$_GET['revision'], $_GET['id_articulo']]);
// Verificar si hay resultados en la consulta
$respuestas = $stmt_respuestas->fetch(PDO::FETCH_ASSOC);

// Redirigir a una página que indique que el revisor aún no ha completado la evaluación
if (!$respuestas) {
    header("Location: evaluacion_incompleta.php?revisor=" . urlencode($_GET['revision']) . "&id_articulo=" . urlencode($_GET['id_articulo']));
    exit();
}

// Depurar los resultados de la consulta
if ($respuestas) {
    echo "<pre>Datos recuperados: ";
    print_r($respuestas);
    echo "</pre>";
} else {
    echo "<p>No se encontraron datos para este artículo y revisor.</p>";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revisión <?php echo $revision; ?></title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <h1 style="font-family: Arial, sans-serif; color: #333;">Revisión <?php echo $revision; ?></h1>

    <!-- Mostrar el formulario solo si se seleccionó una revisión -->
    <?php if (isset($_GET['revision'])): ?>
        <?php if ($resultados_publicados): ?>
            <!-- Validar si $respuestas tiene datos antes de usarla -->
            <?php if (isset($respuestas) && $respuestas): ?>
                <?php
                // Manejar valores vacíos o nulos en los datos recuperados
                $calidad_tecnica = $respuestas['calidad_tecnica'] ?? 'No especificado';
                $originalidad = $respuestas['originalidad'] ?? 'No especificado';
                $valoracion_global = $respuestas['valoracion_global'] ?? 'No especificado';
                $argumentos_valoracion = $respuestas['argumentos_valoracion'] ?? 'No especificado';
                $comentarios_autores = $respuestas['comentarios_autores'] ?? 'No especificado';

                // Ajustar los checkboxes para que se marquen correctamente si el valor es '1'
                echo "<h2 style=\"font-family: Arial, sans-serif; color: #555;\">Formulario de Evaluación (Modo Consulta)</h2>";
                echo "<form style=\"border: 1px solid #ccc; padding: 15px;\">";
                echo "<div style=\"margin-bottom: 15px;\">";
                echo "<label for=\"calidad_tecnica\" style=\"font-size: 14px; display: block; margin-bottom: 5px;\">Calidad Técnica:</label>";
                echo "<input type=\"checkbox\" id=\"calidad_tecnica\" name=\"calidad_tecnica\" disabled " . ($respuestas['calidad_tecnica'] == 1 ? 'checked' : '') . ">";
                echo "</div>";

                echo "<div style=\"margin-bottom: 15px;\">";
                echo "<label for=\"originalidad\" style=\"font-size: 14px; display: block; margin-bottom: 5px;\">Originalidad:</label>";
                echo "<input type=\"checkbox\" id=\"originalidad\" name=\"originalidad\" disabled " . ($respuestas['originalidad'] == 1 ? 'checked' : '') . ">";
                echo "</div>";

                echo "<div style=\"margin-bottom: 15px;\">";
                echo "<label for=\"valoracion_global\" style=\"font-size: 14px; display: block; margin-bottom: 5px;\">Valoración Global:</label>";
                echo "<input type=\"checkbox\" id=\"valoracion_global\" name=\"valoracion_global\" disabled " . ($respuestas['valoracion_global'] == 1 ? 'checked' : '') . ">";
                echo "</div>";

                echo "<div style=\"margin-bottom: 15px;\">";
                echo "<label for=\"argumentos_valoracion\" style=\"font-size: 14px; display: block; margin-bottom: 5px;\">Argumentos de Valoración Global:</label>";
                echo "<textarea id=\"argumentos_valoracion\" name=\"argumentos_valoracion\" rows=\"3\" style=\"width: 100%; font-size: 12px; padding: 5px; border: 1px solid #ccc;\" readonly>" . htmlspecialchars($argumentos_valoracion) . "</textarea>";
                echo "</div>";

                echo "<div style=\"margin-bottom: 15px;\">";
                echo "<label for=\"comentarios_autores\" style=\"font-size: 14px; display: block; margin-bottom: 5px;\">Comentarios a Autores:</label>";
                echo "<textarea id=\"comentarios_autores\" name=\"comentarios_autores\" rows=\"3\" style=\"width: 100%; font-size: 12px; padding: 5px; border: 1px solid #ccc;\" readonly>" . htmlspecialchars($comentarios_autores) . "</textarea>";
                echo "</div>";
                echo "</form>";
                ?>
            <?php else: ?>
                <!-- Mostrar un mensaje si no hay datos -->
                <p>No se encontraron datos válidos para este artículo y revisor.</p>
            <?php endif; ?>

        <?php else: ?>
            <!-- Formulario editable si los resultados no están publicados -->
            <h2 style="font-family: Arial, sans-serif; color: #555;">Formulario de Evaluación</h2>
            <form style="border: 1px solid #ccc; padding: 15px;">
                <div style="margin-bottom: 15px;">
                    <label for="calidad_tecnica" style="font-size: 14px; display: block; margin-bottom: 5px;">Calidad Técnica:</label>
                    <input type="checkbox" id="calidad_tecnica" name="calidad_tecnica">
                </div>

                <div style="margin-bottom: 15px;">
                    <label for="originalidad" style="font-size: 14px; display: block; margin-bottom: 5px;">Originalidad:</label>
                    <input type="checkbox" id="originalidad" name="originalidad">
                </div>

                <div style="margin-bottom: 15px;">
                    <label for="valoracion_global" style="font-size: 14px; display: block; margin-bottom: 5px;">Valoración Global:</label>
                    <input type="checkbox" id="valoracion_global" name="valoracion_global">
                </div>

                <div style="margin-bottom: 15px;">
                    <label for="argumentos_valoracion" style="font-size: 14px; display: block; margin-bottom: 5px;">Argumentos de Valoración Global:</label>
                    <textarea id="argumentos_valoracion" name="argumentos_valoracion" rows="3" style="width: 100%; font-size: 12px; padding: 5px; border: 1px solid #ccc;"></textarea>
                </div>

                <div style="margin-bottom: 15px;">
                    <label for="comentarios_autores" style="font-size: 14px; display: block; margin-bottom: 5px;">Comentarios a Autores:</label>
                    <textarea id="comentarios_autores" name="comentarios_autores" rows="3" style="width: 100%; font-size: 12px; padding: 5px; border: 1px solid #ccc;"></textarea>
                </div>

                <button type="submit" style="font-size: 14px; padding: 10px 20px; background-color: #4CAF50; color: white; border: none; cursor: pointer;">Enviar Evaluación</button>
            </form>
        <?php endif; ?>
    <?php endif; ?>

    <br><br>
    <a href="acceso_articulo.php" style="font-family: Arial, sans-serif; font-size: 14px; color: #007BFF; text-decoration: none;">Volver</a>
    <br><br>
    <a href="dashboard.php" style="font-family: Arial, sans-serif; font-size: 14px; color: #007BFF; text-decoration: none;">Volver al inicio</a>

    <script>
        document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const field = this.name;
                const value = this.checked ? 1 : 0;
                const idArticulo = <?php echo json_encode($_GET['id_articulo']); ?>;
                const rutRevisor = <?php echo json_encode($_GET['revision']); ?>;

                fetch('procesar_evaluacion.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        field,
                        value,
                        id_articulo: idArticulo,
                        rut_revisor: rutRevisor
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log('Actualización exitosa');
                    } else {
                        console.error('Error al actualizar:', data.message);
                    }
                })
                .catch(error => console.error('Error en la solicitud:', error));
            });
        });
    </script>
</body>
</html>
