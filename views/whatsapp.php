<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ./login');
    exit;
}
require_once __DIR__ . '/../config/Database.php';
$db = \Config\Database::getInstance();
$role = $_SESSION['role'] ?? 'Operador';
$userName = $_SESSION['user_id'] == 1 ? 'Administrador' : ($_SESSION['email'] ?? 'Usuario');
$isAdmin = in_array($role, ['Admin', 'SuperAdmin']);

$csrfToken = \App\Middlewares\CsrfMiddleware::generateToken();

// URL pública sugerida para el webhook
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$domainName = $_SERVER['HTTP_HOST'];
$scriptPath = dirname($_SERVER['SCRIPT_NAME']);
$basePath = str_replace('/public', '', $scriptPath);
if ($basePath === '/' || $basePath === '\\') $basePath = '';
$webhookUrl = $protocol . $domainName . $basePath . '/webhook/onurix';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>WhatsApp - Mensajería</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4F46E5;
            --primary-hover: #4338CA;
            --bg-color: #0F172A;
            --border: rgba(255, 255, 255, 0.1);
            --chat-bg: #f8fafc;
            --panel-bg: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: var(--bg-color);
            display: flex;
            height: 100vh;
            overflow: hidden;
            color: var(--text-main);
        }

        .main-content {
            flex: 1;
            display: flex;
            background: var(--chat-bg);
            border-radius: 20px 0 0 20px;
            overflow: hidden;
            box-shadow: -10px 0 30px rgba(0,0,0,0.1);
            z-index: 11;
            position: relative;
        }

        /* ----- LISTA DE CHATS (Left Panel) ----- */
        .chat-list-panel {
            width: 380px;
            background: var(--panel-bg);
            border-right: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
            z-index: 2;
        }

        .chat-list-header {
            padding: 25px 20px 15px;
            border-bottom: 1px solid #f1f5f9;
        }

        .chat-list-header h2 {
            font-size: 1.5rem;
            color: #1e1b4b;
            font-weight: 800;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .chat-list-header h2 i {
            color: #25D366; /* WhatsApp color */
        }

        .search-box {
            position: relative;
        }

        .search-box input {
            width: 100%;
            padding: 12px 15px 12px 40px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            background: #f1f5f9;
            color: #334155;
            font-size: 0.9rem;
            outline: none;
            transition: all 0.3s;
        }

        .search-box input:focus {
            border-color: var(--primary);
            background: #fff;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .search-box svg {
            position: absolute;
            left: 14px;
            top: 13px;
            color: #94a3b8;
            width: 18px;
            height: 18px;
        }

        .chat-list {
            flex: 1;
            overflow-y: auto;
            padding: 10px 0;
        }
        
        .chat-list::-webkit-scrollbar { width: 6px; }
        .chat-list::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 10px; }

        .chat-item {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            gap: 15px;
            cursor: pointer;
            transition: all 0.2s;
            border-bottom: 1px solid transparent;
        }

        .chat-item:hover {
            background: #f8fafc;
        }

        .chat-item.active {
            background: #eef2ff;
            border-left: 4px solid var(--primary);
        }

        .chat-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: linear-gradient(135deg, #a855f7, #6366f1);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.2rem;
            flex-shrink: 0;
            box-shadow: 0 4px 10px rgba(99, 102, 241, 0.3);
            position: relative;
        }
        
        .chat-avatar img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }

        .chat-info {
            flex: 1;
            min-width: 0;
        }

        .chat-name {
            font-weight: 700;
            color: #1e293b;
            font-size: 1rem;
            margin-bottom: 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .chat-preview {
            font-size: 0.85rem;
            color: var(--text-muted);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .chat-preview strong {
            color: #1e293b;
            font-weight: 600;
        }

        .chat-meta {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 6px;
        }

        .chat-time {
            font-size: 0.75rem;
            color: var(--text-muted);
            font-weight: 500;
        }

        .chat-unread {
            background: #ef4444;
            color: white;
            font-size: 0.7rem;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 10px;
            min-width: 20px;
            text-align: center;
        }

        /* ----- PANEL DE CHAT ACTIVO (Right Panel) ----- */
        .chat-active-panel {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: var(--chat-bg);
            background-image: radial-gradient(circle at center, rgba(99, 102, 241, 0.03) 0%, transparent 70%);
            position: relative;
        }

        .chat-header {
            padding: 15px 30px;
            background: var(--panel-bg);
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.02);
            z-index: 2;
        }
        
        .chat-header .chat-avatar {
            width: 42px;
            height: 42px;
            font-size: 1.1rem;
            background: linear-gradient(135deg, #10b981, #3b82f6);
        }

        .chat-header-info {
            flex: 1;
        }

        .chat-header-info h2 {
            font-size: 1.1rem;
            font-weight: 700;
            color: #1e293b;
        }

        .chat-header-info span {
            font-size: 0.8rem;
            color: var(--text-muted);
        }

        .chat-actions {
            display: flex;
            gap: 15px;
        }

        .btn-icon {
            background: transparent;
            border: none;
            color: #64748b;
            cursor: pointer;
            padding: 8px;
            border-radius: 50%;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-icon:hover {
            background: #f1f5f9;
            color: var(--primary);
        }

        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 30px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .chat-messages::-webkit-scrollbar { width: 8px; }
        .chat-messages::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 10px; }

        .date-divider {
            text-align: center;
            margin: 10px 0;
            position: relative;
        }
        
        .date-divider span {
            background: #e2e8f0;
            color: #475569;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 12px;
        }

        .message {
            display: flex;
            flex-direction: column;
            max-width: 70%;
            position: relative;
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .message-bubble {
            padding: 12px 18px;
            border-radius: 18px;
            font-size: 0.95rem;
            line-height: 1.5;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            position: relative;
        }

        .message.received {
            align-self: flex-start;
        }

        .message.received .message-bubble {
            background: #ffffff;
            color: #334155;
            border: 1px solid #e2e8f0;
            border-bottom-left-radius: 4px;
        }

        .message.sent {
            align-self: flex-end;
        }

        .message.sent .message-bubble {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: #ffffff;
            border-bottom-right-radius: 4px;
        }

        .message-time {
            font-size: 0.7rem;
            margin-top: 5px;
            color: #94a3b8;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .message.sent .message-time {
            align-self: flex-end;
        }
        
        .message-status {
            color: #3b82f6;
        }
        
        .message-status.read {
            color: #10b981;
        }

        /* ----- ZONA DE INPUT ----- */
        .chat-input-area {
            padding: 20px 30px;
            background: var(--panel-bg);
            border-top: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow: 0 -4px 20px rgba(0,0,0,0.02);
            z-index: 2;
        }

        .input-wrapper {
            flex: 1;
            background: #f1f5f9;
            border-radius: 24px;
            display: flex;
            align-items: center;
            padding: 5px 15px;
            border: 1px solid transparent;
            transition: all 0.3s;
        }
        
        .input-wrapper:focus-within {
            background: #ffffff;
            border-color: #cbd5e1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .input-wrapper input {
            flex: 1;
            background: transparent;
            border: none;
            padding: 12px 10px;
            font-size: 1rem;
            color: #1e293b;
            outline: none;
        }
        
        .input-wrapper input::placeholder {
            color: #94a3b8;
        }

        .btn-send {
            background: linear-gradient(135deg, #4F46E5, #6366f1);
            color: white;
            border: none;
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 4px 15px rgba(79, 70, 229, 0.3);
        }

        .btn-send:hover {
            transform: translateY(-2px) scale(1.05);
            box-shadow: 0 6px 20px rgba(79, 70, 229, 0.4);
        }

        .btn-send svg {
            width: 20px;
            height: 20px;
            margin-left: 2px;
        }
        
        /* Empty state */
        .empty-chat {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #94a3b8;
            opacity: 0.8;
            padding: 0 20px;
            text-align: center;
        }
        
        .empty-chat svg {
            width: 80px;
            height: 80px;
            margin-bottom: 20px;
            color: #cbd5e1;
        }
        
        .empty-chat h3 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #475569;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

    <!-- Sidebar Global -->
    <?php require_once __DIR__ . '/components/sidebar.php'; ?>

    <main class="main-content">
        <!-- Lista de Chats -->
        <aside class="chat-list-panel">
            <div class="chat-list-header">
                <h2>
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
                    WhatsApp API
                </h2>
                <div class="search-box">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    <input type="text" placeholder="Buscar en chats...">
                </div>
            </div>
            
            <div class="chat-list">
                <!-- Chat 1 (Activo) -->
                <div class="chat-item active" onclick="activateChat(this)">
                    <div class="chat-avatar">C</div>
                    <div class="chat-info">
                        <div class="chat-name">Cliente Ejemplo 1</div>
                        <div class="chat-preview"><strong>Tú:</strong> ¡Hola! Tu pedido ya está en producción...</div>
                    </div>
                    <div class="chat-meta">
                        <div class="chat-time">10:45 AM</div>
                    </div>
                </div>

                <!-- Chat 2 (No leídos) -->
                <div class="chat-item" onclick="activateChat(this)">
                    <div class="chat-avatar" style="background: linear-gradient(135deg, #10b981, #3b82f6);">M</div>
                    <div class="chat-info">
                        <div class="chat-name">María González</div>
                        <div class="chat-preview" style="color:#1e293b; font-weight:600;">¿En qué estado se encuentra mi lona?</div>
                    </div>
                    <div class="chat-meta">
                        <div class="chat-time">09:30 AM</div>
                        <div class="chat-unread">2</div>
                    </div>
                </div>

                <!-- Chat 3 -->
                <div class="chat-item" onclick="activateChat(this)">
                    <div class="chat-avatar" style="background: linear-gradient(135deg, #f59e0b, #ef4444);">J</div>
                    <div class="chat-info">
                        <div class="chat-name">Juan Pérez (Empresa XYZ)</div>
                        <div class="chat-preview">Perfecto, pasaré mañana a recogerlo. ¡Gracias!</div>
                    </div>
                    <div class="chat-meta">
                        <div class="chat-time">Ayer</div>
                    </div>
                </div>
                
                <!-- Chat 4 -->
                <div class="chat-item" onclick="activateChat(this)">
                    <div class="chat-avatar" style="background: linear-gradient(135deg, #64748b, #475569);">A</div>
                    <div class="chat-info">
                        <div class="chat-name">Ana Martínez</div>
                        <div class="chat-preview"><strong>Tú:</strong> Adjunto la factura de su anticipo.</div>
                    </div>
                    <div class="chat-meta">
                        <div class="chat-time">Lun</div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Panel Activo -->
        <section class="chat-active-panel">
            
            <div class="chat-header">
                <div class="chat-avatar" id="activeAvatar">C</div>
                <div class="chat-header-info">
                    <h2 id="activeName">Cliente Ejemplo 1</h2>
                    <span id="activePhone">+57 300 000 0000</span>
                </div>
                <div class="chat-actions">
                    <button class="btn-icon" title="Buscar en la conversación">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    </button>
                    <!-- Menú 3 puntos Opciones -->
                    <div style="position:relative;" id="chatMenuWrapper">
                        <button class="btn-icon" title="Opciones" onclick="toggleChatMenu()">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="1"></circle><circle cx="12" cy="5" r="1"></circle><circle cx="12" cy="19" r="1"></circle></svg>
                        </button>
                        <div class="chat-dropdown-menu" id="chatDropdownMenu">
                            <?php if($isAdmin): ?>
                            <a href="#" onclick="openConfigModal(); return false;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                Configuración & Llave API
                            </a>
                            <?php else: ?>
                            <a href="#" onclick="alert('Consulta con un administrador para las configuraciones.'); return false;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                                Solo Administradores
                            </a>
                            <?php endif; ?>
                            <a href="#" onclick="return false;">Ver perfil de cliente</a>
                            <a href="#" onclick="return false;">Limpiar chat</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="chat-messages" id="chatMessages">
                <div class="date-divider"><span>Hoy</span></div>
                
                <div class="message received">
                    <div class="message-bubble">
                        ¡Hola! Quería saber el estado de mi pedido #12045. ¿Ya está listo?
                    </div>
                    <div class="message-time">10:42 AM</div>
                </div>

                <div class="message sent">
                    <div class="message-bubble">
                        ¡Hola! Tu pedido ya está en producción, deberíamos estar finalizándolo en la tarde. Te enviaremos una notificación cuando esté preparado para entrega.
                    </div>
                    <div class="message-time">
                        10:45 AM 
                        <svg class="message-status read" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    </div>
                </div>
            </div>

            <div class="chat-input-area">
                <button class="btn-icon" title="Adjuntar">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path></svg>
                </button>
                <div class="input-wrapper">
                    <button class="btn-icon" style="padding:4px; margin-right:5px;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M8 14s1.5 2 4 2 4-2 4-2"></path><line x1="9" y1="9" x2="9.01" y2="9"></line><line x1="15" y1="9" x2="15.01" y2="9"></line></svg>
                    </button>
                    <input type="text" id="msgInput" placeholder="Escribe un mensaje aquí..." onkeypress="handleEnter(event)">
                </div>
                <button class="btn-send" onclick="sendMessage()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                </button>
            </div>
            
        </section>
    </main>
    
    <?php if($isAdmin): ?>
    <!-- Modal de Configuración Onurix Webhook -->
    <div id="modal-config-wa" class="wa-modal">
        <div class="wa-backdrop" onclick="closeConfigModal()"></div>
        <div class="wa-modal-card">
            <button class="wa-close" onclick="closeConfigModal()">&times;</button>
            <div class="wa-modal-header">
                <div class="wa-icon-box">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                </div>
                <div>
                    <h3>Configuración de WhatsApp</h3>
                    <p>Integración y Webhooks de la API (Onurix)</p>
                </div>
            </div>
            
            <form id="formWaConfig" onsubmit="saveWaConfig(event)">
                <input type="hidden" name="csrf_token" id="csrf_token" value="<?= $csrfToken ?>">
                
                <div class="wa-form-group">
                    <label>URL de Destino (Webhook) *</label>
                    <div style="display:flex; align-items:center; gap:10px;">
                        <input type="text" class="wa-input" id="wa_url" readonly value="<?= $webhookUrl ?>" style="background:#f1f5f9; cursor:text;">
                        <button type="button" class="wa-btn wa-btn-icon" onclick="copyToClipboard('wa_url')" title="Copiar URL">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                        </button>
                    </div>
                    <small>Copia esta URL exacta en tu panel de Onurix para recibir los mensajes entrantes en el ERP.</small>
                </div>

                <div class="wa-form-group">
                    <label>Salt de Validación (Llave Secreta) *</label>
                    <div style="display:flex; align-items:center; gap:10px;">
                        <input type="text" id="wa_salt" name="salt" class="wa-input" readonly style="background:#f1f5f9; cursor:text;" placeholder="Presiona Generar para crear llave API" required minlength="16">
                        <button type="button" class="wa-btn wa-btn-icon" onclick="generateApiKey()" title="Generar Nueva Llave API" style="color:#4F46E5; border-color:#cbd5e1;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.2 15c.7-1.2 1-2.5.7-3.9-.6-2-2.4-3.5-4.4-3.5h-1.5c-2.8 0-5 2.2-5 5v5c0 2.8 2.2 5 5 5h1.5c2-0 3.8-1.5 4.4-3.5M16.5 14H22m-2.5-2.5L22 14l-2.5 2.5"/></svg>
                        </button>
                    </div>
                    <small>Clave secreta segura creada por ti (mínimo 16 caracteres). Escríbela en Onurix y aquí para validar que los datos son reales.</small>
                </div>

                <div class="wa-form-group">
                    <label>Modo de Operación</label>
                    <div class="wa-radio-group">
                        <label class="wa-radio-card">
                            <input type="radio" name="modo" value="demo" checked onchange="updateRadioSelection(this)">
                            <div class="wa-radio-content">
                                <strong>Modo Demo</strong>
                                <span>Ideal para pruebas.</span>
                            </div>
                        </label>
                        <label class="wa-radio-card">
                            <input type="radio" name="modo" value="produccion" onchange="updateRadioSelection(this)">
                            <div class="wa-radio-content">
                                <strong>Producción</strong>
                                <span>Mensajería en vivo.</span>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="wa-form-actions">
                    <button type="button" class="wa-btn wa-btn-ghost" onclick="closeConfigModal()">Cancelar</button>
                    <button type="submit" class="wa-btn wa-btn-primary" id="btnSaveConfig">
                        Guardar Configuración
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <style>
        /* Dropdown Menu CSS */
        .chat-dropdown-menu {
            position: absolute;
            top: 40px;
            right: 0;
            background: #fff;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            width: 260px;
            z-index: 100;
            display: flex;
            flex-direction: column;
            padding: 8px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .chat-dropdown-menu.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        .chat-dropdown-menu a {
            padding: 12px 16px;
            color: #475569;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 12px;
            border-radius: 8px;
            transition: background 0.2s;
        }
        .chat-dropdown-menu a:hover {
            background: #f1f5f9;
            color: #4F46E5;
        }

        /* Modal CSS */
        .wa-modal {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }
        .wa-modal.active {
            display: flex;
            animation: fadeIn 0.2s;
        }
        .wa-backdrop {
            position: absolute;
            inset: 0;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(4px);
        }
        .wa-modal-card {
            background: #fff;
            width: 600px;
            max-width: 95%;
            border-radius: 20px;
            z-index: 1;
            padding: 35px 40px;
            position: relative;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
            animation: slideUp 0.3s cubic-bezier(0.16,1,0.3,1);
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .wa-close {
            position: absolute;
            top: 20px;
            right: 25px;
            background: #f1f5f9;
            border: none;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            font-size: 1.5rem;
            color: #64748b;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .wa-close:hover {
            background: #e2e8f0;
            color: #0f172a;
        }
        .wa-modal-header {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
        }
        .wa-icon-box {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #10b981, #3b82f6);
            color: white;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 20px rgba(59, 130, 246, 0.3);
        }
        .wa-modal-header h3 {
            font-size: 1.5rem;
            color: #0f172a;
            margin-bottom: 5px;
        }
        .wa-modal-header p {
            color: #64748b;
            font-size: 0.95rem;
        }
        .wa-form-group {
            margin-bottom: 25px;
            text-align: left;
        }
        .wa-form-group label {
            display: block;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 10px;
            font-size: 0.95rem;
        }
        .wa-form-group small {
            display: block;
            margin-top: 8px;
            color: #64748b;
            font-size: 0.8rem;
            line-height: 1.5;
        }
        .wa-input {
            width: 100%;
            padding: 14px 18px;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            font-size: 1rem;
            outline: none;
            transition: all 0.2s;
            color: #1e293b;
            font-family: inherit;
        }
        .wa-input:focus {
            border-color: #4F46E5;
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        }
        .wa-btn {
            padding: 14px 24px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
        }
        .wa-btn-icon {
            padding: 14px;
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
        }
        .wa-btn-icon:hover {
            background: #e2e8f0;
            color: #0f172a;
        }
        .wa-btn-ghost {
            background: transparent;
            color: #64748b;
        }
        .wa-btn-ghost:hover {
            background: #f1f5f9;
            color: #0f172a;
        }
        .wa-btn-primary {
            background: linear-gradient(135deg, #4F46E5, #6366f1);
            color: white;
            box-shadow: 0 10px 20px -5px rgba(79, 70, 229, 0.4);
        }
        .wa-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 25px -5px rgba(79, 70, 229, 0.5);
        }
        .wa-radio-group {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        .wa-radio-card {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 18px;
            cursor: pointer;
            display: flex;
            align-items: flex-start;
            gap: 15px;
            transition: all 0.2s;
        }
        .wa-radio-card:hover {
            background: #f8fafc;
        }
        .wa-radio-card input {
            margin-top: 4px;
            width: 18px;
            height: 18px;
            accent-color: #4F46E5;
        }
        .wa-radio-content strong {
            display: block;
            color: #1e293b;
            font-size: 1rem;
            margin-bottom: 4px;
        }
        .wa-radio-content span {
            color: #64748b;
            font-size: 0.85rem;
            line-height: 1.4;
            display: block;
        }
        .wa-radio-card.active {
            border-color: #4F46E5;
            background: #eef2ff;
        }
        .wa-form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 35px;
            padding-top: 25px;
            border-top: 1px solid #e2e8f0;
        }
    </style>
    <?php endif; ?>

    <script>
        // Funcionalidad básica de la UI (Simulación)
        
        function activateChat(element) {
            // Remover clase active de todos
            document.querySelectorAll('.chat-item').forEach(el => el.classList.remove('active'));
            // Agregar al clickeado
            element.classList.add('active');
            
            // Actualizar header
            const name = element.querySelector('.chat-name').innerText;
            const avatarHtml = element.querySelector('.chat-avatar').innerHTML;
            const avatarBg = element.querySelector('.chat-avatar').style.background;
            
            document.getElementById('activeName').innerText = name;
            document.getElementById('activeAvatar').innerHTML = avatarHtml;
            if(avatarBg) document.getElementById('activeAvatar').style.background = avatarBg;
            else document.getElementById('activeAvatar').style.background = 'linear-gradient(135deg, #a855f7, #6366f1)';
            
            // Simular cambio de chat (limpiar mensajes)
            const chatContainer = document.getElementById('chatMessages');
            chatContainer.innerHTML = `
                <div class="date-divider"><span>Hoy</span></div>
                <div class="empty-chat" style="padding:40px;">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                    <h4 style="color:#64748b; font-weight:500;">Inicio de la conversación con ${name}</h4>
                </div>
            `;
        }

        function handleEnter(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        }

        function sendMessage() {
            const input = document.getElementById('msgInput');
            const msg = input.value.trim();
            if (!msg) return;

            const chatMessages = document.getElementById('chatMessages');
            
            // Remover empty state si existe
            const emptyState = chatMessages.querySelector('.empty-chat');
            if(emptyState) emptyState.remove();

            const time = new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
            
            const msgHtml = `
                <div class="message sent">
                    <div class="message-bubble">
                        ${escapeHtml(msg)}
                    </div>
                    <div class="message-time">
                        ${time} 
                        <svg class="message-status" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    </div>
                </div>
            `;
            
            chatMessages.insertAdjacentHTML('beforeend', msgHtml);
            input.value = '';
            
            // Scroll to bottom
            chatMessages.scrollTop = chatMessages.scrollHeight;
            
            // Actualizar preview en sidebar
            document.querySelector('.chat-item.active .chat-preview').innerHTML = `<strong>Tú:</strong> ${escapeHtml(msg)}`;
            document.querySelector('.chat-item.active .chat-time').innerText = time;
        }
        
        function escapeHtml(unsafe) {
            return unsafe
                 .replace(/&/g, "&amp;")
                 .replace(/</g, "&lt;")
                 .replace(/>/g, "&gt;")
                 .replace(/"/g, "&quot;")
                 .replace(/'/g, "&#039;");
        }
        
        // Scroll to bottom inicialmente
        window.onload = function() {
            const chatMessages = document.getElementById('chatMessages');
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
        function toggleChatMenu() {
            document.getElementById('chatDropdownMenu').classList.toggle('show');
        }

        // Click outside to close standard dropdowns
        document.addEventListener('click', function(e) {
            const menu = document.getElementById('chatDropdownMenu');
            if(menu && !menu.contains(e.target) && !document.getElementById('chatMenuWrapper').contains(e.target)) {
                menu.classList.remove('show');
            }
        });

        // ================= CONFIGURACION MODAL VÍA AJAX =================
        function openConfigModal() {
            // Cargar estado inicial desde BD
            fetch('<?= htmlspecialchars($sidebarBasePath) ?>/api/whatsapp/config')
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    if(!data.salt) {
                        generateApiKey();
                    } else {
                        document.getElementById('wa_salt').value = data.salt;
                    }
                    const rb = document.querySelector(`input[name="modo"][value="${data.modo}"]`);
                    if(rb) rb.checked = true;
                    // Update UI cards
                    document.querySelectorAll('.wa-radio-card').forEach(rc => {
                        const cb = rc.querySelector('input');
                        if(cb.checked) rc.classList.add('active');
                        else rc.classList.remove('active');
                    });
                }
                document.getElementById('modal-config-wa').classList.add('active');
                document.getElementById('chatDropdownMenu').classList.remove('show');
            }).catch(e => {
                alert("Error cargando configuración. Por favor recarga la página.");
                console.error(e);
            });
        }

        function closeConfigModal() {
            document.getElementById('modal-config-wa').classList.remove('active');
        }

        function generateApiKey() {
            if(document.getElementById('wa_salt').value) {
                if(!confirm("Si cambias la llave API actual se desconectará de Onurix hasta que pegues la nueva en su panel. ¿Estás seguro?")) {
                    return;
                }
            }
            const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            let key = 'bnnr_wa_'; // prefix
            for (let i = 0; i < 24; i++) {
                key += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            document.getElementById('wa_salt').value = key;
        }

        function updateRadioSelection(input) {
            document.querySelectorAll('.wa-radio-card').forEach(rc => rc.classList.remove('active'));
            if(input.checked) {
                input.closest('.wa-radio-card').classList.add('active');
            }
        }

        function copyToClipboard(id) {
            const el = document.getElementById(id);
            el.select();
            el.setSelectionRange(0, 99999); 
            navigator.clipboard.writeText(el.value).then(() => {
                alert("URL copiada al portapapeles.");
            });
        }

        function saveWaConfig(e) {
            e.preventDefault();
            const btn = document.getElementById('btnSaveConfig');
            btn.innerHTML = 'Guardando...';
            btn.disabled = true;

            const formData = new FormData(document.getElementById('formWaConfig'));
            
            fetch('<?= htmlspecialchars($sidebarBasePath) ?>/api/whatsapp/config', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                btn.innerHTML = 'Guardar Configuración';
                btn.disabled = false;
                
                if(data.status === 'success') {
                    alert('¡Configuración guardada exitosamente!');
                    closeConfigModal();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                btn.innerHTML = 'Guardar Configuración';
                btn.disabled = false;
                alert('No se pudo guardar. Revisa tu conexión.');
                console.error(error);
            });
        }
        
    </script>
</body>
</html>
