<?php
namespace App\Controllers;

use Config\Database;

class WhatsappController
{
    public function getConfig()
    {
        $db = Database::getInstance();
        $stmt = $db->query("SELECT clave, valor FROM configuracion WHERE clave IN ('onurix_webhook_salt', 'whatsapp_modo')");
        $data = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);
        
        $salt = (isset($data['onurix_webhook_salt'])) ? $data['onurix_webhook_salt'] : '';
        $modo = (isset($data['whatsapp_modo'])) ? $data['whatsapp_modo'] : 'demo';

        echo json_encode(['status' => 'success', 'salt' => $salt, 'modo' => $modo]);
        exit;
    }

    public function saveConfig()
    {
        $db = Database::getInstance();
        $salt = $_POST['salt'] ?? '';
        $modo = $_POST['modo'] ?? 'demo';

        // Validar SALT (Onurix exige minimo 16 caracteres para webhook)
        if (strlen($salt) < 16) {
            echo json_encode(['status' => 'error', 'message' => 'El Salt de Validación debe tener al menos 16 caracteres.']);
            exit;
        }

        try {
            // Guardar SALT
            $stmt = $db->prepare("INSERT INTO configuracion (clave, valor) VALUES ('onurix_webhook_salt', :val) ON DUPLICATE KEY UPDATE valor = VALUES(valor)");
            $stmt->execute(['val' => $salt]);

            // Guardar MODO
            $stmtModo = $db->prepare("INSERT INTO configuracion (clave, valor) VALUES ('whatsapp_modo', :val) ON DUPLICATE KEY UPDATE valor = VALUES(valor)");
            $stmtModo->execute(['val' => $modo]);

            echo json_encode(['status' => 'success', 'message' => 'Configuración de WhatsApp guardada correctamente.']);
        } catch (\Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error al guardar la configuración: ' . $e->getMessage()]);
        }
        exit;
    }
}
