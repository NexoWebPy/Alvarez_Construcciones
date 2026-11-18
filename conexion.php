<?php
$produccion = getenv('APP_ENV') === 'production';
$host = getenv('DB_HOST') ?: ($produccion ? '' : 'localhost');
$user = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASSWORD');
$database = getenv('DB_NAME') ?: 'constructora_db';

if ($password === false) {
    $password = '';
}

if ($produccion && (!$host || !$user || $user === 'root' || $password === '')) {
    error_log('La configuración de base de datos de producción está incompleta o usa una cuenta no permitida.');
    http_response_code(500);
    die("El servicio no está configurado correctamente.");
}

$conexion = new mysqli($host, $user, $password, $database);

if ($conexion->connect_error) {
    error_log("Error de conexión a la base de datos: " . $conexion->connect_error);
    http_response_code(500);
    die("No se pudo conectar con el servicio. Intentá nuevamente más tarde.");
}

$conexion->set_charset("utf8mb4");
?>