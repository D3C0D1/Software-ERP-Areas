<?php
require_once dirname(__DIR__) . '/config/Database.php';
try {
    $db = \Config\Database::getInstance();
    $db->exec("ALTER TABLE usuarios ADD COLUMN ver_metricas_recepcion TINYINT(1) DEFAULT 0");
    echo "Column ver_metricas_recepcion added successfully.\n";
}
catch (Exception $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Column already exists.\n";
    }
    else {
        echo "Error: " . $e->getMessage() . "\n";
    }
}