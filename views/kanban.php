<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ./login');
    exit;
}

$role = $_SESSION['role'] ?? 'Operador';
$canViewPrices = (in_array($role, ['Admin', 'SuperAdmin']) || !empty($_SESSION['ver_precios']));
$canEditOrders = (in_array($role, ['Admin', 'SuperAdmin']) || !empty($_SESSION['editar_pedidos']));
$csrfToken = \App\Middlewares\CsrfMiddleware::generateToken();

// Áreas activas para modal de envío
$dbK = \Config\Database::getInstance();
$areasActivas = $dbK->query("SELECT id, nombre FROM areas WHERE estado = 1 ORDER BY orden ASC")->fetchAll(\PDO::FETCH_ASSOC);

// Info del área actual
$areaParam = $_GET['area_id'] ?? null;
$stmtA = $dbK->prepare("SELECT id, nombre FROM areas WHERE id = :id AND estado = 1 LIMIT 1");
$stmtA->execute(['id' => $areaParam]);
$currentArea = $stmtA->fetch(\PDO::FETCH_ASSOC);

// Si no se encontró el área (o no se pasó), redirigir al dashboard
if (!$currentArea) {
    header('Location: ./dashboard');
    exit;
}

// Configuración WhatsApp para el modal Finalizar
try {
    $waCfgObj = $dbK->query("SELECT clave, valor FROM configuracion WHERE clave IN
        ('whatsapp_activo','whatsapp_phone_sender_id','whatsapp_template_id','onurix_api_id','onurix_api_key','sonido_habilitado','sonido_tema', 'sms_fin_enabled', 'sms_fin_checked_default', 'wa_fin_enabled', 'wa_fin_checked_default')");
    $waCfgK = $waCfgObj->fetchAll(\PDO::FETCH_KEY_PAIR);
}
catch (\Exception $e) {
}
$waActivo = ($waCfgK['whatsapp_activo'] ?? '1') === '1';
$waCredsOk = !empty($waCfgK['onurix_api_id']) && !empty($waCfgK['onurix_api_key'])
    && !empty($waCfgK['whatsapp_phone_sender_id']) && !empty($waCfgK['whatsapp_template_id']);
$smsFinEnabled = ($waCfgK['sms_fin_enabled'] ?? '1') === '1';
$smsFinCheckedDefault = ($waCfgK['sms_fin_checked_default'] ?? '1') === '1';
$waFinEnabled = ($waCfgK['wa_fin_enabled'] ?? '1') === '1';
$waFinCheckedDefault = ($waCfgK['wa_fin_checked_default'] ?? '1') === '1';
$sonidoHabilitado = $waCfgK['sonido_habilitado'] ?? '1';
$sonidoTema = $waCfgK['sonido_tema'] ?? 'cristal';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <meta name="csrf-token" content="<?= $csrfToken?>">
    <title>Kanban –
        <?= htmlspecialchars($currentArea['nombre'])?>
    </title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4F46E5;
            --bg: #0F172A;
            --surface: rgba(30, 41, 59, .7);
            --border: rgba(255, 255, 255, .1);
            --text: #F8FAFC;
            --muted: #94A3B8;
            --recep: #3B82F6;
            --proc: #F59E0B;
            --prep: #10B981;
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
            background-image: radial-gradient(at 0% 0%, rgba(79, 70, 229, .1) 0, transparent 50%), radial-gradient(at 100% 100%, rgba(16, 185, 129, .05) 0, transparent 50%);
        }

        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .topbar {
            height: 64px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            padding: 0 28px;
            justify-content: space-between;
            background: rgba(15, 23, 42, .6);
            backdrop-filter: blur(10px);
        }

        .topbar h1 {
            font-size: 1.1rem;
            font-weight: 700;
        }

        .topbar h1 span {
            color: var(--primary);
        }

        .topbar-right {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .btn-back {
            background: rgba(99, 102, 241, .1);
            color: #a5b4fc;
            border: 1px solid rgba(99, 102, 241, .25);
            border-radius: 8px;
            padding: 6px 14px;
            font-size: .82rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .btn-back:hover {
            background: rgba(99, 102, 241, .2);
        }

        .btn-logout {
            background: rgba(239, 68, 68, .1);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, .25);
            border-radius: 8px;
            padding: 6px 14px;
            font-size: .82rem;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-logout:hover {
            background: rgba(239, 68, 68, .2);
        }

        /* Board */
        .board-wrap {
            flex: 1;
            padding: 24px;
            display: flex;
            gap: 20px;
            overflow-x: auto;
            align-items: flex-start;
        }

        @media (max-width: 768px) {
            .board-wrap {
                flex-direction: column;
                padding: 12px;
                gap: 16px;
                overflow-x: hidden;
            }
            .main-content {
                padding-left: 0 !important;
                width: 100%;
            }
        }

        .kanban-col {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            width: 300px;
            min-width: 300px;
            display: flex;
            flex-direction: column;
            max-height: calc(100vh - 112px);
        }

        @media (max-width: 768px) {
            .kanban-col {
                width: 100% !important;
                min-width: 100% !important;
                max-height: none !important;
            }
        }

        .col-header {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .col-header h3 {
            font-size: .95rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .dot {
            width: 9px;
            height: 9px;
            border-radius: 50%;
        }

        .dot.recepcion {
            background: var(--recep);
            box-shadow: 0 0 8px var(--recep);
        }

        .dot.proceso {
            background: var(--proc);
            box-shadow: 0 0 8px var(--proc);
        }

        .dot.preparado {
            background: var(--prep);
            box-shadow: 0 0 8px var(--prep);
        }

        .badge,
        .col-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 20px;
            font-size: .7rem;
            font-weight: 700;
            color: #fff;
        }

        .badge.pago-completo {
            background: #10b981;
        }

        .badge.abono {
            background: #f59e0b;
        }

        .badge.no-pago {
            background: #ef4444;
        }

        .badge.prioridad {
            background: #ef4444;
            animation: pulse 2s infinite;
        }

        .badge.largo {
            background: #6366f1;
        }

        .badge.normal {
            background: rgba(100, 116, 139, .55);
            color: #cbd5e1;
            border: 1px solid rgba(148, 163, 184, .25);
        }

        /* Borde izquierdo de la card según prioridad */
        .card.prio-prioridad {
            border-left: 3px solid #ef4444;
        }

        .card.prio-largo {
            border-left: 3px solid #6366f1;
        }

        .card.prio-normal {
            border-left: 3px solid rgba(255, 255, 255, .07);
        }

        /* Animaciones de deadline para tarjetas kanban */
        @keyframes kanban-pulse-orange {

            0%,
            100% {
                box-shadow: 0 0 0 0 rgba(251, 146, 60, 0.6), inset 0 0 0 2px rgba(251, 146, 60, 0.4);
            }

            50% {
                box-shadow: 0 0 0 5px rgba(251, 146, 60, 0.2), inset 0 0 0 2px rgba(249, 115, 22, 0.7);
            }
        }

        @keyframes kanban-pulse-red {

            0%,
            100% {
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.5), inset 0 0 0 2px rgba(239, 68, 68, 0.3);
            }

            50% {
                box-shadow: 0 0 0 5px rgba(239, 68, 68, 0.2), inset 0 0 0 2px rgba(239, 68, 68, 0.6);
            }
        }

        @keyframes kanban-pulse-critical {

            0%,
            100% {
                box-shadow: 0 0 0 0 rgba(185, 28, 28, 0.7), inset 0 0 0 3px rgba(185, 28, 28, 0.5);
            }

            50% {
                box-shadow: 0 0 0 7px rgba(185, 28, 28, 0.25), inset 0 0 0 3px rgba(185, 28, 28, 0.9);
            }
        }

        .card.deadline-soon {
            animation: kanban-pulse-orange 1.8s ease-in-out infinite;
            border-color: #fb923c !important;
        }

        .card.deadline-overdue {
            animation: kanban-pulse-red 2s ease-in-out infinite;
            border-color: #ef4444 !important;
            background: rgba(254, 226, 226, 0.12) !important;
        }

        .card.deadline-critical {
            animation: kanban-pulse-critical 1.2s ease-in-out infinite;
            border-color: #b91c1c !important;
            background: rgba(254, 202, 202, 0.25) !important;
        }

        .col-badge {
            background: rgba(255, 255, 255, .1);
            color: var(--muted);
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }
        }

        .col-body {
            padding: 14px;
            overflow-y: auto;
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 12px;
            min-height: 120px;
        }

        .empty-col {
            text-align: center;
            padding: 28px 12px;
            color: var(--muted);
            font-size: .85rem;
            border: 1px dashed var(--border);
            border-radius: 8px;
        }

        /* Cards */
        .card {
            background: rgba(15, 23, 42, .65);
            border: 1px solid var(--border);
            padding: 14px;
            border-radius: 10px;
            cursor: grab;
            transition: transform .2s, box-shadow .2s;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, .3);
            border-color: rgba(255, 255, 255, .18);
        }

        .card:active {
            cursor: grabbing;
        }

        .card-id {
            font-size: .72rem;
            color: var(--primary);
            font-weight: 700;
            margin-bottom: 3px;
        }

        .card-name {
            font-size: .95rem;
            font-weight: 600;
            margin-bottom: 6px;
        }

        .card-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: .75rem;
            color: var(--muted);
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid rgba(255, 255, 255, .05);
            flex-wrap: wrap;
            gap: 6px;
        }

        .card-actions {
            display: flex;
            gap: 6px;
            margin-top: 10px;
            flex-wrap: wrap;
        }

        .btn-card {
            border: none;
            color: #fff;
            padding: 4px 10px;
            border-radius: 6px;
            cursor: pointer;
            font-size: .72rem;
            font-weight: 600;
            transition: opacity .2s;
        }

        .btn-card:hover {
            opacity: .85;
        }

        .btn-tomar {
            background: var(--primary);
        }

        .btn-preparar {
            background: #f59e0b;
        }

        .btn-finalizar {
            background: #10b981;
        }

        .btn-enviar {
            background: #6366f1;
        }

        /* Toast */
        .toast {
            position: fixed;
            bottom: 24px;
            right: 24px;
            background: rgba(30, 41, 59, .95);
            border: 1px solid var(--border);
            padding: 14px 22px;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, .5);
            display: flex;
            align-items: center;
            gap: 10px;
            transform: translateY(80px);
            opacity: 0;
            transition: all .3s cubic-bezier(.16, 1, .3, 1);
            z-index: 500;
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

        /* Modales */
        .modal-ovl {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .72);
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
            border-radius: 16px;
            width: 100%;
            max-width: 520px;
            max-height: 88vh;
            overflow-y: auto;
            box-shadow: 0 24px 60px rgba(0, 0, 0, .5);
            animation: fadeUp .2s ease;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(14px);
            }
        }

        .modal-head {
            padding: 20px 24px 16px;
            border-bottom: 1px solid rgba(255, 255, 255, .07);
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
        }

        .modal-head h2 {
            font-size: 1.05rem;
            font-weight: 700;
        }

        .modal-head .pid {
            font-size: .82rem;
            color: #6366f1;
            margin-top: 2px;
        }

        .modal-close {
            background: none;
            border: none;
            color: var(--muted);
            font-size: 1.4rem;
            cursor: pointer;
            padding: 0 4px;
            line-height: 1;
        }

        .modal-close:hover {
            color: var(--text);
        }

        .modal-body {
            padding: 18px 24px;
        }

        .modal-footer {
            padding: 14px 24px 20px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .detail-block {
            background: rgba(0, 0, 0, .2);
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, .05);
            padding: 14px;
            margin-bottom: 12px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            border-bottom: 1px solid rgba(255, 255, 255, .04);
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-lbl {
            color: var(--muted);
            font-size: .8rem;
        }

        .detail-val {
            color: var(--text);
            font-size: .85rem;
            font-weight: 500;
        }

        .sec-title {
            font-size: .72rem;
            font-weight: 700;
            color: #6366f1;
            text-transform: uppercase;
            letter-spacing: .05em;
            margin-bottom: 7px;
        }

        .btn-modal {
            flex: 1;
            min-width: 100px;
            padding: 10px 14px;
            border-radius: 10px;
            border: none;
            font-size: .84rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: opacity .2s, transform .1s;
        }

        .btn-modal:hover {
            opacity: .88;
            transform: translateY(-1px);
        }

        .btn-modal.primary {
            background: var(--primary);
            color: #fff;
        }

        .btn-modal.success {
            background: #10b981;
            color: #fff;
        }

        .btn-modal.warning {
            background: #f59e0b;
            color: #fff;
        }

        .btn-modal.purple {
            background: #6366f1;
            color: #fff;
        }

        .btn-modal.ghost {
            background: rgba(255, 255, 255, .07);
            color: var(--text);
            border: 1px solid rgba(255, 255, 255, .12);
        }

        .area-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin: 10px 0;
        }

        .area-sel-btn {
            padding: 12px;
            border: 2px solid rgba(255, 255, 255, .1);
            border-radius: 10px;
            background: rgba(255, 255, 255, .04);
            color: var(--text);
            cursor: pointer;
            font-size: .85rem;
            font-weight: 500;
            text-align: center;
            transition: all .2s;
        }

        .area-sel-btn:hover,
        .area-sel-btn.selected {
            border-color: #6366f1;
            background: rgba(99, 102, 241, .15);
            color: #a5b4fc;
        }

        .pago-saldo {
            color: #ef4444;
            font-weight: 700;
        }

        /* Forms in Modal */
        .form-row {
            display: flex;
            gap: 12px;
            margin-bottom: 12px;
        }

        .form-col {
            flex: 1;
        }

        .form-group label {
            display: block;
            font-size: 0.8rem;
            color: var(--muted);
            margin-bottom: 4px;
        }

        .form-control {
            width: 100%;
            padding: 8px 10px;
            background: rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text);
            border-radius: 6px;
            font-size: 0.85rem;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
        }
    </style>
</head>

<body>
    <?php include __DIR__ . '/components/sidebar.php'; ?>

    <div class="main-content">
        <header class="topbar">
            <div style="display:flex; align-items:center; gap:16px;">
                <button id="btnHamburger"
                    style="background:none; border:none; color:#f1f5f9; cursor:pointer; font-size:1.5rem; display:flex;">☰</button>
                <h1>🗂 <span>
                        <?= htmlspecialchars($currentArea['nombre'])?>
                    </span></h1>
            </div>
            <div class="topbar-right">
                <a class="btn-back" href="dashboard">← Dashboard</a>
                <button class="btn-logout" onclick="logout()">Cerrar Sesión</button>
            </div>
        </header>

        <div class="board-wrap">
            <!-- Recepción del área -->
            <div class="kanban-col">
                <div class="col-header">
                    <h3>
                        <div class="dot recepcion"></div> Recepción
                    </h3>
                    <span class="col-badge" id="cnt-recepcion">0</span>
                </div>
                <div class="col-body" id="col-recepcion" ondragover="allowDrop(event)" ondrop="drop(event,'recepcion')">
                </div>
            </div>
            <!-- En Proceso -->
            <div class="kanban-col">
                <div class="col-header">
                    <h3>
                        <div class="dot proceso"></div> En Proceso <small
                            style="font-size:.7rem;color:var(--muted);font-weight:400;">(Tuyo)</small>
                    </h3>
                    <span class="col-badge" id="cnt-proceso">0</span>
                </div>
                <div class="col-body" id="col-proceso" ondragover="allowDrop(event)" ondrop="drop(event,'proceso')">
                </div>
            </div>
            <!-- Preparado -->
            <div class="kanban-col">
                <div class="col-header">
                    <h3>
                        <div class="dot preparado"></div> Preparado <small
                            style="font-size:.7rem;color:var(--muted);font-weight:400;">(Listo)</small>
                    </h3>
                    <span class="col-badge" id="cnt-preparado">0</span>
                </div>
                <div class="col-body" id="col-preparado" ondragover="allowDrop(event)" ondrop="drop(event,'preparado')">
                </div>
            </div>
        </div>
    </div>

    <div class="toast" id="toast"><span id="toast-icon">✅</span><span id="toast-msg">OK</span></div>

    <!-- Modal Detalles -->
    <div class="modal-ovl" id="modalDet" onclick="if(event.target===this) cerrar('modalDet')">
        <div class="modal-box">
            <div class="modal-head">
                <div>
                    <h2 id="detNombre">–</h2>
                    <div class="pid" id="detId">#PED-0000</div>
                </div>
                <button class="modal-close" onclick="cerrar('modalDet')">×</button>
            </div>
            <div class="modal-body">
                <div id="detBadges" style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:14px;"></div>
                <div class="sec-title">📋 Información</div>
                <div class="detail-block" id="detInfo"></div>
                <div id="detPago"></div>
                <div id="detNotas"></div>
                <div id="detArchivos"></div>
            </div>
            <div class="modal-footer" id="detBotones"></div>
        </div>
    </div>

    <!-- Modal Enviar Área -->
    <div class="modal-ovl" id="modalEnviar" onclick="if(event.target===this) cerrar('modalEnviar')">
        <div class="modal-box" style="max-width:420px;">
            <div class="modal-head">
                <div>
                    <h2>📤 Enviar a Área</h2>
                    <div class="pid" id="envLabel">#PED-0000</div>
                </div>
                <button class="modal-close" onclick="cerrar('modalEnviar')">×</button>
            </div>
            <div class="modal-body">
                <p style="color:var(--muted);font-size:.84rem;margin-bottom:12px;">Selecciona el área de destino:</p>
                <div class="area-grid" id="areaGrid">
                    <?php foreach ($areasActivas as $a): ?>
                    <button class="area-sel-btn" data-id="<?= $a['id']?>" onclick="selArea(this)">
                        <?= htmlspecialchars($a['nombre'])?>
                    </button>
                    <?php
endforeach; ?>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-modal ghost" onclick="cerrar('modalEnviar')">Cancelar</button>
                <button class="btn-modal purple" id="btnConfEnvio" disabled onclick="ejecutarEnvio()">📤
                    Confirmar</button>
            </div>
        </div>
    </div>

    <!-- Modal Finalizar Pedido -->
    <div class="modal-ovl" id="modalFinalizar" onclick="if(event.target===this) cerrar('modalFinalizar')">
        <div class="modal-box" style="max-width:440px;">
            <div class="modal-head">
                <div style="flex:1;">
                    <h2 style="color:#10b981; margin-bottom: 2px;">✅ Finalizar Pedido</h2>
                    <div class="pid" id="finLabel">#PED-0000</div>
                </div>
                <button class="modal-close" onclick="cerrar('modalFinalizar')">×</button>
            </div>
            <div class="modal-body" style="padding:20px 20px 10px;">
                <div style="text-align:center; margin-bottom:18px;">
                    <div style="font-size:2.8rem; margin-bottom:10px;">📦</div>
                    <p style="font-weight:600; font-size:1.02rem; margin-bottom:6px;">¿Confirmas la finalización del
                        pedido?</p>
                    <p style="color:var(--muted); font-size:.86rem;">El pedido se marcará como terminado y desaparecerá
                        del tablero activo.</p>
                </div>

                <!-- SMS checkbox (dinámico) -->
                <?php if ($smsFinEnabled): ?>
                <div
                    style="background:rgba(99,102,241,0.08); border:1px solid rgba(99,102,241,0.15); border-radius:12px; padding:13px 15px; margin-bottom:10px;">
                    <label
                        style="display:flex; align-items:flex-start; gap:12px; cursor:pointer; font-size:.88rem; line-height:1.4;">
                        <input type="checkbox" id="chkSendSmsFin"
                            style="width:18px; height:18px; margin-top: 2px; accent-color:#6366f1;"
                            <?= $smsFinCheckedDefault ? 'checked' : '' ?>>
                        <span>Notificar al cliente por <strong>SMS</strong> que el pedido está listo</span>
                    </label>
                </div>
                <?php
else: ?>
                <input type="hidden" id="chkSendSmsFin" value="0">
                <?php
endif; ?>

                <!-- WhatsApp checkbox (dinámico) -->
                <?php if ($waActivo && $waFinEnabled): ?>
                <div
                    style="background:rgba(37,211,102,0.07); border:1px solid rgba(37,211,102,0.22); border-radius:12px; padding:13px 15px; margin-bottom:10px;">
                    <label
                        style="display:flex; align-items:flex-start; gap:12px; cursor:pointer; font-size:.88rem; line-height:1.4;">
                        <input type="checkbox" id="chkSendWaFin"
                            style="width:18px; height:18px; margin-top: 2px; accent-color:#25d366;" <?=($waCredsOk &&
        $waFinCheckedDefault) ? 'checked' : '' ?>>
                        <span>
                            Notificar al cliente por <strong style="color:#25d366;">WhatsApp</strong> que el pedido está
                            listo
                            <span
                                style="background:#25d366;color:#fff;border-radius:20px;padding:1px 7px;font-size:.72rem;font-weight:700;margin-left:4px;">💙
                                WA</span>
                            <?php if (!$waCredsOk): ?>
                            <br><small style="color:#f59e0b;">⚠ Configura las credenciales WhatsApp en Configuración
                                Avanzada</small>
                            <?php
    endif; ?>
                        </span>
                    </label>
                </div>
                <?php
else: ?>
                <input type="hidden" id="chkSendWaFin" value="0">
                <?php
endif; ?>
            </div>
            <div class="modal-footer">
                <button class="btn-modal ghost" onclick="cerrar('modalFinalizar')">Cancelar</button>
                <button class="btn-modal success" id="btnConfFinalizar" onclick="ejecutarFinalizacion()">Confirmar y
                    Finalizar</button>
            </div>
        </div>
    </div>

    <!-- Modal Editar Pedido -->
    <div class="modal-ovl" id="modalEditar" onclick="if(event.target===this) cerrar('modalEditar')">
        <div class="modal-box" style="max-width:550px;">
            <div class="modal-head">
                <div>
                    <h2>✏️ Editar Pedido</h2>
                    <div class="pid" id="editarPedidoID">#PED-0000</div>
                </div>
                <button class="modal-close" onclick="cerrar('modalEditar')">×</button>
            </div>
            <div class="modal-body">
                <form id="formEditar" onsubmit="guardarEdicion(event)">
                    <input type="hidden" id="editId">
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label>👤 Nombre del Cliente</label>
                                <input type="text" id="editCliente" class="form-control" required>
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label>✉️ Correo Electrónico</label>
                                <input type="email" id="editEmail" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label>📞 Teléfono</label>
                                <input type="text" id="editTelefono" class="form-control">
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group" id="groupEditEstadoPago">
                                <label>💵 Estado de Pago</label>
                                <select id="editEstadoPago" class="form-control">
                                    <option value="no_pago">No Pago</option>
                                    <option value="abono">Abono</option>
                                    <option value="pago_completo">Pago Completo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-row" id="groupEditMontos">
                        <div class="form-col">
                            <div class="form-group">
                                <label>💰 Total ($)</label>
                                <input type="number" id="editTotal" class="form-control">
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label>💳 Abonado ($)</label>
                                <input type="number" id="editAbonado" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label>🕒 Prioridad</label>
                                <select id="editPrioridad" class="form-control">
                                    <option value="normal">Normal</option>
                                    <option value="prioridad">Prioridad</option>
                                    <option value="largo">Largo</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-col"></div>
                    </div>
                    <div class="form-group" style="margin-bottom:12px;">
                        <label>📝 Notas del Pedido</label>
                        <textarea id="editDescripcion" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="form-group" style="margin-bottom:12px;">
                        <label>📎 Archivos Adjuntos Actuales</label>
                        <div id="editArchivosActuales"
                            style="margin-bottom:10px; font-size:0.85rem; display:flex; flex-direction:column; gap:5px;">
                        </div>
                        <label style="margin-top:10px;">Subir nuevos archivos</label>
                        <input type="file" id="editFileInput" class="form-control" multiple>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn-modal ghost" onclick="cerrar('modalEditar')">Cancelar</button>
                <button type="submit" form="formEditar" class="btn-modal primary">💾 Guardar Cambios</button>
            </div>
        </div>
    </div>

    <script>
        const basePath = window.location.pathname.replace(/\/kanban\/?$/i, '');
        const AREA_ID = <?= (int)$currentArea['id']?>;
        const USER_ROLE = <?= json_encode($role)?>;
        const CAN_VIEW_PRICES = <?= $canViewPrices ? 'true' : 'false'?>;
        const CAN_EDIT_ORDERS = <?= $canEditOrders ? 'true' : 'false'?>;

        var _pedido = null;
        var _areaDestino = null;

        /* ======= TOAST ======= */
        function showToast(msg, type) {
            var t = document.getElementById('toast');
            document.getElementById('toast-msg').textContent = msg;
            document.getElementById('toast-icon').textContent = type === 'success' ? '✅' : '⚠️';
            t.className = 'toast show ' + (type || 'success');
            setTimeout(function () { t.classList.remove('show'); }, 3500);
        }

        /* ======= BOARD ======= */
        async function loadBoard() {
            try {
                var r = await fetch(basePath + '/api/kanban/board/' + AREA_ID);
                var res = await r.json();
                if (res.status === 'success') renderBoard(res.data);
            } catch (e) { console.error(e); }
        }

        function renderBoard(data) {
            ['recepcion', 'proceso', 'preparado'].forEach(function (fase) {
                var col = document.getElementById('col-' + fase);
                col.innerHTML = '';
                var arr = data[fase] || [];
                document.getElementById('cnt-' + fase).textContent = arr.length;
                if (arr.length === 0) { col.innerHTML = '<div class="empty-col">No hay pedidos aquí</div>'; return; }
                arr.forEach(function (p) { col.appendChild(mkCard(p)); });
            });
        }

        function mkCard(p) {
            var div = document.createElement('div');
            div.className = 'card'; div.draggable = true; div.id = 'ped_' + p.id;
            div.ondragstart = (function (x) { return function (ev) { drag(ev, x.id, x.fase_actual); }; })(p);
            div.onclick = (function (x) { return function (e) { if (e.target.tagName !== 'BUTTON') verDet(x); }; })(p);

            var pc = 'no-pago', pl = 'Sin Pago';
            if (p.estado_pago === 'pago_completo') {
                pc = 'pago-completo';
                pl = CAN_VIEW_PRICES ? 'Pago Completo ($' + parseFloat(p.total || 0).toLocaleString() + ')' : 'Pago Completo';
            }
            else if (p.estado_pago === 'abono') {
                pc = 'abono';
                pl = CAN_VIEW_PRICES ? 'Abono ($' + parseFloat(p.abonado || 0).toLocaleString() + ')' : 'Abono';
            }
            else if (p.estado_pago === 'no_pago') {
                pc = 'no-pago';
                pl = (CAN_VIEW_PRICES && parseFloat(p.total)) ? 'No Pago ($' + parseFloat(p.total).toLocaleString() + ')' : 'No Pago';
            }
            // Badge de prioridad
            var prioClass, prioText;
            if (p.prioridad === 'prioridad') { prioClass = 'prioridad'; prioText = '⚠️ Prioridad'; }
            else if (p.prioridad === 'largo') { prioClass = 'largo'; prioText = '📅 Largo'; }
            else { prioClass = 'normal'; prioText = '🕒 Normal'; }
            var prioBadge = '<span class="badge ' + prioClass + '">' + prioText + '</span>';

            // Badge Editado
            var editBadge = '';
            if (p.fue_editado == 1) {
                editBadge = '<span class="badge" style="background:#8b5cf6;color:white;margin-left:5px;">✏️ Editado</span>';
            }

            // Clase en la card para borde de color de prioridad
            div.classList.add('prio-' + prioClass);

            // Clase de deadline (vencimiento de fecha_entrega_esperada)
            if (p.fecha_entrega_esperada) {
                var ahora = Date.now();
                var limite = new Date(p.fecha_entrega_esperada + 'T23:59:59').getTime();
                var diffHoras = (limite - ahora) / 3600000;
                if (diffHoras < -48) div.classList.add('deadline-critical');
                else if (diffHoras < 0) div.classList.add('deadline-overdue');
                else if (diffHoras <= 12) div.classList.add('deadline-soon');
            }
            // Botones
            var btns = '<div class="card-actions">';
            if (p.fase_actual === 'recepcion') btns += '<button class="btn-card btn-tomar" onclick="accion(' + p.id + ',\'tomar\');event.stopPropagation();">▶ Tomar</button>';
            else if (p.fase_actual === 'proceso') btns += '<button class="btn-card btn-preparar" onclick="accion(' + p.id + ',\'preparar\');event.stopPropagation();">✔ Preparado</button>';
            else if (p.fase_actual === 'preparado') {
                btns += '<button class="btn-card btn-finalizar" onclick="finalizar(' + p.id + ');event.stopPropagation();">✅ Finalizar</button>';
                btns += '<button class="btn-card btn-enviar" onclick="abrirEnviar(' + p.id + ');event.stopPropagation();">📤 Enviar</button>';
            }
            btns += '</div>';

            // Badge de fase (Trabajándolo o Preparado por)
            var faseBadge = '';
            if (p.fase_actual === 'proceso' && p.operador_asignado) {
                faseBadge = '<div style="margin-top:5px;"><span class="badge" style="background:#3b82f6;color:white;font-size:0.7rem;">🛠️ Trabajándolo: ' + p.operador_asignado + '</span></div>';
            } else if (p.fase_actual === 'preparado' && p.operador_asignado) {
                faseBadge = '<div style="margin-top:5px;"><span class="badge" style="background:#10b981;color:white;font-size:0.7rem;">✅ Preparado por: ' + p.operador_asignado + '</span></div>';
            }

            div.innerHTML = '<div style="display:flex;justify-content:space-between;align-items:flex-start;">'
                + '<div class="card-id">#PED-' + String(p.id).padStart(4, '0') + '</div>'
                + '<div style="text-align:right;"><span class="badge ' + pc + '">' + pl + '</span></div>'
                + '</div>'
                + '<div class="card-name">' + (p.cliente_nombre || '') + '</div>'
                + '<div style="margin:5px 0 8px;">' + prioBadge + editBadge + '</div>'
                + (faseBadge)
                + '<div class="card-meta"><span>🕒 ' + (p.last_movement_at || '').substring(0, 16) + '</span></div>'
                + btns;
            return div;
        }

        /* ======= DRAG ======= */
        function allowDrop(ev) { ev.preventDefault(); }
        function drag(ev, id, fase) { ev.dataTransfer.setData('id', id); ev.dataTransfer.setData('fase', fase); }
        function drop(ev, target) {
            ev.preventDefault();
            var id = ev.dataTransfer.getData('id'), fase = ev.dataTransfer.getData('fase');
            if (!id || fase === target) return;
            moverLibre(id, target);
        }
        async function moverLibre(id, fase) {
            var csrf = document.querySelector('meta[name="csrf-token"]').content;
            var r = await fetch(basePath + '/api/kanban/mover_libre', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ pedido_id: id, nueva_fase: fase, csrf_token: csrf }) });
            var res = await r.json();
            if (res.status === 'success') { if (window.BannerSounds) BannerSounds.mover(); showToast(res.message, 'success'); loadBoard(); }
            else showToast(res.message, 'error');
        }

        /* ======= ACCIONES ======= */
        async function accion(id, tipo) {
            var csrf = document.querySelector('meta[name="csrf-token"]').content;
            var ep = tipo === 'tomar' ? '/api/kanban/tomar' : '/api/kanban/finalizar-tarea';
            var r = await fetch(basePath + ep, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ pedido_id: id, csrf_token: csrf }) });
            var res = await r.json();
            if (res.status === 'success') { if (window.BannerSounds) BannerSounds.mover(); showToast(res.message, 'success'); loadBoard(); cerrar('modalDet'); }
            else showToast(res.message, 'error');
        }

        var _finalizarId = null;

        async function finalizar(id) {
            _finalizarId = id;
            document.getElementById('finLabel').textContent = '#PED-' + String(id).padStart(4, '0');
            abrir('modalFinalizar');
        }

        var _finalizarEnCurso = false;

        async function ejecutarFinalizacion() {
            if (!_finalizarId) return;
            if (_finalizarEnCurso) return; // bloqueo doble click

            var btn = document.getElementById('btnConfFinalizar');
            _finalizarEnCurso = true;
            if (btn) {
                btn.disabled = true;
                btn.style.opacity = '0.55';
                btn.style.cursor = 'not-allowed';
            }

            var csrf = document.querySelector('meta[name="csrf-token"]').content;
            var sendSms = document.getElementById('chkSendSmsFin').checked ? '1' : '0';
            var waChk = document.getElementById('chkSendWaFin');
            var sendWa = (waChk && waChk.checked) ? '1' : '0';

            try {
                var r = await fetch(basePath + '/api/kanban/finalizar', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ pedido_id: _finalizarId, send_sms: sendSms, send_whatsapp: sendWa, csrf_token: csrf })
                });
                var res = await r.json();
                cerrar('modalFinalizar');
                if (res.status === 'success') { if (window.BannerSounds) BannerSounds.finalizar(); showToast('Pedido finalizado ✅', 'success'); loadBoard(); cerrar('modalDet'); }
                else showToast(res.message, 'error');
            } catch (err) {
                showToast('Error de conexión al finalizar.', 'error');
            }

            // Bloqueo de 20 segundos con cuenta regresiva
            var segundos = 20;
            var intervalo = setInterval(function () {
                segundos--;
                if (btn) btn.textContent = 'Espera ' + segundos + 's...';
                if (segundos <= 0) {
                    clearInterval(intervalo);
                    _finalizarEnCurso = false;
                    if (btn) {
                        btn.disabled = false;
                        btn.style.opacity = '';
                        btn.style.cursor = '';
                        btn.textContent = 'Confirmar y Finalizar';
                    }
                }
            }, 1000);
        }

        /* ======= ENVIAR ÁREA ======= */
        function abrirEnviar(id) {
            _pedido = _pedido && _pedido.id === id ? _pedido : { id: id };
            _areaDestino = null;
            document.getElementById('btnConfEnvio').disabled = true;
            document.getElementById('envLabel').textContent = '#PED-' + String(id).padStart(4, '0');
            document.querySelectorAll('.area-sel-btn').forEach(function (b) { b.classList.remove('selected'); });
            cerrar('modalDet');
            abrir('modalEnviar');
        }
        function selArea(btn) {
            document.querySelectorAll('.area-sel-btn').forEach(function (b) { b.classList.remove('selected'); });
            btn.classList.add('selected');
            _areaDestino = btn.getAttribute('data-id');
            document.getElementById('btnConfEnvio').disabled = false;
        }
        async function ejecutarEnvio() {
            if (!_pedido || !_areaDestino) return;
            var csrf = document.querySelector('meta[name="csrf-token"]').content;
            var r = await fetch(basePath + '/api/kanban/enviar', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ pedido_id: _pedido.id, area_destino_id: parseInt(_areaDestino), csrf_token: csrf }) });
            var res = await r.json();
            if (res.status === 'success') { if (window.BannerSounds) BannerSounds.mover(); showToast(res.message, 'success'); loadBoard(); cerrar('modalEnviar'); }
            else showToast(res.message, 'error');
        }

        /* ======= MODAL DETALLES ======= */
        async function verDet(p) {
            _pedido = p;
            document.getElementById('detNombre').textContent = p.cliente_nombre || '–';
            document.getElementById('detId').textContent = '#PED-' + String(p.id).padStart(4, '0');

            var pc = 'no-pago', pl = '❌ Sin Pago';
            if (p.estado_pago === 'pago_completo') { pc = 'pago-completo'; pl = '✅ Pago Completo'; }
            else if (p.estado_pago === 'abono') { pc = 'abono'; pl = '💰 Abono'; }
            else if (p.estado_pago === 'no_pago' && CAN_VIEW_PRICES && parseFloat(p.total)) {
                pc = 'no-pago'; pl = '❌ No Pago ($' + parseFloat(p.total).toLocaleString() + ')';
            }
            var prioC = 'pago-completo', prioL = '🕒 Normal';
            if (p.prioridad === 'prioridad') { prioC = 'prioridad'; prioL = '⚠️ PRIORIDAD'; }
            else if (p.prioridad === 'largo') { prioC = 'largo'; prioL = '📅 LARGO'; }
            var faseColor = { recepcion: '#3b82f6', proceso: '#f59e0b', preparado: '#10b981' }[p.fase_actual] || '#6366f1';

            document.getElementById('detBadges').innerHTML =
                '<span class="badge ' + pc + '">' + pl + '</span>'
                + '<span class="badge ' + prioC + '">' + prioL + '</span>'
                + '<span style="background:' + faseColor + ';color:#fff;padding:3px 10px;border-radius:20px;font-size:.72rem;font-weight:700;display:inline-block;">📍 ' + p.fase_actual + '</span>';

            document.getElementById('detInfo').innerHTML =
                '<div class="detail-row"><span class="detail-lbl">📞 Teléfono</span><span class="detail-val">' + (p.cliente_telefono || 'No registrado') + '</span></div>'
                + '<div class="detail-row"><span class="detail-lbl">📧 Email</span><span class="detail-val">' + (p.cliente_email || 'No registrado') + '</span></div>'
                + '<div class="detail-row"><span class="detail-lbl" style="color:#60a5fa;">📅 Ingresado</span><span class="detail-val">' + (p.created_at || 'N/A') + '</span></div>'
                + '<div class="detail-row"><span class="detail-lbl" style="color:#fb923c;">⏳ Esperado</span><span class="detail-val">' + (p.fecha_entrega_esperada || '<i>Sin fecha</i>') + '</span></div>'
                + '<div class="detail-row"><span class="detail-lbl">🕒 Últ. Mov.</span><span class="detail-val">' + (p.last_movement_at || '').substring(0, 16) + '</span></div>';

            // Pago
            var pagoHTML = '<div class="sec-title">💳 Pago</div><div class="detail-block">';
            if (CAN_VIEW_PRICES) {
                var saldo = Number(p.total || 0) - Number(p.abonado || 0);
                pagoHTML += '<div class="detail-row"><span class="detail-lbl">Total</span><span class="detail-val">$' + Number(p.total || 0).toLocaleString() + '</span></div>'
                    + '<div class="detail-row"><span class="detail-lbl">Abonado</span><span class="detail-val">$' + Number(p.abonado || 0).toLocaleString() + '</span></div>'
                    + '<div class="detail-row"><span class="detail-lbl">Saldo</span><span class="pago-saldo">$' + saldo.toLocaleString() + '</span></div>';
            } else {
                pagoHTML += '<div style="text-align:center;padding:6px 0;"><span class="badge ' + pc + '" style="font-size:.9rem;padding:7px 16px;">' + pl + '</span></div>';
            }
            pagoHTML += '</div>';
            document.getElementById('detPago').innerHTML = pagoHTML;

            document.getElementById('detNotas').innerHTML = '<div class="sec-title">📝 Notas</div>'
                + '<div class="detail-block" style="color:#cbd5e1;font-size:.86rem;line-height:1.6;">'
                + (p.descripcion ? p.descripcion.replace(/\n/g, '<br>') : '<span style="color:#64748b;font-style:italic;">Sin notas</span>')
                + '</div>';

            // Archivos
            document.getElementById('detArchivos').innerHTML = '<div class="sec-title">📎 Archivos Adjuntos</div><div id="archBox"><span style="color:var(--muted);font-size:.8rem;">Cargando...</span></div>';
            cargarArchivos(p.id);

            // Botones del modal
            var btns = '';
            if (CAN_EDIT_ORDERS) {
                btns += '<button class="btn-modal purple" style="max-width:120px;" onclick="abrirEditar()">✏️ Editar</button>';
            }
            if (p.fase_actual === 'recepcion') btns += '<button class="btn-modal primary" onclick="accion(' + p.id + ',\'tomar\')">▶ Tomar Tarea</button>';
            else if (p.fase_actual === 'proceso') btns += '<button class="btn-modal warning" onclick="accion(' + p.id + ',\'preparar\')">✔ Marcar Preparado</button>';
            else if (p.fase_actual === 'preparado') {
                btns += '<button class="btn-modal success" onclick="finalizar(' + p.id + ')">✅ Finalizar</button>'
                    + '<button class="btn-modal purple" onclick="abrirEnviar(' + p.id + ')">📤 Enviar a Área</button>';
            }
            btns += '<button class="btn-modal ghost" onclick="cerrar(\'modalDet\')">Cerrar</button>';
            document.getElementById('detBotones').innerHTML = btns;

            abrir('modalDet');
        }

        async function cargarArchivos(id, targetBoxId = 'archBox', forEdit = false) {
            try {
                var r = await fetch(basePath + '/api/kanban/archivos/' + id);
                var res = await r.json();
                var box = document.getElementById(targetBoxId);
                if (!box) return;

                if (forEdit) {
                    window._archivos_eliminados = [];
                }

                if (res.status === 'success' && res.data && res.data.length > 0) {
                    var html = '';
                    res.data.forEach(function (f) {
                        var ext = f.nombre_archivo.split('.').pop().toLowerCase();
                        var ico = ['jpg', 'jpeg', 'png', 'gif'].indexOf(ext) >= 0 ? '🖼️' : ext === 'pdf' ? '📕' : '📄';

                        if (forEdit) {
                            html += '<div id="file_row_' + f.id + '" style="display:flex;align-items:center;padding:6px;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);border-radius:6px;margin-bottom:4px; justify-content:space-between;">';
                            html += '<a href="' + basePath + '/storage/uploads/' + f.ruta_almacenamiento + '" target="_blank" style="display:flex;align-items:center;gap:6px;color:#cbd5e1;text-decoration:none;font-size:0.85rem;"><span style="font-size:1.1rem;">' + ico + '</span> ' + f.nombre_archivo + '</a>';
                            html += '<button type="button" onclick="eliminarArchivoEdit(' + f.id + ')" style="background:none; border:none; color:#ef4444; font-weight:bold; cursor:pointer; font-size:1.1rem; line-height:1;">&times;</button>';
                            html += '</div>';
                        } else {
                            html += '<a href="' + basePath + '/storage/uploads/' + f.ruta_almacenamiento + '" target="_blank" style="display:flex;align-items:center;gap:8px;padding:8px 10px;background:rgba(99,102,241,.08);border:1px solid rgba(99,102,241,.2);border-radius:8px;margin-bottom:6px;text-decoration:none;color:var(--text);font-size:.82rem;">'
                                + ' <span>' + ico + '</span><span style="flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">' + f.nombre_archivo + '</span><span style="color:#6366f1;font-size:.72rem;">⬇ Ver</span></a>';
                        }
                    });
                    box.innerHTML = html;
                } else {
                    box.innerHTML = '<span style="color:#64748b;font-size:.8rem;font-style:italic;">Sin archivos adjuntos.</span>';
                }
            } catch (e) {
                var b = document.getElementById(targetBoxId);
                if (b) b.innerHTML = '<span style="color:#ef4444;font-size:.8rem;">Error al cargar.</span>';
            }
        }

        function eliminarArchivoEdit(fileId) {
            if (confirm('¿Eliminar este archivo?')) {
                window._archivos_eliminados.push(fileId);
                var row = document.getElementById('file_row_' + fileId);
                if (row) row.style.display = 'none';
            }
        }

        /* ===== EDITAR PEDIDO ===== */
        function abrirEditar() {
            if (!_pedido) return;
            var p = _pedido;
            document.getElementById('editId').value = p.id;
            document.getElementById('editarPedidoID').textContent = '#PED-' + String(p.id).padStart(4, '0');
            document.getElementById('editCliente').value = p.cliente_nombre || '';
            document.getElementById('editEmail').value = p.cliente_email || '';
            document.getElementById('editTelefono').value = p.cliente_telefono || '';
            document.getElementById('editDescripcion').value = p.descripcion || '';
            document.getElementById('editEstadoPago').value = p.estado_pago || 'no_pago';
            document.getElementById('editPrioridad').value = p.prioridad || 'normal';
            document.getElementById('editTotal').value = p.total || 0;
            document.getElementById('editAbonado').value = p.abonado || 0;
            document.getElementById('editFileInput').value = '';

            if (!CAN_VIEW_PRICES) {
                document.getElementById('groupEditEstadoPago').style.display = 'none';
                document.getElementById('groupEditMontos').style.display = 'none';
            } else {
                document.getElementById('groupEditEstadoPago').style.display = 'block';
                document.getElementById('groupEditMontos').style.display = 'flex';
            }

            document.getElementById('editArchivosActuales').innerHTML = '<span style="color:#64748b;font-size:0.8rem;">Cargando...</span>';
            cargarArchivos(p.id, 'editArchivosActuales', true);

            cerrar('modalDet');
            abrir('modalEditar');
        }

        async function guardarEdicion(e) {
            e.preventDefault();

            var editTotal = parseFloat(document.getElementById('editTotal').value) || 0;
            var editEstadoPago = document.getElementById('editEstadoPago').value;

            if (CAN_VIEW_PRICES) {
                if (editTotal <= 0 && editEstadoPago !== 'no_pago') {
                    showToast('Debe ingresar un monto total mayor a 0 para el pedido.', 'error');
                    return;
                }
                if (editEstadoPago === 'abono') {
                    var editAbonado = parseFloat(document.getElementById('editAbonado').value) || 0;
                    if (editAbonado <= 0) {
                        showToast('Debe ingresar el monto abonado mayor a 0.', 'error');
                        return;
                    }
                }
            }

            var csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            var formData = new FormData();
            formData.append('pedido_id', parseInt(document.getElementById('editId').value));
            formData.append('cliente_nombre', document.getElementById('editCliente').value);
            formData.append('cliente_email', document.getElementById('editEmail').value);
            formData.append('cliente_telefono', document.getElementById('editTelefono').value);
            formData.append('descripcion', document.getElementById('editDescripcion').value);
            if (CAN_VIEW_PRICES) {
                formData.append('estado_pago', document.getElementById('editEstadoPago').value);
                formData.append('total', parseFloat(document.getElementById('editTotal').value) || 0);
                formData.append('abonado', parseFloat(document.getElementById('editAbonado').value) || 0);
            }
            formData.append('prioridad', document.getElementById('editPrioridad').value);
            formData.append('csrf_token', csrfToken);

            if (window._archivos_eliminados && window._archivos_eliminados.length > 0) {
                formData.append('archivos_eliminados', JSON.stringify(window._archivos_eliminados));
            }

            var files = document.getElementById('editFileInput').files;
            if (files && files.length > 0) {
                for (let i = 0; i < files.length; i++) {
                    formData.append('archivos[]', files[i]);
                }
            }

            try {
                var r = await fetch(basePath + '/api/pedidos/editar', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    body: formData
                });
                var res = await r.json();
                if (res.status === 'success') {
                    cerrar('modalEditar');
                    showToast('Pedido actualizado correctamente.', 'success');
                    loadBoard();
                } else { showToast('Error: ' + res.message, 'error'); }
            } catch (err) { showToast('Error de red.', 'error'); }
        }

        function abrir(id) { document.getElementById(id).classList.add('open'); }
        function cerrar(id) { document.getElementById(id).classList.remove('open'); }

        async function logout() {
            var csrf = document.querySelector('meta[name="csrf-token"]').content;
            try { await fetch(basePath + '/api/logout', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ csrf_token: csrf }) }); } catch (e) { }
            window.location.href = basePath + '/login';
        }

        document.addEventListener('DOMContentLoaded', loadBoard);
        setInterval(loadBoard, 30000); // refresh cada 30s
    </script>
    <script>
        window.BANNER_SOUND_CFG = { enabled: <?= $sonidoHabilitado === '1' ? 'true' : 'false' ?>, theme: '<?= htmlspecialchars($sonidoTema)?>' };
    </script>
    <script src="<?= $basePath?>/js/sounds.js"></script>
</body>

</html>