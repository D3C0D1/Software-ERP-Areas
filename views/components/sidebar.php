<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();
require_once __DIR__ . '/../../config/Database.php';

$db = \Config\Database::getInstance();
$userId = $_SESSION['user_id'] ?? 0;
$role = $_SESSION['role'] ?? 'Operador';
$userName = ($userId == 1) ? 'Administrador' : ($_SESSION['email'] ?? 'Usuario');
$currentUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$currentAreaId = $_GET['area_id'] ?? null;
$sidebarBasePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');

// Cargar datos del usuario (incluyendo foto_perfil)
try {
    $stmtU = $db->prepare("SELECT nombre, email, foto_perfil FROM usuarios WHERE id = :id");
    $stmtU->execute(['id' => $userId]);
    $sidebarUser = $stmtU->fetch(PDO::FETCH_ASSOC);
    $sidebarFoto = $sidebarUser['foto_perfil'] ?? '';
    $sidebarNombre = $sidebarUser['nombre'] ?? $userName;
    $sidebarEmail = $sidebarUser['email'] ?? $userName;
}
catch (\Exception $e) {
    $sidebarFoto = '';
    $sidebarNombre = $userName;
    $sidebarEmail = $userName;
}

// Nombre y logo de empresa dinámico desde configuracion
try {
    $cfgRows = $db->query("SELECT clave, valor FROM configuracion WHERE clave IN (
        'empresa_nombre', 'empresa_logo', 
        'icon_dashboard', 'icon_recepcion', 'icon_reportes', 'icon_reportes_pedidos', 
        'icon_usuarios', 'icon_areas', 'icon_configuracion'
    )")->fetchAll(\PDO::FETCH_KEY_PAIR);

    $sidebarEmpresa = $cfgRows['empresa_nombre'] ?? 'Banner';
    $sidebarLogo = $cfgRows['empresa_logo'] ?? '';
}
catch (\Exception $e) {
    $sidebarEmpresa = 'Banner';
    $sidebarLogo = '';
    $cfgRows = [];
}

// Obtener las áreas a las que el usuario tiene acceso
try {
    if (in_array($role, ['Admin', 'SuperAdmin', 'Gerente'])) {
        $stmt = $db->query("SELECT id, nombre, icono FROM areas WHERE estado = 1 ORDER BY orden ASC");
        $misAreas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    else {
        $stmt = $db->prepare("SELECT a.id, a.nombre, a.icono FROM areas a JOIN usuario_areas ua ON a.id = ua.area_id WHERE ua.usuario_id = :uid AND a.estado = 1 ORDER BY a.orden ASC");
        $stmt->execute(['uid' => $userId]);
        $misAreas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
catch (\PDOException $e) {
    // Si no existe la columna icono, la creamos al vuelo para solucionar el error fatal en producción
    if (strpos($e->getMessage(), 'Column not found') !== false || strpos($e->getMessage(), 'Unknown column') !== false) {
        $db->exec("ALTER TABLE areas ADD COLUMN icono MEDIUMTEXT DEFAULT NULL AFTER descripcion");
        // Reintentar
        if (in_array($role, ['Admin', 'SuperAdmin', 'Gerente'])) {
            $stmt = $db->query("SELECT id, nombre, icono FROM areas WHERE estado = 1 ORDER BY orden ASC");
            $misAreas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        else {
            $stmt = $db->prepare("SELECT a.id, a.nombre, a.icono FROM areas a JOIN usuario_areas ua ON a.id = ua.area_id WHERE ua.usuario_id = :uid AND a.estado = 1 ORDER BY a.orden ASC");
            $stmt->execute(['uid' => $userId]);
            $misAreas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
    else {
        $misAreas = [];
    }
}

// Helper icons
$iconMap = [
    'Dashboard' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="m16 8-4.5 4.5"></path><path d="M12 16h.01"></path></svg>',
    'Recepción' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v2"></path><path d="M18 12c0-3.3-2.7-6-6-6S6 8.7 6 12"></path><path d="M4 12h16v6a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2z"></path><path d="M12 20v2"></path></svg>',
    'Bordado' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="6" cy="6" r="3"></circle><circle cx="6" cy="18" r="3"></circle><line x1="20" y1="4" x2="8.12" y2="15.88"></line><line x1="14.47" y1="14.48" x2="20" y2="20"></line><line x1="8.12" y1="8.12" x2="12" y2="12"></line></svg>',
    'Diseño' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m18 2-6 6"></path><path d="M12 18h6"></path><path d="m15 15 2 2-6 6H5v-6l6-6-2-2"></path></svg>',
    'Impresión' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>',
    'Sublimado' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m19 11-8-8-8.6 8.6a2 2 0 0 0 0 2.8l5.2 5.2c.8.8 2 .8 2.8 0L19 11Z"></path><path d="m5 2 5 5"></path><path d="M2 13h15"></path><path d="M22 20a2 2 0 1 1-4 0c0-1.6 1.7-2.4 2-4 .3 1.6 2 2.4 2 4Z"></path></svg>',
    'Confección' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.38 3.46 16 2a8 8 0 0 1-8 0L3.62 3.46a2 2 0 0 0-1.34 2.23l.58 3.47a1 1 0 0 0 .99.84H6v10c0 1.1.9 2 2 2h8a2 2 0 0 0 2-2V10h2.15a1 1 0 0 0 .99-.84l.58-3.47a2 2 0 0 0-1.34-2.23z"></path></svg>',
    'Calidad' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 7 17l-5-5"></path><path d="m22 10-7.5 7.5L13 16"></path></svg>',
    'Usuarios' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>',
    'Áreas' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>',
    'Configuración' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"></path><circle cx="12" cy="12" r="3"></circle></svg>',
    'Reportes Pedidos' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>',
    'Default' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 20h20"></path><path d="M5 20V8l7-6 7 6v12"></path></svg>'
];

// Helper para decidir si mostrar <img> o el valor directo (si es SVG)
$parseIcon = function ($val) {
    if (empty($val))
        return null;
    if (strpos($val, '<svg') !== false)
        return $val; // Es un SVG directo
    return '<img src="' . $val . '" style="width:20px;height:20px;object-fit:contain;filter:drop-shadow(0 0 1px rgba(255,255,255,0.2));">';
};

// Reemplazar íconos configurados
$mapKeys = [
    'icon_dashboard' => 'Dashboard',
    'icon_recepcion' => 'Recepción',
    'icon_reportes' => 'Reportes',
    'icon_reportes_pedidos' => 'Reportes Pedidos',
    'icon_usuarios' => 'Usuarios',
    'icon_areas' => 'Áreas',
    'icon_configuracion' => 'Configuración'
];

foreach ($mapKeys as $cfgKey => $mapKey) {
    $parsed = $parseIcon($cfgRows[$cfgKey] ?? '');
    if ($parsed)
        $iconMap[$mapKey] = $parsed;
}

function getIcon($name, $map)
{
    foreach ($map as $key => $icon) {
        if (stripos($name, $key) !== false || stripos($key, $name) !== false)
            return $icon;
    }
    return $map['Default'];
}
?>
<style>
    body {
        background-image: linear-gradient(rgba(15, 23, 42, 0.85), rgba(15, 23, 42, 0.85)), url('<?= htmlspecialchars($sidebarBasePath)?>/img/LEON.jpg') !important;
        background-size: cover !important;
        background-position: center !important;
        background-repeat: no-repeat !important;
        background-attachment: fixed !important;
    }

    .sidebar {
        width: 260px;
        background: rgba(15, 23, 42, 1);
        border-right: 1px solid rgba(255, 255, 255, 0.1);
        display: flex;
        flex-direction: column;
        padding: 20px 0;
        z-index: 10;
        height: 100vh;
        color: #F8FAFC;
    }

    .sidebar .brand {
        padding: 0 24px 20px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        margin-bottom: 20px;
    }

    .sidebar .brand h2 {
        font-weight: 700;
        font-size: 1.5rem;
        background: linear-gradient(to right, #6EE7B7, #3B82F6);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin: 0;
    }

    .sidebar .user-info {
        padding: 0 24px 20px;
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
    }

    .sidebar .avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #4F46E5;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 1.2rem;
        color: #FFF;
        overflow: hidden;
        flex-shrink: 0;
    }

    .sidebar .avatar img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        border-radius: 50%;
        background: rgba(255, 255, 255, .08);
        padding: 4px;
    }

    .sidebar .brand-logo-img {
        width: 36px;
        height: 36px;
        object-fit: contain;
        border-radius: 8px;
        background: rgba(255, 255, 255, .07);
        padding: 3px;
        flex-shrink: 0;
    }

    .sidebar .user-details h4 {
        font-size: 0.95rem;
        font-weight: 600;
        color: #F8FAFC;
        margin: 0;
    }

    .sidebar .user-details span {
        font-size: 0.8rem;
        color: #94A3B8;
    }

    .sidebar .nav-menu {
        list-style: none;
        flex: 1;
        padding: 0;
        margin: 0;
        overflow-y: auto;
    }

    .sidebar .nav-item {
        padding: 12px 24px;
        color: #94A3B8;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 12px;
        transition: all 0.3s;
        border-left: 3px solid transparent;
        font-size: 0.95rem;
    }

    .sidebar .nav-item:hover,
    .sidebar .nav-item.active {
        background: rgba(255, 255, 255, 0.05);
        color: #FFF;
        border-left-color: #4F46E5;
    }

    .sidebar .nav-item svg {
        flex-shrink: 0;
        color: #94A3B8;
        transition: color 0.3s;
    }

    .sidebar .nav-item.active svg,
    .sidebar .nav-item:hover svg {
        color: #4F46E5;
    }

    /* ── HAMBURGER MODES ── */
    .sidebar {
        transition: width 0.3s cubic-bezier(0.16, 1, 0.3, 1), transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .sidebar.mode-icon {
        width: 78px;
    }

    .sidebar.mode-icon .brand h2,
    .sidebar.mode-icon .user-details,
    .sidebar.mode-icon .nav-item span {
        display: none;
    }

    .sidebar.mode-icon .brand {
        padding: 0 10px 20px;
        justify-content: center;
    }

    .sidebar.mode-icon .brand-logo-img {
        margin: 0;
    }

    .sidebar.mode-icon .nav-item {
        justify-content: center !important;
        padding: 15px 0 !important;
        border-left: none !important;
    }

    .sidebar.mode-icon .nav-item svg {
        margin: 0 !important;
    }

    .sidebar.mode-icon svg[class^="chevron"] {
        display: none !important;
    }

    /* Ajustes específicos para móviles */
    @media (max-width: 768px) {
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            transform: translateX(-100%);
            transition: transform 0.3s ease;
            box-shadow: 10px 0 30px rgba(0,0,0,0.5);
        }
        .sidebar.mode-full {
            transform: translateX(0);
        }
        .sidebar.mode-icon {
            width: 80px;
            transform: translateX(0);
        }
        .sidebar.mode-hidden {
            transform: translateX(-100%);
        }
    }

    .sidebar-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 9;
        backdrop-filter: blur(2px);
    }
    
    @media (max-width: 768px) {
        .sidebar-overlay.active {
            display: block;
        }
    }
</style>
<aside class="sidebar">
    <div class="brand" style="display:flex;align-items:center;gap:10px;">
        <?php if ($sidebarLogo): ?>
        <img src="<?= htmlspecialchars($sidebarLogo)?>" class="brand-logo-img" alt="Logo">
        <?php
endif; ?>
        <h2>
            <?= htmlspecialchars($sidebarEmpresa)?>
        </h2>
    </div>
    <div class="user-info" onclick="openPerfilModal()" title="Editar mi perfil"
        style="cursor:pointer; transition: background 0.2s; border-radius: 10px; padding: 8px 24px 20px;">
        <div class="avatar" id="sidebarAvatar">
            <?php if (!empty($sidebarFoto)): ?>
            <img src="<?= htmlspecialchars($sidebarFoto)?>" alt="Foto de perfil" onerror="this.onerror=null; this.parentElement.innerHTML='<?= strtoupper(substr($sidebarNombre, 0, 1))?>';">
            <?php
else: ?>
            <?= strtoupper(substr($sidebarNombre, 0, 1))?>
            <?php
endif; ?>
        </div>
        <div class="user-details">
            <h4 style="text-overflow: ellipsis; overflow: hidden; white-space: nowrap; max-width: 150px;">
                <?= htmlspecialchars($sidebarNombre)?>
            </h4>
            <span>
                <?= htmlspecialchars($role)?>
            </span>
        </div>
    </div>
    <ul class="nav-menu">
        <li style="margin-bottom:15px; border-bottom:1px solid rgba(255,255,255,0.05); padding-bottom:10px;">
            <a href="<?= htmlspecialchars($sidebarBasePath)?>/dashboard"
                class="nav-item <?=(!$currentAreaId && strpos($currentUri, 'dashboard') !== false) ? 'active' : ''?>">
                <?= $iconMap['Dashboard']?> <span>Monitoreo</span>
            </a>
            <a href="<?= htmlspecialchars($sidebarBasePath)?>/recepcion"
                class="nav-item <?=(strpos($currentUri, 'recepcion') !== false) ? 'active' : ''?>">
                <?= $iconMap['Recepción']?> <span>Recepción</span>
            </a>
            <a href="<?= htmlspecialchars($sidebarBasePath)?>/whatsapp"
                class="nav-item <?=(strpos($currentUri, 'whatsapp') !== false) ? 'active' : ''?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg> <span>WhatsApp</span>
            </a>
        </li>

        <!-- Dinámico por área (Desplegable Acordeón) -->
        <li style="margin-top:5px; padding-top:5px; border-top:1px solid rgba(255,255,255,0.05);">
            <!-- Botón Colapsable -->
            <div class="nav-item" onclick="toggleAreasMenu(this)" style="cursor:pointer; justify-content: space-between; padding-right:20px; align-items: center;" id="btnAreasToggle">
                <div style="display:flex; align-items:center; gap:12px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #6EE7B7;"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>
                    <span style="font-weight: 600;">Áreas de Trabajo</span>
                </div>
                <svg class="chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="transition: transform 0.3s, color 0.3s; color:#94A3B8; <?= $currentAreaId ? 'transform: rotate(180deg);' : '' ?>"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </div>
            
            <!-- Lista interior colapsable -->
            <ul id="submenuAreas" style="display: <?= $currentAreaId ? 'block' : 'none' ?>; list-style:none; padding-left: 0; margin:0; margin-top:2px;">
                <?php foreach ($misAreas as $area): ?>
                <li>
                    <a href="<?= htmlspecialchars($sidebarBasePath)?>/kanban?area_id=<?= $area['id']?>"
                        class="nav-item <?=($currentAreaId == $area['id'] && strpos($currentUri, 'kanban') !== false) ? 'active' : ''?>" style="padding-left: 45px; padding-top: 10px; padding-bottom: 10px;">
                        <?php if (!empty($area['icono'])): ?>
                        <span
                            style="font-size:1.1rem; line-height:1; flex-shrink:0; display: flex; align-items: center; justify-content: center; width: 20px; height: 20px;">
                            <?= $area['icono']?>
                        </span>
                        <?php else: ?>
                        <span style="display:flex; align-items:center; justify-content:center; width:20px; height:20px; transform: scale(0.85);"><?= getIcon($area['nombre'], $iconMap)?></span>
                        <?php endif; ?>
                        <span>
                            <?= htmlspecialchars($area['nombre'])?>
                        </span>
                    </a>
                </li>
                <?php endforeach; ?>
                
                <?php if (in_array($role, ['Admin', 'SuperAdmin', 'Gerente'])): ?>
                <li>
                    <a href="<?= htmlspecialchars($sidebarBasePath)?>/admin-areas"
                        class="nav-item <?=(strpos($currentUri, 'admin-areas') !== false) ? 'active' : ''?>" style="padding-left: 45px; padding-top: 10px; padding-bottom: 10px;">
                        <span style="font-size:1.1rem; line-height:1; flex-shrink:0; display: flex; align-items: center; justify-content: center; width: 20px; height: 20px;">
                            <?= $iconMap['Áreas']?>
                        </span>
                        <span>Áreas y Workflow</span>
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </li>

        <script>
        function toggleAreasMenu(btn) {
            const submenu = document.getElementById('submenuAreas');
            const chevron = btn.querySelector('.chevron');
            if(submenu.style.display === 'none' || !submenu.style.display) {
                submenu.style.display = 'block';
                chevron.style.transform = 'rotate(180deg)';
                localStorage.setItem('sidebarAreas_state', 'open');
            } else {
                submenu.style.display = 'none';
                chevron.style.transform = 'rotate(0deg)';
                localStorage.setItem('sidebarAreas_state', 'closed');
            }
        }
        
        // Mantener recordado si estaba abierto o cerrado al recargar la pagina
        document.addEventListener('DOMContentLoaded', () => {
            const submenu = document.getElementById('submenuAreas');
            const chevron = document.querySelector('#btnAreasToggle .chevron');
            const state = localStorage.getItem('sidebarAreas_state');
            if(!<?= $currentAreaId ? 'true' : 'false' ?>) {
                if(state === 'open') {
                    submenu.style.display = 'block';
                    chevron.style.transform = 'rotate(180deg)';
                }
            }
        });
        </script>

        <!-- Flujo de Trabajo (Desplegable Acordeón) -->
        <li style="margin-top:5px; padding-top:5px; border-top:1px solid rgba(255,255,255,0.05);">
            <div class="nav-item" onclick="toggleFlujoMenu(this)" style="cursor:pointer; justify-content: space-between; padding-right:20px; align-items: center;" id="btnFlujoToggle">
                <div style="display:flex; align-items:center; gap:12px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #f59e0b;">
                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                    </svg>
                    <span style="font-weight: 600;">Flujo de Trabajo</span>
                </div>
                <svg class="chevron-flujo" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="transition: transform 0.3s, color 0.3s; color:#94A3B8; <?= (strpos($currentUri, 'flujo-trabajo') !== false || strpos($currentUri, 'base-datos-pedidos') !== false) ? 'transform: rotate(180deg);' : '' ?>"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </div>
            
            <ul id="submenuFlujo" style="display: <?= (strpos($currentUri, 'flujo-trabajo') !== false || strpos($currentUri, 'base-datos-pedidos') !== false) ? 'block' : 'none' ?>; list-style:none; padding-left: 0; margin:0; margin-top:2px;">
                <li>
                    <a href="<?= htmlspecialchars($sidebarBasePath)?>/flujo-trabajo"
                        class="nav-item <?=(strpos($currentUri, 'flujo-trabajo') !== false) ? 'active' : ''?>" style="padding-left: 45px; padding-top: 10px; padding-bottom: 10px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect></svg>
                        <span>Gestión Pedidos Activos</span>
                    </a>
                </li>
                <li>
                    <a href="<?= htmlspecialchars($sidebarBasePath)?>/base-datos-pedidos"
                        class="nav-item <?=(strpos($currentUri, 'base-datos-pedidos') !== false) ? 'active' : ''?>" style="padding-left: 45px; padding-top: 10px; padding-bottom: 10px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"></ellipse><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path></svg>
                        <span>Base de Datos Pedidos</span>
                    </a>
                </li>
            </ul>
        </li>

        <script>
        function toggleFlujoMenu(btn) {
            const submenu = document.getElementById('submenuFlujo');
            const chevron = btn.querySelector('.chevron-flujo');
            if(submenu.style.display === 'none' || !submenu.style.display) {
                submenu.style.display = 'block';
                chevron.style.transform = 'rotate(180deg)';
                localStorage.setItem('sidebarFlujo_state', 'open');
            } else {
                submenu.style.display = 'none';
                chevron.style.transform = 'rotate(0deg)';
                localStorage.setItem('sidebarFlujo_state', 'closed');
            }
        }
        document.addEventListener('DOMContentLoaded', () => {
            const submenu = document.getElementById('submenuFlujo');
            const chevron = document.querySelector('#btnFlujoToggle .chevron-flujo');
            const state = localStorage.getItem('sidebarFlujo_state');
            const isFlujoPage = <?= (strpos($currentUri, 'flujo-trabajo') !== false || strpos($currentUri, 'base-datos-pedidos') !== false) ? 'true' : 'false' ?>;
            if(!isFlujoPage && state === 'open') {
                submenu.style.display = 'block';
                chevron.style.transform = 'rotate(180deg)';
            }
        });
        </script>

        <?php if (in_array($role, ['Admin', 'SuperAdmin', 'Gerente'])): ?>
        <!-- Menú desplegable Reportes -->
        <li style="margin-top:5px; padding-top:5px; border-top:1px solid rgba(255,255,255,0.05);">
            <div class="nav-item" onclick="toggleReportesMenu(this)" style="cursor:pointer; justify-content: space-between; padding-right:20px; align-items: center;" id="btnReportesToggle">
                <div style="display:flex; align-items:center; gap:12px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #60a5fa;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                    <span style="font-weight: 600;">Reportes</span>
                </div>
                <svg class="chevron-reportes" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="transition: transform 0.3s, color 0.3s; color:#94A3B8;"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </div>
            
            <?php
            $isReportesActive = (strpos($currentUri, 'reporte-movimientos') !== false || strpos($currentUri, 'reportes-pedidos') !== false || strpos($currentUri, 'clientes') !== false || strpos($currentUri, 'clientes-prospectos') !== false || strpos($currentUri, 'clientes-potenciales') !== false);
            ?>
            <ul id="submenuReportes" style="display: <?= $isReportesActive ? 'block' : 'none' ?>; list-style:none; padding-left: 0; margin:0; margin-top:2px;">
                <li>
                    <a href="<?= htmlspecialchars($sidebarBasePath)?>/reporte-movimientos"
                        class="nav-item <?=(strpos($currentUri, 'reporte-movimientos') !== false) ? 'active' : ''?>" style="padding-left: 45px; padding-top: 10px; padding-bottom: 10px;">
                        <span style="display:flex; align-items:center; justify-content:center; width:20px; height:20px; transform: scale(0.85);"><?= $iconMap['Reportes'] ?? '📄'?></span>
                        <span>Auditoría</span>
                    </a>
                </li>
                <li>
                    <a href="<?= htmlspecialchars($sidebarBasePath)?>/reportes-pedidos"
                        class="nav-item <?=(strpos($currentUri, 'reportes-pedidos') !== false) ? 'active' : ''?>" style="padding-left: 45px; padding-top: 10px; padding-bottom: 10px;">
                        <span style="display:flex; align-items:center; justify-content:center; width:20px; height:20px; transform: scale(0.85);"><?= $iconMap['Reportes Pedidos']?></span>
                        <span>De Pedidos</span>
                    </a>
                </li>
                <?php if (in_array($role, ['Admin', 'SuperAdmin'])): ?>
                <li>
                    <a href="<?= htmlspecialchars($sidebarBasePath)?>/clientes"
                        class="nav-item <?=(strpos($currentUri, 'clientes') !== false && strpos($currentUri, 'clientes-prospectos') === false && strpos($currentUri, 'clientes-potenciales') === false) ? 'active' : ''?>" style="padding-left: 45px; padding-top: 10px; padding-bottom: 10px;">
                        <span style="display:flex; align-items:center; justify-content:center; width:20px; height:20px; transform: scale(0.85);"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg></span>
                        <span>Vista Clientes</span>
                    </a>
                </li>
                <li>
                    <a href="<?= htmlspecialchars($sidebarBasePath)?>/clientes-prospectos"
                        class="nav-item <?=(strpos($currentUri, 'clientes-prospectos') !== false) ? 'active' : ''?>" style="padding-left: 45px; padding-top: 10px; padding-bottom: 10px;">
                        <span style="display:flex; align-items:center; justify-content:center; width:20px; height:20px; transform: scale(0.85);"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20v-6M6 20V10M18 20V4"></path></svg></span>
                        <span>Clientes prospectos</span>
                    </a>
                </li>
                <li>
                    <a href="<?= htmlspecialchars($sidebarBasePath)?>/clientes-potenciales"
                        class="nav-item <?=(strpos($currentUri, 'clientes-potenciales') !== false) ? 'active' : ''?>" style="padding-left: 45px; padding-top: 10px; padding-bottom: 10px;">
                        <span style="display:flex; align-items:center; justify-content:center; width:20px; height:20px; transform: scale(0.85);"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg></span>
                        <span>Clientes potenciales</span>
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </li>
        <script>
        function toggleReportesMenu(btn) {
            const submenu = document.getElementById('submenuReportes');
            const chevron = btn.querySelector('.chevron-reportes');
            if(submenu.style.display === 'none' || !submenu.style.display) {
                submenu.style.display = 'block';
                chevron.style.transform = 'rotate(180deg)';
                localStorage.setItem('sidebarReportes_state', 'open');
            } else {
                submenu.style.display = 'none';
                chevron.style.transform = 'rotate(0deg)';
                localStorage.setItem('sidebarReportes_state', 'closed');
            }
        }
        document.addEventListener('DOMContentLoaded', () => {
            const submenu = document.getElementById('submenuReportes');
            const chevron = document.querySelector('#btnReportesToggle .chevron-reportes');
            const state = localStorage.getItem('sidebarReportes_state');
            const isPage = <?= $isReportesActive ? 'true' : 'false' ?>;
            if(!isPage && state === 'open') {
                if(submenu) submenu.style.display = 'block';
                if(chevron) chevron.style.transform = 'rotate(180deg)';
            }
        });
        </script>
        <?php endif; ?>

        <?php if (in_array($role, ['Admin', 'SuperAdmin'])): ?>
        <!-- Menú desplegable Contabilidad -->
        <li style="margin-top:5px; padding-top:5px; border-top:1px solid rgba(255,255,255,0.05);">
            <div class="nav-item" onclick="toggleContaMenu(this)" style="cursor:pointer; justify-content: space-between; padding-right:20px; align-items: center;" id="btnContaToggle">
                <div style="display:flex; align-items:center; gap:12px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #10b981;"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"></path></svg>
                    <span style="font-weight: 600;">Contabilidad</span>
                </div>
                <svg class="chevron-conta" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="transition: transform 0.3s, color 0.3s; color:#94A3B8;"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </div>
            
            <?php
            $isContaActive = (strpos($currentUri, 'contabilidad') !== false);
            ?>
            <ul id="submenuConta" style="display: <?= $isContaActive ? 'block' : 'none' ?>; list-style:none; padding-left: 0; margin:0; margin-top:2px;">
                <li>
                    <a href="<?= htmlspecialchars($sidebarBasePath)?>/contabilidad"
                        class="nav-item <?=(strpos($currentUri, 'contabilidad') !== false) ? 'active' : ''?>" style="padding-left: 45px; padding-top: 10px; padding-bottom: 10px;">
                         <span style="display:flex; align-items:center; justify-content:center; width:20px; height:20px; transform: scale(0.85);"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"></path></svg></span>
                        <span>Ver Contabilidad</span>
                    </a>
                </li>
            </ul>
        </li>
        <script>
        function toggleContaMenu(btn) {
            const submenu = document.getElementById('submenuConta');
            const chevron = btn.querySelector('.chevron-conta');
            if(submenu.style.display === 'none' || !submenu.style.display) {
                submenu.style.display = 'block';
                chevron.style.transform = 'rotate(180deg)';
                localStorage.setItem('sidebarConta_state', 'open');
            } else {
                submenu.style.display = 'none';
                chevron.style.transform = 'rotate(0deg)';
                localStorage.setItem('sidebarConta_state', 'closed');
            }
        }
        document.addEventListener('DOMContentLoaded', () => {
            const submenu = document.getElementById('submenuConta');
            const chevron = document.querySelector('#btnContaToggle .chevron-conta');
            const state = localStorage.getItem('sidebarConta_state');
            const isPage = <?= $isContaActive ? 'true' : 'false' ?>;
            if(!isPage && state === 'open') {
                if(submenu) submenu.style.display = 'block';
                if(chevron) chevron.style.transform = 'rotate(180deg)';
            }
        });
        </script>
        <?php endif; ?>

        <?php if (in_array($role, ['Admin', 'SuperAdmin', 'Gerente'])): ?>
        <!-- Botones sueltos -->
        <li style="margin-top:15px; border-top:1px solid rgba(255,255,255,0.05); padding-top:10px;">
            <a href="<?= htmlspecialchars($sidebarBasePath)?>/admin-usuarios"
                class="nav-item <?=(strpos($currentUri, 'admin-usuarios') !== false) ? 'active' : ''?>">
                <?= $iconMap['Usuarios']?> <span>Empleados y accesos</span>
            </a>
            <?php if (in_array($role, ['Admin', 'SuperAdmin'])): ?>
            <a href="<?= htmlspecialchars($sidebarBasePath)?>/configuracion"
                class="nav-item <?=(strpos($currentUri, 'configuracion') !== false) ? 'active' : ''?>">
                <?= $iconMap['Configuración']?> <span>Configuración</span>
            </a>
            <?php endif; ?>
        </li>
        <?php endif; ?>

        <!-- Mi Cuenta: visible para todos los roles -->
        <li style="margin-top:auto; border-top:1px solid rgba(255,255,255,0.05); padding-top:10px;">
            <a href="<?= htmlspecialchars($sidebarBasePath)?>/mi-cuenta"
                class="nav-item <?=(strpos($currentUri, 'mi-cuenta') !== false) ? 'active' : ''?>"
                title="Editar mi perfil">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
                <span>Mi Cuenta</span>
            </a>
        </li>
    </ul>
</aside>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const btn = document.getElementById('btnHamburger');
        const sidebar = document.querySelector('.sidebar');
        if (!btn || !sidebar) return;

        let modes = ['mode-full', 'mode-icon', 'mode-hidden'];
        let currentMode = localStorage.getItem('sidebarMode') || 'mode-full';

        const overlay = document.querySelector('.sidebar-overlay') || document.createElement('div');
        if (!document.querySelector('.sidebar-overlay')) {
            overlay.className = 'sidebar-overlay';
            document.body.appendChild(overlay);
        }

        function applyMode(mode) {
            sidebar.classList.remove('mode-full', 'mode-icon', 'mode-hidden');
            overlay.classList.remove('active');
            if (mode !== 'mode-full') {
                sidebar.classList.add(mode);
            }
            if (window.innerWidth <= 768 && mode !== 'mode-hidden') {
                overlay.classList.add('active');
            }
        }

        applyMode(currentMode);

        btn.addEventListener('click', () => {
            let idx = modes.indexOf(currentMode);
            if (window.innerWidth <= 768) {
                // En móvil solo alternamos entre full y oculto para mejor UX
                currentMode = (currentMode === 'mode-hidden') ? 'mode-full' : 'mode-hidden';
            } else {
                currentMode = modes[(idx + 1) % modes.length];
            }
            localStorage.setItem('sidebarMode', currentMode);
            applyMode(currentMode);
        });

        overlay.addEventListener('click', () => {
            currentMode = 'mode-hidden';
            localStorage.setItem('sidebarMode', currentMode);
            applyMode(currentMode);
        });
    });
</script>

<!-- Modal Global de Error/Permisos - CON ANIMACIÓN -->
<style>
    @keyframes gErrorFadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    @keyframes gErrorPopIn {
        from {
            opacity: 0;
            transform: scale(0.82) translateY(-20px);
        }

        to {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }

    #modalGlobalError {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 99999;
        justify-content: center;
        align-items: center;
        font-family: 'Inter', sans-serif;
    }

    #modalGlobalError.active {
        display: flex;
        animation: gErrorFadeIn 0.25s ease forwards;
    }

    #modalGlobalError .g-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, 0.88);
        backdrop-filter: blur(8px);
    }

    #modalGlobalError .g-card {
        position: relative;
        z-index: 1;
        background: #1E293B;
        border-radius: 24px;
        padding: 48px 40px 36px;
        max-width: 420px;
        width: 90%;
        text-align: center;
        box-shadow: 0 30px 80px rgba(0, 0, 0, 0.7), 0 0 0 1px var(--g-accent-border);
        animation: gErrorPopIn 0.38s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        --g-accent-border: rgba(239, 68, 68, 0.35);
    }

    #modalGlobalError .g-close {
        position: absolute;
        top: 14px;
        right: 16px;
        background: rgba(255, 255, 255, 0.08);
        border: none;
        color: #94A3B8;
        font-size: 1.3rem;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s, color 0.2s;
    }

    #modalGlobalError .g-close:hover {
        background: rgba(239, 68, 68, 0.25);
        color: #F87171;
    }

    #modalGlobalError .g-icon {
        font-size: 56px;
        margin-bottom: 18px;
        display: block;
    }

    #modalGlobalError .g-title {
        font-size: 1.55rem;
        font-weight: 800;
        color: #F8FAFC;
        margin-bottom: 10px;
        letter-spacing: -0.02em;
    }

    #modalGlobalError .g-text {
        color: #94A3B8;
        line-height: 1.65;
        font-size: 0.95rem;
        margin-bottom: 30px;
    }

    #modalGlobalError .g-btn {
        background: var(--g-btn-color, #4F46E5);
        color: white;
        border: none;
        padding: 13px 28px;
        border-radius: 10px;
        cursor: pointer;
        font-size: 1rem;
        width: 100%;
        font-weight: 700;
        letter-spacing: 0.01em;
        transition: opacity 0.2s, transform 0.15s;
    }

    #modalGlobalError .g-btn:hover {
        opacity: 0.88;
        transform: translateY(-1px);
    }
</style>

<div id="modalGlobalError">
    <div class="g-backdrop"></div>
    <div class="g-card">
        <button class="g-close" onclick="closeGlobalError()" title="Cerrar">&times;</button>
        <span id="globalErrorIcon" class="g-icon">🚫</span>
        <div id="globalErrorTitle" class="g-title">Acceso Denegado</div>
        <div id="globalErrorText" class="g-text">No tienes permisos para esta acción.</div>
        <button class="g-btn" onclick="closeGlobalError()">Entendido</button>
    </div>
</div>

<script>
    /**
     * Manejador global de respuestas de API
     */
    function handleApiResponse(res, onSuccess) {
        const sidebarBasePath = "<?= htmlspecialchars($sidebarBasePath)?>";

        if (res.status === 'success') {
            if (onSuccess) onSuccess(res);
        } else {
            const msg = res.message || "";
            if (msg.includes("Permisos insuficientes")) {
                showGlobalError('permisos', "Tu rol actual no tiene autorización para realizar esta operación.");
            } else if (msg.includes("tiempo expirado") || msg.includes("Sesión expirada")) {
                window.location.href = sidebarBasePath + '/login?error=timeout';
            } else {
                showGlobalError('error', msg || "Ocurrió un error inesperado.");
            }
        }
    }

    function showGlobalError(tipo, texto) {
        const modal = document.getElementById('modalGlobalError');
        const card = modal.querySelector('.g-card');
        const icon = document.getElementById('globalErrorIcon');
        const title = document.getElementById('globalErrorTitle');
        const body = document.getElementById('globalErrorText');

        if (tipo === 'permisos') {
            icon.textContent = '🚫';
            title.textContent = 'Permisos Insuficientes';
            card.style.setProperty('--g-accent-border', 'rgba(239,68,68,0.35)');
            card.querySelector('.g-btn').style.setProperty('--g-btn-color', '#EF4444');
        } else if (tipo === 'timeout') {
            icon.textContent = '⏰';
            title.textContent = 'Sesión Cerrada';
            card.style.setProperty('--g-accent-border', 'rgba(99,102,241,0.4)');
            card.querySelector('.g-btn').style.setProperty('--g-btn-color', '#4F46E5');
        } else {
            icon.textContent = '⚠️';
            title.textContent = 'Error';
            card.style.setProperty('--g-accent-border', 'rgba(245,158,11,0.35)');
            card.querySelector('.g-btn').style.setProperty('--g-btn-color', '#F59E0B');
        }

        body.textContent = texto;

        // Reiniciar animación
        modal.classList.remove('active');
        void modal.offsetWidth; // reflow
        modal.classList.add('active');
    }

    function closeGlobalError() {
        document.getElementById('modalGlobalError').classList.remove('active');
    }

    // Detectar redirección por error desde rutas de vistas
    document.addEventListener('DOMContentLoaded', function () {
        const params = new URLSearchParams(window.location.search);
        const errorParam = params.get('error');

        if (errorParam === 'permisos') {
            showGlobalError('permisos', 'Tu rol actual no tiene autorización para acceder a esa sección del sistema.');
            // Limpiar URL sin recargar
            const url = new URL(window.location.href);
            url.searchParams.delete('error');
            window.history.replaceState({}, '', url.toString());
        } else if (errorParam === 'timeout') {
            showGlobalError('timeout', 'Tu sesión ha sido cerrada automáticamente por inactividad para proteger tu cuenta.');
            const url = new URL(window.location.href);
            url.searchParams.delete('error');
            window.history.replaceState({}, '', url.toString())
        }
    });
</script>

<!-- ============ MODAL PERFIL ============ -->
<style>
    @keyframes perfilFadeIn {
        from {
            opacity: 0
        }

        to {
            opacity: 1
        }
    }

    @keyframes perfilPopIn {
        from {
            opacity: 0;
            transform: scale(0.85) translateY(-18px)
        }

        to {
            opacity: 1;
            transform: scale(1) translateY(0)
        }
    }

    #modalPerfil {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 99998;
        justify-content: center;
        align-items: center;
        font-family: 'Inter', sans-serif;
    }

    #modalPerfil.active {
        display: flex;
        animation: perfilFadeIn .25s ease forwards;
    }

    #modalPerfil .pf-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, 0.88);
        backdrop-filter: blur(8px);
    }

    #modalPerfil .pf-card {
        position: relative;
        z-index: 1;
        background: #1E293B;
        border-radius: 24px;
        padding: 40px 36px 32px;
        max-width: 420px;
        width: 92%;
        border: 1px solid rgba(79, 70, 229, 0.35);
        box-shadow: 0 30px 80px rgba(0, 0, 0, 0.6);
        animation: perfilPopIn .38s cubic-bezier(.16, 1, .3, 1) forwards;
    }

    #modalPerfil .pf-close {
        position: absolute;
        top: 14px;
        right: 16px;
        background: rgba(255, 255, 255, 0.08);
        border: none;
        color: #94A3B8;
        font-size: 1.3rem;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background .2s, color .2s;
    }

    #modalPerfil .pf-close:hover {
        background: rgba(239, 68, 68, 0.25);
        color: #F87171;
    }

    #modalPerfil .pf-avatar-wrap {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 12px;
        margin-bottom: 28px;
    }

    #modalPerfil .pf-avatar-ring {
        width: 105px;
        height: 105px;
        border-radius: 50%;
        border: 4px solid #4F46E5;
        padding: 4px;
        background: rgba(79, 70, 229, 0.15);
        position: relative;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 0 20px rgba(79, 70, 229, 0.2);
    }

    #modalPerfil .pf-avatar-ring:hover {
        border-color: #6EE7B7;
        transform: scale(1.08);
        box-shadow: 0 0 30px rgba(110, 231, 183, 0.3);
    }

    #modalPerfil #pfAvatarInner {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #4F46E5;
    }

    #modalPerfil .pf-avatar-ring img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    #modalPerfil .pf-initial {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.8rem;
        font-weight: 900;
        color: #F1F5F9;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    #modalPerfil .pf-avatar-ring .pf-overlay {
        position: absolute;
        inset: 0;
        border-radius: 50%;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity .2s;
        font-size: 1.6rem;
    }

    #modalPerfil .pf-avatar-ring:hover .pf-overlay {
        opacity: 1;
    }

    #modalPerfil h2 {
        font-size: 1.35rem;
        font-weight: 800;
        color: #F8FAFC;
        text-align: center;
        margin-bottom: 4px;
    }

    #modalPerfil .pf-email {
        color: #64748B;
        font-size: .82rem;
        text-align: center;
        margin-bottom: 24px;
    }

    #modalPerfil .pf-section {
        font-size: .75rem;
        color: #4F46E5;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        margin-bottom: 8px;
    }

    #modalPerfil .pf-input {
        width: 100%;
        padding: 10px 14px;
        border-radius: 8px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        background: rgba(0, 0, 0, 0.25);
        color: #F8FAFC;
        font-size: .9rem;
        outline: none;
        transition: border-color .2s;
    }

    #modalPerfil .pf-input:focus {
        border-color: #4F46E5;
    }

    #modalPerfil .pf-form-group {
        margin-bottom: 14px;
    }

    #modalPerfil .pf-btn {
        width: 100%;
        padding: 13px;
        border-radius: 10px;
        border: none;
        background: linear-gradient(135deg, #4F46E5, #6EE7B7 200%);
        color: #fff;
        font-size: 1rem;
        font-weight: 700;
        cursor: pointer;
        margin-top: 8px;
        transition: opacity .2s, transform .15s;
    }

    #modalPerfil .pf-btn:hover {
        opacity: .88;
        transform: translateY(-1px);
    }

    #modalPerfil .pf-btn:disabled {
        opacity: .5;
        cursor: not-allowed;
        transform: none;
    }

    #modalPerfil .pf-msg {
        text-align: center;
        font-size: .85rem;
        margin-top: 10px;
        min-height: 18px;
    }

    #modalPerfil .pf-msg.ok {
        color: #6EE7B7;
    }

    #modalPerfil .pf-msg.err {
        color: #F87171;
    }
</style>

<div id="modalPerfil">
    <div class="pf-backdrop" onclick="closePerfilModal()"></div>
    <div class="pf-card">
        <button class="pf-close" onclick="closePerfilModal()">&times;</button>

        <!-- Avatar con preview -->
        <div class="pf-avatar-wrap">
            <div class="pf-avatar-ring" onclick="document.getElementById('pfFotoInput').click()" title="Cambiar foto">
                <div id="pfAvatarInner">
                    <?php if (!empty($sidebarFoto)): ?>
                    <img id="pfAvatarImg" src="<?= htmlspecialchars($sidebarFoto)?>" alt="Foto">
                    <?php
else: ?>
                    <div class="pf-initial">
                        <?= strtoupper(substr($sidebarNombre, 0, 1))?>
                    </div>
                    <?php
endif; ?>
                </div>
                <div class="pf-overlay">📷</div>
            </div>
            <input type="file" id="pfFotoInput" accept="image/*" style="display:none">
            <small style="color:#64748B; font-size:.75rem;">Clic en la foto para cambiarla</small>
        </div>

        <h2 id="pfNombreDisplay">
            <?= htmlspecialchars($sidebarNombre)?>
        </h2>
        <div class="pf-email">
            <?= htmlspecialchars($sidebarEmail)?>
        </div>

        <!-- Nombre -->
        <div class="pf-section">Nombre</div>
        <div class="pf-form-group">
            <input type="text" id="pfNombre" class="pf-input" value="<?= htmlspecialchars($sidebarNombre)?>"
                maxlength="50" placeholder="Tu nombre">
        </div>

        <!-- Nueva contraseña -->
        <div class="pf-section">Nueva Contraseña</div>
        <div class="pf-form-group">
            <input type="password" id="pfPassword" class="pf-input" placeholder="Dejar vacío para no cambiar">
        </div>
        <div class="pf-form-group">
            <input type="password" id="pfPasswordConf" class="pf-input" placeholder="Confirmar nueva contraseña">
        </div>

        <button class="pf-btn" id="pfGuardarBtn" onclick="guardarPerfil()">Guardar Cambios</button>
        <div class="pf-msg" id="pfMsg"></div>
    </div>
</div>

<script>
    function openPerfilModal() {
        document.getElementById('pfPassword').value = '';
        document.getElementById('pfPasswordConf').value = '';
        document.getElementById('pfMsg').textContent = '';
        document.getElementById('pfMsg').className = 'pf-msg';
        // No resetear nombre para mantener el valor actual
        const m = document.getElementById('modalPerfil');
        m.classList.remove('active');
        void m.offsetWidth;
        m.classList.add('active');
    }
    function closePerfilModal() {
        document.getElementById('modalPerfil').classList.remove('active');
    }

    // Preview de foto antes de subir
    document.getElementById('pfFotoInput').addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = function (e) {
            const inner = document.getElementById('pfAvatarInner');
            inner.innerHTML = '<img id="pfAvatarImg" src="' + e.target.result + '" alt="Preview" style="width:100%;height:100%;border-radius:50%;object-fit:cover;">';
        };
        reader.readAsDataURL(file);
    });

    async function guardarPerfil() {
        const pw = document.getElementById('pfPassword').value;
        const pwc = document.getElementById('pfPasswordConf').value;
        const foto = document.getElementById('pfFotoInput').files[0];
        const msg = document.getElementById('pfMsg');
        const btn = document.getElementById('pfGuardarBtn');

        msg.textContent = '';
        msg.className = 'pf-msg';

        const nombre = document.getElementById('pfNombre').value.trim();
        const nombreOriginal = <?= json_encode($sidebarNombre)?>;

        if (!pw && !foto && nombre === nombreOriginal) {
            msg.textContent = 'No hay cambios que guardar.';
            msg.className = 'pf-msg err';
            return;
        }
        if (pw && pw !== pwc) {
            msg.textContent = 'Las contraseñas no coinciden.';
            msg.className = 'pf-msg err';
            return;
        }

        btn.disabled = true;
        btn.textContent = 'Guardando…';

        const formData = new FormData();
        if (nombre && nombre !== nombreOriginal) formData.append('nombre', nombre);
        if (pw) formData.append('nueva_password', pw);
        if (foto) formData.append('foto', foto);

        const sidebarBasePath = "<?= htmlspecialchars($sidebarBasePath)?>";
        try {
            const req = await fetch(sidebarBasePath + '/api/perfil/actualizar', {
                method: 'POST',
                body: formData
            });
            const res = await req.json();
            if (res.status === 'success') {
                msg.textContent = '✓ ' + res.message;
                msg.className = 'pf-msg ok';
                // Actualizar avatar en sidebar si cambió foto
                if (res.data && res.data.foto_url) {
                    const sAvatar = document.getElementById('sidebarAvatar');
                    if (sAvatar) {
                        sAvatar.innerHTML = '<img src="' + res.data.foto_url + '?t=' + Date.now() + '" alt="Foto" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">';
                    }
                    const pfInner = document.getElementById('pfAvatarInner');
                    if (pfInner) pfInner.innerHTML = '<img src="' + res.data.foto_url + '?t=' + Date.now() + '" alt="Foto" style="width:100%;height:100%;object-fit:cover;">';
                }
                // Actualizar nombre en sidebar y modal si cambió
                if (res.data && res.data.nombre_actualizado && res.data.nombre) {
                    const sName = document.querySelector('.sidebar .user-details h4');
                    if (sName) sName.textContent = res.data.nombre;
                    const pfDisplay = document.getElementById('pfNombreDisplay');
                    if (pfDisplay) pfDisplay.textContent = res.data.nombre;
                }
                document.getElementById('pfPassword').value = '';
                document.getElementById('pfPasswordConf').value = '';
                document.getElementById('pfFotoInput').value = '';
            } else {
                msg.textContent = '✗ ' + (res.message || 'Error al guardar.');
                msg.className = 'pf-msg err';
            }
        } catch (e) {
            msg.textContent = '✗ Error de conexión: ' + e.message;
            msg.className = 'pf-msg err';
        }

        btn.disabled = false;
        btn.textContent = 'Guardar Cambios';
    }
</script>