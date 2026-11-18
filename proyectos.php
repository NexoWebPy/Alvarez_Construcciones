<?php
// Incluimos la conexión a la base de datos de AC Alvarez Construcciones
include("conexion.php");

/**
 * Datos estructurados para los modelos de Cabañas Alpinas A-Frame.
 * Puedes vincular esta estructura dinámicamente con una tabla en MySQL si lo deseas.
 */
$proyectos = [
    [
        "id" => "alpina-alpina-clasica",
        "categoria" => "Residencial",
        "nombre" => "ALPINA LA SOÑADA",
        "subtitulo" => "Diseño emblemático de 2 niveles,con amplio comedor y sala optimizado para confort y distinción",
        "precio" => "Desde Gs. 210.000.000",
        "superficie" => "110 m²",
        "habitaciones" => "2 Habitaciones",
        "banos" => "1 Baño Familiar",
        "descripcion" => "Estructura icónica con techado a dos aguas, distribución optimizada en dos niveles y ampliación lateral que integra áreas sociales y exteriores.",
        "detalles" => [
            "Estructura principal reforzada: Perfiles de acero negro y soporte en mampostería de ladrillo visto con revestimiento interior de madera tratada.",
            "Amplio espacio concepto abierto (Open Plan) para sala de estar, comedor y cocina/quincho",
            "2 habitaciones independientes, 1 baño completo y acceso directo a la terraza exterior.",
            "Cerramiento de piso a techo con vidrio templado para máxima entrada de luz natural.",
            "Balcón frontal en planta alta con barandilla metálica, más una terraza transitable sobre el módulo lateral.",
            "Techo de chapa perforada/ondulada a dos aguas con aislación e iluminación LED empotrada en bastidor de madera."
        ],
        "imagenes" => [
            "foto3.jpeg",
            "foto7.jpeg",
            "foto5.jpeg",
            "foto6.jpeg",
            "foto4.jpeg",
            "foto2.jpeg",
            "foto1.jpeg"
        ]
    ],
    [
        "id" => "alpina-premium-lux",
        "categoria" => "Premium",
        "nombre" => "Paisaje De la Quinta",
        "subtitulo" => "Cabaña de lujo con piscina privada, fachada panorámica y acabados prémium",
        "precio" => "Desde Gs. 280.000.000",
        "superficie" => "95 m²",
        "habitaciones" => "2 Habitaciones",
        "banos" => "1 Baños En-Suite",
        "descripcion" => "Estructura icónica a dos aguas con diseño arquitectónico industrial-rústico, totalmente equipada con amueblamiento, piscina privada, fogonero y amplio patio integrado.",
        "detalles" => [
            "Incluye construcción de piscina exterior terminada con revestimiento",
            "Balcón panorámico en planta alta",
            "Revestimiento exterior en piedra decorativa y ladrillo visto",
            "Sistema de iluminación LED perimetral e interior",
            "Quincho con Fogon especial para compartir momentos al aire libre",
        ],
        "imagenes" => [
            "foto13.jpg",
            "foto11.jpg",
            "foto12.jpg",
            "foto14.jpg",
            "foto10.jpg",
            "foto15.jpg"
        ]
    ],
    [
        "id" => "alpina-compact",
        "categoria" => "Turismo",
        "nombre" => "A-Frame Compact Resort",
        "subtitulo" => "Solución rentable para glamping, alquileres temporales o cabaña de fin de semana",
        "precio" => "Desde Gs. 115.000.000",
        "superficie" => "45 m²",
        "habitaciones" => "1 Habitación en Loft",
        "banos" => "1 Baño Completo",
        "descripcion" => "Diseño compacto ultra-eficiente, ideal para emprendimientos turísticos o casas de descanso en predios reducidos.",
        "detalles" => [
            "Optimizada para rápida ejecución de obra",
            "Gran resistencia a la intemperie y bajo mantenimiento",
            "Área social integrada en planta baja y dormitorio en mezzanine",
            "Instalación eléctrica y sanitaria completa"
        ],
        "imagenes" => [
            "7.jpeg",
            "6.jpeg"
        ]
    ]
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proyectos & Modelos A-Frame | AC Alvarez Construcciones</title>
    <!-- FontAwesome para Iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
     <style>

        /* === ESTILOS GLOBALES - MATCH ESTÉTICA AC ALVAREZ === */

        :root {

            --bg-dark: #111111;

            --card-bg: rgba(34, 34, 34, 0.78);

            --card-border: rgba(184, 138, 74, 0.22);

            --gold-primary: #b88a4a;

            --gold-light: #d5ae70;

            --gold-dark: #8f682f;

            --text-main: #ffffff;

            --text-muted: #b8b8b8;

        }


        * {

            margin: 0;

            padding: 0;

            box-sizing: border-box;

            font-family: 'Segoe UI', system-ui, -apple-system, BlinkMacSystemFont, Roboto, sans-serif;

        }


        body {

            background-color: var(--bg-dark);

            background-image:

                radial-gradient(circle at 10% 20%, rgba(184, 138, 74, 0.08) 0%, transparent 40%),

                radial-gradient(circle at 90% 80%, rgba(184, 138, 74, 0.05) 0%, transparent 40%);

            background-attachment: fixed;

            color: var(--text-main);

            min-height: 100vh;

            padding-bottom: 60px;

        }


        /* === ENCABEZADO HEADER / NAVBAR === */

        header {

            position: sticky;

            top: 0;

            z-index: 1000;

            background: rgba(17, 17, 17, 0.88);

            backdrop-filter: blur(15px);

            -webkit-backdrop-filter: blur(15px);

            border-bottom: 1px solid var(--card-border);

            padding: 15px 5%;

            display: flex;

            justify-content: space-between;

            align-items: center;

        }


        .brand-container {

            display: flex;

            align-items: center;

            gap: 12px;

            text-decoration: none;

        }


        .brand-logo {

            width: 46px;

            height: 46px;

            border-radius: 50%;

            object-fit: cover;

            border: 2px solid var(--gold-primary);

            box-shadow: 0 0 10px rgba(184, 138, 74, 0.3);

        }


        .brand-title {

            font-size: 1.25rem;

            font-weight: 700;

            letter-spacing: 1.5px;

            color: #ffffff;

            text-transform: uppercase;

        }


        .brand-title span {

            color: var(--gold-primary);

            display: block;

            font-size: 0.75rem;

            letter-spacing: 2px;

        }


        .nav-actions {

            display: flex;

            gap: 12px;

        }


        .btn-gold {

            background: linear-gradient(135deg, var(--gold-primary), var(--gold-light));

            color: #000000;

            padding: 9px 20px;

            border-radius: 30px;

            text-decoration: none;

            font-weight: 700;

            font-size: 0.9rem;

            display: inline-flex;

            align-items: center;

            gap: 8px;

            transition: all 0.3s ease;

            box-shadow: 0 4px 15px rgba(184, 138, 74, 0.2);

            border: none;

            cursor: pointer;

        }


        .btn-gold:hover {

            transform: translateY(-2px);

            box-shadow: 0 6px 20px rgba(184, 138, 74, 0.4);

        }


        .btn-outline {

            background: transparent;

            color: var(--text-main);

            border: 1px solid var(--card-border);

            padding: 9px 20px;

            border-radius: 30px;

            text-decoration: none;

            font-weight: 600;

            font-size: 0.9rem;

            display: inline-flex;

            align-items: center;

            gap: 8px;

            transition: all 0.3s ease;

        }


        .btn-outline:hover {

            border-color: var(--gold-primary);

            color: var(--gold-primary);

            background: rgba(184, 138, 74, 0.08);

        }


        /* === SECCIÓN HÉROE / ENCABEZADO DE PÁGINA === */

        .hero-banner {

            max-width: 1200px;

            margin: 40px auto 30px auto;

            padding: 0 20px;

            text-align: center;

        }


        .hero-badge {

            background: rgba(184, 138, 74, 0.15);

            color: var(--gold-primary);

            border: 1px solid var(--gold-primary);

            padding: 6px 18px;

            border-radius: 20px;

            font-size: 0.8rem;

            font-weight: 700;

            letter-spacing: 2px;

            text-transform: uppercase;

            display: inline-block;

            margin-bottom: 15px;

        }


        .hero-banner h1 {

            font-size: 2.5rem;

            font-weight: 800;

            margin-bottom: 12px;

            letter-spacing: 1px;

            background: linear-gradient(180deg, #ffffff 0%, #d0d0d0 100%);

            -webkit-background-clip: text;

            -webkit-text-fill-color: transparent;

        }


        .hero-banner p {

            color: var(--text-muted);

            font-size: 1.1rem;

            max-width: 700px;

            margin: 0 auto;

            line-height: 1.6;

        }


        /* === FILTROS DE CATEGORÍA === */

        .filter-container {

            display: flex;

            justify-content: center;

            gap: 10px;

            margin: 30px 0 40px 0;

            flex-wrap: wrap;

            padding: 0 20px;

        }


        .filter-btn {

            background: rgba(255, 255, 255, 0.03);

            border: 1px solid var(--card-border);

            color: var(--text-muted);

            padding: 8px 20px;

            border-radius: 20px;

            cursor: pointer;

            font-size: 0.9rem;

            font-weight: 600;

            transition: all 0.3s ease;

        }


        .filter-btn.active, .filter-btn:hover {

            background: rgba(184, 138, 74, 0.15);

            border-color: var(--gold-primary);

            color: var(--gold-primary);

        }


        /* === GRILLA DE PROYECTOS / TARJETAS GLASSMORPHISM === */

        .projects-grid {

            max-width: 1200px;

            margin: 0 auto;

            padding: 0 20px;

            display: grid;

            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));

            gap: 30px;

        }


        .project-card {

            background: var(--card-bg);

            border: 1px solid var(--card-border);

            border-radius: 20px;

            overflow: hidden;

            backdrop-filter: blur(12px);

            -webkit-backdrop-filter: blur(12px);

            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);

            display: flex;

            flex-direction: column;

            transition: transform 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease;

        }


        .project-card:hover {

            transform: translateY(-7px);

            border-color: var(--gold-primary);

            box-shadow: 0 15px 35px rgba(184, 138, 74, 0.2);

        }


        /* === CONTENEDOR CARRUSEL EN TARJETA === */

        .carousel-container {

            position: relative;

            width: 100%;

            height: 240px;

            background: #000;

            overflow: hidden;

        }


        .carousel-slide {

            width: 100%;

            height: 100%;

            display: none;

            position: relative;

        }


        .carousel-slide.active {

            display: block;

        }


        .carousel-slide img {

            width: 100%;

            height: 100%;

            object-fit: cover;

            transition: transform 0.5s ease;

        }


        .project-card:hover .carousel-slide img {

            transform: scale(1.04);

        }


        .carousel-btn {

            position: absolute;

            top: 50%;

            transform: translateY(-50%);

            background: rgba(0, 0, 0, 0.6);

            color: #fff;

            border: 1px solid rgba(255, 255, 255, 0.2);

            width: 32px;

            height: 32px;

            border-radius: 50%;

            display: flex;

            align-items: center;

            justify-content: center;

            cursor: pointer;

            z-index: 10;

            transition: all 0.2s ease;

        }


        .carousel-btn:hover {

            background: var(--gold-primary);

            color: #000;

            border-color: var(--gold-primary);

        }


        .carousel-btn.prev { left: 10px; }

        .carousel-btn.next { right: 10px; }


        .carousel-indicators {

            position: absolute;

            bottom: 12px;

            left: 50%;

            transform: translateX(-50%);

            display: flex;

            gap: 6px;

            z-index: 10;

        }


        .dot {

            width: 8px;

            height: 8px;

            border-radius: 50%;

            background: rgba(255, 255, 255, 0.4);

            cursor: pointer;

            transition: all 0.3s ease;

        }


        .dot.active {

            background: var(--gold-primary);

            width: 20px;

            border-radius: 10px;

        }


        .card-category-tag {

            position: absolute;

            top: 12px;

            right: 12px;

            background: rgba(10, 12, 16, 0.8);

            border: 1px solid var(--gold-primary);

            color: var(--gold-primary);

            font-size: 0.75rem;

            font-weight: 700;

            padding: 4px 12px;

            border-radius: 12px;

            backdrop-filter: blur(5px);

            z-index: 10;

        }


        /* === CUERPO DE LA TARJETA === */

        .card-body {

            padding: 22px;

            display: flex;

            flex-direction: column;

            flex-grow: 1;

        }


        .card-title {

            font-size: 1.3rem;

            font-weight: 700;

            color: #ffffff;

            margin-bottom: 6px;

        }


        .card-subtitle {

            font-size: 0.85rem;

            color: var(--text-muted);

            margin-bottom: 16px;

            line-height: 1.4;

        }


        /* Especificaciones Rápidas */

        .specs-grid {

            display: grid;

            grid-template-columns: repeat(3, 1fr);

            gap: 8px;

            background: rgba(0, 0, 0, 0.3);

            border: 1px solid rgba(255, 255, 255, 0.05);

            padding: 10px;

            border-radius: 12px;

            margin-bottom: 18px;

            text-align: center;

        }


        .spec-item {

            display: flex;

            flex-direction: column;

            align-items: center;

            font-size: 0.75rem;

            color: var(--text-muted);

        }


        .spec-item i {

            color: var(--gold-primary);

            font-size: 0.9rem;

            margin-bottom: 4px;

        }


        .spec-item strong {

            color: var(--text-main);

            font-size: 0.82rem;

        }


        .price-tag {

            font-size: 1.15rem;

            font-weight: 800;

            color: var(--gold-primary);

            margin-bottom: 18px;

            display: flex;

            align-items: center;

            gap: 6px;

        }


        .card-footer {

            margin-top: auto;

            display: flex;

            gap: 10px;

        }


        .card-footer .btn-gold,

        .card-footer .btn-outline {

            width: 100%;

            justify-content: center;

        }


        /* === MODAL DE DETALLES EXTENDIDOS === */

        .modal-overlay {

            position: fixed;

            top: 0;

            left: 0;

            width: 100%;

            height: 100%;

            background: rgba(0, 0, 0, 0.85);

            backdrop-filter: blur(10px);

            z-index: 2000;

            display: none;

            align-items: center;

            justify-content: center;

            padding: 20px;

        }


        .modal-overlay.active {

            display: flex;

        }


        .modal-content {

            background: #171717;

            border: 1px solid var(--gold-primary);

            border-radius: 20px;

            max-width: 800px;

            width: 100%;

            max-height: 90vh;

            overflow-y: auto;

            padding: 30px;

            position: relative;

            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.8);

            color: var(--text-main);

        }


        .modal-close {

            position: absolute;

            top: 20px;

            right: 20px;

            background: rgba(255, 255, 255, 0.1);

            border: none;

            color: #fff;

            width: 36px;

            height: 36px;

            border-radius: 50%;

            cursor: pointer;

            font-size: 1.1rem;

            display: flex;

            align-items: center;

            justify-content: center;

            transition: all 0.2s;

        }


        .modal-close:hover {

            background: var(--gold-primary);

            color: #000;

        }


        .modal-header h2 {

            font-size: 1.8rem;

            color: #ffffff;

            margin-bottom: 6px;

        }


        .modal-header .sub {

            color: var(--gold-primary);

            font-weight: 600;

            margin-bottom: 20px;

        }


        .modal-body p {

            line-height: 1.7;

            color: #cccccc;

            margin-bottom: 20px;

        }


        .modal-details-list {

            list-style: none;

            margin-bottom: 25px;

        }


        .modal-details-list li {

            position: relative;

            padding-left: 26px;

            margin-bottom: 10px;

            color: var(--text-main);

            line-height: 1.5;

        }


        .modal-details-list li::before {

            content: "\f00c";

            font-family: "Font Awesome 6 Free";

            font-weight: 900;

            position: absolute;

            left: 0;

            color: var(--gold-primary);

        }


        /* Ajustes Responsivos */

        @media (max-width: 768px) {

            .projects-grid {

                grid-template-columns: 1fr;

            }


            .hero-banner h1 {

                font-size: 1.8rem;

            }


            header {

                flex-direction: column;

                gap: 15px;

            }

        }
        /* =====================================================
FOOTER AC ALVAREZ
===================================================== */

/* =====================================================
   FOOTER AC ALVAREZ CONSTRUCCIONES
===================================================== */

.footer {
    width: 100%;
    box-sizing: border-box;

    background:
        linear-gradient(
            180deg,
            #111111 0%,
            #080a0d 100%
        );

    border-top:
        1px solid
        rgba(184, 138, 74, 0.22);

    margin-top: 80px;
    margin-bottom: 0 !important;

    padding: 65px 20px 25px;
}

/* CONTENEDOR */

.footer .container {
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0;
    box-sizing: border-box;
}

/* ===============================
GRID
================================ */

.footer-grid {
    display: grid;

    grid-template-columns:
        2fr
        1fr
        1fr
        1fr;

    gap: 50px;

    padding-bottom: 45px;
}

/* ===============================
MARCA
================================ */

.footer-brand {
    max-width: 360px;
}

.footer-logo {
    display: inline-flex;
    margin-bottom: 18px;
}

.footer-logo img {
    width: 70px;
    height: 70px;

    object-fit: cover;

    border-radius: 50%;

    border:
        2px solid
        var(--gold-primary);

    box-shadow:
        0 0 20px
        rgba(184, 138, 74, 0.25);
}

.footer-brand p {
    color: var(--text-muted);

    font-size: 0.9rem;
    line-height: 1.7;

    margin-top: 0;
    margin-bottom: 22px;
}

/* ===============================
REDES SOCIALES
================================ */

.footer-social {
    display: flex;
    gap: 10px;
}

.footer-social a {
    width: 40px;
    height: 40px;

    display: flex;

    align-items: center;
    justify-content: center;

    border-radius: 50%;

    border:
        1px solid
        rgba(184, 138, 74, 0.3);

    background:
        rgba(255, 255, 255, 0.03);

    color: var(--text-muted);

    text-decoration: none;

    transition: all 0.3s ease;
}

.footer-social a:hover {
    color: #000;

    background: var(--gold-primary);

    border-color: var(--gold-primary);

    transform: translateY(-3px);

    box-shadow:
        0 5px 15px
        rgba(184, 138, 74, 0.25);
}

/* ===============================
COLUMNAS
================================ */

.footer-column {
    display: flex;

    flex-direction: column;

    gap: 11px;
}

.footer-column h4 {
    color: #ffffff;

    font-size: 0.95rem;

    margin-top: 0;
    margin-bottom: 8px;

    text-transform: uppercase;

    letter-spacing: 1px;
}

.footer-column a,
.footer-column span {
    color: var(--text-muted);

    text-decoration: none;

    font-size: 0.85rem;

    transition: color 0.25s ease;
}

.footer-column a:hover {
    color: var(--gold-primary);
}

.footer-column i {
    color: var(--gold-primary);

    width: 18px;

    margin-right: 5px;
}

/* ===============================
FOOTER BOTTOM
================================ */

.footer-bottom {
    width: 100%;

    border-top:
        1px solid
        rgba(255, 255, 255, 0.06);

    padding-top: 22px;

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 20px;

    flex-wrap: wrap;

    margin: 0;
}

.footer-bottom p {
    color: #666e78;

    font-size: 0.78rem;

    margin: 0;
}

.footer-legal {
    display: flex;

    align-items: center;

    justify-content: center;

    gap: 18px;

    flex-wrap: wrap;
}

.footer-legal a {
    color: #777f89;

    text-decoration: none;

    font-size: 0.75rem;

    transition: color 0.25s ease;
}

.footer-legal a:hover {
    color: var(--gold-primary);
}

/* ===============================
LOGIN
================================ */

.btn-login {
    display: inline-flex !important;

    align-items: center;
    justify-content: center;

    gap: 7px;

    padding: 7px 13px;

    border:
        1px solid
        rgba(184, 138, 74, 0.4);

    border-radius: 20px;

    color:
        var(--gold-primary) !important;

    background:
        rgba(184, 138, 74, 0.06);

    transition: all 0.3s ease;
}

.btn-login:hover {
    background: var(--gold-primary);

    color: #000 !important;

    border-color: var(--gold-primary);
}

/* =====================================================
RESPONSIVE
===================================================== */

@media (max-width: 850px) {

    .footer-grid {
        grid-template-columns:
            repeat(2, 1fr);

        gap: 40px;
    }
}

@media (max-width: 600px) {

    .footer {
        padding:
            50px 18px 20px;

        margin-top: 60px;
        margin-bottom: 0 !important;
    }

    .footer-grid {
        grid-template-columns: 1fr;

        gap: 35px;
    }

    .footer-brand {
        max-width: 100%;
    }

    .footer-bottom {
        flex-direction: column;

        text-align: center;

        margin-bottom: 0;
    }

    .footer-legal {
        flex-direction: column;

        gap: 12px;
    }
}

/* =====================================================
CORRECCIÓN FINAL — ELIMINAR ESPACIO DESPUÉS DEL FOOTER
===================================================== */

html,
body {
    margin: 0;
    padding: 0;
    min-height: 100%;
}

body {
    background: #111111;
    overflow-x: hidden;
}

.footer:last-child {
    margin-bottom: 0 !important;
}

    .catalog-page {
        background:
            radial-gradient(circle at 15% 10%, rgba(184, 138, 74, 0.10), transparent 30%),
            radial-gradient(circle at 85% 25%, rgba(184, 138, 74, 0.06), transparent 28%),
            linear-gradient(180deg, #080a0c 0%, #0b0e11 45%, #080a0c 100%);
        color: #f2f2f0;
        min-height: 100vh;
    }

    .catalog-page .hero-banner {
        position: relative;
        width: min(100% - 40px, 1240px);
        min-height: 390px;
        margin: 42px auto 0;
        padding: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 30px;
        background:
            linear-gradient(115deg, rgba(8,10,12,0.98) 0%, rgba(8,10,12,0.88) 40%, rgba(8,10,12,0.52) 100%),
            url("5.jpeg") center / cover no-repeat;
        box-shadow: 0 25px 70px rgba(0, 0, 0, 0.45);
    }

    .catalog-page .hero-banner::before {
        content: "";
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at 70% 40%, rgba(184, 138, 74, 0.16), transparent 35%);
    }

    .catalog-page .hero-banner > * {
        position: relative;
        z-index: 1;
    }

    .catalog-page .hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 18px;
        padding: 8px 18px;
        border: 1px solid rgba(184, 138, 74, 0.35);
        border-radius: 999px;
        background: rgba(184, 138, 74, 0.08);
        color: #d8b273;
        font-size: 0.7rem;
        font-weight: 800;
        letter-spacing: 0.2em;
        text-transform: uppercase;
    }

    .catalog-page .hero-banner h1 {
        margin: 0;
        color: #ffffff;
        font-size: clamp(2.4rem, 6vw, 5rem);
        font-weight: 800;
        letter-spacing: -0.055em;
        line-height: 0.98;
        text-wrap: balance;
    }

    .catalog-page .hero-banner p {
        margin: 18px auto 0;
        max-width: 760px;
        color: rgba(242, 242, 240, 0.72);
        font-size: clamp(0.9rem, 2vw, 1.05rem);
        line-height: 1.8;
    }

    .catalog-page .filter-container {
        width: min(100% - 40px, 1240px);
        margin: 38px auto 46px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-wrap: wrap;
        gap: 10px;
    }

    .catalog-page .filter-btn {
        position: relative;
        min-height: 43px;
        padding: 0 18px;
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.025);
        color: #b7b9b8;
        font-size: 0.74rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        cursor: pointer;
        transition: transform 0.35s cubic-bezier(0.22, 1, 0.36, 1), background 0.35s, border-color 0.35s, color 0.35s, box-shadow 0.35s;
    }

    .catalog-page .filter-btn.active,
    .catalog-page .filter-btn:hover {
        transform: translateY(-2px);
        color: #090909;
        border-color: #b88a4a;
        background: linear-gradient(135deg, #d8b273, #b88a4a);
        box-shadow: 0 10px 30px rgba(184, 138, 74, 0.16);
    }

    .catalog-page .projects-grid {
        width: min(100% - 40px, 1240px);
        margin: 0 auto 90px;
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 24px;
    }

    .catalog-page .project-card {
        position: relative;
        display: flex;
        flex-direction: column;
        min-width: 0;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 24px;
        background: linear-gradient(145deg, rgba(255, 255, 255, 0.035), rgba(255, 255, 255, 0.012)), #111519;
        box-shadow: 0 18px 50px rgba(0, 0, 0, 0.28);
        transition: transform 0.35s cubic-bezier(0.22, 1, 0.36, 1), border-color 0.35s, box-shadow 0.35s;
    }

    .catalog-page .project-card::before {
        content: "";
        position: absolute;
        z-index: 5;
        top: 0;
        left: 18%;
        right: 18%;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(216, 178, 115, 0.5), transparent);
        opacity: 0.7;
    }

    .catalog-page .project-card:hover {
        transform: translateY(-8px);
        border-color: rgba(184, 138, 74, 0.35);
        box-shadow: 0 28px 70px rgba(0, 0, 0, 0.45), 0 15px 45px rgba(184, 138, 74, 0.07);
    }

    .catalog-page .carousel-container {
        position: relative;
        height: 290px;
        overflow: hidden;
        background: #060708;
    }

    .catalog-page .carousel-container::after {
        content: "";
        position: absolute;
        inset: 0;
        z-index: 2;
        pointer-events: none;
        background: linear-gradient(to top, rgba(0, 0, 0, 0.55), transparent 35%, rgba(0, 0, 0, 0.08));
    }

    .catalog-page .carousel-slide {
        display: none;
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
    }

    .catalog-page .carousel-slide.active { display: block; }

    .catalog-page .carousel-slide img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.8s cubic-bezier(0.22, 1, 0.36, 1), filter 0.5s ease;
    }

    .catalog-page .project-card:hover .carousel-slide.active img {
        transform: scale(1.045);
        filter: saturate(1.05);
    }

    .catalog-page .carousel-btn {
        position: absolute;
        top: 50%;
        z-index: 10;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        transform: translateY(-50%);
        border: 1px solid rgba(255, 255, 255, 0.15);
        border-radius: 50%;
        background: rgba(5, 7, 8, 0.6);
        color: #fff;
        cursor: pointer;
        backdrop-filter: blur(10px);
        transition: transform 0.35s cubic-bezier(0.22, 1, 0.36, 1), background 0.35s, border-color 0.35s, color 0.35s;
    }

    .catalog-page .carousel-btn.prev { left: 14px; }
    .catalog-page .carousel-btn.next { right: 14px; }

    .catalog-page .carousel-btn:hover {
        transform: translateY(-50%) scale(1.08);
        background: #b88a4a;
        border-color: #b88a4a;
        color: #090909;
    }

    .catalog-page .card-category-tag {
        position: absolute;
        left: 18px;
        top: 18px;
        z-index: 12;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 8px 12px;
        border: 1px solid rgba(216, 178, 115, 0.3);
        border-radius: 999px;
        background: rgba(8, 10, 12, 0.72);
        color: #e5c58e;
        font-size: 0.64rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.1em;
    }

    .catalog-page .card-body {
        display: flex;
        flex-direction: column;
        flex: 1;
        padding: 25px;
    }

    .catalog-page .card-title {
        margin: 0;
        color: #ffffff;
        font-size: 1.35rem;
        font-weight: 800;
        line-height: 1.2;
        letter-spacing: -0.035em;
    }

    .catalog-page .card-subtitle {
        margin: 10px 0 0;
        color: #7e8385;
        font-size: 0.84rem;
        line-height: 1.7;
    }

    .catalog-page .specs-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 9px;
        margin: 22px 0;
        padding: 10px;
        border: 1px solid rgba(255, 255, 255, 0.06);
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.025);
    }

    .catalog-page .spec-item {
        min-width: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 12px;
        border: 1px solid rgba(255, 255, 255, 0.04);
        border-radius: 12px;
        color: #b7b9b8;
        font-size: 0.75rem;
        text-align: center;
    }

    .catalog-page .spec-item i {
        color: #d8b273;
        font-size: 0.9rem;
        margin-bottom: 4px;
    }

    .catalog-page .spec-item strong {
        color: #ffffff;
        font-size: 0.82rem;
    }

    .catalog-page .price-tag {
        margin-top: auto;
        display: flex;
        align-items: center;
        gap: 6px;
        padding-top: 18px;
        border-top: 1px solid rgba(255, 255, 255, 0.07);
        font-size: 1.15rem;
        font-weight: 800;
        color: #d8b273;
    }

    .catalog-page .card-footer {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        margin-top: 20px;
    }

    .catalog-page .btn-gold,
    .catalog-page .btn-outline {
        min-height: 46px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 0 15px;
        border-radius: 11px;
        font-size: 0.7rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        cursor: pointer;
        transition: transform 0.35s cubic-bezier(0.22,1,0.36,1), background 0.35s, border-color 0.35s, color 0.35s, box-shadow 0.35s;
    }

    .catalog-page .btn-gold {
        background: linear-gradient(135deg, #d8b273, #b88a4a);
        color: #090909;
        box-shadow: 0 10px 25px rgba(184, 138, 74, 0.12);
    }

    .catalog-page .btn-gold:hover,
    .catalog-page .btn-outline:hover {
        transform: translateY(-2px);
    }

    .catalog-page .btn-outline {
        border: 1px solid rgba(216, 178, 115, 0.25);
        background: rgba(255, 255, 255, 0.025);
        color: #d8b273;
    }

    .catalog-page .btn-outline:hover {
        border-color: #b88a4a;
        background: rgba(184, 138, 74, 0.08);
        color: #e5c58e;
    }

    .catalog-page .modal-overlay {
        position: fixed;
        inset: 0;
        z-index: 3000;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 25px;
        background: rgba(3, 4, 5, 0.78);
        backdrop-filter: blur(14px);
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .catalog-page .modal-overlay.active {
        display: flex;
        opacity: 1;
    }

    .catalog-page .modal-content {
        position: relative;
        width: min(760px, 100%);
        max-height: min(850px, 90vh);
        overflow-y: auto;
        border: 1px solid rgba(216, 178, 115, 0.2);
        border-radius: 24px;
        background: linear-gradient(145deg, rgba(255,255,255,0.04), rgba(255,255,255,0.01)), #0c0f12;
        box-shadow: 0 40px 100px rgba(0, 0, 0, 0.7);
        transform: translateY(20px) scale(0.98);
        animation: modalIn 0.4s cubic-bezier(0.22, 1, 0.36, 1) forwards;
    }

    @keyframes modalIn {
        to { transform: translateY(0) scale(1); }
    }

    .catalog-page .modal-close {
        position: absolute;
        top: 20px;
        right: 20px;
        width: 38px;
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.04);
        color: #f2f2f0;
        cursor: pointer;
        transition: transform 0.35s cubic-bezier(0.22, 1, 0.36, 1), background 0.35s, color 0.35s, border-color 0.35s;
    }

    .catalog-page .modal-close:hover {
        transform: rotate(90deg);
        background: #b88a4a;
        border-color: #b88a4a;
        color: #090909;
    }

    .catalog-page .modal-header h2 {
        margin: 0 0 6px;
        color: #ffffff;
        font-size: 1.8rem;
        letter-spacing: -0.03em;
    }

    .catalog-page .modal-header .sub {
        margin-bottom: 20px;
        color: #d8b273;
        font-weight: 600;
    }

    .catalog-page .modal-body p {
        margin: 0 0 20px;
        color: #cccccc;
        line-height: 1.7;
    }

    .catalog-page .modal-details-list {
        list-style: none;
        margin: 0 0 25px;
        padding: 0;
    }

    .catalog-page .modal-details-list li {
        position: relative;
        padding: 10px 0 10px 25px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        color: #f2f2f0;
        line-height: 1.5;
    }

    .catalog-page .modal-details-list li::before {
        content: "✓";
        position: absolute;
        left: 0;
        color: #d8b273;
        font-weight: 900;
    }

    @media (max-width: 1050px) {
        .catalog-page .projects-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }

    @media (max-width: 700px) {
        .catalog-page .hero-banner { min-height: 390px; margin-top: 24px; border-radius: 22px; }
        .catalog-page .projects-grid { grid-template-columns: 1fr; gap: 18px; margin-bottom: 60px; }
        .catalog-page .filter-container { justify-content: flex-start; overflow-x: auto; flex-wrap: nowrap; padding-bottom: 5px; }
        .catalog-page .filter-btn { flex: 0 0 auto; min-height: 40px; padding: 0 15px; font-size: 0.65rem; }
        .catalog-page .card-footer { grid-template-columns: 1fr; }
    }

    @media (max-width: 420px) {
        .catalog-page .hero-banner { min-height: 360px; }
        .catalog-page .hero-banner h1 { font-size: 2.15rem; }
        .catalog-page .card-category-tag { left: 12px; top: 12px; padding: 7px 10px; font-size: 0.58rem; }
    }
    </style>
</head>
<body class="catalog-page">

    <!-- ENCABEZADO PRINCIPAL -->
    <header>
        <a href="index.html" class="brand-container">
            <img src="CasaAlpinaLogo.jpeg" alt="AC Alvarez" class="brand-logo" onerror="this.src='https://via.placeholder.com/46x46/1a1a1a/c29547?text=AC'">
            <div class="brand-title">
                AC ALVAREZ
                <span>CONSTRUCCIONES</span>
            </div>
        </a>
        <div class="nav-actions">
            <a href="index.html" class="btn-outline"><i class="fa-solid fa-house"></i> Inicio</a>
            
        </div>
       
    </header>

    <!-- SECCIÓN DE BIENVENIDA -->
    <section class="hero-banner">
        <span class="hero-badge">PROYECTOS UNICOS</span>
        <h1>Cabañas Alpinas en Paraguay</h1>
        <p>Diseño arquitectónico sustentable, óptimo aislamiento térmico y estilo vanguardista para tu terreno o emprendimiento turístico.</p>
    </section>

    <!-- FILTROS DE CATEGORÍA -->
    <div class="filter-container">
        <button class="filter-btn active" onclick="filterCategory('all', this)">Todos los Modelos</button>
        <button class="filter-btn" onclick="filterCategory('Residencial', this)">Residenciales</button>
        <button class="filter-btn" onclick="filterCategory('Premium', this)">Premium / Lujo</button>
        <button class="filter-btn" onclick="filterCategory('Turismo', this)">Ecoturismo / Glamping</button>
    </div>

    <!-- GRILLA DE PROYECTOS -->
    <main class="projects-grid">
        <?php foreach ($proyectos as $p): ?>
            <article class="project-card" data-category="<?php echo $p['categoria']; ?>">
                
                <!-- CARRUSEL DE IMÁGENES -->
                <div class="carousel-container" id="carousel-<?php echo $p['id']; ?>">
                    <span class="card-category-tag"><?php echo $p['categoria']; ?></span>
                    
                    <?php foreach ($p['imagenes'] as $index => $img): ?>
                        <div class="carousel-slide <?php echo ($index === 0) ? 'active' : ''; ?>">
                            <img src="<?php echo $img; ?>" alt="<?php echo $p['nombre']; ?>" onerror="this.src='https://via.placeholder.com/600x400/12161f/c29547?text=AC+Alvarez+Construcciones'">
                        </div>
                    <?php endforeach; ?>

                    <?php if (count($p['imagenes']) > 1): ?>
                        <button class="carousel-btn prev" onclick="moveSlide('<?php echo $p['id']; ?>', -1)"><i class="fa-solid fa-chevron-left"></i></button>
                        <button class="carousel-btn next" onclick="moveSlide('<?php echo $p['id']; ?>', 1)"><i class="fa-solid fa-chevron-right"></i></button>
                        
                        <div class="carousel-indicators">
                            <?php foreach ($p['imagenes'] as $index => $img): ?>
                                <span class="dot <?php echo ($index === 0) ? 'active' : ''; ?>" onclick="setSlide('<?php echo $p['id']; ?>', <?php echo $index; ?>)"></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- DETALLES Y ESPECIFICACIONES -->
                <div class="card-body">
                    <h2 class="card-title"><?php echo $p['nombre']; ?></h2>
                    <p class="card-subtitle"><?php echo $p['subtitulo']; ?></p>

                    <div class="specs-grid">
                        <div class="spec-item">
                            <i class="fa-solid fa-ruler-combined"></i>
                            <span>Superficie</span>
                            <strong><?php echo $p['superficie']; ?></strong>
                        </div>
                        <div class="spec-item">
                            <i class="fa-solid fa-bed"></i>
                            <span>Dormitorios</span>
                            <strong><?php echo $p['habitaciones']; ?></strong>
                        </div>
                        <div class="spec-item">
                            <i class="fa-solid fa-bath"></i>
                            <span>Baños</span>
                            <strong><?php echo $p['banos']; ?></strong>
                        </div>
                    </div>

                    <div class="price-tag">
                        <i class="fa-solid fa-tag"></i> <?php echo $p['precio']; ?>
                    </div>

                    <div class="card-footer">
                        <button class="btn-outline" onclick="openModal('modal-<?php echo $p['id']; ?>')">
                            <i class="fa-solid fa-circle-info"></i> Ver Detalles
                        </button>
                        <a href="https://wa.me/595981000000?text=Hola,%20me%20interesa%20el%20modelo%20<?php echo urlencode($p['nombre']); ?>" target="_blank" class="btn-gold">
                            <i class="fa-brands fa-whatsapp"></i> Cotizar
                        </a>
                    </div>
                </div>
            </article>

            <!-- VENTANA MODAL DE DETALLES -->
            <div class="modal-overlay" id="modal-<?php echo $p['id']; ?>">
                <div class="modal-content">
                    <button class="modal-close" onclick="closeModal('modal-<?php echo $p['id']; ?>')">&times;</button>
                    <div class="modal-header">
                        <h2><?php echo $p['nombre']; ?></h2>
                        <div class="sub"><?php echo $p['precio']; ?> &bull; <?php echo $p['superficie']; ?></div>
                    </div>
                    <div class="modal-body">
                        <p><?php echo $p['descripcion']; ?></p>
                        <h4 style="color: var(--gold-primary); margin-bottom: 12px;">Especificaciones Técnicas & Equipamiento:</h4>
                        <ul class="modal-details-list">
                            <?php foreach ($p['detalles'] as $detalle): ?>
                                <li><?php echo $detalle; ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <div style="text-align: center; margin-top: 25px;">
                            <a href="https://wa.me/595981000000?text=Hola,%20solicito%20mas%20informacion%20sobre%20<?php echo urlencode($p['nombre']); ?>" target="_blank" class="btn-gold" style="padding: 12px 30px; font-size: 1rem;">
                                <i class="fa-brands fa-whatsapp"></i> Solicitar Presupuesto Detallado
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </main>

    <!-- =====================================================
     FOOTER AC ALVAREZ CONSTRUCCIONES
===================================================== -->

<footer class="footer">


<div class="container">

    <div class="footer-grid">

        <!-- ===============================
             MARCA
        ================================ -->

        <div class="footer-brand">

            <a
                href="index.html"
                class="footer-logo"
            >

                <img
                    src="CasaAlpinaLogo.jpeg"
                    alt="AC Alvarez Construcciones"
                >

            </a>

            <p>
                Diseñamos y construimos espacios
                únicos para vivir, descansar y disfrutar.
            </p>

            <div class="footer-social">

                <a
                    href="https://www.instagram.com/alva.rez_construcciones/"
                    target="_blank"
                    rel="noopener noreferrer"
                    aria-label="Instagram"
                >
                    <i class="fab fa-instagram"></i>
                </a>

                <a
                    href="https://www.facebook.com/AlvarezConstruccionesPy"
                    target="_blank"
                    rel="noopener noreferrer"
                    aria-label="Facebook"
                >
                    <i class="fab fa-facebook-f"></i>
                </a>

                <a
                    href="https://www.tiktok.com/@alvarezconstrucciones1"
                    target="_blank"
                    rel="noopener noreferrer"
                    aria-label="TikTok"
                >
                    <i class="fab fa-tiktok"></i>
                </a>

                <a
                    href="https://wa.me/595981704655"
                    target="_blank"
                    rel="noopener noreferrer"
                    aria-label="WhatsApp"
                >
                    <i class="fab fa-whatsapp"></i>
                </a>

            </div>

        </div>


        <!-- ===============================
             NAVEGACIÓN
        ================================ -->

        <div class="footer-column">

            <h4>
                Navegación
            </h4>

            <a href="index.html">
                Inicio
            </a>

            <a href="index.html#nosotros">
                Nosotros
            </a>

            <a href="index.html#servicios">
                Servicios
            </a>

            <a href="proyecto.php">
                Proyectos
            </a>

            <a href="index.html#contacto">
                Contacto
            </a>

        </div>


        <!-- ===============================
             SERVICIOS
        ================================ -->

        <div class="footer-column">

            <h4>
                Servicios
            </h4>

            <a href="index.html#servicios">
                Casas Alpinas
            </a>

            <a href="index.html#servicios">
                Cabañas
            </a>

            <a href="index.html#servicios">
                Piscinas
            </a>

            <a href="index.html#servicios">
                Diseño personalizado
            </a>

            <a href="index.html#servicios">
                Asesoramiento
            </a>

        </div>


        <!-- ===============================
             CONTACTO
        ================================ -->

        <div class="footer-column">

            <h4>
                Contacto
            </h4>

            <a
                href="https://wa.me/595981704655"
                target="_blank"
                rel="noopener noreferrer"
            >

                <i class="fab fa-whatsapp"></i>

                +595 981 704 655

            </a>

            <span>

                <i class="fas fa-map-marker-alt"></i>

                Paraguay

            </span>

        </div>

    </div>


    <!-- ===============================
         FOOTER BOTTOM
    ================================ -->

    <div class="footer-bottom">

        <p>
            ©
            <span id="currentYear"></span>
            AC Alvarez Construcciones.
            Todos los derechos reservados.
        </p>


        <div class="footer-legal">

            <a href="terminos.html">
                Términos y Condiciones
            </a>

            <a href="privacidad.html">
                Política de Privacidad
            </a>

            <a href="cookies.html">
                Política de Cookies
            </a>

            <a
                href="login.php"
                class="btn-login"
            >
                <i class="fas fa-user"></i>
                Iniciar Sesión
            </a>

        </div>


        <p>
            Creado por NexoWeb.Py
        </p>

    </div>

</div>


</footer>




    <!-- SCRIPT INTERACTIVO EN JAVASCRIPT -->
    <script>
        // LÓGICA DE CARRUSEL POR TARJETA
        function moveSlide(id, direction) {
            const container = document.getElementById('carousel-' + id);
            const slides = container.querySelectorAll('.carousel-slide');
            const dots = container.querySelectorAll('.dot');
            let activeIndex = 0;

            slides.forEach((slide, index) => {
                if (slide.classList.contains('active')) {
                    activeIndex = index;
                }
            });

            slides[activeIndex].classList.remove('active');
            if (dots.length > 0) dots[activeIndex].classList.remove('active');

            let newIndex = activeIndex + direction;
            if (newIndex >= slides.length) newIndex = 0;
            if (newIndex < 0) newIndex = slides.length - 1;

            slides[newIndex].classList.add('active');
            if (dots.length > 0) dots[newIndex].classList.add('active');
        }

        function setSlide(id, index) {
            const container = document.getElementById('carousel-' + id);
            const slides = container.querySelectorAll('.carousel-slide');
            const dots = container.querySelectorAll('.dot');

            slides.forEach(slide => slide.classList.remove('active'));
            dots.forEach(dot => dot.classList.remove('active'));

            slides[index].classList.add('active');
            dots[index].classList.add('active');
        }

        // FILTRO DE CATEGORÍAS
        function filterCategory(category, button) {
            const cards = document.querySelectorAll('.project-card');
            const buttons = document.querySelectorAll('.filter-btn');

            buttons.forEach(btn => btn.classList.remove('active'));
            if (button) button.classList.add('active');

            cards.forEach(card => {
                if (category === 'all' || card.getAttribute('data-category') === category) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        // MODAL DE DETALLES
        function openModal(modalId) {
            document.getElementById(modalId).classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
            document.body.style.overflow = 'auto';
        }

        // Cerrar modal haciendo clic fuera del contenido
        window.onclick = function(event) {
            if (event.target.classList.contains('modal-overlay')) {
                event.target.classList.remove('active');
                document.body.style.overflow = 'auto';
            }
        };
          /* Año automático */

    document.getElementById("currentYear").textContent =
        new Date().getFullYear();

    </script>
</body>
</html>
