<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

include 'conexion.php';

// Obtener todos los usuarios de la base de datos
$resultado = $conexion->query("SELECT id, password FROM usuarios");

$actualizados = 0;

while ($usuario = $resultado->fetch_assoc()) {
    $id = $usuario['id'];
    $pass_actual = $usuario['password'];

    // Verificar si la contraseña no está encriptada aún
    $informacion_hash = password_get_info($pass_actual);
    if ($informacion_hash['algo'] === null) {
        $pass_hash = password_hash($pass_actual, PASSWORD_DEFAULT);

        $stmt = $conexion->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $pass_hash, $id);
        $stmt->execute();
        $actualizados++;
    }
}

echo "<div style='font-family: sans-serif; background: #121212; color: #fff; padding: 20px; text-align: center;'>";
echo "<h3 style='color: #28a745;'>¡Proceso completado!</h3>";
echo "<p>Se encriptaron las contraseñas de $actualizados usuarios de forma segura.</p>";
echo "<a href='login.php' style='color: #007bff; text-decoration: none;'>Volver al Login</a>";
echo "</div>";
?>