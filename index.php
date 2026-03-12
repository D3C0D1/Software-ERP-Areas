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
        <a href="<?= $basePath?>/public/" class="btn-action">
            mas información &nbsp;&rarr;
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

</body>

</html>