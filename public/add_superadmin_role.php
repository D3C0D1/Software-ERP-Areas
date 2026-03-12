<?php
require_once __DIR__ . '/../config/Database.php';
try {
    $db = \Config\Database::getInstance();
    $db->exec("INSERT IGNORE INTO roles (id, nombre, descripcion) VALUES (4, 'SuperAdmin', 'Control total y acceso a contabilidad')");
    echo "OK";
}
catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}