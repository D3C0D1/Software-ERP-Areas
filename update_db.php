<?php
require_once __DIR__ . '/config/Database.php';
$db = \Config\Database::getInstance();
try {
    $db->exec("ALTER TABLE usuarios ADD COLUMN editar_pedidos TINYINT(1) NOT NULL DEFAULT 0;");
    echo "Columna editar_pedidos agregada.\n";
}
catch (Exception $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Columna ya existe.\n";
    }
    else {
        echo "Error: " . $e->getMessage() . "\n";
    }
}