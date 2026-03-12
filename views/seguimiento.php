<?php
// Vista Pública de Seguimiento de Pedidos
$token = $_GET['token'] ?? '';
if (empty($token)) {
    die("Token de seguimiento inválido.");
}

// Check basePath from PHP like in other views
$scriptName = dirname($_SERVER['SCRIPT_NAME']);
$basePath = rtrim($scriptName, '/\\');
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seguimiento de Pedido | Banner</title>
    <!-- Incluyendo fuentes de Google para tipografía moderna -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-glow: rgba(79, 70, 229, 0.5);
            --completed: #10b981;
            --completed-glow: rgba(16, 185, 129, 0.4);
            --bg-color: #0f172a;
            --card-bg: rgba(255, 255, 255, 0.08);
            --card-border: rgba(255, 255, 255, 0.15);
            --text-light: #f8fafc;
            --text-muted: #94a3b8;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            /* LEON.jpg as requested with dark overlay */
            background-image: linear-gradient(rgba(15, 23, 42, 0.82), rgba(15, 23, 42, 0.82)), url('<?= $basePath?>/img/LEON.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: var(--text-light);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 10px 20px;
        }

        .header-logo {
            margin-bottom: 20px;
            margin-top: 10px;
            text-align: center;
            animation: fadeInDown 0.8s ease;
        }

        .header-logo img {
            max-width: 250px;
            height: auto;
            object-fit: contain;
            /* Optional glow to make the logo pop</h3>*/
            filter: drop-shadow(0 0 10px rgba(255, 255, 255, 0.2));
        }

        .order-info {
            background: rgba(15, 23, 42, 0.7);
            border: 1px solid var(--card-border);
            border-radius: 16px;
            padding: 18px 32px;
            text-align: center;
            margin-bottom: 30px;
            backdrop-filter: blur(10px);
            width: 100%;
            max-width: 600px;
            animation: fadeInUp 0.8s ease;
        }

        .order-info h1 {
            font-size: 1.6rem;
            font-weight: 700;
            margin-bottom: 8px;
            background: linear-gradient(135deg, #a5b4fc, #818cf8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .order-info p {
            color: var(--text-muted);
            font-size: 1rem;
            margin-bottom: 5px;
        }

        .order-info .client-name {
            font-size: 1.15rem;
            font-weight: 600;
            color: var(--text-light);
            margin-bottom: 2px;
        }

        .current-area-txt {
            margin-top: 15px;
            font-size: 1.25rem;
            color: #38bdf8;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        /* Timeline Container */
        .timeline-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
            gap: 20px;
            width: 100%;
            max-width: 1100px;
            animation: fadeIn 1.2s ease;
        }

        /* Tile / Card */
        .timeline-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 12px;
            background: var(--card-bg);
            border: 1.5px solid var(--card-border);
            border-radius: 16px;
            padding: 20px 15px;
            width: 130px;
            min-height: 120px;
            text-align: center;
            backdrop-filter: blur(12px);
            transition: all 0.4s ease;
            position: relative;
        }

        /* Connecting lines between tiles for desktop */
        @media (min-width: 768px) {
            .timeline-item:not(:last-child)::after {
                content: '';
                position: absolute;
                top: 50%;
                right: -21px;
                /* Gap is 20, line crosses gap */
                width: 20px;
                height: 2px;
                background: var(--card-border);
                z-index: -1;
                transition: all 0.4s ease;
            }
        }

        .timeline-item .icon {
            font-size: 1.8rem;
            color: var(--text-muted);
            transition: color 0.4s ease, transform 0.4s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
        }

        .timeline-item .title {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-muted);
            transition: color 0.4s ease;
            line-height: 1.2;
        }

        /* Status States */
        .timeline-item.passed {
            background: rgba(16, 185, 129, 0.1);
            border-color: rgba(16, 185, 129, 0.4);
        }

        .timeline-item.passed .icon {
            color: var(--completed);
        }

        .timeline-item.passed .title {
            color: var(--text-light);
        }

        @media (min-width: 768px) {
            .timeline-item.passed:not(:last-child)::after {
                background: var(--completed);
            }
        }

        .timeline-item.active {
            background: rgba(79, 70, 229, 0.15);
            border-color: var(--primary);
            box-shadow: 0 0 20px var(--primary-glow);
            transform: translateY(-5px);
        }

        .timeline-item.active .icon {
            color: #a5b4fc;
            animation: pulse 2s infinite;
        }

        .timeline-item.active .title {
            color: #fff;
            font-weight: 700;
        }

        .timeline-item.future {
            opacity: 0.6;
            filter: grayscale(0.8);
        }

        /* Spinner / Loading state */
        .loading-overlay {
            position: fixed;
            inset: 0;
            background: var(--bg-color);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            flex-direction: column;
            gap: 20px;
            transition: opacity 0.5s ease;
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid rgba(255, 255, 255, 0.1);
            border-top-color: var(--primary);
            border-radius: 50%;
            animation: spin 1s infinite linear;
        }

        /* Animations */
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                scale: 0.95;
            }

            to {
                opacity: 1;
                scale: 1;
            }
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
                filter: drop-shadow(0 0 0 rgba(165, 180, 252, 0));
            }

            50% {
                transform: scale(1.15);
                filter: drop-shadow(0 0 10px rgba(165, 180, 252, 0.8));
            }

            100% {
                transform: scale(1);
                filter: drop-shadow(0 0 0 rgba(165, 180, 252, 0));
            }
        }

        .status-badge {
            display: inline-block;
            margin-top: 10px;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-badge.completado {
            background: rgba(16, 185, 129, 0.2);
            color: #34d399;
        }

        .status-badge.cancelado {
            background: rgba(239, 68, 68, 0.2);
            color: #f87171;
        }

        .status-badge.en_curso {
            background: rgba(99, 102, 241, 0.2);
            color: #a5b4fc;
        }

        .status-badge.pendiente {
            background: rgba(245, 158, 11, 0.2);
            color: #fbbf24;
        }
    </style>
</head>

<body>

    <!-- Loading Screen -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner"></div>
        <p style="color: var(--text-muted); font-weight: 500;">Buscando información de tu pedido...</p>
    </div>

    <!-- Main Content -->
    <div class="header-logo">
        <img src="<?= $basePath?>/img/Logo.png" alt="Banner Logotipo" id="empresaLogo"
            onerror="this.src='https://via.placeholder.com/280x80?text=Banner+Logo'">
    </div>

    <div class="order-info" id="orderInfo" style="display: none;">
        <h1 id="orderTitle">Pedido #...</h1>
        <p class="client-name" id="clientName">Cargando cliente...</p>
        <div class="status-badge" id="orderStatusBadge">...</div>
        <div id="paymentBadgesContainer"
            style="margin-top:14px; display:flex; flex-direction:column; align-items:center; gap:10px; width:100%;">
        </div>
        <div class="current-area-txt" id="currentAreaText" style="margin-top:14px;">📍 Área Actual: ...</div>
    </div>

    <div class="timeline-container" id="timelineContainer">
        <!-- Renderizado dinámico de JS -->
    </div>

    <script>
        const TOKEN = "<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8')?>";
        const API_URL = "<?= $basePath?>/api/seguimiento/" + TOKEN;

        // Mapeo sugerido de íconos por nombre de área (Fallback a cajón si no coincide)
        const getIconForArea = (name) => {
            const n = name.toLowerCase();
            if (n.includes('recepción') || n.includes('recepcion')) return '📥';
            if (n.includes('mensajería') || n.includes('mensajeria') || n.includes('bordado')) return '✉️';
            if (n.includes('diseño') || n.includes('diseno')) return '🖌️';
            if (n.includes('impresión') || n.includes('impresion')) return '🖨️';
            if (n.includes('sublimado')) return '🔥';
            if (n.includes('confección') || n.includes('confeccion')) return '✂️';
            if (n.includes('calidad') || n.includes('prueba')) return '📋';
            if (n.includes('finalizado') || n.includes('empaque')) return '🏁';
            return '📦';
        };

        let lastStateHash = "";

        async function pullStatus() {
            try {
                const response = await fetch(API_URL);
                if (!response.ok) {
                    // Si tira 404, detener polling y avisar
                    if (response.status === 404) {
                        document.getElementById('loadingOverlay').innerHTML = `
                            <h2 style="color:#ef4444; margin-bottom:10px;">Pedido No Encontrado</h2>
                            <p style="color:var(--text-muted);">El enlace no es válido o el pedido no existe.</p>
                            <div style="margin-top:20px; font-size: 0.75rem; background:rgba(0,0,0,0.5); padding:10px; border-radius:8px; text-align:left; color:#94a3b8; max-width:90%;">
                                <strong>Debug Info:</strong><br>
                                Token Query: ${TOKEN}<br>
                                Fetch URL: ${API_URL}<br>
                                HTTP: 404
                            </div>
                        `;
                        return;
                    }
                    throw new Error("HTTP Status " + response.status);
                }

                const json = await response.json();
                if (json.status !== 'success') throw new Error(json.message);

                renderUI(json.data);

                // Hide loading on first success
                const overlay = document.getElementById('loadingOverlay');
                if (overlay) {
                    overlay.style.opacity = '0';
                    setTimeout(() => overlay.remove(), 500);
                    document.getElementById('orderInfo').style.display = 'block';
                }
            } catch (err) {
                console.error("Error al obtener estado:", err);
            }
        }

        function renderUI(data) {
            const pedido = data.pedido;
            const areas = data.areas || [];

            // Format order id (PED-0000)
            const formattedId = "#PED-" + String(pedido.id).padStart(4, '0');
            document.getElementById('orderTitle').innerText = "Pedido " + formattedId;
            document.getElementById('clientName').innerText = pedido.cliente_nombre;

            // Update Badge
            const badge = document.getElementById('orderStatusBadge');
            badge.innerText = pedido.estado.replace('_', ' ');
            badge.className = "status-badge " + pedido.estado;

            // Render Payment Badges
            const paymentContainer = document.getElementById('paymentBadgesContainer');
            paymentContainer.innerHTML = '';

            const paymentState = pedido.estado_pago || 'no_pago';
            const amount = parseFloat(pedido.total) || 0;
            const abonado = parseFloat(pedido.abonado) || 0;
            const date = (pedido.created_at || '').substring(0, 10);
            const badgeBase = 'display:inline-block;padding:8px 18px;border-radius:20px;font-size:0.88rem;font-weight:700;letter-spacing:0.3px;';

            function mkBadge(text, bg, color) {
                const el = document.createElement('div');
                el.setAttribute('style', badgeBase + 'background:' + bg + ';color:' + color + ';border:1px solid ' + color + '44;');
                el.innerText = text;
                return el;
            }

            if (paymentState === 'pago_completo') {
                paymentContainer.appendChild(
                    mkBadge('✅ Pago Completo — $' + amount.toLocaleString('es-CO') + (date ? '  · ' + date : ''),
                        'rgba(16,185,129,0.15)', '#34d399')
                );
            } else if (paymentState === 'abono') {
                paymentContainer.appendChild(
                    mkBadge('💰 Abono — Total: $' + amount.toLocaleString('es-CO') + (date ? '  · ' + date : ''),
                        'rgba(245,158,11,0.15)', '#fbbf24')
                );
                paymentContainer.appendChild(
                    mkBadge('↳ Abonado: $' + abonado.toLocaleString('es-CO') + '   |   Saldo: $' + (amount - abonado).toLocaleString('es-CO'),
                        'rgba(245,158,11,0.1)', '#fbbf24')
                );
            } else {
                // no_pago
                paymentContainer.appendChild(
                    mkBadge('❌ No Pago — Deuda: $' + amount.toLocaleString('es-CO') + (date ? '  · ' + date : ''),
                        'rgba(239,68,68,0.15)', '#f87171')
                );
            }

            // Construir Array Lineal de Áreas / Pasos (Forzar "Finalizado" al final para que aparezca)
            const steps = [];

            // Asumimos que las áreas vienen ordenadas por 'orden ASC' de la DB
            for (let i = 0; i < areas.length; i++) {
                steps.push({
                    id: areas[i].id,
                    name: areas[i].nombre,
                    icon: getIconForArea(areas[i].nombre)
                });
            }
            // Agregamos el paso Finalizado a la cola
            steps.push({
                id: 'finalizado',
                name: 'Finalizado',
                icon: getIconForArea('Finalizado')
            });

            // Determinar Step Actual
            // Logica:
            // Si el estado es 'cancelado', todo queda gris o finalizado mal.
            // Si el estado es 'completado', el current Step es 'finalizado'.
            // Sino, buscamos el indice en 'steps' donde step.id == pedido.area_actual_id

            let currentIndex = 0;
            if (pedido.estado === 'completado') {
                currentIndex = steps.length - 1; // El ultimo es finalizado
            } else if (pedido.estado === 'cancelado') {
                currentIndex = -1; // Especial
            } else {
                const idx = steps.findIndex(s => s.id == pedido.area_actual_id);
                if (idx !== -1) currentIndex = idx;
            }

            // Create simple hash to avoid unnecessary DOM paints
            const hash = `${pedido.id}_${pedido.estado}_${pedido.area_actual_id}_${currentIndex}`;
            if (hash === lastStateHash) return; // No changes
            lastStateHash = hash;

            // Redibujar Timeline
            const container = document.getElementById('timelineContainer');
            container.innerHTML = "";

            steps.forEach((step, index) => {
                const item = document.createElement('div');

                // Class Logic
                let cssClass = "timeline-item ";
                if (currentIndex === -1) {
                    cssClass += "future"; // Cancelado
                }
                else if (index < currentIndex) {
                    cssClass += "passed";
                } else if (index === currentIndex) {
                    if (pedido.estado === 'completado') {
                        cssClass += "passed active"; // Active and completed!
                    } else {
                        cssClass += "active";
                    }
                } else {
                    cssClass += "future";
                }

                item.className = cssClass;

                // Inner HTML
                item.innerHTML = `
                    <div class="icon">${step.icon}</div>
                    <div class="title">${step.name}</div>
                `;

                container.appendChild(item);
            });
        }

        // Init Polling (cada 3 segundos)
        pullStatus();
        setInterval(pullStatus, 3000);

    </script>
</body>

</html>