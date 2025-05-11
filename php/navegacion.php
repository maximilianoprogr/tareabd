<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    echo "<script>alert('Acceso denegado. Esta sección es solo para el jefe del comité.');</script>";
    header('Location: ../php/dashboard.php');
    exit();
}
?>

<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <a class="navbar-brand" href="#" data-page="../php/index.php">Inicio</a> <!-- Enlace dinámico -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link" href="#" data-page="../php/crear.php">Crear Artículo</a></li>
                <li class="nav-item"><a class="nav-link" href="#" data-page="../php/mostrar.php">Ver Artículos</a></li>
                <li class="nav-item"><a class="nav-link" href="#" data-page="../php/gestionar_revisores.php">Gestionar Revisores</a></li>
                <li class="nav-item"><a class="nav-link" href="#" data-page="../php/buscar_articulos.php">Buscar Artículos</a></li>
                <li class="nav-item"><a class="nav-link" href="#" data-page="../php/crear.php">Enviar Artículo</a></li>
            </ul>
        </div>
    </div>
</nav>
