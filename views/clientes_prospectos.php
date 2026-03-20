<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) { header('Location: ./login'); exit; }

$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
if ($basePath === '/' || $basePath === '\\') $basePath = '';

$userName = $_SESSION['email'] ?? 'Admin';
$role = $_SESSION['role'] ?? 'Admin';

require_once __DIR__ . '/../config/Database.php';
$db = \Config\Database::getInstance();

// Obtener todos los clientes agrupados por nombre y telefono
$stmt = $db->query("
    SELECT cliente_nombre AS nombre, 
           cliente_telefono AS telefono, 
           COUNT(id) AS compras,
           MAX(created_at) AS ultima_compra,
           SUM(total) as monto_total
    FROM pedidos
    WHERE cliente_nombre IS NOT NULL AND cliente_nombre != '' AND deleted_at IS NULL
    GROUP BY cliente_nombre, cliente_telefono
    ORDER BY compras DESC
");
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$frecuentes = [];
$muy_frecuentes = [];
$fijos = [];

foreach ($clientes as $cliente) {
    if ($cliente['compras'] >= 10) {
        $fijos[] = $cliente;
    } elseif ($cliente['compras'] >= 5) {
        $muy_frecuentes[] = $cliente;
    } elseif ($cliente['compras'] >= 2) {
        $frecuentes[] = $cliente;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes Prospectos</title>
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
        @media (max-width: 768px) {
            .content-area { padding: 16px; }
        }

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
        .stat-card.frecuente .stat-value { color:#3b82f6; }
        .stat-card.muy_frecuente .stat-value { color:#f59e0b; }
        .stat-card.fijo .stat-value { color:#10b981; }

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
        .col-frecuente .kanban-column-header { border-top:3px solid #3b82f6; border-radius:16px 16px 0 0; }
        .col-frecuente .count { color:#3b82f6; }
        .col-muy_frecuente .kanban-column-header { border-top:3px solid #f59e0b; border-radius:16px 16px 0 0; }
        .col-muy_frecuente .count { color:#f59e0b; }
        .col-fijo .kanban-column-header { border-top:3px solid #10b981; border-radius:16px 16px 0 0; }
        .col-fijo .count { color:#10b981; }

        .kanban-column-body { padding:12px; overflow-y:auto; flex:1; display:flex; flex-direction:column; gap:10px; }

        /* Kanban Card */
        .kanban-card {
            background:rgba(0,0,0,0.2); border:1px solid rgba(255,255,255,0.06); border-radius:12px;
            padding:14px 16px; cursor:default; transition:all 0.2s;
        }
        .kanban-card:hover { background:rgba(255,255,255,0.04); border-color:rgba(255,255,255,0.12); }
        .kanban-card .card-client { font-size:1rem; font-weight:700; margin-bottom:4px; color:#f8fafc; }
        .kanban-card .card-info { font-size:0.8rem; color:var(--muted); line-height:1.4; margin-bottom:4px; display:flex; gap:6px; align-items:center;}
        .kanban-card .card-footer { display:flex; justify-content:space-between; align-items:center; margin-top:8px;}
        .kanban-card .card-badge {
            font-size:0.7rem; padding:3px 10px; border-radius:20px; font-weight:700;
        }
        .badge-compras { background:rgba(99,102,241,0.15); color:#818cf8; }
        .badge-monto { background:rgba(16,185,129,0.15); color:#34d399; }

        .kanban-empty { text-align:center; padding:30px 15px; color:var(--muted); font-size:0.85rem; }
        .kanban-empty i { font-size:2rem; margin-bottom:8px; display:block; opacity:0.5; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/components/sidebar.php'; ?>
    <div class="main-content">
        <header class="topbar">
            <div style="display:flex; align-items:center; gap:16px;">
                <button id="btnHamburger" style="background:none; border:none; color:var(--text); cursor:pointer; font-size:1.5rem; display:flex;">☰</button>
                <div class="breadcrumb">Reportes / <span>Clientes Prospectos</span></div>
            </div>
        </header>

        <div class="content-area">
            <div class="page-title">
                <h1>Clientes Prospectos</h1>
                <p>Mide y monitorea a tus clientes basado en las veces que han comprado.</p>
            </div>

            <div class="stats-bar">
                <div class="stat-card" style="border-left: 4px solid var(--primary);">
                    <div class="stat-label"><i class="fas fa-address-book" style="margin-right:4px; color:var(--primary);"></i> Total Clientes</div>
                    <div class="stat-value" style="color:var(--text);"><?= count($clientes) ?></div>
                </div>
                <div class="stat-card frecuente">
                    <div class="stat-label"><i class="fas fa-users" style="margin-right:4px;"></i> C. Frecuente</div>
                    <div class="stat-value"><?= count($frecuentes) ?></div>
                </div>
                <div class="stat-card muy_frecuente">
                    <div class="stat-label"><i class="fas fa-star-half-alt" style="margin-right:4px;"></i> C. Muy Frecuente</div>
                    <div class="stat-value"><?= count($muy_frecuentes) ?></div>
                </div>
                <div class="stat-card fijo">
                    <div class="stat-label"><i class="fas fa-star" style="margin-right:4px;"></i> C. Fijo</div>
                    <div class="stat-value"><?= count($fijos) ?></div>
                </div>
            </div>

            <div class="kanban-board">
                <!-- Frecuente -->
                <div class="kanban-column col-frecuente">
                    <div class="kanban-column-header">
                        <h3><i class="fas fa-users" style="color:#3b82f6;"></i> Cliente Frecuente (2-4 compras)</h3>
                        <span class="count"><?= count($frecuentes) ?></span>
                    </div>
                    <div class="kanban-column-body">
                        <?php if (empty($frecuentes)): ?>
                            <div class="kanban-empty"><i class="fas fa-search"></i> Sin clientes</div>
                        <?php else: foreach ($frecuentes as $c): ?>
                            <?php $kcat = 'frecuente'; include __DIR__ . '/_kanban_card_cliente.php'; ?>
                        <?php endforeach; endif; ?>
                    </div>
                </div>

                <!-- Muy Frecuente -->
                <div class="kanban-column col-muy_frecuente">
                    <div class="kanban-column-header">
                        <h3><i class="fas fa-star-half-alt" style="color:#f59e0b;"></i> C. Muy Frecuente (5-9 compras)</h3>
                        <span class="count"><?= count($muy_frecuentes) ?></span>
                    </div>
                    <div class="kanban-column-body">
                        <?php if (empty($muy_frecuentes)): ?>
                            <div class="kanban-empty"><i class="fas fa-search"></i> Sin clientes</div>
                        <?php else: foreach ($muy_frecuentes as $c): ?>
                            <?php $kcat = 'muy_frecuente'; include __DIR__ . '/_kanban_card_cliente.php'; ?>
                        <?php endforeach; endif; ?>
                    </div>
                </div>

                <!-- Fijo -->
                <div class="kanban-column col-fijo">
                    <div class="kanban-column-header">
                        <h3><i class="fas fa-star" style="color:#10b981;"></i> Cliente Fijo (10+ compras)</h3>
                        <span class="count"><?= count($fijos) ?></span>
                    </div>
                    <div class="kanban-column-body">
                        <?php if (empty($fijos)): ?>
                            <div class="kanban-empty"><i class="fas fa-search"></i> Sin clientes</div>
                        <?php else: foreach ($fijos as $c): ?>
                            <?php $kcat = 'fijo'; include __DIR__ . '/_kanban_card_cliente.php'; ?>
                        <?php endforeach; endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Historial de Pedidos -->
    <div id="modalHistorial" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.7); z-index:1000; align-items:center; justify-content:center; backdrop-filter:blur(4px);">
        <div style="background:var(--bg); border:1px solid var(--border); width:90%; max-width:650px; border-radius:16px; overflow:hidden; box-shadow:0 25px 50px -12px rgba(0,0,0,0.5); display:flex; flex-direction:column; max-height:85vh;">
            <div style="padding:20px; border-bottom:1px solid var(--border); display:flex; justify-content:space-between; align-items:center; background:rgba(255,255,255,0.02);">
                <h2 id="modalTitle" style="font-size:1.2rem; font-weight:800; color:#fff;">Historial del Cliente</h2>
                <button onclick="closeModal()" style="background:none; border:none; color:var(--muted); cursor:pointer; font-size:1.5rem;">&times;</button>
            </div>
            <div id="modalBody" style="padding:20px; overflow-y:auto; flex:1;">
                <!-- Pedidos inyectados -->
            </div>
        </div>
    </div>

    <script>
        const basePath = '<?= $basePath ?>';

        function openHistorial(name, phone) {
            const modal = document.getElementById('modalHistorial');
            const title = document.getElementById('modalTitle');
            const body = document.getElementById('modalBody');

            title.innerText = `Historial: ${name}`;
            body.innerHTML = '<div style="text-align:center; padding:40px;"><i class="fas fa-spinner fa-spin" style="font-size:2rem; color:var(--primary);"></i></div>';
            modal.style.display = 'flex';

            fetch(`${basePath}/api/clientes/pedidos?telefono=${encodeURIComponent(phone)}&nombre=${encodeURIComponent(name)}`)
                .then(r => r.json())
                .then(res => {
                    if(res.status === 'success') {
                        if(res.data.length === 0) {
                            body.innerHTML = '<div style="text-align:center; color:var(--muted); padding:40px;">No se encontraron pedidos recientes.</div>';
                            return;
                        }
                        let html = '<div style="display:flex; flex-direction:column; gap:12px;">';
                        res.data.forEach((o, index) => {
                            const date = new Date(o.created_at).toLocaleDateString('es-ES', { day:'2-digit', month:'short', year:'numeric' });
                            html += `
                                <div style="background:rgba(255,255,255,0.03); border:1px solid var(--border); border-radius:12px; padding:14px; position:relative; display:flex; gap:12px;">
                                    <div style="background:var(--primary); color:#fff; width:24px; height:24px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:0.75rem; font-weight:800; flex-shrink:0;">${index + 1}</div>
                                    <div style="flex:1;">
                                        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:8px;">
                                            <div style="font-weight:800; color:var(--primary);">#${o.id}</div>
                                            <div style="font-size:0.75rem; color:var(--muted); font-weight:600;">${date}</div>
                                        </div>
                                        <div style="font-size:0.9rem; font-weight:600; color:#fff; margin-bottom:6px;">${o.descripcion || '(Sin descripción)'}</div>
                                        <div style="display:flex; justify-content:space-between; align-items:center; margin-top:10px;">
                                            <div style="font-size:1.1rem; font-weight:900; color:#34d399;">$${parseInt(o.total || 0).toLocaleString()}</div>
                                            <div style="font-size:0.65rem; text-transform:uppercase; font-weight:800; padding:4px 10px; border-radius:20px; background:rgba(255,255,255,0.05); color:var(--muted);">${o.estado || '---'}</div>
                                        </div>
                                    </div>
                                </div>
                            `;
                        });
                        html += '</div>';
                        body.innerHTML = html;
                    } else {
                        body.innerHTML = '<div style="text-align:center; color:#f87171; padding:20px;">' + (res.message || 'Error al cargar historial') + '</div>';
                    }
                })
                .catch(err => {
                    body.innerHTML = '<div style="text-align:center; color:#f87171; padding:20px;">Error de red o conexión.</div>';
                });
        }

        function closeModal() {
            document.getElementById('modalHistorial').style.display = 'none';
        }

        // Cerrar modal al hacer click fuera
        window.onclick = function(event) {
            const modal = document.getElementById('modalHistorial');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>
