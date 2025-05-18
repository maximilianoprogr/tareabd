<?php
session_start();
include('../php/conexion.php');

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

// Obtener el rol del usuario desde la base de datos
$stmt_rol = $pdo->prepare("SELECT tipo FROM Usuario WHERE rut = ?");
$stmt_rol->execute([$_SESSION['usuario']]);
$rol = $stmt_rol->fetchColumn();

// Actualizar el rol en la sesión con el valor obtenido de la base de datos
$_SESSION['rol'] = $rol;

// Verificar si el usuario no es Jefe del Comité de Programa
if (strcasecmp($rol, 'Jefe Comite de Programa') !== 0) {
    echo "<p style='color: red; font-weight: bold;'>Acceso denegado: Solo el Jefe del Comité de Programa puede acceder a esta página.</p>";
    header("Refresh: 3; url=inicio.php"); // Redirigir al inicio después de 3 segundos
    exit();
}

// Consulta para obtener artículos con autores, tópicos y revisores (como arrays)
$sql = "
SELECT 
    a.id_articulo,
    a.titulo,
    GROUP_CONCAT(DISTINCT u.nombre SEPARATOR '|||') AS autores,
    GROUP_CONCAT(DISTINCT t.nombre SEPARATOR '|||') AS topicos,
    GROUP_CONCAT(DISTINCT r.nombre SEPARATOR '|||') AS revisores
FROM Articulo a
LEFT JOIN Autor_Articulo aa ON a.id_articulo = aa.id_articulo
LEFT JOIN Usuario u ON aa.rut_autor = u.rut
LEFT JOIN Articulo_Topico at ON a.id_articulo = at.id_articulo
LEFT JOIN Topico t ON at.id_topico = t.id_topico
LEFT JOIN Articulo_Revisor ar ON a.id_articulo = ar.id_articulo
LEFT JOIN Usuario r ON ar.rut_revisor = r.rut
GROUP BY a.id_articulo, a.titulo
ORDER BY a.id_articulo ASC
";

$stmt = $pdo->query($sql);
$articulos = $stmt->fetchAll();

// Obtener todos los revisores con sus tópicos y artículos asignados
$sql_revisores = "
SELECT 
    u.rut,
    u.nombre,
    GROUP_CONCAT(DISTINCT t.nombre SEPARATOR ', ') AS topicos,
    COUNT(DISTINCT ar2.id_articulo) AS articulos_asignados,
    GROUP_CONCAT(DISTINCT a2.titulo SEPARATOR ', ') AS titulos_asignados
FROM Revisor r
JOIN Usuario u ON r.rut = u.rut
LEFT JOIN Revisor_Topico rt ON r.rut = rt.rut_revisor
LEFT JOIN Topico t ON rt.id_topico = t.id_topico
LEFT JOIN Articulo_Revisor ar2 ON r.rut = ar2.rut_revisor
LEFT JOIN Articulo a2 ON ar2.id_articulo = a2.id_articulo
GROUP BY u.rut, u.nombre
ORDER BY u.nombre
";
$stmt_revisores = $pdo->query($sql_revisores);
$revisores = $stmt_revisores->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de Artículos</title>
    <link rel="stylesheet" href="../css/estilos_articulos.css">
    <style>
        .subtabla { border-collapse: collapse; width: 100%; }
        .subtabla td { border: none; padding: 2px 8px; background: #f9f9f9; }
        .resaltar-pocos-revisores {
            background-color: #fff3cd;
        }
    </style>
</head>
<body>
<a href="dashboard.php">Volver al inicio</a>
<h2>Listado de Artículos</h2>
<table border="1" cellpadding="6" style="border-collapse:collapse;width:100%">
    <tr style="background:#f2f2f2">
        <th>N°</th>
        <th>Título</th>
        <th>Autores</th>
        <th>Tópicos</th>
        <th>Revisores</th>
        <th>Acciones</th>
    </tr>
    <?php foreach ($articulos as $articulo): 
        $revisores_art = array_filter(explode('|||', $articulo['revisores'] ?? ''));
        $clase_resaltar = count($revisores_art) < 2 ? 'resaltar-pocos-revisores' : '';
    ?>
    <tr class="<?= $clase_resaltar ?>">
        <td><?= htmlspecialchars($articulo['id_articulo']) ?></td>
        <td><?= htmlspecialchars($articulo['titulo']) ?></td>
        <td>
            <?php
            $autores = array_filter(explode('|||', $articulo['autores'] ?? ''));
            echo $autores ? nl2br(htmlspecialchars(implode("\n", $autores))) : 'Sin autores';
            ?>
        </td>
        <td>
            <?php
            $topicos = array_filter(explode('|||', $articulo['topicos'] ?? ''));
            echo $topicos ? nl2br(htmlspecialchars(implode("\n", $topicos))) : 'Sin tópicos';
            ?>
        </td>
        <td>
            <?php
            if ($revisores_art) {
                foreach ($revisores_art as $revisor) {
                    echo htmlspecialchars($revisor);
                    // Botón para quitar revisor (envía id_articulo y nombre del revisor)
                    echo ' <a href="utilidades/quitar_revizor.php?id_articulo=' . urlencode($articulo['id_articulo']) .
                        '&revisor=' . urlencode($revisor) . '" onclick="return confirm(\'¿Quitar este revisor del artículo?\')" style="color:#fff;background:#dc3545;padding:2px 8px;border-radius:3px;text-decoration:none;margin-left:6px;font-size:13px;">Quitar</a><br>';
                }
            } else {
                echo 'Sin revisores';
            }
            ?>
        </td>
        <td>
            <a href="utilidades/asignar_revisor.php?id_articulo=<?= urlencode($articulo['id_articulo']) ?>">Asignar revisor</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<!-- Tabla de revisores al final -->
<h2 style="margin-top:40px;">Listado de Revisores</h2>
<table border="1" cellpadding="6" style="border-collapse:collapse;width:100%">
    <tr style="background:#f2f2f2">
        <th>Nombre</th>
        <th>Tópicos</th>
        <th>Artículos asignados</th>
        <th>Acción</th>
    </tr>
    <?php foreach ($revisores as $revisor): ?>
    <tr>
        <td><?= htmlspecialchars($revisor['nombre']) ?></td>
        <td><?= $revisor['topicos'] ? nl2br(htmlspecialchars(str_replace(', ', "\n", $revisor['topicos']))) : 'Sin tópicos'; ?></td>
        <td>
            <?php
            if ($revisor['titulos_asignados']) {
                echo nl2br(htmlspecialchars(str_replace(', ', "\n", $revisor['titulos_asignados'])));
            } else {
                echo 'Sin artículos';
            }
            ?>
        </td>
        <td>
            <a href="utilidades/asignar_articulo.php?rut_revisor=<?= urlencode($revisor['rut']) ?>" style="background:#007BFF;color:#fff;padding:4px 10px;border-radius:4px;text-decoration:none;font-size:13px;">
                Asignar artículo
            </a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
</body>
</html>