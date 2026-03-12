<?php
$data = json_encode(['pedido_id' => 1]);
$ch = curl_init('http://localhost/Software/Banner_software/public/api/pedidos/eliminar');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Cookie: PHPSESSID=' . 'TEST_PHP_SESSID'
]);
$response = curl_exec($ch);
echo "Response: " . $response . "\n";
