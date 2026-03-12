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
$smsFinalizar = $rows['sms_finalizar'] ?? 'Pedido {numero_pedido}: Informa que el cliente {nombre}, tiene listo su pedido, por favor acérquese a la oficina para reclamarlo.';
$autoBackup = $rows['auto_backup_diario'] ?? '1';

// WhatsApp
$waActivo = ($rows['whatsapp_activo'] ?? '1') === '1';
$waPhoneSenderId = $rows['whatsapp_phone_sender_id'] ?? '';
$waTemplateId = $rows['whatsapp_template_id'] ?? '';
$waCredsOk = !empty($onurixId) && !empty($onurixKey) && !empty($waPhoneSenderId) && !empty($waTemplateId);


// Iconos personalizados del menú
$iconMenuItems = [
    'dashboard' => ['clave' => 'icon_dashboard', 'label' => 'Dashboard'],
    'recepcion' => ['clave' => 'icon_recepcion', 'label' => 'Recepción'],
    'reportes' => ['clave' => 'icon_reportes', 'label' => 'Reportes / Auditoría'],
    'reportes_pedidos' => ['clave' => 'icon_reportes_pedidos', 'label' => 'Reportes Pedidos'],
    'usuarios' => ['clave' => 'icon_usuarios', 'label' => 'Usuarios y Accesos'],
    'areas' => ['clave' => 'icon_areas', 'label' => 'Áreas y Workflow'],
    'configuracion' => ['clave' => 'icon_configuracion', 'label' => 'Configuración'],
];

$scriptName = dirname($_SERVER['SCRIPT_NAME']);
$phpBasePath = rtrim($scriptName, '/\\');
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
            <h1>&#9881;&#65039; <span>Configuracion</span> Avanzada</h1>
        </header>
        <div class="page-body">
            <a href="<?= $phpBasePath?>/configuracion" class="btn btn-ghost btn-sm" style="margin-bottom: 20px;">
                &#8592; Volver a Configuración General
            </a>
            <div class="page-title">Configuración Avanzada</div>
            <p class="page-sub">Credenciales, plantillas SMS, desarrollador y limpieza de la base de datos.</p>


            <!-- CREDENCIALES DE PRUEBA (Login Panel) -->
            <div class="section-card">
                <div class="section-header">
                    <div class="section-icon">&#128273;</div>
                    <div>
                        <div class="section-title">Panel de Credenciales de Prueba</div>
                        <div class="section-sub">Controla si el panel "Datos de Prueba" aparece en el Login</div>
                    </div>
                </div>
                <div class="form-group"
                    style="display:flex; align-items:center; gap:16px; background:rgba(99,102,241,.08); border:1px solid rgba(99,102,241,.2); border-radius:10px; padding:16px;">
                    <label
                        style="display:flex; align-items:center; gap:10px; cursor:pointer; font-size:0.95rem; font-weight:500;">
                        <input type="checkbox" id="chkMostrarCredenciales"
                            style="width:20px; height:20px; cursor:pointer; accent-color:#6366f1;"
                            <?=($rows['mostrar_credenciales'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <span>Mostrar el panel de credenciales en la pantalla de Login</span>
                    </label>
                </div>
                <p class="form-hint" style="margin-top:8px;">Desactívalo en producción para que los visitantes no vean
                    las contraseñas de prueba.</p>
                <button class="btn btn-primary" style="margin-top:12px;" onclick="guardarCredenciales()">&#128190;
                    Guardar Preferencia</button>
            </div>

            <!-- SMS ONURIX -->
            <div class="section-card">
                <div class="section-header">
                    <div class="section-icon">&#128241;</div>
                    <div>
                        <div class="section-title">SMS - API Onurix</div>
                        <div class="section-sub">Credenciales de mensajeria automatica</div>
                    </div>
                </div>
                <div class="api-card">
                    <div class="api-info">
                        <div class="api-name">Onurix SMS</div>
                        <div class="api-desc">Notificaciones automaticas a clientes.</div>
                        <?php if ($onurixId && $onurixKey): ?>
                        <div class="api-key-preview">ID:
                            <?= htmlspecialchars($onurixId)?> &middot; Key:
                            <?= str_repeat('&bull;', 8) . substr($onurixKey, -4)?>
                        </div>
                        <?php
endif; ?>
                    </div>
                    <div style="display:flex;flex-direction:column;align-items:flex-end;gap:10px;">
                        <span class="status-badge <?=($onurixId && $onurixKey) ? 'ok' : 'warn'?>">
                            <?=($onurixId && $onurixKey) ? '&#9989; Configurado' : '&#9888;&#65039; Sin configurar'?>
                        </span>
                        <button class="btn btn-warning btn-sm" onclick="abrirOnurix()">&#128273; Configurar
                            Credenciales</button>
                    </div>
                </div>
                <div
                    style="margin-top:14px;padding:12px;background:rgba(245,158,11,.07);border:1px solid rgba(245,158,11,.2);border-radius:10px;font-size:.82rem;color:#fbbf24;">
                    &#128161; Se usan para SMS al crear y finalizar pedidos.
                </div>
            </div>

            <!-- SALDO ONURIX -->
            <div class="section-card">
                <div class="section-header" style="margin-bottom: 12px; padding-bottom: 10px;">
                    <div class="section-icon" style="background:rgba(52,211,153,.15);border-color:rgba(52,211,153,.3);">
                        &#128176;</div>
                    <div>
                        <div class="section-title" style="color:#34d399;">Saldo Actual de Onurix</div>
                        <div class="section-sub">Consulta tu balance disponible en tiempo real</div>
                    </div>
                </div>
                <div
                    style="display: flex; align-items: center; justify-content: space-between; background: rgba(0,0,0,0.25); border: 1px solid var(--border); border-radius: 10px; padding: 18px 24px;">
                    <div style="display:flex; flex-direction: column;">
                        <span
                            style="font-size: 0.85rem; font-weight: 600; color: var(--muted); margin-bottom: 4px;">Saldo
                            Disponible:</span>
                        <span id="onurixBalanceAmount"
                            style="font-size: 1.8rem; font-weight: 800; color: #fff;">Cargando...</span>
                    </div>
                    <button class="btn btn-ghost" onclick="fetchOnurixBalance()">&#8635; Actualizar Saldo</button>
                </div>
            </div>

            <!-- WHATSAPP ONURIX -->
            <div class="section-card" style="border-color:rgba(37,211,102,.25);">
                <div class="section-header">
                    <div class="section-icon" style="background:rgba(37,211,102,.12);border-color:rgba(37,211,102,.3);">
                        &#128153;</div>
                    <div>
                        <div class="section-title" style="color:#25d366;">WhatsApp - Plantilla Onurix</div>
                        <div class="section-sub">Envío automático de guía del pedido por WhatsApp mediante plantilla
                            META</div>
                    </div>
                </div>

                <!-- Toggle habilitar / deshabilitar -->
                <div class="form-group"
                    style="display:flex;align-items:center;gap:16px;background:rgba(37,211,102,.07);border:1px solid rgba(37,211,102,.2);border-radius:10px;padding:16px;margin-bottom:18px;">
                    <label
                        style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:0.95rem;font-weight:500;flex:1;">
                        <input type="checkbox" id="chkWhatsappActivo"
                            style="width:20px;height:20px;cursor:pointer;accent-color:#25d366;" <?= $waActivo ? 'checked'
    : '' ?>>
                        <span>Habilitar envío de WhatsApp en Recepción (checkbox visible al crear pedido)</span>
                    </label>
                    <span class="status-badge <?= $waActivo ? 'ok' : 'warn'?>">
                        <?= $waActivo ? '&#9989; Activo' : '&#9888;&#65039; Inactivo'?>
                    </span>
                </div>

                <!-- Credenciales WhatsApp -->
                <div class="form-group">
                    <label class="form-label">Phone Sender ID
                        <span style="font-size:.73rem;color:#64748b;font-weight:400;"> (ID del número remitente — Onurix
                            → WhatsApp → Templates)</span>
                    </label>
                    <input type="text" id="inputWaPhoneSender" class="form-input" placeholder="Ej: 123456789012345"
                        value="<?= htmlspecialchars($waPhoneSenderId)?>">
                    <p class="form-hint">Encúentralo en <strong>onurix.com</strong> → WhatsApp → Tu número remitente.
                    </p>
                </div>

                <div class="form-group">
                    <label class="form-label">Template ID &mdash; <span style="color:#25d366;">Recepción</span>
                        <span style="font-size:.73rem;color:#64748b;font-weight:400;"> (plantilla al crear pedido &rarr;
                            <code>recepcion_de_pedidos</code>)</span>
                    </label>
                    <input type="text" id="inputWaTemplateId" class="form-input" placeholder="Ej: 987654321"
                        value="<?= htmlspecialchars($waTemplateId)?>">
                    <p class="form-hint">Onurix &rarr; WhatsApp &rarr; Templates &rarr; ID de
                        <strong>recepcion_de_pedidos</strong>.
                    </p>
                </div>

                <div class="form-group">
                    <label class="form-label">Template ID &mdash; <span style="color:#10b981;">Finalizar Pedido</span>
                        <span style="font-size:.73rem;color:#64748b;font-weight:400;"> (plantilla al completar pedido
                            &rarr; <code>recoger_pedido</code>)</span>
                    </label>
                    <input type="text" id="inputWaTemplateIdFinalizar" class="form-input" placeholder="Ej: 123456789"
                        value="<?= htmlspecialchars($rows['whatsapp_template_id_finalizar'] ?? '')?>">
                    <p class="form-hint">Onurix &rarr; WhatsApp &rarr; Templates &rarr; ID de
                        <strong>recoger_pedido</strong>.
                    </p>
                </div>

                <!-- Variables de la plantilla (nombres según META) -->
                <div
                    style="background:rgba(0,0,0,.15);border:1px solid rgba(255,255,255,.07);border-radius:10px;padding:14px 16px;margin-bottom:16px;">
                    <p style="font-size:.82rem;color:#94a3b8;margin-bottom:12px;">
                        &#128736;&#65039; <strong style="color:#e2e8f0;">Nombres de variables de la plantilla</strong>
                        &mdash; busca el payload en Onurix → WhatsApp → tu plantilla para ver los nombres exactos.
                    </p>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label">Variable «nombre del cliente»</label>
                            <input type="text" id="inputWaVarNombre" class="form-input" placeholder="nombre"
                                value="<?= htmlspecialchars($rows['whatsapp_var_nombre'] ?? 'nombre')?>">
                            <p class="form-hint" style="margin-top:4px;">Ej: <code>nombre</code></p>
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label">Variable «link de seguimiento»</label>
                            <input type="text" id="inputWaVarLink" class="form-input" placeholder="link"
                                value="<?= htmlspecialchars($rows['whatsapp_var_link'] ?? 'link')?>">
                            <p class="form-hint" style="margin-top:4px;">Ej: <code>link</code></p>
                        </div>
                    </div>
                </div>


                <div style="padding:12px;border-radius:10px;font-size:.8rem;margin-bottom:16px;
                    <?= $waCredsOk
    ? 'background:rgba(37,211,102,.07);border:1px solid rgba(37,211,102,.2);color:#6ee7b7;'
    : 'background:rgba(245,158,11,.07);border:1px solid rgba(245,158,11,.2);color:#fbbf24;'?>">
                    <?= $waCredsOk
    ? '&#9989; Todas las credenciales WhatsApp están configuradas. El checkbox aparecerá al crear pedidos.'
    : '&#9888; Completa el Phone Sender ID y Template ID. También asegúrate de tener configurado el ID y Key de Onurix arriba.'?>
                </div>

                <div
                    style="background:rgba(37,211,102,.05);border:1px solid rgba(37,211,102,.15);border-radius:10px;padding:12px;font-size:.8rem;color:#6ee7b7;margin-bottom:18px;">
                    &#128161; <strong>Importante:</strong> Antes de enviar, asegúrate de que el negocio, el número
                    telefónico
                    y la plantilla estén en estado <em>saludable</em> en Meta. Si alguno presenta advertencias o
                    bloqueos,
                    el mensaje será rechazado por Meta.
                </div>

                <button class="btn btn-success" onclick="guardarWhatsappConfig()" style="background:#25d366;">
                    &#128190; Guardar Configuración WhatsApp
                </button>
            </div>

            <!-- PLANTILLAS SMS -->

            <div class="section-card">
                <div class="section-header">
                    <div class="section-icon" style="background:rgba(16,185,129,.12);border-color:rgba(16,185,129,.3);">
                        &#9993;&#65039;</div>
                    <div>
                        <div class="section-title">Plantillas de Mensajes SMS</div>
                        <div class="section-sub">Personaliza los mensajes que reciben tus clientes</div>
                    </div>
                </div>
                <div class="var-chips">
                    <span style="font-size:.76rem;color:var(--muted);">Variables (<strong
                            style="color:#a5b4fc;">arrastra</strong> al campo o haz clic):</span>
                    <?php foreach (['{nombre}' => 'Nombre del cliente', '{numero_pedido}' => 'Número de pedido (Ej: PED-0001)', '{link_seguimiento}' => 'Link de seguimiento', '{empresa}' => 'Nombre de empresa'] as $var => $desc): ?>
                    <span class="var-chip" draggable="true" data-var="<?= htmlspecialchars($var)?>"
                        title="<?= htmlspecialchars($desc)?>" onclick="insertarVariable('<?= $var?>')"
                        ondragstart="chipDragStart(event,'<?= $var?>')">&#8942;
                        <?= htmlspecialchars($var)?>
                    </span>
                    <?php
endforeach; ?>
                </div>
                <div class="form-group">
                    <label class="form-label" for="tplCrear">&#128230; Mensaje al <strong
                            style="color:#f1f5f9;">CREAR</strong> un pedido</label>
                    <textarea id="tplCrear" class="form-input tpl-area" rows="4" onfocus="_activeField='tplCrear'"
                        ondragover="chipDragOver(event)" ondragleave="chipDragLeave(event)"
                        ondrop="chipDrop(event,'tplCrear')" oninput="renderPreviews()"
                        style="resize:vertical;line-height:1.6;"><?= htmlspecialchars($smsCrear)?></textarea>
                    <p class="form-hint">Se envia cuando se registra un nuevo pedido en Recepcion.</p>
                </div>
                <div class="preview-box">
                    <div class="preview-label blue">&#128065; VISTA PREVIA - Crear</div>
                    <div class="preview-text" id="prevCrear"></div>
                </div>
                <hr class="divider">
                <div class="form-group">
                    <label class="form-label" for="tplFinalizar">&#9989; Mensaje al <strong
                            style="color:#f1f5f9;">FINALIZAR</strong> un pedido</label>
                    <textarea id="tplFinalizar" class="form-input tpl-area" rows="4"
                        onfocus="_activeField='tplFinalizar'" ondragover="chipDragOver(event)"
                        ondragleave="chipDragLeave(event)" ondrop="chipDrop(event,'tplFinalizar')"
                        oninput="renderPreviews()"
                        style="resize:vertical;line-height:1.6;"><?= htmlspecialchars($smsFinalizar)?></textarea>
                    <p class="form-hint">Se envia cuando se presiona "Finalizar" en el Kanban.</p>
                </div>
                <div class="preview-box">
                    <div class="preview-label green">&#128065; VISTA PREVIA - Finalizar</div>
                    <div class="preview-text" id="prevFinalizar"></div>
                </div>
                <div
                    style="background:rgba(16,185,129,.06);border:1px solid rgba(16,185,129,.18);border-radius:10px;padding:12px;font-size:.8rem;color:#6ee7b7;margin-bottom:18px;">
                    &#128161; La firma SMS de Onurix se agrega automaticamente al final. No la incluyas aqui.
                </div>
                <button class="btn btn-success" onclick="guardarPlantillas()">&#128190; Guardar Plantillas SMS</button>
            </div>


            <!-- BACKUP BASE DE DATOS -->
            <div class="section-card" style="border-color:rgba(16, 185, 129, .3);">
                <div class="section-header">
                    <div class="section-icon"
                        style="background:rgba(16, 185, 129, .1);border-color:rgba(16, 185, 129, .3);color:#10b981;">
                        &#128190;
                    </div>
                    <div>
                        <div class="section-title" style="color:#10b981;">Base de Datos (Backups)</div>
                        <div class="section-sub">Exportar toda la información de la plataforma a un archivo SQL
                            descargable.</div>
                    </div>
                </div>

                <div
                    style="margin-bottom: 24px; padding: 18px; border-radius:12px; border: 1px solid rgba(16,185,129,.3); background: rgba(16,185,129,.05);">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <div>
                            <h4 style="color:#10b981; font-weight:700; margin-bottom:6px; font-size:1.05rem;">
                                Exportación Manual Inmediata</h4>
                            <p style="font-size:0.85rem; color:var(--muted); max-width:400px;">Genera un archivo .sql al
                                instante con todas las tablas y registros para descargarlo a tu equipo.</p>
                        </div>
                        <button class="btn btn-success" onclick="exportarDB()">&#11015;&#65039; Exportar Ahora
                            (.sql)</button>
                    </div>
                </div>

                <div class="form-group"
                    style="display:flex; align-items:center; gap:16px; background:rgba(99,102,241,.08); border:1px solid rgba(99,102,241,.2); border-radius:10px; padding:16px;">
                    <label
                        style="display:flex; align-items:center; gap:10px; cursor:pointer; font-size:0.95rem; font-weight:500; flex:1;">
                        <input type="checkbox" id="chkAutoBackup"
                            style="width:20px; height:20px; cursor:pointer; accent-color:#6366f1;" <?=($autoBackup === '1'
     ) ? 'checked' : '' ?>>
                        <span>Habilitar Auto-Backup Físico Diario (Copia Silenciosa)</span>
                    </label>
                </div>
                <p class="form-hint" style="margin-top:8px;">Una vez al día, la plataforma guardará automáticamente una
                    copia en formato SQL internamente dentro del servidor remoto (Ej: BD_Bnner_Lunes_01_10_2025...).
                    Recomendado mantener activo.</p>
                <button class="btn btn-primary" style="margin-top:12px;" onclick="guardarOpcionBackup()">&#128190;
                    Guardar Opciones</button>
            </div>

            <!-- ZONA DE PELIGRO -->
            <div class="section-card" style="border-color:rgba(239,68,68,.2);">
                <div class="section-header">
                    <div class="section-icon" style="background:rgba(239,68,68,.1);border-color:rgba(239,68,68,.3);">
                        &#9888;&#65039;</div>
                    <div>
                        <div class="section-title" style="color:#f87171;">Zona de Peligro (Limpieza de Capacidad)</div>
                        <div class="section-sub">Las acciones ejecutadas en esta área eliminarán información física y de
                            la BD permanentemente para vaciar almacenamiento.</div>
                    </div>
                </div>

                <div style="margin-bottom: 20px;">
                    <button class="btn btn-ghost btn-sm" onclick="confirmarLimpiar()"
                        style="border-color: #f87171; color: #f87171;">
                        &#129529; Purgar Archivos Adjuntos Antiguos (+90 dias)
                    </button>
                    <p style="font-size:.8rem;color:var(--muted);margin-top:8px;">
                        Elimina permanentemente los archivos físicos subidos a los pedidos superiores a 90 días,
                        liberando espacio en disco.
                    </p>
                </div>

                <div style="margin-bottom: 20px;">
                    <button class="btn btn-ghost btn-sm" onclick="abrirLimpiarAuditoria()"
                        style="border-color: #f59e0b; color: #f59e0b;">
                        &#128196; Vaciar Reportes de Auditoría
                    </button>
                    <p style="font-size:.8rem;color:var(--muted);margin-top:8px;">
                        Elimina permanentemente todo el historial de cambios en los reportes de auditoría de
                        (movimientos y estados), liberando espacio en disco.
                    </p>
                </div>

                <div style="margin-bottom: 20px;">
                    <button class="btn btn-ghost btn-sm" onclick="abrirPeligro()"
                        style="background:rgba(239,68,68,.1); border-color: #ef4444; color: #ef4444; font-weight:700;">
                        &#128465; Eliminar Pedidos Antiguos Completos
                    </button>
                    <p style="font-size:.8rem;color:var(--muted);margin-top:8px;">
                        Esta acción elimina permanentemente todos los pedidos superiores a 30 días junto con todo su
                        detalle, comentarios y archivos para recuperar espacio vital de la base de datos.
                    </p>
                </div>

                <div>
                    <button class="btn btn-danger-sm" onclick="abrirSuperPeligro()"
                        style="background:#ef4444; color:white; border:none; padding:10px 16px; font-size:.9rem; width:100%;">
                        &#128163; BORRAR TODOS LOS PEDIDOS Y DETALLES
                    </button>
                    <p style="font-size:.8rem;color:var(--muted);margin-top:8px;">
                        <strong>Borrón y cuenta nueva:</strong> Elimina todos los pedidos del Kanban sin excepción. Solo
                        quedarán los usuarios y configuraciones del sistema.
                    </p>
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

    <!-- MODAL PELIGRO EXTREMO -->
    <div class="modal-ovl" id="modalPeligro" style="z-index: 10000;">
        <div class="modal-box" style="animation: bounceIn .4s ease;">
            <div class="modal-head" style="border-bottom-color: rgba(239,68,68,.2);">
                <h2 style="color:#f87171;">&#9888;&#65039; ACCIÓN IRREVERSIBLE</h2>
                <button class="modal-close" onclick="cerrar('modalPeligro')">&times;</button>
            </div>
            <div class="modal-body" style="text-align:center;">
                <div style="font-size:3rem; margin-bottom:15px; animation: pulse 1s infinite alternate;">&#9888;&#65039;
                </div>
                <p style="font-size:1.05rem; font-weight:700; color:#f87171; margin-bottom:10px;">¡ATENCIÓN!</p>
                <p style="font-size:.95rem; color:var(--text); margin-bottom:15px;" id="peligroTexto">
                    Estás a punto de eliminar información crítica e irreversiblemente.
                </p>
                <p
                    style="font-size:.85rem; color:var(--muted); background:rgba(239,68,68,.08); padding:10px; border-radius:8px;">
                    ¿Estás absolutamente seguro de que deseas continuar con esta limpieza?
                </p>
            </div>
            <div class="modal-footer" style="justify-content:center; gap:15px; padding-top:10px;">
                <button class="btn btn-ghost" onclick="cerrar('modalPeligro')">Cancelar Misión</button>
                <button class="btn btn-danger-sm"
                    style="background:#ef4444; color:white; border:none; padding:10px 20px;" id="btnConfirmarPeligro"
                    onclick="ejecutarLimpiezaSevera()">¡Sí, Eliminar Permanentemente!</button>
            </div>
        </div>
    </div>
    <style>
        @keyframes bounceIn {
            0% {
                transform: scale(0.8);
                opacity: 0;
            }

            60% {
                transform: scale(1.05);
                opacity: 1;
            }

            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        @keyframes pulse {
            from {
                transform: scale(1);
            }

            to {
                transform: scale(1.1);
            }
        }
    </style>

    <!-- TOAST -->
    <div class="toast" id="toastEl"><span id="toastMsg">OK</span></div>

    <script>
        var basePath = "<?php echo addslashes($phpBasePath); ?>";

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

        async function fetchOnurixBalance() {
            var el = document.getElementById('onurixBalanceAmount');
            if (el) { el.textContent = 'Cargando...'; el.style.color = '#fff'; }
            try {
                var r = await fetch(basePath + '/api/config/probar-saldo');
                var res = await r.json();
                if (res.status === 'success') {
                    if (el) {
                        el.textContent = '$' + parseFloat(res.balance).toLocaleString('es-CO');
                        el.style.color = '#34d399';
                    }
                } else {
                    if (el) {
                        el.textContent = 'Error';
                        el.style.color = '#f87171';
                    }
                    toast('Error obteniendo saldo: ' + res.message, 'error');
                }
            } catch (e) {
                if (el) {
                    el.textContent = 'Error RED';
                    el.style.color = '#f87171';
                }
                toast('Error de red al consultar saldo.', 'error');
            }
        }

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
            document.getElementById('logoFile').value = '';
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
                return tpl.replace(/\{nombre\}/g, 'Juan Perez').replace(/\{numero_pedido\}/g, 'PED-0001').replace(/\{link_seguimiento\}/g, 'banner.com.co/seguimiento.php?token=1234').replace(/\{empresa\}/g, empresa);
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

        async function guardarOpcionBackup() {
            var m = document.getElementById('chkAutoBackup').checked ? '1' : '0';
            var csrf = document.querySelector('meta[name="csrf-token"]').content;
            try {
                var r = await fetch(basePath + '/api/config/guardar', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ csrf_token: csrf, auto_backup_diario: m })
                });
                var res = await r.json();
                if (res.status === 'success') toast('Preferencias de Backup actualizadas.', 'success');
                else toast('Error: ' + res.message, 'error');
            } catch (e) { toast('Error de conexión.', 'error'); }
        }

        async function exportarDB() {
            var btn = event.currentTarget;
            var originalText = btn.innerHTML;
            btn.innerHTML = '&#8987; Exportando...';
            btn.disabled = true;
            toast('Iniciando copia de seguridad. Por favor espera...', 'info');

            try {
                var r = await fetch(basePath + '/api/config/exportar-db');
                var ct = r.headers.get('content-type');

                if (ct && ct.includes('application/json')) {
                    var res = await r.json();
                    toast('Error: ' + res.message, 'error');
                } else if (r.ok) {
                    var blob = await r.blob();
                    var url = window.URL.createObjectURL(blob);
                    var a = document.createElement('a');
                    a.href = url;

                    var disp = r.headers.get('content-disposition');
                    var filename = 'BD_Bnner_Manual.sql';
                    if (disp && disp.indexOf('filename=') !== -1) {
                        filename = disp.split('filename=')[1].replace(/"/g, '');
                    }

                    a.download = filename;
                    document.body.appendChild(a);
                    a.click();
                    a.remove();
                    window.URL.revokeObjectURL(url);
                    toast('&#10004; Base de datos descargada con éxito.', 'success');
                } else {
                    toast('Ocurrió un error (Código HTTP ' + r.status + ')', 'error');
                }
            } catch (e) {
                toast('Error de red al intentar exportar la base de datos.', 'error');
            }

            btn.innerHTML = originalText;
            btn.disabled = false;
        }

        function confirmarLimpiar() { if (!confirm('¿Eliminar todos los archivos adjuntos con mas de 90 dias de antigüedad para liberar espacio?')) return; limpiarLogs(); }
        async function limpiarLogs() {
            toast('Purgando archivos antiguos...', 'info');
            var csrf = document.querySelector('meta[name="csrf-token"]').content;
            try {
                var r = await fetch(basePath + '/api/reportes-pedidos/eliminar-archivos', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ csrf_token: csrf, rango: '3_months' }) });
                var res = await r.json();
                toast(res.message || 'Archivos purgados.', res.status === 'success' ? 'success' : 'error');
            } catch (e) { toast('Error de red.', 'error'); }
        }

        let _tipoLimpieza = '';
        function abrirPeligro() {
            _tipoLimpieza = 'antiguos';
            document.getElementById('peligroTexto').textContent = 'Estás a punto de eliminar todos los pedidos y sus detalles que tengan más de 30 días de antigüedad.';
            abrir('modalPeligro');
        }

        function abrirLimpiarAuditoria() {
            _tipoLimpieza = 'auditoria';
            document.getElementById('peligroTexto').textContent = 'Estás a punto de eliminar TODO el historial de reportes de auditoría.\nLos pedidos no se verán afectados.';
            abrir('modalPeligro');
        }

        function abrirSuperPeligro() {
            _tipoLimpieza = 'todos';
            document.getElementById('peligroTexto').innerHTML = 'Estás a punto de <strong style="color:red; font-size:1.1em;">ELIMINAR TODOS LOS PEDIDOS</strong>.<br>Solo sobrevivirá la configuración y los usuarios.';
            abrir('modalPeligro');
        }

        async function ejecutarLimpiezaSevera() {
            cerrar('modalPeligro');
            toast('Ejecutando limpieza...', 'warn');
            var csrf = document.querySelector('meta[name="csrf-token"]').content;

            let endpoint = '';
            if (_tipoLimpieza === 'todos') {
                endpoint = '/api/config/eliminar-todos-pedidos';
            } else if (_tipoLimpieza === 'auditoria') {
                endpoint = '/api/config/eliminar-auditoria';
            } else {
                endpoint = '/api/reportes-pedidos/eliminar-pedidos';
            }

            try {
                var r = await fetch(basePath + endpoint, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ csrf_token: csrf, rango: '1_month' }) });
                var res = await r.json();

                if (res.status === 'success') {
                    toast(res.message || 'Limpieza exitosa.', 'success');
                } else {
                    toast('Error: ' + res.message, 'error');
                }
            } catch (e) {
                toast('Error de red ejecutando limpieza.', 'error');
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            renderPreviews();
            ['tplCrear', 'tplFinalizar'].forEach(function (id) { var el = document.getElementById(id); if (el) el.addEventListener('input', renderPreviews); });
            fetchOnurixBalance();
        });

        async function guardarWhatsappConfig() {
            var activo = document.getElementById('chkWhatsappActivo').checked ? '1' : '0';
            var phoneSender = document.getElementById('inputWaPhoneSender').value.trim();
            var templateId = document.getElementById('inputWaTemplateId').value.trim();
            var templateIdFin = document.getElementById('inputWaTemplateIdFinalizar').value.trim();
            var varNombre = document.getElementById('inputWaVarNombre').value.trim() || 'nombre';
            var varLink = document.getElementById('inputWaVarLink').value.trim() || 'link';
            var csrf = document.querySelector('meta[name="csrf-token"]').content;
            try {
                var r = await fetch(basePath + '/api/config/guardar', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        csrf_token: csrf,
                        whatsapp_activo: activo,
                        whatsapp_phone_sender_id: phoneSender,
                        whatsapp_template_id: templateId,
                        whatsapp_template_id_finalizar: templateIdFin,
                        whatsapp_var_nombre: varNombre,
                        whatsapp_var_link: varLink
                    })
                });
                var res = await r.json();
                if (res.status === 'success') {
                    toast('Configuración WhatsApp guardada correctamente.', 'success');
                    setTimeout(function () { location.reload(); }, 1200);
                } else {
                    toast('Error: ' + res.message, 'error');
                }
            } catch (e) {
                toast('Error de red al guardar WhatsApp.', 'error');
            }
        }


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
    </script>
</body>

</html>