<?php
require_once __DIR__ . '/config/Database.php';
use Config\Database;

echo "<h2>Analizando tablas y columnas de fecha...</h2>";

try {
    $pdo = Database::getInstance();

    // Buscar tablas que tengan columnas relacionadas con fecha/hora
    $tables_stmt = $pdo->query("SELECT TABLE_NAME, COLUMN_NAME, DATA_TYPE 
                               FROM INFORMATION_SCHEMA.COLUMNS 
                               WHERE TABLE_SCHEMA = (SELECT DATABASE()) 
                               AND (COLUMN_NAME LIKE '%fecha%' OR COLUMN_NAME LIKE '%created%' OR COLUMN_NAME LIKE '%date%')
                               AND DATA_TYPE IN ('datetime', 'timestamp')");
    $columns = $tables_stmt->fetchAll();

    if (empty($columns)) {
        echo "No se encontraron columnas de fecha.";
    }
    else {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr><th>Tabla</th><th>Columna</th><th>Tipo</th><th>Ejemplo de valor actual</th></tr>";
        foreach ($columns as $col) {
            $tableName = $col['TABLE_NAME'];
            $colName = $col['COLUMN_NAME'];

            // Obtener un ejemplo
            $sample_stmt = $pdo->query("SELECT `$colName` FROM `$tableName` WHERE `$colName` IS NOT NULL LIMIT 1");
            $sample = $sample_stmt->fetch();
            $val = $sample ? $sample[$colName] : 'N/A';

            echo "<tr><td>$tableName</td><td>$colName</td><td>{$col['DATA_TYPE']}</td><td>$val</td></tr>";
        }
        echo "</table>";
    }

}
catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>