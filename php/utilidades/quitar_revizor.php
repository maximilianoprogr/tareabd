<?php
session_start();
include('../conexion.php');

$id_articulo = $_GET['id_articulo'] ?? null;
$revisor_nombre = $_GET['revisor'] ?? null;

if (!$id_articulo || !$revisor_nombre) {
    header("Location: ../asignar_articulos.php?error=Faltan+datos");
    exit;
}

$stmt = $pdo->prepare("SELECT rut FROM Usuario WHERE nombre = ?");
$stmt->execute([$revisor_nombre]);
$revisor = $stmt->fetch();

if (!$revisor) {
    header("Location: ../asignar_articulos.php?error=Revisor+no+encontrado");
    exit;
}

$rut_revisor = $revisor['rut'];

$stmt = $pdo->prepare("DELETE FROM Articulo_Revisor WHERE id_articulo = ? AND rut_revisor = ?");
$stmt->execute([$id_articulo, $rut_revisor]);

header("Location: ../asignar_articulos.php?exito=Revisor+eliminado");
exit;
?>