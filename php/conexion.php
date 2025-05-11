<?php
$host = '127.0.0.1';  // o 'localhost'
$db   = 'base11';      // Nombre de la base de datos
$user = 'root';        // Usuario de MySQL (en XAMPP generalmente es 'root')
$pass = '';            // Contraseña de MySQL (en XAMPP generalmente está vacía)
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    // Depuración: Confirmar conexión
    echo "<script>console.log('Conexión a la base de datos exitosa');</script>";
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>
