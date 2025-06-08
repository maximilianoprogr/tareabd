<?php
// Este archivo contiene la barra de navegación principal del sistema.
// Verifica si el usuario tiene el rol adecuado para acceder a esta sección.
// Proporciona enlaces dinámicos a diferentes páginas del sistema.

// Iniciar sesión para manejar autenticación de usuarios
session_start();

// Verificar si el usuario tiene el rol adecuado para acceder a esta sección
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    // Muestra una alerta de acceso denegado
    echo "<script>alert('Acceso denegado. Esta sección es solo para el jefe del comité.');</script>"; // Mostrar alerta de acceso denegado
    // Redirige al dashboard si no tiene permisos
    header('Location: ../php/dashboard.php'); // Redirigir al dashboard si no tiene permisos
    exit();
}
?>

<!-- Define la barra de navegación principal -->
<nav class="navbar navbar-expand-lg navbar-light bg-light"> 
    <!-- Contenedor fluido para la barra de navegación -->
    <div class="container-fluid"> 
        <!-- Enlace dinámico al inicio -->
        <a class="navbar-brand" href="#" data-page="../php/index.php">Inicio</a> 
        <!-- Botón para colapsar la barra en dispositivos pequeños -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"> 
            <!-- Icono del botón de colapsar -->
            <span class="navbar-toggler-icon"></span> 
        </button>
        <!-- Contenedor colapsable de los enlaces de navegación -->
        <div class="collapse navbar-collapse" id="navbarNav"> 
            <!-- Lista de enlaces de navegación -->
            <ul class="navbar-nav"> 
                <!-- Enlace para crear un artículo -->
                <li class="nav-item"><a class="nav-link" href="#" data-page="../php/crear.php">Crear Artículo</a></li> 
                <!-- Enlace para ver los artículos -->
                <li class="nav-item"><a class="nav-link" href="#" data-page="../php/mostrar.php">Ver Artículos</a></li> 
                <!-- Enlace para gestionar revisores -->
                <li class="nav-item"><a class="nav-link" href="#" data-page="../php/gestionar_revisores.php">Gestionar Revisores</a></li> 
                <!-- Acción: gestionar_revisores.php
                     Estilo: Enlace de navegación con diseño personalizado -->
                <!-- Enlace para buscar artículos -->
                <li class="nav-item"><a class="nav-link" href="#" data-page="../php/buscar_articulos.php">Buscar Artículos</a></li> 
                <!-- Enlace para enviar un artículo -->
                <li class="nav-item"><a class="nav-link" href="#" data-page="../php/crear.php">Enviar Artículo</a></li> 
            </ul>
        </div>
    </div>
</nav>
