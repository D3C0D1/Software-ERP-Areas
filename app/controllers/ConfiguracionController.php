<?php
namespace App\Controllers;

use Config\Database;
use Exception;
use PDO;

class ConfiguracionController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
        if (session_status() === PHP_SESSION_NONE)
            session_start();
    }

    /** Muestra la vista de configuración */
    public function indexView()
    {
        require_once dirname(__DIR__, 2) . '/views/configuracion.php';
    }

    /** POST /api/config/guardar — guarda una o más claves */
    public function guardar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit;
        }
        try {
            $this->crearTablaIfNeeded();

            $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            $allowed = [
                'empresa_nombre', 'empresa_logo', 'onurix_api_id', 'onurix_api_key', 'sms_crear', 'sms_finalizar',
                'icon_dashboard', 'icon_recepcion', 'icon_reportes', 'icon_reportes_pedidos', 'icon_usuarios', 'icon_areas', 'icon_configuracion',
                'mostrar_credenciales', 'fondo_login', 'fondo_dashboard', 'auto_backup_diario',
                'whatsapp_phone_sender_id', 'whatsapp_template_id', 'whatsapp_activo',
                'whatsapp_template_id_finalizar',
                'whatsapp_var_nombre', 'whatsapp_var_link',
                'sonido_habilitado', 'sonido_tema',
                'sms_fin_enabled', 'sms_fin_checked_default',
                'wa_fin_enabled', 'wa_fin_checked_default',
                'sms_crear_enabled', 'sms_crear_checked_default',
                'wa_crear_enabled', 'wa_crear_checked_default'
            ];

            $stmt = $this->db->prepare(
                "INSERT INTO configuracion (clave, valor) VALUES (:k, :v)
                 ON DUPLICATE KEY UPDATE valor = :v2, updated_at = NOW()"
            );

            $saved = 0;
            $savedKeys = [];
            foreach ($allowed as $key) {
                if (array_key_exists($key, $input)) {
                    $val = $input[$key];
                    $stmt->execute(['k' => $key, 'v' => $val, 'v2' => $val]);
                    $saved++;
                    $savedKeys[] = $key;
                }
            }

            if ($saved === 0)
                throw new Exception("No se envió ningún valor válido.");

            // LOG AUDITORIA
            $adminId = $_SESSION['user_id'] ?? null;
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $accion = 'actualizar';
            if (in_array('onurix_api_key', $savedKeys))
                $accion = 'onurix_cambio';

            $stmtAudit = $this->db->prepare("INSERT INTO auditoria_logs (usuario_id, entidad_tipo, entidad_id, accion, descripcion_accion, ip_address) VALUES (?, 'configuracion', 0, ?, ?, ?)");
            $stmtAudit->execute([$adminId, $accion, 'Modificó configuraciones: ' . implode(', ', $savedKeys), $ip]);

            echo json_encode(['status' => 'success', 'message' => 'Guardado correctamente.']);
        }
        catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * POST /api/config/subir-fondo
     * Guarda la imagen en disco (public/img/fondos/) y almacena solo la ruta en BD.
     * Evita el error 2006 "MySQL server has gone away" causado por imágenes base64 grandes.
     */
    public function subirFondo()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit;
        }
        try {
            $this->crearTablaIfNeeded();
            $clave = $_POST['clave'] ?? null;
            $allowedClaves = ['fondo_login', 'fondo_dashboard', 'empresa_logo'];

            if (!in_array($clave, $allowedClaves)) {
                throw new Exception("Clave de imagen inválida.");
            }

            // QUITAR imagen actual
            if (!empty($_POST['quitar'])) {
                $stmtC = $this->db->prepare("SELECT valor FROM configuracion WHERE clave = :k");
                $stmtC->execute(['k' => $clave]);
                $currentUrl = $stmtC->fetchColumn();
                if ($currentUrl && strpos($currentUrl, '/img/fondos/') !== false) {
                    $abs = dirname(__DIR__, 2) . '/public/img/fondos/' . basename(parse_url($currentUrl, PHP_URL_PATH));
                    if (file_exists($abs))
                        @unlink($abs);
                }
                $s = $this->db->prepare("INSERT INTO configuracion (clave, valor) VALUES (:k, '') ON DUPLICATE KEY UPDATE valor='', updated_at=NOW()");
                $s->execute(['k' => $clave]);
                echo json_encode(['status' => 'success', 'message' => 'Imagen eliminada.', 'url' => '']);
                return;
            }

            // GUARDAR nueva imagen
            if (empty($_FILES['imagen']) || $_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception("No se recibió ningún archivo de imagen válido.");
            }

            $file = $_FILES['imagen'];
            if ($file['size'] > 5 * 1024 * 1024) {
                throw new Exception("El archivo no puede superar 5 MB.");
            }

            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($file['tmp_name']);
            $exts = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
            if (!isset($exts[$mime])) {
                throw new Exception("Tipo de archivo no permitido. Usa JPG, PNG o WebP.");
            }
            $ext = $exts[$mime];

            $uploadDir = dirname(__DIR__, 2) . '/public/img/fondos/';
            if (!is_dir($uploadDir))
                mkdir($uploadDir, 0755, true);

            // Borrar imagen anterior si era subida por nosotros
            $stmtC = $this->db->prepare("SELECT valor FROM configuracion WHERE clave = :k");
            $stmtC->execute(['k' => $clave]);
            $oldUrl = $stmtC->fetchColumn();
            if ($oldUrl && strpos($oldUrl, '/img/fondos/') !== false) {
                $absOld = $uploadDir . basename(parse_url($oldUrl, PHP_URL_PATH));
                if (file_exists($absOld))
                    @unlink($absOld);
            }

            $filename = $clave . '_' . time() . '.' . $ext;
            $dest = $uploadDir . $filename;
            if (!move_uploaded_file($file['tmp_name'], $dest)) {
                throw new Exception("No se pudo guardar el archivo en el servidor.");
            }

            // Calcular URL relativa
            $scriptDir = dirname($_SERVER['SCRIPT_NAME']); // p.ej /Bnner/public
            $base = rtrim(str_replace('/public', '', $scriptDir), '/');
            $url = $base . '/public/img/fondos/' . $filename;

            $stmtSave = $this->db->prepare("INSERT INTO configuracion (clave, valor) VALUES (:k, :v) ON DUPLICATE KEY UPDATE valor=:v2, updated_at=NOW()");
            $stmtSave->execute(['k' => $clave, 'v' => $url, 'v2' => $url]);

            echo json_encode(['status' => 'success', 'message' => 'Imagen guardada correctamente.', 'url' => $url]);

        }
        catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /** POST /api/config/limpiar-logs — borra movimientos_pedido con más de 90 días */
    public function limpiarLogs()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit;
        }
        try {
            $stmt = $this->db->prepare(
                "DELETE FROM movimientos_pedido WHERE created_at < NOW() - INTERVAL 90 DAY"
            );
            $stmt->execute();
            $n = $stmt->rowCount();
            echo json_encode(['status' => 'success', 'message' => "Se eliminaron {$n} registros antiguos."]);
        }
        catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /** GET /api/config/get — retorna configuración (sin clave secreta completa) */
    public function getConfig()
    {
        try {
            $this->crearTablaIfNeeded();
            $stmt = $this->db->query("SELECT clave, valor FROM configuracion");
            $rows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

            // Enmascarar la API key
            if (!empty($rows['onurix_api_key'])) {
                $key = $rows['onurix_api_key'];
                $rows['onurix_api_key'] = str_repeat('•', max(0, strlen($key) - 4)) . substr($key, -4);
            }
            echo json_encode(['status' => 'success', 'data' => $rows]);
        }
        catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /** GET /api/config/probar-sms — Pruebas de conexión Onurix */
    public function probarSms()
    {
        try {
            require_once dirname(__DIR__) . '/services/OnurixService.php';
            $onurix = new \App\Services\OnurixService();
            $res = $onurix->probarConexion();

            if ($res['ok']) {
                echo json_encode(['status' => 'success', 'message' => $res['msg'], 'detail' => $res['detail'] ?? null]);
            }
            else {
                echo json_encode(['status' => 'error', 'message' => $res['msg'] ?? 'Error desconocido', 'detail' => $res['detail'] ?? null]);
            }
        }
        catch (\Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Fallo al instanciar servicio: ' . $e->getMessage()]);
        }
        exit;
    }

    /** GET /api/config/probar-saldo — Obtiene el saldo de Onurix */
    public function probarSaldo()
    {
        try {
            require_once dirname(__DIR__) . '/services/OnurixService.php';
            $onurix = new \App\Services\OnurixService();
            $res = $onurix->obtenerSaldo();

            if ($res['ok']) {
                echo json_encode(['status' => 'success', 'balance' => $res['balance']]);
            }
            else {
                echo json_encode(['status' => 'error', 'message' => $res['msg'] ?? 'Error desconocido', 'detail' => $res['detail'] ?? null]);
            }
        }
        catch (\Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Fallo al instanciar servicio: ' . $e->getMessage()]);
        }
        exit;
    }
    /** POST /api/config/eliminar-todos-pedidos — Elimina todos los pedidos */
    public function eliminarTodosPedidos()
    {
        try {
            $this->db->beginTransaction();

            // Eliminar archivos adjuntos (físicos)
            $stmtArchivos = $this->db->query("SELECT ruta_almacenamiento FROM archivos WHERE entidad_tipo='pedido'");
            while ($file = $stmtArchivos->fetchColumn()) {
                $p = dirname(__DIR__, 2) . '/storage/uploads/' . $file;
                if (file_exists($p))
                    @unlink($p);
            }

            // Eliminar dependencias
            $this->db->exec("DELETE FROM archivos WHERE entidad_tipo='pedido'");
            $this->db->exec("DELETE FROM auditoria_logs WHERE entidad_tipo='pedido'");

            // Eliminar explicitly logs y registros con FK
            $this->db->exec("DELETE FROM movimientos_pedido");
            $this->db->exec("DELETE FROM pagos");
            $this->db->exec("DELETE FROM devoluciones");

            // Eliminar finalmente los pedidos
            $this->db->exec("DELETE FROM pedidos");

            $this->db->commit();

            // Resetear AUTO_INCREMENT (Causa commit implícito, se hace después del commit explícito)
            $this->db->exec("ALTER TABLE pedidos AUTO_INCREMENT = 1");
            $this->db->exec("ALTER TABLE movimientos_pedido AUTO_INCREMENT = 1");
            $this->db->exec("ALTER TABLE pagos AUTO_INCREMENT = 1");
            $this->db->exec("ALTER TABLE devoluciones AUTO_INCREMENT = 1");
            $this->db->exec("ALTER TABLE archivos AUTO_INCREMENT = 1");

            http_response_code(200);
            echo json_encode(['status' => 'success', 'message' => "Se han eliminado todos los pedidos y sus detalles."]);
        }
        catch (\Exception $e) {
            if ($this->db->inTransaction())
                $this->db->rollBack();
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /** POST /api/config/eliminar-auditoria — Elimina todos los reportes de auditoría */
    public function eliminarAuditoria()
    {
        try {
            // Eliminar solo los reportes (vaciar tabla completa o por un rango)
            // Para simplicidad, se solicita al cliente vaciar todos
            $this->db->exec("DELETE FROM auditoria_logs");

            // Resetear AUTO_INCREMENT (implícito)
            $this->db->exec("ALTER TABLE auditoria_logs AUTO_INCREMENT = 1");

            http_response_code(200);
            echo json_encode(['status' => 'success', 'message' => "Se ha vaciado todo el historial de los reportes de auditoría."]);
        }
        catch (\Exception $e) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /** Exporta la BD bajo demanda manualmente */
    public function exportarDB()
    {
        try {
            $this->generarBackupDB(true);
        }
        catch (\Exception $e) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /** Ejecuta un proceso asíncrono para verificar si se debe hacer el backup diario */
    public function checkAutoBackup()
    {
        try {
            $this->crearTablaIfNeeded();

            $stmt = $this->db->query("SELECT valor FROM configuracion WHERE clave = 'ultima_exportacion_db'");
            $res = $stmt->fetch();
            $ultima = $res ? $res['valor'] : '';

            $stmt2 = $this->db->query("SELECT valor FROM configuracion WHERE clave = 'auto_backup_diario'");
            $res2 = $stmt2->fetch();
            $autoBackup = $res2 ? $res2['valor'] : '1';

            if ($autoBackup === '1' && $ultima !== date('Y-m-d')) {
                // Hacer backup pero sin forzar descarga
                $this->generarBackupDB(false);
                echo json_encode(['status' => 'success', 'message' => "Backup diario generado."]);
            }
            else {
                echo json_encode(['status' => 'success', 'message' => "No requiere backup el dia de hoy."]);
            }
        }
        catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /** Lógica principal para generar el SQL y guardarlo/descargarlo */
    private function generarBackupDB($descargar = false)
    {
        $dir = dirname(dirname(__DIR__)) . '/public/backups';
        if (!is_dir($dir))
            mkdir($dir, 0755, true);

        // Nombres de archivos requeridos: Dia_Fecha_Hora
        $dias = ['Domingo', 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'];
        $diaStr = $dias[date('w')];
        $fechaStr = date('d_m_Y_H_i_s');

        $filename = "BD_Bnner_{$diaStr}_{$fechaStr}.sql";
        $filepath = $dir . '/' . $filename;

        $fileHandler = fopen($filepath, 'w');
        if (!$fileHandler)
            throw new \Exception("No se pudo crear el archivo de respaldo físico.");

        fwrite($fileHandler, "-- Respaldo Generado Automáticamente: " . date('Y-m-d H:i:s') . "\n\n");
        fwrite($fileHandler, "SET FOREIGN_KEY_CHECKS=0;\n\n");

        $tablesObj = $this->db->query("SHOW TABLES");
        while ($tableRow = $tablesObj->fetch(\PDO::FETCH_NUM)) {
            $table = $tableRow[0];
            $createObj = $this->db->query("SHOW CREATE TABLE `" . $table . "`");
            $createRow = $createObj->fetch(\PDO::FETCH_NUM);

            fwrite($fileHandler, "DROP TABLE IF EXISTS `$table`;\n");
            fwrite($fileHandler, $createRow[1] . ";\n\n");

            $rowsObj = $this->db->query("SELECT * FROM `" . $table . "`");
            while ($row = $rowsObj->fetch(\PDO::FETCH_ASSOC)) {
                $colsEsced = array_map(function ($c) {
                    return "`$c`";
                }, array_keys($row));
                $valsEsced = array_map(function ($v) {
                    if ($v === null)
                        return "NULL";
                    return $this->db->quote($v);
                }, array_values($row));

                fwrite($fileHandler, "INSERT IGNORE INTO `$table` (" . implode(', ', $colsEsced) . ") VALUES (" . implode(', ', $valsEsced) . ");\n");
            }
            fwrite($fileHandler, "\n");
        }
        fwrite($fileHandler, "SET FOREIGN_KEY_CHECKS=1;\n");
        fclose($fileHandler);

        $stmt = $this->db->prepare("INSERT INTO configuracion (clave, valor) VALUES ('ultima_exportacion_db', :fecha1) ON DUPLICATE KEY UPDATE valor = :fecha2");
        $stmt->execute(['fecha1' => date('Y-m-d'), 'fecha2' => date('Y-m-d')]);

        if ($descargar) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . basename($filepath));
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filepath));
            readfile($filepath);
            exit;
        }

        return $filename;
    }

    private function crearTablaIfNeeded()
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS configuracion (
                clave       VARCHAR(100) PRIMARY KEY,
                valor       MEDIUMTEXT,
                updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        try {
            // Asegurar que soporte base64 grandes en hostinger
            $this->db->exec("ALTER TABLE configuracion MODIFY valor MEDIUMTEXT");
        }
        catch (\Exception $e) {
        }

        $defaults = [
            'empresa_nombre' => 'Banner',
            'empresa_logo' => '',
            'onurix_api_id' => '',
            'onurix_api_key' => '',
            'sms_crear' => 'Hola {nombre}, tu pedido {link_seguimiento} ha sido creado. Gracias por confiar en {empresa}',
            'sms_finalizar' => 'Hola {nombre}, su pedido ha sido terminado, ya lo puede recoger en {empresa}',
            'mostrar_credenciales' => '1',
            'auto_backup_diario' => '1',
            'ultima_exportacion_db' => '',
            'whatsapp_phone_sender_id' => '',
            'whatsapp_template_id' => '',
            'whatsapp_activo' => '1',
        ];
        $s = $this->db->prepare("INSERT IGNORE INTO configuracion (clave, valor) VALUES (:k, :v)");
        foreach ($defaults as $k => $v)
            $s->execute(['k' => $k, 'v' => $v]);
    }
}