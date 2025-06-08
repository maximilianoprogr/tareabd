<?php
// Configuración de la conexión a la base de datos
$host = '127.0.0.1'; // Dirección del servidor de la base de datos
$db = 'base11'; // Nombre de la base de datos
$user = 'root'; // Usuario de la base de datos
$pass = ''; // Contraseña del usuario de la base de datos
$charset = 'utf8mb4'; // Codificación de caracteres

// Configurar el Data Source Name (DSN) para PDO
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Configurar el modo de errores a excepciones
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Configurar el modo de obtención de datos a asociativo
    PDO::ATTR_EMULATE_PREPARES => false, // Deshabilitar la emulación de consultas preparadas
];

try {
    // Crear una nueva instancia de PDO para la conexión a la base de datos
    $pdo = new PDO($dsn, $user, $pass, $options);

    echo "<script>console.log('Conexión a la base de datos exitosa');</script>"; // Mensaje de éxito en la consola del navegador
} catch (\PDOException $e) {
    // Manejar errores de conexión
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>
