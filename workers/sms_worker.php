<?php
/**
 * Cronjob de Servidor / Aplicación CLI.
 * Ubica esto para que CRONTAB en Linux o Tareas Programadas Windows lo ejecute cada 1 Minuto:
 * * * * * php /Applications/AMPPS/www/Bnner/workers/sms_worker.php
 */

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../app/services/OnurixService.php';

use App\Services\OnurixService;

try {
    echo "[" . date('Y-m-d H:i:s') . "] Iniciando Worker SMS Onurix...\n";

    // El worker inicializa independiente la conexión sin sobrecargar Apache (web)
    $smsService = new OnurixService();
    $resultado = $smsService->procesarColaPendiente();

    echo "[" . date('Y-m-d H:i:s') . "] DONE. " . $resultado . "\n";

}
catch (\Exception $e) {
    // Recomendación: Almacenar la traza en un error_log real
    echo "[" . date('Y-m-d H:i:s') . "] ERROR CRÍTICO CRON: " . $e->getMessage() . "\n";
}