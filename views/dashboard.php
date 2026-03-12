<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ./login');
    exit;
}

$role = $_SESSION['role'] ?? 'Operador';
$csrfToken = \App\Middlewares\CsrfMiddleware::generateToken();

require_once __DIR__ . '/../config/Database.php';
try {
    $db = \Config\Database::getInstance();
    $rows = $db->query("SELECT valor FROM configuracion WHERE clave = 'fondo_dashboard'")->fetch(\PDO::FETCH_ASSOC);
    $fondoDashboard = $rows['valor'] ?? '';
}
catch (\Exception $e) {
    $fondoDashboard = '';
}
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
if (empty($fondoDashboard)) {
    $fondoDashboard = $basePath . '/img/LEON.jpg';
}

// Cargar Listas completas para los modales de stat-cards
$statLists = [
    'recepcion' => $db->query("SELECT p.*, COALESCE(a.nombre, 'Guía Generada') AS area_nombre FROM pedidos p LEFT JOIN areas a ON p.area_actual_id = a.id WHERE p.area_actual_id IS NULL AND p.estado NOT IN ('cancelado','completado') ORDER BY p.last_movement_at DESC")->fetchAll(PDO::FETCH_ASSOC),
    'proceso' => $db->query("SELECT p.*, COALESCE(a.nombre, 'Guía Generada') AS area_nombre FROM pedidos p LEFT JOIN areas a ON p.area_actual_id = a.id WHERE p.area_actual_id IS NOT NULL AND p.estado NOT IN ('cancelado','completado') AND p.fase_actual != 'preparado' ORDER BY p.last_movement_at DESC")->fetchAll(PDO::FETCH_ASSOC),
    'preparados' => $db->query("SELECT p.*, COALESCE(a.nombre, 'Guía Generada') AS area_nombre FROM pedidos p LEFT JOIN areas a ON p.area_actual_id = a.id WHERE p.estado NOT IN ('cancelado','completado') AND p.fase_actual = 'preparado' ORDER BY p.last_movement_at DESC")->fetchAll(PDO::FETCH_ASSOC),
    'completados' => $db->query("SELECT p.*, COALESCE(a.nombre, 'Guía Generada') AS area_nombre FROM pedidos p LEFT JOIN areas a ON p.area_actual_id = a.id WHERE p.estado = 'completado' ORDER BY p.last_movement_at DESC")->fetchAll(PDO::FETCH_ASSOC),
    'entregados' => $db->query("SELECT p.*, COALESCE(a.nombre, 'Guía Generada') AS area_nombre FROM pedidos p LEFT JOIN areas a ON p.area_actual_id = a.id WHERE p.estado = 'completado' AND p.entregado = 1 ORDER BY p.last_movement_at DESC")->fetchAll(PDO::FETCH_ASSOC),
    'no_entregados' => $db->query("SELECT p.*, COALESCE(a.nombre, 'Guía Generada') AS area_nombre FROM pedidos p LEFT JOIN areas a ON p.area_actual_id = a.id WHERE p.estado != 'cancelado' AND (p.entregado IS NULL OR p.entregado = 0) ORDER BY p.last_movement_at DESC")->fetchAll(PDO::FETCH_ASSOC),
    'fin_no_entregados' => $db->query("SELECT p.*, COALESCE(a.nombre, 'Guía Generada') AS area_nombre FROM pedidos p LEFT JOIN areas a ON p.area_actual_id = a.id WHERE p.estado = 'completado' AND (p.entregado IS NULL OR p.entregado = 0) ORDER BY p.last_movement_at DESC")->fetchAll(PDO::FETCH_ASSOC),
    'pagados' => $db->query("SELECT p.*, COALESCE(a.nombre, 'Guía Generada') AS area_nombre FROM pedidos p LEFT JOIN areas a ON p.area_actual_id = a.id WHERE p.estado_pago = 'pago_completo' AND p.estado != 'cancelado' ORDER BY p.last_movement_at DESC")->fetchAll(PDO::FETCH_ASSOC),
    'no_pagados' => $db->query("SELECT p.*, COALESCE(a.nombre, 'Guía Generada') AS area_nombre FROM pedidos p LEFT JOIN areas a ON p.area_actual_id = a.id WHERE p.estado_pago = 'no_pago' AND p.estado != 'cancelado' ORDER BY p.last_movement_at DESC")->fetchAll(PDO::FETCH_ASSOC),
    'abonados' => $db->query("SELECT p.*, COALESCE(a.nombre, 'Guía Generada') AS area_nombre FROM pedidos p LEFT JOIN areas a ON p.area_actual_id = a.id WHERE p.estado_pago = 'abono' AND p.estado != 'cancelado' ORDER BY p.last_movement_at DESC")->fetchAll(PDO::FETCH_ASSOC),
    'caducados' => $db->query("SELECT p.*, COALESCE(a.nombre, 'Guía Generada') AS area_nombre FROM pedidos p LEFT JOIN areas a ON p.area_actual_id = a.id WHERE p.fecha_entrega_esperada IS NOT NULL AND p.fecha_entrega_esperada < CURDATE() AND p.estado NOT IN ('completado','cancelado') ORDER BY p.fecha_entrega_esperada ASC")->fetchAll(PDO::FETCH_ASSOC),
    'por_caducar' => $db->query("SELECT p.*, COALESCE(a.nombre, 'Guía Generada') AS area_nombre FROM pedidos p LEFT JOIN areas a ON p.area_actual_id = a.id WHERE p.fecha_entrega_esperada IS NOT NULL AND p.fecha_entrega_esperada >= CURDATE() AND TIMESTAMPDIFF(HOUR, NOW(), CONCAT(p.fecha_entrega_esperada,' 23:59:59')) <= 12 AND p.estado NOT IN ('completado','cancelado') ORDER BY p.fecha_entrega_esperada ASC")->fetchAll(PDO::FETCH_ASSOC),
    'pedidos_dia' => $db->query("SELECT p.*, COALESCE(a.nombre, 'Guía Generada') AS area_nombre FROM pedidos p LEFT JOIN areas a ON p.area_actual_id = a.id WHERE DATE(p.created_at) = CURDATE() ORDER BY p.created_at DESC")->fetchAll(PDO::FETCH_ASSOC),
    'pedidos_mes' => $db->query("SELECT p.*, COALESCE(a.nombre, 'Guía Generada') AS area_nombre FROM pedidos p LEFT JOIN areas a ON p.area_actual_id = a.id WHERE YEAR(p.created_at) = YEAR(CURDATE()) AND MONTH(p.created_at) = MONTH(CURDATE()) ORDER BY p.created_at DESC")->fetchAll(PDO::FETCH_ASSOC),
    'pedidos_total' => $db->query("SELECT p.*, COALESCE(a.nombre, 'Guía Generada') AS area_nombre FROM pedidos p LEFT JOIN areas a ON p.area_actual_id = a.id ORDER BY p.created_at DESC")->fetchAll(PDO::FETCH_ASSOC),
    'eliminados' => $db->query("SELECT p.*, COALESCE(a.nombre, 'Guía Generada') AS area_nombre FROM pedidos p LEFT JOIN areas a ON p.area_actual_id = a.id WHERE p.estado = 'cancelado' ORDER BY p.created_at DESC")->fetchAll(PDO::FETCH_ASSOC)
];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= $csrfToken?>">
    <title>Banner – Dashboard de Métricas</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --bg: #0f172a;
            --surface: rgba(30, 41, 59, 0.75);
            --surface2: rgba(15, 23, 42, 0.9);
            --border: rgba(255, 255, 255, 0.08);
            --primary: #6366f1;
            --text: #f1f5f9;
            --muted: #94a3b8;
            --recep: #3b82f6;
            --proc: #f59e0b;
            --prep: #10b981;
            --comp: #8b5cf6;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background: url('<?= strpos($fondoDashboard, ' data:image') === 0 ? $fondoDashboard : htmlspecialchars($fondoDashboard)?>') center center / cover no-repeat fixed #0f172a;
            color: var(--text);
            min-height: 100vh;
            display: flex;
        }

        /* Overlay para asegurar legibilidad */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.8) 0%, rgba(99, 102, 241, 0.3) 100%);
            z-index: -1;
        }

        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* ── Topbar ── */
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
            z-index: 100;
        }

        .topbar h1 {
            font-size: 1.05rem;
            font-weight: 700;
            letter-spacing: -.01em;
        }

        .topbar h1 span {
            color: var(--primary);
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .btn-logout {
            background: rgba(239, 68, 68, .1);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, .25);
            border-radius: 8px;
            padding: 7px 14px;
            font-size: .82rem;
            font-weight: 600;
            cursor: pointer;
            transition: background .2s;
        }

        .btn-logout:hover {
            background: rgba(239, 68, 68, .22);
        }

        .refresh-btn {
            background: rgba(99, 102, 241, .12);
            color: #a5b4fc;
            border: 1px solid rgba(99, 102, 241, .25);
            border-radius: 8px;
            padding: 7px 14px;
            font-size: .82rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: background .2s;
        }

        .refresh-btn:hover {
            background: rgba(99, 102, 241, .22);
        }

        .refresh-btn.spinning svg {
            animation: spin .7s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* ── Page content ── */
        .page-body {
            flex: 1;
            padding: 32px;
            overflow-y: auto;
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 800;
            margin-bottom: 6px;
            background: linear-gradient(135deg, #f1f5f9, #a5b4fc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .page-sub {
            color: var(--muted);
            font-size: .88rem;
            margin-bottom: 28px;
        }

        /* ── Summary Bar ── */
        .summary-bar {
            display: flex;
            gap: 12px;
            margin-bottom: 32px;
            flex-wrap: wrap;
        }

        .sum-pill {
            display: flex;
            align-items: center;
            gap: 8px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 10px 18px;
        }

        .sum-dot {
            width: 9px;
            height: 9px;
            border-radius: 50%;
        }

        .sum-label {
            font-size: .78rem;
            color: var(--muted);
        }

        .sum-val {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text);
            margin-left: 2px;
        }

        /* ── Grid de áreas ── */
        .areas-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        /* ── Tarjeta de área ── */
        .area-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 22px;
            cursor: pointer;
            transition: transform .2s, box-shadow .2s, border-color .2s;
            position: relative;
            overflow: hidden;
        }

        .area-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--card-accent, var(--primary));
            border-radius: 16px 16px 0 0;
        }

        .area-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 32px rgba(0, 0, 0, .35);
            border-color: rgba(255, 255, 255, .15);
        }

        .area-card-header {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 18px;
        }

        .area-icon {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            flex-shrink: 0;
        }

        .area-name {
            font-size: 1.05rem;
            font-weight: 700;
        }

        .area-link {
            margin-left: auto;
            font-size: .75rem;
            color: var(--primary);
            text-decoration: none;
            background: rgba(99, 102, 241, .12);
            border: 1px solid rgba(99, 102, 241, .2);
            border-radius: 7px;
            padding: 4px 10px;
            white-space: nowrap;
            transition: background .2s;
        }

        .area-link:hover {
            background: rgba(99, 102, 241, .22);
        }

        /* stat mini pills en la tarjeta */
        .area-stats {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .stat-pill {
            flex: 1;
            min-width: 70px;
            background: rgba(0, 0, 0, .18);
            border: 1px solid rgba(255, 255, 255, .06);
            border-radius: 10px;
            padding: 10px 12px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2px;
            cursor: pointer;
            transition: background .18s;
        }

        .stat-pill:hover {
            background: rgba(255, 255, 255, .06);
        }

        .stat-pill-num {
            font-size: 1.6rem;
            font-weight: 800;
            line-height: 1;
        }

        .stat-pill-lbl {
            font-size: .68rem;
            color: var(--muted);
            text-align: center;
        }

        .col-recep {
            color: var(--recep);
        }

        .col-proc {
            color: var(--proc);
        }

        .col-prep {
            color: var(--prep);
        }

        .col-comp {
            color: var(--comp);
        }

        /* Skeleton loader */
        .skeleton-card {
            background: var(--surface);
            border-radius: 16px;
            padding: 22px;
            border: 1px solid var(--border);
        }

        .skel {
            background: linear-gradient(90deg, rgba(255, 255, 255, .04) 25%, rgba(255, 255, 255, .09) 50%, rgba(255, 255, 255, .04) 75%);
            background-size: 200% 100%;
            animation: shine 1.4s infinite;
            border-radius: 8px;
        }

        @keyframes shine {
            to {
                background-position: -200% 0;
            }
        }

        .skel-h {
            height: 14px;
            margin-bottom: 10px;
        }

        .skel-row {
            display: flex;
            gap: 10px;
            margin-top: 18px;
        }

        .skel-box {
            flex: 1;
            height: 60px;
            border-radius: 10px;
        }

        /* ── MODAL ── */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .72);
            z-index: 1000;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .modal-overlay.open {
            display: flex;
        }

        .modal-box {
            background: #1e293b;
            border: 1px solid rgba(255, 255, 255, .1);
            border-radius: 18px;
            width: 100%;
            max-width: 680px;
            max-height: 88vh;
            display: flex;
            flex-direction: column;
            box-shadow: 0 30px 80px rgba(0, 0, 0, .5);
            animation: fadeUp .22s ease;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(18px);
            }
        }

        .modal-head {
            padding: 20px 26px 16px;
            border-bottom: 1px solid rgba(255, 255, 255, .07);
            display: flex;
            align-items: center;
            gap: 14px;
            flex-shrink: 0;
        }

        .modal-head-icon {
            font-size: 1.6rem;
        }

        .modal-head-info {
            flex: 1;
        }

        .modal-head-info h2 {
            font-size: 1.12rem;
            font-weight: 700;
        }

        .modal-head-info p {
            font-size: .8rem;
            color: var(--muted);
            margin-top: 2px;
        }

        .modal-close {
            background: none;
            border: none;
            color: var(--muted);
            font-size: 1.5rem;
            cursor: pointer;
            line-height: 1;
            padding: 0 4px;
        }

        .modal-close:hover {
            color: var(--text);
        }

        /* Tabs dentro del modal */
        .modal-tabs {
            display: flex;
            padding: 0 26px;
            gap: 4px;
            border-bottom: 1px solid rgba(255, 255, 255, .07);
            flex-shrink: 0;
        }

        .tab-btn {
            padding: 12px 16px;
            font-size: .83rem;
            font-weight: 600;
            border: none;
            background: none;
            cursor: pointer;
            color: var(--muted);
            border-bottom: 2px solid transparent;
            transition: color .2s, border-color .2s;
            white-space: nowrap;
        }

        .tab-btn.active {
            color: var(--text);
            border-bottom-color: var(--primary);
        }

        .tab-btn .tab-count {
            display: inline-block;
            background: rgba(255, 255, 255, .1);
            border-radius: 20px;
            padding: 1px 7px;
            font-size: .72rem;
            margin-left: 5px;
        }

        /* Lista de pedidos en modal */
        .modal-body {
            padding: 20px 26px;
            overflow-y: auto;
            flex: 1;
        }

        .pedido-row {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 14px;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, .06);
            background: rgba(0, 0, 0, .15);
            margin-bottom: 8px;
            transition: background .15s;
        }

        .pedido-row:hover {
            background: rgba(255, 255, 255, .04);
        }

        .ped-id {
            font-size: .75rem;
            color: var(--primary);
            font-weight: 700;
            min-width: 80px;
        }

        .ped-info {
            flex: 1;
            min-width: 0;
        }

        .ped-name {
            font-size: .88rem;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .ped-meta {
            font-size: .74rem;
            color: var(--muted);
            margin-top: 2px;
        }

        .pago-badge {
            font-size: .7rem;
            font-weight: 700;
            padding: 3px 9px;
            border-radius: 20px;
            color: white;
            flex-shrink: 0;
        }

        .pago-badge.pc {
            background: #10b981;
        }

        .pago-badge.ab {
            background: #f59e0b;
        }

        .pago-badge.np {
            background: #ef4444;
        }

        .prio-badge {
            font-size: .68rem;
            padding: 2px 7px;
            border-radius: 20px;
            font-weight: 700;
            flex-shrink: 0;
        }

        .prio-badge.p {
            background: rgba(239, 68, 68, .2);
            color: #f87171;
        }

        .prio-badge.l {
            background: rgba(99, 102, 241, .2);
            color: #a5b4fc;
        }

        .empty-modal {
            text-align: center;
            padding: 40px 20px;
            color: var(--muted);
        }

        .empty-modal .em-icon {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .empty-modal p {
            font-size: .88rem;
        }

        /* Loading de la tab */
        .tab-loading {
            text-align: center;
            padding: 32px;
            color: var(--muted);
        }

        /* Toast */
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
            <div style="display:flex; align-items:center; gap:16px;">
                <button id="btnHamburger"
                    style="background:none; border:none; color:#f1f5f9; cursor:pointer; font-size:1.5rem; display:flex;">☰</button>
                <h1>📊 <span>Métricas por Área</span></h1>
            </div>
            <div class="topbar-right">
                <button class="refresh-btn" id="btnRefresh" onclick="cargarMetricas()">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.5">
                        <path d="M23 4v6h-6" />
                        <path d="M1 20v-6h6" />
                        <path d="M3.51 9a9 9 0 0114.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0020.49 15" />
                    </svg>
                    Actualizar
                </button>
                <button class="btn-logout" onclick="logout()">Cerrar Sesión</button>
            </div>
        </header>

        <div class="page-body">
            <div class="page-title">Resumen de Pedidos</div>
            <p class="page-sub">Haz clic en cualquier métrica para ver sus pedidos detallados.</p>

            <!-- Barra de totales globales -->
            <div class="summary-bar" id="summaryBar">
                <div class="sum-pill">
                    <div class="sum-dot" style="background:var(--recep);"></div><span class="sum-label">En
                        Recepción</span><span class="sum-val" id="totRecep">–</span>
                </div>
                <div class="sum-pill">
                    <div class="sum-dot" style="background:var(--proc);"></div><span class="sum-label">En
                        Proceso</span><span class="sum-val" id="totProc">–</span>
                </div>
                <div class="sum-pill">
                    <div class="sum-dot" style="background:var(--prep);"></div><span
                        class="sum-label">Preparados</span><span class="sum-val" id="totPrep">–</span>
                </div>
                <div class="sum-pill">
                    <div class="sum-dot" style="background:var(--comp);"></div><span
                        class="sum-label">Completados</span><span class="sum-val" id="totComp">–</span>
                </div>
                <div class="sum-pill">
                    <div class="sum-dot" style="background:#3b82f6;"></div><span class="sum-label">Pedidos del
                        Día</span><span class="sum-val" id="totDia">–</span>
                </div>
                <div class="sum-pill">
                    <div class="sum-dot" style="background:#8b5cf6;"></div><span class="sum-label" id="lblMes">Pedidos
                        del
                        Mes</span><span class="sum-val" id="totMes">–</span>
                </div>
                <div class="sum-pill">
                    <div class="sum-dot" style="background:#ec4899;"></div><span class="sum-label">Total
                        Realizados</span><span class="sum-val" id="totAll">–</span>
                </div>
                <div class="sum-pill">
                    <div class="sum-dot" style="background:#ef4444;"></div><span
                        class="sum-label">Eliminados</span><span class="sum-val" id="totEliminados">–</span>
                </div>
                <div class="sum-pill">
                    <div class="sum-dot" style="background:#22c55e;"></div><span
                        class="sum-label">Entregados</span><span class="sum-val" id="totEntregados">–</span>
                </div>
                <div class="sum-pill">
                    <div class="sum-dot" style="background:#f97316;"></div><span class="sum-label">No
                        Entregados</span><span class="sum-val" id="totNoEntregados">–</span>
                </div>
                <div class="sum-pill" style="border-color:#fca5a5; background:rgba(254, 226, 226, 0.4);">
                    <div class="sum-dot" style="background:#ef4444;"></div><span class="sum-label">Finaliz. No
                        Entreg.</span><span class="sum-val" id="totFinNoEntregados" style="color:#ef4444;">–</span>
                </div>
                <div class="sum-pill">
                    <div class="sum-dot" style="background:#10b981;"></div><span class="sum-label">Pagados</span><span
                        class="sum-val" id="totPagados">–</span>
                </div>
                <div class="sum-pill">
                    <div class="sum-dot" style="background:#f43f5e;"></div><span class="sum-label">No
                        Pagados</span><span class="sum-val" id="totNoPagados">–</span>
                </div>
                <div class="sum-pill">
                    <div class="sum-dot" style="background:#0ea5e9;"></div><span class="sum-label">Abonados</span><span
                        class="sum-val" id="totAbonados">–</span>
                </div>
                <div class="sum-pill" style="border-color:#fed7aa; background:rgba(255, 237, 213, 0.5);">
                    <div class="sum-dot" style="background:#f97316;"></div><span class="sum-label">Por
                        Caducar</span><span class="sum-val" id="totPorCaducar" style="color:#f97316;">–</span>
                </div>
                <div class="sum-pill" style="border-color:#fca5a5; background:rgba(254, 226, 226, 0.4);">
                    <div class="sum-dot" style="background:#ef4444;"></div><span class="sum-label">Caducados</span><span
                        class="sum-val" id="totCaducados" style="color:#ef4444;">–</span>
                </div>
            </div>

            <!-- Cuadrícula de áreas -->
            <div class="areas-grid" id="areasGrid">
                <!-- Skeleton mientras carga -->
                <?php for ($i = 0; $i < 6; $i++): ?>
                <div class="skeleton-card">
                    <div class="skel skel-h" style="width:60%;"></div>
                    <div class="skel skel-h" style="width:40%; height:10px;"></div>
                    <div class="skel-row">
                        <div class="skel skel-box"></div>
                        <div class="skel skel-box"></div>
                        <div class="skel skel-box"></div>
                        <div class="skel skel-box"></div>
                    </div>
                </div>
                <?php
endfor; ?>
            </div>
        </div>
    </div>

    <!-- ====== MODAL DETALLE ÁREA ====== -->
    <div class="modal-overlay" id="modalArea" onclick="if(event.target===this) cerrarModal()">
        <div class="modal-box">
            <div class="modal-head">
                <span class="modal-head-icon" id="mAreaIcon">📦</span>
                <div class="modal-head-info">
                    <h2 id="mAreaNombre">Área</h2>
                    <p id="mAreaSub">Pedidos del área</p>
                </div>
                <button class="modal-close" onclick="cerrarModal()">×</button>
            </div>

            <div class="modal-tabs">
                <button class="tab-btn active" id="tabRecep" onclick="cambiarTab('recepcion', this)">
                    📥 Recepción <span class="tab-count" id="tcRecep">0</span>
                </button>
                <button class="tab-btn" id="tabProc" onclick="cambiarTab('proceso', this)">
                    ⚙️ En Proceso <span class="tab-count" id="tcProc">0</span>
                </button>
                <button class="tab-btn" id="tabPrep" onclick="cambiarTab('preparado', this)">
                    ✅ Preparado <span class="tab-count" id="tcPrep">0</span>
                </button>
                <button class="tab-btn" id="tabComp" onclick="cambiarTab('completado', this)">
                    🏁 Finalizado <span class="tab-count" id="tcComp">0</span>
                </button>
            </div>

            <div class="modal-body" id="modalBody">
                <div class="tab-loading">Cargando pedidos...</div>
            </div>
        </div>
    </div>

    <!-- Toast -->
    <div class="toast" id="toastEl"><span id="toastMsg">OK</span></div>

    <script>
        const basePath = window.location.pathname.replace(/\/dashboard\/?$/i, '');
        const USER_ROLE = <?= json_encode($role)?>;

        // Paletas de colores para las áreas (cicla automáticamente)
        const ACCENTS = ['#6366f1', '#10b981', '#f59e0b', '#3b82f6', '#ec4899', '#8b5cf6', '#14b8a6', '#f97316'];
        const ICONS = ['🎨', '✂️', '🖨️', '🪡', '📦', '🔧', '⭐', '🏷️', '🎯', '💎'];

        var _areaActual = null;
        var _tabActual = 'recepcion';
        var _cacheAreas = null;
        var _cachePedidos = {}; // area_id + fase => data

        /* ========= CARGAR MÉTRICAS ========= */
        async function cargarMetricas() {
            var btn = document.getElementById('btnRefresh');
            btn.classList.add('spinning');
            _cachePedidos = {}; // invalidar cache

            try {
                var r = await fetch(basePath + '/api/dashboard/areas');
                var res = await r.json();
                if (res.status !== 'success') throw new Error(res.message);
                _cacheAreas = res.data;
                renderAreas(res.data, res.global);
            } catch (e) {
                showToast('Error cargando métricas: ' + e.message, 'error');
            } finally {
                btn.classList.remove('spinning');
            }
        }

        /* ========= RENDER ÁREAS ========= */
        function renderAreas(areas, globalMetrics) {
            var grid = document.getElementById('areasGrid');
            grid.innerHTML = '';

            var totals = { recepcion: 0, proceso: 0, preparado: 0, completado: 0 };

            if (areas.length === 0) {
                grid.innerHTML = '<div style="grid-column:1/-1;text-align:center;color:var(--muted);padding:60px;">No hay áreas activas configuradas.</div>';
                return;
            }

            areas.forEach(function (area, idx) {
                var accent = ACCENTS[idx % ACCENTS.length];
                var icon = area.icono || ICONS[idx % ICONS.length];

                totals.recepcion += parseInt(area.cnt_recepcion || 0);
                totals.proceso += parseInt(area.cnt_proceso || 0);
                totals.preparado += parseInt(area.cnt_preparado || 0);
                totals.completado += parseInt(area.cnt_completado || 0);

                var totalActivos = parseInt(area.cnt_recepcion || 0) + parseInt(area.cnt_proceso || 0) + parseInt(area.cnt_preparado || 0);

                var card = document.createElement('div');
                card.className = 'area-card';
                card.style.setProperty('--card-accent', accent);
                card.setAttribute('data-area-id', area.id);
                card.setAttribute('data-area-nombre', area.nombre);
                card.setAttribute('data-area-icon', icon);

                card.innerHTML =
                    '<div class="area-card-header">' +
                    '<div class="area-icon" style="background:' + accent + '22; border:1px solid ' + accent + '44;">' + icon + '</div>' +
                    '<div>' +
                    '<div class="area-name">' + area.nombre + '</div>' +
                    '<div style="font-size:.74rem;color:var(--muted);margin-top:2px;">' + totalActivos + ' pedido' + (totalActivos !== 1 ? 's' : '') + ' activo' + (totalActivos !== 1 ? 's' : '') + '</div>' +
                    '</div>' +
                    '<a class="area-link" href="' + basePath + '/kanban?area_id=' + area.id + '" onclick="event.stopPropagation();" title="Ir al área">🗂 Ir al área</a>' +
                    '</div>' +
                    '<div class="area-stats">' +
                    '<div class="stat-pill" onclick="abrirModal(' + area.id + ', \'' + area.nombre + '\', \'' + icon + '\', \'recepcion\')" title="En Recepción">' +
                    '<span class="stat-pill-num col-recep">' + (area.cnt_recepcion || 0) + '</span>' +
                    '<span class="stat-pill-lbl">Recepción</span>' +
                    '</div>' +
                    '<div class="stat-pill" onclick="abrirModal(' + area.id + ', \'' + area.nombre + '\', \'' + icon + '\', \'proceso\')" title="En Proceso">' +
                    '<span class="stat-pill-num col-proc">' + (area.cnt_proceso || 0) + '</span>' +
                    '<span class="stat-pill-lbl">En Proceso</span>' +
                    '</div>' +
                    '<div class="stat-pill" onclick="abrirModal(' + area.id + ', \'' + area.nombre + '\', \'' + icon + '\', \'preparado\')" title="Preparados">' +
                    '<span class="stat-pill-num col-prep">' + (area.cnt_preparado || 0) + '</span>' +
                    '<span class="stat-pill-lbl">Preparado</span>' +
                    '</div>' +
                    '<div class="stat-pill" onclick="abrirModal(' + area.id + ', \'' + area.nombre + '\', \'' + icon + '\', \'completado\')" title="Finalizados">' +
                    '<span class="stat-pill-num col-comp">' + (area.cnt_completado || 0) + '</span>' +
                    '<span class="stat-pill-lbl">Finalizado</span>' +
                    '</div>' +
                    '</div>';

                // Click en la tarjeta completa => abrir modal en pestaña recepción
                card.addEventListener('click', function (e) {
                    if (e.target.closest('.stat-pill') || e.target.closest('.area-link')) return;
                    abrirModal(area.id, area.nombre, icon, 'recepcion');
                });

                grid.appendChild(card);
            });

            // Totales globales
            document.getElementById('totRecep').textContent = totals.recepcion;
            document.getElementById('totProc').textContent = totals.proceso;
            document.getElementById('totPrep').textContent = totals.preparado;
            document.getElementById('totComp').textContent = totals.completado;

            if (globalMetrics) {
                document.getElementById('totDia').textContent = globalMetrics.pedidos_dia || 0;
                document.getElementById('totMes').textContent = globalMetrics.pedidos_mes || 0;
                document.getElementById('totAll').textContent = globalMetrics.pedidos_total || 0;
                document.getElementById('totEliminados').textContent = globalMetrics.pedidos_eliminados || 0;
                document.getElementById('totEntregados').textContent = globalMetrics.pedidos_entregados || 0;
                document.getElementById('totNoEntregados').textContent = globalMetrics.pedidos_no_entregados || 0;
                document.getElementById('totFinNoEntregados').textContent = globalMetrics.pedidos_fin_no_entregados || 0;
                document.getElementById('totPagados').textContent = globalMetrics.pedidos_pagados || 0;
                document.getElementById('totNoPagados').textContent = globalMetrics.pedidos_no_pagados || 0;
                document.getElementById('totAbonados').textContent = globalMetrics.pedidos_abonados || 0;
                document.getElementById('totPorCaducar').textContent = globalMetrics.pedidos_por_caducar || 0;
                document.getElementById('totCaducados').textContent = globalMetrics.pedid0;

                if (globalMetrics.mes_actual_nombre) {
                    document.getElementById('lblMes').textContent = 'Pedidos de ' + globalMetrics.mes_actual_nombre;
                }
            }
        }

        /* ========= MODAL ========= */
        async function abrirModal(areaId, nombre, icon, fase) {
            _areaActual = areaId;
            _tabActual = fase;

            // Cabecera
            document.getElementById('mAreaIcon').textContent = icon || '📦';
            document.getElementById('mAreaNombre').textContent = nombre;

            // Contar tabs con datos de cache si ya tenemos el área
            actualizarTabCounts(areaId);

            // Activar tab correspondiente
            document.querySelectorAll('.tab-btn').forEach(function (b) { b.classList.remove('active'); });
            var tabId = { recepcion: 'tabRecep', proceso: 'tabProc', preparado: 'tabPrep', completado: 'tabComp' }[fase] || 'tabRecep';
            document.getElementById(tabId).classList.add('active');

            // Abrir overlay
            document.getElementById('modalArea').classList.add('open');

            // Cargar pedidos
            await cargarPedidos(areaId, fase);
        }

        function actualizarTabCounts(areaId) {
            if (!_cacheAreas) return;
            var area = _cacheAreas.find(function (a) { return parseInt(a.id) === parseInt(areaId); });
            if (!area) return;
            document.getElementById('tcRecep').textContent = area.cnt_recepcion || 0;
            document.getElementById('tcProc').textContent = area.cnt_proceso || 0;
            document.getElementById('tcPrep').textContent = area.cnt_preparado || 0;
            document.getElementById('tcComp').textContent = area.cnt_completado || 0;

            var subTexts = { recepcion: 'pedidos en espera', proceso: 'en producción', preparado: 'listos para envío', completado: 'finalizados' };
            document.getElementById('mAreaSub').textContent = (area.cnt_recepcion || 0) + ' recepción · ' + (area.cnt_proceso || 0) + ' proceso · ' + (area.cnt_preparado || 0) + ' preparado';
        }

        async function cambiarTab(fase, btn) {
            _tabActual = fase;
            document.querySelectorAll('.tab-btn').forEach(function (b) { b.classList.remove('active'); });
            btn.classList.add('active');
            await cargarPedidos(_areaActual, fase);
        }

        async function cargarPedidos(areaId, fase) {
            var cacheKey = areaId + '_' + fase;

            // Check cache (30s TTL)
            if (_cachePedidos[cacheKey] && (Date.now() - _cachePedidos[cacheKey].ts < 30000)) {
                renderPedidos(_cachePedidos[cacheKey].data, fase);
                return;
            }

            document.getElementById('modalBody').innerHTML = '<div class="tab-loading">⏳ Cargando pedidos...</div>';

            try {
                var r = await fetch(basePath + '/api/dashboard/area/' + areaId + '/pedidos?fase=' + fase);
                var res = await r.json();
                if (res.status !== 'success') throw new Error(res.message);
                _cachePedidos[cacheKey] = { data: res.data, ts: Date.now() };
                renderPedidos(res.data, fase);
            } catch (e) {
                document.getElementById('modalBody').innerHTML = '<div class="empty-modal"><div class="em-icon">⚠️</div><p>Error: ' + e.message + '</p></div>';
            }
        }

        function renderPedidos(pedidos, fase) {
            var body = document.getElementById('modalBody');
            if (!pedidos || pedidos.length === 0) {
                var faseLabels = { recepcion: 'recepción', proceso: 'proceso', preparado: 'preparado', completado: 'finalizado' };
                body.innerHTML = '<div class="empty-modal"><div class="em-icon">📭</div><p>No hay pedidos en <strong>' + (faseLabels[fase] || fase) + '</strong>.</p></div>';
                return;
            }

            var html = '';
            pedidos.forEach(function (p) {
                var padId = '#PED-' + String(p.id).padStart(4, '0');
                var pagoClass = 'np', pagoLbl = 'Sin Pago';
                if (p.estado_pago === 'pago_completo') { pagoClass = 'pc'; pagoLbl = 'Pago Completo'; }
                else if (p.estado_pago === 'abono') { pagoClass = 'ab'; pagoLbl = 'Abono'; }

                // Si el usuario es Admin mostramos monto; si no, solo el badge
                var montoHTML = '';
                if (((USER_ROLE === 'Admin' || USER_ROLE === 'SuperAdmin') || USER_ROLE === 'SuperAdmin')) {
                    if (p.estado_pago === 'pago_completo') {
                        montoHTML = '<span style="font-size:.72rem;color:#10b981;font-weight:600;">$' + Number(p.total || 0).toLocaleString() + '</span>';
                    } else if (p.estado_pago === 'abono') {
                        var saldo = Number(p.total || 0) - Number(p.abonado || 0);
                        montoHTML = '<span style="font-size:.72rem;color:#f59e0b;font-weight:600;">Ab: $' + Number(p.abonado || 0).toLocaleString() + ' · Saldo: $' + saldo.toLocaleString() + '</span>';
                    } else if (p.estado_pago === 'no_pago' && parseFloat(p.total) > 0) {
                        montoHTML = '<span style="font-size:.72rem;color:#ef4444;font-weight:600;">Deuda: $' + Number(p.total || 0).toLocaleString() + '</span>';
                    }
                }

                var prioHTML = '';
                if (p.prioridad === 'prioridad') prioHTML = '<span class="prio-badge p">⚠️ PRIO</span>';
                else if (p.prioridad === 'largo') prioHTML = '<span class="prio-badge l">📅 LARGO</span>';

                var fecha = (p.last_movement_at || p.created_at || '').substring(0, 16);

                html += '<div class="pedido-row" style="cursor:pointer;" onclick="window.location.href=\'' + basePath + '/kanban?area_id=' + _areaActual + '\'" title="Ir al kanban del área">'
                    + '<div class="ped-id">' + padId + '</div>'
                    + '<div class="ped-info">'
                    + '<div class="ped-name">' + (p.cliente_nombre || '–') + '</div>'
                    + '<div class="ped-meta">' + fecha + (p.cliente_telefono ? ' · ' + p.cliente_telefono : '') + '</div>'
                    + (montoHTML ? '<div style="margin-top:3px;">' + montoHTML + '</div>' : '')
                    + '</div>'
                    + (prioHTML ? prioHTML : '')
                    + '<span class="pago-badge ' + pagoClass + '">' + pagoLbl + '</span>'
                    + '</div>';
            });
            body.innerHTML = html;
        }

        function cerrarModal() {
            document.getElementById('modalArea').classList.remove('open');
        }

        /* ========= TOAST ========= */
        function showToast(msg, type) {
            var t = document.getElementById('toastEl');
            document.getElementById('toastMsg').textContent = msg;
            t.className = 'toast show ' + (type || 'success');
            setTimeout(function () { t.classList.remove('show'); }, 3500);
        }

        /* ========= LOGOUT ========= */
        async function logout() {
            var csrf = document.querySelector('meta[name="csrf-token"]').content;
            try { await fetch(basePath + '/api/logout', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ csrf_token: csrf }) }); }
            catch (e) { }
            window.location.href = basePath + '/login';
        }

        /* ========= INIT ========= */
        document.addEventListener('DOMContentLoaded', cargarMetricas);

        // Auto-refresh cada 2 minutos
        setInterval(function () {
            if (!document.getElementById('modalArea').classList.contains('open')) {
                cargarMetricas();
            }
        }, 120000);

        // Silent Check para Auto-Backup diario (Solo admin, no bloquea ni alerta a menos que la consola lo solicite)
        if (((USER_ROLE === 'Admin' || USER_ROLE === 'SuperAdmin') || USER_ROLE === 'SuperAdmin')) {
            setTimeout(function () {
                var csrf = document.querySelector('meta[name="csrf-token"]').content;
                fetch(basePath + '/api/config/check-autobackup', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ csrf_token: csrf })
                }).catch(e => { }); // Silent fail
            }, 3000); // 3 segundos después de cargar la página inicial
        }
    </script>
</body>

</html>