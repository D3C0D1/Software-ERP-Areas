<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuración de base path
$scriptName = dirname($_SERVER['SCRIPT_NAME']);
$basePath = str_replace('/public', '', $scriptName);
if ($basePath === '/' || $basePath === '\\') {
    $basePath = '';
}

// Datos de usuario autenticado
$userName = $_SESSION['email'] ?? 'Admin';
$role = $_SESSION['role'] ?? 'Admin';
$userInitials = strtoupper(substr($userName, 0, 2));

// Token CSRF
$csrfToken = \App\Middlewares\CsrfMiddleware::generateToken();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Base de Clientes | ERP</title>
    <!-- Mismo estilo que el resto del sistema -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-color: #0f172a;
            --sidebar-bg: #1e1e2d;
            --sidebar-text: #a1a5b7;
            --sidebar-hover: #151521;
            --primary-color: #6366f1;
            --primary-hover: #4f46e5;
            --text-main: #f1f5f9;
            --text-muted: #94a3b8;
            --border-color: rgba(255, 255, 255, 0.1);
            --card-bg: rgba(30, 41, 59, 0.75);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-main);
            display: flex;
            min-height: 100vh;
            overflow-x: hidden;
        }

        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            margin-left: 0;
            overflow-y: auto;
        }

        .topbar {
            height: 64px;
            padding: 0 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border-color);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .topbar .breadcrumb {
            font-size: 0.95rem;
            color: var(--text-muted);
            font-weight: 500;
        }
        
        .topbar .breadcrumb span {
            color: var(--text-main);
            font-weight: 600;
        }
        
        .content-area {
            padding: 30px;
            flex: 1;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .page-title h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-main);
        }
        .page-title p {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-top: 5px;
        }

        .search-bar-container {
            display: flex;
            align-items: center;
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 8px 16px;
            width: 350px;
        }

        .search-bar-container i {
            color: var(--text-muted);
            margin-right: 10px;
        }

        .search-bar-container input {
            background: transparent;
            border: none;
            outline: none;
            width: 100%;
            font-size: 0.95rem;
            color: var(--text-main);
        }

        /* Table Card */
        .card {
            background-color: var(--card-bg);
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            border: 1px solid var(--border-color);
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }
        th, td {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border-color);
        }
        th {
            background-color: rgba(255, 255, 255, 0.03);
            color: var(--text-muted);
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.04);
        }
        .td-name {
            font-weight: 600;
            color: var(--text-main);
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .td-name .avatar-client {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: rgba(99, 102, 241, 0.15);
            color: #818cf8;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: bold;
        }
        .td-phone {
            color: var(--text-muted);
        }
        .badge-count {
            background: rgba(59, 130, 246, 0.15);
            color: #60a5fa;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 700;
        }

        /* Loading Spinner */
        .spinner-container {
            display: flex;
            justify-content: center;
            padding: 40px;
        }
        .spinner {
            border: 4px solid rgba(0, 0, 0, 0.1);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border-left-color: var(--primary-color);
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: var(--text-muted);
            display: none;
        }

        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }
        .modal-content {
            background: #1e293b;
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);
            overflow: hidden;
            animation: slideDown 0.3s ease-out;
        }
        @keyframes slideDown {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .modal-header {
            padding: 16px 24px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .modal-header h2 {
            font-size: 1.25rem;
            color: var(--text-main);
        }
        .close-modal {
            font-size: 1.5rem;
            color: var(--text-muted);
            cursor: pointer;
        }
        .modal-body {
            padding: 24px;
        }
        .radio-option {
            display: flex;
            align-items: center;
            padding: 12px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .radio-option:hover {
            border-color: var(--primary-color);
            background: rgba(255,255,255,0.05);
        }
        .radio-option input {
            margin-right: 12px;
            cursor: pointer;
        }
        .radio-label {
            display: flex;
            flex-direction: column;
            cursor: pointer;
            width: 100%;
        }
        .radio-name {
            font-weight: 600;
            color: var(--text-main);
        }
        .radio-stats {
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-top: 2px;
        }

        /* Filter Buttons */
        .filter-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-color);
            color: var(--text-muted);
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }
        .filter-btn:hover {
            background: rgba(99, 102, 241, 0.1);
            border-color: rgba(99, 102, 241, 0.3);
            color: #818cf8;
        }
        .filter-btn.active {
            background: rgba(99, 102, 241, 0.15);
            border-color: var(--primary-color);
            color: #a5b4fc;
            font-weight: 600;
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <?php include __DIR__ . '/components/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Topbar -->
        <header class="topbar">
            <div style="display:flex; align-items:center; gap:16px;">
                <button id="btnHamburger" style="background:none; border:none; color:var(--text-main); cursor:pointer; font-size:1.5rem; display:flex;">☰</button>
                <div class="breadcrumb">
                    <span>Base de Clientes</span>
                </div>
            </div>
        </header>

        <!-- Flow Area -->
        <div class="content-area">
            <div class="page-header">
                <div class="page-title">
                    <div style="display:flex; align-items:center; gap:12px;">
                        <h1>Base de Clientes</h1>
                        <span id="totalClientesBadge" style="background:rgba(99,102,241,0.15); color:#818cf8; padding:4px 12px; border-radius:20px; font-weight:700; font-size:0.9rem; border:1px solid rgba(99,102,241,0.3);">
                            <i class="fas fa-users" style="margin-right:6px;"></i> <span id="totalClientesValue">0</span> Clientes
                        </span>
                    </div>
                    <p>Visualiza y busca en el historial de clientes registrados en los pedidos.</p>
                </div>
                <div style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
                    <div class="search-bar-container">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchInput" placeholder="Buscar por cliente o teléfono...">
                    </div>
                </div>
            </div>

            <!-- Filters + Fusionar Todos -->
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; flex-wrap:wrap; gap:12px;">
                <div style="display:flex; gap:8px; flex-wrap:wrap;">
                    <button class="filter-btn active" data-sort="alpha" onclick="sortClients('alpha', this)">
                        <i class="fas fa-sort-alpha-down"></i> A-Z
                    </button>
                    <button class="filter-btn" data-sort="compras" onclick="sortClients('compras', this)">
                        <i class="fas fa-shopping-cart"></i> Más Compras
                    </button>
                    <button class="filter-btn" data-sort="inversion" onclick="sortClients('inversion', this)">
                        <i class="fas fa-dollar-sign"></i> Mayor Inversión
                    </button>
                    <button class="filter-btn" data-sort="reciente" onclick="sortClients('reciente', this)">
                        <i class="fas fa-clock"></i> Más Reciente
                    </button>
                </div>
                <button id="btnMergeAll" onclick="openMergeAllModal()" style="display:none; background:linear-gradient(135deg, #6366f1, #8b5cf6); color:white; border:none; padding:10px 20px; border-radius:8px; cursor:pointer; font-weight:600; font-size:0.9rem; box-shadow:0 4px 15px rgba(99,102,241,0.35); transition: transform 0.2s;">
                    <i class="fas fa-object-group" style="margin-right:6px;"></i> Fusionar Todos los Duplicados
                </button>
            </div>

            <div class="card">
                <table id="clientsTable" style="display:none;">
                    <thead>
                        <tr>
                            <th>Nombre del Cliente</th>
                            <th>Teléfono</th>
                            <th>Compras</th>
                            <th>Inversión Total</th>
                            <th>Última Compra</th>
                            <th style="width: 80px;"></th>
                        </tr>
                    </thead>
                    <tbody id="clientsTableBody">
                        <!-- JS injected rows -->
                    </tbody>
                </table>
                <div class="spinner-container" id="loadingSpinner">
                    <div class="spinner"></div>
                </div>
                <div class="empty-state" id="emptyState">
                    <i class="fas fa-search" style="font-size: 3rem; color: #cbd5e1; margin-bottom: 15px;"></i>
                    <h3>No se encontraron resultados</h3>
                    <p>Intenta con otro término de búsqueda.</p>
                </div>
            </div>
        </div>

        <!-- Modal Fusionar Individual -->
        <div id="mergeModal" class="modal-overlay" style="display:none;">
            <div class="modal-content">
                <div class="modal-header">
                    <h2><i class="fas fa-object-group" style="color:#818cf8; margin-right:8px;"></i> Fusionar Clientes</h2>
                    <span class="close-modal" onclick="closeMergeModal()">&times;</span>
                </div>
                <div class="modal-body">
                    <p style="color: var(--text-muted); font-size: 0.95rem;">Selecciona el nombre principal que deseas conservar para el número <strong id="mergePhone" style="color: var(--text-main);"></strong>. Los demás registros adoptarán este nombre y se unificará su historial.</p>
                    <form id="mergeForm">
                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                        <input type="hidden" id="mergeTelefono" name="telefono">
                        <div id="mergeOptions" style="margin-top:15px; display:flex; flex-direction:column; gap:10px;">
                            <!-- Opciones generadas por JS -->
                        </div>
                        <div style="margin-top:20px; text-align:right;">
                            <button type="button" onclick="closeMergeModal()" style="background:rgba(255,255,255,0.1); color:var(--text-main); border:none; padding:8px 16px; border-radius:6px; cursor:pointer; font-weight:500; margin-right:10px;">Cancelar</button>
                            <button type="submit" style="background:var(--primary-color); color:white; border:none; padding:8px 16px; border-radius:6px; cursor:pointer; font-weight:500;">Aplicar Fusión</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Fusionar Todos -->
        <div id="mergeAllModal" class="modal-overlay" style="display:none;">
            <div class="modal-content" style="max-width:650px; max-height:85vh; overflow-y:auto;">
                <div class="modal-header">
                    <h2><i class="fas fa-layer-group" style="color:#818cf8; margin-right:8px;"></i> Fusionar Todos los Duplicados</h2>
                    <span class="close-modal" onclick="closeMergeAllModal()">&times;</span>
                </div>
                <div class="modal-body">
                    <p style="color: var(--text-muted); font-size: 0.95rem; margin-bottom:20px;">A continuación se muestran todos los grupos de clientes que comparten el mismo número de teléfono. Selecciona el nombre que deseas conservar para cada grupo.</p>
                    <div id="mergeAllGroups">
                        <!-- JS generated -->
                    </div>
                    <div style="margin-top:24px; text-align:right; border-top:1px solid var(--border-color); padding-top:16px;">
                        <button type="button" onclick="closeMergeAllModal()" style="background:rgba(255,255,255,0.1); color:var(--text-main); border:none; padding:10px 18px; border-radius:8px; cursor:pointer; font-weight:500; margin-right:10px;">Cancelar</button>
                        <button type="button" onclick="submitMergeAll()" style="background:linear-gradient(135deg, #6366f1, #8b5cf6); color:white; border:none; padding:10px 20px; border-radius:8px; cursor:pointer; font-weight:600; box-shadow:0 4px 12px rgba(99,102,241,0.3);">
                            <i class="fas fa-check-double" style="margin-right:6px;"></i> Fusionar Todos
                        </button>
                    </div>
                </div>
            </div>
        </div>
        </div>
        <!-- Modal Resultado de Fusión -->
        <div id="mergeResultModal" class="modal-overlay" style="display:none;">
            <div class="modal-content" style="max-width:550px;">
                <div class="modal-header">
                    <h2><i class="fas fa-check-circle" style="color:#10b981; margin-right:8px;"></i> Fusión Completada</h2>
                    <span class="close-modal" onclick="document.getElementById('mergeResultModal').style.display='none'">&times;</span>
                </div>
                <div class="modal-body" id="mergeResultBody">
                    <!-- JS generated -->
                </div>
            </div>
        </div>

        <!-- Modal Editar Cliente -->
        <div id="editClientModal" class="modal-overlay" style="display:none;">
            <div class="modal-content">
                <div class="modal-header">
                    <h2><i class="fas fa-edit" style="color:#818cf8; margin-right:8px;"></i> Editar Cliente</h2>
                    <span class="close-modal" onclick="closeEditModal()">&times;</span>
                </div>
                <div class="modal-body">
                    <form id="editClientForm">
                        <input type="hidden" name="old_nombre" id="editOldNombre">
                        <input type="hidden" name="old_telefono" id="editOldTelefono">
                        <div class="form-group" style="margin-bottom:15px;">
                            <label style="display:block; margin-bottom:5px; color:var(--text-muted); font-size:0.9rem;">Nombre del Cliente</label>
                            <input type="text" name="new_nombre" id="editNewNombre" required style="width:100%; padding:10px; border-radius:8px; border:1px solid rgba(255,255,255,0.2); background:rgba(0,0,0,0.2); color:white; font-size:1rem;">
                        </div>
                        <div class="form-group" style="margin-bottom:20px;">
                            <label style="display:block; margin-bottom:5px; color:var(--text-muted); font-size:0.9rem;">Teléfono</label>
                            <input type="text" name="new_telefono" id="editNewTelefono" style="width:100%; padding:10px; border-radius:8px; border:1px solid rgba(255,255,255,0.2); background:rgba(0,0,0,0.2); color:white; font-size:1rem;">
                        </div>
                        <div style="text-align:right;">
                            <button type="button" onclick="closeEditModal()" style="background:rgba(255,255,255,0.1); color:var(--text-main); border:none; padding:8px 16px; border-radius:6px; cursor:pointer; font-weight:500; margin-right:10px;">Cancelar</button>
                            <button type="submit" style="background:var(--primary-color); color:white; border:none; padding:8px 16px; border-radius:6px; cursor:pointer; font-weight:500;">Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        const basePath = '<?= $basePath ?>';
        const csrfToken = '<?= $csrfToken ?>';
        let allClients = [];
        let currentSort = 'alpha';

        async function fetchClients() {
            try {
                const apiEndpoint = basePath + '/public/api/clientes/list';
                const res = await fetch(apiEndpoint);
                const text = await res.text();
                if (text.trim().startsWith('<')) {
                    console.error("Received HTML instead of JSON for the API request.", text);
                    return;
                }
                const json = JSON.parse(text);
                if (json.status === 'success') {
                    // Parse numeric fields to ensure sorting works
                    allClients = json.data.map(c => ({
                        ...c,
                        compras: parseInt(c.compras) || 0,
                        monto_total: parseFloat(c.monto_total) || 0
                    }));
                    document.getElementById('totalClientesValue').innerText = allClients.length;
                    applySortAndRender();
                } else {
                    console.error('Error fetching clients:', json.message);
                }
            } catch (err) {
                console.error('Network/Parsing error', err);
            } finally {
                document.getElementById('loadingSpinner').style.display = 'none';
                document.getElementById('clientsTable').style.display = 'table';
            }
        }

        function applySortAndRender() {
            let data = [...allClients];
            const term = document.getElementById('searchInput').value.toLowerCase().trim();
            if (term) {
                data = data.filter(c =>
                    (c.nombre && c.nombre.toLowerCase().includes(term)) ||
                    (c.telefono && c.telefono.toLowerCase().includes(term))
                );
            }
            // Sort first, then render
            data = sortData(data, currentSort);
            renderTable(data);
        }

        function sortData(data, sortBy) {
            const sorted = [...data];
            switch(sortBy) {
                case 'alpha':
                    sorted.sort((a, b) => (a.nombre || '').localeCompare(b.nombre || '', 'es'));
                    break;
                case 'compras':
                    sorted.sort((a, b) => b.compras - a.compras);
                    break;
                case 'inversion':
                    sorted.sort((a, b) => b.monto_total - a.monto_total);
                    break;
                case 'reciente':
                    sorted.sort((a, b) => {
                        const da = a.ultima_compra ? new Date(a.ultima_compra).getTime() : 0;
                        const db = b.ultima_compra ? new Date(b.ultima_compra).getTime() : 0;
                        return db - da;
                    });
                    break;
            }
            return sorted;
        }

        function sortClients(sortBy, btn) {
            currentSort = sortBy;
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            applySortAndRender();
        }

        function getDuplicateGroups(data) {
            const grouped = {};
            const order = [];
            data.forEach(c => {
                const phone = (c.telefono || '').trim();
                const key = phone || ('__solo_' + c.nombre + '_' + Math.random());
                if (!grouped[key]) {
                    grouped[key] = [];
                    order.push(key);
                }
                grouped[key].push(c);
            });
            // Return in insertion order
            return order.map(k => ({ key: k, clients: grouped[k] }));
        }

        function renderTable(data) {
            const tbody = document.getElementById('clientsTableBody');
            const emptyState = document.getElementById('emptyState');
            tbody.innerHTML = '';

            if (data.length === 0) {
                emptyState.style.display = 'block';
                document.getElementById('btnMergeAll').style.display = 'none';
                return;
            }
            emptyState.style.display = 'none';

            const groups = getDuplicateGroups(data);

            // Check if there are duplicate groups
            const dupCount = groups.filter(g => g.clients.length > 1 && g.clients[0].telefono).length;
            document.getElementById('btnMergeAll').style.display = dupCount > 0 ? 'inline-flex' : 'none';

            groups.forEach(group => {
                const isDuplicate = group.clients.length > 1 && group.clients[0].telefono;

                if(isDuplicate) {
                    const trHeader = document.createElement('tr');
                    trHeader.style.backgroundColor = 'rgba(99, 102, 241, 0.06)';
                    trHeader.innerHTML = `
                        <td colspan="6" style="padding: 10px 20px;">
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <span style="font-weight: bold; color:var(--text-main);">
                                    <i class="fas fa-users" style="color: var(--primary-color); margin-right: 5px;"></i> Teléfono coincidente: ${group.clients[0].telefono} (${group.clients.length} registros)
                                </span>
                                <button onclick="openMergeModal('${group.clients[0].telefono}')" style="background:var(--primary-color); color:white; border:none; padding:6px 15px; border-radius:6px; cursor:pointer; font-weight:500; font-size:0.85rem;">
                                    <i class="fas fa-object-group" style="margin-right:5px;"></i> Fusionar
                                </button>
                            </div>
                        </td>
                    `;
                    tbody.appendChild(trHeader);
                }

                group.clients.forEach(c => {
                    const tr = document.createElement('tr');
                    if (isDuplicate) tr.style.borderLeft = '3px solid rgba(99, 102, 241, 0.3)';
                    const initials = (c.nombre || 'C').substring(0,2).toUpperCase();

                    const monto = new Intl.NumberFormat('es-CO', {
                        style: 'currency',
                        currency: 'COP',
                        maximumFractionDigits: 0
                    }).format(c.monto_total);

                    const fecha = c.ultima_compra ? new Date(c.ultima_compra).toLocaleDateString('es-CO', {
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric'
                    }) : '—';

                    tr.innerHTML = `
                        <td class="td-name">
                            <div class="avatar-client">${initials}</div>
                            ${c.nombre}
                        </td>
                        <td class="td-phone">${c.telefono || '—'}</td>
                        <td><span class="badge-count">${c.compras} ${c.compras == 1 ? 'compra' : 'compras'}</span></td>
                        <td style="font-weight: 600; color: #10b981;">${monto}</td>
                        <td style="color: var(--text-muted); font-size: 0.85rem;">${fecha}</td>
                        <td style="text-align:right;">
                            <button onclick="openEditModal('${c.nombre}', '${c.telefono || ''}')" style="background:rgba(255,255,255,0.05); color:var(--text-muted); border:1px solid var(--border-color); padding:6px 12px; border-radius:6px; cursor:pointer; font-size:0.85rem; transition:all 0.2s;">
                                <i class="fas fa-edit"></i> Editar
                            </button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            });
        }

        // ── Modal Editar Cliente ──
        function openEditModal(nombre, telefono) {
            document.getElementById('editOldNombre').value = nombre;
            document.getElementById('editOldTelefono').value = telefono;
            document.getElementById('editNewNombre').value = nombre;
            document.getElementById('editNewTelefono').value = telefono;
            document.getElementById('editClientModal').style.display = 'flex';
        }

        function closeEditModal() {
            document.getElementById('editClientModal').style.display = 'none';
        }

        document.getElementById('editClientForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData.entries());

            try {
                const apiEndpoint = basePath + '/public/api/clientes/update';
                const res = await fetch(apiEndpoint, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({...data, csrf_token: csrfToken})
                });
                const json = await res.json();

                if (json.status === 'success') {
                    closeEditModal();
                    document.getElementById('loadingSpinner').style.display = 'flex';
                    document.getElementById('clientsTable').style.display = 'none';
                    await fetchClients();
                    alert(json.message);
                } else {
                    alert('Error: ' + json.message);
                }
            } catch (err) {
                console.error(err);
                alert('Error de conexión');
            }
        });


        // ── Modal Fusionar Individual ──
        function openMergeModal(phone) {
            document.getElementById('mergePhone').innerText = phone;
            document.getElementById('mergeTelefono').value = phone;

            const optionsContainer = document.getElementById('mergeOptions');
            optionsContainer.innerHTML = '';

            const clientsWPhone = allClients.filter(c => c.telefono === phone);

            clientsWPhone.forEach((c, index) => {
                const checked = index === 0 ? 'checked' : '';
                optionsContainer.innerHTML += `
                    <label class="radio-option">
                        <input type="radio" name="nombre_final" value="${c.nombre}" ${checked}>
                        <div class="radio-label">
                            <span class="radio-name">${c.nombre}</span>
                            <span class="radio-stats">${c.compras} compras | Inversión: $${c.monto_total.toLocaleString()}</span>
                        </div>
                    </label>
                `;
            });

            document.getElementById('mergeModal').style.display = 'flex';
        }

        function closeMergeModal() {
            document.getElementById('mergeModal').style.display = 'none';
        }

        // Build before/after info for merge result display
        function buildMergeInfo(phone, nombreFinal) {
            const clients = allClients.filter(c => c.telefono === phone);
            const before = clients.map(c => ({
                nombre: c.nombre,
                compras: c.compras,
                monto: c.monto_total
            }));
            const totalCompras = clients.reduce((sum, c) => sum + c.compras, 0);
            const totalMonto = clients.reduce((sum, c) => sum + c.monto_total, 0);
            return { phone, nombreFinal, before, totalCompras, totalMonto, count: clients.length };
        }

        function showMergeResult(mergeInfos) {
            const body = document.getElementById('mergeResultBody');
            let html = `
                <div style="background:rgba(16,185,129,0.1); border:1px solid rgba(16,185,129,0.25); border-radius:10px; padding:14px 18px; margin-bottom:18px;">
                    <div style="display:flex; align-items:center; gap:8px; margin-bottom:4px;">
                        <i class="fas fa-check-circle" style="color:#10b981; font-size:1.2rem;"></i>
                        <strong style="color:#10b981; font-size:1rem;">¡Fusión exitosa!</strong>
                    </div>
                    <p style="color:var(--text-muted); font-size:0.88rem; margin:0;">Se fusionaron <strong style="color:var(--text-main);">${mergeInfos.reduce((s,m)=>s+m.count,0)} registros</strong> en <strong style="color:var(--text-main);">${mergeInfos.length} grupo(s)</strong>.</p>
                </div>
            `;

            mergeInfos.forEach(info => {
                html += `
                    <div style="background:rgba(0,0,0,0.15); border:1px solid var(--border-color); border-radius:10px; padding:16px; margin-bottom:12px;">
                        <div style="display:flex; align-items:center; gap:8px; margin-bottom:12px;">
                            <i class="fas fa-phone-alt" style="color:#818cf8;"></i>
                            <strong style="color:var(--text-main);">${info.phone}</strong>
                        </div>

                        <div style="margin-bottom:12px;">
                            <div style="font-size:0.75rem; text-transform:uppercase; color:var(--text-muted); font-weight:600; margin-bottom:6px; letter-spacing:0.05em;">
                                <i class="fas fa-arrow-left" style="margin-right:4px;"></i> ANTES (${info.count} registros separados)
                            </div>
                            ${info.before.map(b => `
                                <div style="display:flex; justify-content:space-between; padding:6px 10px; border-radius:6px; background:rgba(239,68,68,0.06); border:1px solid rgba(239,68,68,0.12); margin-bottom:4px; font-size:0.85rem;">
                                    <span style="color:var(--text-main);">${b.nombre}</span>
                                    <span style="color:var(--text-muted);">${b.compras} compras | $${b.monto.toLocaleString()}</span>
                                </div>
                            `).join('')}
                        </div>

                        <div>
                            <div style="font-size:0.75rem; text-transform:uppercase; color:var(--text-muted); font-weight:600; margin-bottom:6px; letter-spacing:0.05em;">
                                <i class="fas fa-arrow-right" style="margin-right:4px;"></i> DESPUÉS (1 registro unificado)
                            </div>
                            <div style="display:flex; justify-content:space-between; padding:8px 12px; border-radius:6px; background:rgba(16,185,129,0.08); border:1px solid rgba(16,185,129,0.2); font-size:0.9rem;">
                                <span style="color:#10b981; font-weight:600;">${info.nombreFinal}</span>
                                <span style="color:var(--text-muted);">${info.totalCompras} compras | $${info.totalMonto.toLocaleString()}</span>
                            </div>
                        </div>
                    </div>
                `;
            });

            html += `
                <div style="text-align:right; margin-top:16px;">
                    <button onclick="document.getElementById('mergeResultModal').style.display='none'" style="background:var(--primary-color); color:white; border:none; padding:10px 20px; border-radius:8px; cursor:pointer; font-weight:600;">
                        <i class="fas fa-check" style="margin-right:6px;"></i> Entendido
                    </button>
                </div>
            `;

            body.innerHTML = html;
            document.getElementById('mergeResultModal').style.display = 'flex';
        }

        document.getElementById('mergeForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const phone = formData.get('telefono');
            const nombreFinal = formData.get('nombre_final');

            // Capture before-state info
            const mergeInfo = buildMergeInfo(phone, nombreFinal);

            try {
                const apiEndpoint = basePath + '/public/api/clientes/merge';
                const res = await fetch(apiEndpoint, {
                    method: 'POST',
                    body: formData
                });
                const json = await res.json();

                if (json.status === 'success') {
                    closeMergeModal();
                    document.getElementById('loadingSpinner').style.display = 'flex';
                    document.getElementById('clientsTable').style.display = 'none';
                    await fetchClients();
                    showMergeResult([mergeInfo]);
                } else {
                    alert('Error: ' + json.message);
                }
            } catch (err) {
                console.error(err);
                alert('Error de conexión');
            }
        });

        // ── Modal Fusionar Todos ──
        function openMergeAllModal() {
            const groups = getDuplicateGroups(allClients);
            const dupGroups = groups.filter(g => g.clients.length > 1 && g.clients[0].telefono);

            if (dupGroups.length === 0) {
                alert('No hay clientes duplicados para fusionar.');
                return;
            }

            const container = document.getElementById('mergeAllGroups');
            container.innerHTML = '';

            dupGroups.forEach((group, gIdx) => {
                const phone = group.clients[0].telefono;
                let groupHtml = `
                    <div style="background:rgba(0,0,0,0.15); border:1px solid var(--border-color); border-radius:10px; padding:16px; margin-bottom:14px;">
                        <div style="display:flex; align-items:center; gap:8px; margin-bottom:12px;">
                            <i class="fas fa-phone-alt" style="color:#818cf8;"></i>
                            <strong style="color:var(--text-main);">${phone}</strong>
                            <span style="background:rgba(99,102,241,0.15); color:#818cf8; padding:2px 10px; border-radius:20px; font-size:0.75rem; font-weight:700;">${group.clients.length} registros</span>
                        </div>
                        <div style="display:flex; flex-direction:column; gap:8px;">
                `;

                group.clients.forEach((c, cIdx) => {
                    const checked = cIdx === 0 ? 'checked' : '';
                    groupHtml += `
                        <label class="radio-option" style="margin:0;">
                            <input type="radio" name="merge_all_${gIdx}" value="${c.nombre}" data-phone="${phone}" ${checked}>
                            <div class="radio-label">
                                <span class="radio-name">${c.nombre}</span>
                                <span class="radio-stats">${c.compras} compras | $${c.monto_total.toLocaleString()}</span>
                            </div>
                        </label>
                    `;
                });

                groupHtml += '</div></div>';
                container.innerHTML += groupHtml;
            });

            document.getElementById('mergeAllModal').style.display = 'flex';
        }

        function closeMergeAllModal() {
            document.getElementById('mergeAllModal').style.display = 'none';
        }

        async function submitMergeAll() {
            const groups = getDuplicateGroups(allClients);
            const dupGroups = groups.filter(g => g.clients.length > 1 && g.clients[0].telefono);

            const merges = [];
            const mergeInfos = [];

            dupGroups.forEach((group, gIdx) => {
                const selected = document.querySelector(`input[name="merge_all_${gIdx}"]:checked`);
                if (selected) {
                    const phone = selected.dataset.phone;
                    const nombre = selected.value;
                    merges.push({ telefono: phone, nombre_final: nombre });
                    mergeInfos.push(buildMergeInfo(phone, nombre));
                }
            });

            if (merges.length === 0) {
                alert('No se seleccionó ningún nombre.');
                return;
            }

            try {
                const apiEndpoint = basePath + '/public/api/clientes/merge-all';
                const res = await fetch(apiEndpoint, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ merges, csrf_token: csrfToken })
                });
                const json = await res.json();

                if (json.status === 'success') {
                    closeMergeAllModal();
                    document.getElementById('loadingSpinner').style.display = 'flex';
                    document.getElementById('clientsTable').style.display = 'none';
                    await fetchClients();
                    showMergeResult(mergeInfos);
                } else {
                    alert('Error: ' + json.message);
                }
            } catch (err) {
                console.error(err);
                alert('Error de conexión');
            }
        }

        // Real-time search on every keystroke
        document.getElementById('searchInput').addEventListener('input', () => {
            applySortAndRender();
        });

        // Init
        document.addEventListener('DOMContentLoaded', fetchClients);
    </script>
</body>
</html>
