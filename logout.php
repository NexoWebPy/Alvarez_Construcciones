<?php
require_once 'seguridad.php';
ac_iniciar_sesion();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !ac_validar_csrf($_POST['csrf_token'] ?? null)) {
    http_response_code(403);
    exit('Solicitud no válida.');
}

ac_destruir_sesion();

// Redirigir al inicio de sesión
header("Location: login.php");
exit();
?>