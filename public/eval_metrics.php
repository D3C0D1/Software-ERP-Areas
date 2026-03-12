<?php
require_once __DIR__ . '/../config/Database.php';
$db = \Config\Database::getInstance();

echo "<pre>\n";

// 1. What accion values exist in movimientos_pedido?
echo "=== ACCIONES EN movimientos_pedido ===\n";
$rows = $db->query("SELECT accion, COUNT(*) AS total FROM movimientos_pedido GROUP BY accion ORDER BY total DESC")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r)
    echo $r['accion'] . " → " . $r['total'] . "\n";

// 2. Estado pagos distribution
echo "\n=== ESTADO_PAGO EN pedidos ===\n";
$rows = $db->query("SELECT estado_pago, COUNT(*) AS total, SUM(total) AS sum_total, SUM(abonado) AS sum_abonado FROM pedidos WHERE estado != 'cancelado' GROUP BY estado_pago")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r)
    printf("%-15s count=%d total=$%.0f abonado=$%.0f\n", $r['estado_pago'], $r['total'], $r['sum_total'], $r['sum_abonado']);

// 3. Pedidos updated today
echo "\n=== PEDIDOS ACTUALIZADOS HOY (" . date('Y-m-d') . ") ===\n";
$rows = $db->query("SELECT id, cliente_nombre, estado_pago, total, abonado, updated_at FROM pedidos WHERE DATE(updated_at) = CURDATE() AND estado != 'cancelado' ORDER BY updated_at DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
if (empty($rows))
    echo "(ninguno hoy)\n";
foreach ($rows as $r)
    printf("#%d %s | %s | total=$%.0f abonado=$%.0f | %s\n", $r['id'], $r['cliente_nombre'], $r['estado_pago'], $r['total'], $r['abonado'], $r['updated_at']);

// 4. Ingresos HOY (correcto): pago_completo → suma total; abono → suma de lo abonado
$ingresosHoyPagoCompleto = (float)$db->query(
    "SELECT COALESCE(SUM(total),0) FROM pedidos WHERE DATE(fecha_pago) = CURDATE() AND estado_pago='pago_completo' AND estado!='cancelado'"
)->fetchColumn();
$ingresosHoyAbono = (float)$db->query(
    "SELECT COALESCE(SUM(abonado),0) FROM pedidos WHERE DATE(fecha_pago) = CURDATE() AND estado_pago='abono' AND estado!='cancelado'"
)->fetchColumn();
printf("\n=== INGRESOS HOY (CORRECTO) ===\nPago Completo: $%.0f\nAbonado: $%.0f\nTOTAL INGRESADO HOY: $%.0f\n", $ingresosHoyPagoCompleto, $ingresosHoyAbono, $ingresosHoyPagoCompleto + $ingresosHoyAbono);

// 5. Ingresos semana (Lun-Dom)
$ingresosSemanaPagoCompleto = (float)$db->query(
    "SELECT COALESCE(SUM(total),0) FROM pedidos WHERE DATE(fecha_pago) >= DATE(NOW() - INTERVAL WEEKDAY(NOW()) DAY) AND estado_pago='pago_completo' AND estado!='cancelado'"
)->fetchColumn();
$ingresosSemanAabono = (float)$db->query(
    "SELECT COALESCE(SUM(abonado),0) FROM pedidos WHERE DATE(fecha_pago) >= DATE(NOW() - INTERVAL WEEKDAY(NOW()) DAY) AND estado_pago='abono' AND estado!='cancelado'"
)->fetchColumn();
printf("\n=== INGRESOS SEMANA ACTUAL ===\nPago Completo: $%.0f\nAbonado: $%.0f\nTOTAL SEMANA: $%.0f\n", $ingresosSemanaPagoCompleto, $ingresosSemanAabono, $ingresosSemanaPagoCompleto + $ingresosSemanAabono);

// 6. Ingresos mes
$ingresosMessPagoCompleto = (float)$db->query(
    "SELECT COALESCE(SUM(total),0) FROM pedidos WHERE YEAR(fecha_pago)=YEAR(CURDATE()) AND MONTH(fecha_pago)=MONTH(CURDATE()) AND estado_pago='pago_completo' AND estado!='cancelado'"
)->fetchColumn();
$ingresosMessAbono = (float)$db->query(
    "SELECT COALESCE(SUM(abonado),0) FROM pedidos WHERE YEAR(fecha_pago)=YEAR(CURDATE()) AND MONTH(fecha_pago)=MONTH(CURDATE()) AND estado_pago='abono' AND estado!='cancelado'"
)->fetchColumn();
printf("\n=== INGRESOS MES ACTUAL ===\nPago Completo: $%.0f\nAbonado: $%.0f\nTOTAL MES: $%.0f\n", $ingresosMessPagoCompleto, $ingresosMessAbono, $ingresosMessPagoCompleto + $ingresosMessAbono);

// 7. CxC - cuentas por cobrar (independiente de fecha)
$cxcTotal = (float)$db->query("SELECT COALESCE(SUM(total-abonado),0) FROM pedidos WHERE estado!='cancelado' AND estado_pago!='pago_completo' AND total>0")->fetchColumn();
$cxcAbono = (float)$db->query("SELECT COALESCE(SUM(total-abonado),0) FROM pedidos WHERE estado!='cancelado' AND estado_pago='abono' AND total>0")->fetchColumn();
$cxcNoPago = (float)$db->query("SELECT COALESCE(SUM(total),0) FROM pedidos WHERE estado!='cancelado' AND estado_pago='no_pago' AND total>0")->fetchColumn();
printf("\n=== CUENTAS POR COBRAR ===\nTotal pendiente (abono+nopago): $%.0f\nDe pedidos con abono parcial: $%.0f\nDe pedidos sin pago: $%.0f\n", $cxcTotal, $cxcAbono, $cxcNoPago);

// 8. Facturación del mes
$facMes = (float)$db->query("SELECT COALESCE(SUM(total),0) FROM pedidos WHERE YEAR(created_at)=YEAR(CURDATE()) AND MONTH(created_at)=MONTH(CURDATE()) AND estado!='cancelado'")->fetchColumn();
printf("\n=== FACTURACIÓN MES (total de pedidos creados este mes) ===\n$%.0f\n", $facMes);

echo "</pre>";