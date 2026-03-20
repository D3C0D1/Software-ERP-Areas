<div class="kanban-card" onclick="openHistorial('<?= addslashes($c['nombre']) ?>', '<?= addslashes($c['telefono']) ?>')" style="cursor:pointer;">
    <div class="card-client"><?= htmlspecialchars($c['nombre'] ?? 'Sin nombre') ?></div>
    
    <div class="card-info">
        <i class="fas fa-phone-alt"></i> <?= htmlspecialchars($c['telefono'] ?? 'Sin teléfono') ?>
    </div>
    
    <?php if (!empty($c['ultima_compra'])): ?>
    <div class="card-info">
        <i class="fas fa-history" title="Última compra"></i> 
        <?= date('d M Y, H:i', strtotime($c['ultima_compra'])) ?>
    </div>
    <?php endif; ?>

    <div class="card-footer">
        <span class="card-badge badge-compras">
            <?= (int) ($c['compras'] ?? 0) ?> compras
        </span>
        <span class="card-badge badge-monto">
            $<?= number_format((float)($c['monto_total'] ?? 0), 2) ?>
        </span>
    </div>
</div>
