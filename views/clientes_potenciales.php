<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) { header('Location: ./login'); exit; }

$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
if ($basePath === '/' || $basePath === '\\') $basePath = '';

$userName = $_SESSION['email'] ?? 'Admin';
$role = $_SESSION['role'] ?? 'Admin';

require_once __DIR__ . '/../config/Database.php';
$db = \Config\Database::getInstance();

// Obtener clientes categorizados por su pedido MÁXIMO (su capacidad individual)
$stmt = $db->query("
    SELECT cliente_nombre AS nombre, 
           cliente_telefono AS telefono, 
           COUNT(id) AS compras,
           MAX(total) AS pedido_maximo,
           MAX(created_at) AS ultima_compra,
           SUM(total) as monto_total
    FROM pedidos
    WHERE cliente_nombre IS NOT NULL AND cliente_nombre != '' AND deleted_at IS NULL
    GROUP BY cliente_nombre, cliente_telefono
    HAVING MAX(total) >= 800000
    ORDER BY pedido_maximo DESC
");
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$potenciales = [];    // 800k - 1.2M
$muy_potentes = [];   // 1.2M - 2M
$socios = [];         // 2M+

foreach ($clientes as $cliente) {
    $max = (float)$cliente['pedido_maximo'];
    if ($max >= 2000000) {
        $socios[] = $cliente;
    } elseif ($max >= 1200000) {
        $muy_potentes[] = $cliente;
    } elseif ($max >= 800000) {
        $potenciales[] = $cliente;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes Potenciales | Alto Valor</title>
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
        @media (max-width: 768px) { .content-area { padding: 16px; } }

        .page-title h1 {
            font-size:1.5rem; font-weight:800; margin-bottom:4px;
            background:linear-gradient(135deg,#f1f5f9,#fcd34d); -webkit-background-clip:text; -webkit-text-fill-color:transparent;
        }
        .page-title p { color:var(--muted); font-size:0.88rem; margin-bottom:24px; }

        .stats-bar { display:flex; gap:14px; margin-bottom:28px; flex-wrap:wrap; }
        .stat-card {
            background:var(--surface); border:1px solid var(--border); border-radius:14px;
            padding:18px 22px; flex:1; min-width:180px; transition: transform 0.2s;
        }
        .stat-card:hover { transform: translateY(-3px); }
        .stat-card .stat-label { font-size:0.72rem; color:var(--muted); text-transform:uppercase; letter-spacing:0.05em; font-weight:700; }
        .stat-card .stat-value { font-size:1.8rem; font-weight:800; margin-top:4px; }
        
        .stat-card.potencial .stat-value { color:#818cf8; }
        .stat-card.muy_potente .stat-value { color:#fbbf24; }
        .stat-card.socio .stat-value { color:#10b981; }

        .kanban-board { display:grid; grid-template-columns:repeat(auto-fit, minmax(320px, 1fr)); gap:20px; }
        @media (max-width: 768px) { .kanban-board { grid-template-columns: 1fr; } }
        
        .kanban-column {
            background:var(--surface); border:1px solid var(--border); border-radius:16px;
            display:flex; flex-direction:column; max-height:80vh;
        }
        @media (max-width: 768px) { .kanban-column { max-height: none; } }

        .kanban-column-header {
            padding:18px 20px; border-bottom:1px solid var(--border);
            display:flex; align-items:center; justify-content:space-between; flex-shrink:0;
        }
        .kanban-column-header h3 { font-size:0.9rem; font-weight:800; display:flex; align-items:center; gap:10px; text-transform:uppercase; letter-spacing:0.02em;}
        .kanban-column-header .count {
            background:rgba(255,255,255,0.08); padding:3px 12px; border-radius:20px;
            font-size:0.75rem; font-weight:800;
        }

        .col-potencial .kanban-column-header { border-top:4px solid #818cf8; border-radius:16px 16px 0 0; }
        .col-muy_potente .kanban-column-header { border-top:4px solid #fbbf24; border-radius:16px 16px 0 0; }
        .col-socio .kanban-column-header { border-top:4px solid #10b981; border-radius:16px 16px 0 0; }

        .kanban-column-body { padding:14px; overflow-y:auto; flex:1; display:flex; flex-direction:column; gap:12px; }

        /* Kanban Card */
        .kanban-card {
            background:rgba(15, 23, 42, 0.4); border:1px solid rgba(255,255,255,0.05); border-radius:14px;
            padding:16px; position:relative; overflow:hidden;
        }
        .kanban-card::after {
            content: ''; position: absolute; top:0; left:0; width:4px; height:100%;
        }
        .card-potencial::after { background: #818cf8; }
        .card-muy_potente::after { background: #fbbf24; }
        .card-socio::after { background: #10b981; }

        .card-client { font-size:1.05rem; font-weight:800; color:#f8fafc; margin-bottom:6px; }
        .card-info { font-size:0.82rem; color:var(--muted); display:flex; align-items:center; gap:8px; margin-bottom:4px; }
        .card-price-tag {
            margin-top:12px; padding:10px; background:rgba(255,255,255,0.03); border-radius:10px;
            display:flex; flex-direction:column; gap:2px;
        }
        .price-label { font-size:0.65rem; text-transform:uppercase; font-weight:700; color:var(--muted); }
        .price-value { font-size:1.15rem; font-weight:900; color:#fff; }
        
        .card-footer { display:flex; justify-content:space-between; align-items:center; margin-top:12px; border-top:1px solid rgba(255,255,255,0.05); padding-top:10px;}
        .card-badge { font-size:0.7rem; font-weight:800; opacity:0.85; }

        .kanban-empty { text-align:center; padding:40px 20px; color:var(--muted); font-size:0.9rem; }
        .kanban-empty i { font-size:2.5rem; margin-bottom:12px; display:block; opacity:0.3; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/components/sidebar.php'; ?>
    <div class="main-content">
        <header class="topbar">
            <div style="display:flex; align-items:center; gap:16px;">
                <button id="btnHamburger" style="background:none; border:none; color:var(--text); cursor:pointer; font-size:1.5rem; display:flex;">☰</button>
                <div class="breadcrumb">Reportes / <span>Clientes Potenciales</span></div>
            </div>
        </header>

        <div class="content-area">
            <div class="page-title">
                <h1>Clientes Potenciales (Poder Adquisitivo)</h1>
                <p>Clasificación basada en el valor de sus pedidos individuales, no en el volumen acumulado.</p>
            </div>

            <div class="stats-bar">
                <div class="stat-card" style="border-left:4px solid var(--primary);">
                    <div class="stat-label">Total Analizados</div>
                    <div class="stat-value"><?= count($clientes) ?></div>
                </div>
                <div class="stat-card socio">
                    <div class="stat-label">Socio (2.0M+)</div>
                    <div class="stat-value"><?= count($socios) ?></div>
                </div>
            </div>

            <div class="kanban-board">
                <!-- Potencial -->
                <div class="kanban-column col-potencial">
                    <div class="kanban-column-header">
                        <h3><i class="fas fa-medal" style="color:#818cf8;"></i> C. Potencial (800k - 1.2M)</h3>
                        <span class="count"><?= count($potenciales) ?></span>
                    </div>
                    <div class="kanban-column-body">
                        <?php if (empty($potenciales)): ?>
                            <div class="kanban-empty"><i class="fas fa-gem"></i> Sin registros</div>
                        <?php else: foreach ($potenciales as $c): ?>
                            <div class="kanban-card card-potencial" onclick="openHistorial('<?= addslashes($c['nombre']) ?>', '<?= addslashes($c['telefono']) ?>')" style="cursor:pointer;">
                                <div class="card-client"><?= htmlspecialchars($c['nombre']) ?></div>
                                <div class="card-info"><i class="fas fa-phone-alt"></i> <?= htmlspecialchars($c['telefono']) ?></div>
                                <div class="card-price-tag">
                                    <span class="price-label">Pedido más alto registrado</span>
                                    <span class="price-value">$<?= number_format($c['pedido_maximo'], 0) ?></span>
                                </div>
                                <div class="card-footer">
                                    <span class="card-badge"><i class="fas fa-shopping-bag"></i> <?= $c['compras'] ?> total</span>
                                    <span style="font-size:0.7rem; color:var(--muted);"><?= date('d/m/y', strtotime($c['ultima_compra'])) ?></span>
                                </div>
                            </div>
                        <?php endforeach; endif; ?>
                    </div>
                </div>

                <!-- Muy Potente -->
                <div class="kanban-column col-muy_potente">
                    <div class="kanban-column-header">
                        <h3><i class="fas fa-crown" style="color:#fbbf24;"></i> Muy Potencial (1.2M - 2M)</h3>
                        <span class="count"><?= count($muy_potentes) ?></span>
                    </div>
                    <div class="kanban-column-body">
                        <?php if (empty($muy_potentes)): ?>
                            <div class="kanban-empty"><i class="fas fa-gem"></i> Sin registros</div>
                        <?php else: foreach ($muy_potentes as $c): ?>
                            <div class="kanban-card card-muy_potente" onclick="openHistorial('<?= addslashes($c['nombre']) ?>', '<?= addslashes($c['telefono']) ?>')" style="cursor:pointer;">
                                <div class="card-client"><?= htmlspecialchars($c['nombre']) ?></div>
                                <div class="card-info"><i class="fas fa-phone-alt"></i> <?= htmlspecialchars($c['telefono']) ?></div>
                                <div class="card-price-tag">
                                    <span class="price-label">Pedido más alto registrado</span>
                                    <span class="price-value">$<?= number_format($c['pedido_maximo'], 0) ?></span>
                                </div>
                                <div class="card-footer">
                                    <span class="card-badge"><i class="fas fa-shopping-bag"></i> <?= $c['compras'] ?> total</span>
                                    <span style="font-size:0.7rem; color:var(--muted);"><?= date('d/m/y', strtotime($c['ultima_compra'])) ?></span>
                                </div>
                            </div>
                        <?php endforeach; endif; ?>
                    </div>
                </div>

                <!-- Socio -->
                <div class="kanban-column col-socio">
                    <div class="kanban-column-header">
                        <h3><i class="fas fa-gem" style="color:#10b981;"></i> Socio (2M+)</h3>
                        <span class="count"><?= count($socios) ?></span>
                    </div>
                    <div class="kanban-column-body">
                        <?php if (empty($socios)): ?>
                            <div class="kanban-empty"><i class="fas fa-gem"></i> Sin registros</div>
                        <?php else: foreach ($socios as $c): ?>
                            <div class="kanban-card card-socio" onclick="openHistorial('<?= addslashes($c['nombre']) ?>', '<?= addslashes($c['telefono']) ?>')" style="cursor:pointer;">
                                <div class="card-client"><?= htmlspecialchars($c['nombre']) ?></div>
                                <div class="card-info"><i class="fas fa-phone-alt"></i> <?= htmlspecialchars($c['telefono']) ?></div>
                                <div class="card-price-tag">
                                    <span class="price-label">Pedido más alto registrado</span>
                                    <span class="price-value">$<?= number_format($c['pedido_maximo'], 0) ?></span>
                                </div>
                                <div class="card-footer">
                                    <span class="card-badge"><i class="fas fa-shopping-bag"></i> <?= $c['compras'] ?> total</span>
                                    <span style="font-size:0.7rem; color:var(--muted);"><?= date('d/m/y', strtotime($c['ultima_compra'])) ?></span>
                                </div>
                            </div>
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
