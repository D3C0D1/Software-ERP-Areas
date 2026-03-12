<?php
require_once __DIR__ . '/config/Database.php';
$db = \Config\Database::getInstance();
try {
    $db->exec("ALTER TABLE pedidos ADD COLUMN fue_editado TINYINT(1) NOT NULL DEFAULT 0;");
    echo "Column fue_editado added";
}
catch (Exception $e) {
    if (strpos($e->getMessage(), 'Duplicate column') !== false) {
        echo "Column already exists";
    }
    else {
        echo "Error: " . $e->getMessage();
    }
}