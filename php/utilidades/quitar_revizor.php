<?php
// Iniciar sesión para manejar autenticación de usuarios
session_start();

// Incluir archivo de conexión a la base de datos
include('../conexion.php');

// Obtener el ID del artículo y el nombre del revisor desde los parámetros GET
$id_articulo = $_GET['id_articulo'] ?? null;
$revisor_nombre = $_GET['revisor'] ?? null;

// Validar que se hayan proporcionado los datos necesarios
if (!$id_articulo || !$revisor_nombre) {
    header("Location: ../asignar_articulos.php?error=Faltan+datos"); // Redirigir con mensaje de error si faltan datos
    exit;
}

// Consultar el RUT del revisor en la base de datos
$stmt = $pdo->prepare("SELECT rut FROM Usuario WHERE nombre = ?");
$stmt->execute([$revisor_nombre]);
$revisor = $stmt->fetch();

// Validar que el revisor exista
if (!$revisor) {
    header("Location: ../asignar_articulos.php?error=Revisor+no+encontrado"); // Redirigir con mensaje de error si el revisor no existe
    exit;
}

$rut_revisor = $revisor['rut']; // Obtener el RUT del revisor

// Eliminar la asignación del revisor al artículo
$stmt = $pdo->prepare("DELETE FROM Articulo_Revisor WHERE id_articulo = ? AND rut_revisor = ?");
$stmt->execute([$id_articulo, $rut_revisor]);

// Redirigir con mensaje de éxito
header("Location: ../asignar_articulos.php?exito=Revisor+eliminado");
exit;
?>