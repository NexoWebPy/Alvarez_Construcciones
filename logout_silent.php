<?php
require_once 'seguridad.php';
ac_iniciar_sesion();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ac_validar_csrf($_POST['csrf_token'] ?? null)) {
	ac_destruir_sesion();
	http_response_code(204);
	exit;
}

http_response_code(403);
?>