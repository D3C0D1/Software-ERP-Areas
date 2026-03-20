<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['Admin', 'SuperAdmin'])) {
    header('Location: ./login'); exit;
}
require_once __DIR__ . '/../config/Database.php';
$db = \Config\Database::getInstance();
$csrfToken = \App\Middlewares\CsrfMiddleware::generateToken();

// ── Background ───────────────────────────────────────────────────────────────
try {
    $fRow = $db->query("SELECT valor FROM configuracion WHERE clave = 'fondo_dashboard'")->fetch(\PDO::FETCH_ASSOC);
    $fondoDashboard = $fRow['valor'] ?? '';
} catch (\Exception $e) { $fondoDashboard = ''; }
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
if (empty($fondoDashboard)) $fondoDashboard = $basePath . '/img/LEON.jpg';

// ── PERÍODO ─────────────────────────────────────────────────────────────────
// periodo: hoy | semana | mes | custom (desde+hasta)
$periodo = $_GET['periodo'] ?? 'hoy';
$desde = $_GET['desde'] ?? date('Y-m-d');
$hasta = $_GET['hasta'] ?? date('Y-m-d');

// Normalizar fechas
$desde = preg_match('/^\d{4}-\d{2}-\d{2}$/', $desde) ? $desde : date('Y-m-d');
$hasta = preg_match('/^\d{4}-\d{2}-\d{2}$/', $hasta) ? $hasta : date('Y-m-d');

switch ($periodo) {
    case 'hoy':
        $desde = date('Y-m-d');
        $hasta = date('Y-m-d');
        $periodoLabel = 'Hoy, ' . date('d/m/Y');
        break;
    case 'semana':
        // Lunes de la semana actual
        $desde = date('Y-m-d', strtotime('monday this week'));
        $hasta = date('Y-m-d');
        $periodoLabel = 'Esta semana (' . date('d/m', strtotime($desde)) . ' - ' . date('d/m') . ')';
        break;
    case 'mes':
        $desde = date('Y-m-01');
        $hasta = date('Y-m-d');
        $periodoLabel = 'Este mes (' . date('F Y') . ')';
        break;
    case 'custom':
    default:
        $periodoLabel = date('d/m/Y', strtotime($desde)) . ' → ' . date('d/m/Y', strtotime($hasta));
        break;
}

// Cálculo unificado de ingresos
$statsRes = $db->query("
    SELECT 
        COALESCE(SUM(h.monto), 0) as total_periodo,
        COALESCE(SUM(CASE WHEN h.metodo_pago='efectivo' OR h.metodo_pago IS NULL OR h.metodo_pago='' THEN h.monto ELSE 0 END), 0) as total_efectivo,
        COALESCE(SUM(CASE WHEN h.metodo_pago='transferencia' THEN h.monto ELSE 0 END), 0) as total_transferencia
    FROM historial_pagos h
    JOIN pedidos p ON p.id = h.pedido_id
    WHERE DATE(h.fecha_pago) BETWEEN '$desde' AND '$hasta'
      AND p.estado != 'cancelado'
")->fetch(\PDO::FETCH_ASSOC);

$ingresosPeriodo = (float)$statsRes['total_periodo'];
$ingresosEfectivo = (float)$statsRes['total_efectivo'];
$ingresosTransferencia = (float)$statsRes['total_transferencia'];


// Comparar con período anterior de la misma duración
$diffDays = max(1, (int)((strtotime($hasta) - strtotime($desde)) / 86400) + 1);
$prevDesde = date('Y-m-d', strtotime($desde) - $diffDays * 86400);
$prevHasta = date('Y-m-d', strtotime($desde) - 86400);
$ingresosAnt = (float)$db->query(
    "SELECT COALESCE(SUM(h.monto),0) FROM historial_pagos h
     JOIN pedidos p ON p.id = h.pedido_id
     WHERE DATE(h.fecha_pago) BETWEEN '$prevDesde' AND '$prevHasta'
       AND p.estado != 'cancelado'"
)->fetchColumn();
$ingresosDelta = $ingresosAnt > 0 ? (($ingresosPeriodo - $ingresosAnt) / $ingresosAnt) * 100 : 0;

// Cuentas por cobrar (siempre global, no por período)
$cxcTotal = (float)$db->query(
    "SELECT COALESCE(SUM(total-abonado),0) FROM pedidos
     WHERE estado!='cancelado' AND estado_pago!='pago_completo' AND total>0"
)->fetchColumn();
$cxcCount = (int)$db->query(
    "SELECT COUNT(*) FROM pedidos WHERE estado!='cancelado' AND estado_pago!='pago_completo' AND total>0"
)->fetchColumn();

// Facturado en período (total facturado = SUM(total) pedidos CREADOS en período)
$facturadoPeriodo = (float)$db->query(
    "SELECT COALESCE(SUM(total),0) FROM pedidos
     WHERE DATE(created_at) BETWEEN '$desde' AND '$hasta' AND estado!='cancelado'"
)->fetchColumn();
$facturadoMes = (float)$db->query(
    "SELECT COALESCE(SUM(total),0) FROM pedidos
     WHERE YEAR(created_at)=YEAR(CURDATE()) AND MONTH(created_at)=MONTH(CURDATE()) AND estado!='cancelado'"
)->fetchColumn();
$facturacionMes = $facturadoMes; // alias para compatibilidad con el HTML
$cxcPeriodo = $facturadoPeriodo - $ingresosPeriodo;

// Cartera en Riesgo (siempre global)
$carteraRiesgo = (float)$db->query(
    "SELECT COALESCE(SUM(total-abonado),0) FROM pedidos
     WHERE estado!='cancelado' AND estado_pago='no_pago' AND total>0 AND created_at < NOW()-INTERVAL 7 DAY"
)->fetchColumn();
$pedidosEnRiesgo = (int)$db->query(
    "SELECT COUNT(*) FROM pedidos
     WHERE estado!='cancelado' AND estado_pago='no_pago' AND total>0 AND created_at < NOW()-INTERVAL 7 DAY"
)->fetchColumn();

// ── Gráfico: comparativa por período ─────────────────────────────────────────
// Generar hasta 30 puntos dentro del período seleccionado
$chartLabels = [];
$chartFact   = [];
$chartRec    = [];
$startTs = strtotime($desde);
$endTs   = strtotime($hasta);
$totalDias = (int)(($endTs - $startTs) / 86400) + 1;
// Si hay muchos días, agrupar por semana; si <= 31, por día
$groupBy = ($totalDias <= 31) ? 'day' : ($totalDias <= 90 ? 'week' : 'month');

if ($groupBy === 'day') {
    for ($ts = $startTs; $ts <= $endTs; $ts += 86400) {
        $d = date('Y-m-d', $ts);
        $chartLabels[] = date('d/m', $ts);
        $chartFact[] = (float)$db->query("SELECT COALESCE(SUM(total),0) FROM pedidos WHERE DATE(created_at)='$d' AND estado!='cancelado'")->fetchColumn();
        $chartRec[] = (float)$db->query("SELECT COALESCE(SUM(h.monto),0) FROM historial_pagos h JOIN pedidos p ON p.id=h.pedido_id WHERE DATE(h.fecha_pago)='$d' AND p.estado!='cancelado'")->fetchColumn();
    }
} elseif ($groupBy === 'week') {
    $cur = $startTs;
    while ($cur <= $endTs) {
        $wEnd = min($cur + 6*86400, $endTs);
        $d1 = date('Y-m-d', $cur);
        $d2 = date('Y-m-d', $wEnd);
        $chartLabels[] = date('d/m', $cur);
        $chartFact[] = (float)$db->query("SELECT COALESCE(SUM(total),0) FROM pedidos WHERE DATE(created_at) BETWEEN '$d1' AND '$d2' AND estado!='cancelado'")->fetchColumn();
        $chartRec[] = (float)$db->query("SELECT COALESCE(SUM(h.monto),0) FROM historial_pagos h JOIN pedidos p ON p.id=h.pedido_id WHERE DATE(h.fecha_pago) BETWEEN '$d1' AND '$d2' AND p.estado!='cancelado'")->fetchColumn();
        $cur += 7*86400;
    }
} else {
    // By month
    $curY = (int)date('Y', $startTs);
    $curM = (int)date('m', $startTs);
    while (mktime(0,0,0,$curM,1,$curY) <= $endTs) {
        $d1 = sprintf('%04d-%02d-01', $curY, $curM);
        $d2 = date('Y-m-t', mktime(0,0,0,$curM,1,$curY));
        $chartLabels[] = date('M/y', mktime(0,0,0,$curM,1,$curY));
        $chartFact[] = (float)$db->query("SELECT COALESCE(SUM(total),0) FROM pedidos WHERE DATE(created_at) BETWEEN '$d1' AND '$d2' AND estado!='cancelado'")->fetchColumn();
        $chartRec[] = (float)$db->query("SELECT COALESCE(SUM(h.monto),0) FROM historial_pagos h JOIN pedidos p ON p.id=h.pedido_id WHERE DATE(h.fecha_pago) BETWEEN '$d1' AND '$d2' AND p.estado!='cancelado'")->fetchColumn();
        $curM++;
        if ($curM > 12) { $curM = 1; $curY++; }
    }
}

// ── Gráfico Semanal Fijo (Lun-Dom semana actual) ─────────────────────────────
$weekDays   = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
$weekFact   = [];
$weekPagos  = [];

$chartWeek = $_GET['chart_week'] ?? '';
// If user selected a specific date from the calendar picker:
if (!empty($chartWeek) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $chartWeek)) {
    // Get the monday of the selected date's week
    $weekMonday = strtotime('monday this week', strtotime($chartWeek));
    $displayDate = $chartWeek;
} else if (!empty($chartWeek) && preg_match('/^\d{4}-W\d{2}$/', $chartWeek)) {
    // Fallback for previous week format if present in URL
    $parts = explode('-W', $chartWeek);
    $weekMonday = strtotime($parts[0] . 'W' . $parts[1]);
    $displayDate = date('Y-m-d', $weekMonday);
} else {
    $weekMonday = strtotime('monday this week');
    $displayDate = date('Y-m-d', $weekMonday);
}

for ($i = 0; $i < 7; $i++) {
    $wDate = date('Y-m-d', $weekMonday + $i * 86400);
    $weekFact[]  = (float)$db->query("SELECT COALESCE(SUM(total),0) FROM pedidos WHERE DATE(created_at)='$wDate' AND estado!='cancelado'")->fetchColumn();
    $weekPagos[] = (float)$db->query("SELECT COALESCE(SUM(h.monto),0) FROM historial_pagos h JOIN pedidos p ON p.id=h.pedido_id WHERE DATE(h.fecha_pago)='$wDate' AND p.estado!='cancelado'")->fetchColumn();
}

// ── Deudas antiguas ───────────────────────────────────────────────────────────
$deudas = $db->query(
    "SELECT id, cliente_nombre, estado, entregado, estado_pago, total, abonado,
            created_at, DATEDIFF(NOW(), created_at) AS dias_atraso
     FROM pedidos
     WHERE estado!='cancelado' AND estado_pago!='pago_completo'
       AND (total-abonado)>0 AND created_at < NOW()-INTERVAL 3 DAY
     ORDER BY dias_atraso DESC LIMIT 15"
)->fetchAll(\PDO::FETCH_ASSOC);

// ── Datos para modales de KPI ─────────────────────────────────────────────────
// Modal 1: Pedidos con pago registrado en el período
$modalIngresos = $db->query(
    "SELECT id, cliente_nombre, estado_pago, total, abonado, fecha_pago AS updated_at
     FROM pedidos
     WHERE DATE(fecha_pago) BETWEEN '$desde' AND '$hasta'
       AND estado_pago IN ('pago_completo','abono') AND estado!='cancelado'
     ORDER BY fecha_pago DESC"
)->fetchAll(\PDO::FETCH_ASSOC);

// Modal 2: Cuentas por cobrar (pendientes de pago)
$modalCxC = $db->query(
    "SELECT id, cliente_nombre, estado_pago, total, abonado, created_at,
            DATEDIFF(NOW(), created_at) AS dias
     FROM pedidos
     WHERE estado!='cancelado' AND estado_pago!='pago_completo' AND total>0
     ORDER BY (total-abonado) DESC LIMIT 100"
)->fetchAll(\PDO::FETCH_ASSOC);

// Modal 3: Cartera en riesgo
$modalRiesgo = $db->query(
    "SELECT id, cliente_nombre, estado_pago, total, abonado, created_at,
            DATEDIFF(NOW(), created_at) AS dias
     FROM pedidos
     WHERE estado!='cancelado' AND estado_pago='no_pago'
       AND total>0 AND created_at < NOW()-INTERVAL 7 DAY
     ORDER BY DATEDIFF(NOW(), created_at) DESC"
)->fetchAll(\PDO::FETCH_ASSOC);

// Modal 4: Pedidos facturados en el período
$modalFacturado = $db->query(
    "SELECT id, cliente_nombre, estado_pago, total, abonado, created_at
     FROM pedidos
     WHERE DATE(created_at) BETWEEN '$desde' AND '$hasta' AND estado!='cancelado'
     ORDER BY created_at DESC LIMIT 100"
)->fetchAll(\PDO::FETCH_ASSOC);

// ── Últimos pagos del período (Única fuente: historial_pagos) ─────────────────────
$pagos = $db->query(
    "SELECT h.pedido_id AS id, p.cliente_nombre, 
            (CASE WHEN h.observacion LIKE '%inicial%' THEN 'abono' ELSE 'abono_hist' END) AS tipo_pago,
            h.monto, h.metodo_pago, h.fecha_pago AS fecha_mov, 
            (CASE WHEN h.observacion LIKE '%inicial%' THEN 'Abono Inicial' ELSE 'Abono Parcial' END) AS origen,
            u.nombre AS registrado_por
     FROM historial_pagos h
     JOIN pedidos p ON p.id = h.pedido_id
     LEFT JOIN usuarios u ON u.id = h.usuario_id
     WHERE DATE(h.fecha_pago) BETWEEN '$desde' AND '$hasta'
       AND p.estado != 'cancelado'
     ORDER BY h.fecha_pago DESC LIMIT 60"
)->fetchAll(\PDO::FETCH_ASSOC);

// ── Extras (globales, no filtradas por período) ───────────────────────────────
$noPagados     = (int)$db->query("SELECT COUNT(*) FROM pedidos WHERE estado_pago='no_pago' AND estado!='cancelado' AND total>0")->fetchColumn();
$noPagadosSum  = (float)$db->query("SELECT COALESCE(SUM(total),0) FROM pedidos WHERE estado_pago='no_pago' AND estado!='cancelado' AND total>0")->fetchColumn();
$conAbono      = (int)$db->query("SELECT COUNT(*) FROM pedidos WHERE estado_pago='abono' AND estado!='cancelado'")->fetchColumn();
$sumaRestanteAbono = (float)$db->query("SELECT COALESCE(SUM(total-abonado),0) FROM pedidos WHERE estado_pago='abono' AND estado!='cancelado'")->fetchColumn();

// ── Modal data para mini-stats (filtradas por período seleccionado) ───────────
$modalNoPagados = $db->query(
    "SELECT id, cliente_nombre, total, abonado, estado, created_at,
            DATEDIFF(NOW(), created_at) AS dias
     FROM pedidos
     WHERE estado_pago='no_pago' AND estado!='cancelado' AND total>0
       AND DATE(created_at) BETWEEN '$desde' AND '$hasta'
     ORDER BY total DESC LIMIT 200"
)->fetchAll(\PDO::FETCH_ASSOC);

$modalConAbono = $db->query(
    "SELECT id, cliente_nombre, total, abonado, estado, created_at,
            DATEDIFF(NOW(), created_at) AS dias
     FROM pedidos
     WHERE estado_pago='abono' AND estado!='cancelado'
       AND DATE(created_at) BETWEEN '$desde' AND '$hasta'
     ORDER BY (total-abonado) DESC LIMIT 200"
)->fetchAll(\PDO::FETCH_ASSOC);

$modalRecaudo = $db->query(
    "SELECT h.id, h.pedido_id, p.cliente_nombre, h.monto, h.metodo_pago,
            h.observacion, h.fecha_pago,
            u.nombre AS registrado_por
     FROM historial_pagos h
     JOIN pedidos p ON p.id = h.pedido_id
     LEFT JOIN usuarios u ON u.id = h.usuario_id
     WHERE DATE(h.fecha_pago) BETWEEN '$desde' AND '$hasta'
       AND p.estado != 'cancelado'
     ORDER BY h.fecha_pago DESC LIMIT 200"
)->fetchAll(\PDO::FETCH_ASSOC);

function fmt($n) { return '$' . number_format($n, 0, ',', '.'); }
function pct($val, $max) { return $max > 0 ? min(round(($val / $max) * 100), 100) : 0; }
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= $csrfToken ?>">
    <title>Banner – Contabilidad y Finanzas</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script
        src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
    <style>
        :root {
            --bg: #0f172a;
            --surface: rgba(30, 41, 59, .72);
            --border: rgba(255, 255, 255, .07);
            --primary: #6366f1;
            --accent: #10b981;
            --danger: #f43f5e;
            --warn: #f59e0b;
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
            background: url('<?= strpos($fondoDashboard,' data:image')===0 ? $fondoDashboard : htmlspecialchars($fondoDashboard) ?>') center/cover no-repeat fixed #0f172a;
            color: var(--text);
            min-height: 100vh;
            display: flex;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background: linear-gradient(135deg, rgba(15, 23, 42, .92) 0%, rgba(16, 185, 129, .08) 100%);
            z-index: -1;
        }

        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Topbar */
        .topbar {
            height: 62px;
            padding: 0 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(15, 23, 42, .75);
            border-bottom: 1px solid var(--border);
            backdrop-filter: blur(14px);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .topbar h1 {
            font-size: 1.05rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--text);
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-sm {
            background: rgba(255, 255, 255, .07);
            color: var(--text);
            border: 1px solid var(--border);
            padding: 7px 14px;
            border-radius: 8px;
            font-size: .83rem;
            font-weight: 600;
            cursor: pointer;
            transition: .2s;
        }

        .btn-sm:hover {
            background: rgba(255, 255, 255, .12);
        }

        .btn-danger {
            background: rgba(244, 63, 94, .1);
            color: #f87171;
            border-color: rgba(244, 63, 94, .25);
        }

        /* Body */
        .page-body {
            padding: 22px 28px;
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
        }

        .page-header {
            margin-bottom: 22px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 14px;
        }

        .page-title {
            font-size: 1.6rem;
            font-weight: 800;
            background: linear-gradient(90deg, #10b981, #34d399, #6ee7b7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 4px;
        }

        .page-sub {
            color: var(--muted);
            font-size: .88rem;
        }

        /* ── Período Selector ─────────────────────────────────────────── */
        .period-bar {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .period-btn {
            padding: 6px 14px;
            border-radius: 20px;
            border: 1px solid var(--border);
            background: rgba(255, 255, 255, .05);
            color: var(--muted);
            font-size: .82rem;
            font-weight: 600;
            cursor: pointer;
            transition: .2s;
            text-decoration: none;
        }

        .period-btn:hover {
            background: rgba(255, 255, 255, .1);
            color: var(--text);
        }

        .period-btn.active {
            background: var(--accent);
            color: #fff;
            border-color: var(--accent);
            box-shadow: 0 0 12px rgba(16, 185, 129, .3);
        }

        .period-sep {
            color: var(--border);
            font-size: 1.2rem;
        }

        .date-inputs {
            display: flex;
            gap: 6px;
            align-items: center;
        }

        .date-inputs input[type=date] {
            background: rgba(255, 255, 255, .07);
            border: 1px solid var(--border);
            color: var(--text);
            padding: 5px 10px;
            border-radius: 8px;
            font-size: .8rem;
            outline: none;
            colorscheme: dark;
        }

        .date-inputs input[type=date]:focus {
            border-color: var(--accent);
        }

        .period-badge {
            background: rgba(16, 185, 129, .1);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, .2);
            padding: 4px 10px;
            border-radius: 8px;
            font-size: .78rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        /* ── KPI Grid ─────────────────────────────────────────────────── */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 20px;
        }

        .kpi-card {
            background: rgba(30, 41, 59, .65);
            border: 1px solid rgba(255, 255, 255, .06);
            border-radius: 16px;
            padding: 20px;
            backdrop-filter: blur(12px);
            position: relative;
            overflow: hidden;
            transition: transform .3s, box-shadow .3s;
        }

        .kpi-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 24px -10px rgba(0, 0, 0, .5);
            border-color: rgba(255, 255, 255, .1);
        }

        .kpi-bg {
            position: absolute;
            right: -14px;
            bottom: -14px;
            opacity: .04;
            transform: rotate(-15deg);
            pointer-events: none;
        }

        .kpi-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 14px;
        }

        .kpi-lbl {
            font-size: .76rem;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: var(--muted);
            font-weight: 600;
        }

        .kpi-ico {
            width: 38px;
            height: 38px;
            border-radius: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .ico-g {
            background: rgba(16, 185, 129, .15);
            color: #10b981;
        }

        .ico-b {
            background: rgba(99, 102, 241, .15);
            color: #6366f1;
        }

        .ico-y {
            background: rgba(245, 158, 11, .15);
            color: #f59e0b;
        }

        .ico-r {
            background: rgba(244, 63, 94, .15);
            color: #f43f5e;
        }

        .kpi-val {
            font-size: 1.85rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 6px;
            letter-spacing: -.02em;
        }

        .kpi-sub {
            font-size: .8rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .up {
            color: #10b981;
        }

        .dn {
            color: #f43f5e;
        }

        .nt {
            color: var(--muted);
        }

        .kpi-bar {
            height: 3px;
            background: rgba(255, 255, 255, .07);
            border-radius: 4px;
            margin-top: 10px;
            overflow: hidden;
        }

        .kpi-bar-fill {
            height: 100%;
            border-radius: 4px;
        }

        /* ── Main Grid ─────────────────────────────────────────────────── */
        .main-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 16px;
            margin-bottom: 20px;
        }

        .panel {
            background: rgba(30, 41, 59, .65);
            border: 1px solid rgba(255, 255, 255, .06);
            border-radius: 16px;
            backdrop-filter: blur(12px);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .panel-hd {
            padding: 16px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, .05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .panel-title {
            font-size: .95rem;
            font-weight: 600;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .panel-bd {
            padding: 20px;
            flex: 1;
        }

        /* ── Chart ─────────────────────────────────────────────────────── */
        .chart-legend {
            display: flex;
            gap: 16px;
            font-size: .78rem;
            color: var(--muted);
        }

        .legend-dot {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 2px;
            margin-right: 4px;
        }

        canvas#weekChart {
            width: 100% !important;
            height: 230px !important;
        }

        /* ── Weekly Projection Chart ──────────────────────────────────── */
        .weekly-chart-panel {
            background: rgba(15, 23, 42, 0.72);
            border: 1px solid rgba(255, 255, 255, .07);
            border-radius: 18px;
            backdrop-filter: blur(14px);
            overflow: hidden;
            margin-bottom: 20px;
        }

        .weekly-chart-header {
            padding: 18px 24px 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .weekly-chart-title {
            font-size: 1rem;
            font-weight: 700;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 9px;
        }

        .weekly-chart-legend {
            display: flex;
            gap: 20px;
            font-size: .8rem;
            color: var(--muted);
        }

        .weekly-chart-legend span {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .wl-dot {
            width: 12px;
            height: 12px;
            border-radius: 3px;
        }

        .weekly-chart-body {
            padding: 4px 20px 24px;
            height: 280px;
            position: relative;
        }

        canvas#weeklyProjectionChart {
            width: 100% !important;
            height: 100% !important;
        }

        /* ── Debt List ─────────────────────────────────────────────────── */
        .debt-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 9px;
        }

        .debt-item {
            background: rgba(0, 0, 0, .2);
            border: 1px solid rgba(244, 63, 94, .1);
            border-radius: 10px;
            padding: 13px 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: .2s;
            gap: 12px;
        }

        .debt-item:hover {
            background: rgba(0, 0, 0, .35);
            border-color: rgba(244, 63, 94, .3);
        }

        .d-client {
            font-size: .88rem;
            font-weight: 600;
            color: #fff;
        }

        .d-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: .68rem;
            font-weight: 700;
            background: rgba(244, 63, 94, .15);
            color: #f43f5e;
            margin-left: 6px;
        }

        .d-badge.warn {
            background: rgba(245, 158, 11, .15);
            color: #f59e0b;
        }

        .d-meta {
            font-size: .76rem;
            color: var(--muted);
            margin-top: 2px;
        }

        .d-amount {
            color: #f43f5e;
            font-weight: 700;
            font-size: .95rem;
            white-space: nowrap;
        }

        .d-total {
            font-size: .72rem;
            color: var(--muted);
            margin-top: 1px;
        }

        .d-status {
            display: inline-block;
            padding: 1px 6px;
            border-radius: 4px;
            font-size: .68rem;
            font-weight: 700;
        }

        .s-ent {
            background: rgba(16, 185, 129, .15);
            color: #10b981;
        }

        .s-nent {
            background: rgba(245, 158, 11, .15);
            color: #f59e0b;
        }

        /* ── Stats row ─────────────────────────────────────────────────── */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 20px;
        }

        .stat-mini {
            background: rgba(30, 41, 59, .65);
            border: 1px solid rgba(255, 255, 255, .06);
            border-radius: 14px;
            padding: 18px 20px;
            display: flex;
            align-items: center;
            gap: 18px;
            backdrop-filter: blur(10px);
        }

        .stat-mini-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .stat-mini-lbl {
            font-size: .76rem;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: var(--muted);
            font-weight: 600;
        }

        .stat-mini-val {
            font-size: 1.5rem;
            font-weight: 700;
            color: #fff;
            margin: .3rem 0;
        }

        .stat-mini-sub {
            font-size: .78rem;
        }

        /* ── Pago Table ─────────────────────────────────────────────────── */
        .pago-table {
            width: 100%;
            border-collapse: collapse;
            font-size: .86rem;
        }

        .pago-table th {
            padding: 11px 14px;
            color: var(--muted);
            font-weight: 600;
            text-align: left;
            background: rgba(255, 255, 255, .02);
            border-bottom: 1px solid rgba(255, 255, 255, .05);
            font-size: .78rem;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .pago-table td {
            padding: 11px 14px;
            border-bottom: 1px solid rgba(255, 255, 255, .04);
        }

        .pago-table tr:hover td {
            background: rgba(255, 255, 255, .02);
        }

        .badge-tp {
            padding: 3px 8px;
            border-radius: 6px;
            font-size: .69rem;
            font-weight: 700;
        }

        .bp-full {
            background: rgba(16, 185, 129, .15);
            color: #10b981;
        }

        .bp-abono {
            background: rgba(99, 102, 241, .15);
            color: #a5b4fc;
        }

        /* ── Responsive ─── */
        @media(max-width:1100px) {
            .kpi-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .main-grid {
                grid-template-columns: 1fr;
            }

            .stats-row {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media(max-width:680px) {
            .kpi-grid {
                grid-template-columns: 1fr;
            }

            .page-body {
                padding: 14px;
            }

            .stats-row {
                grid-template-columns: 1fr;
            }
        }

        /* ── Modals ─────────────────────────────────────────────────────── */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(5px);
            z-index: 1000;
            display: none;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .modal-overlay.active {
            display: flex;
            opacity: 1;
        }

        .modal-box {
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 16px;
            width: 100%;
            max-width: 800px;
            max-height: 85vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            transform: translateY(20px);
            transition: transform 0.3s;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        .modal-overlay.active .modal-box {
            transform: translateY(0);
        }

        .modal-header {
            padding: 18px 24px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(255, 255, 255, 0.03);
        }

        .modal-title {
            font-weight: 700;
            font-size: 1.1rem;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .modal-close {
            background: none;
            border: none;
            color: var(--muted);
            cursor: pointer;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: 0.2s;
        }

        .modal-close:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
        }

        .modal-body {
            padding: 0;
            overflow-y: auto;
            flex: 1;
        }
    </style>
</head>

<body>
    <?php include __DIR__ . '/components/sidebar.php'; ?>

    <div class="main-content">
        <header class="topbar">
            <h1>
                <svg width="20" height="20" fill="none" stroke="#10b981" stroke-width="2.5" viewBox="0 0 24 24">
                    <path d="M3 3v18h18" />
                    <path d="M18 17V9" />
                    <path d="M13 17V5" />
                    <path d="M8 17v-3" />
                </svg>
                Dashboard Contable &amp; Financiero
            </h1>
            <div class="topbar-right">
                <button class="btn-sm" onclick="location.reload()">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"
                        style="vertical-align:middle;margin-right:4px;">
                        <path d="M23 4v6h-6M1 20v-6h6" />
                        <path d="M3.51 9a9 9 0 0114.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0020.49 15" />
                    </svg>Actualizar
                </button>
                <button class="btn-sm btn-danger"
                    onclick="window.location.href='<?= rtrim($basePath,'/') ?>/dashboard'">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"
                        style="vertical-align:middle;margin-right:4px;">
                        <path d="M19 12H5M12 19l-7-7 7-7" />
                    </svg>Volver
                </button>
            </div>
        </header>

        <div class="page-body">

            <!-- Header + period selector -->
            <div class="page-header">
                <div>
                    <h2 class="page-title">Resumen Financiero</h2>
                    <p class="page-sub">Datos reales · Sin modificación de la base de datos ·
                        <?= date('d/m/Y H:i') ?>
                    </p>
                </div>

                <form method="GET" id="periodoForm"
                    style="display:flex;flex-direction:column;align-items:flex-end;gap:10px;">
                    <div class="period-bar">
                        <a href="?periodo=hoy" class="period-btn <?= $periodo==='hoy'?'active':'' ?>">Hoy</a>
                        <a href="?periodo=semana" class="period-btn <?= $periodo==='semana'?'active':'' ?>">Esta
                            Semana</a>
                        <a href="?periodo=mes" class="period-btn <?= $periodo==='mes'?'active':'' ?>">Este Mes</a>
                        <span class="period-sep">|</span>
                        <span style="font-size:.8rem;color:var(--muted);">Personalizado:</span>
                        <div class="date-inputs">
                            <input type="date" name="desde" id="inputDesde"
                                value="<?= $periodo==='custom' ? $desde : date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>">
                            <span style="color:var(--muted);font-size:.8rem;">→</span>
                            <input type="date" name="hasta" id="inputHasta"
                                value="<?= $periodo==='custom' ? $hasta : date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>">
                            <input type="hidden" name="periodo" value="custom">
                            <button type="submit" class="period-btn <?= $periodo==='custom'?'active':'' ?>"
                                style="border-radius:8px;">
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5"
                                    viewBox="0 0 24 24" style="vertical-align:middle;margin-right:3px;">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                                    <line x1="16" y1="2" x2="16" y2="6" />
                                    <line x1="8" y1="2" x2="8" y2="6" />
                                    <line x1="3" y1="10" x2="21" y2="10" />
                                </svg>Filtrar
                            </button>
                        </div>
                    </div>
                    <div class="period-badge">
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10" />
                            <polyline points="12 6 12 12 16 14" />
                        </svg>
                        <?= htmlspecialchars($periodoLabel) ?>
                    </div>
                </form>
            </div>

            <!-- KPI cards -->
            <div class="kpi-grid">

                <!-- Ingresos del Período -->
                <div class="kpi-card" style="cursor:pointer;" onclick="openModal('modal_ingresos')">
                    <div class="kpi-bg">
                        <svg width="90" height="90" fill="none" stroke="#10b981" stroke-width="1.5" viewBox="0 0 24 24">
                            <path d="M12 2v20M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6" />
                        </svg>
                    </div>
                    <div class="kpi-head">
                        <div>
                            <div class="kpi-lbl">Ingresos del Período</div>
                            <div style="font-size:.72rem;color:var(--muted);margin-top:2px;">Pago completo + abonos
                                recibidos</div>
                        </div>
                        <div class="kpi-ico ico-g">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5"
                                viewBox="0 0 24 24">
                                <path d="M12 2v20M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6" />
                            </svg>
                        </div>
                    </div>
                    <div class="kpi-val">
                        <?= fmt($ingresosPeriodo) ?>
                    </div>
                    <div class="kpi-sub <?= $ingresosDelta >= 0 ? 'up' : 'dn' ?>">
                        <?php if ($ingresosAnt > 0): ?>
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5"
                            viewBox="0 0 24 24">
                            <?= $ingresosDelta >= 0 ? '<path d="M12 19V5M5 12l7-7 7 7"/>' : '<path d="M12 5v14M5 12l7 7 7-7"/>' ?>
                        </svg>
                        <?= abs(round($ingresosDelta,1)) ?>% vs período anterior
                        <?php else: ?>
                        <span class="nt">💵 Ef: <?= fmt($ingresosEfectivo) ?> · 🏦 Tr: <?= fmt($ingresosTransferencia) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="kpi-bar">
                        <div class="kpi-bar-fill"
                            style="width:<?= pct($ingresosPeriodo, max($facturacionMes,1)) ?>%;background:#10b981;">
                        </div>
                    </div>
                </div>

                <!-- Cuentas por Cobrar (global) -->
                <div class="kpi-card" style="cursor:pointer;" onclick="openModal('modal_cxc')">
                    <div class="kpi-bg">
                        <svg width="90" height="90" fill="none" stroke="#f59e0b" stroke-width="1.5" viewBox="0 0 24 24">
                            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
                            <polyline points="14 2 14 8 20 8" />
                            <line x1="16" y1="13" x2="8" y2="13" />
                            <line x1="16" y1="17" x2="8" y2="17" />
                        </svg>
                    </div>
                    <div class="kpi-head">
                        <div>
                            <div class="kpi-lbl">Cuentas por Cobrar</div>
                            <div style="font-size:.72rem;color:var(--muted);margin-top:2px;">Total pendiente de recaudo
                                (global)</div>
                        </div>
                        <div class="kpi-ico ico-y">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5"
                                viewBox="0 0 24 24">
                                <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
                                <polyline points="14 2 14 8 20 8" />
                            </svg>
                        </div>
                    </div>
                    <div class="kpi-val">
                        <?= fmt($cxcTotal) ?>
                    </div>
                    <div class="kpi-sub dn">
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5"
                            viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10" />
                            <line x1="12" y1="8" x2="12" y2="12" />
                            <line x1="12" y1="16" x2="12.01" y2="16" />
                        </svg>
                        <?= $cxcCount ?> pedidos pendientes de cobro
                    </div>
                    <div class="kpi-bar">
                        <div class="kpi-bar-fill"
                            style="width:<?= pct($cxcTotal, max($facturacionMes,1)) ?>%;background:#f59e0b;"></div>
                    </div>
                </div>

                <!-- Cuentas por Cobrar del Período -->
                <div class="kpi-card" style="cursor:pointer;" onclick="openModal('modal_cxc_periodo')">
                    <div class="kpi-bg">
                        <svg width="90" height="90" fill="none" stroke="#6366f1" stroke-width="1.5" viewBox="0 0 24 24">
                            <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" />
                            <circle cx="12" cy="7" r="4" />
                        </svg>
                    </div>
                    <div class="kpi-head">
                        <div>
                            <div class="kpi-lbl">CxC del Período</div>
                            <div style="font-size:.72rem;color:var(--muted);margin-top:2px;">Facturado - Ingresos del
                                período</div>
                        </div>
                        <div class="kpi-ico ico-b">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5"
                                viewBox="0 0 24 24">
                                <path d="M12 2v20M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6" />
                            </svg>
                        </div>
                    </div>
                    <div class="kpi-val">
                        <?= fmt($cxcPeriodo) ?>
                    </div>
                    <div class="kpi-sub <?= $cxcPeriodo >= 0 ? 'dn' : 'up' ?>">
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5"
                            viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10" />
                            <polyline points="12 6 12 12 16 14" />
                        </svg>
                        Balance neto del período
                    </div>
                    <div class="kpi-bar">
                        <div class="kpi-bar-fill"
                            style="width:<?= pct(abs($cxcPeriodo), max($facturadoPeriodo,1)) ?>%;background:#6366f1;">
                        </div>
                    </div>
                </div>

                <!-- Facturado del Período -->
                <div class="kpi-card" style="cursor:pointer;" onclick="openModal('modal_facturado')">
                    <div class="kpi-bg">
                        <svg width="90" height="90" fill="none" stroke="#6366f1" stroke-width="1.5" viewBox="0 0 24 24">
                            <path d="M21.21 15.89A10 10 0 118 2.83M22 12A10 10 0 0012 2v10z" />
                        </svg>
                    </div>
                    <div class="kpi-head">
                        <div>
                            <div class="kpi-lbl">Facturado en Período</div>
                            <div style="font-size:.72rem;color:var(--muted);margin-top:2px;">Valor de pedidos creados
                            </div>
                        </div>
                        <div class="kpi-ico ico-b">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5"
                                viewBox="0 0 24 24">
                                <path d="M21.21 15.89A10 10 0 118 2.83M22 12A10 10 0 0012 2v10z" />
                            </svg>
                        </div>
                    </div>
                    <div class="kpi-val">
                        <?= fmt($facturadoPeriodo) ?>
                    </div>
                    <div class="kpi-sub nt">
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <rect x="3" y="4" width="18" height="18" rx="2" />
                            <line x1="16" y1="2" x2="16" y2="6" />
                            <line x1="8" y1="2" x2="8" y2="6" />
                            <line x1="3" y1="10" x2="21" y2="10" />
                        </svg>
                        Mes completo:
                        <?= fmt($facturacionMes) ?>
                    </div>
                    <div class="kpi-bar">
                        <div class="kpi-bar-fill" style="width:100%;background:#6366f1;"></div>
                    </div>
                </div>
            </div>
            <!-- /kpi-grid -->

            <!-- Main grid: Chart + Debts -->
            <div class="main-grid">

                <!-- Chart: Proyección Semanal (doble barra) -->
                <div class="panel">
                    <div class="panel-hd">
                        <div class="panel-title" style="display:flex;align-items:center;gap:12px;">
                            <div style="display:flex;align-items:center;gap:8px;">
                                <svg width="16" height="16" fill="none" stroke="#6366f1" stroke-width="2.5"
                                    viewBox="0 0 24 24">
                                    <path d="M3 3v18h18" />
                                    <path d="M18 17V9" />
                                    <path d="M13 17V5" />
                                    <path d="M8 17v-3" />
                                </svg>
                                Proyección de Facturación Semanal
                            </div>
                            <form method="GET" style="display:flex;align-items:center;margin:0;">
                                <?php foreach($_GET as $k => $v): if($k!=='chart_week'): ?>
                                <input type="hidden" name="<?= htmlspecialchars($k) ?>" value="<?= htmlspecialchars($v) ?>">
                                <?php endif; endforeach; ?>
                                <div style="position:relative; display:flex; align-items:center;">
                                    <input type="date" name="chart_week" onchange="this.form.submit()" 
                                        style="color: var(--text); background: rgba(255,255,255,0.05); padding: 5px 10px; border-radius: 8px; border: 1px solid var(--border); font-size: 0.78rem; outline: none; cursor: pointer; color-scheme: dark; font-family: inherit; font-weight: 500;" 
                                        title="Seleccionar cualquier día de la semana" value="<?= htmlspecialchars($displayDate) ?>">
                                </div>
                            </form>
                        </div>
                        <div class="weekly-chart-legend"
                            style="font-size:.78rem;color:var(--muted);display:flex;gap:16px;">
                            <span style="display:flex;align-items:center;gap:5px;"><span class="wl-dot"
                                    style="background:linear-gradient(180deg,#6ea8fe,#3b59f5);"></span>Facturado</span>
                            <span style="display:flex;align-items:center;gap:5px;"><span class="wl-dot"
                                    style="background:linear-gradient(180deg,#34d399,#059669);"></span>Recaudado
                                (Abonos/Pagos)</span>
                        </div>
                    </div>
                    <div class="panel-bd" style="position:relative;height:260px;">
                        <canvas id="weeklyProjectionChart"></canvas>
                    </div>
                </div>

                <!-- Debt list -->
                <div class="panel">
                    <div class="panel-hd">
                        <div class="panel-title">
                            <svg width="16" height="16" fill="none" stroke="#f43f5e" stroke-width="2.5"
                                viewBox="0 0 24 24">
                                <path
                                    d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0zM12 9v4m0 4h.01" />
                            </svg>
                            Alerta: Deudas Antiguas
                        </div>
                        <span style="font-size:.74rem;color:var(--muted);">
                            <?= count($deudas) ?> deudores
                        </span>
                    </div>
                    <div class="panel-bd" style="padding-top:14px;max-height:310px;overflow-y:auto;">
                        <?php if (empty($deudas)): ?>
                        <div
                            style="text-align:center;color:var(--muted);padding:28px 0;display:flex;flex-direction:column;align-items:center;gap:10px;">
                            <svg width="36" height="36" fill="none" stroke="#10b981" stroke-width="1.5"
                                viewBox="0 0 24 24">
                                <path d="M22 11.08V12a10 10 0 11-5.93-9.14" />
                                <polyline points="22 4 12 14.01 9 11.01" />
                            </svg>
                            Sin deudas antiguas registradas
                        </div>
                        <?php else: ?>
                        <ul class="debt-list">
                            <?php foreach ($deudas as $d):
              $pendiente = $d['total'] - $d['abonado'];
              $dias = (int)$d['dias_atraso'];
              $badgeClass = $dias >= 10 ? '' : 'warn';
              $estadoLabel = match(true) {
                $d['estado'] === 'completado' && !empty($d['entregado']) => 'Entregado',
                $d['estado'] === 'completado' => 'Finalizado',
                $d['estado'] === 'en_curso'   => 'En Proceso',
                default => 'En Recepción'
              };
              $statusClass = ($d['entregado'] && $d['estado']==='completado') ? 's-ent' : 's-nent';
            ?>
                            <li class="debt-item">
                                <div style="min-width:0;">
                                    <div class="d-client"
                                        style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:160px;">
                                        <?= htmlspecialchars($d['cliente_nombre']) ?>
                                        <span class="d-badge <?= $badgeClass ?>">
                                            <?= $dias ?>d
                                        </span>
                                    </div>
                                    <div class="d-meta">
                                        #PED-
                                        <?= str_pad($d['id'],4,'0',STR_PAD_LEFT) ?>
                                        · <span class="d-status <?= $statusClass ?>">
                                            <?= $estadoLabel ?>
                                        </span>
                                        ·
                                        <?= $d['estado_pago']==='no_pago' ? '<span style="color:#f43f5e">Sin pago</span>' : '<span style="color:#f59e0b">Con abono</span>' ?>
                                    </div>
                                </div>
                                <div style="text-align:right;flex-shrink:0;">
                                    <div class="d-amount">
                                        <?= fmt($pendiente) ?>
                                    </div>
                                    <div class="d-total">/
                                        <?= fmt($d['total']) ?>
                                    </div>
                                </div>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <!-- /main-grid -->

            <!-- Stats row -->
            <div class="stats-row" style="margin-bottom:20px;">
                <div class="stat-mini" style="cursor:pointer;" onclick="openModal('modal_no_pagados')">
                    <div class="stat-mini-icon ico-r" style="border-radius:12px;">
                        <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10" />
                            <line x1="12" y1="8" x2="12" y2="12" />
                            <line x1="12" y1="16" x2="12.01" y2="16" />
                        </svg>
                    </div>
                    <div>
                        <div class="stat-mini-lbl">Pedidos Sin Pago</div>
                        <div class="stat-mini-val">
                            <?= $noPagados ?>
                        </div>
                        <div class="stat-mini-sub dn">
                            <?= fmt($noPagadosSum) ?> sin cobrar
                        </div>
                    </div>
                </div>

                <div class="stat-mini" style="cursor:pointer;" onclick="openModal('modal_con_abono')">
                    <div class="stat-mini-icon ico-y" style="border-radius:12px;">
                        <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <rect x="1" y="4" width="22" height="16" rx="2" ry="2" />
                            <line x1="1" y1="10" x2="23" y2="10" />
                        </svg>
                    </div>
                    <div>
                        <div class="stat-mini-lbl">Pedidos con Abono</div>
                        <div class="stat-mini-val">
                            <?= $conAbono ?>
                        </div>
                        <div class="stat-mini-sub" style="color:#f59e0b;">Resta por cobrar:
                            <?= fmt($sumaRestanteAbono) ?>
                        </div>
                    </div>
                </div>

                <div class="stat-mini" style="cursor:pointer;" onclick="openModal('modal_recaudo')">
                    <div class="stat-mini-icon ico-b" style="border-radius:12px;">
                        <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <line x1="12" y1="1" x2="12" y2="23" />
                            <path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6" />
                        </svg>
                    </div>
                    <div>
                        <div class="stat-mini-lbl">Recaudo del Período</div>
                        <div class="stat-mini-val">
                            <?= fmt($ingresosPeriodo) ?>
                        </div>
                        <div class="stat-mini-sub up">
                            <?= $facturadoPeriodo > 0 ? round(($ingresosPeriodo/max($facturadoPeriodo,1))*100,1) : 0 ?>%
                            de lo facturado
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payments table -->
            <div class="panel" style="margin-bottom:40px;">
                <div class="panel-hd">
                    <div class="panel-title">
                        <svg width="16" height="16" fill="none" stroke="#10b981" stroke-width="2.5" viewBox="0 0 24 24">
                            <path d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        Pagos Registrados &mdash;
                        <?= htmlspecialchars($periodoLabel) ?>
                    </div>
                    <span style="font-size:.74rem;color:var(--muted);">
                        <?= count($pagos) ?> transacciones ·
                        <?= fmt($ingresosPeriodo) ?>
                    </span>
                </div>
                <div class="panel-bd" style="padding:0;">
                    <div style="overflow-x:auto;">
                        <?php if (empty($pagos)): ?>
                        <div style="text-align:center;color:var(--muted);padding:36px;display:flex;flex-direction:column;align-items:center;gap:10px;">
                            <svg width="36" height="36" fill="none" stroke="#94a3b8" stroke-width="1.5" viewBox="0 0 24 24">
                                <path d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                            Sin pagos registrados en este período
                        </div>
                        <?php else: ?>
                        <table class="pago-table">
                            <thead>
                                <tr>
                                    <th>#Pedido</th>
                                    <th>Cliente</th>
                                    <th>Tipo</th>
                                    <th>Origen</th>
                                    <th>Método</th>
                                    <th>Registrado por</th>
                                    <th>Fecha</th>
                                    <th style="text-align:right;">Monto</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pagos as $pg):
                                    $montoFila = (float)($pg['monto'] ?? 0);
                                    $tipoPago  = $pg['tipo_pago'] ?? '';
                                    $origen    = $pg['origen'] ?? 'Pedido';
                                    $metodo    = ucfirst($pg['metodo_pago'] ?? 'efectivo');
                                    $fechaMov  = $pg['fecha_mov'] ?? '';
                                ?>
                                <tr>
                                    <td style="font-weight:600;color:#a5b4fc;">#PED-
                                        <?= str_pad($pg['id'],4,'0',STR_PAD_LEFT) ?>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($pg['cliente_nombre']) ?>
                                    </td>
                                    <td>
                                        <?php if ($tipoPago==='pago_completo'): ?>
                                        <span class="badge-tp bp-full">Pago Completo</span>
                                        <?php elseif ($tipoPago==='abono_hist'): ?>
                                        <span class="badge-tp" style="background:rgba(139,92,246,.15);color:#8b5cf6;">Abono Hist.</span>
                                        <?php else: ?>
                                        <span class="badge-tp bp-abono">Abono</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="color:var(--muted);font-size:.78rem;"><?= htmlspecialchars($origen) ?></td>
                                    <td style="color:var(--muted);font-size:.78rem;"><?= htmlspecialchars($metodo) ?></td>
                                    <td style="color:var(--muted);">
                                        <?= htmlspecialchars($pg['registrado_por'] ?? 'Sistema') ?>
                                    </td>
                                    <td style="color:var(--muted);">
                                        <?= $fechaMov ? date('d/m H:i', strtotime($fechaMov)) : '—' ?>
                                    </td>
                                    <td style="text-align:right;font-weight:700;color:#10b981;">+
                                        <?= fmt($montoFila) ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr style="background:rgba(16,185,129,.05);border-top:1px solid rgba(16,185,129,.15);">
                                    <td colspan="7" style="padding:12px 14px;font-weight:700;color:#34d399;">Total del
                                        período</td>
                                    <td
                                        style="text-align:right;font-weight:800;color:#10b981;font-size:1rem;padding:12px 14px;">
                                        <?= fmt($ingresosPeriodo) ?>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div><!-- /page-body -->
    </div><!-- /main-content -->

    <!-- ═══════════════════════════════════════════
         MODALES DE DETALLE KPI
    ═══════════════════════════════════════════ -->

    <!-- MODAL 1: Ingresos del Período -->
    <div class="modal-overlay" id="modal_ingresos" onclick="handleOverlayClick(event, 'modal_ingresos')">
        <div class="modal-box">
            <div class="modal-header">
                <div class="modal-title">
                    <svg width="18" height="18" fill="none" stroke="#10b981" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
                    Ingresos del Período &mdash; <?= htmlspecialchars($periodoLabel) ?>
                </div>
                <button class="modal-close" onclick="closeModal('modal_ingresos')">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            <div class="modal-body">
                <?php if (empty($modalIngresos)): ?>
                <div style="text-align:center;color:var(--muted);padding:40px;">Sin registros de pago en este período.</div>
                <?php else: ?>
                <table class="pago-table">
                    <thead>
                        <tr>
                            <th>#Pedido</th>
                            <th>Cliente</th>
                            <th>Estado Pago</th>
                            <th>Total</th>
                            <th>Abonado</th>
                            <th>Pendiente</th>
                            <th>Fecha Pago</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($modalIngresos as $r):
                            $pen = (float)$r['total'] - (float)$r['abonado'];
                        ?>
                        <tr>
                            <td style="font-weight:700;color:#a5b4fc;">#PED-<?= str_pad($r['id'],4,'0',STR_PAD_LEFT) ?></td>
                            <td><?= htmlspecialchars($r['cliente_nombre']) ?></td>
                            <td>
                                <?php if($r['estado_pago']==='pago_completo'): ?>
                                <span class="badge-tp bp-full">Pago Completo</span>
                                <?php else: ?>
                                <span class="badge-tp bp-abono">Abono</span>
                                <?php endif; ?>
                            </td>
                            <td style="color:#f1f5f9;font-weight:600;"><?= fmt((float)$r['total']) ?></td>
                            <td style="color:#10b981;font-weight:600;"><?= fmt((float)$r['abonado']) ?></td>
                            <td style="color:<?= $pen>0?'#f59e0b':'#10b981' ?>;"><?= fmt($pen) ?></td>
                            <td style="color:var(--muted);font-size:.8rem;"><?= $r['updated_at'] ? date('d/m/Y H:i', strtotime($r['updated_at'])) : '—' ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr style="background:rgba(16,185,129,.06);border-top:1px solid rgba(16,185,129,.15);">
                            <td colspan="3" style="padding:12px 14px;font-weight:700;color:#34d399;">Total recaudado</td>
                            <td style="padding:12px 14px;font-weight:700;color:#f1f5f9;"><?= fmt(array_sum(array_column($modalIngresos,'total'))) ?></td>
                            <td style="padding:12px 14px;font-weight:800;color:#10b981;font-size:1rem;"><?= fmt($ingresosPeriodo) ?></td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- MODAL 2: Cuentas por Cobrar (Global) -->
    <div class="modal-overlay" id="modal_cxc" onclick="handleOverlayClick(event, 'modal_cxc')">
        <div class="modal-box">
            <div class="modal-header">
                <div class="modal-title">
                    <svg width="18" height="18" fill="none" stroke="#f59e0b" stroke-width="2.5" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    Cuentas por Cobrar — Global (<?= $cxcCount ?> pedidos)
                </div>
                <button class="modal-close" onclick="closeModal('modal_cxc')">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            <div class="modal-body">
                <?php if (empty($modalCxC)): ?>
                <div style="text-align:center;color:var(--muted);padding:40px;">Sin cuentas pendientes. ¡Todo cobrado!</div>
                <?php else: ?>
                <table class="pago-table">
                    <thead>
                        <tr>
                            <th>#Pedido</th>
                            <th>Cliente</th>
                            <th>Estado Pago</th>
                            <th>Total</th>
                            <th>Abonado</th>
                            <th>Pendiente</th>
                            <th>Días</th>
                            <th>Fecha Creación</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($modalCxC as $r):
                            $pen = (float)$r['total'] - (float)$r['abonado'];
                            $dias = (int)$r['dias'];
                        ?>
                        <tr>
                            <td style="font-weight:700;color:#a5b4fc;">#PED-<?= str_pad($r['id'],4,'0',STR_PAD_LEFT) ?></td>
                            <td><?= htmlspecialchars($r['cliente_nombre']) ?></td>
                            <td>
                                <?php if($r['estado_pago']==='no_pago'): ?>
                                <span class="badge-tp" style="background:rgba(244,63,94,.15);color:#f43f5e;">Sin Pago</span>
                                <?php else: ?>
                                <span class="badge-tp bp-abono">Con Abono</span>
                                <?php endif; ?>
                            </td>
                            <td style="color:#f1f5f9;font-weight:600;"><?= fmt((float)$r['total']) ?></td>
                            <td style="color:#10b981;"><?= fmt((float)$r['abonado']) ?></td>
                            <td style="color:#f59e0b;font-weight:700;"><?= fmt($pen) ?></td>
                            <td style="color:<?= $dias>=10?'#f43f5e':'#f59e0b' ?>;font-weight:700;"><?= $dias ?>d</td>
                            <td style="color:var(--muted);font-size:.8rem;"><?= date('d/m/Y', strtotime($r['created_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr style="background:rgba(245,158,11,.06);border-top:1px solid rgba(245,158,11,.15);">
                            <td colspan="5" style="padding:12px 14px;font-weight:700;color:#fbbf24;">Total por cobrar</td>
                            <td style="padding:12px 14px;font-weight:800;color:#f59e0b;font-size:1rem;"><?= fmt($cxcTotal) ?></td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- MODAL 3: CxC del Período (Facturado - Ingresos) -->
    <div class="modal-overlay" id="modal_cxc_periodo" onclick="handleOverlayClick(event, 'modal_cxc_periodo')">
        <div class="modal-box">
            <div class="modal-header">
                <div class="modal-title">
                    <svg width="18" height="18" fill="none" stroke="#6366f1" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
                    CxC del Período — <?= htmlspecialchars($periodoLabel) ?>
                </div>
                <button class="modal-close" onclick="closeModal('modal_cxc_periodo')">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            <div class="modal-body">
                <div style="padding:12px 20px;background:rgba(99,102,241,.08);border-bottom:1px solid rgba(99,102,241,.15);font-size:.83rem;color:#a5b4fc;">
                    📊 Facturado: <strong><?= fmt($facturadoPeriodo) ?></strong> &nbsp;·&nbsp; Recaudado: <strong style="color:#10b981"><?= fmt($ingresosPeriodo) ?></strong> &nbsp;·&nbsp; Balance CxC: <strong style="color:#f43f5e"><?= fmt($cxcPeriodo) ?></strong>
                </div>
                <?php
                    // Pedidos creados en el período que aún tienen saldo pendiente
                    $modalCxcPeriodo = $db->query(
                        "SELECT id, cliente_nombre, estado_pago, total, abonado, created_at, estado_pago
                         FROM pedidos
                         WHERE DATE(created_at) BETWEEN '$desde' AND '$hasta'
                           AND estado!='cancelado'
                         ORDER BY total DESC LIMIT 200"
                    )->fetchAll(\PDO::FETCH_ASSOC);
                ?>
                <?php if (empty($modalCxcPeriodo)): ?>
                <div style="text-align:center;color:var(--muted);padding:40px;">Sin pedidos en este período.</div>
                <?php else: ?>
                <table class="pago-table">
                    <thead>
                        <tr>
                            <th>#Pedido</th>
                            <th>Cliente</th>
                            <th>Estado Pago</th>
                            <th>Total Facturado</th>
                            <th>Recaudado</th>
                            <th>Pendiente</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($modalCxcPeriodo as $r):
                            $pen = (float)$r['total'] - (float)$r['abonado'];
                        ?>
                        <tr>
                            <td style="font-weight:700;color:#a5b4fc;">#PED-<?= str_pad($r['id'],4,'0',STR_PAD_LEFT) ?></td>
                            <td><?= htmlspecialchars($r['cliente_nombre']) ?></td>
                            <td>
                                <?php if($r['estado_pago']==='pago_completo'): ?>
                                <span class="badge-tp bp-full">Pago Completo</span>
                                <?php elseif($r['estado_pago']==='abono'): ?>
                                <span class="badge-tp bp-abono">Con Abono</span>
                                <?php else: ?>
                                <span class="badge-tp" style="background:rgba(244,63,94,.15);color:#f43f5e;">Sin Pago</span>
                                <?php endif; ?>
                            </td>
                            <td style="color:#f1f5f9;font-weight:600;"><?= fmt((float)$r['total']) ?></td>
                            <td style="color:#10b981;"><?= fmt((float)$r['abonado']) ?></td>
                            <td style="color:<?= $pen>0?'#f59e0b':'#10b981' ?>;font-weight:700;"><?= fmt($pen) ?></td>
                            <td style="color:var(--muted);font-size:.8rem;"><?= date('d/m/Y', strtotime($r['created_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr style="background:rgba(99,102,241,.06);border-top:1px solid rgba(99,102,241,.15);">
                            <td colspan="3" style="padding:12px 14px;font-weight:700;color:#a5b4fc;">Totales período</td>
                            <td style="padding:12px 14px;font-weight:700;color:#f1f5f9;"><?= fmt($facturadoPeriodo) ?></td>
                            <td style="padding:12px 14px;font-weight:700;color:#10b981;"><?= fmt($ingresosPeriodo) ?></td>
                            <td style="padding:12px 14px;font-weight:800;color:#f59e0b;font-size:1rem;"><?= fmt($cxcPeriodo) ?></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- MODAL 4: Facturado en Período -->
    <div class="modal-overlay" id="modal_facturado" onclick="handleOverlayClick(event, 'modal_facturado')">
        <div class="modal-box">
            <div class="modal-header">
                <div class="modal-title">
                    <svg width="18" height="18" fill="none" stroke="#6366f1" stroke-width="2.5" viewBox="0 0 24 24"><path d="M21.21 15.89A10 10 0 118 2.83M22 12A10 10 0 0012 2v10z"/></svg>
                    Facturado en Período — <?= htmlspecialchars($periodoLabel) ?>
                </div>
                <button class="modal-close" onclick="closeModal('modal_facturado')">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            <div class="modal-body">
                <?php if (empty($modalFacturado)): ?>
                <div style="text-align:center;color:var(--muted);padding:40px;">Sin pedidos creados en este período.</div>
                <?php else: ?>
                <table class="pago-table">
                    <thead>
                        <tr>
                            <th>#Pedido</th>
                            <th>Cliente</th>
                            <th>Descripción</th>
                            <th>Estado Pago</th>
                            <th>Total</th>
                            <th>Abonado</th>
                            <th>Fecha Creación</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            // Re-fetch with description for this modal
                            $modalFacturadoFull = $db->query(
                                "SELECT id, cliente_nombre, descripcion, estado_pago, total, abonado, created_at
                                 FROM pedidos
                                 WHERE DATE(created_at) BETWEEN '$desde' AND '$hasta' AND estado!='cancelado'
                                 ORDER BY created_at DESC LIMIT 200"
                            )->fetchAll(\PDO::FETCH_ASSOC);
                        ?>
                        <?php foreach($modalFacturadoFull as $r): ?>
                        <tr>
                            <td style="font-weight:700;color:#a5b4fc;">#PED-<?= str_pad($r['id'],4,'0',STR_PAD_LEFT) ?></td>
                            <td><?= htmlspecialchars($r['cliente_nombre']) ?></td>
                            <td style="color:var(--muted);font-size:.78rem;max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= htmlspecialchars($r['descripcion'] ?? '') ?>"><?= htmlspecialchars(substr($r['descripcion'] ?? '—', 0, 40)) ?></td>
                            <td>
                                <?php if($r['estado_pago']==='pago_completo'): ?>
                                <span class="badge-tp bp-full">Pago Completo</span>
                                <?php elseif($r['estado_pago']==='abono'): ?>
                                <span class="badge-tp bp-abono">Con Abono</span>
                                <?php else: ?>
                                <span class="badge-tp" style="background:rgba(244,63,94,.15);color:#f43f5e;">Sin Pago</span>
                                <?php endif; ?>
                            </td>
                            <td style="color:#f1f5f9;font-weight:700;"><?= fmt((float)$r['total']) ?></td>
                            <td style="color:#10b981;"><?= fmt((float)$r['abonado']) ?></td>
                            <td style="color:var(--muted);font-size:.8rem;"><?= date('d/m/Y H:i', strtotime($r['created_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr style="background:rgba(99,102,241,.06);border-top:1px solid rgba(99,102,241,.15);">
                            <td colspan="4" style="padding:12px 14px;font-weight:700;color:#a5b4fc;">Total facturado en período</td>
                            <td style="padding:12px 14px;font-weight:800;color:#6366f1;font-size:1rem;"><?= fmt($facturadoPeriodo) ?></td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- MODAL 5: Pedidos Sin Pago -->
    <div class="modal-overlay" id="modal_no_pagados" onclick="handleOverlayClick(event, 'modal_no_pagados')">
        <div class="modal-box">
            <div class="modal-header">
                <div class="modal-title">
                    <svg width="18" height="18" fill="none" stroke="#f43f5e" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    Pedidos Sin Pago &mdash; <?= htmlspecialchars($periodoLabel) ?>
                </div>
                <button class="modal-close" onclick="closeModal('modal_no_pagados')">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            <div class="modal-body">
                <div style="padding:10px 20px;background:rgba(244,63,94,.07);border-bottom:1px solid rgba(244,63,94,.15);font-size:.82rem;color:#fca5a5;">
                    <?php $totNoPag = array_sum(array_column($modalNoPagados,'total')); ?>
                    <?= count($modalNoPagados) ?> pedidos sin pago en el período &nbsp;·&nbsp; Total: <strong><?= fmt($totNoPag) ?></strong>
                </div>
                <?php if (empty($modalNoPagados)): ?>
                <div style="text-align:center;color:var(--muted);padding:40px;">Sin pedidos sin pago en este período. ✅</div>
                <?php else: ?>
                <table class="pago-table">
                    <thead>
                        <tr>
                            <th>#Pedido</th>
                            <th>Cliente</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th>Días</th>
                            <th>Fecha Creación</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($modalNoPagados as $r):
                            $dias = (int)$r['dias'];
                            $estadoLabel = match($r['estado']) {
                                'completado' => 'Finalizado',
                                'en_curso'   => 'En Proceso',
                                default      => 'En Recepción'
                            };
                        ?>
                        <tr>
                            <td style="font-weight:700;color:#a5b4fc;">#PED-<?= str_pad($r['id'],4,'0',STR_PAD_LEFT) ?></td>
                            <td><?= htmlspecialchars($r['cliente_nombre']) ?></td>
                            <td style="color:#f43f5e;font-weight:700;"><?= fmt((float)$r['total']) ?></td>
                            <td><span style="background:rgba(244,63,94,.12);color:#f87171;padding:2px 8px;border-radius:5px;font-size:.75rem;font-weight:700;"><?= $estadoLabel ?></span></td>
                            <td style="color:<?= $dias>=10?'#f43f5e':'#f59e0b' ?>;font-weight:700;"><?= $dias ?>d</td>
                            <td style="color:var(--muted);font-size:.8rem;"><?= date('d/m/Y', strtotime($r['created_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr style="background:rgba(244,63,94,.06);border-top:1px solid rgba(244,63,94,.15);">
                            <td colspan="2" style="padding:12px 14px;font-weight:700;color:#fca5a5;">Total sin cobrar (período)</td>
                            <td style="padding:12px 14px;font-weight:800;color:#f43f5e;font-size:1rem;"><?= fmt($totNoPag) ?></td>
                            <td colspan="3"></td>
                        </tr>
                    </tfoot>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- MODAL 6: Pedidos con Abono -->
    <div class="modal-overlay" id="modal_con_abono" onclick="handleOverlayClick(event, 'modal_con_abono')">
        <div class="modal-box">
            <div class="modal-header">
                <div class="modal-title">
                    <svg width="18" height="18" fill="none" stroke="#f59e0b" stroke-width="2.5" viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                    Pedidos con Abono &mdash; <?= htmlspecialchars($periodoLabel) ?>
                </div>
                <button class="modal-close" onclick="closeModal('modal_con_abono')">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            <div class="modal-body">
                <div style="padding:10px 20px;background:rgba(245,158,11,.07);border-bottom:1px solid rgba(245,158,11,.15);font-size:.82rem;color:#fde68a;">
                    <?php $totPendAbono = array_sum(array_map(fn($r)=>(float)$r['total']-(float)$r['abonado'], $modalConAbono)); ?>
                    <?= count($modalConAbono) ?> pedidos con abono en el período &nbsp;·&nbsp; Pendiente: <strong><?= fmt($totPendAbono) ?></strong>
                </div>
                <?php if (empty($modalConAbono)): ?>
                <div style="text-align:center;color:var(--muted);padding:40px;">Sin pedidos con abono en este período.</div>
                <?php else: ?>
                <table class="pago-table">
                    <thead>
                        <tr>
                            <th>#Pedido</th>
                            <th>Cliente</th>
                            <th>Total</th>
                            <th>Abonado</th>
                            <th>Pendiente</th>
                            <th>Días</th>
                            <th>Fecha Creación</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($modalConAbono as $r):
                            $pen = (float)$r['total'] - (float)$r['abonado'];
                            $dias = (int)$r['dias'];
                        ?>
                        <tr>
                            <td style="font-weight:700;color:#a5b4fc;">#PED-<?= str_pad($r['id'],4,'0',STR_PAD_LEFT) ?></td>
                            <td><?= htmlspecialchars($r['cliente_nombre']) ?></td>
                            <td style="color:#f1f5f9;font-weight:600;"><?= fmt((float)$r['total']) ?></td>
                            <td style="color:#10b981;font-weight:600;"><?= fmt((float)$r['abonado']) ?></td>
                            <td style="color:#f59e0b;font-weight:700;"><?= fmt($pen) ?></td>
                            <td style="color:<?= $dias>=10?'#f43f5e':'#f59e0b' ?>;font-weight:700;"><?= $dias ?>d</td>
                            <td style="color:var(--muted);font-size:.8rem;"><?= date('d/m/Y', strtotime($r['created_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr style="background:rgba(245,158,11,.06);border-top:1px solid rgba(245,158,11,.15);">
                            <td colspan="4" style="padding:12px 14px;font-weight:700;color:#fde68a;">Total pendiente por cobrar</td>
                            <td style="padding:12px 14px;font-weight:800;color:#f59e0b;font-size:1rem;"><?= fmt($totPendAbono) ?></td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- MODAL 7: Recaudo del Período -->
    <div class="modal-overlay" id="modal_recaudo" onclick="handleOverlayClick(event, 'modal_recaudo')">
        <div class="modal-box">
            <div class="modal-header">
                <div class="modal-title">
                    <svg width="18" height="18" fill="none" stroke="#6366f1" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
                    Recaudo del Período &mdash; <?= htmlspecialchars($periodoLabel) ?>
                </div>
                <button class="modal-close" onclick="closeModal('modal_recaudo')">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            <div class="modal-body">
                <div style="padding:10px 20px;background:rgba(99,102,241,.07);border-bottom:1px solid rgba(99,102,241,.15);font-size:.82rem;color:#a5b4fc;">
                    <?= count($modalRecaudo) ?> transacciones en el período &nbsp;·&nbsp;
                    💵 Efectivo: <strong><?= fmt(array_sum(array_map(fn($r)=>($r['metodo_pago']==='efectivo'||(empty($r['metodo_pago']))?(float)$r['monto']:0), $modalRecaudo))) ?></strong>
                    &nbsp;·&nbsp;
                    🏦 Transferencia: <strong><?= fmt(array_sum(array_map(fn($r)=>($r['metodo_pago']==='transferencia'?(float)$r['monto']:0), $modalRecaudo))) ?></strong>
                    &nbsp;·&nbsp; Total: <strong style="color:#10b981"><?= fmt($ingresosPeriodo) ?></strong>
                </div>
                <?php if (empty($modalRecaudo)): ?>
                <div style="text-align:center;color:var(--muted);padding:40px;">Sin pagos registrados en este período.</div>
                <?php else: ?>
                <table class="pago-table">
                    <thead>
                        <tr>
                            <th>#Pedido</th>
                            <th>Cliente</th>
                            <th>Concepto</th>
                            <th>Método</th>
                            <th>Registrado por</th>
                            <th>Fecha Pago</th>
                            <th style="text-align:right;">Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($modalRecaudo as $r): ?>
                        <tr>
                            <td style="font-weight:700;color:#a5b4fc;">#PED-<?= str_pad($r['pedido_id'],4,'0',STR_PAD_LEFT) ?></td>
                            <td><?= htmlspecialchars($r['cliente_nombre']) ?></td>
                            <td style="color:var(--muted);font-size:.78rem;">
                                <?php
                                    $obs = $r['observacion'] ?? '';
                                    if (stripos($obs,'inicial')!==false) echo 'Abono Inicial';
                                    elseif (stripos($obs,'completo')!==false) echo 'Pago Completo';
                                    elseif ($obs) echo htmlspecialchars(substr($obs,0,30));
                                    else echo 'Abono';
                                ?>
                            </td>
                            <td>
                                <?php if(($r['metodo_pago']??'')=='transferencia'): ?>
                                <span style="background:rgba(99,102,241,.15);color:#a5b4fc;padding:2px 7px;border-radius:5px;font-size:.73rem;font-weight:700;">🏦 Transfer.</span>
                                <?php else: ?>
                                <span style="background:rgba(16,185,129,.12);color:#34d399;padding:2px 7px;border-radius:5px;font-size:.73rem;font-weight:700;">💵 Efectivo</span>
                                <?php endif; ?>
                            </td>
                            <td style="color:var(--muted);"><?= htmlspecialchars($r['registrado_por'] ?? 'Sistema') ?></td>
                            <td style="color:var(--muted);font-size:.8rem;"><?= $r['fecha_pago'] ? date('d/m/Y H:i', strtotime($r['fecha_pago'])) : '—' ?></td>
                            <td style="text-align:right;font-weight:700;color:#10b981;">+<?= fmt((float)$r['monto']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr style="background:rgba(16,185,129,.06);border-top:1px solid rgba(16,185,129,.15);">
                            <td colspan="6" style="padding:12px 14px;font-weight:700;color:#34d399;">Total recaudado en período</td>
                            <td style="text-align:right;padding:12px 14px;font-weight:800;color:#10b981;font-size:1rem;"><?= fmt($ingresosPeriodo) ?></td>
                        </tr>
                    </tfoot>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- JS: Modal helpers -->
    <script>
        function openModal(id) {
            const el = document.getElementById(id);
            if (!el) return;
            el.style.display = 'flex';
            requestAnimationFrame(() => el.classList.add('active'));
            document.body.style.overflow = 'hidden';
        }
        function closeModal(id) {
            const el = document.getElementById(id);
            if (!el) return;
            el.classList.remove('active');
            setTimeout(() => { el.style.display = 'none'; }, 280);
            document.body.style.overflow = '';
        }
        function handleOverlayClick(e, id) {
            if (e.target === e.currentTarget) closeModal(id);
        }
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal-overlay.active').forEach(m => closeModal(m.id));
            }
        });
    </script>

    <!-- Weekly Projection Chart JS -->
    <script>
        // Registrar el plugin de datalabels
        Chart.register(ChartDataLabels);

        (function () {
            const wLabels = <?= json_encode($weekDays) ?>;
            const wFact = <?= json_encode($weekFact) ?>;
            const wPagos = <?= json_encode($weekPagos) ?>;

            const wCtx = document.getElementById('weeklyProjectionChart').getContext('2d');

            // Gradient for Facturado (blue)
            const gFact = wCtx.createLinearGradient(0, 0, 0, 280);
            gFact.addColorStop(0, 'rgba(99,130,255,0.95)');
            gFact.addColorStop(1, 'rgba(59,89,245,0.65)');

            // Gradient for Pagos (green)
            const gPagos = wCtx.createLinearGradient(0, 0, 0, 280);
            gPagos.addColorStop(0, 'rgba(52,211,153,0.95)');
            gPagos.addColorStop(1, 'rgba(5,150,105,0.65)');

            new Chart(wCtx, {
                type: 'bar',
                data: {
                    labels: wLabels,
                    datasets: [
                        {
                            label: 'Facturado',
                            data: wFact,
                            backgroundColor: gFact,
                            borderColor: 'rgba(99,130,255,0)',
                            borderRadius: { topLeft: 12, topRight: 12, bottomLeft: 0, bottomRight: 0 },
                            borderSkipped: false,
                            barPercentage: 0.85,
                            categoryPercentage: 0.9,
                        },
                        {
                            label: 'Abonos / Pagos',
                            data: wPagos,
                            backgroundColor: gPagos,
                            borderColor: 'rgba(52,211,153,0)',
                            borderRadius: { topLeft: 12, topRight: 12, bottomLeft: 0, bottomRight: 0 },
                            borderSkipped: false,
                            barPercentage: 0.85,
                            categoryPercentage: 0.9,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(15,23,42,.96)',
                            borderColor: 'rgba(255,255,255,.1)',
                            borderWidth: 1,
                            titleColor: '#f1f5f9',
                            bodyColor: '#94a3b8',
                            padding: 13,
                            callbacks: {
                                label: c => ' ' + c.dataset.label + ': $' + Number(c.parsed.y).toLocaleString('es-CO')
                            }
                        },
                        datalabels: {
                            anchor: 'end',
                            align: 'top',
                            offset: 5,
                            color: '#ffffff',
                            font: { size: 11, weight: 'bold', family: 'Inter' },
                            formatter: v => v > 0 ? '$' + (v >= 1000000 ? (v / 1000000).toFixed(1) + 'M' : v >= 1000 ? (v / 1000).toFixed(0) + 'K' : v) : ''
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { color: '#94a3b8', font: { size: 12, family: 'Inter' } },
                            border: { display: false }
                        },
                        y: {
                            grid: { color: 'rgba(255,255,255,.04)', drawBorder: false },
                            border: { display: false },
                            ticks: {
                                color: '#94a3b8',
                                font: { size: 11 },
                                callback: v => '$' + (v >= 1000000 ? (v / 1000000).toFixed(1) + 'M' : v >= 1000 ? (v / 1000).toFixed(0) + 'K' : v)
                            }
                        }
                    },
                    animation: {
                        duration: 900,
                        easing: 'easeOutQuart'
                    }
                }
            });
        })();
    </script>


</body>

</html>