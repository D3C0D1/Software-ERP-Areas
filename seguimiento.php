<?php
// Mostrar errores para diagnosticar en Hostinger
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE)
    session_start();

$token = trim(isset($_GET['token']) ? $_GET['token'] : '');

// Modo poll: devolver JSON para auto-refresh
if (isset($_GET['poll']) && $token !== '') {
    header('Content-Type: application/json; charset=utf-8');
    try {
        require_once __DIR__ . '/config/Database.php';
        $db = \Config\Database::getInstance();
        $s = $db->prepare("SELECT p.id, p.estado, p.fase_actual, p.area_actual_id, COALESCE(a.nombre,'Guia Generada') as area_nombre FROM pedidos p LEFT JOIN areas a ON p.area_actual_id=a.id WHERE p.token_seguimiento=:t");
        $s->execute(array('t' => $token));
        $row = $s->fetch(\PDO::FETCH_ASSOC);
        if ($row) {
            echo json_encode(array(
                'estado' => $row['estado'],
                'faseActual' => $row['fase_actual'],
                'areaActualId' => $row['area_actual_id'],
                'areaNombre' => $row['area_nombre'],
            ));
        }
        else {
            http_response_code(404);
            echo '{"error":"not found"}';
        }
    }
    catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array('error' => $e->getMessage()));
    }
    exit;
}

if ($token === '') {
    die("Error: Token no encontrado en la URL.");
}

$pedidoData = null;
$areasData = array();
$logoUrl = '';
$debugInfo = array();
$dbError = '';

try {
    require_once __DIR__ . '/config/Database.php';
    $db = \Config\Database::getInstance();

    $debugInfo['HOST'] = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '?';
    $debugInfo['SCRIPT'] = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '?';
    $debugInfo['URI'] = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '?';
    $debugInfo['PHP_VER'] = PHP_VERSION;

    // Buscar pedido
    $s = $db->prepare("SELECT p.id, p.cliente_nombre, p.estado, p.fase_actual, p.area_actual_id, COALESCE(a.nombre,'Guia Generada') as area_nombre, p.token_seguimiento, p.estado_pago, p.total, p.abonado, p.created_at FROM pedidos p LEFT JOIN areas a ON p.area_actual_id=a.id WHERE p.token_seguimiento=:t");
    $s->execute(array('t' => $token));
    $pedidoData = $s->fetch(\PDO::FETCH_ASSOC);

    $debugInfo['tokens_totales'] = $db->query("SELECT COUNT(*) FROM pedidos WHERE token_seguimiento IS NOT NULL AND token_seguimiento != ''")->fetchColumn();
    $debugInfo['token_hex'] = bin2hex($token);

    $areasData = $db->query("SELECT id, nombre, icono FROM areas WHERE estado=1 ORDER BY orden ASC")->fetchAll(\PDO::FETCH_ASSOC);

    try {
        $logoUrl = $db->query("SELECT valor FROM configuracion WHERE clave='empresa_logo' LIMIT 1")->fetchColumn();
        if (!$logoUrl)
            $logoUrl = '';
    }
    catch (Exception $e) {
        $logoUrl = '';
    }

    $fondoLogin = '';
    try {
        $fondoLogin = $db->query("SELECT valor FROM configuracion WHERE clave='fondo_login' LIMIT 1")->fetchColumn();
    }
    catch (Exception $e) {
    }

    // Registrar apertura sin usar autoload del framework
    require_once __DIR__ . '/app/controllers/SeguimientoController.php';
    $ctrl = new \App\Controllers\SeguimientoController();
    $ctrl->registrarAperturaCliente($token);

}
catch (Exception $e) {
    $dbError = $e->getMessage();
}

$scriptDir = dirname(isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '/seguimiento.php');
$basePath = rtrim($scriptDir, '/') . '/public';
$rootPath = rtrim($scriptDir, '/');

if (empty($fondoLogin)) {
    $fondoLogin = $rootPath . '/img/LEON.jpg';
}

$areasJson = json_encode($areasData, JSON_UNESCAPED_UNICODE);

function seguimIcono($name)
{
    $n = strtolower($name);
    if (strpos($n, 'recep') !== false)
        return '&#x1F4CB;';
    if (strpos($n, 'dise') !== false)
        return '&#x1F58C;';
    if (strpos($n, 'impre') !== false)
        return '&#x1F5A8;';
    if (strpos($n, 'subli') !== false)
        return '&#x1F525;';
    if (strpos($n, 'confec') !== false)
        return '&#x2702;';
    if (strpos($n, 'borda') !== false)
        return '&#x1F9F5;';
    if (strpos($n, 'mensa') !== false)
        return '&#x1F9F5;';
    if (strpos($n, 'calid') !== false)
        return '&#x1F4CB;';
    if (strpos($n, 'prueb') !== false)
        return '&#x1F4CB;';
    if (strpos($n, 'final') !== false)
        return '&#x1F3C1;';
    if (strpos($n, 'empaq') !== false)
        return '&#x1F3C1;';
    return '&#x1F4E6;';
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seguimiento de Pedido | Banner</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-glow: rgba(79, 70, 229, 0.5);
            --completed: #10b981;
            --bg: #0f172a;
            --card: rgba(255, 255, 255, 0.08);
            --border: rgba(255, 255, 255, 0.15);
            --light: #f8fafc;
            --muted: #94a3b8;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--light);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 10px 20px 40px;
        }

        .header-logo {
            margin: 16px 0 14px;
            text-align: center;
        }

        .header-logo img {
            max-width: 200px;
            height: auto;
            object-fit: contain;
            filter: drop-shadow(0 0 10px rgba(255, 255, 255, .18));
        }

        .header-title {
            margin-bottom: 20px;
            text-align: center;
        }

        .header-title h2 {
            font-size: 1.2rem;
            font-weight: 700;
            color: #a5b4fc;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .header-title p {
            font-size: .8rem;
            color: var(--muted);
            margin-top: 3px;
        }

        .order-card {
            background: rgba(15, 23, 42, .75);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 18px 28px;
            text-align: center;
            margin-bottom: 26px;
            width: 100%;
            max-width: 600px;
        }

        /* Fondo de Login */
        .bg-cover {
            position: fixed;
            inset: 0;
            z-index: -2;
            background: url('<?php echo strpos($fondoLogin, "data:image") === 0 ? $fondoLogin : htmlspecialchars($fondoLogin); ?>') center center / cover no-repeat;
            filter: brightness(.35) saturate(.7);
        }

        .order-card h1 {
            font-size: 1.45rem;
            font-weight: 700;
            margin-bottom: 5px;
            background: linear-gradient(135deg, #a5b4fc, #818cf8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .order-card .cname {
            font-size: 1.05rem;
            font-weight: 600;
            color: var(--light);
            margin-bottom: 4px;
        }

        .badge {
            display: inline-block;
            margin-top: 6px;
            padding: 4px 14px;
            border-radius: 20px;
            font-size: .75rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .badge.completado {
            background: rgba(16, 185, 129, .2);
            color: #34d399;
        }

        .badge.cancelado {
            background: rgba(239, 68, 68, .2);
            color: #f87171;
        }

        .badge.activo {
            background: rgba(99, 102, 241, .2);
            color: #a5b4fc;
        }

        .badge.pendiente {
            background: rgba(245, 158, 11, .2);
            color: #fbbf24;
        }

        .area-txt {
            margin-top: 12px;
            font-size: 1.05rem;
            color: #38bdf8;
            font-weight: 600;
        }

        .timeline {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
            gap: 18px;
            width: 100%;
            max-width: 1100px;
        }

        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: var(--card);
            border: 1.5px solid var(--border);
            border-radius: 14px;
            padding: 16px 12px;
            width: 120px;
            min-height: 100px;
            text-align: center;
            transition: all .4s ease;
            position: relative;
        }

        @media(min-width:768px) {
            .step:not(:last-child)::after {
                content: '';
                position: absolute;
                top: 50%;
                right: -19px;
                width: 18px;
                height: 2px;
                background: var(--border);
                z-index: -1;
            }

            .step.passed:not(:last-child)::after {
                background: var(--completed);
            }
        }

        .step .ico {
            font-size: 1.7rem;
            color: var(--muted);
        }

        .step .lbl {
            font-size: .75rem;
            font-weight: 600;
            color: var(--muted);
        }

        .step.passed {
            background: rgba(16, 185, 129, .1);
            border-color: rgba(16, 185, 129, .4);
        }

        .step.passed .ico,
        .step.passed .lbl {
            color: var(--completed);
        }

        .step.active {
            background: rgba(79, 70, 229, .18);
            border-color: var(--primary);
            box-shadow: 0 0 18px var(--primary-glow);
            transform: translateY(-5px);
        }

        .step.active .ico {
            color: #a5b4fc;
            animation: pulse 2s infinite;
        }

        .step.active .lbl {
            color: #fff;
            font-weight: 700;
        }

        .step.future {
            opacity: .55;
            filter: grayscale(.8);
        }

        .dbg {
            margin-top: 28px;
            background: rgba(0, 0, 0, .5);
            border: 1px solid #334155;
            border-radius: 10px;
            padding: 14px 18px;
            max-width: 620px;
            width: 100%;
            font-size: .72rem;
            color: #64748b;
            text-align: left;
            line-height: 1.8;
        }

        .dbg strong {
            color: #94a3b8;
        }

        .dbg code {
            background: rgba(255, 255, 255, .06);
            padding: 1px 5px;
            border-radius: 4px;
            color: #a5b4fc;
        }

        .ok {
            color: #34d399;
        }

        .fail {
            color: #f87171;
        }

        .error-wrap {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 75vh;
            gap: 12px;
            text-align: center;
        }

        .error-wrap h2 {
            font-size: 1.4rem;
            color: #f87171;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
                filter: drop-shadow(0 0 0 rgba(165, 180, 252, 0));
            }

            50% {
                transform: scale(1.15);
                filter: drop-shadow(0 0 10px rgba(165, 180, 252, .8));
            }

            100% {
                transform: scale(1);
                filter: drop-shadow(0 0 0 rgba(165, 180, 252, 0));
            }
        }
    </style>
</head>

<body>

    <?php if ($dbError !== ''): ?>
    <div class="error-wrap">
        <h2>Error de Conexion a BD</h2>
        <p style="color:var(--muted)">
            <?php echo htmlspecialchars($dbError); ?>
        </p>
    </div>
</body>

</html>
<?php exit;
endif; ?>

<?php if (!$pedidoData): ?>
<div class="error-wrap">
    <h2>Pedido No Encontrado</h2>
    <p style="color:var(--muted)">Token: <code
            style="color:#a5b4fc;background:rgba(255,255,255,.07);padding:2px 8px;border-radius:6px;"><?php echo htmlspecialchars($token); ?></code>
    </p>
</div>
<div class="dbg">
    <strong>Diagnostico</strong><br><br>
    <strong>Token buscado:</strong> <code><?php echo htmlspecialchars($token); ?></code> &nbsp;|&nbsp;
    <strong>Hex:</strong> <code><?php echo htmlspecialchars($debugInfo['token_hex']); ?></code><br>
    <strong>Pedidos con token en BD:</strong>
    <span class="<?php echo ($debugInfo['tokens_totales'] > 0) ? 'ok' : 'fail'; ?>">
        <?php echo (int)$debugInfo['tokens_totales']; ?> pedidos
    </span><br>
    <strong>PHP Version:</strong> <code><?php echo htmlspecialchars($debugInfo['PHP_VER']); ?></code><br>
    <strong>HOST:</strong> <code><?php echo htmlspecialchars($debugInfo['HOST']); ?></code><br>
    <strong>SCRIPT:</strong> <code><?php echo htmlspecialchars($debugInfo['SCRIPT']); ?></code><br>
    <br>
    <strong>Tokens disponibles en la BD:</strong><br>
    <?php
    try {
        $stmtTok = $db->query("SELECT id, cliente_nombre, token_seguimiento, fase_actual, created_at FROM pedidos WHERE token_seguimiento IS NOT NULL ORDER BY id DESC LIMIT 10");
        $tokRows = $stmtTok->fetchAll(\PDO::FETCH_ASSOC);
        if ($tokRows) {
            echo '<table style="border-collapse:collapse;margin-top:6px;font-size:0.8rem;">';
            echo '<tr><th style="padding:4px 10px;border:1px solid #334;">ID</th><th style="padding:4px 10px;border:1px solid #334;">Cliente</th><th style="padding:4px 10px;border:1px solid #334;">Token</th><th style="padding:4px 10px;border:1px solid #334;">Fase</th><th style="padding:4px 10px;border:1px solid #334;">Creado</th></tr>';
            foreach ($tokRows as $row) {
                echo '<tr>';
                echo '<td style="padding:4px 10px;border:1px solid #334;">' . (int)$row['id'] . '</td>';
                echo '<td style="padding:4px 10px;border:1px solid #334;">' . htmlspecialchars((string)$row['cliente_nombre']) . '</td>';
                $match = $row['token_seguimiento'] === $token ? ' style="color:#34d399;font-weight:700;"' : '';
                echo '<td' . $match . ' style="padding:4px 10px;border:1px solid #334;">' . htmlspecialchars((string)$row['token_seguimiento']) . '</td>';
                echo '<td style="padding:4px 10px;border:1px solid #334;">' . htmlspecialchars((string)$row['fase_actual']) . '</td>';
                echo '<td style="padding:4px 10px;border:1px solid #334;">' . htmlspecialchars((string)$row['created_at']) . '</td>';
                echo '</tr>';
            }
            echo '</table>';
        }
        else {
            echo '<span class="fail">No hay pedidos con token en la base de datos.</span>';
        }
    }
    catch (Exception $e) {
        echo '<span class="fail">Error al listar tokens: ' . htmlspecialchars($e->getMessage()) . '</span>';
    }
?>
</div>
<div class="bg-cover"></div>
<div class="bg-overlay"></div>
</body>

</html>
<?php exit;
endif;

// ----- Pedido encontrado -----
$pid = $pedidoData['id'];
$fmtId = '#PED-' . str_pad($pid, 4, '0', STR_PAD_LEFT);
$cliente = $pedidoData['cliente_nombre'];
$estado = isset($pedidoData['estado']) ? $pedidoData['estado'] : 'activo';
$fase = $pedidoData['fase_actual'];
$areaNom = $pedidoData['area_nombre'];
$areaId = $pedidoData['area_actual_id'];
$estadoPago = $pedidoData['estado_pago'] ?? 'no_pago';
$totalPedido = floatval($pedidoData['total'] ?? 0);
$abonadoPedido = floatval($pedidoData['abonado'] ?? 0);
$fechaPedido = isset($pedidoData['created_at']) ? substr($pedidoData['created_at'], 0, 10) : '';

$steps = array();
$steps[] = array('id' => 'recepcion', 'nombre' => 'Guia Generada', 'icono' => '&#x1F4CB;');
foreach ($areasData as $ad) {
    $steps[] = $ad;
}
$steps[] = array('id' => 'finalizado', 'nombre' => 'Finalizado', 'icono' => '');

$cur = 0;
if ($estado === 'completado')
    $cur = count($steps) - 1;
elseif ($estado === 'cancelado')
    $cur = -2;
elseif (empty($areaId))
    $cur = 0;
else {
    foreach ($steps as $i => $s) {
        if ($s['id'] === 'recepcion' || $s['id'] === 'finalizado')
            continue;
        if ((string)$s['id'] === (string)$areaId) {
            $cur = $i;
            break;
        }
    }
}
?>

<div class="bg-cover"></div>
<div class="bg-overlay"></div>

<div class="header-logo">
    <?php if ($logoUrl): ?>
    <img src="<?php echo htmlspecialchars((string)$logoUrl); ?>" alt="Logo"
        onerror="this.src='<?php echo (string)$basePath; ?>/img/Logo.png';this.onerror=null;">
    <?php
else: ?>
    <img src="<?php echo (string)$basePath; ?>/img/Logo.png" alt="Logo" onerror="this.style.display='none';">
    <?php
endif; ?>
</div>

<div class="header-title">
    <h2>Seguimiento de Pedido</h2>
    <p>Actualizacion en tiempo real del estado de tu guia</p>
</div>

<div class="order-card">
    <h1>Pedido
        <?php echo htmlspecialchars($fmtId); ?>
    </h1>
    <p class="cname">
        <?php echo htmlspecialchars($cliente); ?>
    </p>
    <div class="badge <?php echo htmlspecialchars($estado); ?>">
        <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $estado))); ?>
    </div>
    <div class="area-txt" id="areaDisplay">
        <?php
if ($estado === 'completado')
    echo '&#x2705; Listo &mdash; Todo Terminado (puede pasar a recoger)';
elseif ($estado === 'cancelado')
    echo '&#x274C; Pedido Cancelado';
elseif (empty($areaId))
    echo '&#x1F4CB; Guia Generada &mdash; En Recepcion';
else {
    $faseTxt = '';
    if ($fase === 'recepcion')
        $faseTxt = 'En Recepción: ';
    else if ($fase === 'preparado')
        $faseTxt = 'Lista / Espera: ';
    else
        $faseTxt = 'En Proceso: ';
    echo '&#x1F4CD; ' . $faseTxt . htmlspecialchars($areaNom);
}
?>
    </div>

    <?php
// ── Badge de pago ──
$badgeStyle = 'display:inline-block;margin-top:10px;padding:6px 16px;border-radius:20px;font-size:.82rem;font-weight:700;';
if ($estadoPago === 'pago_completo') {
    $bg = 'rgba(16,185,129,.18)';
    $color = '#34d399';
    $border = '#34d39944';
    $lbl = '✅ Pago Completo — $' . number_format($totalPedido, 0, ',', '.') . ($fechaPedido ? '  ·  ' . $fechaPedido : '');
    echo "<div style=\"{$badgeStyle}background:{$bg};color:{$color};border:1px solid {$border};\">{$lbl}</div>";
}
elseif ($estadoPago === 'abono') {
    $bg = 'rgba(245,158,11,.18)';
    $color = '#fbbf24';
    $border = '#fbbf2444';
    $saldo = $totalPedido - $abonadoPedido;
    $lbl = '💰 Abono — Total: $' . number_format($totalPedido, 0, ',', '.') . ($fechaPedido ? '  ·  ' . $fechaPedido : '');
    $lbl2 = '↳ Abonado: $' . number_format($abonadoPedido, 0, ',', '.') . '  |  Saldo: $' . number_format($saldo, 0, ',', '.');
    echo "<div style=\"{$badgeStyle}background:{$bg};color:{$color};border:1px solid {$border};\">{$lbl}</div>";
    echo "<div style=\"{$badgeStyle}background:{$bg};color:{$color};border:1px solid {$border};margin-left:0;\">{$lbl2}</div>";
}
else {
    // no_pago
    $bg = 'rgba(239,68,68,.18)';
    $color = '#f87171';
    $border = '#f8717144';
    $lbl = '❌ No Pago — Deuda: $' . number_format($totalPedido, 0, ',', '.') . ($fechaPedido ? '  ·  ' . $fechaPedido : '');
    echo "<div style=\"{$badgeStyle}background:{$bg};color:{$color};border:1px solid {$border};\">{$lbl}</div>";
}
?>
</div>

<div class="timeline" id="timelineBox">
    <?php foreach ($steps as $i => $step):
    $ico = isset($step['icono']) ? trim($step['icono']) : '';
    if ($ico === '')
        $ico = seguimIcono($step['nombre']);
    if ($cur < 0)
        $cls = 'future';
    elseif ($i < $cur)
        $cls = 'passed';
    elseif ($i === $cur)
        $cls = 'active';
    else
        $cls = 'future';
?>
    <div class="step <?php echo $cls; ?>">
        <div class="ico">
            <?php echo $ico; ?>
        </div>
        <div class="lbl">
            <?php echo htmlspecialchars($step['nombre']); ?>
        </div>
    </div>
    <?php
endforeach; ?>
</div>

<div id="jsdata" data-token="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>"
    data-areas='<?php echo addslashes($areasJson); ?>'>
</div>

<script>
    var jsd = document.getElementById('jsdata');
    var TOKEN = jsd.getAttribute('data-token');
    var AREAS = JSON.parse(jsd.getAttribute('data-areas').replace(/\\'/g, "'"));
    var pollTimer;

    function pollEstado() {
        var base = window.location.href.split('?')[0];
        var url = base + '?token=' + encodeURIComponent(TOKEN) + '&poll=1';
        fetch(url).then(function (r) {
            return r.json();
        }).then(function (data) {
            if (!data || !data.estado) return;
            var txt = document.getElementById('areaDisplay');
            if (data.estado === 'completado') {
                txt.innerHTML = '&#x2705; Listo &mdash; Todo Terminado';
                txt.style.color = '#34d399';
                clearInterval(pollTimer);
            } else if (!data.areaActualId) {
                txt.innerHTML = '&#x1F4CB; Guia Generada &mdash; En Recepcion';
            } else {
                var faseTxt = '';
                if (data.faseActual === 'recepcion') faseTxt = 'En Recepción: ';
                else if (data.faseActual === 'preparado') faseTxt = 'Lista / Espera: ';
                else faseTxt = 'En Proceso: ';
                txt.innerHTML = '&#x1F4CD; ' + faseTxt + (data.areaNombre || '...');
            }
            var items = document.querySelectorAll('.step');
            var cur = 0;
            if (data.estado === 'completado') {
                cur = items.length - 1;
            } else if (!data.areaActualId) {
                cur = 0;
            } else {
                for (var i = 0; i < AREAS.length; i++) {
                    if (String(AREAS[i].id) === String(data.areaActualId)) { cur = i + 1; break; }
                }
            }
            for (var j = 0; j < items.length; j++) {
                items[j].classList.remove('passed', 'active', 'future');
                if (j < cur) items[j].classList.add('passed');
                else if (j === cur) items[j].classList.add('active');
                else items[j].classList.add('future');
            }
        }).catch(function () { });
    }
    // Refrescar cada 5 segundos para tiempo real
    pollTimer = setInterval(pollEstado, 5000);
</script>
</body>

</html>