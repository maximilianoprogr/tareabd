<?php
// procesar_quitar_revisores.php

session_start();
include('conexion.php');

// Verificar si el usuario tiene permisos de jefe del comité
if (!isset($_SESSION['rol']) || ($_SESSION['rol'] !== 'admin' && $_SESSION['rol'] !== 'Jefe Comite de Programa')) {
    header("Location: dashboard.php");
    exit();
}

// Verificar que se haya enviado el formulario correctamente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_articulo'], $_POST['rut_revisores'])) {
    $id_articulo = $_POST['id_articulo'];
    $rut_revisores = $_POST['rut_revisores'];

    // Eliminar los revisores seleccionados del artículo
    $sql_delete = "DELETE FROM Articulo_Revisor WHERE id_articulo = ? AND rut_revisor = ?";
    $stmt_delete = $pdo->prepare($sql_delete);

    foreach ($rut_revisores as $rut_revisor) {
        $stmt_delete->execute([$id_articulo, $rut_revisor]);
    }

    // Redirigir de vuelta a la página de asignar artículos con un mensaje de éxito
    $_SESSION['mensaje_exito'] = "Revisores eliminados exitosamente.";
    header("Location: asignar_articulos.php");
    exit();
} else {
    echo "<p style='color: red;'>Error: No se enviaron los datos correctamente.</p>";
    echo "<a href='asignar_articulos.php'>Volver</a>";
    exit();
}
?>
