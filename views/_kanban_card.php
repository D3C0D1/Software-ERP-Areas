<div class="kanban-card">
    <div class="card-top">
        <span class="card-id">#<?= $p['id'] ?></span>
        <div style="display:flex; align-items:center; gap:8px;">
            <span class="card-area"><?= htmlspecialchars($p['area_nombre'] ?? 'Sin Área') ?></span>
            <button class="btn-notify" onclick="abrirModalNotificar('<?= htmlspecialchars(addslashes($p['cliente_nombre'] ?? '')) ?>', '<?= htmlspecialchars($kanbanCat ?? '') ?>')" title="Notificar a Superior">
                <i class="fas fa-bell"></i>
            </button>
        </div>
    </div>
    <div class="card-client"><?= htmlspecialchars($p['cliente_nombre'] ?? 'Sin cliente') ?></div>
    <div class="card-desc"><?= htmlspecialchars($p['descripcion'] ?? 'Sin descripción') ?></div>
    <div class="card-footer">
        <span class="card-date">
            <?php if (!empty($p['fecha_entrega_esperada'])): ?>
                <i class="fas fa-calendar-alt" style="margin-right:3px;"></i>
                <?= date('d M Y', strtotime($p['fecha_entrega_esperada'])) ?>
            <?php else: ?>
                Sin fecha
            <?php endif; ?>
        </span>
        <?php
            $pagoCls = 'pago-nopago'; $pagoTxt = 'No Pago';
            if (($p['estado_pago'] ?? '') === 'pago_completo') { $pagoCls = 'pago-completo'; $pagoTxt = 'Pagado'; }
            elseif (($p['estado_pago'] ?? '') === 'abono') { $pagoCls = 'pago-abono'; $pagoTxt = 'Abono'; }
        ?>
        <span class="card-pago <?= $pagoCls ?>"><?= $pagoTxt ?></span>
    </div>
</div>
