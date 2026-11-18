<?php
// Deshabilitar la caché del navegador para evitar volver mediante el botón "Atrás"
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
require_once 'seguridad.php';
ac_iniciar_sesion();
include 'conexion.php';

// Verificar autenticación del cliente
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'cliente') {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$nombre_usuario = $_SESSION['nombre'] ?? 'Cliente';

$proyectos_stmt = $conexion->prepare("SELECT nombre_proyecto, monto_gastado, porcentaje_avance FROM proyectos WHERE cliente_id = ? ORDER BY id DESC");
$proyectos_stmt->bind_param("i", $usuario_id);
$proyectos_stmt->execute();
$proyectos_usuario = $proyectos_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Consultar archivos del cliente en la BD
$query = "SELECT a.id, a.tipo, a.descripcion, a.fecha_subida, p.nombre_proyecto 
          FROM archivos a 
          JOIN proyectos p ON a.proyecto_id = p.id 
          WHERE p.cliente_id = ? 
          ORDER BY a.fecha_subida DESC";

$stmt = $conexion->prepare($query);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$archivos = $stmt->get_result();

$actualizaciones = $archivos->num_rows;

/* ===== MENSAJES DEL CLIENTE ===== */
$mensaje_envio = '';
$tipo_mensaje_envio = 'exito';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion_mensaje']) && $_POST['accion_mensaje'] === 'enviar_mensaje') {
    if (!ac_validar_csrf($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        $mensaje_envio = 'La sesión expiró. Actualizá la página e intentá de nuevo.';
        $tipo_mensaje_envio = 'error';
    } else {
        $asunto = trim((string)($_POST['asunto'] ?? ''));
        $cuerpo = trim((string)($_POST['cuerpo'] ?? ''));

        if ($asunto === '' || mb_strlen($asunto, 'UTF-8') > 150) {
            $mensaje_envio = 'Ingresá un asunto válido (máximo 150 caracteres).';
            $tipo_mensaje_envio = 'error';
        } elseif ($cuerpo === '' || mb_strlen($cuerpo, 'UTF-8') > 4000) {
            $mensaje_envio = 'Escribí un mensaje válido (máximo 4.000 caracteres).';
            $tipo_mensaje_envio = 'error';
        } else {
            $stmt = $conexion->prepare(
                'INSERT INTO mensajes (remitente_id, destinatario_id, asunto, mensaje) VALUES (?, 1, ?, ?)'
            );
            $stmt->bind_param('iss', $usuario_id, $asunto, $cuerpo);
            if ($stmt->execute()) {
                $mensaje_envio = 'Mensaje enviado. Esperá la respuesta de tu administrador.';
                $tipo_mensaje_envio = 'exito';
            } else {
                error_log('Error al enviar mensaje: ' . $stmt->error);
                $mensaje_envio = 'No se pudo enviar el mensaje. Intentá de nuevo.';
                $tipo_mensaje_envio = 'error';
            }
            $stmt->close();
        }
    }
}

/* Consultar mensajes del cliente con respuestas */
$query_mensajes = "
    SELECT m.id, m.asunto, m.mensaje, m.respuesta, m.leido, m.respondido_en,
           u_res.nombre AS respondido_por_nombre, m.creado_en
    FROM mensajes m
    LEFT JOIN usuarios u_res ON u_res.id = m.respondido_por
    WHERE m.remitente_id = ?
    ORDER BY m.creado_en DESC
";
$stmt_mensajes = $conexion->prepare($query_mensajes);
$stmt_mensajes->bind_param('i', $usuario_id);
$stmt_mensajes->execute();
$mensajes_cliente = $stmt_mensajes->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_mensajes->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <title>AC Alvarez - Portal Privado de Clientes</title>
    <style>
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
    font-family: 'Manrope', 'Avenir Next', sans-serif;
}

html {
    height: 100%;
}

        body {
            /* Fondo con imagen y overlay oscuro animado */
            background: linear-gradient(135deg, rgba(15, 17, 21, 0.88), rgba(10, 10, 12, 0.95)), 
                        url('fondo1.jpeg') no-repeat center center fixed;
            background-size: cover;
            color: #e5e7eb;
            min-height: 100dvh;
            min-height: -webkit-fill-available;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        /* Header Translucido */
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 18px 50px;
            background: rgba(18, 18, 18, 0.6);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo-img {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: #fff;
            box-shadow: 0 0 12px rgba(194, 149, 71, 0.4);
        }

        .brand-name {
            font-weight: 800;
            letter-spacing: 1.5px;
            font-size: 1.1rem;
            color: #fff;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-name {
            font-size: 0.9rem;
            color: #d1d5db;
        }

        .btn-logout {
            color: #ef4444;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 700;
            padding: 8px 18px;
            border: 1px solid rgba(239, 68, 68, 0.4);
            border-radius: 20px;
            background: rgba(239, 68, 68, 0.12);
            transition: all 0.3s ease;
            cursor: pointer;
            font-family: inherit;
        }

        .btn-logout:hover {
            background: rgba(239, 68, 68, 0.3);
            box-shadow: 0 0 15px rgba(239, 68, 68, 0.3);
        }

        /* Contenedor Principal */
        .container {
            width: 100%;
            max-width: 1100px;
            margin: 24px auto;
            padding: 0 16px;
        }

        /* Banner de Bienvenida */
        .welcome-card {
            background: rgba(25, 28, 36, 0.65);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4);
        }

        .welcome-card h1 {
            font-size: 2rem;
            font-weight: 800;
            color: #fff;
            margin-bottom: 8px;
        }

        .welcome-card p {
            color: #9ca3af;
            font-size: 0.95rem;
        }

        .badge-project {
            display: inline-block;
            margin-top: 15px;
            background: rgba(194, 149, 71, 0.2);
            color: #d4a359;
            border: 1px solid #c29547;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 1px;
        }

        /* Barra de Progreso Dinámica */
        .progress-card {
            background: rgba(25, 28, 36, 0.65);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 35px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4);
        }

        .progress-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-weight: 700;
            font-size: 1rem;
        }

        .progress-bg {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            height: 14px;
            overflow: hidden;
            width: 100%;
        }

        .progress-fill {
            background: linear-gradient(90deg, #c29547, #f39c12);
            height: 100%;
            border-radius: 12px;
            box-shadow: 0 0 12px rgba(243, 156, 18, 0.6);
        }

        /* Galeria / Lista de Archivos */
        .section-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .grid-archivos {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
        }

        /* Tarjeta de Archivo interactiva */
        .archivo-card {
            background: rgba(25, 28, 36, 0.7);
            backdrop-filter: blur(14px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            display: flex;
            flex-direction: column;
        }

        .archivo-card:hover {
        }

        .archivo-preview {
            width: 100%;
            height: 180px;
            object-fit: cover;
            background: #121212;
        }

        .archivo-doc-icon {
            width: 100%;
            height: 180px;
            background: rgba(18, 18, 18, 0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: #c29547;
        }

        .archivo-info {
            padding: 20px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
            justify-content: space-between;
        }

        .archivo-desc {
            font-size: 0.9rem;
            color: #e5e7eb;
            margin-bottom: 12px;
            line-height: 1.4;
        }

        .archivo-fecha {
            font-size: 0.75rem;
            color: #9ca3af;
            margin-bottom: 15px;
        }

        .btn-ver {
            display: block;
            text-align: center;
            background: #c29547;
            color: #000;
            padding: 10px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.85rem;
            transition: background 0.3s;
        }

        @media (max-width: 768px) {
            header { padding: 15px 20px; }
            .grid-archivos { grid-template-columns: 1fr; }
        }

        /* ===== MENSAJES CLIENTE ===== */
        .msg-alert {
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 0.88rem;
            font-weight: 700;
            margin-bottom: 24px;
            border: 1px solid transparent;
        }
        .msg-alert.msg-exito {
            background: rgba(34, 197, 94, 0.10);
            border-color: rgba(34, 197, 94, 0.35);
            color: #86efac;
        }
        .msg-alert.msg-error {
            background: rgba(239, 68, 68, 0.10);
            border-color: rgba(239, 68, 68, 0.35);
            color: #fca5a5;
        }

        .msg-section {
            margin-top: 40px;
        }

        .msg-header {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 24px;
        }

        .msg-header .section-title {
            margin: 0;
            font-size: 1.5rem;
        }

        .msg-subtitle {
            color: #9ca3af;
            font-size: 0.9rem;
            margin: 0;
        }

        .msg-compose {
            background: rgba(25, 28, 36, 0.65);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 20px;
            padding: 26px;
            margin-bottom: 30px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4);
        }

        .msg-compose h3 {
            margin: 0 0 18px 0;
            color: #fff;
            font-size: 1.15rem;
            font-weight: 800;
        }

        .msg-form {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .msg-field {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .msg-field label {
            font-size: 0.82rem;
            font-weight: 700;
            color: #d1d5db;
        }

        .msg-field input,
        .msg-field textarea {
            min-height: 46px;
            padding: 12px 14px;
            border: 1px solid rgba(255, 255, 255, 0.13);
            border-radius: 11px;
            background: rgba(10, 12, 16, 0.75);
            color: #f9fafb;
            font-family: inherit;
            font-size: 0.9rem;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .msg-field textarea {
            min-height: 120px;
            resize: vertical;
        }

        .msg-field input:focus,
        .msg-field textarea:focus {
            outline: none;
            border-color: #c29547;
            box-shadow: 0 0 0 3px rgba(194, 149, 71, 0.12);
        }

        .msg-btn-send {
            min-height: 48px;
            padding: 13px 20px;
            border: none;
            border-radius: 11px;
            background: linear-gradient(110deg, #b78334, #edc476);
            color: #17130c;
            font-weight: 900;
            font-size: 0.91rem;
            cursor: pointer;
            box-shadow: 0 7px 20px rgba(194, 149, 71, 0.15);
            transition: transform 0.2s ease, filter 0.2s ease;
        }

        .msg-btn-send:hover {
            transform: translateY(-2px);
            filter: brightness(1.06);
        }

        .msg-list-wrap {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .msg-list-title {
            margin: 0 0 12px 0;
            color: #fff;
            font-size: 1.15rem;
            font-weight: 800;
        }

        .msg-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .msg-item {
            background: rgba(25, 28, 36, 0.65);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        }

        .msg-item-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding: 16px 18px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        }

        .msg-item-subject {
            font-weight: 800;
            color: #fff;
            font-size: 0.98rem;
        }

        .msg-item-date {
            font-size: 0.75rem;
            color: #9ca3af;
            white-space: nowrap;
        }

        .msg-item-status {
            font-size: 0.7rem;
            font-weight: 800;
            padding: 2px 8px;
            border-radius: 999px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }

        .msg-item-status.msg-status-unread {
            background: rgba(194, 149, 71, 0.15);
            color: #d4a359;
            border: 1px solid rgba(194, 149, 71, 0.3);
        }

        .msg-item-status.msg-status-read {
            background: rgba(34, 197, 94, 0.15);
            color: #86efac;
            border: 1px solid rgba(34, 197, 94, 0.3);
        }

        .msg-item-body {
            padding: 16px 18px;
            color: #e5e7eb;
            font-size: 0.9rem;
            line-height: 1.6;
        }

        .msg-item-body p {
            margin: 0;
        }

        .msg-reply {
            padding: 14px 18px 18px 18px;
            border-top: 1px solid rgba(255, 255, 255, 0.06);
        }

        .msg-reply-pending {
            padding: 14px 18px 18px 18px;
            border-top: 1px solid rgba(255, 255, 255, 0.06);
            color: #9ca3af;
            font-size: 0.85rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .msg-reply-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            margin-bottom: 8px;
        }

        .msg-reply-label {
            font-size: 0.78rem;
            font-weight: 800;
            color: #c29547;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .msg-reply-date,
        .msg-reply-by {
            font-size: 0.72rem;
            color: #9ca3af;
        }

        .msg-reply-body {
            color: #e5e7eb;
            font-size: 0.9rem;
            line-height: 1.6;
            margin: 0;
        }

        .msg-empty {
            text-align: center;
            padding: 32px 20px;
            color: #9ca3af;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .msg-compose { padding: 20px; }
            .msg-item-header { flex-direction: column; align-items: flex-start; gap: 8px; }
            .msg-item-date { white-space: normal; }
            .msg-reply-header { flex-direction: column; align-items: flex-start; gap: 4px; }
        }
    </style>
    <link rel="stylesheet" href="portal.css">
    <link rel="stylesheet" href="site-redesign.css">
</head>
<body class="client-page">

    <!-- Header / Navbar -->
    <header>
        <div class="logo-container">
            <img src="CasaAlpinaLogo.jpeg" alt="AC Alvarez Construcciones" class="logo-img">
            <span class="brand-name">AC ALVAREZ</span>
        </div>
        <div class="user-menu">
            <span class="user-name">Hola, <strong><?= htmlspecialchars($nombre_usuario) ?></strong></span>
            <form action="logout.php" method="post">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(ac_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                <button type="submit" class="btn-logout">Cerrar Sesión</button>
            </form>
        </div>
    </header>

    <!-- Contenido Principal -->
    <main class="container portal-container">
        <!-- Saludo e Información del Proyecto -->
        <div class="welcome-card">
            <div class="welcome-copy">
                <h1>¡Bienvenido, <?= htmlspecialchars($nombre_usuario, ENT_QUOTES, 'UTF-8') ?>!</h1>
                <p>Este es el avance de tu obra. Consultá el porcentaje completado y el monto gastado hasta hoy.</p>
            </div>
            <span class="badge-project">AVANCE DE TU OBRA</span>
        </div>

        <!-- Mensajes con el administrador -->
        <?php if ($mensaje_envio !== ''): ?>
            <div class="msg-alert msg-<?= htmlspecialchars($tipo_mensaje_envio, ENT_QUOTES, 'UTF-8') ?>" role="status">
                <?= htmlspecialchars($mensaje_envio, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <section class="msg-section">
            <div class="msg-header">
                <h2 class="section-title">Mensajes con el administrador</h2>
                <p class="msg-subtitle">Enviá consultas sobre tu obra y recibí respuestas del equipo de AC Alvarez.</p>
            </div>

            <div class="msg-compose">
                <h3>Nuevo mensaje</h3>
                <form action="cliente.php" method="POST" class="msg-form">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(ac_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="accion_mensaje" value="enviar_mensaje">

                    <div class="msg-field">
                        <label for="msg-asunto">Asunto</label>
                        <input type="text" id="msg-asunto" name="asunto" maxlength="150" placeholder="Ej.: Consulta sobre avance de obra" required>
                    </div>

                    <div class="msg-field">
                        <label for="msg-cuerpo">Mensaje</label>
                        <textarea id="msg-cuerpo" name="cuerpo" rows="5" placeholder="Escribí tu consulta o pregunta..." required></textarea>
                    </div>

                    <button type="submit" class="msg-btn-send">Enviar mensaje</button>
                </form>
            </div>

            <div class="msg-list-wrap">
                <h3 class="msg-list-title">Historial de mensajes</h3>
                <?php if ($mensajes_cliente): ?>
                    <div class="msg-list">
                        <?php foreach ($mensajes_cliente as $msg): ?>
                            <article class="msg-item <?= !$msg['leido'] ? 'msg-unread' : '' ?>">
                                <div class="msg-item-header">
                                    <span class="msg-item-subject"><?= htmlspecialchars($msg['asunto'], ENT_QUOTES, 'UTF-8') ?></span>
                                    <span class="msg-item-date"><?= htmlspecialchars(date('d/m/Y H:i', strtotime($msg['creado_en'])), ENT_QUOTES, 'UTF-8') ?></span>
                                    <span class="msg-item-status <?= !$msg['leido'] ? 'msg-status-unread' : 'msg-status-read' ?>">
                                        <?= !$msg['leido'] ? 'Sin leer' : 'Leído' ?>
                                    </span>
                                </div>
                                <div class="msg-item-body">
                                    <p><?= nl2br(htmlspecialchars($msg['mensaje'], ENT_QUOTES, 'UTF-8')) ?></p>
                                </div>
                                <?php if ($msg['respuesta'] !== null && $msg['respuesta'] !== ''): ?>
                                    <div class="msg-reply">
                                        <div class="msg-reply-header">
                                            <span class="msg-reply-label">Respuesta del administrador</span>
                                            <?php if ($msg['respondido_en']): ?>
                                                <span class="msg-reply-date"><?= htmlspecialchars(date('d/m/Y H:i', strtotime($msg['respondido_en'])), ENT_QUOTES, 'UTF-8') ?></span>
                                            <?php endif; ?>
                                            <?php if ($msg['respondido_por_nombre']): ?>
                                                <span class="msg-reply-by">por <?= htmlspecialchars($msg['respondido_por_nombre'], ENT_QUOTES, 'UTF-8') ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <p class="msg-reply-body"><?= nl2br(htmlspecialchars($msg['respuesta'], ENT_QUOTES, 'UTF-8')) ?></p>
                                    </div>
                                <?php else: ?>
                                    <div class="msg-reply msg-reply-pending">
                                        <span class="msg-reply-label">Estado:</span>
                                        <span class="msg-reply-status">Pendiente de respuesta</span>
                                    </div>
                                <?php endif; ?>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="msg-empty">
                        <p>Aún no has enviado mensajes al administrador.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section class="project-progress-list" aria-labelledby="project-progress-title">
            <h2 class="section-title" id="project-progress-title">Avance de tu obra</h2>
            <?php if ($proyectos_usuario): ?>
                <?php foreach ($proyectos_usuario as $proyecto): ?>
                    <?php
                    $porcentaje_avance = $proyecto['porcentaje_avance'] !== null
                        ? max(0, min(100, (int) $proyecto['porcentaje_avance']))
                        : null;
                    ?>
                    <article class="progress-card project-progress-card">
                        <div class="project-progress-header">
                            <h3><?= htmlspecialchars($proyecto['nombre_proyecto'], ENT_QUOTES, 'UTF-8') ?></h3>
                            <strong><?= $porcentaje_avance !== null ? $porcentaje_avance . '%' : 'Sin actualizar' ?></strong>
                        </div>
                        <div class="project-metrics">
                            <div class="project-metric">
                                <span>Gastado hasta hoy</span>
                                <strong><?= $proyecto['monto_gastado'] !== null ? 'Gs. ' . number_format((float) $proyecto['monto_gastado'], 0, ',', '.') : 'Sin actualizar' ?></strong>
                            </div>
                            <div class="project-metric">
                                <span>Avance de obra</span>
                                <strong><?= $porcentaje_avance !== null ? $porcentaje_avance . '% completado' : 'Sin actualizar' ?></strong>
                            </div>
                        </div>
                        <div class="progress-bg" role="progressbar" aria-label="Avance de <?= htmlspecialchars($proyecto['nombre_proyecto'], ENT_QUOTES, 'UTF-8') ?>" aria-valuemin="0" aria-valuemax="100"<?= $porcentaje_avance !== null ? ' aria-valuenow="' . $porcentaje_avance . '"' : '' ?>>
                            <div class="progress-fill" style="width: <?= $porcentaje_avance ?? 0 ?>%"></div>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="progress-card project-progress-card">
                    <p>Todavía no hay una obra asignada a tu cuenta.</p>
                </div>
            <?php endif; ?>
        </section>

        <!-- Galería de Avances y Documentos -->
        <div class="section-heading">
            <h2 class="section-title">Avances recientes y documentos</h2>
            <span class="section-count">
                <?= (int) $actualizaciones ?> <?= $actualizaciones === 1 ? 'archivo publicado' : 'archivos publicados' ?>
            </span>
        </div>

        <div class="grid-archivos">
            <?php if($archivos && $archivos->num_rows > 0): ?>
                <?php while($row = $archivos->fetch_assoc()): ?>
                    <div class="archivo-card">
                        <?php if($row['tipo'] === 'foto_avance'): ?>
                            <img src="archivo.php?id=<?= (int) $row['id'] ?>" class="archivo-preview" alt="Avance de Obra">
                        <?php else: ?>
                            <div class="archivo-doc-icon" aria-hidden="true">DOCUMENTO</div>
                        <?php endif; ?>

                        <div class="archivo-info">
                            <div>
                                <h3 class="archivo-proyecto"><?= htmlspecialchars($row['nombre_proyecto'], ENT_QUOTES, 'UTF-8') ?></h3>
                                <p class="archivo-desc"><?= htmlspecialchars($row['descripcion'], ENT_QUOTES, 'UTF-8') ?></p>
                                <span class="archivo-fecha">Publicado: <?= htmlspecialchars($row['fecha_subida'], ENT_QUOTES, 'UTF-8') ?></span>
                            </div>
                            <a href="archivo.php?id=<?= (int) $row['id'] ?>" target="_blank" rel="noopener noreferrer" class="btn-ver">Ver / Descargar Archivo</a>
                        </div>
                    </div>
                <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-state">
                <p>Aún no se han cargado fotos o comprobantes de tu proyecto.</p>
            </div>
        <?php endif; ?>
        </div>

        <!-- Galería de Avances y Documentos -->
        <div class="section-heading">
            <h2 class="section-title">Avances recientes y documentos</h2>
    <script>
        // Si la página se recupera de la caché de navegación del navegador (botón Atrás)
        window.addEventListener('pageshow', function (event) {
            if (event.persisted || (performance && performance.navigation.type === 2)) {
                window.location.reload();
            }
        });

        const logoutToken = <?= json_encode(ac_csrf_token()) ?>;
        let logoutRequested = false;

        document.querySelector('form[action="logout.php"]')?.addEventListener('submit', function () {
            logoutRequested = true;
        });

        document.querySelector('form input[name="accion_mensaje"][value="enviar_mensaje"]')?.closest('form')?.addEventListener('submit', function () {
            logoutRequested = true;
        });

        window.addEventListener('beforeunload', function () {
            if (logoutRequested) return;

            navigator.sendBeacon(
                'logout_silent.php',
                new URLSearchParams({ csrf_token: logoutToken })
            );
        });
    </script>
</body>
</html>
