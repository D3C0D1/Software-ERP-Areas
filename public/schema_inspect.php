<?php
require_once __DIR__ . '/../config/Database.php';
$db = \Config\Database::getInstance();

// Check pedidos table columns
$cols = $db->query("SHOW COLUMNS FROM pedidos")->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c)
    echo $c['Field'] . " | " . $c['Type'] . "\n";

echo "\n--- movimientos_pedido ---\n";
$cols2 = $db->query("SHOW COLUMNS FROM movimientos_pedido")->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols2 as $c)
    echo $c['Field'] . " | " . $c['Type'] . "\n";