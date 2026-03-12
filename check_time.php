<?php
// Script para verificar la hora del sistema y de la base de datos en Hostinger
// Ubicación recomendada: /Applications/AMPPS/www/Bnner/check_time.php

// 1. Hora de PHP (Servidor Web)
echo "<h3>1. Hora de PHP (Servidor Web)</h3>";
echo "Zona horaria actual de PHP: " . date_default_timezone_get() . "<br>";
echo "Fecha y hora actual de PHP: " . date('Y-m-d H:i:s') . "<br>";

// 2. Hora de la Base de Datos (MySQL)
echo "<h3>2. Hora de la Base de Datos (MySQL)</h3>";

require_once __DIR__ . '/config/Database.php';
use Config\Database;

try {
    $pdo = Database::getInstance();

    // Consultar variables de tiempo de MySQL
    $stmt = $pdo->query("SELECT NOW() as mysql_now, @@global.time_zone as global_tz, @@session.time_zone as session_tz");
    $row = $stmt->fetch();

    echo "Fecha y hora actual de MySQL (NOW()): " . $row['mysql_now'] . "<br>";
    echo "Zona horaria Global de MySQL: " . $row['global_tz'] . "<br>";
    echo "Zona horaria de Sesión de MySQL: " . $row['session_tz'] . "<br>";

}
catch (Exception $e) {
    echo "Error al conectar con la base de datos: " . $e->getMessage();
}

echo "<h3>3. Instrucciones para sincronizar con Colombia</h3>";
echo "<p>Para que tu sistema use la hora de Bogotá, Colombia (UTC-5), debes realizar lo siguiente:</p>";
echo "<ul>
    <li><b>En PHP:</b> Al inicio de tu archivo principal (index.php), agrega: <br><code>date_default_timezone_set('America/Bogota');</code></li>
    <li><b>En MySQL:</b> Después de conectar a la base de datos, ejecuta el siguiente comando: <br><code>SET time_zone = '-05:00';</code></li>
</ul>";
?>
