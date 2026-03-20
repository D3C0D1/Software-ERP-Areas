<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Admin', 'SuperAdmin', 'Gerente'])) {
    header('Location: dashboard');
    exit;
}

require_once __DIR__ . '/../config/Database.php';
$db = \Config\Database::getInstance();
$csrfToken = \App\Middlewares\CsrfMiddleware::generateToken();
try {
    $sndRows = $db->query("SELECT clave, valor FROM configuracion WHERE clave IN ('sonido_habilitado','sonido_tema')")->fetchAll(PDO::FETCH_KEY_PAIR);
}
catch (\Exception $e) {
    $sndRows = [];
}
$sonidoHabilitado = $sndRows['sonido_habilitado'] ?? '1';
$sonidoTema = $sndRows['sonido_tema'] ?? 'cristal';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="<?= $csrfToken?>">
    <title>Reportes de Pedidos | Banner</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-dark: #0f172a;
            --surface: #1e293b;
            --primary: #4f46e5;
            --accent: #10b981;
            --text: #f8fafc;
            --muted: #94a3b8;
            --border: rgba(255, 255, 255, 0.08);
            --danger: #ef4444;
            --warning: #f59e0b;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: var(--bg-dark);
            color: var(--text);
            display: flex;
            min-height: 100vh;
        }

        .main-content {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
        }

        .header {
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .header h1 {
            font-size: 1.8rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .controls-pane {
            background: var(--surface);
            padding: 20px;
            border-radius: 12px;
            border: 1px solid var(--border);
            margin-bottom: 24px;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .controls-row {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: flex-end;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .form-group label {
            font-size: 0.85rem;
            color: var(--muted);
            font-weight: 500;
        }

        .form-control {
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid var(--border);
            color: var(--text);
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 0.9rem;
            outline: none;
            transition: border-color 0.2s;
            min-width: 180px;
        }

        .form-control:focus {
            border-color: var(--primary);
        }

        .btn {
            background: var(--primary);
            color: #fff;
            border: none;
            padding: 10px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn:hover {
            opacity: 0.9;
        }

        .btn-danger {
            background: var(--danger);
        }

        .btn-warning {
            background: var(--warning);
            color: #000;
        }

        .btn-excel {
            background: #10b981;
        }

        /* Tabs */
        .tabs {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            border-bottom: 1px solid var(--border);
        }

        .tab {
            padding: 10px 20px;
            color: var(--muted);
            cursor: pointer;
            border-bottom: 3px solid transparent;
            font-weight: 500;
        }

        .tab:hover {
            color: var(--text);
        }

        .tab.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
        }

        /* Table */
        .table-container {
            background: var(--surface);
            border-radius: 12px;
            border: 1px solid var(--border);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        th,
        td {
            padding: 14px 20px;
            border-bottom: 1px solid var(--border);
            font-size: 0.9rem;
            white-space: nowrap;
        }

        th {
            background: rgba(0, 0, 0, 0.2);
            color: var(--muted);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
        }

        tbody tr:hover {
            background: rgba(255, 255, 255, 0.03);
        }

        .estado-tr {
            cursor: pointer;
        }

        .badge {
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-pendiente {
            background: rgba(59, 130, 246, 0.2);
            color: #60a5fa;
        }

        .badge-completado {
            background: rgba(16, 185, 129, 0.2);
            color: #34d399;
        }

        .badge-cancelado {
            background: rgba(239, 68, 68, 0.2);
            color: #f87171;
        }

        .modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.6);
            z-index: 1000;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .modal-content {
            background: var(--surface);
            border-radius: 12px;
            border: 1px solid var(--border);
            width: 100%;
            max-width: 600px;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
        }

        .modal-header {
            padding: 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-body {
            padding: 20px;
            overflow-y: auto;
        }

        .modal-close {
            background: none;
            border: none;
            color: var(--muted);
            cursor: pointer;
            font-size: 1.5rem;
        }

        .timeline {
            margin-top: 20px;
            border-left: 2px solid var(--primary);
            margin-left: 10px;
            padding-left: 20px;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 20px;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -26px;
            top: 0;
            width: 10px;
            height: 10px;
            background: var(--primary);
            border-radius: 50%;
            border: 2px solid var(--surface);
        }

        .timeline-date {
            font-size: 0.8rem;
            color: var(--muted);
            margin-bottom: 4px;
        }

        .timeline-title {
            font-weight: 600;
            font-size: 0.95rem;
            margin-bottom: 4px;
        }

        .timeline-desc {
            font-size: 0.9rem;
            color: #cbd5e1;
        }

        .danger-zone {
            margin-top: 20px;
            padding: 20px;
            background: rgba(239, 68, 68, 0.05);
            border: 1px solid rgba(239, 68, 68, 0.2);
            border-radius: 10px;
        }
    </style>
</head>

<body>
    <?php include __DIR__ . '/components/sidebar.php'; ?>

    <main class="main-content">
        <div class="header">
            <div>
                <h1>
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <polyline points="10 9 9 9 8 9"></polyline>
                    </svg>
                    Reportes y Analíticas de Pedidos
                </h1>
                <p style="color:var(--muted); margin-top:5px;">Administración avanzada, históricos y limpieza del
                    sistema.</p>
            </div>

            <button class="btn btn-excel" onclick="exportData()"><svg xmlns="http://www.w3.org/2000/svg" width="16"
                    height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="12" y1="18" x2="12" y2="12"></line>
                    <line x1="9" y1="15" x2="15" y2="15"></line>
                </svg> Exportar Excel (CSV)</button>
        </div>

        <div class="controls-pane">
            <div class="controls-row">
                <div class="form-group">
                    <label>Filtro por Rango (Métricas de Tiempo)</label>
                    <select id="filtroRango" class="form-control" onchange="cargarDatos()">
                        <option value="0">Toda la historia</option>
                        <option value="7">Últimos 7 días (Semana actual)</option>
                        <option value="30">Últimos 30 días (Mes actual)</option>
                        <option value="90">Últimos 3 meses</option>
                    </select>
                </div>
                <div class="form-group" style="flex:1;">
                    <label>Buscar Pedido (ID, Cliente o Área)</label>
                    <input type="text" id="filtroTexto" class="form-control" placeholder="Ej. Juan Pérez o 1045"
                        oninput="filtrarLocal()">
                </div>
            </div>
        </div>

        <div class="tabs">
            <div class="tab active" data-tab="proceso" onclick="cambiarTab('proceso')">En Proceso</div>
            <div class="tab" data-tab="finalizados" onclick="cambiarTab('finalizados')">Finalizados</div>
            <div class="tab" data-tab="eliminados" onclick="cambiarTab('eliminados')">Eliminados / Cancelados</div>
        </div>

        <div class="table-container">
            <table id="pedidosTable">
                <thead>
                    <tr>
                        <th width="50"><input type="checkbox" id="checkAll" onchange="toggleChecks()"></th>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Estado</th>
                        <th>Área / Fase</th>
                        <th>Creado El</th>
                        <th>Economía</th>
                        <?php if (in_array($_SESSION['role'] ?? '', ['Admin', 'SuperAdmin'])): ?>
                        <th>Acciones</th>
                        <?php
endif; ?>
                    </tr>
                </thead>
                <tbody id="tablaBody">
                    <tr>
                        <td colspan="9" style="text-align:center; padding:30px; color:var(--muted)">Cargando...</td>
                    </tr>
                </tbody>
            </table>
        </div>


    </main>

    <!-- Modal Detalles / Seguimiento -->
    <div id="modalDetalles" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalDetallesTitle">Seguimiento de Pedido</h2>
                <button class="modal-close" onclick="cerrarModales()">&times;</button>
            </div>
            <div class="modal-body" id="modalDetallesBody">
                <!-- Vía JS -->
            </div>
        </div>
    </div>

    <!-- Modal Limpieza -->
    <div id="modalDanger" class="modal">
        <div class="modal-content" style="max-width:500px;">
            <div class="modal-header">
                <h2 id="mdTitle" style="color:var(--danger)">Eliminar</h2>
                <button class="modal-close" onclick="cerrarModales()">&times;</button>
            </div>
            <div class="modal-body">
                <p id="mdText" style="margin-bottom:15px;"></p>
                <div class="form-group" id="mdSelector">
                    <label>Rango a eliminar:</label>
                    <select id="dangerRango" class="form-control">
                    </select>
                </div>
                <div style="display:flex; gap:10px; margin-top:25px; justify-content:flex-end;">
                    <button class="btn" style="background:#475569;" onclick="cerrarModales()">Cancelar</button>
                    <button class="btn btn-danger" id="mdConfirmBtn" onclick="ejecutarLimpieza()">Confirmar
                        Eliminación</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const basePath = window.location.pathname.replace(/\/reportes-pedidos\/?$/i, '');
        let currentTab = 'proceso';
        let allData = [];
        let dangerMode = '';

        function cambiarTab(tab) {
            currentTab = tab;
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.querySelector(`.tab[data-tab="${tab}"]`).classList.add('active');
            cargarDatos();
        }

        async function cargarDatos() {
            const tbody = document.getElementById('tablaBody');
            tbody.innerHTML = '<tr><td colspan="9" style="text-align:center; padding:30px; color:var(--muted)">Cargando...</td></tr>';

            const dias = document.getElementById('filtroRango').value;
            try {
                const r = await fetch(`${basePath}/api/reportes-pedidos/list?tab=${currentTab}&rango_dias=${dias}`);
                const res = await r.json();
                if (res.status === 'success') {
                    allData = res.data;
                    filtrarLocal();
                } else alert('Error: ' + res.message);
            } catch (e) {
                tbody.innerHTML = '<tr><td colspan="9" style="text-align:center; padding:30px; color:var(--danger)">Error de conexión.</td></tr>';
            }
        }

        function filtrarLocal() {
            const term = document.getElementById('filtroTexto').value.toLowerCase();
            const tbody = document.getElementById('tablaBody');
            tbody.innerHTML = '';

            const filtered = allData.filter(p => {
                if (!term) return true;
                return p.id.toString().includes(term) ||
                    (p.cliente_nombre || '').toLowerCase().includes(term) ||
                    (p.area_nombre || '').toLowerCase().includes(term);
            });

            if (filtered.length === 0) {
                tbody.innerHTML = '<tr><td colspan="9" style="text-align:center; padding:30px; color:var(--muted)">Sin resultados.</td></tr>';
                return;
            }

            filtered.forEach(p => {
                const tr = document.createElement('tr');
                tr.className = 'estado-tr';

                let bClase = 'badge-pendiente';
                if (p.estado === 'completado') bClase = 'badge-completado';
                else if (p.estado === 'cancelado') bClase = 'badge-cancelado';

                let isAdmin = <?=(in_array($_SESSION['role'] ?? '', ['Admin', 'SuperAdmin'])) ? 'true' : 'false'?>;
                let isSuperAdmin = <?= (($_SESSION['role'] ?? '') === 'SuperAdmin') ? 'true' : 'false' ?>;
                
                let btnEliminar = isAdmin ? `<button class="btn btn-danger btn-sm" style="padding:4px 8px; font-size:0.75rem;" onclick="event.stopPropagation(); eliminarPedido(${p.id})">🗑 Eliminar</button>` : '';
                
                let btnRevertir = (isSuperAdmin && p.estado === 'completado') 
                    ? `<button class="btn btn-warning btn-sm" style="padding:4px 8px; font-size:0.75rem; margin-right:5px; color:white;" onclick="event.stopPropagation(); revertirPedido(${p.id})">↩ Revertir</button>` 
                    : '';

                tr.innerHTML = `
                    <td onclick="event.stopPropagation()"><input type="checkbox" class="ped-check" value="${p.id}"></td>
                    <td onclick="abrirSeguimiento(${p.id})">#${p.id}</td>
                    <td onclick="abrirSeguimiento(${p.id})"><strong>${p.cliente_nombre}</strong></td>
                    <td onclick="abrirSeguimiento(${p.id})"><span class="badge ${bClase}">${p.estado.toUpperCase()}</span></td>
                    <td onclick="abrirSeguimiento(${p.id})">${p.area_nombre || 'N/A'}<br><small style="color:var(--muted)">${p.fase_actual}</small></td>
                    <td onclick="abrirSeguimiento(${p.id})">${p.created_at.split(' ')[0]}</td>
                    <td onclick="abrirSeguimiento(${p.id})">${p.adjuntos > 0 ? '📎 ' + p.adjuntos : '-'}</td>
                    <td onclick="abrirSeguimiento(${p.id})">$${parseFloat(p.total).toLocaleString()}</td>
                    ${isAdmin ? '<td onclick="event.stopPropagation()">' + btnRevertir + btnEliminar + '</td>' : ''}
                `;
                tbody.appendChild(tr);
            });
        }

        function toggleChecks() {
            const m = document.getElementById('checkAll').checked;
            document.querySelectorAll('.ped-check').forEach(c => c.checked = m);
        }

        // CSV Export Data
        function exportData() {
            let csv = ["ID\tCliente\tEstado\tArea\tFase\tAdjuntos\tTotal\tFecha"];
            allData.forEach(p => {
                csv.push(`${p.id}\t"${p.cliente_nombre || ''}"\t${p.estado}\t${p.area_nombre || 'N/A'}\t${p.fase_actual}\t${p.adjuntos}\t${p.total}\t${p.created_at}`);
            });

            const blob = new Blob([csv.join('\n')], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement("a");
            link.href = URL.createObjectURL(blob);
            link.download = `Reporte_${currentTab}_${new Date().getTime()}.csv`;
            link.click();
        }

        async function abrirSeguimiento(id) {
            document.getElementById('modalDetallesBody').innerHTML = '<p>Cargando detalles...</p>';
            document.getElementById('modalDetalles').style.display = 'flex';

            try {
                const r = await fetch(`${basePath}/api/reportes-pedidos/detalles/${id}`);
                const res = await r.json();
                if (res.status === 'success') {
                    let ped = res.data.pedido;
                    let movs = res.data.auditoria; // Trazabilidad estricta
                    let movsArea = res.data.movimientos_area; // Desglose entre áreas
                    let tiempos = res.data.tiempos_area;
                    let tiempoTotal = res.data.tiempo_total;

                    let html = `<div style="background:rgba(0,0,0,0.2); padding:15px; border-radius:8px; margin-bottom:20px;">
                        <h3>Pedido #${ped.id} - ${ped.cliente_nombre}</h3>
                        <p style="color:var(--muted); font-size:0.9rem; margin-top:5px;">Área actual: <strong>${ped.area_nombre || 'Ninguna'}</strong> | Asignado: <strong>${ped.asignado_nombre || 'Nadie'}</strong></p>
                    </div>`;

                    // Renderizar los tiempos y demoras
                    html += `<h4>⏳ Tiempos de Demora por Área</h4>`;
                    if (Object.keys(tiempos).length === 0) {
                        html += `<p style="color:var(--muted); font-size:0.9rem;">Sin información de tiempos en áreas.</p>`;
                    } else {
                        html += `<div style="display:flex; flex-wrap:wrap; gap:10px; margin-top:10px; margin-bottom:20px;">`;
                        for (let area in tiempos) {
                            let horas = (tiempos[area] / 3600).toFixed(1);
                            let color = horas > 24 ? 'var(--danger)' : (horas > 8 ? 'var(--warning)' : 'var(--accent)');
                            html += `<div style="background:rgba(255,255,255,0.05); padding:10px; border-radius:6px; flex:1; min-width:120px; border-bottom:3px solid ${color};">
                                        <div style="font-size:0.75rem; color:var(--muted); text-transform:uppercase;">${area}</div>
                                        <div style="font-size:1.2rem; font-weight:700;">${horas}h</div>
                                     </div>`;
                        }
                        let totHoras = (tiempoTotal / 3600).toFixed(1);
                        html += `<div style="background:rgba(79, 70, 229, 0.1); padding:10px; border-radius:6px; flex:1; min-width:120px; border-bottom:3px solid var(--primary);">
                                    <div style="font-size:0.75rem; color:var(--primary); text-transform:uppercase;">TOTAL RECORRIDO</div>
                                    <div style="font-size:1.2rem; font-weight:700;">${totHoras}h</div>
                                 </div></div>`;
                    }

                    // Timeline global
                    html += `<h4 style="margin-top:20px;">Línea de Tiempo Operativa</h4><div class="timeline">`;
                    if (movsArea.length === 0 && movs.length === 0) html += '<p style="color:var(--muted)">Sin movimientos reportados.</p>';
                    else {
                        // Combinamos (si quisiéramos) o simplemente mostramos la auditoría detallada
                        movs.forEach(m => {
                            html += `
                            <div class="timeline-item">
                                <div class="timeline-date">${m.created_at}</div>
                                <div class="timeline-title">${m.accion.toUpperCase()}</div>
                                <div class="timeline-desc">${m.descripcion_accion}</div>
                            </div>`;
                        });
                    }
                    html += `</div>`;

                    document.getElementById('modalDetallesBody').innerHTML = html;
                } else {
                    document.getElementById('modalDetallesBody').innerHTML = `<div class="empty-modal"><div class="em-icon">⚠️</div><p>Error: ${res.message}</p></div>`;
                }
            } catch (e) {
                document.getElementById('modalDetallesBody').innerHTML = `<div class="empty-modal"><div class="em-icon">⚠️</div><p>Error al obtener detalles.</p></div>`;
            }
        }

        async function eliminarPedido(id) {
            if (!confirm('¡Peligro! ¿Estás totalmente seguro de eliminar permanentemente el pedido #' + id + ' y borrar todos sus archivos adjuntos? Esta acción es irreversible.')) return;

            try {
                const csrf = document.querySelector('meta[name="csrf-token"]').content;
                const r = await fetch(basePath + '/api/pedidos/eliminar', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ pedido_id: id, csrf_token: csrf })
                });
                const res = await r.json();
                if (res.status === 'success') {
                    if (window.BannerSounds) BannerSounds.eliminar();
                    cargarDatos();
                } else {
                    alert('Error: ' + res.message);
                }
            } catch (e) {
                alert('Ocurrió un error al intentar eliminar el pedido. Verifique su conexión y permisos.');
            }
        }

        async function revertirPedido(id) {
            if (!confirm('¿Deseas revertir el pedido #' + id + ' a estado PENDIENTE? Aparecerá nuevamente en el tablero Kanban.')) return;

            try {
                const csrf = document.querySelector('meta[name="csrf-token"]').content;
                const r = await fetch(basePath + '/api/kanban/revertir', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ pedido_id: id, csrf_token: csrf })
                });
                const res = await r.json();
                if (res.status === 'success') {
                    if (window.BannerSounds) BannerSounds.mover();
                    alert('Pedido revertido correctamente.');
                    cargarDatos();
                } else {
                    alert('Error: ' + res.message);
                }
            } catch (e) {
                alert('Ocurrió un error de conexión.');
            }
        }

        function cerrarModales() {
            document.querySelectorAll('.modal').forEach(m => m.style.display = 'none');
        }

        document.addEventListener('DOMContentLoaded', cargarDatos);
    </script>
    <script>
        window.BANNER_SOUND_CFG = { enabled: <?= $sonidoHabilitado === '1' ? 'true' : 'false'?>, theme: '<?= htmlspecialchars($sonidoTema)?>' };
    </script>
    <script src="<?= $basePath?>/js/sounds.js"></script>
</body>

</html>