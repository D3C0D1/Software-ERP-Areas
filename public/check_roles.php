<?php
require_once __DIR__ . '/../config/Database.php';
$db = \Config\Database::getInstance();

// Check roles table
echo "-- ROLES\n";
$roles = $db->query("SELECT * FROM roles")->fetchAll(PDO::FETCH_ASSOC);
foreach ($roles as $r)
    echo $r['id'] . " | " . $r['nombre'] . "\n";

// Check add_superadmin_role ran
echo "\n-- Done\n";