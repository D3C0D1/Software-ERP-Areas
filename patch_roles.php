<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/database.php';
$db = \Config\Database::getInstance();
$db->query("INSERT IGNORE INTO roles (nombre) VALUES ('SuperAdmin')");
echo "Inserted SuperAdmin role.\n";
