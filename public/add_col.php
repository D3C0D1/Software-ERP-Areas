<?php
require_once __DIR__ . '/../config/Database.php';
try {
    $db = \Config\Database::getInstance();
    $db->exec("ALTER TABLE usuarios ADD COLUMN crear_enviar_pedidos TINYINT(1) DEFAULT 0");
    echo "OK";
}
catch (Exception $e) {
    if (strpos($e->getMessage(), 'Duplicate column') !== false) {
        echo "OK - already exists";
    }
    else {
        echo "Error: " . $e->getMessage();
    }
}