<?php
require_once __DIR__ . '/config/Database.php';
$db = \Config\Database::getInstance();
$stmt = $db->query("DESCRIBE pedidos");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));