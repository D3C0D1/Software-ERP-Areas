<?php
require_once __DIR__ . '/../config/Database.php';
try {
    $dbL = \Config\Database::getInstance();
    $loginRows = $dbL->query("SELECT clave, valor FROM configuracion WHERE clave IN
('empresa_nombre','mostrar_credenciales', 'fondo_login','sonido_habilitado','sonido_tema')")->fetchAll(\PDO::FETCH_KEY_PAIR);
    $loginEmpresaNombre = $loginRows['empresa_nombre'] ?? 'Banner';
    $mostrarCredenciales = ($loginRows['mostrar_credenciales'] ?? '1') === '1';
    $fondoLogin = $loginRows['fondo_login'] ?? '';
    $sonidoHabilitado = $loginRows['sonido_habilitado'] ?? '1';
    $sonidoTema = $loginRows['sonido_tema'] ?? 'cristal';
}
catch (\Exception $e) {
    $loginEmpresaNombre = 'Banner';
    $mostrarCredenciales = true;
    $fondoLogin = '';
}
$scriptName = dirname($_SERVER['SCRIPT_NAME']);
$basePath = rtrim($scriptName, '/\\');

if (empty($fondoLogin)) {
    $fondoLogin = $basePath . '/img/LEON.jpg';
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>
        <?= htmlspecialchars($loginEmpresaNombre)?> - Ingreso
    </title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
            background: #0f172a;
        }

        /* Fondo */
        .bg-cover {
            position: fixed;
            inset: 0;
            z-index: 0;
            background: url('<?= strpos($fondoLogin, ' data:image') === 0 ? $fondoLogin : htmlspecialchars($fondoLogin)?>') center center / cover no-repeat;
            filter: brightness(.35) saturate(.7);
        }

        /* Overlay degradado encima del fondo */
        .bg-overlay {
            position: fixed;
            inset: 0;
            z-index: 1;
            background: linear-gradient(135deg, rgba(15, 23, 42, .75) 0%, rgba(99, 102, 241, .18) 100%);
        }

        /* Tarjeta */
        .login-card {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 430px;
            padding: 40px 36px 36px;
            background: rgba(15, 23, 42, .82);
            backdrop-filter: blur(22px);
            -webkit-backdrop-filter: blur(22px);
            border: 1px solid rgba(255, 255, 255, .10);
            border-radius: 24px;
            box-shadow: 0 32px 80px rgba(0, 0, 0, .6);
            animation: slideUp .55s cubic-bezier(.16, 1, .3, 1) forwards;
            transform: translateY(30px);
            opacity: 0;
        }

        @keyframes slideUp {
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        /* Logo encima del formulario */
        .brand-logo {
            display: block;
            width: 90px;
            height: 90px;
            object-fit: contain;
            margin: 0 auto 18px;
            border-radius: 18px;
            border: 2px solid rgba(255, 255, 255, .12);
            background: rgba(99, 102, 241, .1);
            padding: 8px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, .4);
        }

        .brand-logo-placeholder {
            width: 90px;
            height: 90px;
            margin: 0 auto 18px;
            border-radius: 18px;
            border: 2px solid rgba(255, 255, 255, .12);
            background: rgba(99, 102, 241, .1);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
        }

        .header {
            text-align: center;
            margin-bottom: 28px;
        }

        .header h1 {
            font-size: 1.75rem;
            font-weight: 800;
            background: linear-gradient(120deg, #fff 0%, #a5b4fc 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 6px;
        }

        .header p {
            color: #94a3b8;
            font-size: .9rem;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-group label {
            display: block;
            font-size: .82rem;
            font-weight: 600;
            color: #94a3b8;
            margin-bottom: 7px;
        }

        .form-control {
            width: 100%;
            background: rgba(255, 255, 255, .06);
            border: 1px solid rgba(255, 255, 255, .12);
            border-radius: 12px;
            padding: .85rem 1rem;
            color: #f1f5f9;
            font-size: .95rem;
            transition: border-color .25s, background .25s;
            outline: none;
        }

        .form-control::placeholder {
            color: #475569;
        }

        .form-control:focus {
            border-color: #6366f1;
            background: rgba(99, 102, 241, .08);
        }

        .btn-submit {
            width: 100%;
            padding: .9rem;
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 4px 20px rgba(99, 102, 241, .45);
            transition: transform .15s, box-shadow .15s;
            position: relative;
            overflow: hidden;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(99, 102, 241, .55);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .password-container {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .toggle-password:hover {
            color: #f1f5f9;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 18px;
            font-size: .87rem;
            display: none;
            text-align: center;
        }

        .alert.error {
            background: rgba(239, 68, 68, .12);
            border: 1px solid rgba(239, 68, 68, .25);
            color: #fca5a5;
            display: block;
        }

        .alert.success {
            background: rgba(16, 185, 129, .10);
            border: 1px solid rgba(16, 185, 129, .25);
            color: #6ee7b7;
            display: block;
        }

        /* Quick login panel */
        .creds-panel {
            position: fixed;
            top: 20px;
            right: 20px;
            background: rgba(15, 23, 42, .92);
            border: 1px solid rgba(99, 102, 241, .4);
            padding: 18px;
            border-radius: 16px;
            box-shadow: 0 12px 40px rgba(0, 0, 0, .5);
            z-index: 999;
            backdrop-filter: blur(12px);
            color: #f1f5f9;
            width: 290px;
            animation: slideLeft .45s ease;
        }

        @keyframes slideLeft {
            from {
                transform: translateX(110%);
                opacity: 0;
            }
        }

        .creds-title {
            font-size: .95rem;
            font-weight: 700;
            color: #6ee7b7;
            margin-bottom: 8px;
            padding-bottom: 8px;
            border-bottom: 1px solid rgba(255, 255, 255, .08);
        }

        .creds-role {
            background: rgba(0, 0, 0, .3);
            padding: 10px 12px;
            border-radius: 10px;
            margin-bottom: 8px;
            cursor: pointer;
            border: 1px solid transparent;
            transition: .25s;
        }

        .creds-role:hover {
            border-color: #6366f1;
        }

        .creds-role strong {
            color: #fff;
            display: block;
            margin-bottom: 2px;
            font-size: .88rem;
        }

        .creds-role span {
            color: #94a3b8;
            font-size: .76rem;
        }

        .creds-close {
            position: absolute;
            top: 10px;
            right: 12px;
            background: none;
            border: none;
            color: #64748b;
            font-size: 1.4rem;
            cursor: pointer;
        }

        .creds-close:hover {
            color: #fff;
        }

        /* Modal persistente para sesión expirada */
        .timeout-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.85);
            z-index: 2000;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(10px);
        }

        .timeout-content {
            background: #1E293B;
            padding: 40px;
            border-radius: 24px;
            text-align: center;
            max-width: 400px;
            border: 1px solid rgba(99, 102, 241, 0.4);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.7);
            animation: popIn 0.5s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes popIn {
            from {
                transform: scale(0.8);
                opacity: 0;
            }

            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        .timeout-icon {
            font-size: 60px;
            margin-bottom: 20px;
        }

        .timeout-title {
            font-size: 1.6rem;
            font-weight: 800;
            color: #F8FAFC;
            margin-bottom: 12px;
        }

        .timeout-text {
            color: #94A3B8;
            margin-bottom: 30px;
            font-size: 1rem;
            line-height: 1.6;
        }
    </style>
</head>

<body>
    <div class="bg-cover"></div>
    <div class="bg-overlay"></div>

    <!-- Modal Sesión Expirada -->
    <div id="timeoutModal" class="timeout-modal">
        <div class="timeout-content">
            <div class="timeout-icon">⏰</div>
            <div class="timeout-title">Sesión Cerrada</div>
            <div class="timeout-text">Tu sesión ha sido cerrada por inactividad para proteger tu cuenta.</div>
            <button class="btn-submit" onclick="document.getElementById('timeoutModal').style.display='none'">Volver a
                Ingresar</button>
        </div>
    </div>

    <!-- Quick credentials panel -->
    <?php if ($mostrarCredenciales): ?>
    <div id="credsPanel" class="creds-panel">
        <button class="creds-close"
            onclick="document.getElementById('credsPanel').style.display='none'">&times;</button>
        <div class="creds-title">&#128273; Datos de Prueba</div>
        <div class="creds-role" onclick="fillCreds('admin@erp.com','admin123')">
            <strong>Administrador (Gerencia)</strong>
            <span>admin@erp.com &nbsp;|&nbsp; admin123</span>
        </div>
        <div class="creds-role" onclick="fillCreds('juan@erp.com','operador123')">
            <strong>Operador (Planta)</strong>
            <span>juan@erp.com &nbsp;|&nbsp; operador123</span>
        </div>
    </div>
    <?php
endif; ?>

    <!-- Card principal -->
    <div class="login-card">
        <!-- Logo / imagen sobre el formulario -->
        <?php
$logoPath = $basePath . '/img/Logo.png';
$logoPhysical = __DIR__ . '/../public/img/Logo.png';
?>
        <?php if (file_exists($logoPhysical)): ?>
        <img src="<?= htmlspecialchars($logoPath)?>" class="brand-logo" alt="Logo">
        <?php
else: ?>
        <div class="brand-logo-placeholder">&#127962;</div>
        <?php
endif; ?>

        <div class="header">
            <h1>
                <?= htmlspecialchars($loginEmpresaNombre)?>
            </h1>
            <p>Accede a tu cuenta productiva</p>
        </div>

        <div id="alertBox" class="alert"></div>

        <form id="loginForm">
            <div class="form-group">
                <label for="email">Usuario o Correo</label>
                <input type="text" id="email" name="email" class="form-control"
                    placeholder="Ej: oper01 o correo@empresa.com" required autocomplete="username">
            </div>
            <div class="form-group">
                <label for="password">Contrasena</label>
                <div class="password-container">
                    <input type="password" id="password" name="password" class="form-control"
                        style="padding-right: 40px;"
                        placeholder="&#9679;&#9679;&#9679;&#9679;&#9679;&#9679;&#9679;&#9679;" required
                        autocomplete="current-password">
                    <button type="button" for="password" id="togglePasswordBtn" class="toggle-password"
                        onclick="togglePassword()" tabindex="-1">
                        <svg id="eyeIcon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </button>
                </div>
            </div>
            <button type="submit" id="btnSubmit" class="btn-submit">Ingresar al Sistema</button>
        </form>
    </div>

    <script>
        var BASE = "<?php echo addslashes($basePath); ?>";

        document.addEventListener('DOMContentLoaded', function () {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('error') === 'timeout') {
                document.getElementById('timeoutModal').style.display = 'flex';
            }
        });

        function fillCreds(email, pass) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = pass;
            var a = document.getElementById('alertBox');
            a.textContent = 'Credenciales cargadas. Da clic en Ingresar.';
            a.className = 'alert success';
        }

        document.getElementById('loginForm').addEventListener('submit', async function (e) {
            e.preventDefault();
            var email = document.getElementById('email').value;
            var password = document.getElementById('password').value;
            var btn = document.getElementById('btnSubmit');
            var alert = document.getElementById('alertBox');
            btn.textContent = 'Verificando...';
            btn.disabled = true;
            alert.className = 'alert';
            try {
                var r = await fetch(BASE + '/api/login', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email: email, password: password })
                });  // Si la respuesta no es OK (ej. 404, 500) pero trae JSON
                if (!r.ok) {
                    var errorData;
                    try {
                        errorData = await r.json();
                    } catch (e) {
                        throw new Error(`HTTP Error ${r.status}`);
                    }
                    throw new Error(errorData.message || `Error ${r.status}`);
                }

                var res = await r.json();
                if (res.status === 'success') {
                    alert.textContent = 'Acceso concedido. Redirigiendo...';
                    alert.className = 'alert success';
                    if (window.BannerSounds) BannerSounds.login();
                    setTimeout(function () { window.location.href = BASE + '/dashboard'; }, 900);
                } else {
                    throw new Error(res.message || 'Error al iniciar sesion.');
                }
            } catch (err) {
                console.error("Fetch Error Details:", err);
                alert.textContent = 'Error de conexion con el servidor. Detalles: ' + err.message + ' (Ver consola para mas info)';
                alert.className = 'alert error';
                btn.textContent = 'Ingresar al Sistema';
                btn.disabled = false;
            }
        });

        // Functionality for Showing/Hiding password
        function togglePassword() {
            var pwd = document.getElementById('password');
            var eye = document.getElementById('eyeIcon');
            if (pwd.type === 'password') {
                pwd.type = 'text';
                // Cambio a icono tachado
                eye.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>';
            } else {
                pwd.type = 'password';
                // Cambio a icono normal
                eye.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
            }
        }
    </script>
    <script>
  .BANNER_SOUND_CFG = { enabled: <?= $sonidoHabilitado === '1' ? 'true' : 'false' ?>, theme: '<?= htmlspecialchars($sonidoTema)?>' };
    </script>
    <script src="<?= $basePath?>/js/sounds.js"></script>
</body>

</html>