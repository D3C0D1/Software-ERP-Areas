<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) { header('Location: ./login'); exit; }

$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
if ($basePath === '/' || $basePath === '\\') $basePath = '';

$role = $_SESSION['role'] ?? 'Admin';
$csrfToken = \App\Middlewares\CsrfMiddleware::generateToken();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Base de Datos de Pedidos</title>
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
        body { background:var(--bg); color:var(--text); min-height:100vh; display:flex; }
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

        .toolbar { display:flex; gap:12px; margin-bottom:20px; flex-wrap:wrap; align-items:center; }
        .search-box {
            display:flex; align-items:center; background:rgba(0,0,0,0.2);
            border:1px solid var(--border); border-radius:8px; padding:8px 16px; width:350px;
        }
        .search-box i { color:var(--muted); margin-right:10px; }
        .search-box input { background:transparent; border:none; outline:none; width:100%; font-size:0.95rem; color:var(--text); }
        .filter-select {
            background:rgba(0,0,0,0.2); border:1px solid var(--border); border-radius:8px;
            padding:8px 14px; color:var(--text); font-size:0.88rem; outline:none; cursor:pointer;
        }
        .filter-select option { background:#1e293b; color:var(--text); }

        .card {
            background:var(--surface); border:1px solid var(--border); border-radius:12px; overflow:hidden;
        }
        table { width:100%; border-collapse:collapse; text-align:left; }
        th, td { padding:14px 18px; border-bottom:1px solid var(--border); }
        th {
            background:rgba(255,255,255,0.03); color:var(--muted); font-weight:600;
            font-size:0.8rem; text-transform:uppercase; letter-spacing:0.05em;
        }
        tbody tr { transition: background 0.15s; }
        tbody tr:hover { background:rgba(255,255,255,0.04); }
        .badge-pago { font-size:0.7rem; padding:3px 9px; border-radius:20px; font-weight:700; }
        .bp-pago { background:rgba(16,185,129,0.15); color:#10b981; }
        .bp-abono { background:rgba(245,158,11,0.15); color:#f59e0b; }
        .bp-nopago { background:rgba(239,68,68,0.15); color:#ef4444; }
        .badge-estado { font-size:0.7rem; padding:3px 9px; border-radius:20px; font-weight:700; }
        .be-activo { background:rgba(59,130,246,0.15); color:#3b82f6; }
        .be-completado { background:rgba(16,185,129,0.15); color:#10b981; }
        .be-cancelado { background:rgba(239,68,68,0.15); color:#ef4444; }

        .spinner-container { display:flex; justify-content:center; padding:40px; }
        .spinner { border:4px solid rgba(255,255,255,0.1); width:40px; height:40px; border-radius:50%; border-left-color:var(--primary); animation:spin 1s linear infinite; }
        @keyframes spin { to { transform:rotate(360deg); } }
        .empty-state { text-align:center; padding:50px 20px; color:var(--muted); display:none; }
        .pagination { display:flex; justify-content:center; gap:6px; padding:16px; }
        .page-btn {
            background:rgba(255,255,255,0.05); border:1px solid var(--border); color:var(--muted);
            padding:6px 12px; border-radius:6px; cursor:pointer; font-size:0.85rem; transition:all 0.2s;
        }
        .page-btn:hover, .page-btn.active { background:rgba(99,102,241,0.15); border-color:var(--primary); color:#a5b4fc; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/components/sidebar.php'; ?>
    <div class="main-content">
        <header class="topbar">
            <div style="display:flex; align-items:center; gap:16px;">
                <button id="btnHamburger" style="background:none; border:none; color:var(--text); cursor:pointer; font-size:1.5rem; display:flex;">☰</button>
                <div class="breadcrumb">Flujo de Trabajo / <span>Base de Datos de Pedidos</span></div>
            </div>
        </header>

        <div class="content-area">
            <div class="page-title">
                <h1>Base de Datos de Pedidos</h1>
                <p>Consulta todos los pedidos del sistema con detalle completo.</p>
            </div>

            <div class="toolbar">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Buscar por ID, cliente, nota...">
                </div>
                <select id="filterEstado" class="filter-select">
                    <option value="">Todos los estados</option>
                    <option value="activo">Activos</option>
                    <option value="completado">Completados</option>
                    <option value="cancelado">Cancelados</option>
                </select>
                <select id="filterPago" class="filter-select">
                    <option value="">Todos los pagos</option>
                    <option value="pago_completo">Pagados</option>
                    <option value="abono">Con abono</option>
                    <option value="no_pago">Sin pago</option>
                </select>
            </div>

            <div class="card">
                <table id="pedidosTable" style="display:none;">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nota / Descripción</th>
                            <th>Cliente</th>
                            <th>Teléfono</th>
                            <th>Total</th>
                            <th>Abonado</th>
                            <th>Pago</th>
                            <th>Estado</th>
                            <th>Fecha Creación</th>
                            <th>Entrega</th>
                        </tr>
                    </thead>
                    <tbody id="pedidosTableBody"></tbody>
                </table>
                <div class="spinner-container" id="loadingSpinner"><div class="spinner"></div></div>
                <div class="empty-state" id="emptyState">
                    <i class="fas fa-database" style="font-size:3rem; margin-bottom:15px; opacity:0.5;"></i>
                    <h3>Sin resultados</h3>
                    <p>No se encontraron pedidos con esos filtros.</p>
                </div>
                <div class="pagination" id="pagination"></div>
            </div>
        </div>
    </div>

    <script>
        const basePath = '<?= $basePath ?>';
        let allPedidos = [];
        let currentPage = 1;
        const perPage = 25;

        async function fetchPedidos() {
            try {
                const res = await fetch(basePath + '/public/api/pedidos/list');
                const text = await res.text();
                if (text.trim().startsWith('<')) { console.error("HTML response"); return; }
                const json = JSON.parse(text);
                if (json.status === 'success') {
                    allPedidos = json.data || [];
                    applyFilters();
                }
            } catch(e) { console.error(e); }
            finally {
                document.getElementById('loadingSpinner').style.display = 'none';
                document.getElementById('pedidosTable').style.display = 'table';
            }
        }

        function applyFilters() {
            let data = [...allPedidos];
            const term = document.getElementById('searchInput').value.toLowerCase().trim();
            const estado = document.getElementById('filterEstado').value;
            const pago = document.getElementById('filterPago').value;

            if (term) {
                data = data.filter(p =>
                    String(p.id).includes(term) ||
                    (p.cliente_nombre && p.cliente_nombre.toLowerCase().includes(term)) ||
                    (p.descripcion && p.descripcion.toLowerCase().includes(term)) ||
                    (p.cliente_telefono && p.cliente_telefono.includes(term))
                );
            }
            if (estado) {
                if (estado === 'activo') {
                    data = data.filter(p => p.estado !== 'completado' && p.estado !== 'cancelado');
                } else {
                    data = data.filter(p => p.estado === estado);
                }
            }
            if (pago) {
                data = data.filter(p => p.estado_pago === pago);
            }

            renderTable(data);
        }

        function renderTable(data) {
            const tbody = document.getElementById('pedidosTableBody');
            const emptyState = document.getElementById('emptyState');
            const pagination = document.getElementById('pagination');
            tbody.innerHTML = '';

            if (data.length === 0) {
                emptyState.style.display = 'block';
                pagination.innerHTML = '';
                return;
            }
            emptyState.style.display = 'none';

            // Pagination
            const totalPages = Math.ceil(data.length / perPage);
            if (currentPage > totalPages) currentPage = totalPages;
            const start = (currentPage - 1) * perPage;
            const pageData = data.slice(start, start + perPage);

            pageData.forEach(p => {
                const tr = document.createElement('tr');
                const total = parseFloat(p.total || 0);
                const abonado = parseFloat(p.abonado || 0);
                const fmt = v => new Intl.NumberFormat('es-CO', { style:'currency', currency:'COP', maximumFractionDigits:0 }).format(v);

                let pagoCls = 'bp-nopago', pagoTxt = 'Sin pago';
                if (p.estado_pago === 'pago_completo') { pagoCls = 'bp-pago'; pagoTxt = 'Pagado'; }
                else if (p.estado_pago === 'abono') { pagoCls = 'bp-abono'; pagoTxt = 'Abono'; }

                let estCls = 'be-activo', estTxt = p.estado || 'activo';
                if (p.estado === 'completado') { estCls = 'be-completado'; estTxt = 'Completado'; }
                else if (p.estado === 'cancelado') { estCls = 'be-cancelado'; estTxt = 'Cancelado'; }
                else { estTxt = 'Activo'; }

                const creado = p.created_at ? new Date(p.created_at).toLocaleDateString('es-CO', { day:'numeric', month:'short', year:'numeric' }) : '—';
                const entrega = p.fecha_entrega_esperada ? new Date(p.fecha_entrega_esperada).toLocaleDateString('es-CO', { day:'numeric', month:'short', year:'numeric' }) : '—';

                tr.innerHTML = `
                    <td style="font-weight:700; color:var(--primary);">#${p.id}</td>
                    <td style="max-width:250px;">
                        <div style="font-weight:500; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${p.descripcion || 'Sin nota'}</div>
                    </td>
                    <td style="font-weight:600;">${p.cliente_nombre || '—'}</td>
                    <td style="color:var(--muted);">${p.cliente_telefono || '—'}</td>
                    <td style="font-weight:600; color:#10b981;">${fmt(total)}</td>
                    <td style="font-weight:600; color:#f59e0b;">${fmt(abonado)}</td>
                    <td><span class="badge-pago ${pagoCls}">${pagoTxt}</span></td>
                    <td><span class="badge-estado ${estCls}">${estTxt}</span></td>
                    <td style="font-size:0.83rem; color:var(--muted);">${creado}</td>
                    <td style="font-size:0.83rem; color:var(--muted);">${entrega}</td>
                `;
                tbody.appendChild(tr);
            });

            // Render Pagination
            pagination.innerHTML = '';
            if (totalPages > 1) {
                for (let i = 1; i <= totalPages; i++) {
                    const btn = document.createElement('button');
                    btn.className = 'page-btn' + (i === currentPage ? ' active' : '');
                    btn.textContent = i;
                    btn.onclick = () => { currentPage = i; applyFilters(); };
                    pagination.appendChild(btn);
                }
            }
        }

        document.getElementById('searchInput').addEventListener('input', () => { currentPage = 1; applyFilters(); });
        document.getElementById('filterEstado').addEventListener('change', () => { currentPage = 1; applyFilters(); });
        document.getElementById('filterPago').addEventListener('change', () => { currentPage = 1; applyFilters(); });

        document.addEventListener('DOMContentLoaded', fetchPedidos);
    </script>
</body>
</html>
