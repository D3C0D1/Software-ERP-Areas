<?php
/**
 * Script de verificación de zona horaria en la base de datos local
 * Compara las fechas con la hora actual de Colombia (UTC-5)
 */

require_once __DIR__ . '/config/Database.php';
use Config\Database;

date_default_timezone_set('America/Bogota');
$horaColombiaActual = date('Y-m-d H:i:s');

echo "<!DOCTYPE html>
<html lang='es'>
<head>
<meta charset='UTF-8'>
<title>Verificación de Zona Horaria</title>
<style>
  body { font-family: Arial, sans-serif; background: #0f172a; color: #e2e8f0; padding: 20px; }
  h2 { color: #a78bfa; border-bottom: 1px solid #334155; padding-bottom: 8px; }
  h3 { color: #94a3b8; margin-top: 30px; }
  table { width: 100%; border-collapse: collapse; margin: 10px 0 30px; font-size: 13px; }
  th { background: #1e293b; color: #a78bfa; padding: 10px; text-align: left; }
  td { padding: 8px 10px; border-bottom: 1px solid #1e293b; }
  tr:hover td { background: #1e293b; }
  .ok   { color: #4ade80; font-weight: bold; }
  .warn { color: #facc15; font-weight: bold; }
  .bad  { color: #f87171; font-weight: bold; }
  .info-box { background: #1e293b; border-left: 4px solid #a78bfa; padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; }
</style>
</head>
<body>";

echo "<h2>🕒 Verificación de Zona Horaria — Base de Datos Local</h2>";
echo "<div class='info-box'>
  <b>Hora actual de Colombia (UTC-5):</b> {$horaColombiaActual}<br>
  <b>Zona PHP configurada:</b> " . date_default_timezone_get() . "
</div>";

try {
    $pdo = Database::getInstance();

    // Verificar hora MySQL
    $row = $pdo->query("SELECT NOW() as mysql_now, @@session.time_zone as tz")->fetch();
    echo "<div class='info-box'>
      <b>Hora MySQL (NOW()):</b> {$row['mysql_now']}<br>
      <b>Zona sesión MySQL:</b> {$row['tz']}
    </div>";

    $diff_segundos = abs(strtotime($horaColombiaActual) - strtotime($row['mysql_now']));
    if ($diff_segundos < 60) {
        echo "<p class='ok'>✅ PHP y MySQL están sincronizados con la hora de Colombia (diferencia: {$diff_segundos}s)</p>";
    }
    else {
        $diff_horas = round($diff_segundos / 3600, 1);
        echo "<p class='bad'>❌ Hay una diferencia de {$diff_horas} horas entre Colombia y MySQL. Verifica la configuración.</p>";
    }

    // ---- PEDIDOS ----
    echo "<h3>📦 Tabla: pedidos</h3>";
    echo "<table><tr><th>ID</th><th>Cliente</th><th>created_at</th><th>updated_at</th><th>last_movement_at</th><th>Estado</th></tr>";
    $stmt = $pdo->query("SELECT id, cliente_nombre, created_at, updated_at, last_movement_at, estado FROM pedidos ORDER BY id");
    while ($r = $stmt->fetch()) {
        // Verificar si la hora parece razonable (debe estar en el rango del 4-5 Mar 2026 hora Colombia)
        $hora = (int)date('H', strtotime($r['created_at']));
        $estado_hora = ($hora >= 0 && $hora <= 23) ? "<span class='ok'>✅ OK</span>" : "<span class='bad'>❌ Revisar</span>";
        echo "<tr>
            <td>{$r['id']}</td>
            <td>{$r['cliente_nombre']}</td>
            <td>{$r['created_at']}</td>
            <td>{$r['updated_at']}</td>
            <td>{$r['last_movement_at']}</td>
            <td>{$estado_hora}</td>
        </tr>";
    }
    echo "</table>";

    // ---- MOVIMIENTOS ----
    echo "<h3>🔄 Tabla: movimientos_pedido (primeros 20)</h3>";
    echo "<table><tr><th>ID</th><th>Pedido ID</th><th>Acción</th><th>created_at</th></tr>";
    $stmt = $pdo->query("SELECT id, pedido_id, accion, created_at FROM movimientos_pedido ORDER BY id LIMIT 20");
    while ($r = $stmt->fetch()) {
        echo "<tr><td>{$r['id']}</td><td>{$r['pedido_id']}</td><td>{$r['accion']}</td><td>{$r['created_at']}</td></tr>";
    }
    echo "</table>";

    // ---- USUARIOS ----
    echo "<h3>👥 Tabla: usuarios</h3>";
    echo "<table><tr><th>ID</th><th>Nombre</th><th>last_activity</th><th>created_at</th></tr>";
    $stmt = $pdo->query("SELECT id, nombre, last_activity, created_at FROM usuarios ORDER BY id");
    while ($r = $stmt->fetch()) {
        echo "<tr><td>{$r['id']}</td><td>{$r['nombre']}</td><td>{$r['last_activity']}</td><td>{$r['created_at']}</td></tr>";
    }
    echo "</table>";

    // ---- AUDITORIA (últimos 10) ----
    echo "<h3>📋 Tabla: auditoria_logs (últimos 10)</h3>";
    echo "<table><tr><th>ID</th><th>Usuario ID</th><th>Acción</th><th>created_at</th></tr>";
    $stmt = $pdo->query("SELECT id, usuario_id, accion, created_at FROM auditoria_logs ORDER BY id DESC LIMIT 10");
    while ($r = $stmt->fetch()) {
        echo "<tr><td>{$r['id']}</td><td>{$r['usuario_id']}</td><td>{$r['accion']}</td><td>{$r['created_at']}</td></tr>";
    }
    echo "</table>";

    // Resumen
    echo "<div class='info-box'>
      <b>Resumen:</b><br>
      • Si los pedidos del 4 de Marzo muestran horas entre las <b>12:00 y las 19:00</b>, la corrección fue exitosa ✅<br>
      • Si los pedidos muestran horas entre las <b>17:00 y las 00:00</b>, los datos aún están en UTC ❌ (no se ejecutó el script)
    </div>";

}
catch (Exception $e) {
    echo "<p class='bad'>Error al conectar: " . $e->getMessage() . "</p>";
}

echo "</body></html>";
?>
