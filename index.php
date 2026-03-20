<?php
// /Applications/AMPPS/www/Bnner/index.php
// Página web principal que muestra el nombre de la empresa y redirige al login administrativo.

require_once __DIR__ . '/config/Database.php';

try {
    $db = \Config\Database::getInstance();
    $rows = $db->query("SELECT clave, valor FROM configuracion WHERE clave IN ('empresa_nombre', 'fondo_login', 'empresa_logo')")->fetchAll(\PDO::FETCH_KEY_PAIR);
    $empresaNombre = $rows['empresa_nombre'] ?? 'Agencia de publicidad';
    $fondoLogin = $rows['fondo_login'] ?? '';
    $logo = $rows['empresa_logo'] ?? '';
}
catch (\Exception $e) {
    $empresaNombre = 'Agencia de publicidad';
    $fondoLogin = '';
    $logo = '';
}

// Determinar basePath
$scriptName = dirname($_SERVER['SCRIPT_NAME']);
if ($scriptName === '/' || $scriptName === '\\')
    $scriptName = '';
$basePath = rtrim($scriptName, '/\\');

if (empty($fondoLogin)) {
    $fondoLogin = $basePath . '/public/img/LEON.jpg';
}

$bgUrl = strpos($fondoLogin, 'data:image') === 0 ? $fondoLogin : htmlspecialchars($fondoLogin);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?= htmlspecialchars($empresaNombre)?>
    </title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: #080a14;
            color: #ffffff;
            overflow-x: hidden;
            position: relative;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .hero-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('<?= $bgUrl?>') center center / cover no-repeat;
            filter: brightness(0.6) saturate(1.2);
            z-index: 0;
        }

        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(8, 10, 20, 0.8) 0%, rgba(30, 10, 50, 0.6) 100%);
            z-index: 1;
        }

        header {
            position: relative;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px 40px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .social-icons {
            position: absolute;
            left: 40px;
            display: flex;
            gap: 15px;
        }

        .social-icons a {
            color: #cbd5e1;
            font-size: 1.2rem;
            transition: color 0.3s;
        }

        .social-icons a:hover {
            color: #fff;
        }

        .nav-container {
            display: flex;
            align-items: center;
            gap: 50px;
        }

        .main-nav {
            display: flex;
            gap: 30px;
            align-items: center;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            opacity: 0.8;
        }

        .main-nav a {
            text-decoration: none;
            color: #cbd5e1;
            transition: color 0.3s;
        }

        .main-nav a:hover,
        .main-nav a.active {
            color: #fff;
            opacity: 1;
        }

        .logo-center {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
        }

        .logo-center img {
            height: 45px;
        }

        .logo-center span {
            font-size: 0.70rem;
            color: #cbd5e1;
            font-weight: 300;
            margin-top: 5px;
            opacity: 0.8;
            letter-spacing: 0.5px;
        }

        .btn-header-login {
            position: absolute;
            right: 40px;
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            text-decoration: none;
            padding: 8px 20px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }

        .btn-header-login:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-1px);
        }

        .hero-container {
            position: relative;
            z-index: 10;
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 40px;
        }

        .hero-title {
            font-size: 5rem;
            font-weight: 800;
            margin-bottom: 25px;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
            letter-spacing: -1px;
        }

        .hero-subtitle {
            font-size: 1.15rem;
            color: #e2e8f0;
            margin-bottom: 45px;
            font-weight: 400;
            letter-spacing: 0.5px;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: #9333ea;
            color: #fff;
            border: none;
            padding: 14px 34px;
            border-radius: 4px;
            font-size: 0.95rem;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 10px 20px -5px rgba(147, 51, 234, 0.5);
        }

        .btn-action:hover {
            background: #a855f7;
            transform: translateY(-2px);
            box-shadow: 0 15px 25px -5px rgba(168, 85, 247, 0.6);
        }

        .clients-section {
            position: relative;
            z-index: 10;
            padding: 40px 20px;
            text-align: center;
            background: linear-gradient(to top, rgba(8, 10, 20, 1) 10%, transparent 100%);
            margin-top: auto;
        }

        .clients-title {
            font-size: 0.8rem;
            letter-spacing: 2.5px;
            font-weight: 700;
            color: #cbd5e1;
            margin-bottom: 30px;
            text-transform: uppercase;
        }

        .clients-logos {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 50px;
            flex-wrap: wrap;
            opacity: 0.6;
        }

        .clients-logos img,
        .clients-logos h3 {
            transition: all 0.3s;
        }

        .clients-logos img:hover,
        .clients-logos h3:hover {
            opacity: 1;
            transform: scale(1.05);
        }

        @media (max-width: 992px) {
            header {
                flex-direction: column;
                gap: 20px;
                padding: 20px;
                height: auto;
            }

            .social-icons {
                position: static;
                justify-content: center;
                width: 100%;
            }

            .btn-header-login {
                position: static;
                margin-top: 10px;
            }

            .nav-container {
                flex-direction: column;
                gap: 20px;
            }

            .main-nav {
                flex-wrap: wrap;
                justify-content: center;
                gap: 15px;
            }

            .logo-center {
                order: -1;
                margin-bottom: 15px;
            }

            .hero-title {
                font-size: 3.5rem;
            }
        }

        /* ═══ Modal Crear Pedido ═══ */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .78);
            z-index: 1000;
            justify-content: center;
            align-items: center;
            padding: 20px;
            backdrop-filter: blur(6px);
        }
        .modal-overlay.open { display: flex; }

        .modal-box {
            background: linear-gradient(145deg, #1e1b3a, #15132b);
            border: 1px solid rgba(147, 51, 234, .2);
            border-radius: 20px;
            width: 100%;
            max-width: 600px;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
            box-shadow: 0 30px 80px rgba(0, 0, 0, .6), 0 0 60px rgba(147, 51, 234, .15);
            animation: modalSlide .28s cubic-bezier(.16, 1, .3, 1);
            overflow: hidden;
        }

        @keyframes modalSlide {
            from { opacity: 0; transform: translateY(24px) scale(.97); }
        }

        .modal-head {
            padding: 22px 26px 18px;
            border-bottom: 1px solid rgba(255, 255, 255, .07);
            display: flex;
            align-items: center;
            gap: 14px;
            flex-shrink: 0;
        }
        .modal-head-icon { font-size: 1.6rem; }
        .modal-head-info h2 { font-size: 1.15rem; font-weight: 700; }
        .modal-head-info p { font-size: .8rem; color: #94a3b8; margin-top: 2px; }
        .modal-close {
            margin-left: auto;
            background: none;
            border: none;
            color: #94a3b8;
            font-size: 1.6rem;
            cursor: pointer;
            line-height: 1;
            padding: 0 4px;
            transition: color .2s;
        }
        .modal-close:hover { color: #fff; }

        /* Servicio grid */
        .service-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 12px;
            padding: 18px 24px 6px;
        }
        @media(max-width: 550px) { .service-grid { grid-template-columns: repeat(2, 1fr); } }

        .service-card {
            position: relative;
            border-radius: 14px;
            overflow: hidden;
            cursor: pointer;
            border: 2px solid rgba(255,255,255,.06);
            transition: all .25s;
            aspect-ratio: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-end;
        }
        .service-card img {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform .35s;
        }
        .service-card:hover img { transform: scale(1.1); }
        .service-card::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(0deg, rgba(0,0,0,.8) 0%, rgba(0,0,0,.15) 50%, transparent 100%);
            z-index: 1;
        }
        .service-card .svc-label {
            position: relative;
            z-index: 2;
            color: #fff;
            font-size: .85rem;
            font-weight: 700;
            padding: 10px;
            text-align: center;
            text-shadow: 0 2px 8px rgba(0,0,0,.7);
        }
        
        /* Icon styles */
        .modal-btn-icon {
            width: 18px;
            height: 18px;
            fill: none;
            stroke: currentColor;
            stroke-width: 2.5;
            stroke-linecap: round;
            stroke-linejoin: round;
        }
        .service-card.selected {
            border-color: #9333ea;
            box-shadow: 0 0 0 3px rgba(147,51,234,.45), 0 8px 24px rgba(147,51,234,.2);
        }
        .service-card.selected::before {
            content: '\2713';
            position: absolute;
            top: 7px;
            right: 7px;
            z-index: 3;
            background: #9333ea;
            color: #fff;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .85rem;
            font-weight: 800;
            box-shadow: 0 2px 8px rgba(147,51,234,.4);
        }

        /* Form */
        .crear-form { padding: 4px 24px 24px; overflow-y: auto; }
        .crear-form label {
            display: block;
            font-size: .76rem;
            font-weight: 600;
            color: #94a3b8;
            margin-bottom: 5px;
            margin-top: 14px;
            text-transform: uppercase;
            letter-spacing: .04em;
        }
        .crear-form input, .crear-form textarea {
            width: 100%;
            background: rgba(0,0,0,.25);
            border: 1px solid rgba(255,255,255,.1);
            border-radius: 10px;
            padding: 12px 14px;
            color: #f1f5f9;
            font-size: .9rem;
            font-family: inherit;
            outline: none;
            transition: border-color .2s, box-shadow .2s;
        }
        .crear-form input:focus, .crear-form textarea:focus {
            border-color: #9333ea;
            box-shadow: 0 0 0 3px rgba(147,51,234,.15);
        }
        .crear-form input::placeholder, .crear-form textarea::placeholder { color: #475569; }
        .crear-form textarea { resize: vertical; min-height: 70px; }
        .crear-form .required-star { color: #f43f5e; }
        .crear-form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }
        @media(max-width: 480px) { .crear-form-row { grid-template-columns: 1fr; } }

        /* Sub-panel Impresión */
        .sub-panel-impresion {
            display: none;
            padding: 14px;
            margin: -5px 0 8px;
            background: rgba(147,51,234,.08);
            border: 1px solid rgba(147,51,234,.25);
            border-radius: 14px;
            animation: modalSlide .25s;
        }
        .sub-panel-impresion.open { display: block; }
        .sub-panel-impresion h4 {
            color: #c4b5fd;
            font-size: .82rem;
            font-weight: 700;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: .04em;
        }
        .sub-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
        }
        @media(max-width: 480px) { .sub-grid { grid-template-columns: repeat(2, 1fr); } }
        .sub-card {
            position: relative;
            overflow: hidden;
            border-radius: 12px;
            cursor: pointer;
            border: 2px solid rgba(255,255,255,.06);
            transition: all .25s;
            aspect-ratio: 1;
            display: flex;
            align-items: flex-end;
        }
        .sub-card img {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform .35s;
        }
        .sub-card:hover img { transform: scale(1.1); }
        .sub-card::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(0deg, rgba(0,0,0,.85) 0%, rgba(0,0,0,.2) 50%, transparent 100%);
            z-index: 1;
        }
        .sub-card .sub-label {
            position: relative;
            z-index: 2;
            color: #fff;
            font-size: .72rem;
            font-weight: 700;
            padding: 8px;
            text-align: center;
            width: 100%;
            text-shadow: 0 2px 8px rgba(0,0,0,.7);
        }
        .sub-card.selected {
            border-color: #a78bfa;
            box-shadow: 0 0 0 2px rgba(167,139,250,.5);
        }
        .sub-card.selected::before {
            content: '\2713';
            position: absolute;
            top: 5px;
            right: 5px;
            z-index: 3;
            background: #a78bfa;
            color: #fff;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .7rem;
            font-weight: 800;
        }

        /* File upload landing */
        .landing-file-upload {
            margin-top: 14px;
            border: 2px dashed rgba(255,255,255,.15);
            border-radius: 12px;
            padding: 18px;
            text-align: center;
            cursor: pointer;
            transition: border-color .2s, background .2s;
            background: rgba(0,0,0,.15);
        }
        .landing-file-upload:hover {
            border-color: rgba(147,51,234,.5);
            background: rgba(147,51,234,.06);
        }
        .landing-file-upload p {
            color: #64748b;
            font-size: .82rem;
            margin: 0;
        }
        .landing-file-upload .upload-icon {
            font-size: 1.5rem;
            display: block;
            margin-bottom: 4px;
        }

        .btn-submit-pedido {
            width: 100%;
            margin-top: 20px;
            padding: 14px;
            background: linear-gradient(135deg, #9333ea, #7c3aed);
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: transform .2s, box-shadow .2s;
            box-shadow: 0 6px 20px rgba(147,51,234,.35);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-submit-pedido:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(147,51,234,.5);
        }
        .btn-submit-pedido:disabled { opacity: .5; cursor: not-allowed; transform: none; }

        /* Success overlay inside modal */
        .success-overlay {
            position: absolute;
            inset: 0;
            background: rgba(21, 19, 43, .97);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 10;
            padding: 40px;
            text-align: center;
            animation: modalSlide .3s;
        }
        .success-overlay .check-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #10b981, #059669);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(16,185,129,.3);
        }
        .success-overlay h3 { font-size: 1.3rem; font-weight: 700; margin-bottom: 8px; }
        .success-overlay p { color: #94a3b8; font-size: .9rem; margin-bottom: 6px; }
        .success-overlay .ped-number {
            font-size: 1.1rem;
            color: #a78bfa;
            font-weight: 800;
            margin-bottom: 24px;
        }
        .success-overlay .btn-cerrar {
            background: rgba(255,255,255,.08);
            color: #e2e8f0;
            border: 1px solid rgba(255,255,255,.15);
            padding: 10px 30px;
            border-radius: 10px;
            cursor: pointer;
            font-size: .9rem;
            font-weight: 600;
            transition: background .2s;
        }
        .success-overlay .btn-cerrar:hover { background: rgba(255,255,255,.15); }

        /* Toast */
        .toast {
            position: fixed;
            bottom: 28px;
            right: 28px;
            background: #1e1b3a;
            border: 1px solid rgba(255, 255, 255, .1);
            border-left: 4px solid #9333ea;
            border-radius: 12px;
            padding: 14px 22px;
            font-size: .88rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
            transform: translateY(80px);
            opacity: 0;
            transition: all .3s cubic-bezier(.16, 1, .3, 1);
            z-index: 9999;
            color: #f1f5f9;
        }
        .toast.show { transform: translateY(0); opacity: 1; }
        .toast.error { border-left-color: #ef4444; }
    </style>
</head>

<body>

    <div class="hero-bg"></div>
    <div class="hero-overlay"></div>

    <header>
        <div class="social-icons">
            <a href="#"><i class="fab fa-whatsapp"></i></a>
            <a href="#"><i class="fab fa-facebook-f"></i></a>
            <a href="#"><i class="fab fa-instagram"></i></a>
            <a href="#"><i class="fab fa-pinterest-p"></i></a>
        </div>

        <div class="nav-container">
            <nav class="main-nav">
                <a href="#" class="active">Inicio</a>
                <a href="#">Nosotros</a>
                <a href="#">Portafolio</a>
            </nav>

            <a href="<?= $basePath?>/" class="logo-center">
                <?php if ($logo): ?>
                <img src="<?= htmlspecialchars($logo)?>" alt="Banner">
                <?php
else: ?>
                <h2 style="font-size: 2.2rem; font-weight:800; letter-spacing:-1px; text-transform:lowercase">
                    <span style="font-size:1.5rem; display:block; margin-bottom:-10px;">=</span>Banner
                </h2>
                <?php
endif; ?>
                <span>Agencia de Publicidad</span>
            </a>

            <nav class="main-nav">
                <a href="#">Servicios</a>
                <a href="#">Tienda</a>
                <a href="#">Contacto</a>
            </nav>
        </div>

        <a href="<?= $basePath?>/public/" class="btn-header-login">
            &#128100; Área de Empleados
        </a>
    </header>

    <div class="hero-container">
        <h1 class="hero-title">Agencia de publicidad</h1>
        <p class="hero-subtitle">
            Impresión digital en gran formato, Bordados, Corte Laser y Mucho más.
        </p>
        <a href="javascript:void(0)" class="btn-action" onclick="abrirModalCrear()">
            📝 Crea tu Pedido &nbsp;&rarr;
        </a>
    </div>

    <div class="clients-section">
        <p class="clients-title">ALGVNOS DE NUESTROS CLIENTES</p>
        <div class="clients-logos">
            <h3 style="color:#cbd5e1;font-weight:700;font-size:1.8rem;letter-spacing:-1px;">maxim<span
                    style="font-weight:300">&reg;</span></h3>
            <h3 style="color:#cbd5e1;font-family:cursive;font-size:1.8rem;">La Costurera</h3>
            <h3
                style="color:#cbd5e1;border-bottom:3px solid #ef4444;border-top:3px solid #ef4444;border-radius:50%;padding:5px 20px;font-size:1.3rem;">
                FOCUS</h3>
            <h3 style="color:#cbd5e1;font-style:italic;font-weight:800;font-size:1.5rem;">= PAGA<br>RAPIDO</h3>
            <h3 style="color:#cbd5e1;font-weight:300;font-size:1.8rem;">Redexcell</h3>
        </div>
    </div>

    <!-- ====== MODAL CREAR PEDIDO ====== -->
    <div class="modal-overlay" id="modalCrear" onclick="if(event.target===this) cerrarModalCrear()">
        <div class="modal-box" style="position:relative;">
            <div class="modal-head">
                <span class="modal-head-icon">
                    <svg class="modal-btn-icon" style="width:24px; height:24px; color:#9333ea;" viewBox="0 0 24 24"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/></svg>
                </span>
                <div class="modal-head-info">
                    <h2>Crea tu Pedido</h2>
                    <p>Selecciona el servicio que necesitas</p>
                </div>
                <button class="modal-close" onclick="cerrarModalCrear()">&times;</button>
            </div>

            <div class="service-grid" id="serviceGrid">
                <div class="service-card" data-servicio="DTF" onclick="seleccionarServicio(this)">
                    <img src="<?= $basePath ?>/public/img/servicios/dtf.png" alt="DTF">
                    <div class="svc-label">DTF</div>
                </div>
                <div class="service-card" data-servicio="Impresión" onclick="seleccionarServicio(this)">
                    <img src="<?= $basePath ?>/public/img/servicios/impresion.png" alt="Impresión">
                    <div class="svc-label">Impresión</div>
                </div>
                <div class="service-card" data-servicio="Láser" onclick="seleccionarServicio(this)">
                    <img src="<?= $basePath ?>/public/img/servicios/laser.png" alt="Láser">
                    <div class="svc-label">Láser</div>
                </div>
                <div class="service-card" data-servicio="Bordado" onclick="seleccionarServicio(this)">
                    <img src="<?= $basePath ?>/public/img/servicios/bordado.png" alt="Bordado">
                    <div class="svc-label">Bordado</div>
                </div>
                <div class="service-card" data-servicio="Otro" onclick="seleccionarServicio(this)">
                    <img src="<?= $basePath ?>/public/img/servicios/service_otro.png" alt="Otro">
                    <div class="svc-label">Otro</div>
                </div>
            </div>

            <!-- Sub-panel: Tipos de Impresión -->
            <div class="sub-panel-impresion" id="subPanelImpresion">
                <h4><svg class="modal-btn-icon" style="margin-right:6px;" viewBox="0 0 24 24"><path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6z"/></svg> Selecciona el tipo de impresión</h4>
                <div class="sub-grid">
                    <div class="sub-card" data-subtipo="Ecosolvente" onclick="seleccionarSubImpresion(this)">
                        <img src="<?= $basePath ?>/public/img/servicios/ecosolvente.png" alt="Ecosolvente">
                        <div class="sub-label">Ecosolvente</div>
                    </div>
                    <div class="sub-card" data-subtipo="UV" onclick="seleccionarSubImpresion(this)">
                        <img src="<?= $basePath ?>/public/img/servicios/uv.png" alt="UV">
                        <div class="sub-label">UV</div>
                    </div>
                    <div class="sub-card" data-subtipo="Sublimación" onclick="seleccionarSubImpresion(this)">
                        <img src="<?= $basePath ?>/public/img/servicios/sublimacion.png" alt="Sublimación">
                        <div class="sub-label">Sublimación</div>
                    </div>
                    <div class="sub-card" data-subtipo="DTF" onclick="seleccionarSubImpresion(this)">
                        <img src="<?= $basePath ?>/public/img/servicios/dtf.png" alt="DTF">
                        <div class="sub-label">DTF</div>
                    </div>
                </div>
            </div>

            <div class="crear-form" id="crearForm">
                <div class="crear-form-row">
                    <div>
                        <label>Tu Nombre <span class="required-star">*</span></label>
                        <input type="text" id="cpNombre" placeholder="Ej: Juan Pérez">
                    </div>
                    <div>
                        <label>Número Celular <span class="required-star">*</span></label>
                        <input type="tel" id="cpTelefono" placeholder="Ej: 300 123 4567">
                    </div>
                </div>
                <label>Descripción del Trabajo</label>
                <textarea id="cpNotas" placeholder="Medidas, colores, cantidad, material, etc."></textarea>

                <!-- Archivos adjuntos -->
                <label><svg class="modal-btn-icon" style="margin-right:4px; width:14px; height:14px;" viewBox="0 0 24 24"><path d="M21.44 11.05l-9.19 9.19a6 6 0 01-8.49-8.49l9.19-9.19a4 4 0 015.66 5.66l-9.2 9.19a2 2 0 01-2.83-2.83l8.49-8.48"/></svg> Archivos Adjuntos</label>
                <div class="landing-file-upload" id="landingDropZone" onclick="document.getElementById('landingFileInput').click()">
                    <input type="file" id="landingFileInput" multiple style="display:none;" onchange="handleLandingFiles(this.files)">
                    <span class="upload-icon"><svg style="width:32px; height:32px; color:rgba(255,255,255,.3);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg></span>
                    <p>Arrastra archivos aquí o haz clic para seleccionar</p>
                </div>
                <div id="landingFileList" style="margin-top:8px; display:flex; flex-direction:column; gap:5px;"></div>

                <button class="btn-submit-pedido" id="btnSubmitPedido" onclick="enviarPedidoRapido()">
                    <svg class="modal-btn-icon" style="margin-right:8px;" viewBox="0 0 24 24"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/></svg>
                    Crear Pedido
                </button>
            </div>

            <div class="success-overlay" id="successOverlay" style="display:none;">
                <div class="check-circle"><svg style="width:40px; height:40px; color:#fff;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></div>
                <h3>¡Pedido Creado!</h3>
                <p>Tu pedido ha sido registrado exitosamente.</p>
                <div class="ped-number" id="successPedId"></div>
                <p style="font-size:.82rem;">Pronto te contactaremos para confirmar los detalles.</p>
                <button class="btn-cerrar" onclick="cerrarModalCrear()" style="margin-top:16px;">Cerrar</button>
            </div>
        </div>
    </div>

    <!-- Toast -->
    <div class="toast" id="toastEl"><span id="toastMsg">OK</span></div>

    <script>
        var basePath = '<?= $basePath ?>';
        var _servicioSeleccionado = '';
        var _subtipoImpresion = '';
        var _landingArchivos = [];
        
        const BTN_ROCKET_ICON = '<svg class="modal-btn-icon" style="margin-right:8px;" viewBox="0 0 24 24"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/></svg>';
        const FILE_CLIP_ICON = '<svg class="modal-btn-icon" style="margin-right:4px; width:14px; height:14px;" viewBox="0 0 24 24"><path d="M21.44 11.05l-9.19 9.19a6 6 0 01-8.49-8.49l9.19-9.19a4 4 0 015.66 5.66l-9.2 9.19a2 2 0 01-2.83-2.83l8.49-8.48"/></svg>';

        function abrirModalCrear() {
            _servicioSeleccionado = '';
            _subtipoImpresion = '';
            _landingArchivos = [];
            document.querySelectorAll('.service-card').forEach(function(c) { c.classList.remove('selected'); });
            document.querySelectorAll('.sub-card').forEach(function(c) { c.classList.remove('selected'); });
            document.getElementById('subPanelImpresion').classList.remove('open');
            document.getElementById('cpNombre').value = '';
            document.getElementById('cpTelefono').value = '';
            document.getElementById('cpNotas').value = '';
            document.getElementById('landingFileList').innerHTML = '';
            document.getElementById('landingFileInput').value = '';
            document.getElementById('btnSubmitPedido').disabled = false;
            document.getElementById('btnSubmitPedido').innerHTML = BTN_ROCKET_ICON + ' Crear Pedido';
            document.getElementById('successOverlay').style.display = 'none';
            document.getElementById('modalCrear').classList.add('open');
            document.body.style.overflow = 'hidden';
        }

        function cerrarModalCrear() {
            document.getElementById('modalCrear').classList.remove('open');
            document.body.style.overflow = '';
        }

        function seleccionarServicio(el) {
            document.querySelectorAll('.service-card').forEach(function(c) { c.classList.remove('selected'); });
            el.classList.add('selected');
            _servicioSeleccionado = el.dataset.servicio;
            _subtipoImpresion = '';
            document.querySelectorAll('.sub-card').forEach(function(c) { c.classList.remove('selected'); });

            // Show/hide sub-panel for Impresión
            var subPanel = document.getElementById('subPanelImpresion');
            if (_servicioSeleccionado === 'Impresión') {
                subPanel.classList.add('open');
            } else {
                subPanel.classList.remove('open');
            }
        }

        function seleccionarSubImpresion(el) {
            document.querySelectorAll('.sub-card').forEach(function(c) { c.classList.remove('selected'); });
            el.classList.add('selected');
            _subtipoImpresion = el.dataset.subtipo;
        }

        // --- File upload functions ---
        (function(){
            var dz = document.getElementById('landingDropZone');
            if (!dz) return;
            dz.addEventListener('dragover', function(e) {
                e.preventDefault();
                dz.style.borderColor = 'rgba(147,51,234,.6)';
                dz.style.background = 'rgba(147,51,234,.08)';
            });
            dz.addEventListener('dragleave', function(e) {
                e.preventDefault();
                dz.style.borderColor = 'rgba(255,255,255,.15)';
                dz.style.background = 'rgba(0,0,0,.15)';
            });
            dz.addEventListener('drop', function(e) {
                e.preventDefault();
                dz.style.borderColor = 'rgba(255,255,255,.15)';
                dz.style.background = 'rgba(0,0,0,.15)';
                handleLandingFiles(e.dataTransfer.files);
            });
        })();

        function handleLandingFiles(files) {
            for (var i = 0; i < files.length; i++) {
                _landingArchivos.push(files[i]);
            }
            renderLandingFileList();
        }

        function renderLandingFileList() {
            var list = document.getElementById('landingFileList');
            list.innerHTML = '';
            _landingArchivos.forEach(function(file, index) {
                var item = document.createElement('div');
                item.style.cssText = 'display:flex; justify-content:space-between; align-items:center; background:rgba(255,255,255,.05); padding:8px 12px; border-radius:8px; font-size:.82rem; border:1px solid rgba(255,255,255,.1);';
                item.innerHTML = '<span style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:80%; color:#cbd5e1;">' + FILE_CLIP_ICON + ' ' + file.name + '</span>' +
                    '<button type="button" onclick="removeLandingFile(' + index + ')" style="background:none; border:none; color:#f87171; cursor:pointer; font-weight:bold; font-size:1.1rem; line-height:1;">&times;</button>';
                list.appendChild(item);
            });
        }

        function removeLandingFile(index) {
            _landingArchivos.splice(index, 1);
            renderLandingFileList();
            document.getElementById('landingFileInput').value = '';
        }

        function showToast(msg, type) {
            var t = document.getElementById('toastEl');
            document.getElementById('toastMsg').textContent = msg;
            t.className = 'toast show ' + (type || '');
            setTimeout(function() { t.classList.remove('show'); }, 4000);
        }

        async function enviarPedidoRapido() {
            var nombre = document.getElementById('cpNombre').value.trim();
            var telefono = document.getElementById('cpTelefono').value.trim();
            var notas = document.getElementById('cpNotas').value.trim();
            var btn = document.getElementById('btnSubmitPedido');

            if (!nombre) { showToast('El nombre es obligatorio.', 'error'); return; }
            if (!telefono) { showToast('El número de celular es obligatorio.', 'error'); return; }
            if (!_servicioSeleccionado) { showToast('Selecciona un tipo de servicio.', 'error'); return; }
            if (_servicioSeleccionado === 'Impresión' && !_subtipoImpresion) {
                showToast('Selecciona el tipo de impresión.', 'error');
                return;
            }

            // Build service label
            var etiqueta = _servicioSeleccionado;
            if (_servicioSeleccionado === 'Impresión' && _subtipoImpresion) {
                etiqueta = 'Impresión ' + _subtipoImpresion;
            }
            var descripcion = '[' + etiqueta + '] ' + (notas || 'Sin notas adicionales');

            btn.disabled = true;
            btn.innerHTML = '⏳ Enviando...';

            // Use FormData to support file uploads
            var formData = new FormData();
            formData.append('cliente_nombre', nombre);
            formData.append('cliente_telefono', telefono);
            formData.append('descripcion', descripcion);
            formData.append('estado_pago', 'no_pago');
            formData.append('prioridad', 'normal');
            formData.append('total', '0');
            formData.append('abonado', '0');
            formData.append('metodo_pago', 'efectivo');
            formData.append('origen', 'pagina');

            _landingArchivos.forEach(function(file) {
                formData.append('archivos[]', file);
            });

            try {
                var r = await fetch(basePath + '/public/api/pedidos/crear-publico', {
                    method: 'POST',
                    body: formData
                });
                var res = await r.json();
                if (res.status === 'success') {
                    var pedId = res.data ? '#PED-' + String(res.data.pedido_id).padStart(4, '0') : '';
                    document.getElementById('successPedId').textContent = pedId;
                    document.getElementById('successOverlay').style.display = 'flex';
                } else {
                    throw new Error(res.message || 'Error desconocido');
                }
            } catch (e) {
                showToast('Error: ' + e.message, 'error');
                btn.disabled = false;
                btn.innerHTML = BTN_ROCKET_ICON + ' Crear Pedido';
            }
        }

        // Cerrar con Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') cerrarModalCrear();
        });
    </script>

</body>

</html>