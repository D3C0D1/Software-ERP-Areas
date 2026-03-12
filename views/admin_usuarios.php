<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['Admin', 'SuperAdmin'])) {
    header('Location: dashboard');
    exit;
}

require_once __DIR__ . '/../config/Database.php';
$db = \Config\Database::getInstance();

$stmtU = $db->query("SELECT u.id, u.nombre, u.email, u.estado, u.ver_precios, u.editar_pedidos, u.crear_enviar_pedidos, u.devolver_pedidos, u.ver_metricas_recepcion, r.nombre AS rol_nombre, r.id AS rol_id FROM usuarios u JOIN roles r ON u.rol_id = r.id");
$usuarios = $stmtU->fetchAll(PDO::FETCH_ASSOC);

// Cargar Todas las Áreas
$stmtA = $db->query("SELECT id, nombre FROM areas WHERE estado = 1 ORDER BY orden ASC");
$areas = $stmtA->fetchAll(PDO::FETCH_ASSOC);

// Cargar Asignaciones Actuales
$stmtUA = $db->query("SELECT usuario_id, area_id FROM usuario_areas");
$asignaciones = [];
while ($row = $stmtUA->fetch(PDO::FETCH_ASSOC)) {
    $asignaciones[$row['usuario_id']][] = $row['area_id'];
}

// Cargar Todos los Roles
$stmtR = $db->query("SELECT id, nombre FROM roles WHERE estado = 1");
$roles = $stmtR->fetchAll(PDO::FETCH_ASSOC);

$csrfToken = \App\Middlewares\CsrfMiddleware::generateToken();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="<?= $csrfToken?>">
    <title>Banner - Usuarios y Permisos</title>
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
            min-width: 450px;
            max-height: 90vh;
            overflow-y: auto;
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

        .checkbox-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: 10px;
        }

        .check-item {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.05);
            padding: 8px;
            border-radius: 6px;
        }
    </style>
</head>

<body>

    <?php include __DIR__ . '/components/sidebar.php'; ?>

    <main class="main-content">
        <div
            style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border); padding-bottom: 15px; margin-bottom: 20px;">
            <h1>&#128101; Control de Usuarios y Áreas Permitidas</h1>
            <button class="btn" onclick="openCrearModal()">+ Nuevo Usuario</button>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 50px;"></th>
                    <th>Nombre y Accesos</th>
                    <th>Email / Usuario</th>
                    <th>Rol en el Sistema</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $user): ?>
                <tr>
                    <td>
                        <div
                            style="width: 40px; height: 40px; border-radius: 50%; overflow: hidden; background: #4F46E5; display: flex; align-items: center; justify-content: center;">
                            <?php if (!empty($user['foto_perfil'])): ?>
                            <img src="<?= htmlspecialchars($user['foto_perfil'])?>"
                                style="width: 100%; height: 100%; object-fit: cover;">
                            <?php
    else: ?>
                            <span style="font-weight: bold;">
                                <?= strtoupper(substr($user['nombre'], 0, 1))?>
                            </span>
                            <?php
    endif; ?>
                        </div>
                    </td>
                    <td>
                        <strong>
                            <?= htmlspecialchars($user['nombre'])?>
                        </strong><br>
                        <?php
    $misA = $asignaciones[$user['id']] ?? [];
    if (in_array($user['rol_nombre'], ['Admin', 'SuperAdmin']))
        echo '<span style="font-size:0.8rem; color:#6EE7B7;">Acceso Administrativo Global</span>';
    else
        echo '<span style="font-size:0.8rem; color:#94A3B8;">Áreas: ' . count($misA) . ' Asignadas</span>';
    if ($user['ver_precios'])
        echo '<br><span style="font-size:0.75rem; color:#F59E0B; font-weight:bold;">🔍 Puede ver precios</span>';
    if ($user['editar_pedidos'] || in_array($user['rol_nombre'], ['Admin', 'SuperAdmin']))
        echo '<br><span style="font-size:0.75rem; color:#3B82F6; font-weight:bold;">✏️ Puede editar pedidos</span>';
    if ($user['crear_enviar_pedidos'] || in_array($user['rol_nombre'], ['Admin', 'SuperAdmin']))
        echo '<br><span style="font-size:0.75rem; color:#10B981; font-weight:bold;">🚀 Puede crear y enviar pedidos</span>';
    if ($user['devolver_pedidos'] || in_array($user['rol_nombre'], ['Admin', 'SuperAdmin']))
        echo '<br><span style="font-size:0.75rem; color:#EF4444; font-weight:bold;">↩️ Puede revertir envíos</span>';
    if ($user['ver_metricas_recepcion'] || in_array($user['rol_nombre'], ['Admin', 'SuperAdmin']))
        echo '<br><span style="font-size:0.75rem; color:#8B5CF6; font-weight:bold;">📊 Puede ver métricas en recepción</span>';
?>
                    </td>
                    <td>
                        <?= htmlspecialchars($user['email'])?>
                    </td>
                    <td>
                        <?= htmlspecialchars($user['rol_nombre'])?>
                    </td>
                    <td>
                        <div style="display:flex; gap:10px;">
                            <button class="btn" style="background: #10B981;"
                                onclick='editUser(<?= json_encode($user)?>, <?= json_encode($misA)?>)'>Editar</button>
                            <button class="btn btn-danger"
                                onclick="eliminarUsuario(<?= $user['id']?>)">Eliminar</button>
                        </div>
                    </td>
                </tr>
                <?php
endforeach; ?>
            </tbody>
        </table>
    </main>

    <!-- Modal Editar Usuario -->
    <div id="modalUser" class="modal">
        <div class="modal-content">
            <h2 id="modalTitle" style="margin-bottom: 20px;">Permisos de Operador</h2>
            <form onsubmit="saveUser(event)" enctype="multipart/form-data">
                <input type="hidden" id="userId">

                <div
                    style="display: flex; gap: 20px; align-items: center; margin-bottom: 20px; background: rgba(255,255,255,0.03); padding: 15px; border-radius: 12px;">
                    <div id="editAvatarWrap"
                        style="width: 70px; height: 70px; border-radius: 50%; border: 2px solid #4F46E5; flex-shrink: 0; overflow: hidden; background: #000; display: flex; align-items: center; justify-content: center; cursor: pointer;"
                        onclick="document.getElementById('editFotoInput').click()">
                        <img id="editAvatarImg" src=""
                            style="width: 100%; height: 100%; object-fit: cover; display: none;">
                        <span id="editAvatarInitial" style="font-size: 1.8rem; font-weight: 800; color: #fff;"></span>
                    </div>
                    <div>
                        <input type="file" id="editFotoInput" accept="image/*" style="display: none;">
                        <button type="button" class="btn"
                            style="background: rgba(79, 70, 229, 0.2); color: #818cf8; font-size: 0.8rem;"
                            onclick="document.getElementById('editFotoInput').click()">Cambiar Foto</button>
                    </div>
                </div>

                <div class="form-group">
                    <label>Nombre del Colaborador</label>
                    <input type="text" id="nombre" class="form-control" required maxlength="50">
                </div>

                <div class="form-group">
                    <label>Nueva Contraseña <small
                            style="color: #64748b; font-weight: normal;">(Opcional)</small></label>
                    <input type="password" id="editPassword" class="form-control"
                        placeholder="Dejar vacío para no cambiar">
                </div>

                <div class="form-group">
                    <label>Rol Principal</label>
                    <select id="rol_id" class="form-control">
                        <?php foreach ($roles as $r): ?>
                        <option value="<?= $r['id']?>">
                            <?= htmlspecialchars($r['nombre'])?>
                        </option>
                        <?php
endforeach; ?>
                    </select>
                </div>
                <div class="form-group"
                    style="margin-top: 15px; background: rgba(245, 158, 11, 0.1); padding: 10px; border-radius: 6px; border: 1px solid rgba(245, 158, 11, 0.3);">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; margin-bottom: 8px;">
                        <input type="checkbox" id="ver_precios_chk">
                        <strong>Permitir visualizar precios, pagos y saldos de los pedidos</strong>
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" id="editar_pedidos_chk">
                        <strong>Permitir editar pedidos</strong>
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; margin-top: 8px;">
                        <input type="checkbox" id="crear_enviar_pedidos_chk">
                        <strong>Permitir crear y enviar pedidos</strong>
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; margin-top: 8px;">
                        <input type="checkbox" id="devolver_pedidos_chk">
                        <strong>Permitir devolver el pedido despues de entregar</strong>
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; margin-top: 8px;">
                        <input type="checkbox" id="ver_metricas_recepcion_chk">
                        <strong>Permitir ver la recaudación y facturación en recepción</strong>
                    </label>
                </div>
                <h3 style="margin: 20px 0 10px; font-size:1rem; border-top:1px solid var(--border); padding-top:10px;">
                    Áreas Productivas Asignadas</h3>
                <p style="font-size:0.8rem; color:#94A3B8; margin-bottom: 15px;">Selecciona en qué columnas de Kanban
                    puede mover tarjetas este operador.</p>
                <div class="checkbox-grid">
                    <?php foreach ($areas as $ar): ?>
                    <label class="check-item">
                        <input type="checkbox" name="areas[]" value="<?= $ar['id']?>">
                        <?= htmlspecialchars($ar['nombre'])?>
                    </label>
                    <?php
endforeach; ?>
                </div>
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="button" class="btn" style="background:#475569;"
                        onclick="document.getElementById('modalUser').style.display='none'">Cancelar</button>
                    <button type="submit" class="btn">Actualizar Usuario</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Crear Usuario -->
    <div id="modalCrear" class="modal">
        <div class="modal-content">
            <h2 style="margin-bottom: 20px;">&#128100; Nuevo Usuario</h2>
            <form onsubmit="crearUsuario(event)">
                <div class="form-group">
                    <label>Nombre Completo</label>
                    <input type="text" id="createNombre" class="form-control" required placeholder="Ej: Ana Martínez"
                        maxlength="50">
                </div>
                <div class="form-group">
                    <label>Usuario o Correo</label>
                    <input type="text" id="createEmail" class="form-control" required
                        placeholder="Ej: oper01 o ana@empresa.com">
                </div>
                <div class="form-group">
                    <label>Contraseña</label>
                    <input type="password" id="createPassword" class="form-control" required
                        placeholder="Mínimo 6 caracteres">
                </div>
                <div class="form-group">
                    <label>Rol Principal</label>
                    <select id="createRolId" class="form-control">
                        <?php foreach ($roles as $r): ?>
                        <option value="<?= $r['id']?>" <?=$r['nombre']==='Operador' ? 'selected' : '' ?>>
                            <?= htmlspecialchars($r['nombre'])?>
                        </option>
                        <?php
endforeach; ?>
                    </select>
                </div>
                <div class="form-group"
                    style="margin-top: 15px; background: rgba(245, 158, 11, 0.1); padding: 10px; border-radius: 6px; border: 1px solid rgba(245, 158, 11, 0.3);">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; margin-bottom: 8px;">
                        <input type="checkbox" id="createVerPrecios">
                        <strong>Permitir visualizar precios, pagos y saldos</strong>
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" id="createEditarPedidos">
                        <strong>Permitir editar pedidos</strong>
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; margin-top: 8px;">
                        <input type="checkbox" id="createCrearEnviarPedidos">
                        <strong>Permitir crear y enviar pedidos</strong>
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; margin-top: 8px;">
                        <input type="checkbox" id="createDevolverPedidos">
                        <strong>Permitir devolver el pedido despues de entregar</strong>
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; margin-top: 8px;">
                        <input type="checkbox" id="createVerMetricasRecepcion">
                        <strong>Permitir ver la recaudación y facturación en recepción</strong>
                    </label>
                </div>
                <h3 style="margin: 20px 0 10px; font-size:1rem; border-top:1px solid var(--border); padding-top:10px;">
                    Áreas Productivas Asignadas</h3>
                <div class="checkbox-grid">
                    <?php foreach ($areas as $ar): ?>
                    <label class="check-item">
                        <input type="checkbox" name="create_areas[]" value="<?= $ar['id']?>">
                        <?= htmlspecialchars($ar['nombre'])?>
                    </label>
                    <?php
endforeach; ?>
                </div>
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="button" class="btn" style="background:#475569;"
                        onclick="document.getElementById('modalCrear').style.display='none'">Cancelar</button>
                    <button type="submit" class="btn" style="background:#10B981;">&#10003; Crear Usuario</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const basePath = window.location.pathname
            .replace(/\/index\.php\/admin-usuarios\/?/i, '/index.php')
            .replace(/\/admin-usuarios\/?$/i, '');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        function openCrearModal() {
            document.getElementById('createNombre').value = '';
            document.getElementById('createEmail').value = '';
            document.getElementById('createPassword').value = '';
            document.getElementById('createVerPrecios').checked = false;
            document.getElementById('createEditarPedidos').checked = false;
            document.getElementById('createCrearEnviarPedidos').checked = false;
            document.getElementById('createDevolverPedidos').checked = false;
            document.getElementById('createVerMetricasRecepcion').checked = false;
            document.querySelectorAll('input[name="create_areas[]"]').forEach(cb => cb.checked = false);
            document.getElementById('modalCrear').style.display = 'flex';
        }

        function editUser(user, assignedAreas) {
            document.getElementById('modalTitle').innerText = 'Accesos de ' + user.nombre;
            document.getElementById('userId').value = user.id;
            document.getElementById('nombre').value = user.nombre;
            document.getElementById('rol_id').value = user.rol_id;
            document.getElementById('ver_precios_chk').checked = (user.ver_precios == 1);
            document.getElementById('editar_pedidos_chk').checked = (user.editar_pedidos == 1);
            document.getElementById('crear_enviar_pedidos_chk').checked = (user.crear_enviar_pedidos == 1);
            document.getElementById('devolver_pedidos_chk').checked = (user.devolver_pedidos == 1);
            document.getElementById('ver_metricas_recepcion_chk').checked = (user.ver_metricas_recepcion == 1);
            document.getElementById('editPassword').value = '';
            document.getElementById('editFotoInput').value = '';

            // Avatar preview
            const img = document.getElementById('editAvatarImg');
            const initial = document.getElementById('editAvatarInitial');
            if (user.foto_perfil) {
                img.src = user.foto_perfil;
                img.style.display = 'block';
                initial.style.display = 'none';
            } else {
                img.style.display = 'none';
                initial.innerText = user.nombre.charAt(0).toUpperCase();
                initial.style.display = 'block';
            }

            document.querySelectorAll('input[name="areas[]"]').forEach(cb => {
                cb.checked = assignedAreas.includes(parseInt(cb.value));
            });

            document.getElementById('modalUser').style.display = 'flex';
        }

        // Preview de foto en edición
        document.getElementById('editFotoInput').addEventListener('change', function () {
            const file = this.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = function (e) {
                const img = document.getElementById('editAvatarImg');
                const initial = document.getElementById('editAvatarInitial');
                img.src = e.target.result;
                img.style.display = 'block';
                initial.style.display = 'none';
            };
            reader.readAsDataURL(file);
        });

        async function saveUser(e) {
            e.preventDefault();
            const btn = e.target.querySelector('button[type="submit"]');
            const id = document.getElementById('userId').value;
            const areasArray = Array.from(document.querySelectorAll('input[name="areas[]"]:checked'))
                .map(cb => parseInt(cb.value));

            const formData = new FormData();
            formData.append('usuario_id', id);
            formData.append('nombre', document.getElementById('nombre').value);
            formData.append('rol_id', document.getElementById('rol_id').value);
            formData.append('ver_precios', document.getElementById('ver_precios_chk').checked ? 1 : 0);
            formData.append('editar_pedidos', document.getElementById('editar_pedidos_chk').checked ? 1 : 0);
            formData.append('crear_enviar_pedidos', document.getElementById('crear_enviar_pedidos_chk').checked ? 1 : 0);
            formData.append('devolver_pedidos', document.getElementById('devolver_pedidos_chk').checked ? 1 : 0);
            formData.append('ver_metricas_recepcion', document.getElementById('ver_metricas_recepcion_chk').checked ? 1 : 0);
            formData.append('areas', JSON.stringify(areasArray));
            formData.append('csrf_token', csrfToken);

            const pw = document.getElementById('editPassword').value;
            if (pw) formData.append('nueva_password', pw);

            const foto = document.getElementById('editFotoInput').files[0];
            if (foto) formData.append('foto', foto);

            btn.disabled = true;
            btn.textContent = 'Guardando...';

            try {
                const req = await fetch(basePath + '/api/usuarios/editar-admin', {
                    method: 'POST',
                    body: formData
                });
                const res = await req.json();
                handleApiResponse(res, () => {
                    alert('Usuario actualizado correctamente.');
                    window.location.reload();
                });
            } catch (err) {
                alert('Error de conexión: ' + err.message);
            } finally {
                btn.disabled = false;
                btn.textContent = 'Actualizar Usuario';
            }
        }

        async function crearUsuario(e) {
            e.preventDefault();
            const btn = e.target.querySelector('button[type="submit"]');
            const areasArray = Array.from(document.querySelectorAll('input[name="create_areas[]"]:checked'))
                .map(cb => parseInt(cb.value));

            const payload = {
                nombre: document.getElementById('createNombre').value,
                email: document.getElementById('createEmail').value,
                password: document.getElementById('createPassword').value,
                rol_id: document.getElementById('createRolId').value,
                ver_precios: document.getElementById('createVerPrecios').checked ? 1 : 0,
                editar_pedidos: document.getElementById('createEditarPedidos').checked ? 1 : 0,
                crear_enviar_pedidos: document.getElementById('createCrearEnviarPedidos').checked ? 1 : 0,
                devolver_pedidos: document.getElementById('createDevolverPedidos').checked ? 1 : 0,
                ver_metricas_recepcion: document.getElementById('createVerMetricasRecepcion').checked ? 1 : 0,
                areas: areasArray,
                csrf_token: csrfToken
            };

            btn.disabled = true;
            btn.textContent = 'Creando...';

            try {
                const req = await fetch(basePath + '/api/usuarios/crear', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const res = await req.json();
                handleApiResponse(res, () => {
                    alert('Usuario creado correctamente.');
                    window.location.reload();
                });
            } catch (err) {
                alert('Error de conexión: ' + err.message);
            } finally {
                btn.disabled = false;
                btn.textContent = 'Crear Usuario';
            }
        }
        async function eliminarUsuario(userId) {
            if (!confirm('¿Estás seguro de que deseas eliminar este usuario? Esta acción es irreversible.')) return;

            try {
                const req = await fetch(basePath + '/api/usuarios/eliminar', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ usuario_id: userId, csrf_token: csrfToken })
                });
                const res = await req.json();
                handleApiResponse(res, () => {
                    alert('Usuario eliminado correctamente.');
                    window.location.reload();
                });
            } catch (err) {
                alert('Error de conexión: ' + err.message);
            }
        }
    </script>
</body>

</html>