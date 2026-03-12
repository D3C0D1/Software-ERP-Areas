<?php
/**
 * Script de inicialización de la tabla configuracion.
 * Ejecutar una sola vez: http://localhost/Bnner/public/setup-config
 */
require_once __DIR__ . '/../config/Database.php';
$db = \Config\Database::getInstance();

$db->exec("
    CREATE TABLE IF NOT EXISTS configuracion (
        clave   VARCHAR(100) PRIMARY KEY,
        valor   TEXT,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

// Valores por defecto
$defaults = [
    'empresa_nombre' => 'Banner',
    'empresa_logo' => '',
    'onurix_api_id' => '7389',
    'onurix_api_key' => 'baf0076e7d995fc544c21cea4fdf898ce00612f268dc5f38c3565',
];

$stmt = $db->prepare("INSERT IGNORE INTO configuracion (clave, valor) VALUES (:k, :v)");
foreach ($defaults as $k => $v) {
    $stmt->execute(['k' => $k, 'v' => $v]);
}
echo json_encode(['status' => 'ok', 'message' => 'Tabla configuracion lista.']);