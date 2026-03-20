<?php
require_once __DIR__ . '/config/Database.php';

try {
    $db = \Config\Database::getInstance();
    
    // Tabla para almacenar los listados de Chat (1 persona = 1 chat)
    $sqlChats = "
    CREATE TABLE IF NOT EXISTS `whatsapp_chats` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `telefono` varchar(30) NOT NULL,
      `cliente_id` int(11) DEFAULT NULL,
      `nombre_contacto` varchar(150) DEFAULT NULL,
      `ultimo_mensaje` text DEFAULT NULL,
      `fecha_ultimo_mensaje` datetime DEFAULT NULL,
      `no_leidos` int(11) DEFAULT 0,
      `created_at` timestamp DEFAULT current_timestamp(),
      `updated_at` timestamp DEFAULT current_timestamp() ON UPDATE current_timestamp(),
      PRIMARY KEY (`id`),
      UNIQUE KEY `telefono_unico` (`telefono`),
      KEY `fk_cliente_wa` (`cliente_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $db->exec($sqlChats);
    echo "Tabla whatsapp_chats creada o actualizada.\n";
    
    // Tabla para almacenar todos los mensajes de manera individual conectada al ID del chat superior
    $sqlMensajes = "
    CREATE TABLE IF NOT EXISTS `whatsapp_mensajes` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `chat_id` int(11) NOT NULL,
      `direccion` enum('entrante','saliente') NOT NULL,
      `tipo` varchar(50) DEFAULT 'texto',
      `contenido` text DEFAULT NULL,
      `url_archivo` varchar(255) DEFAULT NULL,
      `estado` enum('pendiente','enviado','entregado','leido','fallido','recibido') DEFAULT 'recibido',
      `onurix_id` varchar(100) DEFAULT NULL,
      `fecha` datetime DEFAULT current_timestamp(),
      PRIMARY KEY (`id`),
      KEY `fk_chat_mensaje` (`chat_id`),
      CONSTRAINT `fk_chat_mensaje_ref` FOREIGN KEY (`chat_id`) REFERENCES `whatsapp_chats` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $db->exec($sqlMensajes);
    echo "Tabla whatsapp_mensajes creada o actualizada.\n";
    
} catch (\Exception $e) {
    echo "Error ejecutando el script: " . $e->getMessage() . "\n";
}
