<?php
require_once 'seguridad.php';
ac_iniciar_sesion();
include 'conexion.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!ac_validar_csrf($_POST['csrf_token'] ?? null)) {
        $error = "La sesión expiró. Actualizá la página e intentá de nuevo.";
    } elseif ($email !== '' && $password !== '' && strlen($email) <= 254 && strlen($password) <= 4096) {
        $stmt = $conexion->prepare("SELECT id, nombre, password, rol FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $usuario = $resultado->fetch_assoc();

        if ($usuario && password_verify($password, $usuario['password'])) {
            if (!in_array($usuario['rol'], ['admin', 'cliente'], true)) {
                $error = "Correo o contraseña incorrectos.";
            } else {
                session_regenerate_id(true);
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['nombre'] = $usuario['nombre'];
            $_SESSION['rol'] = $usuario['rol'];

            if ($usuario['rol'] === 'admin') {
                header("Location: admin.php");
            } else {
                header("Location: cliente.php");
            }
            exit();
            }
        } else {
            $error = "Correo o contraseña incorrectos.";
        }
    } else {
        $error = "Correo o contraseña incorrectos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <title>AC Alvarez - Construcción Premium</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        body, html {
            height: 100%;
            color: #fff;
            overflow-x: hidden;
        }

        /* Hero Container con Imagen de Fondo */
        .hero {
            position: relative;
            min-height: 100vh;
            background: linear-gradient(rgba(0, 0, 0, 0.55), rgba(0, 0, 0, 0.75)), url('foto16.jpg') no-repeat center center/cover;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        /* Navbar Superior */
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 50px;
            background: rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(5px);
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: #fff;
        }

        .logo-img {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            object-fit: cover;
            border: 1px solid rgba(194, 149, 71, 0.5);
        }

        .brand-name {
            font-weight: 800;
            letter-spacing: 1.5px;
            font-size: 1.1rem;
            transition: color 0.3s;
        }

        .logo-container:hover .brand-name {
            color: #c29547;
        }

        nav a {
            color: #d1d5db;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            padding: 8px 18px;
            border: 1px solid rgba(194, 149, 71, 0.4);
            border-radius: 20px;
            background: rgba(194, 149, 71, 0.1);
            transition: all 0.3s ease;
        }

        nav a:hover {
            color: #000;
            background: #c29547;
            box-shadow: 0 0 15px rgba(194, 149, 71, 0.4);
        }

        /* Contenido Principal en 2 Columnas */
        .hero-body {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 40px 80px;
            gap: 50px;
            flex-grow: 1;
        }

        /* Columna Izquierda: Mensaje Promocional */
        .hero-text {
            max-width: 580px;
        }

        .badge {
            display: inline-block;
            padding: 6px 16px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            font-size: 0.75rem;
            letter-spacing: 2px;
            margin-bottom: 20px;
            color: #e5e7eb;
            background: rgba(255, 255, 255, 0.05);
        }

        .subtitle-sm {
            color: #d4a359;
            letter-spacing: 3px;
            font-size: 0.8rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        h1 {
            font-size: 3.8rem;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 20px;
            color: #f9fafb;
        }

        .description {
            color: #9ca3af;
            font-size: 1rem;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        /* Columna Derecha: Tarjeta Login */
        .login-card {
            background: rgba(18, 18, 18, 0.75);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 16px;
            padding: 35px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6);
        }

        .login-card h3 {
            font-size: 1.4rem;
            margin-bottom: 8px;
            color: #fff;
            font-weight: 700;
        }

        .login-card p {
            color: #9ca3af;
            font-size: 0.85rem;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            font-size: 0.8rem;
            color: #d1d5db;
            margin-bottom: 6px;
            font-weight: 600;
        }

        .form-group input {
            width: 100%;
            padding: 12px 14px;
            background: rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 8px;
            color: #fff;
            outline: none;
            font-size: 0.9rem;
            transition: border-color 0.3s;
        }

        .form-group input:focus {
            border-color: #c29547;
        }

        .btn-submit {
            width: 100%;
            padding: 12px;
            background: #c29547;
            border: none;
            border-radius: 8px;
            color: #000;
            font-weight: 800;
            font-size: 0.95rem;
            cursor: pointer;
            transition: background 0.3s;
            margin-top: 10px;
        }

        .btn-submit:hover {
            background: #d4a359;
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid #ef4444;
            color: #fca5a5;
            padding: 10px;
            border-radius: 8px;
            font-size: 0.85rem;
            margin-bottom: 15px;
            text-align: center;
        }

        /* Responsivo para celulares */
        @media (max-width: 992px) {
            .hero-body {
                flex-direction: column;
                padding: 30px 20px;
                text-align: center;
            }

            .hero-text h1 {
                font-size: 2.5rem;
            }

            header {
                padding: 15px 20px;
            }
        }

        @media (max-width: 600px) {
            .hero {
                justify-content: flex-start;
                min-height: 100svh;
            }

            .hero-body {
                flex-direction: column-reverse;
                flex-grow: 0;
                gap: 26px;
                padding: 20px 16px 36px;
            }

            .login-card {
                max-width: 420px;
                padding: 26px 22px;
            }

            .hero-text h1 {
                font-size: 2.2rem;
            }
        }
    </style>
    <link rel="stylesheet" href="site-redesign.css">
</head>
<body class="login-page">

    <div class="hero">
        <!-- Navegación -->
        <header>
            <a href="index.html" class="logo-container">
                <img src="CasaAlpinaLogo.jpeg" alt="Logo AC Alvarez" class="logo-img">
                <span class="brand-name">AC ALVAREZ</span>
            </a>
            <nav>
                <a href="index.html">Sitio principal</a>
            </nav>
        </header>

        <!-- Cuerpo del Hero con el Formulario Integrado -->
        <div class="hero-body">
            <!-- Mensaje de Marca -->
            <div class="hero-text">
                <span class="badge">✦ CONSTRUCCIÓN PREMIUM EN PARAGUAY</span>
                <div class="subtitle-sm">DISEÑO • CONSTRUCCIÓN • EXPERIENCIA</div>
                <h1>Construimos lugares para vivir</h1>
                <p class="description">Diseñamos y construimos Casas Alpinas A-Frame, cabañas, piscinas y proyectos únicos pensados para transformar tus ideas en espacios extraordinarios.</p>
                <span class="badge">ESTÁS A UN PASO DE VER TU AVANCE DE OBRA</span>
            </div>

            <!-- Formulario de Acceso al Portal -->
            <div class="login-card">
                <h3>Acceso a Clientes</h3>
                <p>Ingresa para consultar el avance de tu obra y facturas.</p>

                <?php if($error): ?>
                    <div class="alert-error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form action="login.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(ac_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                    <div class="form-group">
                        <label for="email">Correo Electrónico</label>
                        <input type="email" id="email" name="email" autocomplete="email" required placeholder="tu@correo.com">
                    </div>

                    <div class="form-group">
                        <label for="password">Contraseña</label>
                        <input type="password" id="password" name="password" autocomplete="current-password" required placeholder="••••••••">
                    </div>

                    <button type="submit" class="btn-submit">Ingresar al Portal</button>
                </form>
            </div>
        </div>
    </div>

</body>
</html>
