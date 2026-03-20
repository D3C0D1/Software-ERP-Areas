<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/database.php';
$db = \Config\Database::getInstance();

$hoyBogota = (new DateTime('now', new DateTimeZone('America/Bogota')))->format('Y-m-d');
$inicioBogota = new DateTime($hoyBogota . ' 00:00:00', new DateTimeZone('America/Bogota'));
$inicioBogota->setTimezone(new DateTimeZone('UTC'));
$inicioUTC = $inicioBogota->format('Y-m-d H:i:s');
$finBogota = new DateTime($hoyBogota . ' 23:59:59', new DateTimeZone('America/Bogota'));
$finBogota->setTimezone(new DateTimeZone('UTC'));
$finUTC = $finBogota->format('Y-m-d H:i:s');

echo "Hoy Colombia: " . $hoyBogota . "\n";
echo "Rango UTC:\n";
echo "Inicio UTC: " . $inicioUTC . "\n";
echo "Fin UTC   : " . $finUTC . "\n";
echo "---------------------------------\n";

// Historial Pagos
$pagos = $db->query("SELECT p.id, h.monto, h.metodo_pago, h.fecha_pago 
    FROM historial_pagos h JOIN pedidos p on p.id=h.pedido_id 
    ORDER BY h.fecha_pago DESC LIMIT 20")
    ->fetchAll(PDO::FETCH_ASSOC);

$totalRe = 0; $efRe = 0; $trRe = 0;
foreach($pagos as $p) {
    echo "P #" . $p['id'] . " | " . $p['fecha_pago'] . " | $" . $p['monto'] . " (" . $p['metodo_pago'] . ")\n";
    if ($p['fecha_pago'] >= $inicioUTC && $p['fecha_pago'] <= $finUTC) {
        $totalRe += $p['monto'];
        if ($p['metodo_pago'] == 'transferencia') $trRe += $p['monto'];
        else $efRe += $p['monto'];
    }
}
echo "---------------------------------\n";
echo "Total Calculado en rango UTC:\n";
echo "Total: $totalRe\nEf: $efRe\nTr: $trRe\n";
