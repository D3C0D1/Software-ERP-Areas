<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes y Movimientos |
        <?= htmlspecialchars($empresaNombre ?? 'ERP')?>
    </title>
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? ''?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-dark: #0f172a;
            --surface: #1e293b;
            --primary: #4f46e5;
            --text: #f8fafc;
            --muted: #94a3b8;
            --border: rgba(255, 255, 255, 0.08);
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
            position: relative;
            z-index: 1;
        }
        @media (max-width: 768px) {
            .main-content { padding: 15px; }
        }

        .header {
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 1.8rem;
            font-weight: 700;
        }

        .header p {
            color: var(--muted);
            font-size: 0.95rem;
            margin-top: 5px;
        }

        /* Filtros */
        .filters-container {
            background: var(--surface);
            padding: 20px;
            border-radius: 12px;
            border: 1px solid var(--border);
            margin-bottom: 24px;
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
            min-width: 200px;
        }

        .form-control:focus {
            border-color: var(--primary);
        }

        .btn {
            background: var(--primary);
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 600;
            transition: opacity 0.2s;
            height: 40px;
        }

        .btn:hover {
            opacity: 0.9;
        }

        /* Tabla */
        .table-container {
            background: var(--surface);
            border-radius: 12px;
            border: 1px solid var(--border);
            overflow-x: auto;
            width: 100%;
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
            background: rgba(255, 255, 255, 0.02);
        }

        .badge {
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-info {
            background: rgba(59, 130, 246, 0.2);
            color: #60a5fa;
            border: 1px solid rgba(59, 130, 246, 0.3);
        }

        .badge-success {
            background: rgba(16, 185, 129, 0.2);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .badge-warning {
            background: rgba(245, 158, 11, 0.2);
            color: #fbbf24;
            border: 1px solid rgba(245, 158, 11, 0.3);
        }

        .empty-state {
            padding: 40px;
            text-align: center;
            color: var(--muted);
        }

        /* Auditoría Detallada */
        .diff-container {
            font-size: 0.8rem;
            margin-top: 8px;
            background: rgba(0, 0, 0, 0.3);
            border-radius: 6px;
            padding: 8px;
            border-left: 3px solid var(--primary);
        }

        .diff-item {
            display: flex;
            flex-direction: column;
            gap: 4px;
            margin-bottom: 8px;
        }

        .diff-item:last-child {
            margin-bottom: 0;
        }

        .diff-label {
            font-weight: 700;
            color: var(--muted);
            text-transform: uppercase;
            font-size: 0.65rem;
        }

        .diff-content {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .val-old {
            color: #f87171;
            text-decoration: line-through;
            background: rgba(239, 68, 68, 0.1);
            padding: 2px 4px;
            border-radius: 4px;
        }

        .val-new {
            color: #34d399;
            background: rgba(16, 185, 129, 0.1);
            padding: 2px 4px;
            border-radius: 4px;
            font-weight: 600;
        }

        .diff-arrow {
            color: var(--muted);
            font-size: 0.8rem;
        }
    </style>
</head>

<body>
    <?php include __DIR__ . '/components/sidebar.php'; ?>

    <main class="main-content">
        <div class="header">
            <div style="display:flex; align-items:center; gap:16px;">
                <button id="btnHamburger"
                    style="background:none; border:none; color:#f1f5f9; cursor:pointer; font-size:1.5rem; display:flex;">☰</button>
                <div>
                    <h1>Reportes y Auditoría</h1>
                    <p>Registro de movimientos, cambios de configuración y seguimiento de pedidos.</p>
                </div>
            </div>
        </div>

        <div class="filters-container">
            <div class="form-group">
                <label>Filtro por Acción</label>
                <select id="filtroAccion" class="form-control">
                    <option value="">Todas las acciones</option>
                    <option value="crear">Creación (Pedido/Usuario)</option>
                    <option value="actualizar">Modificación o Edición</option>
                    <option value="eliminar">Eliminación</option>
                    <option value="abrio_tracking">Apertura de Guía por Cliente</option>
                    <option value="onurix_cambio">Cambio Clave Onurix / SMS</option>
                </select>
            </div>

            <div class="form-group">
                <label>Filtro por ID Pedido</label>
                <input type="number" id="filtroPedido" class="form-control" placeholder="Ej. 1045">
            </div>

            <div class="form-group">
                <label>Usuario Responsable</label>
                <input type="text" id="filtroUsuario" class="form-control" placeholder="Buscar nombre...">
            </div>

            <button class="btn" onclick="cargarMovimientos()">Aplicar Filtros</button>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Fecha y Hora</th>
                        <th>Usuario</th>
                        <th>Acción</th>
                        <th>Descripción Detallada</th>
                        <th>Cambios (Anterior → Nuevo)</th>
                        <th>Entidad</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody id="tablaBody">
                    <tr>
                        <td colspan="6" class="empty-state">Cargando registros...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </main>

    <script>
        const basePath = window.location.pathname.replace(/\/reporte-movimientos\/?$/i, '');

        async function cargarMovimientos() {
            const tbody = document.getElementById('tablaBody');
            tbody.innerHTML = '<tr><td colspan="7" class="empty-state">Cargando registros...</td></tr>';

            const accion = document.getElementById('filtroAccion').value;
            const pedido = document.getElementById('filtroPedido').value;
            const usuario = document.getElementById('filtroUsuario').value;

            const url = new URL(basePath + '/api/reportes/movimientos', window.location.origin);
            if (accion) url.searchParams.append('accion', accion);
            if (pedido) url.searchParams.append('pedido_id', pedido);
            if (usuario) url.searchParams.append('usuario', usuario);

            try {
                const r = await fetch(url.toString(), {
                    cache: 'no-cache'
                });
                const res = await r.json();

                if (res.status === 'success') {
                    renderizarTabla(res.data);
                } else {
                    tbody.innerHTML = `<tr><td colspan="7" class="empty-state" style="color:#ef4444;">Error: ${res.message}</td></tr>`;
                }
            } catch (e) {
                tbody.innerHTML = `<tr><td colspan="7" class="empty-state" style="color:#ef4444;">Error de conexión.</td></tr>`;
            }
        }

        function getBadge(accion) {
            if (accion.includes('crear') || accion === 'abrio_tracking') return 'badge-success';
            if (accion.includes('eliminar')) return 'badge-warning';
            return 'badge-info';
        }

        function renderizarTabla(data) {
            const tbody = document.getElementById('tablaBody');
            tbody.innerHTML = '';

            if (data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="7" class="empty-state">No se encontraron registros con estos filtros.</td></tr>`;
                return;
            }

            data.forEach(item => {
                const tr = document.createElement('tr');

                let entidadTxt = item.entidad_tipo ? item.entidad_tipo.toUpperCase() : 'SISTEMA';
                if (item.entidad_id && item.entidad_id != 0) {
                    entidadTxt += ` #${item.entidad_id}`;
                    if (item.cliente_nombre) {
                        entidadTxt += `<br><small style="color:var(--muted);">${item.cliente_nombre}</small>`;
                    }
                }

                let usuarioTxt = item.usuario_nombre ? `<strong>${item.usuario_nombre}</strong><br><small style="color:var(--muted);">${item.usuario_rol}</small>` : '<span style="color:var(--muted); font-style:italic;">Sistema / Cliente Externo</span>';

                // Procesar Diffs (data_anterior vs data_nueva)
                let diffsHtml = '<span style="color:var(--muted); font-style:italic; font-size:0.8rem;">Sin cambios técnicos registrados</span>';
                try {
                    if (item.data_anterior || item.data_nueva) {
                        const oldData = item.data_anterior ? JSON.parse(item.data_anterior) : {};
                        const newData = item.data_nueva ? JSON.parse(item.data_nueva) : {};

                        // Si es un string directo (no objeto)
                        if (typeof oldData !== 'object' || typeof newData !== 'object') {
                            diffsHtml = `
                                <div class="diff-container">
                                    <div class="diff-content">
                                        <span class="val-old">${item.data_anterior || 'Ø'}</span>
                                        <span class="diff-arrow">→</span>
                                        <span class="val-new">${item.data_nueva || 'Ø'}</span>
                                    </div>
                                </div>`;
                        } else {
                            // Comparar llaves
                            let changes = [];
                            const allKeys = new Set([...Object.keys(oldData), ...Object.keys(newData)]);

                            allKeys.forEach(k => {
                                if (JSON.stringify(oldData[k]) !== JSON.stringify(newData[k])) {
                                    changes.push(`
                                        <div class="diff-item">
                                            <span class="diff-label">${k.replace(/_/g, ' ')}</span>
                                            <div class="diff-content">
                                                <span class="val-old">${oldData[k] !== undefined ? oldData[k] : 'Ø'}</span>
                                                <span class="diff-arrow">→</span>
                                                <span class="val-new">${newData[k] !== undefined ? newData[k] : 'Ø'}</span>
                                            </div>
                                        </div>
                                    `);
                                }
                            });

                            if (changes.length > 0) {
                                diffsHtml = `<div class="diff-container">${changes.join('')}</div>`;
                            } else if (item.data_nueva && !item.data_anterior) {
                                // Caso de creación: Mostrar todo lo que se insertó
                                let newFields = [];
                                Object.keys(newData).forEach(k => {
                                    newFields.push(`
                                        <div class="diff-item">
                                            <span class="diff-label">${k.replace(/_/g, ' ')}</span>
                                            <div class="diff-content">
                                                <span class="val-new">${newData[k] !== undefined && newData[k] !== null ? newData[k] : 'Ø'}</span>
                                            </div>
                                        </div>
                                    `);
                                });
                                diffsHtml = `<div class="diff-container"><div style="color:#34d399; font-weight:700; margin-bottom:8px; font-size:0.7rem;">[NUEVO REGISTRO]</div>${newFields.join('')}</div>`;
                            }
                        }
                    }
                } catch (err) {
                    // Si no es JSON, mostrar como texto plano si existe
                    if (item.data_anterior || item.data_nueva) {
                        diffsHtml = `
                            <div class="diff-container">
                                <div class="diff-content">
                                    <span class="val-old">${item.data_anterior || 'Ø'}</span>
                                    <span class="diff-arrow">→</span>
                                    <span class="val-new">${item.data_nueva || 'Ø'}</span>
                                </div>
                            </div>`;
                    }
                }

                tr.innerHTML = `
                    <td style="white-space:nowrap; color:var(--muted); font-size:0.8rem;">${item.created_at}</td>
                    <td>${usuarioTxt}</td>
                    <td><span class="badge ${getBadge(item.accion)}">${item.accion.toUpperCase()}</span></td>
                    <td style="max-width:250px; line-height:1.4; font-size:0.85rem;">${item.descripcion_accion}</td>
                    <td style="min-width:200px;">${diffsHtml}</td>
                    <td>${entidadTxt}</td>
                    <td style="font-family:monospace; color:var(--muted); font-size:0.75rem;">${item.ip_address || 'N/A'}</td>
                `;
                tbody.appendChild(tr);
            });
        }

        document.addEventListener('DOMContentLoaded', cargarMovimientos);
    </script>
</body>

</html>