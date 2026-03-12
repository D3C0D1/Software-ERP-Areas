<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['Admin', 'SuperAdmin'])) {
    header('Location: dashboard');
    exit;
}

require_once __DIR__ . '/../config/Database.php';
$db = \Config\Database::getInstance();

// Agregar columna icono si no existe (auto-migración)
try {
    $db->exec("ALTER TABLE areas ADD COLUMN icono MEDIUMTEXT DEFAULT NULL AFTER descripcion");
}
catch (\Exception $e) {
}

$stmt = $db->query("SELECT * FROM areas ORDER BY orden ASC");
$areasList = $stmt->fetchAll(PDO::FETCH_ASSOC);

$csrfToken = \App\Middlewares\CsrfMiddleware::generateToken();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="<?= $csrfToken?>">
    <title>Banner - Áreas</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4F46E5;
            --bg-color: #0F172A;
            --surface: rgba(30, 41, 59, 0.7);
            --border: rgba(255, 255, 255, 0.1);
            --text-main: #F8FAFC;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
            color: var(--text-main);
        }

        body {
            background-color: var(--bg-color);
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        /* Sidebar components provide their own isolated style */

        .main-content {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
        }

        .btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: var(--surface);
            border-radius: 8px;
            overflow: hidden;
        }

        th,
        td {
            padding: 12px;
            border-bottom: 1px solid var(--border);
            text-align: left;
        }

        th {
            background: rgba(0, 0, 0, 0.2);
            font-weight: 600;
            color: #94A3B8;
        }

        .icon-options {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 8px;
        }

        .icon-btn {
            font-size: 1.4rem;
            padding: 4px 8px;
            border-radius: 6px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.04);
            cursor: pointer;
            transition: background 0.2s;
        }

        .icon-btn:hover,
        .icon-btn.selected {
            background: rgba(99, 102, 241, 0.35);
            border-color: #6366f1;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            justify-content: center;
            align-items: center;
            z-index: 100;
        }

        .modal-content {
            background: #1E293B;
            padding: 30px;
            border-radius: 12px;
            width: 400px;
            border: 1px solid var(--border);
        }

        .form-control {
            width: 100%;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid var(--border);
            background: rgba(0, 0, 0, 0.2);
            margin-top: 5px;
        }

        .form-group {
            margin-bottom: 15px;
        }
    </style>
</head>

<body>

    <?php include __DIR__ . '/components/sidebar.php'; ?>

    <main class="main-content">
        <div
            style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border); padding-bottom: 15px; margin-bottom: 20px;">
            <h1>⚙️ Gestión de Áreas / Módulos Kanban</h1>
            <button class="btn" onclick="openModal()">+ Nueva Área</button>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Ícono</th>
                    <th>Nombre (Estación)</th>
                    <th>Descripción</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($areasList as $ar): ?>
                <tr>
                    <td>#
                        <?= $ar['id']?>
                    </td>
                    <td
                        style="font-size:1.5rem; text-align:center; display: flex; align-items: center; justify-content: center; height: 60px;">
                        <?= $ar['icono'] ?? ''?>
                    </td>
                    <td><strong>
                            <?= htmlspecialchars($ar['nombre'])?>
                        </strong></td>
                    <td style="color: #94A3B8;">
                        <?= htmlspecialchars($ar['descripcion'] ?: 'Sin descripción')?>
                    </td>
                    <td>
                        <button class="btn" style="background: #3B82F6;" onclick="editArea(<?= $ar['id']?>, '<?= htmlspecialchars($ar['nombre'])?>',
                            '<?= htmlspecialchars($ar['descripcion'])?>',
                            '<?= htmlspecialchars($ar['icono'] ?? '')?>')">Editar</button>
                        <button class="btn" style="background: #ef4444; margin-left:6px;"
                            onclick="confirmDeleteArea(<?= $ar['id']?>, '<?= htmlspecialchars($ar['nombre'])?>')">🗑
                            Eliminar</button>
                    </td>
                </tr>
                <?php
endforeach; ?>
                <?php if (empty($areasList)): ?>
                <tr>
                    <td colspan="4" style="text-align: center;">No hay áreas configuradas. Todas las de la foto fueron
                        borradas.</td>
                </tr>
                <?php
endif; ?>
            </tbody>
        </table>
    </main>

    <!-- Modal Form -->
    <div id="modalArea" class="modal">
        <div class="modal-content">
            <h2 id="modalTitle" style="margin-bottom: 20px;">Crear Área</h2>
            <form onsubmit="saveArea(event)">
                <input type="hidden" id="areaId">
                <div class="form-group">
                    <label>Nombre del Módulo (Ej. Bordado)</label>
                    <input type="text" id="nombre" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Descripción</label>
                    <textarea id="descripcion" class="form-control" rows="2"></textarea>
                </div>
                <div class="form-group">
                    <label>Ícono del Área</label>
                    <input type="text" id="icono" class="form-control" placeholder="Ej: ✂️ 🖨️ 👕 📅"
                        style="font-size:1.3rem;">
                    <div class="icon-options">
                        <?php
$emojis = ['✂️', '🖨️', '👕', '🎨', '📦', '⚙️', '🔧', '🪡', '🌟', '📋', '🏭', '⚡', '🔍', '🧵', '📐', '🎁'];
foreach ($emojis as $em)
    echo "<button type='button' class='icon-btn' onclick=\"selectIcon('$em')\">$em</button>"; ?>
                    </div>
                    <p style="font-size:0.8rem; color:#94a3b8; margin-top:4px;">Escribe un emoji o selecciona uno
                        arriba.</p>
                </div>
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="button" class="btn" style="background:#475569;"
                        onclick="document.getElementById('modalArea').style.display='none'">Cancelar</button>
                    <button type="submit" class="btn">Guardar Área</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal confirmar eliminación de área -->
    <div id="modalDeleteArea"
        style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.65); z-index:2000; align-items:center; justify-content:center;">
        <div
            style="background:#1e293b; border:1px solid rgba(255,255,255,.15); border-radius:18px; padding:32px 30px; max-width:440px; width:90%; text-align:center; box-shadow:0 20px 60px rgba(0,0,0,.7);">
            <div style="font-size:3rem; margin-bottom:12px;">⚠️</div>
            <h2 style="color:#f8fafc; margin-bottom:8px;" id="deleteAreaTitle">¿Eliminar área?</h2>
            <p style="color:#94a3b8; margin-bottom:16px;" id="deleteAreaMsg">Esta acción no se puede deshacer.</p>
            <div id="deleteAreaPedidosWarn"
                style="display:none; background:rgba(239,68,68,.12); border:1px solid rgba(239,68,68,.35); border-radius:10px; padding:12px; margin-bottom:16px; color:#fca5a5; font-size:.9rem; text-align:left; line-height:1.6;">
            </div>
            <div style="display:flex; gap:12px; justify-content:center;">
                <button onclick="document.getElementById('modalDeleteArea').style.display='none';"
                    style="background:#475569; color:#fff; border:none; border-radius:10px; padding:10px 24px; font-size:1rem; cursor:pointer;">Cancelar</button>
                <button id="btnConfirmDelete"
                    style="background:#ef4444; color:#fff; border:none; border-radius:10px; padding:10px 24px; font-size:1rem; cursor:pointer; font-weight:700;">Sí,
                    Eliminar</button>
            </div>
        </div>
    </div>

    <script>
        var _deleteAreaId = null;
        var csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        var basePath = window.location.pathname.replace(/\/admin-areas\/?$/i, '');

        function openModal() {
            document.getElementById('modalTitle').innerText = "Crear Nuevo Módulo Productivo";
            document.getElementById('areaId').value = '';
            document.getElementById('nombre').value = '';
            document.getElementById('descripcion').value = '';
            document.getElementById('icono').value = '';
            document.querySelectorAll('.icon-btn').forEach(b => b.classList.remove('selected'));
            document.getElementById('modalArea').style.display = 'flex';
        }

        function editArea(id, nom, desc, ico = '') {
            document.getElementById('modalTitle').innerText = "Modificar Módulo";
            document.getElementById('areaId').value = id;
            document.getElementById('nombre').value = nom;
            document.getElementById('descripcion').value = desc;
            document.getElementById('icono').value = ico;
            document.querySelectorAll('.icon-btn').forEach(b => {
                b.classList.toggle('selected', b.textContent.trim() === ico.trim());
            });
            document.getElementById('modalArea').style.display = 'flex';
        }

        function selectIcon(emoji) {
            document.getElementById('icono').value = emoji;
            document.querySelectorAll('.icon-btn').forEach(b => {
                b.classList.toggle('selected', b.textContent.trim() === emoji.trim());
            });
        }

        async function saveArea(e) {
            e.preventDefault();
            const id = document.getElementById('areaId').value;
            const payload = {
                nombre: document.getElementById('nombre').value,
                descripcion: document.getElementById('descripcion').value,
                csrf_token: csrfToken
            };

            const endpoint = id ? '/api/areas/editar' : '/api/areas/crear';
            if (id) payload.id = id;
            payload.icono = document.getElementById('icono').value;

            try {
                const req = await fetch(basePath + endpoint, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const res = await req.json();
                handleApiResponse(res, () => {
                    alert('Cambios guardados con éxito.');
                    window.location.reload();
                });
            } catch (e) {
                alert('Error de conexión.');
            }
        }

        var _deleteAreaId = null;

        function confirmDeleteArea(id, nombre) {
            _deleteAreaId = id;
            const modal = document.getElementById('modalDeleteArea');
            const warn = document.getElementById('deleteAreaPedidosWarn');
            document.getElementById('deleteAreaTitle').textContent = 'Eliminar: ' + nombre;
            document.getElementById('deleteAreaMsg').textContent = '¿Estás seguro que deseas eliminar esta área? Esta acción no se puede deshacer.';
            warn.style.display = 'none';
            warn.innerHTML = '';
            document.getElementById('btnConfirmDelete').disabled = false;
            document.getElementById('btnConfirmDelete').textContent = 'Sí, Eliminar';
            modal.style.display = 'flex';
        }

        document.getElementById('btnConfirmDelete').addEventListener('click', async function () {
            if (!_deleteAreaId) return;
            const warn = document.getElementById('deleteAreaPedidosWarn');
            const modal = document.getElementById('modalDeleteArea');
            const btn = this;
            btn.textContent = 'Eliminando...';
            btn.disabled = true;
            try {
                const res = await fetch(basePath + '/api/areas/eliminar', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: _deleteAreaId, csrf_token: csrfToken })
                });
                const data = await res.json();
                handleApiResponse(data, () => {
                    modal.style.display = 'none';
                    window.location.reload();
                });
                if (data.status !== 'success') {
                    warn.style.display = 'block';
                    warn.innerHTML = '🚫 <strong>' + data.message + '</strong>';
                    btn.textContent = 'Sí, Eliminar';
                    btn.disabled = false;
                }
            } catch (err) {
                warn.style.display = 'block';
                warn.textContent = 'Error de conexión: ' + err.message;
                btn.textContent = 'Sí, Eliminar';
                btn.disabled = false;
            }
        });
    </script>
</body>