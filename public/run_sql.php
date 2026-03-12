<?php
require_once __DIR__ . '/../config/Database.php';

try {
    $db = \Config\Database::getInstance();
    
    // Create the new table for payment history
    $sqlTable = "CREATE TABLE IF NOT EXISTS historial_pagos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        pedido_id INT UNSIGNED NOT NULL,
        monto DECIMAL(10,2) NOT NULL,
        metodo_pago ENUM('efectivo', 'transferencia') NOT NULL DEFAULT 'efectivo',
        fecha_pago DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        usuario_id INT UNSIGNED,
        observacion TEXT,
        FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
    )";
    $db->exec($sqlTable);
    echo "Table historial_pagos created.\n";

    // Add metodo_pago to pedidos to track the initial or main method if needed
    // Assuming past payments are 'efectivo'
    $colCheck = $db->query("SHOW COLUMNS FROM pedidos LIKE 'metodo_pago'");
    if ($colCheck->rowCount() == 0) {
        $db->exec("ALTER TABLE pedidos ADD COLUMN metodo_pago ENUM('efectivo', 'transferencia') NOT NULL DEFAULT 'efectivo'");
        echo "Column metodo_pago added to pedidos.\n";
    }

    echo "SQL Executed successfully.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
