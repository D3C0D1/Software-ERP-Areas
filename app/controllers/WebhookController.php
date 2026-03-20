<?php
namespace App\Controllers;

use Config\Database;

class WebhookController
{
    public function handleOnurix()
    {
        // Onurix envía los datos del webhook (probablemente por POST en formato JSON o Form-data)
        // También puede enviar parámetros de validación para la integración inicial, dependiendo de su API.

        $db = Database::getInstance();
        
        // Obtenemos el SALT (Llave API) configurada en nuestra base de datos para Validar.
        try {
            $stmt = $db->query("SELECT valor FROM configuracion WHERE clave = 'onurix_webhook_salt'");
            $onurixSalt = $stmt->fetchColumn();
        } catch (\Exception $e) {
            $onurixSalt = null;
        }

        // Leer el payload (Cuerpo de la petición)
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        // Log para depuración (Útil para revisar qué manda exactamente Onurix)
        file_put_contents(dirname(__DIR__) . '/../storage/logs/webhook_onurix.log', "[" . date('Y-m-d H:i:s') . "] " . print_r($_POST, true) . "\n" . $input . "\n", FILE_APPEND);

        $verify_token = $_GET['verify_token'] ?? '';
        
        // --- VALIDACION DE SALT ---
        if (!empty($onurixSalt) && $verify_token !== $onurixSalt) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Token invalido.']);
            exit;
        }

        if ($data) {
            // Procesamiento de Mensaje Entrante
            
            $messageId = $data['messageId'] ?? null;
            $customerPhone = $data['toWhatsAppNumber'] ?? null;
            $customerName = $data['toWhatsAppName'] ?? 'Desconocido';
            $receivedAt = isset($data['receivedAt']) ? date('Y-m-d H:i:s', $data['receivedAt']) : date('Y-m-d H:i:s');
            
            if ($messageId && $customerPhone) {
                // Determinar el tipo y el contenido del mensaje
                $tipo = 'texto';
                $contenido = '';
                $urlArchivo = null;
                $mediaId = null;

                if (isset($data['message'])) {
                    $tipo = 'texto';
                    $contenido = $data['message'];
                } elseif (isset($data['button'])) {
                    $tipo = 'boton';
                    $contenido = $data['button']['text'] ?? '';
                } elseif (isset($data['interactive'])) {
                    $tipo = 'interactivo';
                    $contenido = $data['interactive']['title'] ?? '';
                    if (isset($data['interactive']['description']) && $data['interactive']['description'] != '') {
                        $contenido .= " - " . $data['interactive']['description'];
                    }
                } elseif (isset($data['image'])) {
                    $tipo = 'imagen';
                    $contenido = $data['image']['caption'] ?? 'Imagen recibida';
                    $mediaId = $data['image']['mediaId'] ?? null;
                } elseif (isset($data['audio'])) {
                    $tipo = 'audio';
                    $contenido = 'Audio recibido';
                    $mediaId = $data['audio']['mediaId'] ?? null;
                } elseif (isset($data['document'])) {
                    $tipo = 'documento';
                    $contenido = $data['document']['fileName'] ?? 'Documento recibido';
                    $mediaId = $data['document']['mediaId'] ?? null;
                } elseif (isset($data['sticker'])) {
                    $tipo = 'sticker';
                    $contenido = 'Sticker recibido';
                    $mediaId = $data['sticker']['mediaId'] ?? null;
                }
                
                // Si Onurix manda el archivo directamente (rara vez, pero por si acaso guardamos el mediaId en url_archivo para descargarlo luego si se requiere)
                if ($mediaId) {
                    $urlArchivo = "mediaId:" . $mediaId;
                }

                // Obtener o Crear el Chat
                $stmt = $db->prepare("SELECT id_chat, no_leidos FROM whatsapp_chats WHERE telefono = ?");
                $stmt->execute([$customerPhone]);
                $chat = $stmt->fetch();
                
                if ($chat) {
                    $chatId = $chat['id_chat'];
                    $nuevosNoLeidos = (int)$chat['no_leidos'] + 1;
                    
                    $stmtUpd = $db->prepare("UPDATE whatsapp_chats SET ultimo_mensaje = ?, fecha_ultimo_mensaje = ?, no_leidos = ?, nombre_contacto = ? WHERE id_chat = ?");
                    $stmtUpd->execute([$contenido, $receivedAt, $nuevosNoLeidos, $customerName, $chatId]);
                } else {
                    $stmtIns = $db->prepare("INSERT INTO whatsapp_chats (telefono, nombre_contacto, ultimo_mensaje, fecha_ultimo_mensaje, no_leidos, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
                    $stmtIns->execute([$customerPhone, $customerName, $contenido, $receivedAt, 1]);
                    $chatId = $db->lastInsertId();
                }
                
                // Insertar el Mensaje (evitar duplicados por onurix_id que es nuestro id_mensaje)
                $stmtChk = $db->prepare("SELECT id_mensaje FROM whatsapp_mensajes WHERE onurix_id = ?");
                $stmtChk->execute([$messageId]);
                
                if (!$stmtChk->fetch()) {
                    $stmtMsg = $db->prepare("INSERT INTO whatsapp_mensajes (chat_id, direccion, tipo, contenido, url_archivo, estado, onurix_id, fecha) VALUES (?, 'entrante', ?, ?, ?, 'recibido', ?, ?)");
                    $stmtMsg->execute([$chatId, $tipo, $contenido, $urlArchivo, $messageId, $receivedAt]);
                }
            }
        }

        // Respuesta existosa para la plataforma Onurix
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'message' => 'Webhook procesado correctamente.']);
        exit;
    }
}
