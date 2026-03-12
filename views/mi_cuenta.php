<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ./login');
    exit;
}

require_once __DIR__ . '/../config/Database.php';
$db = \Config\Database::getInstance();
$userId = (int)$_SESSION['user_id'];
$role = $_SESSION['role'] ?? 'Operador';

// Cargar datos del usuario actual
$stmt = $db->prepare("SELECT u.nombre, u.email, u.foto_perfil, r.nombre AS rol_nombre FROM usuarios u JOIN roles r ON u.rol_id = r.id WHERE u.id = :id");
$stmt->execute(['id' => $userId]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    header('Location: ./dashboard');
    exit;
}

$csrfToken = \App\Middlewares\CsrfMiddleware::generateToken();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <meta name="csrf-token" content="<?= $csrfToken?>">
    <title>Usuario –
        <?= htmlspecialchars($usuario['nombre'])?>
    </title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --primary: #4F46E5;
            --primary-glow: rgba(79, 70, 229, 0.35);
            --emerald: #10B981;
            --bg: #0F172A;
            --surface: rgba(30, 41, 59, 0.75);
            --border: rgba(255, 255, 255, 0.08);
            --text: #F8FAFC;
            --muted: #94A3B8;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            overflow: hidden;
        }

        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            background-image:
                radial-gradient(at 10% 10%, rgba(79, 70, 229, 0.12) 0, transparent 50%),
                radial-gradient(at 90% 90%, rgba(16, 185, 129, 0.06) 0, transparent 50%);
        }

        /* ── TOP BAR ── */
        .topbar {
            height: 64px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            padding: 0 32px;
            justify-content: space-between;
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(12px);
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .topbar h1 {
            font-size: 1.15rem;
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        .topbar h1 span {
            background: linear-gradient(135deg, #818cf8, #6ee7b7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .btn-back {
            background: rgba(99, 102, 241, 0.1);
            color: #a5b4fc;
            border: 1px solid rgba(99, 102, 241, 0.25);
            border-radius: 8px;
            padding: 6px 14px;
            font-size: .82rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: background .2s;
        }

        .btn-back:hover {
            background: rgba(99, 102, 241, 0.2);
        }

        /* ── LAYOUT ── */
        .page-body {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 48px 24px;
        }

        .account-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 24px;
            width: 100%;
            max-width: 520px;
            backdrop-filter: blur(16px);
            box-shadow: 0 32px 80px rgba(0, 0, 0, 0.4);
            overflow: hidden;
        }

        /* ── BANNER HERO ── */
        .card-hero {
            background: linear-gradient(135deg, #1e1b4b 0%, #0f2027 100%);
            padding: 40px 36px 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        .card-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse at 50% 0%, rgba(79, 70, 229, 0.3) 0%, transparent 70%);
        }

        /* Avatar */
        .avatar-wrap {
            position: relative;
            z-index: 1;
            margin-bottom: 18px;
        }

        .avatar-ring {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            border: 4px solid #4F46E5;
            padding: 4px;
            background: rgba(79, 70, 229, 0.15);
            box-shadow: 0 0 30px rgba(79, 70, 229, 0.4);
            cursor: pointer;
            transition: all .35s cubic-bezier(.16, 1, .3, 1);
            position: relative;
        }

        .avatar-ring:hover {
            border-color: #6ee7b7;
            box-shadow: 0 0 40px rgba(110, 231, 183, 0.35);
            transform: scale(1.06);
        }

        .avatar-inner {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            overflow: hidden;
            background: #4F46E5;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .avatar-inner img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .avatar-initial {
            font-size: 2.8rem;
            font-weight: 900;
            color: #F1F5F9;
        }

        .avatar-overlay {
            position: absolute;
            inset: 4px;
            border-radius: 50%;
            background: rgba(0, 0, 0, 0.55);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity .2s;
            font-size: 1.6rem;
        }

        .avatar-ring:hover .avatar-overlay {
            opacity: 1;
        }

        .avatar-hint {
            font-size: .72rem;
            color: #64748b;
            position: absolute;
            bottom: -20px;
            left: 50%;
            transform: translateX(-50%);
            white-space: nowrap;
        }

        .hero-name {
            position: relative;
            z-index: 1;
            font-size: 1.45rem;
            font-weight: 800;
            text-align: center;
            margin-top: 8px;
            letter-spacing: -0.02em;
            color: #F8FAFC;
        }

        .hero-meta {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 6px 0 28px;
        }

        .role-pill {
            background: rgba(79, 70, 229, 0.2);
            border: 1px solid rgba(79, 70, 229, 0.4);
            border-radius: 20px;
            padding: 3px 12px;
            font-size: .78rem;
            font-weight: 600;
            color: #a5b4fc;
        }

        .email-text {
            color: #475569;
            font-size: .82rem;
        }

        /* ── FORM ── */
        .card-body {
            padding: 32px 36px 36px;
        }

        .section-label {
            font-size: .72rem;
            font-weight: 700;
            color: #4F46E5;
            text-transform: uppercase;
            letter-spacing: .1em;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-label::after {
            content: '';
            flex: 1;
            height: 1px;
            background: rgba(79, 70, 229, 0.2);
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-label {
            display: block;
            font-size: .82rem;
            font-weight: 500;
            color: var(--muted);
            margin-bottom: 6px;
        }

        .form-input {
            width: 100%;
            padding: 11px 14px;
            border-radius: 10px;
            border: 1px solid var(--border);
            background: rgba(0, 0, 0, 0.25);
            color: var(--text);
            font-size: .92rem;
            outline: none;
            transition: border-color .2s, box-shadow .2s;
            font-family: 'Inter', sans-serif;
        }

        .form-input:focus {
            border-color: #4F46E5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.18);
        }

        .form-input::placeholder {
            color: #334155;
        }

        .pass-wrap {
            position: relative;
        }

        .pass-wrap .form-input {
            padding-right: 44px;
        }

        .pass-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #475569;
            cursor: pointer;
            font-size: 1.1rem;
            padding: 4px;
            transition: color .2s;
        }

        .pass-toggle:hover {
            color: #94a3b8;
        }

        /* ── SEPARADOR ── */
        .divider {
            border: none;
            border-top: 1px solid var(--border);
            margin: 24px 0;
        }

        /* ── BOTÓN GUARDAR ── */
        .btn-save {
            width: 100%;
            padding: 14px;
            border-radius: 12px;
            border: none;
            background: linear-gradient(135deg, #4F46E5, #7C3AED);
            color: #fff;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: opacity .2s, transform .15s, box-shadow .2s;
            box-shadow: 0 4px 20px rgba(79, 70, 229, 0.35);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-save:hover {
            opacity: .9;
            transform: translateY(-1px);
            box-shadow: 0 8px 28px rgba(79, 70, 229, 0.45);
        }

        .btn-save:disabled {
            opacity: .5;
            cursor: not-allowed;
            transform: none;
        }

        /* ── TOAST ── */
        .toast {
            position: fixed;
            bottom: 28px;
            right: 28px;
            background: rgba(30, 41, 59, .97);
            border: 1px solid rgba(255, 255, 255, .1);
            border-radius: 14px;
            padding: 14px 22px;
            box-shadow: 0 12px 40px rgba(0, 0, 0, .5);
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: .88rem;
            font-weight: 500;
            transform: translateY(80px);
            opacity: 0;
            transition: all .35s cubic-bezier(.16, 1, .3, 1);
            z-index: 500;
            max-width: 340px;
        }

        .toast.show {
            transform: translateY(0);
            opacity: 1;
        }

        .toast.success {
            border-left: 4px solid #10b981;
        }

        .toast.error {
            border-left: 4px solid #ef4444;
        }

        /* ── SPINNER ── */
        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .spinner {
            width: 18px;
            height: 18px;
            border: 2px solid rgba(255, 255, 255, .3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin .7s linear infinite;
            display: none;
        }
    </style>
</head>

<body>
    <?php include __DIR__ . '/components/sidebar.php'; ?>

    <div class="main-content">
        <header class="topbar">
            <div class="topbar-left">
                <button id="btnHamburger"
                    style="background:none; border:none; color:#f1f5f9; cursor:pointer; font-size:1.5rem; display:flex;">☰</button>
                <h1>👤 <span>Usuario</span></h1>
            </div>
            <a class="btn-back" href="dashboard">← Dashboard</a>
        </header>

        <div class="page-body">
            <div class="account-card">

                <!-- HERO -->
                <div class="card-hero">
                    <div class="avatar-wrap">
                        <div class="avatar-ring" id="avatarRing" onclick="document.getElementById('fotoInput').click()"
                            title="Cambiar foto">
                            <div class="avatar-inner" id="avatarInner">
                                <?php if (!empty($usuario['foto_perfil'])): ?>
                                <img id="avatarImg" src="<?= htmlspecialchars($usuario['foto_perfil'])?>" alt="Foto">
                                <?php
else: ?>
                                <div class="avatar-initial" id="avatarInitial">
                                    <?= strtoupper(substr($usuario['nombre'], 0, 1))?>
                                </div>
                                <?php
endif; ?>
                            </div>
                            <div class="avatar-overlay">📷</div>
                        </div>
                        <span class="avatar-hint">Clic para cambiar foto</span>
                        <input type="file" id="fotoInput" accept="image/*" style="display:none">
                    </div>

                    <div class="hero-name" id="heroName">
                        <?= htmlspecialchars($usuario['nombre'])?>
                    </div>
                    <div class="hero-meta">
                        <span class="role-pill">
                            <?= htmlspecialchars($usuario['rol_nombre'])?>
                        </span>
                        <span class="email-text">
                            <?= htmlspecialchars($usuario['email'])?>
                        </span>
                    </div>
                </div>

                <!-- BODY -->
                <div class="card-body">

                    <!-- NOMBRE -->
                    <div class="section-label">✏️ Nombre de visualización</div>
                    <div class="form-group">
                        <label class="form-label" for="inputNombre">Nombre</label>
                        <input type="text" id="inputNombre" class="form-input"
                            value="<?= htmlspecialchars($usuario['nombre'])?>" placeholder="Tu nombre completo"
                            maxlength="50">
                    </div>

                    <hr class="divider">

                    <!-- CONTRASEÑA -->
                    <div class="section-label">🔒 Seguridad</div>
                    <div class="form-group">
                        <label class="form-label" for="inputPass">Nueva contraseña</label>
                        <div class="pass-wrap">
                            <input type="password" id="inputPass" class="form-input"
                                placeholder="Dejar vacío para no cambiar">
                            <button type="button" class="pass-toggle" onclick="togglePass('inputPass', this)"
                                title="Ver / Ocultar">👁</button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="inputPassConf">Confirmar contraseña</label>
                        <div class="pass-wrap">
                            <input type="password" id="inputPassConf" class="form-input"
                                placeholder="Repetir nueva contraseña">
                            <button type="button" class="pass-toggle" onclick="togglePass('inputPassConf', this)"
                                title="Ver / Ocultar">👁</button>
                        </div>
                    </div>

                    <hr class="divider">

                    <!-- FOTO INFO -->
                    <div class="section-label">🖼️ Foto de perfil</div>
                    <div
                        style="background:rgba(79,70,229,0.06); border:1px solid rgba(79,70,229,0.15); border-radius:10px; padding:12px 16px; margin-bottom:20px; font-size:.83rem; color:#94a3b8; line-height:1.6;">
                        Haz clic en tu avatar para seleccionar una nueva imagen.<br>
                        <span style="color:#64748b; font-size:.78rem;">Formatos: JPG, PNG, GIF, WEBP · Máximo 3
                            MB</span>
                    </div>

                    <!-- BOTÓN -->
                    <button class="btn-save" id="btnGuardar" onclick="guardar()">
                        <span class="spinner" id="spinnerSave"></span>
                        <span id="btnText">💾 Guardar Cambios</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- TOAST -->
    <div class="toast" id="toast">
        <span id="toastIcon">✅</span>
        <span id="toastMsg">OK</span>
    </div>

    <script>
        const sidebarBasePath = window.location.pathname.replace(/\/mi-cuenta\/?$/i, '');
        const originalNombre = <?= json_encode($usuario['nombre'])?>;

        /* ── TOAST ── */
        function showToast(msg, type = 'success') {
            const t = document.getElementById('toast');
            document.getElementById('toastMsg').textContent = msg;
            document.getElementById('toastIcon').textContent = type === 'success' ? '✅' : '❌';
            t.className = 'toast show ' + type;
            setTimeout(() => t.classList.remove('show'), 4000);
        }

        /* ── TOGGLE CONTRASEÑA ── */
        function togglePass(inputId, btn) {
            const el = document.getElementById(inputId);
            const visible = el.type === 'text';
            el.type = visible ? 'password' : 'text';
            btn.textContent = visible ? '👁' : '🙈';
        }

        /* ── PREVIEW FOTO ── */
        document.getElementById('fotoInput').addEventListener('change', function () {
            const file = this.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = e => {
                document.getElementById('avatarInner').innerHTML =
                    '<img id="avatarImg" src="' + e.target.result + '" alt="Preview" style="width:100%;height:100%;object-fit:cover;">';
            };
            reader.readAsDataURL(file);
        });

        /* ── ACTUALIZAR NOMBRE EN HERO ── */
        document.getElementById('inputNombre').addEventListener('input', function () {
            const v = this.value.trim();
            document.getElementById('heroName').textContent = v || originalNombre;
        });

        /* ── GUARDAR ── */
        async function guardar() {
            const nombre = document.getElementById('inputNombre').value.trim();
            const pw = document.getElementById('inputPass').value;
            const pwc = document.getElementById('inputPassConf').value;
            const foto = document.getElementById('fotoInput').files[0];

            // validaciones
            if (!nombre) { showToast('El nombre no puede estar vacío.', 'error'); return; }
            if (pw && pw !== pwc) { showToast('Las contraseñas no coinciden.', 'error'); return; }
            if (pw && pw.length < 6) { showToast('La contraseña debe tener al menos 6 caracteres.', 'error'); return; }

            const nombreCambiado = nombre !== originalNombre;
            if (!nombreCambiado && !pw && !foto) {
                showToast('No hay cambios que guardar.', 'error');
                return;
            }

            // UI loading
            const btn = document.getElementById('btnGuardar');
            const spinner = document.getElementById('spinnerSave');
            const btnTxt = document.getElementById('btnText');
            btn.disabled = true;
            spinner.style.display = 'block';
            btnTxt.textContent = 'Guardando…';

            const formData = new FormData();
            if (nombre) formData.append('nombre', nombre);
            if (pw) formData.append('nueva_password', pw);
            if (foto) formData.append('foto', foto);
            formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]').content);

            try {
                const req = await fetch(sidebarBasePath + '/api/perfil/actualizar', {
                    method: 'POST',
                    body: formData
                });
                const res = await req.json();

                if (res.status === 'success') {
                    showToast('✓ ' + res.message);
                    // Actualizar avatar del sidebar si cambió foto
                    if (res.data && res.data.foto_url) {
                        const sAvatar = document.getElementById('sidebarAvatar');
                        if (sAvatar) {
                            sAvatar.innerHTML = '<img src="' + res.data.foto_url + '?t=' + Date.now() + '" alt="Foto" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">';
                        }
                    }
                    // Actualizar nombre en sidebar
                    if (nombreCambiado && res.data && res.data.nombre_actualizado) {
                        const sName = document.querySelector('.sidebar .user-details h4');
                        if (sName) sName.textContent = nombre;
                        document.title = 'Usuario – ' + nombre;
                    }
                    // Limpiar campos de contraseña
                    document.getElementById('inputPass').value = '';
                    document.getElementById('inputPassConf').value = '';
                    document.getElementById('fotoInput').value = '';
                } else {
                    showToast('✗ ' + (res.message || 'Error al guardar.'), 'error');
                }
            } catch (e) {
                showToast('✗ Error de conexión: ' + e.message, 'error');
            }

            btn.disabled = false;
            spinner.style.display = 'none';
            btnTxt.textContent = '💾 Guardar Cambios';
        }
    </script>
</body>

</html>