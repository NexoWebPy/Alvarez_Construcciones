<?php
require_once 'seguridad.php';
ac_iniciar_sesion();

if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['rol'] ?? null, ['admin', 'cliente'], true)) {
    http_response_code(404);
    exit;
}

$archivo_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$archivo_id || $archivo_id < 1) {
    http_response_code(404);
    exit;
}

require_once 'conexion.php';

if ($_SESSION['rol'] === 'admin') {
    $stmt = $conexion->prepare("SELECT ruta_archivo FROM archivos WHERE id = ?");
    $stmt->bind_param("i", $archivo_id);
} else {
    $stmt = $conexion->prepare(
        "SELECT a.ruta_archivo FROM archivos a
         JOIN proyectos p ON p.id = a.proyecto_id
         WHERE a.id = ? AND p.cliente_id = ?"
    );
    $stmt->bind_param("ii", $archivo_id, $_SESSION['usuario_id']);
}

$stmt->execute();
$archivo = $stmt->get_result()->fetch_assoc();

if (!$archivo) {
    http_response_code(404);
    exit;
}

$directorio_uploads = realpath(__DIR__ . DIRECTORY_SEPARATOR . 'uploads');
$ruta_archivo = realpath(__DIR__ . DIRECTORY_SEPARATOR . $archivo['ruta_archivo']);

if (
    !$directorio_uploads
    || !$ruta_archivo
    || strpos($ruta_archivo, $directorio_uploads . DIRECTORY_SEPARATOR) !== 0
    || !is_file($ruta_archivo)
) {
    http_response_code(404);
    exit;
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($ruta_archivo);
$tipos_permitidos = [
    'image/jpeg' => ['jpg', 'inline'],
    'image/png' => ['png', 'inline'],
    'image/webp' => ['webp', 'inline'],
    'application/pdf' => ['pdf', 'attachment']
];

if (!isset($tipos_permitidos[$mime])) {
    http_response_code(404);
    exit;
}

[$extension, $disposicion] = $tipos_permitidos[$mime];
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, no-store');
header('Content-Type: ' . $mime);
header('Content-Disposition: ' . $disposicion . '; filename="actualizacion.' . $extension . '"');
header('Content-Length: ' . filesize($ruta_archivo));
readfile($ruta_archivo);