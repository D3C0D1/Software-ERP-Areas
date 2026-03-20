<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ./login');
    exit;
}
if (($_SESSION['role'] ?? '') !== 'Admin') {
    header('Location: ./dashboard');
    exit;
}

use App\Middlewares\CsrfMiddleware;

$csrfToken = CsrfMiddleware::generateToken();

$db = \Config\Database::getInstance();
try {
    $rows = $db->query("SELECT clave, valor FROM configuracion")->fetchAll(\PDO::FETCH_KEY_PAIR);
}
catch (\Exception $e) {
    $rows = [];
}

$empresaNombre = $rows['empresa_nombre'] ?? 'Banner';
$empresaLogo = $rows['empresa_logo'] ?? '';
$onurixId = $rows['onurix_api_id'] ?? '';
$onurixKey = $rows['onurix_api_key'] ?? '';
$smsCrear = $rows['sms_crear'] ?? 'Hola {nombre}, tu pedido ha sido creado. Gracias por confiar en {empresa}.';
$smsFinalizar = $rows['sms_finalizar'] ?? 'Hola {nombre}, su pedido ha sido terminado, ya lo puede recoger en {empresa}.';

// Iconos personalizados del menú
$iconMenuItems = [
    'dashboard' => ['clave' => 'icon_dashboard', 'label' => 'Dashboard'],
    'recepcion' => ['clave' => 'icon_recepcion', 'label' => 'Recepción'],
    'reportes' => ['clave' => 'icon_reportes', 'label' => 'Reportes / Auditoría'],
    'reportes_pedidos' => ['clave' => 'icon_reportes_pedidos', 'label' => 'Reportes Pedidos'],
    'usuarios' => ['clave' => 'icon_usuarios', 'label' => 'Usuarios y Accesos'],
    'configuracion' => ['clave' => 'icon_configuracion', 'label' => 'Configuración'],
];

$scriptName = dirname($_SERVER['SCRIPT_NAME']);
$phpBasePath = rtrim($scriptName, '/\\');

$fondoLogin = $rows['fondo_login'] ?? ($phpBasePath . '/img/LEON.jpg');
$fondoDashboard = $rows['fondo_dashboard'] ?? ($phpBasePath . '/img/LEON.jpg');

// Sonidos – leídos desde BD
$sonidoHabilitado = $rows['sonido_habilitado'] ?? '1';
$sonidoTema = $rows['sonido_tema'] ?? 'cristal';

// Notificaciones al Finalizar
$smsFinEnabled = $rows['sms_fin_enabled'] ?? '1';
$smsFinCheckedDefault = $rows['sms_fin_checked_default'] ?? '1';
$waFinEnabled = $rows['wa_fin_enabled'] ?? '1';
$waFinCheckedDefault = $rows['wa_fin_checked_default'] ?? '1';

// Notificaciones al Crear
$smsCrearEnabled = $rows['sms_crear_enabled'] ?? '1';
$smsCrearCheckedDefault = $rows['sms_crear_checked_default'] ?? '1';
$waCrearEnabled = $rows['wa_crear_enabled'] ?? '1';
$waCrearCheckedDefault = $rows['wa_crear_checked_default'] ?? '1';

// Notificaciones Internas (Jefe y Supervisor)
$telefonoJefe = $rows['telefono_jefe'] ?? '';
$telefonoSupervisor = $rows['telefono_supervisor'] ?? '';

try {

    $areas = $db->query("SELECT id, nombre, icono FROM areas ORDER BY orden ASC")->fetchAll(PDO::FETCH_ASSOC);
}
catch (\PDOException $e) {
    $areas = [];
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <meta name="csrf-token" content="<?= $csrfToken?>">
    <title>
        <?= htmlspecialchars($empresaNombre)?> - Configuracion
    </title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --bg: #0f172a;
            --surface: rgba(30, 41, 59, .8);
            --border: rgba(255, 255, 255, .09);
            --primary: #6366f1;
            --text: #f1f5f9;
            --muted: #94a3b8;
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
            background-image: radial-gradient(at 10% 15%, rgba(99, 102, 241, .1) 0, transparent 55%);
        }

        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
        }

        .topbar {
            height: 64px;
            padding: 0 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(15, 23, 42, .6);
            border-bottom: 1px solid var(--border);
            backdrop-filter: blur(12px);
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .topbar h1 {
            font-size: 1.05rem;
            font-weight: 700;
        }

        .topbar h1 span {
            color: var(--primary);
        }

        .page-body {
            padding: 36px;
            max-width: 860px;
            width: 100%;
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 800;
            margin-bottom: 4px;
            background: linear-gradient(135deg, #f1f5f9, #a5b4fc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .page-sub {
            color: var(--muted);
            font-size: .88rem;
            margin-bottom: 32px;
        }

        .section-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 28px;
            margin-bottom: 24px;
        }

        .section-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 22px;
            padding-bottom: 14px;
            border-bottom: 1px solid var(--border);
        }

        .section-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: rgba(99, 102, 241, .15);
            border: 1px solid rgba(99, 102, 241, .3);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .section-title {
            font-size: 1rem;
            font-weight: 700;
        }

        .section-sub {
            font-size: .8rem;
            color: var(--muted);
            margin-top: 2px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-label {
            display: block;
            font-size: .82rem;
            font-weight: 600;
            color: var(--muted);
            margin-bottom: 7px;
            letter-spacing: .02em;
        }

        .form-input {
            width: 100%;
            background: rgba(0, 0, 0, .25);
            border: 1px solid var(--border);
            color: var(--text);
            border-radius: 10px;
            padding: 11px 14px;
            font-size: .9rem;
            transition: border-color .2s;
            outline: none;
        }

        .form-input:focus {
            border-color: var(--primary);
        }

        .form-hint {
            font-size: .76rem;
            color: var(--muted);
            margin-top: 5px;
        }

        .divider {
            border: none;
            border-top: 1px solid var(--border);
            margin: 20px 0;
        }

        .logo-wrap {
            display: flex;
            align-items: center;
            gap: 18px;
            margin-bottom: 14px;
        }

        .logo-preview {
            width: 80px;
            height: 80px;
            border-radius: 14px;
            background: rgba(0, 0, 0, .3);
            border: 2px dashed var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            overflow: hidden;
            flex-shrink: 0;
        }

        .logo-preview img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .logo-actions {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 10px;
            font-size: .85rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            transition: opacity .2s, transform .1s;
        }

        .btn:hover {
            opacity: .88;
            transform: translateY(-1px);
        }

        .btn:active {
            transform: translateY(0);
        }

        .btn-primary {
            background: var(--primary);
            color: #fff;
        }

        .btn-success {
            background: #10b981;
            color: #fff;
        }

        .btn-warning {
            background: #f59e0b;
            color: #fff;
        }

        .btn-ghost {
            background: rgba(255, 255, 255, .07);
            color: var(--text);
            border: 1px solid var(--border);
        }

        .btn-danger-sm {
            background: rgba(239, 68, 68, .1);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, .2);
            padding: 7px 14px;
            font-size: .78rem;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
        }

        .btn-sm {
            padding: 7px 14px;
            font-size: .78rem;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: .78rem;
            font-weight: 600;
        }

        .status-badge.ok {
            background: rgba(16, 185, 129, .15);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, .3);
        }

        .status-badge.warn {
            background: rgba(245, 158, 11, .15);
            color: #fbbf24;
            border: 1px solid rgba(245, 158, 11, .3);
        }

        .api-card {
            background: rgba(0, 0, 0, .2);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 18px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
        }

        .api-info {
            flex: 1;
        }

        .api-name {
            font-size: .95rem;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .api-desc {
            font-size: .8rem;
            color: var(--muted);
        }

        .api-key-preview {
            font-size: .75rem;
            color: var(--muted);
            font-family: monospace;
            background: rgba(0, 0, 0, .3);
            padding: 2px 8px;
            border-radius: 6px;
            margin-top: 6px;
            display: inline-block;
        }

        .var-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 18px;
            align-items: center;
        }

        .var-chip {
            cursor: grab;
            background: rgba(99, 102, 241, .15);
            border: 1px solid rgba(99, 102, 241, .35);
            color: #a5b4fc;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: .76rem;
            font-weight: 700;
            user-select: none;
            transition: background .2s, transform .15s;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .var-chip:hover {
            background: rgba(99, 102, 241, .3);
            transform: scale(1.05);
        }

        .var-chip:active {
            cursor: grabbing;
            transform: scale(.95);
        }

        .tpl-area {
            border: 2px dashed transparent;
            transition: border-color .2s, background .2s;
            border-radius: 10px;
        }

        .tpl-area.drag-over {
            border-color: #6366f1;
            background: rgba(99, 102, 241, .08);
        }

        .preview-box {
            background: rgba(0, 0, 0, .2);
            border: 1px solid rgba(255, 255, 255, .06);
            border-radius: 10px;
            padding: 14px 16px;
            margin-bottom: 16px;
        }

        .preview-label {
            font-size: .72rem;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .preview-label.blue {
            color: #6366f1;
        }

        .preview-label.green {
            color: #10b981;
        }

        .preview-text {
            font-size: .85rem;
            color: #cbd5e1;
            line-height: 1.6;
        }

        .modal-ovl {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .75);
            z-index: 1000;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .modal-ovl.open {
            display: flex;
        }

        .modal-box {
            background: #1e293b;
            border: 1px solid rgba(255, 255, 255, .1);
            border-radius: 18px;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 30px 80px rgba(0, 0, 0, .5);
            animation: fadeUp .22s ease;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(16px);
            }
        }

        .modal-head {
            padding: 22px 26px 16px;
            border-bottom: 1px solid rgba(255, 255, 255, .07);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-head h2 {
            font-size: 1.05rem;
            font-weight: 700;
        }

        .modal-close {
            background: none;
            border: none;
            color: var(--muted);
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0 4px;
        }

        .modal-close:hover {
            color: var(--text);
        }

        .modal-body {
            padding: 22px 26px;
        }

        .modal-footer {
            padding: 14px 26px 22px;
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .field-icon {
            position: relative;
        }

        .field-icon input {
            padding-right: 40px;
        }

        .toggle-eye {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--muted);
            cursor: pointer;
            font-size: 1rem;
        }

        .success-modal-box {
            background: linear-gradient(135deg, #0f2027, #1a3a2f);
            border: 1px solid rgba(16, 185, 129, .3);
            border-radius: 22px;
            width: 100%;
            max-width: 380px;
            text-align: center;
            padding: 44px 36px;
            box-shadow: 0 30px 80px rgba(0, 0, 0, .6);
            animation: fadeUp .3s ease;
        }

        .success-icon {
            font-size: 4rem;
            margin-bottom: 16px;
            display: block;
            animation: pop .4s cubic-bezier(.175, .885, .32, 1.275) forwards;
        }

        @keyframes pop {
            from {
                transform: scale(0);
            }

            to {
                transform: scale(1);
            }
        }

        .success-title {
            font-size: 1.3rem;
            font-weight: 800;
            color: #34d399;
            margin-bottom: 10px;
        }

        .success-sub {
            color: #94a3b8;
            font-size: .88rem;
            margin-bottom: 28px;
            line-height: 1.6;
        }

        .toast {
            position: fixed;
            bottom: 28px;
            right: 28px;
            background: #1e293b;
            border: 1px solid rgba(255, 255, 255, .1);
            border-left: 4px solid var(--primary);
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
        }

        .toast.show {
            transform: translateY(0);
            opacity: 1;
        }

        .toast.success {
            border-left-color: #10b981;
        }

        .toast.error {
            border-left-color: #ef4444;
        }
    </style>
</head>

<body>
    <?php include __DIR__ . '/components/sidebar.php'; ?>
    <div class="main-content">
        <header class="topbar">
            <h1>&#9881;&#65039; <span>Configuracion</span> del Sistema</h1>
        </header>
        <!-- MAIN CONTENT PANEL -->
        <main class="content-panel">
            <!-- ENLACE A CONFIGURACIÓN AVANZADA -->
            <div class="section-card"
                style="display:flex; justify-content:space-between; align-items:center; background:rgba(99,102,241,.05); border-color:#818cf8; margin-bottom: 20px;">
                <div>
                    <div class="section-title" style="color:#4f46e5; font-size:1.1rem;">Configuración Avanzada</div>
                    <div class="section-sub" style="margin-top:5px;">Accede a opciones de desarrollo, plantillas SMS,
                        credenciales Onurix y limpieza de base de datos.</div>
                </div>
                <a href="<?= $phpBasePath?>/configuracion_avanzada" class="btn btn-primary"
                    style="text-decoration:none;">
                    &#9881;&#65039; Ir a Configuraciones Avanzadas
                </a>
            </div>

            <!-- IDENTIDAD DE LA EMPRESA -->
            <div class="section-card">
                <div class="section-header">
                    <div class="section-icon" style="background:rgba(99,102,241,.15);border-color:rgba(99,102,241,.3);">
                        &#127970;</div>
                    <div>
                        <div class="section-title" style="color:#6366f1;">Identidad de la Empresa</div>
                        <div class="section-sub">Personaliza los datos básicos y logo de tu negocio.</div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Logotipo Secundario (Clear)</label>
                    <div class="logo-wrap">
                        <div class="logo-preview" id="logoPreview">
                            <?php if ($empresaLogo): ?>
                            <img src="<?= htmlspecialchars($empresaLogo)?>">
                            <?php
else: ?>
                            Sin Logo
                            <?php
endif; ?>
                        </div>
                        <div class="logo-actions">
                            <label class="btn btn-ghost btn-sm" style="cursor:pointer;">&#128193; Explorar<input
                                    type="file" id="logoInput" accept="image/*" style="display:none;"
                                    onchange="previewLogo(this)"></label>
                            <button class="btn-danger-sm" onclick="quitarLogo()">&#128465; Quitar</button>
                        </div>
                    </div>
                    <p class="form-hint">JPG, PNG, SVG. Max 2 MB.</p>
                </div>
                <hr class="divider">
                <div class="form-group">
                    <label class="form-label" for="inputEmpresa">Nombre de la Empresa</label>
                    <input type="text" id="inputEmpresa" class="form-input"
                        value="<?= htmlspecialchars($empresaNombre)?>" placeholder="Ej: Banner S.A."
                        oninput="renderPreviews()">
                    <p class="form-hint">Aparece en sidebar, login y SMS.</p>
                </div>
                <button class="btn btn-primary" onclick="guardarEmpresa()">&#128190; Guardar Empresa</button>
            </div>

            <!-- NOTIFICACIONES (CREAR PEDIDO) -->
            <div class="section-card">
                <div class="section-header">
                    <div class="section-icon" style="background:rgba(59,130,246,.15);border-color:rgba(59,130,246,.3);">
                        &#128222;</div>
                    <div>
                        <div class="section-title" style="color:#3b82f6;">Notificaciones (Crear Pedido)</div>
                        <div class="section-sub">Configura si se permiten enviar mensajes al crear un pedido y sus
                            estados por defecto.</div>
                    </div>
                </div>

                <div class="form-group"
                    style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom: 20px; background: rgba(0,0,0,0.2); padding: 15px; border-radius: 10px;">
                    <div>
                        <label class="form-label" style="font-weight:700; font-size: 0.95rem; color:#f1f5f9;">SMS via
                            Onurix</label>
                        <p class="form-hint" style="margin-top: 5px;">Habilita el envío de SMS tradicional.</p>
                    </div>
                    <div style="display:flex; flex-direction:column; gap:8px;">
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                            <input type="checkbox" id="chkSmsCrearEnabled" <?=$smsCrearEnabled=='1' ? 'checked' : '' ?>>
                            <span style="font-size: 0.85rem;">Activar funcionalidad de envíos de SMS</span>
                        </label>
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                            <input type="checkbox" id="chkSmsCrearDefault" <?=$smsCrearCheckedDefault=='1' ? 'checked'
                                : '' ?>>
                            <span style="font-size: 0.85rem;">Checkbox marcado de forma predeterminada</span>
                        </label>
                    </div>
                </div>

                <div class="form-group"
                    style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom: 20px; background: rgba(0,0,0,0.2); padding: 15px; border-radius: 10px;">
                    <div>
                        <label class="form-label" style="font-weight:700; font-size: 0.95rem; color:#22c55e;">Mensajes
                            por WhatsApp</label>
                        <p class="form-hint" style="margin-top: 5px;">Habilita el envío de mensaje a WA.</p>
                    </div>
                    <div style="display:flex; flex-direction:column; gap:8px;">
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                            <input type="checkbox" id="chkWaCrearEnabled" <?=$waCrearEnabled=='1' ? 'checked' : '' ?>>
                            <span style="font-size: 0.85rem;">Activar funcionalidad de envíos de WhatsApp</span>
                        </label>
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                            <input type="checkbox" id="chkWaCrearDefault" <?=$waCrearCheckedDefault=='1' ? 'checked'
                                : '' ?>>
                            <span style="font-size: 0.85rem;">Checkbox marcado de forma predeterminada</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- NOTIFICACIONES (FINALIZAR PEDIDO) -->
            <div class="section-card">
                <div class="section-header">
                    <div class="section-icon" style="background:rgba(16,185,129,.15);border-color:rgba(16,185,129,.3);">
                        &#128222;</div>
                    <div>
                        <div class="section-title" style="color:#10b981;">Notificaciones (Finalizar Pedido)</div>
                        <div class="section-sub">Configura si se permiten enviar mensajes y si están marcados por
                            defecto.</div>
                    </div>
                </div>

                <div class="form-group"
                    style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom: 20px; background: rgba(0,0,0,0.2); padding: 15px; border-radius: 10px;">
                    <div>
                        <label class="form-label" style="font-weight:700; font-size: 0.95rem; color:#f1f5f9;">SMS via
                            Onurix</label>
                        <p class="form-hint" style="margin-top: 5px;">Habilita el envío de SMS tradicional.</p>
                    </div>
                    <div style="display:flex; flex-direction:column; gap:8px;">
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                            <input type="checkbox" id="chkSmsFinEnabled" <?=$smsFinEnabled=='1' ? 'checked' : '' ?>>
                            <span style="font-size: 0.85rem;">Activar funcionalidad de envíos de SMS</span>
                        </label>
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                            <input type="checkbox" id="chkSmsFinDefault" <?=$smsFinCheckedDefault=='1' ? 'checked' : ''
                                ?>>
                            <span style="font-size: 0.85rem;">Checkbox marcado de forma predeterminada</span>
                        </label>
                    </div>
                </div>

                <div class="form-group"
                    style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom: 20px; background: rgba(0,0,0,0.2); padding: 15px; border-radius: 10px;">
                    <div>
                        <label class="form-label" style="font-weight:700; font-size: 0.95rem; color:#22c55e;">Mensajes
                            por WhatsApp</label>
                        <p class="form-hint" style="margin-top: 5px;">Habilita el botón de mensaje a WA.</p>
                    </div>
                    <div style="display:flex; flex-direction:column; gap:8px;">
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                            <input type="checkbox" id="chkWaFinEnabled" <?=$waFinEnabled=='1' ? 'checked' : '' ?>>
                            <span style="font-size: 0.85rem;">Activar funcionalidad de envíos de WhatsApp</span>
                        </label>
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                            <input type="checkbox" id="chkWaFinDefault" <?=$waFinCheckedDefault=='1' ? 'checked' : ''
                                ?>>
                            <span style="font-size: 0.85rem;">Checkbox marcado de forma predeterminada</span>
                        </label>
                    </div>
                </div>

                <button class="btn btn-success" onclick="guardarNotificaciones()">&#128190; Guardar
                    Notificaciones (Ambas áreas)</button>
            </div>

            <!-- NOTIFICACIONES INTERNAS -->
            <div class="section-card">
                <div class="section-header">
                    <div class="section-icon" style="background:rgba(245,158,11,.15);border-color:rgba(245,158,11,.3);">
                        &#128100;</div>
                    <div>
                        <div class="section-title" style="color:#f59e0b;">Notificaciones Internas</div>
                        <div class="section-sub">Configura los números telefónicos para enviar alertas rápidas de pedidos al Jefe y Supervisor.</div>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label class="form-label" for="telefonoJefe" style="font-weight:700; color:#f1f5f9;">Teléfono Jefe</label>
                    <input type="text" id="telefonoJefe" class="form-input" value="<?= htmlspecialchars($telefonoJefe) ?>" placeholder="Ej: +573001234567">
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label class="form-label" for="telefonoSupervisor" style="font-weight:700; color:#f1f5f9;">Teléfono Supervisor</label>
                    <input type="text" id="telefonoSupervisor" class="form-input" value="<?= htmlspecialchars($telefonoSupervisor) ?>" placeholder="Ej: +573007654321">
                </div>

                <button class="btn btn-warning" onclick="guardarNotificacionesInternas()">&#128190; Guardar
                    Notificaciones Internas</button>
            </div>

            <!-- FONDOS DEL SISTEMA -->
            <div class="section-card">
                <div class="section-header">
                    <div class="section-icon" style="background:rgba(236,72,153,.15);border-color:rgba(236,72,153,.3);">
                        &#128444;</div>
                    <div>
                        <div class="section-title" style="color:#ec4899;">Fondos del Sistema</div>
                        <div class="section-sub">Personaliza el fondo de la pantalla de Login y del Dashboard.</div>
                    </div>
                </div>

                <!-- Campos lado a lado -->
                <div style="display:flex; gap:30px; flex-wrap:wrap; margin-bottom:20px;">
                    <div class="form-group" style="flex:1; min-width:250px;">
                        <label class="form-label" style="font-weight:600; color:#cbd5e1;">Fondo del Login</label>
                        <div class="logo-wrap" style="flex-direction:column; align-items:flex-start;">
                            <div class="logo-preview" id="fondoLoginPreview"
                                style="width:100%; height:140px; border-radius:10px; background:rgba(0,0,0,.3); border:2px dashed var(--border); overflow:hidden;">
                                <?php if ($fondoLogin): ?>
                                <img src="<?= htmlspecialchars($fondoLogin)?>"
                                    style="object-fit:cover; width:100%; height:100%; border-radius:8px;">
                                <?php
else: ?>
                                <div
                                    style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:0.8rem;">
                                    Sin Fondo</div>
                                <?php
endif; ?>
                            </div>
                            <div class="logo-actions"
                                style="flex-direction:row; background:rgba(255,255,255,.03); padding:8px 12px; border-radius:8px; border:1px solid rgba(255,255,255,.05); width:100%;">
                                <label class="btn btn-ghost btn-sm" style="cursor:pointer; flex:1; text-align:center;">
                                    &#128193; Explorar<input type="file" id="fondoLoginFile" accept="image/*"
                                        style="display:none;" onchange="previewFondo(this, 'fondoLoginPreview')">
                                </label>
                                <button class="btn-danger-sm" style="flex:1;"
                                    onclick="quitarFondo('fondoLoginPreview', 'fondoLoginFile')">&#128465;
                                    Quitar</button>
                            </div>
                        </div>
                    </div>

                    <div class="form-group" style="flex:1; min-width:250px;">
                        <label class="form-label" style="font-weight:600; color:#cbd5e1;">Fondo del Dashboard</label>
                        <div class="logo-wrap" style="flex-direction:column; align-items:flex-start;">
                            <div class="logo-preview" id="fondoDashboardPreview"
                                style="width:100%; height:140px; border-radius:10px; background:rgba(0,0,0,.3); border:2px dashed var(--border); overflow:hidden;">
                                <?php if ($fondoDashboard): ?>
                                <img src="<?= htmlspecialchars($fondoDashboard)?>"
                                    style="object-fit:cover; width:100%; height:100%; border-radius:8px;">
                                <?php
else: ?>
                                <div
                                    style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:0.8rem;">
                                    Sin Fondo</div>
                                <?php
endif; ?>
                            </div>
                            <div class="logo-actions"
                                style="flex-direction:row; background:rgba(255,255,255,.03); padding:8px 12px; border-radius:8px; border:1px solid rgba(255,255,255,.05); width:100%;">
                                <label class="btn btn-ghost btn-sm" style="cursor:pointer; flex:1; text-align:center;">
                                    &#128193; Explorar<input type="file" id="fondoDashboardFile" accept="image/*"
                                        style="display:none;" onchange="previewFondo(this, 'fondoDashboardPreview')">
                                </label>
                                <button class="btn-danger-sm" style="flex:1;"
                                    onclick="quitarFondo('fondoDashboardPreview', 'fondoDashboardFile')">&#128465;
                                    Quitar</button>
                            </div>
                        </div>
                    </div>
                </div>

                <button class="btn btn-primary" onclick="guardarFondos()"
                    style="background:#6366f1; border-color:#6366f1;">
                    &#128190; Guardar Fondos
                </button>
            </div>

            <!-- ICONOS DE ÁREAS DE TRABAJO -->
            <div class="section-card">
                <div class="section-header">
                    <div class="section-icon" style="background:rgba(16,185,129,.15);border-color:rgba(16,185,129,.3);">
                        &#128188;</div>
                    <div>
                        <div class="section-title" style="color:#10b981;">Íconos de Áreas de Trabajo</div>
                        <div class="section-sub">Personaliza un ícono independiente para cada una de tus áreas.</div>
                    </div>
                </div>

                <?php if (empty($areas)): ?>
                <div
                    style="padding:15px; background:rgba(255,255,255,.05); border-radius:10px; text-align:center; color:var(--muted); font-size:.9rem;">
                    No tienes áreas creadas. Dirígete a "Áreas y Workflow" para crearlas.
                </div>
                <?php
else: ?>
                <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(180px, 1fr)); gap:15px;">
                    <?php foreach ($areas as $area): ?>
                    <div
                        style="background:rgba(0,0,0,.2); border:1px solid var(--border); border-radius:12px; padding:15px; display:flex; flex-direction:column; align-items:center; gap:12px;">
                        <div id="areaPreviewContainer_<?= $area['id']?>"
                            style="width:50px; height:50px; border-radius:10px; background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.1); display:flex; align-items:center; justify-content:center; font-size:1.8rem;">
                            <?php if ($area['icono'] && (strpos($area['icono'], '<svg') !== false || strpos($area['icono'], '<img') !== false)): ?>
                            <?= $area['icono']?>
                            <?php
        else: ?>
                            <?= htmlspecialchars($area['icono'] ?: '🏭')?>
                            <?php
        endif; ?>
                        </div>
                        <div style="font-weight:600; font-size:.95rem; text-align:center; color:#e2e8f0;">
                            <?= htmlspecialchars($area['nombre'])?>
                        </div>
                        <button class="btn btn-ghost btn-sm"
                            style="width:100%; border-color:rgba(16,185,129,.3); color:#34d399;"
                            onclick="abrirModalIconosArea(<?= $area['id']?>)">Cambiar Ícono</button>
                    </div>
                    <?php
    endforeach; ?>
                </div>
                <?php
endif; ?>
            </div>



            <!-- ICONOS DEL MENÚ -->
            <div class="section-card">
                <div class="section-header">
                    <div class="section-icon" style="background:rgba(99,102,241,.15);border-color:rgba(99,102,241,.3);">
                        &#128444;</div>
                    <div>
                        <div class="section-title">Íconos del Menú Lateral</div>
                        <div class="section-sub">Selecciona una plantilla grupal o sube íconos propios por sección.
                        </div>
                    </div>
                </div>

                <!-- 4 GRUPOS DE PLANTILLAS CLICKEABLES -->
                <div style="margin-bottom:24px;">
                    <div
                        style="font-size:.78rem;color:var(--muted);margin-bottom:12px;font-weight:700;letter-spacing:.04em;">
                        APLICAR GRUPO — Da clic para previsualizar y aplicar a todos los ítems</div>
                    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px;">

                        <div class="icon-theme-card" onclick="aplicarTema('outline')"
                            style="cursor:pointer;border:2px solid rgba(99,102,241,.3);border-radius:12px;padding:14px;background:rgba(99,102,241,.06);transition:all .2s;"
                            onmouseover="this.style.borderColor='#6366f1'"
                            onmouseout="this.style.borderColor='rgba(99,102,241,.3)'">
                            <div style="font-size:.8rem;font-weight:700;color:#a5b4fc;margin-bottom:10px;">&#10022;
                                Outline / Feather</div>
                            <div style="display:flex;gap:7px;align-items:center;flex-wrap:wrap;">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#6366f1"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="3" width="7" height="7" />
                                    <rect x="14" y="3" width="7" height="7" />
                                    <rect x="14" y="14" width="7" height="7" />
                                    <rect x="3" y="14" width="7" height="7" />
                                </svg>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#6366f1"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M22 12h-4l-3 9L9 3l-3 9H2" />
                                </svg>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#6366f1"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                    <polyline points="14 2 14 8 20 8" />
                                    <line x1="16" y1="13" x2="8" y2="13" />
                                </svg>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#6366f1"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                                    <circle cx="9" cy="7" r="4" />
                                </svg>
                            </div>
                            <div style="font-size:.7rem;color:var(--muted);margin-top:8px;">Líneas finas elegantes</div>
                        </div>

                        <div class="icon-theme-card" onclick="aplicarTema('dash')"
                            style="cursor:pointer;border:2px solid rgba(16,185,129,.25);border-radius:12px;padding:14px;background:rgba(16,185,129,.05);transition:all .2s;"
                            onmouseover="this.style.borderColor='#10b981'"
                            onmouseout="this.style.borderColor='rgba(16,185,129,.25)'">
                            <div style="font-size:.8rem;font-weight:700;color:#34d399;margin-bottom:10px;">&#9632;
                                Dashicons (WordPress)</div>
                            <div style="display:flex;gap:7px;align-items:center;flex-wrap:wrap;">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="#10b981">
                                    <path d="M1 1h8v8H1V1zm10 0h8v8h-8V1zM1 11h8v8H1v-8zm10 0h8v8h-8v-8z" />
                                </svg>
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="#10b981">
                                    <path d="M2 3h16v2H2V3zm0 4h10v2H2V7zm0 4h16v2H2v-2zm0 4h10v2H2v-2z" />
                                </svg>
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="#10b981">
                                    <path
                                        d="M4 2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2zm1 4v8l3-3 2 3 2-3 3 3V6H5z" />
                                </svg>
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="#10b981">
                                    <path d="M10 1a5 5 0 1 1 0 10A5 5 0 0 1 10 1zm-8 17c0-3.3 3.6-6 8-6s8 2.7 8 6H2z" />
                                </svg>
                            </div>
                            <div style="font-size:.7rem;color:var(--muted);margin-top:8px;">Pixel-perfect estilo admin
                                WP</div>
                        </div>

                        <div class="icon-theme-card" onclick="aplicarTema('solid')"
                            style="cursor:pointer;border:2px solid rgba(245,158,11,.25);border-radius:12px;padding:14px;background:rgba(245,158,11,.05);transition:all .2s;"
                            onmouseover="this.style.borderColor='#f59e0b'"
                            onmouseout="this.style.borderColor='rgba(245,158,11,.25)'">
                            <div style="font-size:.8rem;font-weight:700;color:#fbbf24;margin-bottom:10px;">&#9724;
                                Sólido (Material)</div>
                            <div style="display:flex;gap:7px;align-items:center;flex-wrap:wrap;">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="#f59e0b">
                                    <path d="M3 3h8v8H3zm10 0h8v8h-8zM3 13h8v8H3zm10 0h8v8h-8z" />
                                </svg>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="#f59e0b">
                                    <path d="M4 6h16v2H4zm0 4h16v2H4zm0 4h10v2H4z" />
                                </svg>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="#f59e0b">
                                    <path
                                        d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm-1 1.5L18.5 9H13V3.5z" />
                                </svg>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="#f59e0b">
                                    <path
                                        d="M16 11c1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3 1.34 3 3 3zm-8 0c1.66 0 3-1.34 3-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z" />
                                </svg>
                            </div>
                            <div style="font-size:.7rem;color:var(--muted);margin-top:8px;">Relleno sólido, Material
                                Design</div>
                        </div>

                        <div class="icon-theme-card" onclick="aplicarTema('duo')"
                            style="cursor:pointer;border:2px solid rgba(244,114,182,.25);border-radius:12px;padding:14px;background:rgba(244,114,182,.05);transition:all .2s;"
                            onmouseover="this.style.borderColor='#f472b6'"
                            onmouseout="this.style.borderColor='rgba(244,114,182,.25)'">
                            <div style="font-size:.8rem;font-weight:700;color:#f9a8d4;margin-bottom:10px;">&#11835;
                                Duotone (Bicolor)</div>
                            <div style="display:flex;gap:7px;align-items:center;flex-wrap:wrap;">
                                <svg width="20" height="20" viewBox="0 0 24 24">
                                    <rect x="3" y="3" width="7" height="7" rx="1" fill="rgba(244,114,182,0.3)" />
                                    <rect x="14" y="3" width="7" height="7" rx="1" fill="rgba(244,114,182,0.3)" />
                                    <rect x="14" y="14" width="7" height="7" rx="1" fill="#f472b6" />
                                    <rect x="3" y="14" width="7" height="7" rx="1" fill="#f472b6" />
                                </svg>
                                <svg width="20" height="20" viewBox="0 0 24 24">
                                    <path d="M4 6h16v12H4z" fill="rgba(244,114,182,0.25)" />
                                    <rect x="2" y="4" width="20" height="4" rx="1" fill="#f472b6" />
                                </svg>
                                <svg width="20" height="20" viewBox="0 0 24 24">
                                    <path d="M6 4h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"
                                        fill="rgba(244,114,182,0.2)" />
                                    <path d="M8 9h8M8 13h5" stroke="#f472b6" stroke-width="2" stroke-linecap="round" />
                                </svg>
                                <svg width="20" height="20" viewBox="0 0 24 24">
                                    <circle cx="12" cy="8" r="4" fill="rgba(244,114,182,0.3)" />
                                    <path d="M4 20c0-3.3 3.6-6 8-6s8 2.7 8 6" stroke="#f472b6" stroke-width="2"
                                        stroke-linecap="round" fill="none" />
                                </svg>
                            </div>
                            <div style="font-size:.7rem;color:var(--muted);margin-top:8px;">Dos tonos, moderno con
                                profundidad</div>
                        </div>

                    </div>
                </div>

                <!-- GRID INDIVIDUAL -->
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(210px,1fr));gap:12px;"
                    id="iconGrid">
                    <?php foreach ($iconMenuItems as $key => $item): ?>
                    <?php $currentIconUrl = $rows[$item['clave']] ?? ''; ?>
                    <div style="background:rgba(0,0,0,.18);border:1px solid var(--border);border-radius:10px;padding:12px;display:flex;flex-direction:column;gap:8px;"
                        id="iconCard_<?= $key?>">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div id="iconPreview_<?= $key?>"
                                style="width:38px;height:38px;border-radius:9px;background:rgba(99,102,241,.1);border:1px solid rgba(99,102,241,.3);display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0;">
                                <?php if ($currentIconUrl): ?>
                                <img src="<?= htmlspecialchars($currentIconUrl)?>"
                                    style="width:22px;height:22px;object-fit:contain;">
                                <?php
    else: ?>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#6366f1"
                                    stroke-width="2">
                                    <rect x="3" y="3" width="18" height="18" rx="3" />
                                    <path d="M9 3v18M15 3v18M3 9h18M3 15h18" />
                                </svg>
                                <?php
    endif; ?>
                            </div>
                            <div>
                                <div style="font-size:.85rem;font-weight:600;">
                                    <?= htmlspecialchars($item['label'])?>
                                </div>
                                <div style="font-size:.7rem;color:var(--muted);" id="iconStatus_<?= $key?>">
                                    <?= $currentIconUrl ? '✅ Personalizado' : 'Por defecto (SVG)'?>
                                </div>
                            </div>
                        </div>
                        <label
                            style="background:rgba(99,102,241,.08);border:1px dashed rgba(99,102,241,.35);border-radius:8px;padding:7px;text-align:center;cursor:pointer;font-size:.78rem;color:#a5b4fc;">
                            &#128206; Subir ícono propio
                            <input type="file" accept="image/*" style="display:none;"
                                onchange="handleIconUpload(event, '<?= $key?>', '<?= $item['clave']?>')">
                        </label>
                        <?php if ($currentIconUrl): ?>
                        <button class="btn-quitar-icon" onclick="quitarIcono('<?= $key?>', '<?= $item['clave']?>')"
                            style="background:rgba(239,68,68,.07);border:1px solid rgba(239,68,68,.2);color:#f87171;border-radius:7px;padding:5px;font-size:.75rem;cursor:pointer;">&#x2715;
                            Restaurar por defecto</button>
                        <?php
    endif; ?>
                    </div>
                    <?php
endforeach; ?>
                </div>

                <!-- BOTÓN GUARDAR -->
                <div
                    style="margin-top:18px;padding-top:16px;border-top:1px solid var(--border);display:flex;align-items:center;gap:14px;flex-wrap:wrap;">
                    <button class="btn btn-primary" onclick="guardarIconos()">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />
                            <polyline points="17 21 17 13 7 13 7 21" />
                            <polyline points="7 3 7 8 15 8" />
                        </svg>
                        Guardar Íconos del Menú
                    </button>
                    <button class="btn btn-ghost btn-sm" onclick="resetearTodosIconos()">&#x2715; Quitar todos</button>
                    <span style="font-size:.78rem;color:var(--muted);" id="iconSaveStatus"></span>
                </div>

            </div>



            <!-- SONIDOS DEL SISTEMA -->
            <div class="section-card" id="soundCard" style="border-color:rgba(168,85,247,.25);">
                <div class="section-header">
                    <div class="section-icon" style="background:rgba(168,85,247,.15);border-color:rgba(168,85,247,.3);">
                        &#127925;</div>
                    <div>
                        <div class="section-title" style="color:#c084fc;">Sonidos del Sistema</div>
                        <div class="section-sub">Elige un tema sonoro o desactiva los sonidos completamente.</div>
                    </div>
                </div>

                <!-- Toggle habilitar -->
                <div class="form-group"
                    style="display:flex;align-items:center;gap:16px;background:rgba(168,85,247,.07);border:1px solid rgba(168,85,247,.2);border-radius:10px;padding:16px;margin-bottom:20px;">
                    <label
                        style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:0.95rem;font-weight:500;flex:1;">
                        <input type="checkbox" id="chkSoundEnabled"
                            style="width:20px;height:20px;cursor:pointer;accent-color:#a855f7;">
                        <span>Habilitar sonidos del sistema (login, crear pedido, mover, finalizar, eliminar)</span>
                    </label>
                    <span id="soundBadge" class="status-badge warn">&#9888;&#65039; Inactivo</span>
                </div>

                <!-- Selector de temas -->
                <div
                    style="font-size:.78rem;color:var(--muted);margin-bottom:12px;font-weight:700;letter-spacing:.04em;">
                    TEMA DE SONIDO &mdash; haz clic para escuchar y seleccionar</div>
                <div id="soundThemeGrid"
                    style="display:grid;grid-template-columns:repeat(auto-fill,minmax(165px,1fr));gap:12px;margin-bottom:22px;">
                    <!-- generado por JS -->
                </div>

                <div
                    style="background:rgba(168,85,247,.06);border:1px solid rgba(168,85,247,.15);border-radius:10px;padding:12px;font-size:.8rem;color:#c084fc;">
                    &#128161; Los cambios se guardan automáticamente en tu navegador (localStorage). No requiere
                    conexión al servidor.
                </div>
            </div>

    </div>
    </div>

    <!-- MODAL ÍCONOS DE ÁREA -->
    <div class="modal-ovl" id="modalIconosArea" onclick="if(event.target===this)cerrar('modalIconosArea')">
        <div class="modal-box" style="max-width:680px;">
            <div class="modal-head">
                <h2>&#128736; Ícono del Área</h2>
                <button class="modal-close" onclick="cerrar('modalIconosArea')">&times;</button>
            </div>
            <div class="modal-body">
                <p style="font-size:.85rem;color:var(--muted);margin-bottom:16px;">
                    Elige un ícono predefinido o sube tu propia imagen (PNG/SVG &lt; 100 KB):
                </p>
                <!-- Subida de ícono personalizado -->
                <label class="btn btn-ghost btn-sm"
                    style="cursor:pointer; display:inline-flex; gap:8px; margin-bottom:20px;">
                    &#128444; Subir imagen
                    <input type="file" accept="image/*,image/svg+xml" style="display:none;"
                        onchange="manejarSubidaIconoArea(event)">
                </label>
                <!-- Grid de íconos predefinidos -->
                <div id="gridPlantillasIconos"
                    style="display:grid; grid-template-columns:repeat(auto-fill,minmax(52px,1fr)); gap:10px; max-height:340px; overflow-y:auto; padding:4px;">
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL ONURIX -->
    <div class="modal-ovl" id="modalOnurix" onclick="if(event.target===this)cerrarOnurix()">
        <div class="modal-box">
            <div class="modal-head">
                <h2>&#128241; Credenciales Onurix</h2><button class="modal-close"
                    onclick="cerrarOnurix()">&times;</button>
            </div>
            <div class="modal-body">
                <p style="font-size:.85rem;color:var(--muted);margin-bottom:20px;">Encuentra tus datos en
                    <strong>app.onurix.com</strong>.
                </p>
                <div class="form-group">
                    <label class="form-label">ID de Cuenta</label>
                    <input type="text" id="inputOnurixId" class="form-input" placeholder="Ej: 7389"
                        value="<?= htmlspecialchars($onurixId)?>">
                </div>
                <div class="form-group">
                    <label class="form-label">API Key</label>
                    <div class="field-icon">
                        <input type="password" id="inputOnurixKey" class="form-input" placeholder="Token secreto..."
                            value="<?= htmlspecialchars($onurixKey)?>">
                        <button class="toggle-eye" onclick="toggleKey()" type="button">&#128065;</button>
                    </div>
                </div>
                <div
                    style="background:rgba(99,102,241,.08);border:1px solid rgba(99,102,241,.2);border-radius:10px;padding:12px;font-size:.8rem;color:#a5b4fc;">
                    &#128274; Se guarda de forma segura.</div>
            </div>
            <div class="modal-footer" style="display:flex; justify-content:space-between; width:100%;">
                <button class="btn btn-ghost" style="border:1px solid var(--border);color:#6366f1; flex:none;"
                    onclick="probarOnurix()">&#128246; Probar API</button>
                <div style="display:flex; gap:10px;">
                    <button class="btn btn-ghost" onclick="cerrarOnurix()">Cancelar</button>
                    <button class="btn btn-warning" onclick="guardarOnurix()">&#128190; Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL EXITO SMS -->
    <div class="modal-ovl" id="modalExito" onclick="if(event.target===this)cerrarExito()">
        <div class="success-modal-box">
            <span class="success-icon">&#9993;&#65039;</span>
            <div class="success-title">Plantillas Guardadas!</div>
            <p class="success-sub">Los mensajes SMS han sido actualizados correctamente.<br>Se usaran en el proximo
                envio automatico.</p>
            <button class="btn btn-success" onclick="cerrarExito()" style="width:100%;justify-content:center;">&#9989;
                Perfecto</button>
        </div>
    </div>

    <!-- TOAST -->
    <div class="toast" id="toastEl"><span id="toastMsg">OK</span></div>

    <script>
        var basePath = window.location.pathname.replace(/\/index\.php\/configuracion\/?/i, '/index.php').replace(/\/configuracion\/?$/i, '');

        function toast(msg, type) {
            var t = document.getElementById('toastEl');
            document.getElementById('toastMsg').textContent = msg;
            t.className = 'toast show ' + (type || 'success');
            setTimeout(function () { t.classList.remove('show'); }, 3500);
        }
        function abrir(id) { document.getElementById(id).classList.add('open'); }
        function cerrar(id) { document.getElementById(id).classList.remove('open'); }
        function abrirOnurix() { abrir('modalOnurix'); }
        function cerrarOnurix() { cerrar('modalOnurix'); }
        function cerrarExito() { cerrar('modalExito'); }

        var _logoData = null, _logoRemoved = false;
        function previewLogo(input) {
            if (!input.files || !input.files[0]) return;
            if (input.files[0].size > 2097152) { toast('El logo no puede superar 2 MB.', 'error'); return; }
            var reader = new FileReader();
            reader.onload = function (e) {
                _logoData = e.target.result; _logoRemoved = false;
                document.getElementById('logoPreview').innerHTML = '<img src="' + e.target.result + '" style="width:100%;height:100%;object-fit:contain;">';
            };
            reader.readAsDataURL(input.files[0]);
        }
        function quitarLogo() {
            _logoData = null; _logoRemoved = true;
            document.getElementById('logoPreview').innerHTML = '<span>&#127962;</span>';
            var logoInput = document.getElementById('logoInput') || document.getElementById('logoFile');
            if (logoInput) logoInput.value = '';
        }

        // ---------- FONDOS (con subida real de archivo, sin base64) ----------
        // Guardamos referencia al File object directamente
        var _fondoLoginFile = null, _fondoLoginNulo = false;
        var _fondoDashboardFile = null, _fondoDashboardNulo = false;

        function previewFondo(input, previewId) {
            if (!input.files || !input.files[0]) return;
            var file = input.files[0];
            if (file.size > 5 * 1024 * 1024) { toast('El fondo no puede superar 5 MB.', 'error'); input.value = ''; return; }

            // Guardamos el archivo para subirlo después
            if (previewId === 'fondoLoginPreview') {
                _fondoLoginFile = file; _fondoLoginNulo = false;
            } else {
                _fondoDashboardFile = file; _fondoDashboardNulo = false;
            }

            // Vista previa instantánea usando URL.createObjectURL (no base64, más rápido)
            var objectURL = URL.createObjectURL(file);
            var container = document.getElementById(previewId);
            container.innerHTML = '<img src="' + objectURL + '" style="width:100%;height:100%;object-fit:cover;border-radius:8px;">';
        }

        function quitarFondo(previewId, inputId) {
            document.getElementById(previewId).innerHTML = '<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:0.8rem;">Sin Fondo (se eliminará al guardar)</div>';
            document.getElementById(inputId).value = '';
            if (previewId === 'fondoLoginPreview') {
                _fondoLoginFile = null; _fondoLoginNulo = true;
            } else {
                _fondoDashboardFile = null; _fondoDashboardNulo = true;
            }
        }

        async function _subirUnFondo(clave, file, esNulo) {
            var fd = new FormData();
            fd.append('csrf_token', document.querySelector('meta[name="csrf-token"]').content);
            fd.append('clave', clave);
            if (esNulo) {
                fd.append('quitar', '1');
            } else if (file) {
                fd.append('imagen', file);
            } else {
                return null; // sin cambios
            }
            var r = await fetch(basePath + '/api/config/subir-fondo', { method: 'POST', body: fd });
            return await r.json();
        }

        async function guardarFondos() {
            var hayLogin = _fondoLoginFile || _fondoLoginNulo;
            var hayDash = _fondoDashboardFile || _fondoDashboardNulo;

            if (!hayLogin && !hayDash) {
                toast('Selecciona una imagen o usa "Quitar" antes de guardar.', 'error'); return;
            }

            toast('Guardando fondos...', 'success');
            var errores = [];

            try {
                if (hayLogin) {
                    var r1 = await _subirUnFondo('fondo_login', _fondoLoginFile, _fondoLoginNulo);
                    if (r1 && r1.status === 'success') {
                        // Actualizar preview con URL real del servidor
                        if (r1.url) {
                            document.getElementById('fondoLoginPreview').innerHTML =
                                '<img src="' + r1.url + '?t=' + Date.now() + '" style="width:100%;height:100%;object-fit:cover;border-radius:8px;">';
                        }
                        _fondoLoginFile = null; _fondoLoginNulo = false;
                    } else if (r1) {
                        errores.push('Login: ' + r1.message);
                    }
                }

                if (hayDash) {
                    var r2 = await _subirUnFondo('fondo_dashboard', _fondoDashboardFile, _fondoDashboardNulo);
                    if (r2 && r2.status === 'success') {
                        if (r2.url) {
                            document.getElementById('fondoDashboardPreview').innerHTML =
                                '<img src="' + r2.url + '?t=' + Date.now() + '" style="width:100%;height:100%;object-fit:cover;border-radius:8px;">';
                        }
                        _fondoDashboardFile = null; _fondoDashboardNulo = false;
                    } else if (r2) {
                        errores.push('Dashboard: ' + r2.message);
                    }
                }

                if (errores.length === 0) {
                    toast('✅ Fondos guardados correctamente.', 'success');
                } else {
                    toast('Error: ' + errores.join(' | '), 'error');
                }
            } catch (e) {
                toast('Error de red al guardar fondos: ' + e.message, 'error');
            }
        }


        async function guardarEmpresa() {
            var nombre = document.getElementById('inputEmpresa').value.trim();
            if (!nombre) { toast('El nombre no puede estar vacio.', 'error'); return; }
            var csrf = document.querySelector('meta[name="csrf-token"]').content;
            var payload = { csrf_token: csrf, empresa_nombre: nombre };
            if (_logoData) payload.empresa_logo = _logoData;
            if (_logoRemoved) payload.empresa_logo = '';
            try {
                var r = await fetch(basePath + '/api/config/guardar', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
                var res = await r.json();
                if (res.status === 'success') { toast('Empresa guardada correctamente.', 'success'); var b = document.querySelector('.sidebar .brand h2'); if (b) b.textContent = nombre; }
                else { toast('Error: ' + res.message, 'error'); }
            } catch (e) { toast('Error de red.', 'error'); }
        }

        async function guardarCredenciales() {
            var mostrar = document.getElementById('chkMostrarCredenciales').checked ? '1' : '0';
            var csrf = document.querySelector('meta[name="csrf-token"]').content;
            try {
                var r = await fetch(basePath + '/api/config/guardar', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ csrf_token: csrf, mostrar_credenciales: mostrar }) });
                var res = await r.json();
                if (res.status === 'success') toast('Preferencia guardada.', 'success');
                else toast('Error: ' + res.message, 'error');
            } catch (e) { toast('Error de red.', 'error'); }
        }

        async function guardarNotificacionesInternas() {
            var j = document.getElementById('telefonoJefe').value.trim();
            var s = document.getElementById('telefonoSupervisor').value.trim();
            var csrf = document.querySelector('meta[name="csrf-token"]').content;
            
            var payload = {
                csrf_token: csrf,
                telefono_jefe: j,
                telefono_supervisor: s
            };

            try {
                var r = await fetch(basePath + '/api/config/guardar', { 
                    method: 'POST', 
                    headers: { 'Content-Type': 'application/json' }, 
                    body: JSON.stringify(payload) 
                });
                var res = await r.json();
                if (res.status === 'success') {
                    toast('Teléfonos guardados correctamente.', 'success');
                } else {
                    toast('Error: ' + res.message, 'error');
                }
            } catch (e) {
                toast('Error de red.', 'error');
            }
        }

        async function guardarNotificaciones() {
            var csrf = document.querySelector('meta[name="csrf-token"]').content;
            var payload = {
                csrf_token: csrf,
                sms_fin_enabled: document.getElementById('chkSmsFinEnabled').checked ? '1' : '0',
                sms_fin_checked_default: document.getElementById('chkSmsFinDefault').checked ? '1' : '0',
                wa_fin_enabled: document.getElementById('chkWaFinEnabled').checked ? '1' : '0',
                wa_fin_checked_default: document.getElementById('chkWaFinDefault').checked ? '1' : '0',

                sms_crear_enabled: document.getElementById('chkSmsCrearEnabled').checked ? '1' : '0',
                sms_crear_checked_default: document.getElementById('chkSmsCrearDefault').checked ? '1' : '0',
                wa_crear_enabled: document.getElementById('chkWaCrearEnabled').checked ? '1' : '0',
                wa_crear_checked_default: document.getElementById('chkWaCrearDefault').checked ? '1' : '0'
            };
            try {
                var r = await fetch(basePath + '/api/config/guardar', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
                var res = await r.json();
                if (res.status === 'success') toast('Configuraciones de notificaciones guardadas.', 'success');
                else toast('Error: ' + res.message, 'error');
            } catch (e) { toast('Error de red.', 'error'); }
        }

        function toggleKey() { var i = document.getElementById('inputOnurixKey'); i.type = i.type === 'password' ? 'text' : 'password'; }

        async function guardarOnurix() {
            var id = document.getElementById('inputOnurixId').value.trim();
            var key = document.getElementById('inputOnurixKey').value.trim();
            if (!id || !key) { toast('Completa ID y API Key.', 'error'); return; }
            var csrf = document.querySelector('meta[name="csrf-token"]').content;
            try {
                var r = await fetch(basePath + '/api/config/guardar', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ csrf_token: csrf, onurix_api_id: id, onurix_api_key: key }) });
                var res = await r.json();
                if (res.status === 'success') { cerrarOnurix(); toast('Credenciales Onurix guardadas.', 'success'); setTimeout(function () { location.reload(); }, 1400); }
                else { toast('Error: ' + res.message, 'error'); }
            } catch (e) { toast('Error de red.', 'error'); }
        }

        async function probarOnurix() {
            toast('Enviando prueba...', 'info');
            try {
                var r = await fetch(basePath + '/api/config/probar-sms');
                var res = await r.json();
                if (res.status === 'success') {
                    alert('✅ OK: ' + res.message);
                } else {
                    alert('❌ Error: ' + res.message);
                }
            } catch (e) {
                toast('Error de red al probar API.', 'error');
            }
        }

        var _dragVar = null, _activeField = 'tplCrear';

        function chipDragStart(ev, variable) {
            _dragVar = variable;
            ev.dataTransfer.setData('text/plain', variable);
            ev.dataTransfer.effectAllowed = 'copy';
        }
        function chipDragOver(ev) { ev.preventDefault(); ev.dataTransfer.dropEffect = 'copy'; ev.currentTarget.classList.add('drag-over'); }
        function chipDragLeave(ev) { ev.currentTarget.classList.remove('drag-over'); }
        function chipDrop(ev, fieldId) {
            ev.preventDefault(); ev.currentTarget.classList.remove('drag-over');
            var variable = ev.dataTransfer.getData('text/plain') || _dragVar;
            if (!variable) return;
            var campo = document.getElementById(fieldId);
            if (!campo) return;
            campo.focus();
            var start = campo.selectionStart || campo.value.length;
            var end = campo.selectionEnd || campo.value.length;
            campo.value = campo.value.substring(0, start) + variable + campo.value.substring(end);
            campo.selectionStart = campo.selectionEnd = start + variable.length;
            renderPreviews();
        }
        function insertarVariable(variable) {
            var campo = document.getElementById(_activeField || 'tplCrear');
            if (!campo) return;
            var start = campo.selectionStart || campo.value.length;
            var end = campo.selectionEnd || campo.value.length;
            campo.value = campo.value.substring(0, start) + variable + campo.value.substring(end);
            campo.selectionStart = campo.selectionEnd = start + variable.length;
            campo.focus(); renderPreviews();
        }

        function renderPreviews() {
            var empresa = document.getElementById('inputEmpresa').value || 'Mi Empresa';
            function reemplazar(tpl) {
                return tpl.replace(/\{nombre\}/g, 'Juan Perez').replace(/\{link_seguimiento\}/g, 'banner.com.co/seguimiento.php?token=1234').replace(/\{empresa\}/g, empresa);
            }
            var tplC = document.getElementById('tplCrear').value;
            var tplF = document.getElementById('tplFinalizar').value;
            document.getElementById('prevCrear').innerHTML = reemplazar(tplC).replace(/\n/g, '<br>') || '<em style="color:#64748b;">Escribe el mensaje...</em>';
            document.getElementById('prevFinalizar').innerHTML = reemplazar(tplF).replace(/\n/g, '<br>') || '<em style="color:#64748b;">Escribe el mensaje...</em>';
        }

        async function guardarPlantillas() {
            var tplCrear = document.getElementById('tplCrear').value.trim();
            var tplFinalizar = document.getElementById('tplFinalizar').value.trim();
            if (!tplCrear || !tplFinalizar) { toast('Las plantillas no pueden estar vacias.', 'error'); return; }
            var csrf = document.querySelector('meta[name="csrf-token"]').content;
            try {
                var r = await fetch(basePath + '/api/config/guardar', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ csrf_token: csrf, sms_crear: tplCrear, sms_finalizar: tplFinalizar }) });
                var res = await r.json();
                if (res.status === 'success') { abrir('modalExito'); }
                else { toast('Error: ' + res.message, 'error'); }
            } catch (e) { toast('Error de conexion. Reintenta.', 'error'); }
        }

        function confirmarLimpiar() { if (!confirm('Eliminar movimientos de pedido con mas de 90 dias?')) return; limpiarLogs(); }
        async function limpiarLogs() {
            var csrf = document.querySelector('meta[name="csrf-token"]').content;
            try {
                var r = await fetch(basePath + '/api/config/limpiar-logs', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ csrf_token: csrf }) });
                var res = await r.json();
                toast(res.message || 'Hecho', res.status === 'success' ? 'success' : 'error');
            } catch (e) { toast('Error de red.', 'error'); }
        }

        document.addEventListener('DOMContentLoaded', function () {
            renderPreviews();
            ['tplCrear', 'tplFinalizar'].forEach(function (id) { var el = document.getElementById(id); if (el) el.addEventListener('input', renderPreviews); });
        });

        // ====== ÍCONOS DEL MENÚ (LÓGICA MEJORADA) ======
        let iconosTemporales = {}; // Almacena base64 o SVG de los cambios no guardados

        const temasIconos = {
            'outline': {
                'dashboard': `<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>`,
                'recepcion': `<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>`,
                'reportes': `<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>`,
                'reportes_pedidos': `<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/></svg>`,
                'usuarios': `<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>`,
                'areas': `<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>`,
                'configuracion': `<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>`
            },
            'dash': {
                'dashboard': `<svg width="22" height="22" viewBox="0 0 20 20" fill="#10b981"><path d="M1 1h8v8H1V1zm10 0h8v8h-8V1zM1 11h8v8H1v-8zm10 0h8v8h-8v-8z"/></svg>`,
                'recepcion': `<svg width="22" height="22" viewBox="0 0 20 20" fill="#10b981"><path d="M2 3h16v2H2V3zm0 4h10v2H2V7zm0 4h16v2H2v-2zm0 4h10v2H2v-2z"/></svg>`,
                'reportes': `<svg width="22" height="22" viewBox="0 0 20 20" fill="#10b981"><path d="M4 2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2zm1 4v8l3-2 2 3 2-3 3 2V6H5z"/></svg>`,
                'reportes_pedidos': `<svg width="22" height="22" viewBox="0 0 20 20" fill="#10b981"><path d="M17 2h-3.18c-.41-1.16-1.51-2-2.82-2S8.59.84 8.18 2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zM11 2c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1z"/></svg>`,
                'usuarios': `<svg width="22" height="22" viewBox="0 0 20 20" fill="#10b981"><path d="M10 1a5 5 0 1 1 0 10A5 5 0 0 1 10 1zm-8 17c0-3.3 3.6-6 8-6s8 2.7 8 6H2z"/></svg>`,
                'areas': `<svg width="22" height="22" viewBox="0 0 20 20" fill="#10b981"><circle cx="10" cy="10" r="3"/><path d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0zM4 10a6 6 0 1 0 12 0A6 6 0 0 0 4 10z"/></svg>`,
                'configuracion': `<svg width="22" height="22" viewBox="0 0 20 20" fill="#10b981"><path d="M10 0C4.48 0 0 4.48 0 10s4.48 10 10 10 10-4.48 10-10S15.52 0 10 0zM9 15v-2h2v2H9zm0-10h2v6H9V5z"/></svg>`
            },
            'solid': {
                'dashboard': `<svg width="22" height="22" viewBox="0 0 24 24" fill="#f59e0b"><path d="M3 3h8v8H3zm10 0h8v8h-8zM3 13h8v8H3zm10 0h8v8h-8z"/></svg>`,
                'recepcion': `<svg width="22" height="22" viewBox="0 0 24 24" fill="#f59e0b"><path d="M4 6h16v2H4zm0 4h16v2H4zm0 4h10v2H4z"/></svg>`,
                'reportes': `<svg width="22" height="22" viewBox="0 0 24 24" fill="#f59e0b"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm-1 1.5L18.5 9H13V3.5z"/></svg>`,
                'reportes_pedidos': `<svg width="22" height="22" viewBox="0 0 24 24" fill="#f59e0b"><path d="M19 3h-4.18c-.41-1.16-1.51-2-2.82-2s-2.41.84-2.82 2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 0c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1z"/></svg>`,
                'usuarios': `<svg width="22" height="22" viewBox="0 0 24 24" fill="#f59e0b"><path d="M16 11c1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3 1.34 3 3 3zm-8 0c1.66 0 3-1.34 3-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>`,
                'areas': `<svg width="22" height="22" viewBox="0 0 24 24" fill="#f59e0b"><path d="M12 15.6c-1.98 0-3.6-1.62-3.6-3.6s1.62-3.6 3.6-3.6 3.6 1.62 3.6 3.6-1.62 3.6-3.6 3.6z"/><path d="M19.14 12.94c.04-.3.06-.61.06-.94s-.02-.64-.07-.94l2.03-1.58a.49.49 0 0 0 .12-.61l-1.92-3.32a.488.488 0 0 0-.59-.22l-2.39.96c-.5-.38-1.03-.7-1.62-.94l-.36-2.54a.484.484 0 0 0-.48-.41h-3.84c-.24 0-.43.17-.47.41l-.36 2.54c-.59.24-1.13.57-1.62.94l-2.39-.96a.488.488 0 0 0-.59.22L2.74 8.87a.48.48 0 0 0 .12.61l2.03 1.58c-.05.3-.09.63-.09.94s.02.64.07.94l-2.03 1.58a.49.49 0 0 0-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.38 1.03.7 1.62.94l.36 2.54c.05.24.24.41.48.41h3.84c.24 0 .44-.17.47-.41l.36-2.54c.59-.24 1.13-.56 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32a.48.48 0 0 0-.12-.61l-2.01-1.58z"/></svg>`,
                'configuracion': `<svg width="22" height="22" viewBox="0 0 24 24" fill="#f59e0b"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>`
            },
            'duo': {
                'dashboard': `<svg width="22" height="22" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1" fill="rgba(244,114,182,0.3)"/><rect x="14" y="3" width="7" height="7" rx="1" fill="rgba(244,114,182,0.3)"/><rect x="14" y="14" width="7" height="7" rx="1" fill="#f472b6"/><rect x="3" y="14" width="7" height="7" rx="1" fill="#f472b6"/></svg>`,
                'recepcion': `<svg width="22" height="22" viewBox="0 0 24 24"><path d="M4 6h16v12H4z" fill="rgba(244,114,182,0.25)"/><rect x="2" y="4" width="20" height="4" rx="1" fill="#f472b6"/></svg>`,
                'reportes': `<svg width="22" height="22" viewBox="0 0 24 24"><path d="M6 4h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z" fill="rgba(244,114,182,0.2)"/><path d="M8 9h8M8 13h5" stroke="#f472b6" stroke-width="2" stroke-linecap="round"/></svg>`,
                'reportes_pedidos': `<svg width="22" height="22" viewBox="0 0 24 24"><path d="M7 2h10a2 2 0 0 1 2 2v16a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2z" fill="rgba(244,114,182,0.25)"/><rect x="9" y="1" width="6" height="3" rx="1" fill="#f472b6"/></svg>`,
                'usuarios': `<svg width="22" height="22" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4" fill="rgba(244,114,182,0.3)"/><path d="M4 20c0-3.3 3.6-6 8-6s8 2.7 8 6" stroke="#f472b6" stroke-width="2" stroke-linecap="round" fill="none"/></svg>`,
                'areas': `<svg width="22" height="22" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" fill="rgba(244,114,182,0.2)"/><circle cx="12" cy="12" r="3" fill="#f472b6"/><path d="M12 3v2M12 19v2M3 12h2M19 12h2" stroke="#f472b6" stroke-width="2" stroke-linecap="round"/></svg>`,
                'configuracion': `<svg width="22" height="22" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" fill="rgba(244,114,182,0.15)"/><path d="M12 7v6M12 17h.01" stroke="#f472b6" stroke-width="3" stroke-linecap="round"/></svg>`
            }
        };

        function aplicarTema(temaId) {
            const tema = temasIconos[temaId];
            if (!tema) return;

            // Recorrer los items definidos en PHP
            const items = ['dashboard', 'recepcion', 'reportes', 'reportes_pedidos', 'usuarios', 'areas', 'configuracion'];

            items.forEach(key => {
                const claveConfig = 'icon_' + key;
                const svg = tema[key];

                // Actualizar preview local
                const previewEl = document.getElementById('iconPreview_' + key);
                if (previewEl) previewEl.innerHTML = svg;

                // Guardar en objeto temporal para salvar todo luego
                iconosTemporales[claveConfig] = svg;

                // Actualizar estado visual
                const statusEl = document.getElementById('iconStatus_' + key);
                if (statusEl) statusEl.textContent = '🔄 Previsualizando tema...';
            });

            toast('Tema "' + temaId + '" aplicado a la previsualización. No olvides Guardar.', 'warn');
        }

        function handleIconUpload(event, key, clave) {
            var file = event.target.files[0];
            if (!file) return;
            if (file.size > 512000) { toast('El ícono debe pesar menos de 512 KB.', 'error'); return; }

            var reader = new FileReader();
            reader.onload = function (e) {
                var dataUrl = e.target.result;
                iconosTemporales[clave] = dataUrl;

                var previewEl = document.getElementById('iconPreview_' + key);
                if (previewEl) {
                    previewEl.innerHTML = '<img src="' + dataUrl + '" style="width:22px;height:22px;object-fit:contain;">';
                }

                var statusEl = document.getElementById('iconStatus_' + key);
                if (statusEl) statusEl.textContent = '📂 Archivo cargado (sin guardar)';

                // Asegurar que exista el botón de restaurar si no estaba
                const card = document.getElementById('iconCard_' + key);
                if (card && !card.querySelector('.btn-quitar-icon')) {
                    const btn = document.createElement('button');
                    btn.className = 'btn-quitar-icon';
                    btn.innerHTML = '✕ Restaurar por defecto';
                    btn.style.cssText = 'background:rgba(239,68,68,.07);border:1px solid rgba(239,68,68,.2);color:#f87171;border-radius:7px;padding:5px;font-size:.75rem;cursor:pointer;';
                    btn.onclick = () => quitarIcono(key, clave);
                    card.appendChild(btn);
                }
            };
            reader.readAsDataURL(file);
        }

        async function guardarIconos() {
            if (Object.keys(iconosTemporales).length === 0) {
                toast('No hay cambios pendientes para guardar.', 'error');
                return;
            }

            const btn = document.getElementById('btnGuardarIconos');
            const statusLabel = document.getElementById('iconSaveStatus');
            if (btn) btn.disabled = true;
            if (statusLabel) statusLabel.textContent = 'Guardando cambios...';

            var csrf = document.querySelector('meta[name="csrf-token"]').content;
            var payload = { csrf_token: csrf, ...iconosTemporales };

            try {
                var r = await fetch(basePath + '/api/config/guardar', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                var res = await r.json();
                if (res.status === 'success') {
                    toast('¡Todos los íconos se han actualizado correctamente!', 'success');
                    iconosTemporales = {};
                    setTimeout(() => location.reload(), 1500);
                } else {
                    toast('Error: ' + res.message, 'error');
                }
            } catch (e) {
                toast('Error de red al guardar los íconos.', 'error');
            } finally {
                if (btn) btn.disabled = false;
                if (statusLabel) statusLabel.textContent = '';
            }
        }

        async function quitarIcono(key, clave) {
            if (!confirm('¿Restaurar el ícono por defecto para "' + key + '"?')) return;

            // Si el cambio estaba en temporales, lo quitamos de ahí
            if (iconosTemporales[clave] !== undefined) {
                delete iconosTemporales[clave];
            }

            // Mandamos a la API que clave = '' para restaurar defecto en BD
            var csrf = document.querySelector('meta[name="csrf-token"]').content;
            var payload = { csrf_token: csrf };
            payload[clave] = '';

            try {
                var r = await fetch(basePath + '/api/config/guardar', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                var res = await r.json();
                if (res.status === 'success') {
                    toast('Ícono restaurado al original.', 'success');
                    setTimeout(() => location.reload(), 800);
                }
            } catch (e) { toast('Error de red.', 'error'); }
        }

        async function resetearTodosIconos() {
            if (!confirm('¿Seguro que deseas quitar TODAS las personalizaciones de íconos y volver a los originales?')) return;

            var csrf = document.querySelector('meta[name="csrf-token"]').content;
            var payload = { csrf_token: csrf };
            const items = ['dashboard', 'recepcion', 'reportes', 'reportes_pedidos', 'usuarios', 'areas', 'configuracion'];
            items.forEach(k => payload['icon_' + k] = '');

            try {
                var r = await fetch(basePath + '/api/config/guardar', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                var res = await r.json();
                if (res.status === 'success') {
                    toast('Se han restaurado todos los íconos por defecto.', 'success');
                    setTimeout(() => location.reload(), 1200);
                }
            } catch (e) { toast('Error de red.', 'error'); }
        }

        // ======= ÍCONOS DE ÁREAS DE TRABAJO =======
        let _areaIdActual = null;

        const _plantillasArea = [
            '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>',
            '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="6" cy="6" r="3"></circle><circle cx="6" cy="18" r="3"></circle><line x1="20" y1="4" x2="8.12" y2="15.88"></line><line x1="14.47" y1="14.48" x2="20" y2="20"></line><line x1="8.12" y1="8.12" x2="12" y2="12"></line></svg>',
            '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20"></path><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>',
            '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>',
            '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>',
            '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="9" y1="21" x2="9" y2="9"></line></svg>',
            '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>',
            '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M12 8v4l3 3"></path></svg>',
            '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>',
            '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>',
            '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m19 21-7-4-7 4V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16z"></path></svg>',
            '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path></svg>',
            '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12h4l2-9 5 18 2-9h5"></path></svg>',
            '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>',
            '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="21 8 21 21 3 21 3 8"></polyline><rect x="1" y="3" width="22" height="5"></rect><line x1="10" y1="12" x2="14" y2="12"></line></svg>',
            '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>',
            '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>',
            '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>',
            '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>',
            '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 7 17l-5-5"></path><path d="m22 10-7.5 7.5L13 16"></path></svg>'
        ];

        function abrirModalIconosArea(areaId) {
            _areaIdActual = areaId;
            var grid = document.getElementById('gridPlantillasIconos');
            if (!grid) { toast('Modal de íconos no encontrado.', 'error'); return; }
            grid.innerHTML = '';

            _plantillasArea.forEach(function (svgStr) {
                var div = document.createElement('div');
                div.style.cssText = 'padding:14px; background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.1); border-radius:10px; cursor:pointer; display:flex; align-items:center; justify-content:center; color:#e2e8f0; transition:all 0.2s;';
                div.innerHTML = svgStr;
                div.onmouseover = function () { div.style.borderColor = '#10b981'; div.style.color = '#10b981'; div.style.transform = 'translateY(-2px)'; };
                div.onmouseout = function () { div.style.borderColor = 'rgba(255,255,255,.1)'; div.style.color = '#e2e8f0'; div.style.transform = 'translateY(0)'; };
                div.onclick = function () { guardarIconoArea(svgStr); };
                grid.appendChild(div);
            });

            abrir('modalIconosArea');
        }

        function manejarSubidaIconoArea(e) {
            if (!e.target.files || !e.target.files[0]) return;
            if (e.target.files[0].size > 102400) { toast('El ícono no puede superar 100KB.', 'error'); return; }
            var reader = new FileReader();
            reader.onload = function (evt) {
                var imgTag = '<img src="' + evt.target.result + '" style="width:24px; height:24px; object-fit:contain; filter:drop-shadow(0 0 1px rgba(255,255,255,.3));">';
                guardarIconoArea(imgTag);
            };
            reader.readAsDataURL(e.target.files[0]);
        }

        async function guardarIconoArea(htmlStr) {
            if (!_areaIdActual) return;
            var csrf = document.querySelector('meta[name="csrf-token"]').content;

            // Vista previa inmediata
            var previewCont = document.getElementById('areaPreviewContainer_' + _areaIdActual);
            if (previewCont) {
                previewCont.innerHTML = htmlStr;
                previewCont.style.color = '#10b981';
                setTimeout(function () { previewCont.style.color = 'currentColor'; }, 1200);
            }
            cerrar('modalIconosArea');

            try {
                var r = await fetch(basePath + '/api/areas/editar-icono', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ csrf_token: csrf, id: _areaIdActual, icono: htmlStr })
                });
                var res = await r.json();
                if (res.status === 'success') {
                    toast('✅ Ícono de área guardado.', 'success');
                } else {
                    toast('Error: ' + res.message, 'error');
                }
            } catch (e) { toast('Error de red al guardar ícono de área.', 'error'); }
        }

    </script>
    <script>
        window.BANNER_SOUND_CFG = {
            enabled: <?= $sonidoHabilitado === '1' ? 'true' : 'false' ?>,
            theme: '<?= htmlspecialchars($sonidoTema)?>'
        };
    </script>
    <script src="<?= $phpBasePath?>/js/sounds.js"></script>
    <script>
        /* ─── Panel de Sonidos (guarda en BD) ─────────────────────── */
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof BannerSounds === 'undefined') return;
            var chk = document.getElementById('chkSoundEnabled');
            var badge = document.getElementById('soundBadge');
            var grid = document.getElementById('soundThemeGrid');
            if (!chk || !grid) return;

            chk.checked = BannerSounds._enabled;
            actualizarBadge(BannerSounds._enabled);

            chk.addEventListener('change', function () {
                var val = chk.checked;
                BannerSounds.setEnabled(val);
                actualizarBadge(val);
                guardarSonidoDB({ sonido_habilitado: val ? '1' : '0' });
                if (val) BannerSounds.login();
            });

            function actualizarBadge(on) {
                if (badge) {
                    badge.textContent = on ? '✅ Activo' : '⚠️ Inactivo';
                    badge.className = 'status-badge ' + (on ? 'ok' : 'warn');
                }
            }

            async function guardarSonidoDB(payload) {
                try {
                    var csrf = document.querySelector('meta[name="csrf-token"]').content;
                    payload.csrf_token = csrf;
                    var r = await fetch(basePath + '/api/config/guardar', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    });
                    var res = await r.json();
                    if (res.status !== 'success') toast('Error al guardar preferencia de sonido.', 'error');
                } catch (e) { toast('Error de red al guardar sonido.', 'error'); }
            }

            var temas = BannerSounds.getTemas();
            Object.keys(temas).forEach(function (key) {
                var t = temas[key];
                var isActive = BannerSounds._theme === key;
                var card = document.createElement('div');
                card.id = 'soundCard_' + key;
                card.style.cssText = 'cursor:pointer;border-radius:14px;padding:16px;transition:all .22s;' +
                    'background:rgba(168,85,247,.06);' +
                    'border:2px solid ' + (isActive ? t.color : 'rgba(168,85,247,.18)') + ';' +
                    (isActive ? 'box-shadow:0 0 14px ' + t.color + '44;' : '');
                card.innerHTML =
                    '<div style="font-size:.85rem;font-weight:700;color:' + t.color + ';margin-bottom:5px;">' + t.label + '</div>' +
                    '<div style="font-size:.73rem;color:#94a3b8;margin-bottom:12px;">' + t.desc + '</div>' +
                    '<div style="display:flex;gap:7px;">' +
                    '<button id="playBtn_' + key + '" onclick="event.stopPropagation();previewSoundTheme(\'' + key + '\')" ' +
                    'style="flex:1;padding:6px 2px;border-radius:8px;font-size:.72rem;font-weight:600;cursor:pointer;' +
                    'background:rgba(168,85,247,.12);border:1px solid rgba(168,85,247,.3);color:#c084fc;">&#9654; Escuchar</button>' +
                    '<button id="selBtn_' + key + '" onclick="event.stopPropagation();selectSoundTheme(\'' + key + '\')" ' +
                    'style="flex:1;padding:6px 2px;border-radius:8px;font-size:.72rem;font-weight:600;cursor:pointer;' +
                    'background:' + (isActive ? t.color : 'rgba(255,255,255,.06)') + ';' +
                    'border:1px solid ' + (isActive ? t.color : 'rgba(255,255,255,.1)') + ';' +
                    'color:' + (isActive ? '#0f172a' : '#f1f5f9') + ';">' +
                    (isActive ? '&#10003; Activo' : 'Seleccionar') + '</button>' +
                    '</div>';
                card.addEventListener('click', function () { selectSoundTheme(key); });
                grid.appendChild(card);
            });

            window.previewSoundTheme = function (key) {
                var wasEnabled = BannerSounds._enabled;
                var wasTheme = BannerSounds._theme;
                BannerSounds._enabled = true;
                BannerSounds._theme = key;
                BannerSounds.login();
                BannerSounds._theme = wasTheme;
                BannerSounds._enabled = wasEnabled;
            };

            window.selectSoundTheme = function (key) {
                BannerSounds.setTheme(key);
                guardarSonidoDB({ sonido_tema: key });
                var temas = BannerSounds.getTemas();
                Object.keys(temas).forEach(function (k) {
                    var c = document.getElementById('soundCard_' + k);
                    var b = document.getElementById('selBtn_' + k);
                    var t = temas[k];
                    var active = (k === key);
                    if (c) { c.style.borderColor = active ? t.color : 'rgba(168,85,247,.18)'; c.style.boxShadow = active ? '0 0 14px ' + t.color + '44' : ''; }
                    if (b) { b.style.background = active ? t.color : 'rgba(255,255,255,.06)'; b.style.borderColor = active ? t.color : 'rgba(255,255,255,.1)'; b.style.color = active ? '#0f172a' : '#f1f5f9'; b.textContent = active ? '\u2713 Activo' : 'Seleccionar'; }
                });
                if (BannerSounds._enabled) BannerSounds.crear();
                toast('Tema "' + temas[key].label + '" guardado.', 'success');
            };
        });
    </script>
</body>

</html>