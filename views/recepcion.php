<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ./login');
    exit;
}
// Evitar caché del navegador para que las métricas siempre sean frescas
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

require_once __DIR__ . '/../config/Database.php';
$db = \Config\Database::getInstance();

// Cargar áreas para el select dinámico
$stmt = $db->query("SELECT id, nombre FROM areas WHERE estado = 1 ORDER BY orden ASC");
$areas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Cargar Estadísticas (para los recuadros)
$stats = [
    'recepcion' => $db->query("SELECT COUNT(*) FROM pedidos WHERE area_actual_id IS NULL AND estado NOT IN ('cancelado','completado')")->fetchColumn(),
    'proceso' => $db->query("SELECT COUNT(*) FROM pedidos WHERE area_actual_id IS NOT NULL AND estado NOT IN ('cancelado','completado') AND fase_actual != 'preparado'")->fetchColumn(),
    'preparados' => $db->query("SELECT COUNT(*) FROM pedidos WHERE estado NOT IN ('cancelado','completado') AND fase_actual = 'preparado'")->fetchColumn(),
    'finalizados' => $db->query("SELECT COUNT(*) FROM pedidos WHERE estado = 'completado'")->fetchColumn(),
    'entregados' => $db->query("SELECT COUNT(*) FROM pedidos WHERE estado = 'completado' AND entregado = 1")->fetchColumn(),
    'no_entregados' => $db->query("SELECT COUNT(*) FROM pedidos WHERE estado = 'completado' AND (entregado IS NULL OR entregado = 0)")->fetchColumn(),
    'fin_no_entregados' => $db->query("SELECT COUNT(*) FROM pedidos WHERE estado = 'completado' AND (entregado IS NULL OR entregado = 0)")->fetchColumn(),
    'pago_completo' => $db->query("SELECT COUNT(*) FROM pedidos WHERE estado_pago = 'pago_completo' AND estado != 'cancelado'")->fetchColumn(),
    'no_pago' => $db->query("SELECT COUNT(*) FROM pedidos WHERE estado_pago = 'no_pago' AND estado != 'cancelado'")->fetchColumn(),
    'abono' => $db->query("SELECT COUNT(*) FROM pedidos WHERE estado_pago = 'abono' AND estado != 'cancelado'")->fetchColumn(),
    'caducados' => $db->query("SELECT COUNT(*) FROM pedidos WHERE fecha_entrega_esperada IS NOT NULL AND fecha_entrega_esperada < CURDATE() AND estado NOT IN ('completado','cancelado')")->fetchColumn(),
    'por_caducar' => $db->query("SELECT COUNT(*) FROM pedidos WHERE fecha_entrega_esperada IS NOT NULL AND fecha_entrega_esperada >= CURDATE() AND TIMESTAMPDIFF(HOUR, NOW(), CONCAT(fecha_entrega_esperada,' 23:59:59')) <= 12 AND estado NOT IN ('completado','cancelado')")->fetchColumn(),
    'pedidos_hoy' => $db->query("SELECT COUNT(*) FROM pedidos WHERE DATE(created_at) = CURDATE()")->fetchColumn(),
    'pedidos_mes' => $db->query("SELECT COUNT(*) FROM pedidos WHERE YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())")->fetchColumn(),
    'total' => $db->query("SELECT COUNT(*) FROM pedidos")->fetchColumn(),
    'eliminados' => $db->query("SELECT COUNT(*) FROM pedidos WHERE estado = 'cancelado'")->fetchColumn(),
];

// Cargar Listas para las 3 columnas (panel)
$recientes = $db->query("SELECT p.*, COALESCE(a.nombre, 'Guía Generada') AS area_nombre, (SELECT u.nombre FROM movimientos_pedido mp JOIN usuarios u ON mp.usuario_id = u.id WHERE mp.pedido_id = p.id AND mp.accion = 'Creado' ORDER BY mp.id ASC LIMIT 1) AS creado_por FROM pedidos p LEFT JOIN areas a ON p.area_actual_id = a.id WHERE p.area_actual_id IS NULL AND p.estado NOT IN ('cancelado','completado') ORDER BY p.id DESC LIMIT 50")->fetchAll(PDO::FETCH_ASSOC);
$en_proceso = $db->query("SELECT p.*, COALESCE(a.nombre, 'Guía Generada') AS area_nombre, u.nombre AS asignado_a_nombre, (SELECT us.nombre FROM movimientos_pedido mp JOIN usuarios us ON mp.usuario_id = us.id WHERE mp.pedido_id = p.id AND mp.accion = 'Marcado como Preparado' ORDER BY mp.id DESC LIMIT 1) AS preparado_por FROM pedidos p LEFT JOIN areas a ON p.area_actual_id = a.id LEFT JOIN usuarios u ON p.asignado_a_usuario_id = u.id WHERE p.area_actual_id IS NOT NULL AND p.estado NOT IN ('cancelado','completado') ORDER BY p.last_movement_at DESC LIMIT 30")->fetchAll(PDO::FETCH_ASSOC);
$finalizados = $db->query("SELECT p.*, COALESCE(a.nombre, 'Guía Generada') AS area_nombre, (SELECT u.nombre FROM movimientos_pedido mp JOIN usuarios u ON mp.usuario_id = u.id WHERE mp.pedido_id = p.id AND mp.accion = 'Completado' ORDER BY mp.id DESC LIMIT 1) AS finalizado_por, (SELECT u.nombre FROM movimientos_pedido mp JOIN usuarios u ON mp.usuario_id = u.id WHERE mp.pedido_id = p.id AND mp.accion = 'Entregado al Cliente' ORDER BY mp.id DESC LIMIT 1) AS entregado_por FROM pedidos p LEFT JOIN areas a ON p.area_actual_id = a.id WHERE p.estado = 'completado' ORDER BY COALESCE(p.entregado, 0) ASC, CASE WHEN p.entregado = 1 AND p.estado_pago != 'pago_completo' THEN 0 ELSE 1 END ASC, p.last_movement_at DESC LIMIT 150")->fetchAll(PDO::FETCH_ASSOC);

// Cargar Listas completas para los modales de stat-cards
$statLists = [
    'recepcion' => $db->query("SELECT p.*, COALESCE(a.nombre, 'Guía Generada') AS area_nombre FROM pedidos p LEFT JOIN areas a ON p.area_actual_id = a.id WHERE p.area_actual_id IS NULL AND p.estado NOT IN ('cancelado','completado') ORDER BY p.last_movement_at DESC")->fetchAll(PDO::FETCH_ASSOC),
    'proceso' => $db->query("SELECT p.*, COALESCE(a.nombre, 'Guía Generada') AS area_nombre FROM pedidos p LEFT JOIN areas a ON p.area_actual_id = a.id WHERE p.area_actual_id IS NOT NULL AND p.estado NOT IN ('cancelado','completado') AND p.fase_actual != 'preparado' ORDER BY p.last_movement_at DESC")->fetchAll(PDO::FETCH_ASSOC),
    'preparados' => $db->query("SELECT p.*, COALESCE(a.nombre, 'Guía Generada') AS area_nombre FROM pedidos p LEFT JOIN areas a ON p.area_actual_id = a.id WHERE p.estado NOT IN ('cancelado','completado') AND p.fase_actual = 'preparado' ORDER BY p.last_movement_at DESC")->fetchAll(PDO::FETCH_ASSOC),
    'finalizados' => $db->query("SELECT p.*, COALESCE(a.nombre, 'Guía Generada') AS area_nombre FROM pedidos p LEFT JOIN areas a ON p.area_actual_id = a.id WHERE p.estado = 'completado' ORDER BY p.last_movement_at DESC")->fetchAll(PDO::FETCH_ASSOC),
    'entregados' => $db->query("SELECT p.*, COALESCE(a.nombre, 'Guía Generada') AS area_nombre FROM pedidos p LEFT JOIN areas a ON p.area_actual_id = a.id WHERE p.estado = 'completado' AND p.entregado = 1 ORDER BY p.last_movement_at DESC")->fetchAll(PDO::FETCH_ASSOC),
    'no_entregados' => $db->query("SELECT p.*, COALESCE(a.nombre, 'Guía Generada') AS area_nombre FROM pedidos p LEFT JOIN areas a ON p.area_actual_id = a.id WHERE p.estado = 'completado' AND (p.entregado IS NULL OR p.entregado = 0) ORDER BY p.last_movement_at DESC")->fetchAll(PDO::FETCH_ASSOC),
    'fin_no_entregados' => $db->query("SELECT p.*, COALESCE(a.nombre, 'Guía Generada') AS area_nombre FROM pedidos p LEFT JOIN areas a ON p.area_actual_id = a.id WHERE p.estado = 'completado' AND (p.entregado IS NULL OR p.entregado = 0) ORDER BY p.last_movement_at DESC")->fetchAll(PDO::FETCH_ASSOC),
    'pago_completo' => $db->query("SELECT p.*, COALESCE(a.nombre, 'Guía Generada') AS area_nombre FROM pedidos p LEFT JOIN areas a ON p.area_actual_id = a.id WHERE p.estado_pago = 'pago_completo' AND p.estado != 'cancelado' ORDER BY p.last_movement_at DESC")->fetchAll(PDO::FETCH_ASSOC),
    'no_pago' => $db->query("SELECT p.*, COALESCE(a.nombre, 'Guía Generada') AS area_nombre FROM pedidos p LEFT JOIN areas a ON p.area_actual_id = a.id WHERE p.estado_pago = 'no_pago' AND p.estado != 'cancelado' ORDER BY p.last_movement_at DESC")->fetchAll(PDO::FETCH_ASSOC),
    'abono' => $db->query("SELECT p.*, COALESCE(a.nombre, 'Guía Generada') AS area_nombre FROM pedidos p LEFT JOIN areas a ON p.area_actual_id = a.id WHERE p.estado_pago = 'abono' AND p.estado != 'cancelado' ORDER BY p.last_movement_at DESC")->fetchAll(PDO::FETCH_ASSOC),
    'caducados' => $db->query("SELECT p.*, COALESCE(a.nombre, 'Guía Generada') AS area_nombre FROM pedidos p LEFT JOIN areas a ON p.area_actual_id = a.id WHERE p.fecha_entrega_esperada IS NOT NULL AND p.fecha_entrega_esperada < CURDATE() AND p.estado NOT IN ('completado','cancelado') ORDER BY p.fecha_entrega_esperada ASC")->fetchAll(PDO::FETCH_ASSOC),
    'por_caducar' => $db->query("SELECT p.*, COALESCE(a.nombre, 'Guía Generada') AS area_nombre FROM pedidos p LEFT JOIN areas a ON p.area_actual_id = a.id WHERE p.fecha_entrega_esperada IS NOT NULL AND p.fecha_entrega_esperada >= CURDATE() AND TIMESTAMPDIFF(HOUR, NOW(), CONCAT(p.fecha_entrega_esperada,' 23:59:59')) <= 12 AND p.estado NOT IN ('completado','cancelado') ORDER BY p.fecha_entrega_esperada ASC")->fetchAll(PDO::FETCH_ASSOC),
    'pedidos_hoy' => $db->query("SELECT p.*, COALESCE(a.nombre, 'Guía Generada') AS area_nombre FROM pedidos p LEFT JOIN areas a ON p.area_actual_id = a.id WHERE DATE(p.created_at) = CURDATE() ORDER BY p.created_at DESC")->fetchAll(PDO::FETCH_ASSOC),
    'pedidos_mes' => $db->query("SELECT p.*, COALESCE(a.nombre, 'Guía Generada') AS area_nombre FROM pedidos p LEFT JOIN areas a ON p.area_actual_id = a.id WHERE YEAR(p.created_at) = YEAR(CURDATE()) AND MONTH(p.created_at) = MONTH(CURDATE()) ORDER BY p.created_at DESC")->fetchAll(PDO::FETCH_ASSOC),
    'total' => $db->query("SELECT p.*, COALESCE(a.nombre, 'Guía Generada') AS area_nombre FROM pedidos p LEFT JOIN areas a ON p.area_actual_id = a.id ORDER BY p.id DESC")->fetchAll(PDO::FETCH_ASSOC),
    'eliminados' => $db->query("SELECT p.*, COALESCE(a.nombre, 'Guía Generada') AS area_nombre FROM pedidos p LEFT JOIN areas a ON p.area_actual_id = a.id WHERE p.estado = 'cancelado' ORDER BY p.updated_at DESC")->fetchAll(PDO::FETCH_ASSOC),
];

$role = $_SESSION['role'] ?? 'Operador';

// Variables for daily metrics if user has permissions
$canViewReceptionMetrics = (in_array($role, ['Admin', 'SuperAdmin']) || !empty($_SESSION['ver_metricas_recepcion']));
$recaudadoDia = 0;
$facturadoDia = 0;

if ($canViewReceptionMetrics) {
    $hoy = date('Y-m-d');
    
    // Recaudado: suma de todos los movimientos en historial_pagos realizados hoy
    $recaudadoDia = (float)$db->query("SELECT COALESCE(SUM(h.monto),0) FROM historial_pagos h JOIN pedidos p ON p.id = h.pedido_id WHERE DATE(h.fecha_pago) = '$hoy' AND p.estado!='cancelado'")->fetchColumn();
    $recaudadoEfectivo = (float)$db->query("SELECT COALESCE(SUM(h.monto),0) FROM historial_pagos h JOIN pedidos p ON p.id = h.pedido_id WHERE DATE(h.fecha_pago) = '$hoy' AND (h.metodo_pago='efectivo' OR h.metodo_pago IS NULL OR h.metodo_pago='') AND p.estado!='cancelado'")->fetchColumn();
    $recaudadoTransferencia = (float)$db->query("SELECT COALESCE(SUM(h.monto),0) FROM historial_pagos h JOIN pedidos p ON p.id = h.pedido_id WHERE DATE(h.fecha_pago) = '$hoy' AND h.metodo_pago='transferencia' AND p.estado!='cancelado'")->fetchColumn();

    $facturadoDia = (float)$db->query("SELECT COALESCE(SUM(total),0) FROM pedidos WHERE DATE(created_at) = '$hoy' AND estado!='cancelado'")->fetchColumn();
    $facturadoEfectivo = (float)$db->query("SELECT COALESCE(SUM(total),0) FROM pedidos WHERE DATE(created_at) = '$hoy' AND estado!='cancelado' AND metodo_pago='efectivo'")->fetchColumn();
    $facturadoTransferencia = (float)$db->query("SELECT COALESCE(SUM(total),0) FROM pedidos WHERE DATE(created_at) = '$hoy' AND estado!='cancelado' AND metodo_pago='transferencia'")->fetchColumn();
    
    // CxC del Día: Deuda pendiente generada exclusivamente por los pedidos creados el día de hoy
    $cxcDia = (float)$db->query("SELECT COALESCE(SUM(total - abonado),0) FROM pedidos WHERE DATE(created_at) = '$hoy' AND estado!='cancelado' AND estado_pago!='pago_completo' AND total>0")->fetchColumn();
    $cxcEfectivo = (float)$db->query("SELECT COALESCE(SUM(total - abonado),0) FROM pedidos WHERE DATE(created_at) = '$hoy' AND estado!='cancelado' AND estado_pago!='pago_completo' AND total>0 AND metodo_pago='efectivo'")->fetchColumn();
    $cxcTransferencia = (float)$db->query("SELECT COALESCE(SUM(total - abonado),0) FROM pedidos WHERE DATE(created_at) = '$hoy' AND estado!='cancelado' AND estado_pago!='pago_completo' AND total>0 AND metodo_pago='transferencia'")->fetchColumn();

    $statLists['recaudado_dia'] = $db->query("SELECT p.*, COALESCE(a.nombre, 'Guía Generada') AS area_nombre FROM pedidos p LEFT JOIN areas a ON p.area_actual_id = a.id WHERE p.id IN (SELECT DISTINCT h.pedido_id FROM historial_pagos h WHERE DATE(h.fecha_pago) = '$hoy') AND p.estado!='cancelado' ORDER BY p.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
    $statLists['recaudado_efectivo'] = $db->query("SELECT p.*, COALESCE(a.nombre, 'Guía Generada') AS area_nombre FROM pedidos p LEFT JOIN areas a ON p.area_actual_id = a.id WHERE p.id IN (SELECT DISTINCT h.pedido_id FROM historial_pagos h WHERE DATE(h.fecha_pago) = '$hoy' AND (h.metodo_pago='efectivo' OR h.metodo_pago IS NULL OR h.metodo_pago='')) AND p.estado!='cancelado' ORDER BY p.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
    $statLists['recaudado_transferencia'] = $db->query("SELECT p.*, COALESCE(a.nombre, 'Guía Generada') AS area_nombre FROM pedidos p LEFT JOIN areas a ON p.area_actual_id = a.id WHERE p.id IN (SELECT DISTINCT h.pedido_id FROM historial_pagos h WHERE DATE(h.fecha_pago) = '$hoy' AND h.metodo_pago='transferencia') AND p.estado!='cancelado' ORDER BY p.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

    $statLists['facturado_dia'] = $db->query("SELECT p.*, COALESCE(a.nombre, 'Guía Generada') AS area_nombre FROM pedidos p LEFT JOIN areas a ON p.area_actual_id = a.id WHERE DATE(p.created_at) = '$hoy' AND p.estado!='cancelado' ORDER BY p.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
    $statLists['facturado_efectivo'] = $db->query("SELECT p.*, COALESCE(a.nombre, 'Guía Generada') AS area_nombre FROM pedidos p LEFT JOIN areas a ON p.area_actual_id = a.id WHERE DATE(p.created_at) = '$hoy' AND p.estado!='cancelado' AND p.metodo_pago='efectivo' ORDER BY p.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
    $statLists['facturado_transferencia'] = $db->query("SELECT p.*, COALESCE(a.nombre, 'Guía Generada') AS area_nombre FROM pedidos p LEFT JOIN areas a ON p.area_actual_id = a.id WHERE DATE(p.created_at) = '$hoy' AND p.estado!='cancelado' AND p.metodo_pago='transferencia' ORDER BY p.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

    $statLists['cxc_dia'] = $db->query("SELECT p.*, COALESCE(a.nombre, 'Guía Generada') AS area_nombre FROM pedidos p LEFT JOIN areas a ON p.area_actual_id = a.id WHERE DATE(p.created_at) = '$hoy' AND p.estado!='cancelado' AND p.estado_pago!='pago_completo' AND p.total>0 ORDER BY p.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
    $statLists['cxc_efectivo'] = $db->query("SELECT p.*, COALESCE(a.nombre, 'Guía Generada') AS area_nombre FROM pedidos p LEFT JOIN areas a ON p.area_actual_id = a.id WHERE DATE(p.created_at) = '$hoy' AND p.estado!='cancelado' AND p.estado_pago!='pago_completo' AND p.total>0 AND p.metodo_pago='efectivo' ORDER BY p.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
    $statLists['cxc_transferencia'] = $db->query("SELECT p.*, COALESCE(a.nombre, 'Guía Generada') AS area_nombre FROM pedidos p LEFT JOIN areas a ON p.area_actual_id = a.id WHERE DATE(p.created_at) = '$hoy' AND p.estado!='cancelado' AND p.estado_pago!='pago_completo' AND p.total>0 AND p.metodo_pago='transferencia' ORDER BY p.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
}
$userName = $_SESSION['user_id'] == 1 ? 'Administrador' : ($_SESSION['email'] ?? 'Usuario');
$csrfToken = \App\Middlewares\CsrfMiddleware::generateToken();
$canViewPrices = (in_array($role, ['Admin', 'SuperAdmin']) || !empty($_SESSION['ver_precios']));

$stmtUserAux = $db->prepare("SELECT devolver_pedidos FROM usuarios WHERE id = ?");
$stmtUserAux->execute([$_SESSION['user_id']]);
$userAuxD = $stmtUserAux->fetch(PDO::FETCH_ASSOC);
$canRevert = (in_array($role, ['Admin', 'SuperAdmin']) || !empty($userAuxD['devolver_pedidos']));

// Cargar configuración WhatsApp para el modal
try {
    $stmtWa = $db->query("SELECT clave, valor FROM configuracion WHERE clave IN ('whatsapp_phone_sender_id','whatsapp_template_id','whatsapp_activo','onurix_api_id','onurix_api_key','sonido_habilitado','sonido_tema', 'sms_crear_enabled', 'sms_crear_checked_default', 'wa_crear_enabled', 'wa_crear_checked_default')");
    $waCfg = $stmtWa->fetchAll(PDO::FETCH_KEY_PAIR);
}
catch (\Exception $e) {
    $waCfg = [];
}
$waActivo = ($waCfg['whatsapp_activo'] ?? '1') === '1';
$waPhoneSenderId = $waCfg['whatsapp_phone_sender_id'] ?? '';
$waTemplateId = $waCfg['whatsapp_template_id'] ?? '';
$waCredsOk = !empty($waCfg['onurix_api_id']) && !empty($waCfg['onurix_api_key']) && !empty($waPhoneSenderId) && !empty($waTemplateId);

$smsCrearEnabled = ($waCfg['sms_crear_enabled'] ?? '1') === '1';
$smsCrearCheckedDefault = ($waCfg['sms_crear_checked_default'] ?? '1') === '1';
$waCrearEnabled = ($waCfg['wa_crear_enabled'] ?? '1') === '1';
$waCrearCheckedDefault = ($waCfg['wa_crear_checked_default'] ?? '1') === '1';

$sonidoHabilitado = $waCfg['sonido_habilitado'] ?? '1';
$sonidoTema = $waCfg['sonido_tema'] ?? 'cristal';


/**
 * Elimina precio/abonado del array de pedido para roles no autorizados.
 * Evita que los precios aparezcan en el HTML del cliente.
 */
function sanitizePedido(array $p, bool $canViewPrices): array
{
    if (!$canViewPrices) {
        unset($p['total'], $p['abonado']);
    }
    return $p;
}

/** Aplica sanitizePedido a un array de pedidos */
function sanitizeList(array $list, bool $canViewPrices): array
{
    return array_map(fn($p) => sanitizePedido($p, $canViewPrices), $list);
}

function getPaymentInfo($p, $canViewPrices)
{

    $estado = $p['estado_pago'] ?? 'no_pago';
    $lbl = 'No Pago';
    $bg = 'no-pago';

    if ($estado === 'pago_completo') {
        $bg = 'pago-completo';
        $lbl = $canViewPrices ? 'Pago Completo ($' . number_format($p['total'] ?? 0, 0) . ')' : 'Pago Completo';
    }
    elseif ($estado === 'abono') {
        $bg = 'abono';
        $lbl = $canViewPrices ? 'Abono ($' . number_format($p['abonado'] ?? 0, 0) . ')' : 'Abono';
    }
    elseif ($estado === 'no_pago') {
        $bg = 'no-pago';
        if ($canViewPrices && !empty($p['total'])) {
            $lbl = 'No Pago ($' . number_format($p['total'] ?? 0, 0) . ')';
        }
        else {
            $lbl = 'No Pago';
        }
    }

    return "<div class='badge {$bg}'>{$lbl}</div>";
}

function getPriorityBadge($p)
{
    $base = "display:inline-block;padding:3px 9px;border-radius:20px;font-size:.72rem;font-weight:700;";
    $prio = $p['prioridad'] ?? 'normal';
    $badge = "";
    if ($prio === 'prioridad')
        $badge = "<span style=\"{$base}background:#ef4444;color:#fff;animation:pulse-badge 2s infinite;\">⚠️ Prioridad</span>";
    else if ($prio === 'largo')
        $badge = "<span style=\"{$base}background:#6366f1;color:#fff;\">📅 Largo</span>";
    else
        $badge = "<span style=\"{$base}background:rgba(100,116,139,.18);color:#475569;border:1px solid #cbd5e1;\">🕒 Normal</span>";

    if (!empty($p['fue_editado'])) {
        $badge .= " <span style=\"{$base}background:#8b5cf6;color:#fff;margin-left:5px;\">✏️ Editado</span>";
    }

    return $badge;
}

// Retorna la clase CSS de urgencia según fecha_entrega_esperada
function getDeadlineClass($p)
{
    if (empty($p['fecha_entrega_esperada']))
        return '';
    $ahora = new DateTime('now', new DateTimeZone('-05:00'));
    $limite = new DateTime($p['fecha_entrega_esperada'] . ' 23:59:59', new DateTimeZone('-05:00'));
    $diffHoras = ($limite->getTimestamp() - $ahora->getTimestamp()) / 3600;
    if ($diffHoras < -48)
        return 'deadline-critical'; // >48h vencido: rojo fuerte
    if ($diffHoras < 0)
        return 'deadline-overdue'; // vencido: rojo suave
    if ($diffHoras <= 12)
        return 'deadline-soon'; // <12h para vencer: naranja
    return '';
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Banner - Recepción de Pedidos</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4F46E5;
            --bg-color: #0F172A;
            --border: rgba(255, 255, 255, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }
        
        .hover-underline:hover { text-decoration: underline; }

        body {
            background-color: var(--bg-color);
            display: flex;
            height: 100vh;
            overflow: hidden;
            color: #1E293B;
        }

        /* Side bar styles provided by component */

        /* Contenido principal con gradiente claro */
        .main-content {
            flex: 1;
            padding: 40px;
            overflow-y: auto;
            background: linear-gradient(135deg, #eef2ff 0%, #fae8ff 100%);
        }

        .header h1 {
            font-size: 2rem;
            color: #1e1b4b;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .header p {
            color: #64748b;
            font-size: 0.95rem;
            margin-bottom: 30px;
        }

        /* Stats Cards */
        .stats-grid {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .stat-card {
            flex: 1;
            min-width: 180px;
            background: linear-gradient(45deg, #6366f1, #8b5cf6);
            border-radius: 12px;
            padding: 20px;
            color: #ffffff;
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s, filter 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -5px rgba(99, 102, 241, 0.35);
            filter: brightness(1.08);
        }

        /* Modal Stat Records */
        #modalStatRecords .modal-content {
            width: 900px;
            max-width: 97vw;
        }

        .stat-modal-search {
            width: 100%;
            padding: 10px 16px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            font-size: 0.9rem;
            outline: none;
            margin-bottom: 20px;
            transition: border-color 0.2s, box-shadow 0.2s;
            color: #1e293b;
        }

        .stat-modal-search:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12);
        }

        .stat-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.86rem;
        }

        .stat-table thead th {
            background: #f1f5f9;
            padding: 10px 12px;
            text-align: left;
            font-weight: 700;
            color: #475569;
            border-bottom: 2px solid #e2e8f0;
            white-space: nowrap;
        }

        .stat-table tbody tr {
            border-bottom: 1px solid #f1f5f9;
            transition: background 0.15s;
            cursor: pointer;
        }

        .stat-table tbody tr:hover {
            background: #eef2ff;
        }

        .stat-table tbody td {
            padding: 9px 12px;
            color: #334155;
            vertical-align: middle;
        }

        .stat-table .badge {
            margin-bottom: 0;
        }

        .prioridad {
            background: #ef4444 !important;
        }

        .largo {
            background: #6366f1 !important;
        }

        .stat-icon {
            font-size: 2rem;
            opacity: 0.9;
        }

        .stat-info h2 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 2px;
        }

        .stat-info p {
            font-size: 0.8rem;
            font-weight: 500;
            opacity: 0.9;
        }

        /* Actions */
        .actions-bar {
            display: flex;
            gap: 15px;
            margin-bottom: 40px;
        }

        .btn {
            padding: 12px 24px;
            border-radius: 20px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            border: none;
            color: white;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
        }

        .btn-primary {
            background: linear-gradient(45deg, #6366f1, #7c3aed);
        }

        .btn-secondary {
            background: linear-gradient(45deg, #818cf8, #6366f1);
        }

        /* Columns Grid */
        .columns-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 24px;
        }

        .column-panel {
            background: #ffffff;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
            min-height: 500px;
        }

        .search-box {
            position: relative;
            margin-bottom: 30px;
        }

        .search-box input {
            width: 100%;
            padding: 12px 15px 12px 40px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            color: #334155;
            font-size: 0.9rem;
            outline: none;
            transition: all 0.3s;
        }

        .search-box input:focus {
            border-color: #6366f1;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .search-box svg {
            position: absolute;
            left: 12px;
            top: 12px;
            color: #94a3b8;
            width: 18px;
            height: 18px;
        }

        .column-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 20px;
        }

        /* Order Cards */
        .order-card {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            transition: border-color 0.2s;
        }

        .order-card:hover {
            border-color: #6366f1;
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 8px;
        }

        .order-title {
            font-weight: 600;
            color: #1e3a8a;
            font-size: 0.95rem;
        }

        .order-status {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.7rem;
        }

        .order-status.proceso {
            background: #10b981;
        }

        .order-status.recepcion {
            background: #3b82f6;
        }

        .order-status.preparado {
            background: #6366f1;
        }

        .order-details {
            font-size: 0.85rem;
            color: #475569;
            line-height: 1.5;
        }

        .order-details strong {
            color: #1e293b;
        }

        .order-actions {
            margin-top: 12px;
        }

        .btn-sm {
            background: #64748b;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.75rem;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-sm:hover {
            background: #475569;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(4px);
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 12px;
            width: 750px;
            max-width: 95%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            color: #0F172A;
            position: relative;
        }

        .modal-content::-webkit-scrollbar {
            width: 8px;
        }

        .modal-content::-webkit-scrollbar-thumb {
            background-color: #cbd5e1;
            border-radius: 10px;
        }

        .modal-content h2 {
            margin-bottom: 25px;
            color: #1e1b4b;
            font-size: 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 10px;
        }

        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-col {
            flex: 1;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            color: #475569;
        }

        .form-group label i {
            color: #6366f1;
            font-size: 1.1rem;
            font-style: normal;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            font-size: 0.95rem;
            outline: none;
            transition: all 0.2s;
            color: #1e293b;
        }

        .form-control:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        /* Selection Cards */
        .selection-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }

        .selection-card {
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 20px 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            background: #ffffff;
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .selection-card:hover {
            border-color: #cbd5e1;
            background: #f8fafc;
        }

        .selection-card.active {
            border-color: #6366f1;
            background: rgba(99, 102, 241, 0.05);
        }

        .selection-card .icon-circle {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: white;
            margin-bottom: 5px;
        }

        .selection-card .card-title {
            font-weight: 700;
            font-size: 1rem;
            color: #1e293b;
        }

        .selection-card .card-subtitle {
            font-size: 0.8rem;
            color: #64748b;
        }

        /* Pagos Colors */
        .sc-pago-completo.active {
            border-color: #10b981;
            background: rgba(16, 185, 129, 0.05);
        }

        .sc-pago-completo .icon-circle {
            background: #10b981;
        }

        .sc-abono.active {
            border-color: #f59e0b;
            background: rgba(245, 158, 11, 0.05);
        }

        .sc-abono .icon-circle {
            background: #f59e0b;
        }

        .sc-no-pago.active {
            border-color: #ef4444;
            background: rgba(239, 68, 68, 0.05);
        }

        .sc-no-pago .icon-circle {
            background: #ef4444;
        }

        /* Prioridad Colors */
        .sc-prioridad.active {
            border-color: #ef4444;
            background: rgba(239, 68, 68, 0.05);
        }

        .sc-prioridad .icon-circle {
            color: #ef4444;
            background: transparent;
            font-size: 1.8rem;
        }

        .sc-prioridad.active .card-title {
            color: #ef4444;
        }

        .sc-normal.active {
            border-color: #10b981;
            background: #10b981;
            color: white;
        }

        .sc-normal.active .card-title,
        .sc-normal.active .card-subtitle,
        .sc-normal.active .icon-circle {
            color: white;
        }

        .sc-normal .icon-circle {
            color: #10b981;
            background: transparent;
            font-size: 1.8rem;
        }

        .sc-largo.active {
            border-color: #6366f1;
            background: rgba(99, 102, 241, 0.05);
        }

        .sc-largo .icon-circle {
            color: #6366f1;
            background: transparent;
            font-size: 1.8rem;
        }

        .sc-largo.active .card-title {
            color: #6366f1;
        }

        /* File Upload */
        .file-upload-box {
            border: 2px dashed #cbd5e1;
            border-radius: 10px;
            padding: 30px;
            text-align: center;
            color: #64748b;
            background: #f8fafc;
            cursor: pointer;
            transition: all 0.2s;
        }

        .file-upload-box:hover {
            border-color: #6366f1;
            background: #eff6ff;
            color: #6366f1;
        }

        .file-upload-box i {
            font-size: 2rem;
            color: #818cf8;
            margin-bottom: 10px;
            display: block;
        }

        /* Checkbox & Button */
        .sms-check {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
            color: #1e293b;
            margin-top: 15px;
            margin-bottom: 10px;
        }

        .sms-check input {
            width: 20px;
            height: 20px;
            accent-color: #10b981;
        }

        .sms-check i {
            color: #10b981;
            font-size: 1.2rem;
        }

        /* WhatsApp checkbox green */
        .wa-check {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
            color: #1e293b;
            margin-top: 6px;
            margin-bottom: 6px;
        }

        .wa-check input {
            width: 20px;
            height: 20px;
            accent-color: #25d366;
        }

        .wa-section {
            background: linear-gradient(135deg, rgba(37, 211, 102, .07), rgba(37, 211, 102, .03));
            border: 1px solid rgba(37, 211, 102, .25);
            border-radius: 10px;
            padding: 14px 16px;
            margin-top: 4px;
            margin-bottom: 20px;
        }

        .wa-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: #25d366;
            color: white;
            border-radius: 20px;
            padding: 3px 10px;
            font-size: 0.75rem;
            font-weight: 700;
            margin-left: 6px;
        }

        .wa-creds-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: rgba(37, 211, 102, .15);
            border: 1px solid rgba(37, 211, 102, .35);
            color: #16a34a;
            border-radius: 8px;
            padding: 5px 12px;
            font-size: 0.78rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 8px;
            transition: background .2s;
        }

        .wa-creds-btn:hover {
            background: rgba(37, 211, 102, .28);
        }

        /* Modal WhatsApp Credenciales */
        .modal-wa {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, .55);
            z-index: 1100;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(5px);
        }

        .modal-wa .mw-box {
            background: #1e293b;
            border: 1px solid rgba(37, 211, 102, .3);
            border-radius: 18px;
            padding: 32px;
            width: 480px;
            max-width: 95vw;
            box-shadow: 0 30px 80px rgba(0, 0, 0, .5);
            animation: fadeUp .22s ease;
            color: #f1f5f9;
        }

        .modal-wa .mw-box h2 {
            font-size: 1.2rem;
            font-weight: 700;
            color: #25d366;
            margin-bottom: 6px;
            border: none;
        }

        .modal-wa .mw-sub {
            font-size: 0.82rem;
            color: #94a3b8;
            margin-bottom: 20px;
        }

        .modal-wa .mw-field {
            margin-bottom: 14px;
        }

        .modal-wa .mw-label {
            display: block;
            font-size: .8rem;
            font-weight: 600;
            color: #94a3b8;
            margin-bottom: 6px;
        }

        .modal-wa .mw-input {
            width: 100%;
            background: rgba(0, 0, 0, .3);
            border: 1px solid rgba(255, 255, 255, .1);
            color: #f1f5f9;
            border-radius: 9px;
            padding: 10px 13px;
            font-size: .88rem;
            outline: none;
            transition: border-color .2s;
        }

        .modal-wa .mw-input:focus {
            border-color: #25d366;
        }

        .modal-wa .mw-footer {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 22px;
        }

        .btn-wa-save {
            background: #25d366;
            color: white;
            border: none;
            border-radius: 9px;
            padding: 10px 22px;
            font-weight: 700;
            font-size: .88rem;
            cursor: pointer;
            transition: background .2s;
        }

        .btn-wa-save:hover {
            background: #1ebe5d;
        }

        .btn-wa-cancel {
            background: rgba(255, 255, 255, .07);
            color: #94a3b8;
            border: 1px solid rgba(255, 255, 255, .1);
            border-radius: 9px;
            padding: 10px 18px;
            font-weight: 600;
            font-size: .88rem;
            cursor: pointer;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(14px);
            }
        }

        /* Payment Submodal overlay */
        .submodal-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(5px);
            display: none;
            justify-content: center;
            align-items: center;
            border-radius: 12px;
            z-index: 10;
        }

        .submodal-box {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            width: 80%;
            border: 1px solid #e2e8f0;
        }

        /* Sweet Alert mimic */
        .sweet-alert {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0.9);
            background: white;
            padding: 40px;
            border-radius: 16px;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.3);
            z-index: 2000;
            display: none;
            opacity: 0;
            transition: all 0.3s;
        }

        .sweet-alert.show {
            transform: translate(-50%, -50%) scale(1);
            opacity: 1;
        }

        .sa-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 4px solid #10b981;
            color: #10b981;
            font-size: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 600;
            color: white;
            margin-bottom: 5px;
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

        .empty-state {
            text-align: center;
            color: #64748b;
            font-size: 0.9rem;
            padding: 2rem 0;
        }

        /* Overriding sidebar styles for this container */
        .sidebar * {
            color: var(--text-main);
        }

        .sidebar .nav-item {
            color: #94A3B8;
        }

        .sidebar .nav-item.active {
            color: #fff;
        }
    </style>
</head>

<body>

    <?php include __DIR__ . '/components/sidebar.php'; ?>

    <main class="main-content">
        <div class="header">
            <div style="display:flex; align-items:center; gap:16px;">
                <button id="btnHamburger"
                    style="background:none; border:none; color:#1e293b; cursor:pointer; font-size:1.5rem; display:flex;">☰</button>
                <div>
                    <h1>Recepción de Pedidos</h1>
                    <p>Haz clic en cualquier métrica para ver sus pedidos detallados.</p>
                </div>
            </div>
        </div>

        <!-- METRIC CHIPS — 2 filas simétricas con grilla fija -->
        <style>
            .metrics-section {
                display: flex;
                flex-direction: column;
                gap: 10px;
                margin-bottom: 25px;
            }

            .metrics-row {
                display: grid;
                grid-template-columns: repeat(6, 1fr);
                gap: 10px;
            }

            /* Responsive: mismas columnas en ambas filas siempre */
            @media (max-width: 1280px) {
                .metrics-row {
                    grid-template-columns: repeat(4, 1fr);
                }
            }

            @media (max-width: 860px) {
                .metrics-row {
                    grid-template-columns: repeat(3, 1fr);
                }
            }

            @media (max-width: 560px) {
                .metrics-row {
                    grid-template-columns: repeat(2, 1fr);
                }
            }

            .metric-chip {
                background: rgba(255, 255, 255, 0.80);
                backdrop-filter: blur(8px);
                border: 1px solid rgba(99, 102, 241, 0.18);
                border-radius: 10px;
                padding: 11px 14px;
                color: #1e1b4b;
                font-size: 0.8rem;
                font-weight: 600;
                display: flex;
                align-items: center;
                gap: 8px;
                cursor: pointer;
                transition: all 0.2s;
                white-space: nowrap;
                overflow: hidden;
                box-shadow: 0 2px 6px rgba(99, 102, 241, 0.07);
            }

            .metric-chip:hover {
                background: rgba(255, 255, 255, 0.97);
                border-color: rgba(99, 102, 241, 0.38);
                box-shadow: 0 4px 14px rgba(99, 102, 241, 0.15);
                transform: translateY(-2px);
            }

            .metric-chip strong {
                color: #4f46e5;
                font-size: 1.05rem;
                margin-left: auto;
                font-weight: 800;
                flex-shrink: 0;
            }

            .metric-chip-ghost {
                visibility: hidden;
                pointer-events: none;
            }

            .m-dot {
                width: 9px;
                height: 9px;
                border-radius: 50%;
                display: inline-block;
                flex-shrink: 0;
            }

            .bg-blue {
                background: #3b82f6;
            }

            .bg-orange {
                background: #f59e0b;
            }

            .bg-green {
                background: #10b981;
            }

            .bg-purple {
                background: #8b5cf6;
            }

            .bg-pink {
                background: #ec4899;
            }

            .bg-red {
                background: #ef4444;
            }

            .bg-indigo {
                background: #6366f1;
            }

            .bg-teal {
                background: #14b8a6;
            }

            .bg-lime {
                background: #22c55e;
            }

            .bg-amber {
                background: #f97316;
            }

            .bg-sky {
                background: #0ea5e9;
            }

            .bg-violet {
                background: #7c3aed;
            }

            .bg-rose {
                background: #f43f5e;
            }
        </style>

        <!-- Estilos de deadline (vencimiento) para tarjetas -->
        <style>
            @keyframes pulse-border-orange {

                0%,
                100% {
                    box-shadow: 0 0 0 0 rgba(251, 146, 60, 0.5), 0 0 0 3px rgba(251, 146, 60, 0.25);
                    border-color: #fb923c;
                }

                50% {
                    box-shadow: 0 0 0 4px rgba(251, 146, 60, 0.3), 0 0 0 6px rgba(251, 146, 60, 0.1);
                    border-color: #f97316;
                }
            }

            @keyframes pulse-border-red {

                0%,
                100% {
                    box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4), 0 0 0 2px rgba(239, 68, 68, 0.2);
                    border-color: #f87171;
                }

                50% {
                    box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.3), 0 0 0 6px rgba(239, 68, 68, 0.1);
                    border-color: #ef4444;
                }
            }

            @keyframes pulse-border-red-critical {

                0%,
                100% {
                    box-shadow: 0 0 0 0 rgba(185, 28, 28, 0.6), 0 0 0 3px rgba(185, 28, 28, 0.4);
                    border-color: #dc2626;
                }

                50% {
                    box-shadow: 0 0 0 5px rgba(185, 28, 28, 0.4), 0 0 0 9px rgba(185, 28, 28, 0.15);
                    border-color: #b91c1c;
                }
            }

            .order-card.deadline-soon {
                animation: pulse-border-orange 1.8s ease-in-out infinite;
                border: 2px solid #fb923c !important;
            }

            .order-card.deadline-overdue {
                animation: pulse-border-red 2s ease-in-out infinite;
                border: 2px solid #f87171 !important;
                background: rgba(254, 226, 226, 0.35) !important;
            }

            .order-card.deadline-critical {
                animation: pulse-border-red-critical 1.2s ease-in-out infinite;
                border: 2px solid #dc2626 !important;
                background: rgba(254, 202, 202, 0.55) !important;
            }
        </style>

        <?php
$mesesLst = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
$mesActual = $mesesLst[date('n') - 1];
?>
        <style>
            .rec-kpi-grid {
                display: grid;
                grid-template-columns: repeat(5, 1fr);
                gap: 14px;
                width: 100%;
                margin-bottom: 4px;
            }
            .rec-kpi-grid.no-financials {
                grid-template-columns: repeat(2, 1fr);
                max-width: 480px;
            }
            .rec-kpi-card {
                background: #ffffff;
                border: 1.5px solid #e2e8f0;
                border-radius: 14px;
                padding: 16px 18px;
                position: relative;
                overflow: hidden;
                cursor: pointer;
                transition: transform .22s, box-shadow .22s, border-color .22s;
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                min-height: 100px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            }
            .rec-kpi-card:hover {
                transform: translateY(-3px);
                box-shadow: 0 10px 24px -6px rgba(0,0,0,0.13);
            }
            .rec-kpi-card.no-cursor { cursor: default; }
            .rec-kpi-top {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                margin-bottom: 8px;
            }
            .rec-kpi-label {
                font-size: .70rem;
                text-transform: uppercase;
                letter-spacing: .07em;
                color: #64748b;
                font-weight: 700;
                line-height: 1.3;
            }
            .rec-kpi-icon {
                width: 34px;
                height: 34px;
                border-radius: 10px;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
            }
            .rec-kpi-icon svg { display: block; }
            .rec-kpi-value {
                font-size: 1.65rem;
                font-weight: 800;
                letter-spacing: -.02em;
                line-height: 1;
                color: #0f172a;
            }
            .rec-kpi-bar {
                height: 3px;
                background: #f1f5f9;
                border-radius: 4px;
                margin-top: 10px;
                overflow: hidden;
            }
            .rec-kpi-bar-fill { height: 100%; border-radius: 4px; }
            @media (max-width: 900px) {
                .rec-kpi-grid { grid-template-columns: repeat(3, 1fr); }
                .rec-kpi-grid.no-financials { grid-template-columns: repeat(2, 1fr); max-width: 100%; }
            }
            @media (max-width: 560px) {
                .rec-kpi-grid { grid-template-columns: repeat(2, 1fr); }
            }
        </style>

        <div class="rec-kpi-grid<?=!$canViewReceptionMetrics ? ' no-financials' : ''?>">

            <!-- Caducados -->
            <div class="rec-kpi-card" onclick="openStatModal('caducados','Caducados')"
                style="border-color:#fecdd3; border-left: 4px solid #f43f5e;">
                <div class="rec-kpi-top">
                    <span class="rec-kpi-label">Caducados</span>
                    <div class="rec-kpi-icon" style="background:#fff1f2;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#f43f5e" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                    </div>
                </div>
                <div class="rec-kpi-value" style="color:#f43f5e;"><?= number_format($stats['caducados'])?></div>
                <div class="rec-kpi-bar"><div class="rec-kpi-bar-fill" style="width:100%;background:#f43f5e;"></div></div>
            </div>

            <!-- Pedidos del Día -->
            <div class="rec-kpi-card" onclick="openStatModal('pedidos_hoy','Pedidos del Día')"
                style="border-color:#c7d2fe; border-left: 4px solid #6366f1;">
                <div class="rec-kpi-top">
                    <span class="rec-kpi-label">Pedidos del Día</span>
                    <div class="rec-kpi-icon" style="background:#eef2ff;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><line x1="9" y1="12" x2="15" y2="12"/><line x1="9" y1="16" x2="13" y2="16"/>
                        </svg>
                    </div>
                </div>
                <div class="rec-kpi-value" style="color:#4f46e5;"><?= number_format($stats['pedidos_hoy'])?></div>
                <div class="rec-kpi-bar"><div class="rec-kpi-bar-fill" style="width:100%;background:#6366f1;"></div></div>
            </div>

            <?php if ($canViewReceptionMetrics): ?>
            <!-- Recaudado del Día -->
            <div class="rec-kpi-card" style="border-color:#bbf7d0; border-left: 4px solid #10b981; cursor:pointer;" onclick="openStatModal('recaudado_dia', 'Recaudado del Día')">
                <div class="rec-kpi-top">
                    <span class="rec-kpi-label">Recaudado del Día</span>
                    <div class="rec-kpi-icon" style="background:#f0fdf4;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                        </svg>
                    </div>
                </div>
                <div class="rec-kpi-value" style="color:#059669; font-size:1.4rem;">$<?= number_format($recaudadoDia)?></div>
                <div style="font-size: 0.72rem; color: #475569; margin-top: 5px; display: flex; justify-content: space-between;">
                    <span class="hover-underline" onclick="event.stopPropagation(); openStatModal('recaudado_efectivo', 'Efectivo Recaudado')">💵 Ef: $<?= number_format($recaudadoEfectivo) ?></span>
                    <span class="hover-underline" onclick="event.stopPropagation(); openStatModal('recaudado_transferencia', 'Transferencia Recaudado')">🏦 Tr: $<?= number_format($recaudadoTransferencia) ?></span>
                </div>
            </div>

            <!-- Facturado del Día -->
            <div class="rec-kpi-card" style="border-color:#fed7aa; border-left: 4px solid #f59e0b; cursor:pointer;" onclick="openStatModal('facturado_dia', 'Facturado del Día')">
                <div class="rec-kpi-top">
                    <span class="rec-kpi-label">Facturado del Día</span>
                    <div class="rec-kpi-icon" style="background:#fffbeb;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/>
                        </svg>
                    </div>
                </div>
                <div class="rec-kpi-value" style="color:#d97706; font-size:1.4rem;">$<?= number_format($facturadoDia)?></div>
                <div style="font-size: 0.72rem; color: #475569; margin-top: 5px; display: flex; justify-content: space-between;">
                    <span class="hover-underline" onclick="event.stopPropagation(); openStatModal('facturado_efectivo', 'Efectivo Facturado')">💵 Ef: $<?= number_format($facturadoEfectivo) ?></span>
                    <span class="hover-underline" onclick="event.stopPropagation(); openStatModal('facturado_transferencia', 'Transferencia Facturado')">🏦 Tr: $<?= number_format($facturadoTransferencia) ?></span>
                </div>
            </div>

            <!-- CxC del Día -->
            <div class="rec-kpi-card" style="border-color:#fecdd3; border-left: 4px solid #f43f5e; cursor:pointer;" onclick="openStatModal('cxc_dia', 'CxC del Día')">
                <div class="rec-kpi-top">
                    <span class="rec-kpi-label">CxC del Día</span>
                    <div class="rec-kpi-icon" style="background:#fff1f2;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#f43f5e" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                        </svg>
                    </div>
                </div>
                <div class="rec-kpi-value" style="color:#f43f5e; font-size:1.4rem;">$<?= number_format(max(0, ($cxcDia ?? 0)))?></div>
                <div style="font-size: 0.72rem; color: #475569; margin-top: 5px; display: flex; justify-content: space-between;">
                    <span class="hover-underline" onclick="event.stopPropagation(); openStatModal('cxc_efectivo', 'CxC en Efectivo')">💵 Ef: $<?= number_format($cxcEfectivo) ?></span>
                    <span class="hover-underline" onclick="event.stopPropagation(); openStatModal('cxc_transferencia', 'CxC en Transferencia')">🏦 Tr: $<?= number_format($cxcTransferencia) ?></span>
                </div>
            </div>
            <?php
endif; ?>

        </div>
        <div class="actions-bar">
            <button class="btn btn-primary" onclick="document.getElementById('modalPedido').style.display='flex'">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                CREAR NUEVO PEDIDO
            </button>
            <?php if (($_SESSION['role'] ?? '') === 'Admin'): ?>
            <button class="btn btn-secondary" onclick="document.getElementById('modalSmsManual').style.display='flex'">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                </svg>
                ENVIAR SMS MANUAL
            </button>
            <?php
endif; ?>
        </div>

        <div class="columns-grid">
            <!-- Pedidos Recientes -->
            <div class="column-panel">
                <div class="search-box">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input type="text" placeholder="Buscar por #pedido, guía, cliente..." class="search-input"
                        data-target="list-recientes">
                </div>
                <h3 class="column-title" style="display:flex; justify-content:space-between; align-items:center;">
                    <span style="display:flex; align-items:center; gap:8px;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:#3b82f6;">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="7 10 12 15 17 10"></polyline>
                            <line x1="12" y1="15" x2="12" y2="3"></line>
                        </svg>
                        Pedidos en Recepción
                    </span>
                    <span
                        style="background:#eef2ff; color:#3b82f6; padding:4px 12px; border-radius:20px; font-size:0.9rem; font-weight:700; border: 1px solid #bfdbfe;">
                        <?= number_format($stats['recepcion'])?>
                    </span>
                </h3>
                <div id="list-recientes">
                    <?php if (empty($recientes)): ?>
                    <div class="empty-state">No hay pedidos registrados.</div>
                    <?php
else:
    foreach ($recientes as $p): ?>
                    <div class="order-card row-item <?= getDeadlineClass($p)?>">
                        <div class="order-header">
                            <span class="order-title">#PED-
                                <?= str_pad($p['id'], 4, '0', STR_PAD_LEFT)?> -
                                <?= htmlspecialchars($p['cliente_nombre'])?>
                            </span>
                            <?= getPriorityBadge($p)?>
                        </div>
                        <div class="order-details">
                            <p><strong>Área:</strong>
                                <?= htmlspecialchars($p['area_nombre'])?>
                            </p>
                            <p><strong>Fase:</strong>
                                <?= ucfirst($p['fase_actual'])?>
                            </p>
                            <p><strong>Fecha:</strong>
                                <?= $p['created_at']?>
                            </p>
                            <?php if (!empty($p['creado_por'])): ?>
                            <p style="margin-top:4px;">
                                <span
                                    style="display:inline-block;background:#e0e7ff;color:#4338ca;padding:2px 8px;border-radius:12px;font-size:0.75rem;font-weight:600;border:1px solid #c7d2fe;">
                                    👤 Creado por
                                    <?= htmlspecialchars($p['creado_por'])?>
                                </span>
                            </p>
                            <?php
        endif; ?>
                            <div style="margin-top: 10px;">
                                <?= getPaymentInfo($p, $canViewPrices)?>
                            </div>
                        </div>
                        <div class="order-actions">
                            <button class="btn-sm"
                                onclick="verDetalles(<?= htmlspecialchars(json_encode(sanitizePedido($p, $canViewPrices)))?>)">👁️
                                Visualizar</button>
                        </div>
                    </div>
                    <?php
    endforeach;
endif; ?>
                </div>
            </div>

            <!-- Pedidos en Proceso -->
            <div class="column-panel">
                <div class="search-box">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input type="text" placeholder="Buscar por cliente, área..." class="search-input"
                        data-target="list-proceso">
                </div>
                <h3 class="column-title" style="display:flex; justify-content:space-between; align-items:center;">
                    <span style="display:flex; align-items:center; gap:8px;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:#f59e0b;">
                            <circle cx="12" cy="12" r="3"></circle>
                            <path
                                d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z">
                            </path>
                        </svg>
                        Pedidos en Proceso
                    </span>
                    <span
                        style="background:#fffbeb; color:#f59e0b; padding:4px 12px; border-radius:20px; font-size:0.9rem; font-weight:700; border: 1px solid #fde68a;">
                        <?= number_format($stats['proceso'] + $stats['preparados'])?>
                    </span>
                </h3>
                <div id="list-proceso">
                    <?php if (empty($en_proceso)): ?>
                    <div class="empty-state">No hay pedidos en proceso.</div>
                    <?php
else:
    foreach ($en_proceso as $p): ?>
                    <div class="order-card row-item <?= getDeadlineClass($p)?>">
                        <div class="order-header">
                            <span class="order-title">Pedido en Proceso (
                                <?= htmlspecialchars($p['area_nombre'])?>)
                            </span>
                            <?= getPriorityBadge($p)?>
                            <div class="order-status proceso">✓</div>
                        </div>
                        <div class="order-details">
                            <p><strong>ID:</strong> #PED-
                                <?= str_pad($p['id'], 4, '0', STR_PAD_LEFT)?>
                            </p>
                            <p><strong>Cliente:</strong> <span class="client-name">
                                    <?= htmlspecialchars($p['cliente_nombre'])?>
                                </span></p>
                            <p><strong>Estado:</strong> Trabajando en
                                <?= htmlspecialchars($p['area_nombre'])?>
                            </p>
                            <p><strong>Movimiento:</strong>
                                <?= $p['last_movement_at']?>
                            </p>
                            <?php if ($p['fase_actual'] === 'recepcion'): ?>
                            <p style="margin-top:4px;">
                                <span
                                    style="display:inline-block;background:#fee2e2;color:#b91c1c;padding:2px 8px;border-radius:12px;font-size:0.75rem;font-weight:600;border:1px solid #fecaca;">
                                    ⏳ Pedido a la espera
                                </span>
                            </p>
                            <?php
        elseif ($p['fase_actual'] === 'proceso' && !empty($p['asignado_a_nombre'])): ?>
                            <p style="margin-top:4px;">
                                <span
                                    style="display:inline-block;background:#fff7ed;color:#ea580c;padding:2px 8px;border-radius:12px;font-size:0.75rem;font-weight:600;border:1px solid #ffedd5;">
                                    ⚙️ Trabajado por
                                    <?= htmlspecialchars($p['asignado_a_nombre'])?>
                                </span>
                            </p>
                            <?php
        elseif ($p['fase_actual'] === 'preparado' && !empty($p['preparado_por'])): ?>
                            <p style="margin-top:4px;">
                                <span
                                    style="display:inline-block;background:#ecfdf5;color:#059669;padding:2px 8px;border-radius:12px;font-size:0.75rem;font-weight:600;border:1px solid #a7f3d0;">
                                    ✅ Preparado por
                                    <?= htmlspecialchars($p['preparado_por'])?>
                                </span>
                            </p>
                            <?php
        endif; ?>
                            <div style="margin-top: 10px;">
                                <?= getPaymentInfo($p, $canViewPrices)?>
                            </div>
                        </div>
                        <div class="order-actions">
                            <button class="btn-sm"
                                onclick="verDetalles(<?= htmlspecialchars(json_encode(sanitizePedido($p, $canViewPrices)))?>)">👁️
                                Visualizar</button>
                        </div>
                    </div>
                    <?php
    endforeach;
endif; ?>
                </div>
            </div>

            <!-- Pedidos Finalizados -->
            <div class="column-panel">
                <div class="search-box" style="display: flex; align-items: center;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" style="min-width: 16px;">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input type="text" placeholder="Buscar por #pedido..." class="search-input"
                        data-target="list-finalizados" style="flex:1;">
                    <select class="search-input"
                        style="flex:1; padding: 0 5px; border-left: 1px solid #e2e8f0; font-size: 0.8rem; background: transparent; cursor: pointer; outline: none; appearance: auto;"
                        onchange="var inp = this.previousElementSibling; inp.value = this.value; inp.dispatchEvent(new Event('keyup')); this.value = '';">
                        <option value="">▶ Todos (Borrar filtro)</option>
                        <option value="pedidos no entregados">⏳ No Entregados (Todos)</option>
                        <option value="pedidos entregados">📦 Entregados (Todos)</option>
                        <option value="pedidos no entregados pago">-- No Entreg. Completos</option>
                        <option value="pedidos no entregados no pagos">-- No Entreg. No Pagos</option>
                        <option value="pedido no entregado abonado">-- No Entreg. Abonados</option>
                        <option value="pedidos entregados pagos">-- Entregados Completos</option>
                        <option value="pedidos entregados no pagados">-- Entregados No Pagos</option>
                        <option value="pedidos entregados abonados">-- Entregados Abonados</option>
                    </select>
                </div>
                <h3 class="column-title" style="display:flex; justify-content:space-between; align-items:center;">
                    <span style="display:flex; align-items:center; gap:8px;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:#8b5cf6;">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                        Pedidos Finalizados
                    </span>
                    <span
                        style="background:#faf5ff; color:#8b5cf6; padding:4px 12px; border-radius:20px; font-size:0.9rem; font-weight:700; border: 1px solid #e9d5ff;">
                        <?= number_format($stats['finalizados'])?>
                    </span>
                </h3>
                <div id="list-finalizados">
                    <?php if (empty($finalizados)): ?>
                    <div class="empty-state">No hay pedidos finalizados.</div>
                    <?php
else:
    foreach ($finalizados as $p):
        $entregado = !empty($p['entregado']);
        $pagoCompleto = ($p['estado_pago'] === 'pago_completo');

        $cardStyle = "opacity: 0.8; border-color: #e2e8f0;";
        if ($entregado) {
            $cardStyle = "opacity: 0.6; border-color: #e2e8f0;";
            if (!$pagoCompleto) {
                $cardStyle .= " background-color: #fee2e2; border-color: #fca5a5;"; // rojo suave
            }
            else {
                // Entregado y Pago Completo -> 20% más tenue (opacity 0.4) y escala de grises para todos los badges
                $cardStyle = "opacity: 0.4; border-color: #cbd5e1; background-color: #f1f5f9; filter: grayscale(100%);";
            }
        }

        $entregadoBadge = $entregado ? '<span class="badge" style="background:#10b981;color:white;font-size:0.7rem;margin-left:5px;">📦 Entregado</span>' : '<span class="badge" style="background:#f59e0b;color:white;font-size:0.7rem;margin-left:5px;">⏳ No Entregado</span>';

        $searchHidden = '';
        if ($entregado) {
            $searchHidden .= ' pedidos entregados entregado';
            if ($p['estado_pago'] === 'no_pago')
                $searchHidden .= ' pedidos entregados no pagados';
            elseif ($p['estado_pago'] === 'pago_completo')
                $searchHidden .= ' pedidos entregados pagos';
            elseif ($p['estado_pago'] === 'abono')
                $searchHidden .= ' pedidos entregados abonados';
        }
        else {
            $searchHidden .= ' pedidos no entregados no entregado';
            if ($p['estado_pago'] === 'no_pago')
                $searchHidden .= ' pedidos no entregados no pagos';
            elseif ($p['estado_pago'] === 'pago_completo')
                $searchHidden .= ' pedidos no entregados pago';
            elseif ($p['estado_pago'] === 'abono')
                $searchHidden .= ' pedido no entregado abonado';
        }
?>
                    <div class="order-card row-item" style="<?= $cardStyle?>">
                        <div style="display:none;">
                            <?= $searchHidden?>
                        </div>
                        <div class="order-header">
                            <span class="order-title" style="color: #475569;">#PED-
                                <?= str_pad($p['id'], 4, '0', STR_PAD_LEFT)?>
                            </span>
                            <div>
                                <?= getPriorityBadge($p)?>
                                <?= $entregadoBadge?>
                            </div>
                        </div>
                        <div class="order-details">
                            <p><strong>Cliente:</strong>
                                <?= htmlspecialchars($p['cliente_nombre'])?>
                            </p>
                            <p><strong>Terminado en:</strong>
                                <?= $p['last_movement_at']?>
                            </p>
                            <p><strong>Fase Final:</strong>
                                <?= htmlspecialchars($p['area_nombre'])?>
                            </p>
                            <?php if (!empty($p['finalizado_por'])): ?>
                            <p style="margin-top:4px;">
                                <span
                                    style="display:inline-block;background:#fcfdfd;color:#14b8a6;padding:2px 8px;border-radius:12px;font-size:0.75rem;font-weight:600;border:1px solid #99f6e4;">
                                    ✅ Finalizado por
                                    <?= htmlspecialchars($p['finalizado_por'])?>
                                </span>
                            </p>
                            <?php
        endif; ?>
                            <?php if (!empty($p['entregado_por'])): ?>
                            <p style="margin-top:4px;">
                                <span
                                    style="display:inline-block;background:#f8fafc;color:#64748b;padding:2px 8px;border-radius:12px;font-size:0.75rem;font-weight:600;border:1px solid #cbd5e1;">
                                    📦 Entregado por
                                    <?= htmlspecialchars($p['entregado_por'])?>
                                </span>
                            </p>
                            <?php
        endif; ?>
                            <div style="margin-top: 10px;">
                                <?= getPaymentInfo($p, $canViewPrices)?>
                            </div>
                        </div>
                        <div class="order-actions" style="flex-wrap: wrap; gap: 5px;">
                            <?php if (!$entregado && in_array($role, ['Admin', 'SuperAdmin'])): ?>
                            <button class="btn-sm"
                                style="background:#3b82f6; color:white; width: 100%; border:none; padding:8px; border-radius:6px; font-weight:600; cursor:pointer;"
                                onclick="confirmarEntregar(<?= $p['id']?>)">📦 Entregar al Cliente</button>
                            <?php
        endif; ?>
                            <?php if ($entregado && $canRevert): ?>
                            <button class="btn-sm"
                                style="background:#ef4444; color:white; width: 100%; border:none; padding:8px; border-radius:6px; font-weight:600; cursor:pointer;"
                                onclick="confirmarRevertirEntrega(<?= $p['id']?>)">↩️ Revertir de Entregado</button>
                            <?php
        endif; ?>
                            <?php if (!$pagoCompleto && in_array($role, ['Admin', 'SuperAdmin'])): ?>
                            <button class="btn-sm"
                                style="background:#22c55e; color:white; width: 100%; border:none; padding:8px; border-radius:6px; font-weight:600; cursor:pointer;"
                                onclick="abrirModalPagoCompleto(<?= $p['id']?>)">💳 Pagar Completo</button>
                            <?php
        endif; ?>
                            <button class="btn-sm"
                                style="background:#e2e8f0; color:#475569; width: 100%; border:none; padding:8px; border-radius:6px; font-weight:600; cursor:pointer;"
                                onclick="verDetalles(<?= htmlspecialchars(json_encode(sanitizePedido($p, $canViewPrices)))?>)">👁️
                                Resumen</button>
                        </div>
                    </div>
                    <?php
    endforeach;
endif; ?>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal Pago Completo Animado -->
    <div id="modalPagoCompleto" class="modal"
        style="display:none; align-items:center; justify-content:center; background:rgba(0,0,0,0.4);">
        <div class="modal-content"
            style="max-width:380px; text-align:center; padding:30px 20px; border-radius:16px; animation: popIn 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;">
            <style>
                @keyframes popIn {
                    0% {
                        opacity: 0;
                        transform: scale(0.8);
                    }

                    100% {
                        opacity: 1;
                        transform: scale(1);
                    }
                }

                @keyframes iconBounce {

                    0%,
                    100% {
                        transform: translateY(0);
                    }

                    50% {
                        transform: translateY(-10px);
                    }
                }
            </style>
            <div style="font-size:3.5rem; margin-bottom:15px; animation: iconBounce 2s infinite; color:#22c55e;">💳
            </div>
            <h2 style="font-size:1.4rem; color:#1e293b; margin-bottom:10px;">¿Pagar Competo?</h2>
            <p style="color:#64748b; font-size:0.95rem; line-height:1.5; margin-bottom:25px;">
                ¿Desea usted marcar como <strong style="color:#22c55e;">pago completo</strong> este pedido?
                Automáticamente el abono será igual al total del pedido.
            </p>
            <input type="hidden" id="pagoCompletoPedidoId">
            <div style="display:flex; gap:10px; justify-content:center;">
                <button type="button" class="btn-modal"
                    style="background:#cbd5e1; color:#334155; padding:10px 20px; border-radius:8px; border:none; cursor:pointer; font-weight:600;"
                    onclick="document.getElementById('modalPagoCompleto').style.display='none'">Cancelar</button>
                <button type="button" class="btn-modal"
                    style="background:#22c55e; color:white; padding:10px 20px; border-radius:8px; border:none; cursor:pointer; font-weight:600;"
                    onclick="ejecutarPagoCompleto()">Sí, Pagar Completo</button>
            </div>
        </div>
    </div>

    <!-- Modal Nuevo Pedido -->
    <div id="modalPedido" class="modal">
        <div class="modal-content">
            <h2 style="display:flex; justify-content:space-between; align-items:center;">
                Nuevo Registro de Pedido
                <span onclick="document.getElementById('modalPedido').style.display='none'"
                    style="cursor:pointer; font-size:1.5rem;">&times;</span>
            </h2>
            <form id="formRecepcion" onsubmit="crearPedido(event)">
                <input type="hidden" id="csrf_token" value="<?= $csrfToken?>">
                <input type="hidden" id="estado_pago" value="no_pago">
                <input type="hidden" id="prioridad" value="normal">
                <input type="hidden" id="total_pago" value="0">
                <input type="hidden" id="abono_pago" value="0">

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label><i style="font-style:normal;">👤</i> Nombre Completo del Cliente</label>
                            <input type="text" id="cliente" class="form-control" required
                                placeholder="Ingrese el nombre completo">
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label><i style="font-style:normal;">✉️</i> Correo Electrónico</label>
                            <input type="email" id="email" class="form-control" placeholder="ejemplo@correo.com">
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label><i style="font-style:normal;">📞</i> Móvil / Teléfono</label>
                            <input type="text" id="telefono" class="form-control" placeholder="Número de contacto">
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label><i style="font-style:normal;">📅</i> Fecha de Entrega (opcional)</label>
                            <input type="date" id="fecha_entrega" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label><i style="font-style:normal;">📝</i> Notas del Pedido</label>
                    <textarea id="descripcion" class="form-control" rows="3"
                        placeholder="Describa los detalles del pedido..."></textarea>
                </div>

                <div class="form-group">
                    <label><i style="font-style:normal;">💵</i> Estado de Pago</label>
                    <div class="selection-grid" id="gridPagos">
                        <div class="selection-card sc-pago-completo" onclick="selectPago('pago_completo', this)">
                            <div class="icon-circle">✓</div>
                            <div class="card-title">Pago Completo</div>
                        </div>
                        <div class="selection-card sc-abono" onclick="selectPago('abono', this)">
                            <div class="icon-circle">$</div>
                            <div class="card-title">Abono</div>
                        </div>
                        <div class="selection-card sc-no-pago active" onclick="selectPago('no_pago', this)">
                            <div class="icon-circle">✕</div>
                            <div class="card-title">No Pago</div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label><i style="font-style:normal;">🕒</i> Prioridad del Pedido</label>
                    <div class="selection-grid" id="gridPrioridad">
                        <div class="selection-card sc-prioridad" onclick="selectPrioridad('prioridad', this)">
                            <div class="icon-circle">⚠️</div>
                            <div class="card-title">Urgente</div>
                            <div class="card-subtitle">(1 día)</div>
                        </div>
                        <div class="selection-card sc-normal active" onclick="selectPrioridad('normal', this)">
                            <div class="icon-circle">🕒</div>
                            <div class="card-title">Normal</div>
                            <div class="card-subtitle">(2 días)</div>
                        </div>
                        <div class="selection-card sc-largo" onclick="selectPrioridad('largo', this)">
                            <div class="icon-circle">📅</div>
                            <div class="card-title">Largo</div>
                            <div class="card-subtitle">(3 días o más)</div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label><i style="font-style:normal;">📎</i> Archivos Adjuntos</label>
                    <div class="file-upload-box" id="dropZone" onclick="document.getElementById('fileInput').click()">
                        <input type="file" id="fileInput" name="archivos[]" multiple style="display:none;"
                            onchange="handleFiles(this.files)">
                        <i style="font-style:normal;">☁️</i>
                        <p>Arrastra archivos aquí o haz clic para seleccionar</p>
                    </div>
                    <div id="fileList" style="margin-top: 10px; display: flex; flex-direction: column; gap: 5px;"></div>
                </div>

                <?php if ($smsCrearEnabled): ?>
                <div class="sms-check">
                    <input type="checkbox" id="send_sms" <?= $smsCrearCheckedDefault ? 'checked' : ''?>>
                    <label for="send_sms">Enviar notificación via SMS de la Guia al cliente</label>
                </div>
                <?php
else: ?>
                <input type="hidden" id="send_sms" value="0">
                <?php
endif; ?>

                <!-- WhatsApp por plantilla -->
                <?php if ($waActivo && $waCrearEnabled): ?>
                <div class="wa-section">
                    <div class="wa-check">
                        <input type="checkbox" id="send_whatsapp" <?= $waCrearCheckedDefault ? 'checked' : ''?>>
                        <label for="send_whatsapp">
                            Enviar SMS vía WhatsApp de la guía del pedido
                            <span class="wa-badge">&#128153; WhatsApp</span>
                        </label>
                    </div>
                    <p style="font-size:.78rem;color:#555c6a;margin-top:4px;margin-left:30px;">
                        Se enviará un mensaje de WhatsApp usando la plantilla configurada con el link de seguimiento del
                        pedido.
                    </p>
                    <?php if (!$waCredsOk): ?>
                    <p style="font-size:.78rem;color:#d97706;margin-top:6px;margin-left:30px;font-weight:600;">
                        &#9888;&#65039; Faltan configuraciones (Sender ID o Template ID). Complétalas en Configuración.
                    </p>
                    <?php
    endif; ?>
                </div>
                <?php
else: ?>
                <p style="font-size:.8rem;color:#94a3b8;margin-top:4px;margin-bottom:20px;">
                    &#128337; Envío WhatsApp deshabilitado desde Configuración Avanzada.
                </p>
                <?php
endif; ?>

                <div style="display: flex; gap: 15px; margin-top: 20px;">
                    <button type="button" class="btn"
                        style="background:#f1f5f9; color:#475569; border:1px solid #e2e8f0; flex:1; justify-content:center;"
                        onclick="document.getElementById('modalPedido').style.display='none'">Cerrar</button>
                    <button type="submit" class="btn btn-primary"
                        style="flex:2; justify-content:center; font-size:1rem;">
                        CREAR PEDIDO AHORA
                    </button>
                </div>

                <!-- Overlay de Pago (Submodal) -->
                <div class="submodal-overlay" id="paymentOverlay">
                    <div class="submodal-box">
                        <h3 style="margin-bottom:15px; color:#1e293b;" id="paymentTitle">Detalles del Pago</h3>
                        <div class="form-group">
                            <label>Valor Total del Pedido ($)</label>
                            <input type="number" id="inputTotal" class="form-control" placeholder="Ej. 150000">
                        </div>
                        <div class="form-group" id="grpAbono" style="display:none;">
                            <label>Valor Abonado ($)</label>
                            <input type="number" id="inputAbono" class="form-control" placeholder="Ej. 50000">
                        </div>
                        <div class="form-group" id="grpMetodoPago" style="display:none;">
                            <label>💳 Método de Pago</label>
                            <select id="inputMetodoPago" class="form-control">
                                <option value="efectivo">Efectivo 💵</option>
                                <option value="transferencia">Transferencia 🏦</option>
                            </select>
                        </div>
                        <div style="display:flex; gap:10px; margin-top:20px;">
                            <button type="button" class="btn" style="background:#e2e8f0; color:#475569; flex:1;"
                                onclick="closePaymentOverlay(false)">Cancelar</button>
                            <button type="button" class="btn btn-primary" style="flex:1;"
                                onclick="
                                    var v = parseFloat(document.getElementById('inputTotal').value) || 0;
                                    if (v <= 0) {
                                        document.getElementById('inputTotal').style.borderColor='#f43f5e';
                                        document.getElementById('inputTotal').focus();
                                        return;
                                    }
                                    document.getElementById('inputTotal').style.borderColor='';
                                    closePaymentOverlay(true);
                                ">Confirmar</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Sweet Alert Mimic -->
    <div class="sweet-alert" id="successAlert">
        <div class="sa-icon">✓</div>
        <h2 style="border:none; margin-bottom:10px;">¡Excelente!</h2>
        <p style="color:#64748b; font-size:1.1rem; margin-bottom:20px;">Pedido creado exitosamente.</p>
        <button class="btn btn-primary" style="margin:0 auto; width:120px; justify-content:center;"
            onclick="window.location.reload()">OK</button>
    </div>

    <!-- Modal Detalles del Pedido -->
    <div id="modalDetalles" class="modal">
        <div class="modal-content" style="width:500px;">
            <h2 id="detallesTitle" style="border:none; margin-bottom:5px;">Detalles del Pedido</h2>
            <p id="detallesID" style="color:#64748b; font-size:0.9rem; margin-bottom:20px;">#PED-0000</p>

            <div id="detallesBody"
                style="background:#f8fafc; padding:20px; border-radius:12px; border:1px solid #e2e8f0; margin-bottom:20px;">
                <!-- Se llena vía JS -->
            </div>

            <div id="detallesArchivos"
                style="margin-bottom: 20px; padding: 10px; background: #eff6ff; border-radius: 8px; border: 1px solid #bfdbfe; font-size: 0.9rem;">
                <!-- JS inserta archivos -->
            </div>
            
            <div style="display:flex; gap:10px; flex-wrap:wrap; margin-bottom:15px;">
                <button class="btn" style="background:linear-gradient(45deg,#8b5cf6,#6d28d9); flex:1; justify-content:center; min-width:120px; color:white;" onclick="abrirModalPagos(_currentPedido.id);">💰 Ver / Abonar Pagos</button>
            </div>

            <div style="display:flex; gap:10px; flex-wrap:wrap; margin-bottom:15px;" id="detallesBotonesAdmin">
                <?php if (in_array($role, ['Admin', 'SuperAdmin'])): ?>
                <button class="btn" id="btnEditar"
                    style="background:linear-gradient(45deg,#0ea5e9,#0284c7); flex:1; justify-content:center; min-width:120px;"
                    onclick="abrirEditar()">✏️ Editar</button>
                <button class="btn" id="btnEnviarArea"
                    style="background:linear-gradient(45deg,#10b981,#059669); flex:1; justify-content:center; min-width:120px;"
                    onclick="abrirEnviarArea()">📤 Enviar a Área</button>
                <button class="btn" id="btnEliminar"
                    style="background:linear-gradient(45deg,#ef4444,#dc2626); flex:1; justify-content:center; min-width:120px;"
                    onclick="abrirModalEliminar()">🗑️ Eliminar</button>
                <?php
else: ?>
                <p style="font-size:0.8rem; color:#94a3b8; font-style:italic; margin:0;">
                    🔒 Solo los administradores pueden editar pedidos.
                </p>
                <?php
endif; ?>
            </div>
            <div style="text-align:right;">
                <button class="btn" style="background:#f1f5f9; color:#475569; border:1px solid #e2e8f0;"
                    onclick="document.getElementById('modalDetalles').style.display='none'">Cerrar</button>
            </div>
        </div>
    </div>

    <!-- Modal Editar Pedido -->
    <div id="modalEditar" class="modal" onclick="if(event.target===this) this.style.display='none'">
        <div class="modal-content" style="width:650px;">
            <h2 style="display:flex; justify-content:space-between; align-items:center;">
                ✏️ Editar Pedido <span id="editarPedidoID" style="color:#6366f1; font-size:1rem;"></span>
                <span onclick="this.closest('.modal').style.display='none'"
                    style="cursor:pointer; font-size:1.5rem; font-weight:400;">&times;</span>
            </h2>
            <form id="formEditar" onsubmit="guardarEdicion(event)">
                <input type="hidden" id="editId">
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label>👤 Nombre del Cliente</label>
                            <input type="text" id="editCliente" class="form-control" required
                                placeholder="Nombre completo">
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label>✉️ Correo Electrónico</label>
                            <input type="email" id="editEmail" class="form-control" placeholder="ejemplo@correo.com">
                        </div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label>📞 Teléfono</label>
                            <input type="text" id="editTelefono" class="form-control" placeholder="Número de contacto">
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label>💵 Estado de Pago</label>
                            <select id="editEstadoPago" class="form-control">
                                <option value="no_pago">No Pago</option>
                                <option value="abono">Abono</option>
                                <option value="pago_completo">Pago Completo</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label>💰 Total ($)</label>
                            <input type="number" id="editTotal" class="form-control" placeholder="0">
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group" style="padding-top: 28px;">
                            <button type="button" class="btn btn-secondary" style="width:100%; justify-content:center;" onclick="abrirModalPagos(document.getElementById('editId').value)">💰 Ver / Abonar Pagos</button>
                            <input type="hidden" id="editAbonado" value="0">
                        </div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label>🕒 Prioridad</label>
                            <select id="editPrioridad" class="form-control">
                                <option value="normal">Normal (3 días)</option>
                                <option value="prioridad">⚠️ Prioridad (1 día)</option>
                                <option value="largo">📅 Largo (1 semana+)</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label>📅 Fecha de Entrega</label>
                            <input type="date" id="editFechaEntrega" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>📝 Notas del Pedido</label>
                    <textarea id="editDescripcion" class="form-control" rows="3"
                        placeholder="Detalles del pedido..."></textarea>
                </div>

                <!-- Gestión de adjuntos en Editar -->
                <div class="form-group">
                    <label>📎 Archivos Adjuntos Actuales</label>
                    <div id="editArchivosActuales"
                        style="margin-bottom:10px; font-size:0.85rem; display:flex; flex-direction:column; gap:5px;">
                    </div>

                    <label style="margin-top:10px;">Subir nuevos archivos</label>
                    <input type="file" id="editFileInput" class="form-control" multiple>
                </div>
                <div style="display:flex; gap:10px; margin-top:20px;">
                    <button type="button" class="btn"
                        style="background:#f1f5f9; color:#475569; border:1px solid #e2e8f0; flex:1;"
                        onclick="this.closest('.modal').style.display='none'">Cancelar</button>
                    <button type="submit" class="btn btn-primary" style="flex:2; justify-content:center;">💾 Guardar
                        Cambios</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Enviar a Área -->
    <div id="modalEnviarArea" class="modal" onclick="if(event.target===this) this.style.display='none'">
        <div class="modal-content" style="width:480px;">
            <h2 style="display:flex; justify-content:space-between; align-items:center;">
                📤 Enviar a Área
                <span onclick="this.closest('.modal').style.display='none'"
                    style="cursor:pointer; font-size:1.5rem; font-weight:400;">&times;</span>
            </h2>
            <p style="color:#64748b; margin-bottom:20px;">Selecciona el área a la que deseas enviar el pedido <strong
                    id="enviarPedidoLabel"></strong>:</p>
            <div id="areaButtonsGrid"
                style="display:grid; grid-template-columns:repeat(2,1fr); gap:12px; margin-bottom:24px;">
                <?php foreach ($areas as $area): ?>
                <button type="button" class="area-select-btn" data-area-id="<?= $area['id']?>"
                    onclick="seleccionarAreaDestino(this)"
                    style="padding:14px 10px; border:2px solid #e2e8f0; border-radius:10px; background:#f8fafc; color:#1e293b; font-weight:600; font-size:0.9rem; cursor:pointer; transition:all 0.2s; text-align:center;">
                    <?= htmlspecialchars($area['nombre'])?>
                </button>
                <?php
endforeach; ?>
            </div>
            <div style="display:flex; gap:10px;">
                <button type="button" class="btn"
                    style="background:#f1f5f9; color:#475569; border:1px solid #e2e8f0; flex:1;"
                    onclick="document.getElementById('modalEnviarArea').style.display='none'">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnConfirmarEnvio"
                    style="flex:2; justify-content:center;" disabled onclick="ejecutarEnvioArea()">📤 Confirmar
                    Envío</button>
            </div>
        </div>
    </div>

    <!-- Modal Historial de Pagos -->
    <div id="modalPagos" class="modal" onclick="if(event.target===this) this.style.display='none'">
        <div class="modal-content" style="width:600px;">
            <h2 style="display:flex; justify-content:space-between; align-items:center;">
                💰 Pagos del Pedido <span id="pagosPedidoID" style="color:#10b981; font-size:1rem;"></span>
                <span onclick="this.closest('.modal').style.display='none'"
                    style="cursor:pointer; font-size:1.5rem; font-weight:400;">&times;</span>
            </h2>
            <div id="pagosResumen" style="display:flex; gap:15px; margin-bottom:20px; background:#f8fafc; padding:15px; border-radius:10px; border:1px solid #e2e8f0;">
                <div style="flex:1;"><strong>Total:</strong> $<span id="pagosTotalLabel">0</span></div>
                <div style="flex:1; color:#10b981;"><strong>Abonado:</strong> $<span id="pagosAbonadoLabel">0</span></div>
                <div style="flex:1; color:#ef4444;"><strong>Saldo:</strong> $<span id="pagosSaldoLabel">0</span></div>
            </div>
            
            <h3 style="font-size:1.1rem; color:#1e293b; margin-bottom:10px; border-bottom:1px solid #e2e8f0; padding-bottom:5px;">Historial de Pagos</h3>
            <div id="pagosLista" style="max-height:200px; overflow-y:auto; margin-bottom:20px;">
                <!-- JS inserta historial -->
            </div>

            <h3 style="font-size:1.1rem; color:#1e293b; margin-bottom:10px; border-bottom:1px solid #e2e8f0; padding-bottom:5px;">Nuevo Abono</h3>
            <form id="formNuevoAbono" onsubmit="guardarAbono(event);">
                <input type="hidden" id="pagosId">
                <div class="form-row">
                    <div class="form-col">
                        <label>Monto a Abonar ($)</label>
                        <input type="number" id="nuevoAbonoMonto" class="form-control" required min="1">
                    </div>
                    <div class="form-col">
                        <label>Método de Pago</label>
                        <select id="nuevoAbonoMetodo" class="form-control" required>
                            <option value="efectivo">Efectivo 💵</option>
                            <option value="transferencia">Transferencia 🏦</option>
                        </select>
                    </div>
                </div>
                <div class="form-group mt-2" style="margin-top:10px;">
                    <label>Observaciones</label>
                    <input type="text" id="nuevoAbonoObs" class="form-control" placeholder="Opcional">
                </div>
                <div style="text-align:right; margin-top:15px;">
                    <button type="submit" class="btn btn-primary">➕ Registrar Abono</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Registros de Stat Card -->
    <div id="modalStatRecords" class="modal" onclick="if(event.target===this) this.style.display='none'">
        <div class="modal-content">
            <h2 style="display:flex; justify-content:space-between; align-items:center;">
                <span id="statModalTitle">Registros</span>
                <span onclick="document.getElementById('modalStatRecords').style.display='none'"
                    style="cursor:pointer; font-size:1.5rem; font-weight:400;">&times;</span>
            </h2>
            <input type="text" class="stat-modal-search" id="statModalSearch"
                placeholder="Buscar por ID, cliente, área, fase...">
            <div style="overflow-x:auto;">
                <table class="stat-table" id="statModalTable">
                    <thead>
                        <tr>
                            <th>#Pedido</th>
                            <th>Cliente</th>
                            <th>Área</th>
                            <th>Fase</th>
                            <th>Pago</th>
                            <th>Total</th>
                            <th>Abonado</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody id="statModalBody">
                    </tbody>
                </table>
            </div>
            <p id="statModalEmpty" style="text-align:center; color:#94a3b8; padding:2rem 0; display:none;">No hay
                registros para mostrar.</p>
        </div>
    </div>

    <script>
        const basePath = window.location.pathname.replace(/\/recepcion\/?$/i, '');

        // Datos de stat-cards embebidos desde PHP
        const STAT_LISTS = <?= json_encode($statLists, JSON_UNESCAPED_UNICODE)?>;
        const canViewPrices = <?= $canViewPrices ? 'true' : 'false'?>;

        function  openStatModal(tipo, titulo) {
            var list = STAT_LISTS[tipo] || [];
            document.getElementById('statModalTitle').textContent = titulo + ' (' + list.length + ' registros)';
            document.getElementById('statModalSearch').value = '';
            renderStatTable(list);
            document.getElementById('modalStatRecords').style.display = 'flex';
        }

        function renderStatTable(list) {
            var tbody = document.getElementById('statModalBody');
            var empty = document.getElementById('statModalEmpty');
            tbody.innerHTML = '';
            if (list.length === 0) {
                empty.style.display = 'block';
                document.getElementById('statModalTable').style.display = 'none';
                return;
            }
            empty.style.display = 'none';
            document.getElementById('statModalTable').style.display = 'table';
            list.forEach(function (p) {
                var padId = '#PED-' + String(p.id).padStart(4, '0');
                var pagoClass = 'no-pago', pagoTxt = 'No Pago';
                if (p.estado_pago === 'pago_completo') { pagoClass = 'pago-completo'; pagoTxt = 'Pago Completo'; }
                else if (p.estado_pago === 'abono') { pagoClass = 'abono'; pagoTxt = 'Abono'; }
                var fecha = (p.created_at || '').substring(0, 16);
                var total = p.total > 0 ? '$' + Number(p.total).toLocaleString('es-CO') : '—';
                var abonado = (p.abonado > 0) ? '$' + Number(p.abonado).toLocaleString('es-CO') : '$0';
                var tr = document.createElement('tr');
                tr.title = 'Ver detalles';
                tr.style.cursor = 'pointer';
                tr.onclick = (function (pedido) { return function () { document.getElementById('modalStatRecords').style.display = 'none'; verDetalles(pedido); }; })(p);
                tr.innerHTML = '<td style="font-weight:600;color:#4f46e5;">' + padId + '</td>'
                    + '<td>' + (p.cliente_nombre || '') + '</td>'
                    + '<td>' + (p.area_nombre || '') + '</td>'
                    + '<td>' + (p.fase_actual || '') + '</td>'
                    + '<td><span class="badge ' + pagoClass + '">' + pagoTxt + '</span></td>'
                    + '<td style="font-weight:600;color:#059669;">' + total + '</td>'
                    + '<td style="color:#7c3aed;">' + abonado + '</td>'
                    + '<td style="white-space:nowrap;color:#94a3b8;">' + fecha + '</td>';
                tbody.appendChild(tr);
            });
        }

        document.getElementById('statModalSearch').addEventListener('keyup', function () {
            var term = this.value.toLowerCase();
            document.querySelectorAll('#statModalBody tr').forEach(function (tr) {
                tr.style.display = tr.textContent.toLowerCase().includes(term) ? '' : 'none';
            });
        });

        /* ===== FORMULARIO CREAR PEDIDO ===== */
        function selectPago(tipo, element) {
            document.getElementById('estado_pago').value = tipo;
            document.querySelectorAll('#gridPagos .selection-card').forEach(function (c) { c.classList.remove('active'); });
            element.classList.add('active');

            // Todos los tipos abren el overlay para ingresar el valor total
            document.getElementById('paymentOverlay').style.display = 'flex';
            document.getElementById('grpAbono').style.display = 'none';

            if (tipo === 'abono') {
                document.getElementById('paymentTitle').innerText = 'Configurar Abono';
                document.getElementById('grpAbono').style.display = 'block';
            } else if (tipo === 'pago_completo') {
                document.getElementById('paymentTitle').innerText = 'Confirmar Pago Completo';
            } else {
                document.getElementById('paymentTitle').innerText = 'Valor Total del Pedido (Deuda)';
            }
            
            // Mostrar u ocultar metodo_pago según tipo
            if (tipo === 'abono' || tipo === 'pago_completo') {
                document.getElementById('grpMetodoPago').style.display = 'block';
            } else {
                document.getElementById('grpMetodoPago').style.display = 'none';
            }
        }

        function closePaymentOverlay(saveValues) {
            if (saveValues) {
                var total = document.getElementById('inputTotal').value || 0;
                var abono = document.getElementById('inputAbono').value || 0;
                var tipo = document.getElementById('estado_pago').value;
                
                // Si es pago completo, el abono es igual al total
                if (tipo === 'pago_completo') abono = total;
                
                document.getElementById('total_pago').value = total;
                document.getElementById('abono_pago').value = abono;
                var met = document.getElementById('inputMetodoPago').value;
                if (!document.getElementById('metodo_pago')) {
                    var mInput = document.createElement('input');
                    mInput.type = 'hidden'; mInput.id = 'metodo_pago'; mInput.name = 'metodo_pago';
                    document.getElementById('formRecepcion').appendChild(mInput);
                }
                document.getElementById('metodo_pago').value = met;
            } else {
                document.getElementById('estado_pago').value = 'no_pago';
                document.querySelectorAll('#gridPagos .selection-card').forEach(function (c) {
                    c.classList.remove('active');
                    if (c.classList.contains('sc-no-pago')) c.classList.add('active');
                });
                document.getElementById('inputTotal').value = '';
                document.getElementById('inputAbono').value = '';
                document.getElementById('total_pago').value = 0;
                document.getElementById('abono_pago').value = 0;
            }
            document.getElementById('paymentOverlay').style.display = 'none';
        }

        function selectPrioridad(tipo, element) {
            document.getElementById('prioridad').value = tipo;
            document.querySelectorAll('#gridPrioridad .selection-card').forEach(function (c) { c.classList.remove('active'); });
            element.classList.add('active');
            // Tambien actualiza la fecha de entrega
            var hoy = new Date();
            hoy.setHours(0, 0, 0, 0);
            var dias = (tipo === 'prioridad') ? 1 : (tipo === 'normal') ? 2 : 3;
            var destino = new Date(hoy.getTime() + dias * 86400000);
            var yyyy = destino.getFullYear();
            var mm = String(destino.getMonth() + 1).padStart(2, '0');
            var dd = String(destino.getDate()).padStart(2, '0');
            var campo = document.getElementById('fecha_entrega');
            if (campo && !campo._changedByUser) campo.value = yyyy + '-' + mm + '-' + dd;
        }

        // Sincronizar fecha_entrega con prioridad (bidireccional)
        var fechaInput = document.getElementById('fecha_entrega');
        fechaInput.addEventListener('change', function () {
            this._changedByUser = true;
            var val = this.value;
            if (!val) return;
            var hoy = new Date(); hoy.setHours(0, 0, 0, 0);
            var entrega = new Date(val + 'T00:00:00');
            var diffDias = Math.round((entrega - hoy) / 86400000);
            var tipo;
            if (diffDias <= 1) tipo = 'prioridad';
            else if (diffDias === 2) tipo = 'normal';
            else tipo = 'largo';
            var card = document.querySelector('#gridPrioridad .sc-' + tipo);
            if (card) { document.getElementById('prioridad').value = tipo; document.querySelectorAll('#gridPrioridad .selection-card').forEach(function (c) { c.classList.remove('active'); }); card.classList.add('active'); }
            setTimeout(function () { fechaInput._changedByUser = false; }, 100);
        });

        function showSuccessAlert() {
            document.getElementById('modalPedido').style.display = 'none';
            var al = document.getElementById('successAlert');
            al.style.display = 'block';
            setTimeout(function () { al.classList.add('show'); }, 10);
        }

        let globalArchivos = [];

        // --- Drag & Drop Handlers ---
        const dropZone = document.getElementById('dropZone');

        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.style.borderColor = '#6366f1';
            dropZone.style.background = '#eff6ff';
        });

        dropZone.addEventListener('dragleave', (e) => {
            e.preventDefault();
            dropZone.style.borderColor = '#cbd5e1';
            dropZone.style.background = '#f8fafc';
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.style.borderColor = '#cbd5e1';
            dropZone.style.background = '#f8fafc';
            handleFiles(e.dataTransfer.files);
        });

        function handleFiles(files) {
            for (let i = 0; i < files.length; i++) {
                globalArchivos.push(files[i]);
            }
            renderFileList();
        }

        function renderFileList() {
            const list = document.getElementById('fileList');
            list.innerHTML = '';
            globalArchivos.forEach((file, index) => {
                const item = document.createElement('div');
                item.style.cssText = 'display:flex; justify-content:space-between; align-items:center; background:#f1f5f9; padding:8px 12px; border-radius:8px; font-size:0.85rem; border:1px solid #e2e8f0;';
                item.innerHTML = `
                    <span style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:85%; color:#334155;">📎 ${file.name}</span>
                    <button type="button" onclick="removeFile(${index})" style="background:none; border:none; color:#ef4444; cursor:pointer; font-weight:bold; font-size:1.1rem; line-height:1;">&times;</button>
                `;
                list.appendChild(item);
            });
        }

        function removeFile(index) {
            globalArchivos.splice(index, 1);
            renderFileList();
            // Reset input file value to allow re-selection of same file
            document.getElementById('fileInput').value = '';
        }

        async function crearPedido(e) {
            e.preventDefault();

            var total = parseFloat(document.getElementById('total_pago').value) || 0;
            var estadoPago = document.getElementById('estado_pago').value;

            // Se requiere monto total para todos los estados de pago
            if (total <= 0) {
                alert('Debe ingresar el valor total del pedido antes de continuar. Haga clic en la opción de pago seleccionada para ingresar el monto.');
                return;
            }

            if (estadoPago === 'abono') {
                var abono = parseFloat(document.getElementById('abono_pago').value) || 0;
                if (abono <= 0) {
                    alert('Debe ingresar el monto a abonar mayor a 0. Haga clic en la opción de pago Abono para ingresar el monto.');
                    return;
                }
            }

            // Prevenir doble submit
            var btnSubmit = document.querySelector('#formRecepcion button[type="submit"]');
            var originalText = btnSubmit ? btnSubmit.innerHTML : 'Guardar Pedido';
            if (btnSubmit) {
                btnSubmit.disabled = true;
                btnSubmit.innerHTML = '⏱ Guardando...';
            }

            var csrf_token = document.getElementById('csrf_token').value;

            var formData = new FormData();
            formData.append('cliente_nombre', document.getElementById('cliente').value);
            formData.append('cliente_email', document.getElementById('email').value);
            formData.append('cliente_telefono', document.getElementById('telefono').value);
            formData.append('descripcion', document.getElementById('descripcion').value);
            formData.append('estado_pago', document.getElementById('estado_pago').value);
            formData.append('prioridad', document.getElementById('prioridad').value);
            formData.append('total', document.getElementById('total_pago').value);
            formData.append('abonado', document.getElementById('abono_pago').value);
            formData.append('metodo_pago', document.getElementById('metodo_pago') ? document.getElementById('metodo_pago').value : 'efectivo');
            formData.append('send_sms', document.getElementById('send_sms').checked ? '1' : '0');
            var waChk = document.getElementById('send_whatsapp');
            formData.append('send_whatsapp', (waChk && waChk.checked) ? '1' : '0');
            formData.append('fecha_entrega_esperada', document.getElementById('fecha_entrega').value);
            formData.append('csrf_token', csrf_token);


            globalArchivos.forEach((file, index) => {
                formData.append('archivos[]', file);
            });

            try {
                // Not sending Content-Type string so fetch sets multipart form boundary
                var r = await fetch(basePath + '/api/pedidos/crear', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf_token },
                    body: formData
                });
                var res = await r.json();
                if (res.status === 'success') { if (window.BannerSounds) BannerSounds.crear(); showSuccessAlert(); }
                else {
                    alert('Error: ' + res.message);
                    if (btnSubmit) {
                        btnSubmit.disabled = false;
                        btnSubmit.innerHTML = originalText;
                    }
                }
            } catch (err) {
                console.error(err);
                alert('Fallo interno de servidor');
                if (btnSubmit) {
                    btnSubmit.disabled = false;
                    btnSubmit.innerHTML = originalText;
                }
            }
        }


        /* ===== MODAL DETALLES ===== */
        var _currentPedido = null;

        function verDetalles(p) {
            _currentPedido = p;
            var role = '<?= $role?>';
            document.getElementById('detallesID').innerText = '#PED-' + String(p.id).padStart(4, '0');
            document.getElementById('detallesTitle').innerText = p.cliente_nombre;

            var statusPagoTxt = '';
            if (p.estado_pago === 'pago_completo') {
                statusPagoTxt = '<span class="badge pago-completo">Pago Completo</span>';
            } else if (p.estado_pago === 'abono') {
                statusPagoTxt = '<span class="badge abono">Abono</span>';
            } else {
                if (canViewPrices && parseFloat(p.total) > 0) {
                    statusPagoTxt = '<span class="badge no-pago">No Pago ($' + parseFloat(p.total).toLocaleString() + ')</span>';
                } else {
                    statusPagoTxt = '<span class="badge no-pago">No Pago</span>';
                }
            }

            var priorityTxt = '';
            if (p.prioridad === 'prioridad') priorityTxt = '<span class="badge prioridad">PRIORIDAD</span>';
            else if (p.prioridad === 'largo') priorityTxt = '<span class="badge largo">LARGO</span>';
            else priorityTxt = '<span class="badge" style="background:#10b981;">NORMAL</span>';

            var body = '<div style="margin-bottom:15px;display:flex;gap:10px;flex-wrap:wrap;">' + statusPagoTxt + ' ' + priorityTxt + '</div>'
                + '<p><strong>Area Actual:</strong> ' + (p.area_nombre || 'N/A') + '</p>'
                + '<p style="margin-top:6px;"><strong>Fase:</strong> ' + p.fase_actual + '</p>'
                + '<p style="margin-top:6px;"><strong>Telefono:</strong> ' + (p.cliente_telefono || 'No registrado') + '</p>'
                + '<p style="margin-top:6px;"><strong>Email:</strong> ' + (p.cliente_email || 'No registrado') + '</p>'
                + '<hr style="margin:10px 0; border:0; border-top:1px solid #e2e8f0;">'
                + '<p style="color:#64748b; font-size:0.9rem;"><strong>📅 Ingresado:</strong> ' + (p.created_at || 'N/A') + '</p>'
                + '<p style="color:#64748b; font-size:0.9rem; margin-top:4px;"><strong>⏳ Entrega Esperada:</strong> ' + (p.fecha_entrega_esperada || '<span style="font-style:italic;">Sin fecha límite</span>') + '</p>'
                + '<p style="margin-top:10px;"><strong>Notas:</strong><br>' + (p.descripcion || 'Sin notas') + '</p>';

            if (canViewPrices) {
                body += '<div style="margin-top:16px;padding-top:14px;border-top:1px solid #e2e8f0;">'
                    + '<p><strong>Total:</strong> $' + Number(p.total || 0).toLocaleString() + '</p>'
                    + '<p><strong>Abonado:</strong> $' + Number(p.abonado || 0).toLocaleString() + '</p>'
                    + '<p style="color:#ef4444;font-weight:700;"><strong>Saldo:</strong> $' + (Number(p.total || 0) - Number(p.abonado || 0)).toLocaleString() + '</p>'
                    + '</div>';
            }
            document.getElementById('detallesBody').innerHTML = body;

            // Cargar archivos
            document.getElementById('detallesArchivos').innerHTML = '<span style="color:#64748b;">Cargando archivos...</span>';
            cargarArchivos(p.id, 'detallesArchivos', false);

            document.getElementById('modalDetalles').style.display = 'flex';
        }

        async function cargarArchivos(id, containerId, forEdit = false) {
            try {
                var r = await fetch(basePath + '/api/kanban/archivos/' + id);
                var res = await r.json();
                var box = document.getElementById(containerId);
                if (!box) return;

                if (forEdit) {
                    window._archivos_eliminados = [];
                }

                if (res.status === 'success' && res.data && res.data.length > 0) {
                    var html = '';
                    res.data.forEach(function (f) {
                        var ext = (f.nombre_archivo || '').split('.').pop().toLowerCase();
                        var ico = ['jpg', 'jpeg', 'png', 'gif'].indexOf(ext) >= 0 ? '🖼️' : (ext === 'pdf' ? '📕' : '📄');
                        html += '<div id="file_row_' + f.id + '" style="display:flex;align-items:center;padding:6px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;margin-bottom:4px; justify-content:space-between;">';
                        html += '<a href="' + basePath + '/storage/uploads/' + f.ruta_almacenamiento + '" target="_blank" style="display:flex;align-items:center;gap:6px;color:#1e293b;text-decoration:none;font-size:0.85rem;"><span style="font-size:1.1rem;">' + ico + '</span> ' + f.nombre_archivo + '</a>';
                        if (forEdit) {
                            html += '<button type="button" onclick="eliminarArchivoEdit(' + f.id + ')" style="background:none; border:none; color:#ef4444; font-weight:bold; cursor:pointer; font-size:1.1rem; line-height:1;">&times;</button>';
                        }
                        html += '</div>';
                    });
                    box.innerHTML = html;
                } else {
                    box.innerHTML = '<span style="color:#64748b;font-style:italic;">No hay archivos adjuntos.</span>';
                }
            } catch (e) {
                var b = document.getElementById(containerId);
                if (b) b.innerHTML = '<span style="color:#ef4444;">Error al cargar archivos.</span>';
            }
        }

        function eliminarArchivoEdit(fileId) {
            if (confirm('¿Eliminar este archivo?')) {
                window._archivos_eliminados.push(fileId);
                var row = document.getElementById('file_row_' + fileId);
                if (row) row.style.display = 'none';
            }
        }

        /* ===== EDITAR ===== */
        function abrirEditar() {
            if (!_currentPedido) return;
            var p = _currentPedido;
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
            document.getElementById('editFechaEntrega').value = p.fecha_entrega_esperada || '';
            document.getElementById('editFileInput').value = '';

            document.getElementById('editArchivosActuales').innerHTML = '<span style="color:#64748b;">Cargando...</span>';
            cargarArchivos(p.id, 'editArchivosActuales', true);

            document.getElementById('modalDetalles').style.display = 'none';
            document.getElementById('modalEditar').style.display = 'flex';
        }

        async function guardarEdicion(e) {
            e.preventDefault();

            var editTotal = parseFloat(document.getElementById('editTotal').value) || 0;
            var editEstadoPago = document.getElementById('editEstadoPago').value;

            if (editTotal <= 0) {
                alert('Debe ingresar un monto total mayor a 0 para el pedido.');
                return;
            }

            if (editEstadoPago === 'abono') {
                // Remove required field validator for abonado here since it's managed in modal Pagos now
                // var editAbonado = parseFloat(document.getElementById('editAbonado').value) || 0;
            }

            var csrfToken = document.getElementById('csrf_token').value;

            var formData = new FormData();
            formData.append('pedido_id', parseInt(document.getElementById('editId').value));
            formData.append('cliente_nombre', document.getElementById('editCliente').value);
            formData.append('cliente_email', document.getElementById('editEmail').value);
            formData.append('cliente_telefono', document.getElementById('editTelefono').value);
            formData.append('descripcion', document.getElementById('editDescripcion').value);
            formData.append('estado_pago', document.getElementById('editEstadoPago').value);
            formData.append('prioridad', document.getElementById('editPrioridad').value);
            formData.append('fecha_entrega_esperada', document.getElementById('editFechaEntrega').value);
            formData.append('total', parseFloat(document.getElementById('editTotal').value) || 0);
            formData.append('abonado', parseFloat(document.getElementById('editAbonado').value) || 0);
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
                    document.getElementById('modalEditar').style.display = 'none';
                    showToastRecep('Pedido actualizado correctamente.', 'success');
                    setTimeout(function () { window.location.reload(); }, 1200);
                } else { alert('Error: ' + res.message); }
            } catch (err) { alert('Error de red.'); }
        }

        /* ===== ELIMINAR ===== */
        function confirmarEliminar() {
            if (!_currentPedido) return;
            if (!confirm('Eliminar pedido #PED-' + String(_currentPedido.id).padStart(4, '0') + '? Se marcara como cancelado.')) return;
            eliminarPedido(_currentPedido.id);
        }

        async function eliminarPedido(id) {
            var csrfToken = document.getElementById('csrf_token').value;
            try {
                var r = await fetch(basePath + '/api/pedidos/eliminar', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken }, body: JSON.stringify({ pedido_id: id, csrf_token: csrfToken }) });
                var res = await r.json();
                if (res.status === 'success') {
                    document.getElementById('modalDetalles').style.display = 'none';
                    if (window.BannerSounds) BannerSounds.eliminar();
                    showToastRecep('Pedido eliminado.', 'error');
                    setTimeout(function () { window.location.reload(); }, 1200);
                } else { alert('Error: ' + res.message); }
            } catch (err) { alert('Error de red.'); }
        }
        /* ===== ENTREGAR AL CLIENTE ===== */
        async function confirmarEntregar(id) {
            if (!confirm('¿Marcar el pedido #PED-' + String(id).padStart(4, '0') + ' como Entregado al Cliente?')) return;
            var csrfToken = document.getElementById('csrf_token').value;
            try {
                var r = await fetch(basePath + '/api/pedidos/entregar', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ pedido_id: id, csrf_token: csrfToken })
                });
                var res = await r.json();
                if (res.status === 'success') {
                    showToastRecep('Pedido entregado con éxito.', 'success');
                    setTimeout(function () { window.location.reload(); }, 1200);
                } else { alert('Error: ' + res.message); }
            } catch (err) { alert('Error de red.'); }
        }

        async function confirmarRevertirEntrega(id) {
            if (!confirm('¿Revertir la entrega del pedido #PED-' + String(id).padStart(4, '0') + '? Volverá a estar "No Entregado".')) return;
            var csrfToken = document.getElementById('csrf_token').value;
            try {
                var r = await fetch(basePath + '/api/pedidos/revertir-entrega', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ pedido_id: id, csrf_token: csrfToken })
                });
                var res = await r.json();
                if (res.status === 'success') {
                    showToastRecep('Entrega revertida correctamente.', 'success');
                    setTimeout(function () { window.location.reload(); }, 1200);
                } else { alert('Error: ' + res.message); }
            } catch (err) { alert('Error de red.'); }
        }

        /* ===== PAGAR COMPLETO ===== */
        function abrirModalPagoCompleto(id) {
            document.getElementById('pagoCompletoPedidoId').value = id;
            var mod = document.getElementById('modalPagoCompleto');
            mod.style.display = 'flex';
        }

        async function ejecutarPagoCompleto() {
            var id = document.getElementById('pagoCompletoPedidoId').value;
            if (!id) return;
            var csrfToken = document.getElementById('csrf_token').value;
            try {
                var r = await fetch(basePath + '/api/pedidos/pagado-completo', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ pedido_id: id, csrf_token: csrfToken })
                });
                var res = await r.json();
                if (res.status === 'success') {
                    document.getElementById('modalPagoCompleto').style.display = 'none';
                    showToastRecep('El pedido ha sido marcado como pago completo.', 'success');
                    setTimeout(function () { window.location.reload(); }, 1200);
                } else { alert('Error: ' + res.message); }
            } catch (err) { alert('Error de red.'); }
        }

        /* ===== ENVIAR A AREA ===== */
        var _areaDestinoSeleccionada = null;

        function abrirEnviarArea() {
            if (!_currentPedido) return;
            _areaDestinoSeleccionada = null;
            document.getElementById('btnConfirmarEnvio').disabled = true;
            document.querySelectorAll('.area-select-btn').forEach(function (b) {
                b.style.borderColor = '#e2e8f0';
                b.style.background = '#f8fafc';
                b.style.color = '#1e293b';
            });
            document.getElementById('enviarPedidoLabel').textContent = '#PED-' + String(_currentPedido.id).padStart(4, '0');
            document.getElementById('modalDetalles').style.display = 'none';
            document.getElementById('modalEnviarArea').style.display = 'flex';
        }

        function seleccionarAreaDestino(btn) {
            document.querySelectorAll('.area-select-btn').forEach(function (b) {
                b.style.borderColor = '#e2e8f0';
                b.style.background = '#f8fafc';
                b.style.color = '#1e293b';
            });
            btn.style.borderColor = '#6366f1';
            btn.style.background = '#eef2ff';
            btn.style.color = '#4f46e5';
            _areaDestinoSeleccionada = btn.getAttribute('data-area-id');
            document.getElementById('btnConfirmarEnvio').disabled = false;
        }

        async function ejecutarEnvioArea() {
            if (!_currentPedido || !_areaDestinoSeleccionada) return;
            var csrfToken = document.getElementById('csrf_token').value;
            try {
                var r = await fetch(basePath + '/api/pedidos/enviar-area', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken }, body: JSON.stringify({ pedido_id: _currentPedido.id, area_destino_id: parseInt(_areaDestinoSeleccionada), csrf_token: csrfToken }) });
                var res = await r.json();
                if (res.status === 'success') {
                    document.getElementById('modalEnviarArea').style.display = 'none';
                    showToastRecep(res.message, 'success');
                    setTimeout(function () { window.location.reload(); }, 1400);
                } else { alert('Error: ' + res.message); }
            } catch (err) { alert('Error de red.'); }
        }

        /* ===== HISTORIAL DE PAGOS ===== */
        async function abrirModalPagos(pedidoId) {
            document.getElementById('pagosId').value = pedidoId;
            document.getElementById('pagosPedidoID').textContent = '#PED-' + String(pedidoId).padStart(4, '0');
            document.getElementById('nuevoAbonoMonto').value = '';
            document.getElementById('nuevoAbonoObs').value = '';
            
            // Fetch pagos del pedido
            try {
                var r = await fetch(basePath + '/api/pedidos/pagos/' + pedidoId);
                var res = await r.json();
                if (res.status === 'success') {
                    var data = res.data; // {historial: [...], total: 0, abonado: 0}
                    document.getElementById('pagosTotalLabel').textContent = Number(data.total).toLocaleString();
                    document.getElementById('pagosAbonadoLabel').textContent = Number(data.abonado).toLocaleString();
                    document.getElementById('pagosSaldoLabel').textContent = (Number(data.total) - Number(data.abonado)).toLocaleString();
                    
                    var lista = document.getElementById('pagosLista');
                    if (data.historial && data.historial.length > 0) {
                        var html = '<table class="stat-table" style="width:100%; text-align:left;">';
                        html += '<thead><tr><th>Fecha</th><th>Monto</th><th>Método</th><th>Obs</th></tr></thead><tbody>';
                        data.historial.forEach(function(p) {
                            html += '<tr style="border-bottom:1px solid #e2e8f0;">';
                            html += '<td style="padding:6px;">' + p.fecha_pago + '</td>';
                            html += '<td style="padding:6px; font-weight:bold;">$' + Number(p.monto).toLocaleString() + '</td>';
                            html += '<td style="padding:6px;">' + (p.metodo_pago === 'efectivo' ? '💵 Efectivo' : '🏦 Transferencia') + '</td>';
                            html += '<td style="padding:6px; color:#64748b;">' + (p.observacion || '') + '</td>';
                            html += '</tr>';
                        });
                        html += '</tbody></table>';
                        lista.innerHTML = html;
                    } else {
                        lista.innerHTML = '<p style="color:#94a3b8; font-style:italic;text-align:center;">No hay pagos registrados aún.</p>';
                    }
                    document.getElementById('modalPagos').style.display = 'flex';
                }
            } catch (err) {
                alert('No se pudieron cargar los pagos.');
            }
        }

        async function guardarAbono(e) {
            e.preventDefault();
            var pedidoId = document.getElementById('pagosId').value;
            var monto = document.getElementById('nuevoAbonoMonto').value;
            var metodo = document.getElementById('nuevoAbonoMetodo').value;
            var obs = document.getElementById('nuevoAbonoObs').value;
            var csrfToken = document.getElementById('csrf_token').value;

            try {
                var r = await fetch(basePath + '/api/pedidos/nuevo-abono', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ pedido_id: parseInt(pedidoId), monto: parseFloat(monto), metodo_pago: metodo, observacion: obs, csrf_token: csrfToken })
                });
                var res = await r.json();
                if (res.status === 'success') {
                    showToastRecep('Abono registrado correctamente.', 'success');
                    // Recargar modal
                    abrirModalPagos(pedidoId);
                    // Opcionalmente actualizar los datos en modalEditar si se cierra el pagos
                    document.getElementById('editAbonado').value = res.data.nuevo_abonado;
                    if(res.data.nuevo_estado) {
                        document.getElementById('editEstadoPago').value = res.data.nuevo_estado;
                    }
                } else {
                    alert('Error: ' + res.message);
                }
            } catch (err) {
                alert('Error de red al intentar abonar.');
            }
        }

        /* ===== Toast helper ===== */
        function showToastRecep(msg, type) {
            var t = document.getElementById('toastRecep');
            if (!t) {
                t = document.createElement('div');
                t.id = 'toastRecep';
                t.style.cssText = 'position:fixed;bottom:30px;right:30px;padding:14px 22px;border-radius:10px;color:white;font-weight:600;font-size:0.9rem;z-index:9999;transition:opacity 0.4s;';
                document.body.appendChild(t);
            }
            t.style.background = (type === 'success') ? '#10b981' : '#ef4444';
            t.textContent = msg;
            t.style.opacity = '1';
            setTimeout(function () { t.style.opacity = '0'; }, 2200);
        }

        /* ===== Buscador en paneles ===== */
        document.querySelectorAll('.search-input').forEach(function (input) {
            input.addEventListener('keyup', function (e) {
                var term = e.target.value.toLowerCase();
                var targetId = e.target.getAttribute('data-target');
                document.querySelectorAll('#' + targetId + ' .row-item').forEach(function (item) {
                    item.style.display = item.textContent.toLowerCase().includes(term) ? 'block' : 'none';
                });
            });
        });
    </script>

    <!-- Modal Credenciales WhatsApp (acceso rápido desde modal Crear Pedido) -->
    <div class="modal-wa" id="modalWaCredenciales">
        <div class="mw-box">
            <h2>&#128153; Credenciales WhatsApp</h2>
            <p class="mw-sub">
                Configura el <strong>Phone Sender ID</strong> y el <strong>Template ID</strong> generados por META en
                <a href="https://www.onurix.com" target="_blank" style="color:#25d366;">onurix.com</a> →
                WhatsApp → Templates.
            </p>

            <div class="mw-field">
                <label class="mw-label">ID de Cuenta Onurix (client)</label>
                <input type="text" id="waInputClientId" class="mw-input" placeholder="Ej: 7389"
                    value="<?= htmlspecialchars($waCfg['onurix_api_id'] ?? '')?>">
            </div>

            <div class="mw-field">
                <label class="mw-label">API Key Onurix (key)</label>
                <input type="password" id="waInputApiKey" class="mw-input" placeholder="Token secreto..."
                    value="<?= htmlspecialchars($waCfg['onurix_api_key'] ?? '')?>">
            </div>

            <div class="mw-field">
                <label class="mw-label">Phone Sender ID (ID del número remitente)</label>
                <input type="text" id="waInputPhoneSenderId" class="mw-input" placeholder="Ej: 123456789012345"
                    value="<?= htmlspecialchars($waPhoneSenderId)?>">
            </div>

            <div class="mw-field">
                <label class="mw-label">Template ID (ID de la plantilla generado por META)</label>
                <input type="text" id="waInputTemplateId" class="mw-input" placeholder="Ej: 987654321"
                    value="<?= htmlspecialchars($waTemplateId)?>">
            </div>

            <div
                style="background:rgba(37,211,102,.08);border:1px solid rgba(37,211,102,.2);border-radius:9px;padding:10px 14px;font-size:.78rem;color:#86efac;margin-top:8px;">
                &#128161; Puedes encontrar el Phone Sender ID y Template ID en el panel de
                Onurix → WhatsApp → Templates. Las credenciales se guardan en Configuración Avanzada.
            </div>

            <div class="mw-footer">
                <button class="btn-wa-cancel" onclick="cerrarModalWaCredenciales()">Cancelar</button>
                <button class="btn-wa-save" onclick="guardarWaCredenciales()">&#128190; Guardar</button>
            </div>
        </div>
    </div>

    <script>
        tsApp Modal Credenciales ===== */
        function abrirModalWaCredenciales() {
            document.getElementById('modalWaCredenciales').style.display = 'flex';
        }
        function cerrarModalWaCredenciales() {
            document.getElementById('modalWaCredenciales').style.display = 'none';
        }

        async function guardarWaCredenciales() {
            var clientId = document.getElementById('waInputClientId').value.trim();
            var apiKey = document.getElementById('waInputApiKey').value.trim();
            var phoneSender = document.getElementById('waInputPhoneSenderId').value.trim();
            var templateId = document.getElementById('waInputTemplateId').value.trim();

            if (!phoneSender || !templateId) {
                alert('El Phone Sender ID y el Template ID son obligatorios.');
                return;
            }

            var csrf = document.getElementById('csrf_token').value;
            var payload = {
                csrf_token: csrf,
                whatsapp_phone_sender_id: phoneSender,
                whatsapp_template_id: templateId
            };
            if (clientId) payload.onurix_api_id = clientId;
            if (apiKey) payload.onurix_api_key = apiKey;

            try {
                var r = await fetch(basePath + '/api/config/guardar', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                var res = await r.json();
                if (res.status === 'success') {
                    cerrarModalWaCredenciales();
                    showtRec0003; Credenciales WhatsApp guardadas correctamente.', 'success');
                } else {
                    alert('Error al guardar: ' + res.message);
                }
            } catch (e) {
                alert('Error de red al guardar creiales.');
            }
        }
    </script>


    <!-- ===== MODAL CONFIRMAR ELIMINAR PEDIDO ===== -->
    <div id="modalConfirmarEliminar" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(15,23,42,0.7); backdrop-filter:blur(4px); align-items:center; justify-content:center; animation:fadeIn .2s ease;">
        <div style="background:#fff; border-radius:20px; padding:36px 32px; max-width:400px; width:92vw; box-shadow:0 20px 60px rgba(0,0,0,.25); text-align:center; animation:slideUp .25s ease;">
            <div style="width:72px; height:72px; background:#fff1f2; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 18px; font-size:2.4rem; animation:shake .5s .1s ease both;">🗑️</div>
            <h2 style="color:#1e293b; font-size:1.3rem; margin-bottom:10px; border:none;">¿Eliminar pedido?</h2>
            <p style="color:#64748b; font-size:.95rem; margin-bottom:8px;" id="eliminarPedidoLabel">Esta acción marcará el pedido como cancelado.</p>
            <p style="color:#f43f5e; font-size:.82rem; margin-bottom:26px;">⚠️ Esta acción no se puede deshacer.</p>
            <div style="display:flex; gap:12px;">
                <button type="button" onclick="document.getElementById('modalConfirmarEliminar').style.display='none'"
                    style="flex:1; padding:12px; border-radius:12px; border:1.5px solid #e2e8f0; background:#f8fafc; color:#475569; font-weight:600; font-size:.95rem; cursor:pointer; transition:all .2s;">
                    Cancelar
                </button>
                <button type="button" id="btnConfEliminar" onclick="ejecutarEliminar()"
                    style="flex:1; padding:12px; border-radius:12px; border:none; background:linear-gradient(135deg,#ef4444,#dc2626); color:#fff; font-weight:700; font-size:.95rem; cursor:pointer; box-shadow:0 4px 14px rgba(239,68,68,.35); transition:all .2s;">
                    🗑️ Sí, eliminar
                </button>
            </div>
        </div>
    </div>

    <!-- ===== MODAL ENVIAR SMS MANUAL ===== -->
    <div id="modalSmsManual" class="modal-wa" onclick="if(event.target===this) cerrarSmsManual()" style="display:none;">
        <div class="mw-box" style="width:520px; max-width:96vw;">
            <h2 style="display:flex; align-items:center; gap:10px;">
                <span style="font-size:1.4rem;">💬</span> Enviar SMS Manual
            </h2>
            <p class="mw-sub">Escribe el número de destino y el mensaje. Se enviará vía la API Onurix.</p>

            <div class="mw-field">
                <label class="mw-label">📞 Número de teléfono</label>
                <input type="tel" id="smsManualNumero" class="mw-input" placeholder="Ej: 3184483187 (sin +57)"
                    style="font-size:1rem; letter-spacing:.5px;" oninput="this.value=this.value.replace(/[^0-9+]/g,'')">
            </div>

            <div class="mw-field">
                <label class="mw-label">✏️ Mensaje</label>
                <textarea id="smsManualTexto" class="mw-input" rows="5" placeholder="Escribe aquí tu mensaje SMS..."
                    maxlength="160" oninput="actualizarContadorSms(this)"
                    style="resize:vertical; font-size:.92rem; line-height:1.5;"></textarea>
                <div style="display:flex; justify-content:space-between; margin-top:5px;">
                    <span style="font-size:.75rem; color:#64748b;">Máximo 160 caracteres</span>
                    <span id="smsCharCount" style="font-size:.75rem; color:#94a3b8;">0 / 160</span>
                </div>
            </div>

            <!-- Estado respuesta -->
            <div id="smsManualStatus"
                style="display:none; padding:10px 14px; border-radius:9px; font-size:.85rem; font-weight:600; margin-bottom:10px;">
            </div>

            <div class="mw-footer">
                <button type="button" class="btn-wa-cancel" onclick="cerrarSmsManual()">Cancelar</button>
                <button type="button" class="btn-wa-save" id="btnEnviarSmsManual" onclick="enviarSmsManual()"
                    style="background:linear-gradient(135deg,#6366f1,#818cf8); display:flex; align-items:center; gap:8px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.5">
                        <line x1="22" y1="2" x2="11" y2="13" />
                        <polygon points="22 2 15 22 11 13 2 9 22 2" />
                    </svg>
                    Enviar SMS
                </button>
            </div>
        </div>
    </div>

    <script>
        function cerrarSmsManual() {
            document.getElementById('modalSmsManual').style.display = 'none';
            document.getElementById('smsManualNumero').value = '';
            document.getElementById('smsManualTexto').value = '';
            document.getElementById('smsCharCount').textContent = '0 / 160';
            document.getElementById('smsManualStatus').style.display = 'none';
        }

        function actualizarContadorSms(textarea) {
            const len = textarea.value.length;
            const el = document.getElementById('smsCharCount');
            el.textContent = len + ' / 160';
            el.style.color = len > 140 ? '#f59e0b' : '#94a3b8';
            if (len >= 160) el.style.color = '#ef4444';
        }

        async function enviarSmsManual() {
            const numero = document.getElementById('smsManualNumero').value.trim();
            const texto = document.getElementById('smsManualTexto').value.trim();
            const statusEl = document.getElementById('smsManualStatus');
            const btn = document.getElementById('btnEnviarSmsManual');

            if (!numero || numero.length < 7) {
                mostrarSmsStatus('error', '⚠️ Ingresa un número válido.');
                return;
            }
            if (!texto) {
                mostrarSmsStatus('error', '⚠️ El mensaje no puede estar vacío.');
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="animation:spin 1s linear infinite"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg> Enviando...';

            try {
                const res = await fetch('<?= $basePath?>/api/sms/enviar-manual', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ numero, texto })
                });
                const data = await res.json();
                if (data.status === 'success') {
                    mostrarSmsStatus('success', '✅ SMS enviado exitosamente a ' + numero);
                    document.getElementById('smsManualTexto').value = '';
                    document.getElementById('smsCharCount').textContent = '0 / 160';
                } else {
                    mostrarSmsStatus('error', '❌ Error: ' + (data.message || 'No se pudo enviar.'));
                }
            } catch (e) {
                mostrarSmsStatus('error', '❌ Error de conexión al servidor.');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg> Enviar SMS';
            }
        }

        function mostrarSmsStatus(tipo, msg) {
            const el = document.getElementById('smsManualStatus');
            el.style.display = 'block';
            el.style.background = tipo === 'success' ? 'rgba(16,185,129,.15)' : 'rgba(239,68,68,.15)';
            el.style.border = tipo === 'success' ? '1px solid rgba(16,185,129,.4)' : '1px solid rgba(239,68,68,.4)';
            el.style.color = tipo === 'success' ? '#10b981' : '#f87171';
            el.textContent = msg;
        }
    </script>
    <style>
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        @keyframes fadeIn {
            from { opacity:0; } to { opacity:1; }
        }
        @keyframes slideUp {
            from { opacity:0; transform:translateY(30px) scale(.95); }
            to   { opacity:1; transform:translateY(0)    scale(1);   }
        }
        @keyframes shake {
            0%,100%{ transform:rotate(0deg); }
            20%    { transform:rotate(-12deg); }
            40%    { transform:rotate(10deg); }
            60%    { transform:rotate(-8deg); }
            80%    { transform:rotate(6deg); }
        }
    </style>
    <script>
        function abrirModalEliminar() {
            if (!_currentPedido) return;
            var pedNum = '#PED-' + String(_currentPedido.id).padStart(4,'0');
            document.getElementById('eliminarPedidoLabel').textContent = 'Se eliminará el pedido ' + pedNum + '. Esta acción lo marcará como cancelado.';
            var m = document.getElementById('modalConfirmarEliminar');
            m.style.display = 'flex';
        }
        async function ejecutarEliminar() {
            if (!_currentPedido) return;
            var btn = document.getElementById('btnConfEliminar');
            btn.disabled = true;
            btn.textContent = 'Eliminando...';
            var csrfToken = document.getElementById('csrf_token').value;
            try {
                var r = await fetch(basePath + '/api/pedidos/eliminar', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ pedido_id: _currentPedido.id, csrf_token: csrfToken })
                });
                var res = await r.json();
                if (res.status === 'success') {
                    document.getElementById('modalConfirmarEliminar').style.display = 'none';
                    document.getElementById('modalDetalles').style.display = 'none';
                    if (window.BannerSounds) BannerSounds.eliminar();
                    showToastRecep('Pedido eliminado correctamente.', 'error');
                    setTimeout(function(){ window.location.reload(); }, 1200);
                } else {
                    alert('Error: ' + (res.message || 'No se pudo eliminar.'));
                    btn.disabled = false;
                    btn.textContent = '🗑️ Sí, eliminar';
                }
            } catch(e) {
                alert('Error de red. Intenta de nuevo.');
                btn.disabled = false;
                btn.textContent = '🗑️ Sí, eliminar';
            }
        }
    </script>

</body>
<script>
    window.BUND_CFG = { enabled: <?= $sonidoHabilitado === '1' ? 'true' : 'false'?>, theme: '<?= htmlspecialchars($sonidoTema)?>' };
</script>
<script src="<?= $basePath?>/js/sounds.js"></script>

</html>