<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Actualizando Base de Datos...</h1><ul>";

try {
    require_once __DIR__ . '/config/Database.php';
    $db = \Config\Database::getInstance();

    // Fix 1: pedidos.area_actual_id allows NULL (pedido inicia sin área asignada)
    $db->exec('ALTER TABLE pedidos MODIFY COLUMN area_actual_id INT UNSIGNED DEFAULT NULL;');
    echo "<li style='color:green;'>✅ <strong>pedidos.area_actual_id</strong> ahora permite NULL.</li>";

    // Fix 2: movimientos_pedido.area_id allows NULL (log de movimientos sin área)
    $db->exec('ALTER TABLE movimientos_pedido MODIFY COLUMN area_id INT UNSIGNED DEFAULT NULL;');
    echo "<li style='color:green;'>✅ <strong>movimientos_pedido.area_id</strong> ahora permite NULL.</li>";

    echo "</ul><h2 style='color:green;'>¡Base de datos actualizada correctamente!</h2>";
    echo "<p>Ya puedes crear pedidos desde Recepción sin errores.</p>";
    echo "<p><a href='/public/index.php/recepcion'>← Volver a Recepción</a></p>";

}
catch (Exception $e) {
    echo "</ul><h2 style='color:red;'>Error:</h2><pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
}