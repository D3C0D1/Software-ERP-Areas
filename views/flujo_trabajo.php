<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) { header('Location: ./login'); exit; }

$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
if ($basePath === '/' || $basePath === '\\') $basePath = '';

$userName = $_SESSION['email'] ?? 'Admin';
$role = $_SESSION['role'] ?? 'Admin';

require_once __DIR__ . '/../config/Database.php';
$db = \Config\Database::getInstance();

// Pedidos para hoy
$hoy = $db->query("SELECT p.*, COALESCE(a.nombre, 'Sin Área') AS area_nombre FROM pedidos p LEFT JOIN areas a ON p.area_actual_id = a.id WHERE p.estado NOT IN ('cancelado','completado') AND p.fecha_entrega_esperada = CURDATE() ORDER BY p.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

// Pedidos vencidos (1-3 días)
$vencidos = $db->query("SELECT p.*, COALESCE(a.nombre, 'Sin Área') AS area_nombre FROM pedidos p LEFT JOIN areas a ON p.area_actual_id = a.id WHERE p.estado NOT IN ('cancelado','completado') AND p.fecha_entrega_esperada IS NOT NULL AND p.fecha_entrega_esperada < CURDATE() AND DATEDIFF(CURDATE(), p.fecha_entrega_esperada) BETWEEN 1 AND 3 ORDER BY p.fecha_entrega_esperada ASC")->fetchAll(PDO::FETCH_ASSOC);

// Pedidos muy vencidos (>3 días)
$muyVencidos = $db->query("SELECT p.*, COALESCE(a.nombre, 'Sin Área') AS area_nombre FROM pedidos p LEFT JOIN areas a ON p.area_actual_id = a.id WHERE p.estado NOT IN ('cancelado','completado') AND p.fecha_entrega_esperada IS NOT NULL AND DATEDIFF(CURDATE(), p.fecha_entrega_esperada) > 3 ORDER BY p.fecha_entrega_esperada ASC")->fetchAll(PDO::FETCH_ASSOC);

// Pedidos sin fecha
$sinFecha = $db->query("SELECT p.*, COALESCE(a.nombre, 'Sin Área') AS area_nombre FROM pedidos p LEFT JOIN areas a ON p.area_actual_id = a.id WHERE p.estado NOT IN ('cancelado','completado') AND (p.fecha_entrega_esperada IS NULL) ORDER BY p.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

// Configuracion notificaciones
$cfgRows = $db->query("SELECT clave, valor FROM configuracion WHERE clave IN ('telefono_jefe', 'telefono_supervisor')")->fetchAll(PDO::FETCH_KEY_PAIR);
$telJefe = $cfgRows['telefono_jefe'] ?? '';
$telSupervisor = $cfgRows['telefono_supervisor'] ?? '';
$csrfToken = \App\Middlewares\CsrfMiddleware::generateToken();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flujo de Trabajo - Gestión de Pedidos</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg: #0f172a;
            --surface: rgba(30, 41, 59, 0.75);
            --border: rgba(255, 255, 255, 0.08);
            --primary: #6366f1;
            --text: #f1f5f9;
            --muted: #94a3b8;
        }
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Inter',sans-serif; }
        body { background: var(--bg); color: var(--text); min-height:100vh; display:flex; }
        .main-content { flex:1; display:flex; flex-direction:column; min-height:100vh; }

        .topbar {
            height:64px; padding:0 32px; display:flex; align-items:center; justify-content:space-between;
            background:rgba(15,23,42,0.6); backdrop-filter:blur(10px);
            border-bottom:1px solid var(--border); position:sticky; top:0; z-index:100;
        }
        .topbar .breadcrumb { font-size:0.95rem; color:var(--muted); font-weight:500; }
        .topbar .breadcrumb span { color:var(--text); font-weight:600; }

        .content-area { flex:1; padding:28px 32px; overflow-y:auto; }

        .page-title h1 {
            font-size:1.5rem; font-weight:800; margin-bottom:4px;
            background:linear-gradient(135deg,#f1f5f9,#a5b4fc); -webkit-background-clip:text; -webkit-text-fill-color:transparent;
        }
        .page-title p { color:var(--muted); font-size:0.88rem; margin-bottom:24px; }

        /* Stats Bar */
        .stats-bar { display:flex; gap:14px; margin-bottom:28px; flex-wrap:wrap; }
        .stat-card {
            background:var(--surface); border:1px solid var(--border); border-radius:14px;
            padding:18px 22px; flex:1; min-width:180px;
        }
        .stat-card .stat-label { font-size:0.78rem; color:var(--muted); text-transform:uppercase; letter-spacing:0.05em; font-weight:600; }
        .stat-card .stat-value { font-size:1.8rem; font-weight:800; margin-top:4px; }
        .stat-card.today .stat-value { color:#3b82f6; }
        .stat-card.overdue .stat-value { color:#f59e0b; }
        .stat-card.critical .stat-value { color:#ef4444; }
        .stat-card.nofecha .stat-value { color:#94a3b8; }

        /* Kanban Board */
        .kanban-board { display:grid; grid-template-columns:repeat(auto-fit, minmax(300px, 1fr)); gap:20px; }
        @media (max-width: 768px) {
            .kanban-board {
                grid-template-columns: 1fr;
                gap: 16px;
            }
        }
        .kanban-column {
            background:var(--surface); border:1px solid var(--border); border-radius:16px;
            display:flex; flex-direction:column; max-height:75vh;
        }
        @media (max-width: 768px) {
            .kanban-column { max-height: none; }
        }
        .kanban-column-header {
            padding:16px 20px; border-bottom:1px solid var(--border);
            display:flex; align-items:center; justify-content:space-between; flex-shrink:0;
        }
        .kanban-column-header h3 { font-size:0.95rem; font-weight:700; display:flex; align-items:center; gap:8px; }
        .kanban-column-header .count {
            background:rgba(255,255,255,0.1); padding:2px 10px; border-radius:20px;
            font-size:0.75rem; font-weight:700;
        }
        .col-today .kanban-column-header { border-top:3px solid #3b82f6; border-radius:16px 16px 0 0; }
        .col-today .count { color:#3b82f6; }
        .col-overdue .kanban-column-header { border-top:3px solid #f59e0b; border-radius:16px 16px 0 0; }
        .col-overdue .count { color:#f59e0b; }
        .col-critical .kanban-column-header { border-top:3px solid #ef4444; border-radius:16px 16px 0 0; }
        .col-critical .count { color:#ef4444; }
        .col-nofecha .kanban-column-header { border-top:3px solid #94a3b8; border-radius:16px 16px 0 0; }
        .col-nofecha .count { color:#94a3b8; }

        .kanban-column-body { padding:12px; overflow-y:auto; flex:1; display:flex; flex-direction:column; gap:10px; }

        /* Kanban Card */
        .kanban-card {
            background:rgba(0,0,0,0.2); border:1px solid rgba(255,255,255,0.06); border-radius:12px;
            padding:14px 16px; cursor:default; transition:all 0.2s;
        }
        .kanban-card:hover { background:rgba(255,255,255,0.04); border-color:rgba(255,255,255,0.12); }
        .kanban-card .card-top { display:flex; justify-content:space-between; align-items:center; margin-bottom:8px; }
        .kanban-card .card-id { font-size:0.75rem; color:var(--primary); font-weight:700; }
        .kanban-card .card-area {
            font-size:0.68rem; padding:2px 8px; border-radius:20px;
            background:rgba(99,102,241,0.12); color:#a5b4fc; font-weight:600;
        }
        .kanban-card .card-client { font-size:0.9rem; font-weight:600; margin-bottom:4px; }
        .kanban-card .card-desc { font-size:0.8rem; color:var(--muted); line-height:1.4; margin-bottom:8px;
            display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;
        }
        .kanban-card .card-footer { display:flex; justify-content:space-between; align-items:center; }
        .kanban-card .card-date { font-size:0.73rem; color:var(--muted); }
        .kanban-card .card-pago {
            font-size:0.68rem; padding:2px 8px; border-radius:20px; font-weight:700;
        }
        .pago-completo { background:rgba(16,185,129,0.15); color:#10b981; }
        .pago-abono { background:rgba(245,158,11,0.15); color:#f59e0b; }
        .pago-nopago { background:rgba(239,68,68,0.15); color:#ef4444; }

        .btn-notify {
            background: rgba(245,158,11,0.15); border: 1px solid rgba(245,158,11,0.3);
            color: #f59e0b; border-radius: 50%; width: 26px; height: 26px;
            display: inline-flex; align-items: center; justify-content: center;
            cursor: pointer; transition: all 0.2s; font-size: 0.75rem;
        }
        .btn-notify:hover { background: rgba(245,158,11,0.3); transform: scale(1.1); }

        .kanban-empty { text-align:center; padding:30px 15px; color:var(--muted); font-size:0.85rem; }
        .kanban-empty i { font-size:2rem; margin-bottom:8px; display:block; opacity:0.5; }

        /* Modal Styles */
        .modal-overlay {
            position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.6); backdrop-filter: blur(4px);
            display: none; justify-content: center; align-items: center; z-index: 1000;
        }
        .modal-content {
            background: var(--surface); border: 1px solid var(--border); border-radius: 16px;
            width: 90%; max-width: 450px; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);
            animation: modalFadeIn 0.3s ease-out;
        }
        @keyframes modalFadeIn { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }
        .modal-header {
            padding: 20px 24px; border-bottom: 1px solid var(--border);
            display: flex; justify-content: space-between; align-items: center;
        }
        .modal-header h2 { font-size: 1.1rem; font-weight: 700; margin: 0; color: #f1f5f9; display: flex; align-items: center; gap: 8px;}
        .close-modal { color: var(--muted); cursor: pointer; font-size: 1.5rem; line-height: 1; transition: color 0.2s; }
        .close-modal:hover { color: #f1f5f9; }
        .modal-body { padding: 24px; }
        .form-group { margin-bottom: 16px; }
        .form-label { display: block; font-size: 0.85rem; font-weight: 600; color: var(--muted); margin-bottom: 8px; }
        .form-select, .form-textarea {
            width: 100%; background: rgba(0,0,0,0.2); border: 1px solid var(--border);
            color: var(--text); padding: 10px 14px; border-radius: 8px; font-size: 0.95rem; outline: none; transition: border-color 0.2s;
        }
        .form-select:focus, .form-textarea:focus { border-color: var(--primary); }
        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            padding: 10px 20px; font-size: 0.9rem; font-weight: 600; border-radius: 8px;
            border: none; cursor: pointer; transition: all 0.2s;
        }
        .btn-primary { background: var(--primary); color: white; }
        .btn-primary:hover { background: #4f46e5; }
        .btn-secondary { background: rgba(255,255,255,0.1); color: var(--text); }
        .btn-secondary:hover { background: rgba(255,255,255,0.15); }
    </style>
</head>
<body>
    <?php include __DIR__ . '/components/sidebar.php'; ?>
    <div class="main-content">
        <header class="topbar">
            <div style="display:flex; align-items:center; gap:16px;">
                <button id="btnHamburger" style="background:none; border:none; color:var(--text); cursor:pointer; font-size:1.5rem; display:flex;">☰</button>
                <div class="breadcrumb">Flujo de Trabajo / <span>Gestión de Pedidos Activos</span></div>
            </div>
        </header>

        <div class="content-area">
            <div class="page-title">
                <h1>Gestión de Pedidos Activos</h1>
                <p>Visualiza el estado de tus pedidos organizados por urgencia y fechas de entrega.</p>
            </div>

            <div class="stats-bar">
                <div class="stat-card today">
                    <div class="stat-label"><i class="fas fa-calendar-day" style="margin-right:4px;"></i> Para Hoy</div>
                    <div class="stat-value"><?= count($hoy) ?></div>
                </div>
                <div class="stat-card overdue">
                    <div class="stat-label"><i class="fas fa-exclamation-triangle" style="margin-right:4px;"></i> Vencidos</div>
                    <div class="stat-value"><?= count($vencidos) ?></div>
                </div>
                <div class="stat-card critical">
                    <div class="stat-label"><i class="fas fa-fire" style="margin-right:4px;"></i> Muy Vencidos</div>
                    <div class="stat-value"><?= count($muyVencidos) ?></div>
                </div>
                <div class="stat-card nofecha">
                    <div class="stat-label"><i class="fas fa-question-circle" style="margin-right:4px;"></i> Sin Fecha</div>
                    <div class="stat-value"><?= count($sinFecha) ?></div>
                </div>
            </div>

            <div class="kanban-board">
                <!-- Hoy -->
                <div class="kanban-column col-today">
                    <div class="kanban-column-header">
                        <h3><i class="fas fa-calendar-day" style="color:#3b82f6;"></i> Para Hoy</h3>
                        <span class="count"><?= count($hoy) ?></span>
                    </div>
                    <div class="kanban-column-body">
                        <?php if (empty($hoy)): ?>
                            <div class="kanban-empty"><i class="fas fa-check-circle"></i> Sin pedidos para hoy</div>
                        <?php else: foreach ($hoy as $p): ?>
                            <?php $kanbanCat = 'hoy'; include __DIR__ . '/../views/_kanban_card.php'; ?>
                        <?php endforeach; endif; ?>
                    </div>
                </div>

                <!-- Vencidos -->
                <div class="kanban-column col-overdue">
                    <div class="kanban-column-header">
                        <h3><i class="fas fa-exclamation-triangle" style="color:#f59e0b;"></i> Vencidos (1-3 días)</h3>
                        <span class="count"><?= count($vencidos) ?></span>
                    </div>
                    <div class="kanban-column-body">
                        <?php if (empty($vencidos)): ?>
                            <div class="kanban-empty"><i class="fas fa-check-circle"></i> Sin pedidos vencidos</div>
                        <?php else: foreach ($vencidos as $p): ?>
                            <?php $kanbanCat = 'vencido'; include __DIR__ . '/../views/_kanban_card.php'; ?>
                        <?php endforeach; endif; ?>
                    </div>
                </div>

                <!-- Muy Vencidos -->
                <div class="kanban-column col-critical">
                    <div class="kanban-column-header">
                        <h3><i class="fas fa-fire" style="color:#ef4444;"></i> Muy Vencidos (+3 días)</h3>
                        <span class="count"><?= count($muyVencidos) ?></span>
                    </div>
                    <div class="kanban-column-body">
                        <?php if (empty($muyVencidos)): ?>
                            <div class="kanban-empty"><i class="fas fa-check-circle"></i> Sin pedidos críticos</div>
                        <?php else: foreach ($muyVencidos as $p): ?>
                            <?php $kanbanCat = 'muy_vencido'; include __DIR__ . '/../views/_kanban_card.php'; ?>
                        <?php endforeach; endif; ?>
                    </div>
                </div>

                <!-- Sin Fecha -->
                <div class="kanban-column col-nofecha">
                    <div class="kanban-column-header">
                        <h3><i class="fas fa-question-circle" style="color:#94a3b8;"></i> Sin Fecha Asignada</h3>
                        <span class="count"><?= count($sinFecha) ?></span>
                    </div>
                    <div class="kanban-column-body">
                        <?php if (empty($sinFecha)): ?>
                            <div class="kanban-empty"><i class="fas fa-check-circle"></i> Todos tienen fecha</div>
                        <?php else: foreach ($sinFecha as $p): ?>
                            <?php $kanbanCat = 'sin_fecha'; include __DIR__ . '/../views/_kanban_card.php'; ?>
                        <?php endforeach; endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Notificar a Superior -->
    <div id="modalNotificar" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-bell" style="color:#f59e0b;"></i> Notificar a Superior</h2>
                <span class="close-modal" onclick="cerrarModalNotificar()">&times;</span>
            </div>
            <div class="modal-body">
                <form id="formNotificar" onsubmit="enviarNotificacion(event)">
                    <div class="form-group">
                        <label class="form-label">Destinatario</label>
                        <select id="notificarDestinatario" class="form-select" required>
                            <option value="">Selecciona un destinatario...</option>
                            <?php if(!empty($telJefe)): ?><option value="<?= htmlspecialchars($telJefe) ?>">Jefe (<?= htmlspecialchars($telJefe) ?>)</option><?php endif; ?>
                            <?php if(!empty($telSupervisor)): ?><option value="<?= htmlspecialchars($telSupervisor) ?>">Supervisor (<?= htmlspecialchars($telSupervisor) ?>)</option><?php endif; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Mensaje</label>
                        <textarea id="notificarMensaje" class="form-textarea" rows="4" required></textarea>
                    </div>
                    <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;">
                        <button type="button" class="btn btn-secondary" onclick="cerrarModalNotificar()">Cancelar</button>
                        <button type="submit" class="btn btn-primary" id="btnEnviarNotificacion"><i class="fas fa-paper-plane"></i> Enviar Alerta</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const basePath = '<?= $basePath ?>';

        function abrirModalNotificar(cliente, categoria) {
            let mensaje = '';
            if (categoria === 'hoy') {
                mensaje = `El pedido del cliente ${cliente} es para hoy y requiere atención.`;
            } else if (categoria === 'vencido' || categoria === 'muy_vencido') {
                mensaje = `El pedido del cliente ${cliente} está VENCIDO (se necesitaba para ayer o antes) y requiere atención urgente.`;
            } else {
                mensaje = `Aviso sobre el pedido del cliente ${cliente}.`;
            }

            document.getElementById('notificarMensaje').value = mensaje;
            document.getElementById('notificarDestinatario').value = '';
            document.getElementById('modalNotificar').style.display = 'flex';
        }

        function cerrarModalNotificar() {
            document.getElementById('modalNotificar').style.display = 'none';
        }

        async function enviarNotificacion(e) {
            e.preventDefault();
            const destinatario = document.getElementById('notificarDestinatario').value;
            const mensaje = document.getElementById('notificarMensaje').value;

            if (!destinatario) {
                alert('Selecciona un destinatario válido.');
                return;
            }

            const btn = document.getElementById('btnEnviarNotificacion');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';

            try {
                const res = await fetch(basePath + '/api/sms/enviar-manual', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ numero: destinatario, texto: mensaje })
                });
                const data = await res.json();
                
                if (data.status === 'success') {
                    alert('Notificación enviada exitosamente.');
                    cerrarModalNotificar();
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (err) {
                console.error(err);
                alert('Ocurrió un error de red al intentar enviar el mensaje.');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-paper-plane"></i> Enviar Alerta';
            }
        }
    </script>
</body>
</html>
