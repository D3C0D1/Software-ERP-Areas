-- ============================================================
-- SCRIPT DE CORRECCIÓN FINAL: SUMA 5 HORAS
-- Para aplicar cuando los datos fueron restados de más (2 veces)
-- 
-- ⚠️ Ejecutar UNA SOLA VEZ en phpMyAdmin.
-- ============================================================

START TRANSACTION;

-- Tabla: pedidos
UPDATE `pedidos` SET `last_movement_at` = DATE_ADD(`last_movement_at`, INTERVAL 5 HOUR) WHERE `last_movement_at` IS NOT NULL;
UPDATE `pedidos` SET `created_at` = DATE_ADD(`created_at`, INTERVAL 5 HOUR) WHERE `created_at` IS NOT NULL;
UPDATE `pedidos` SET `deleted_at` = DATE_ADD(`deleted_at`, INTERVAL 5 HOUR) WHERE `deleted_at` IS NOT NULL;

-- Tabla: movimientos_pedido
UPDATE `movimientos_pedido` SET `created_at` = DATE_ADD(`created_at`, INTERVAL 5 HOUR) WHERE `created_at` IS NOT NULL;

-- Tabla: auditoria_logs
UPDATE `auditoria_logs` SET `created_at` = DATE_ADD(`created_at`, INTERVAL 5 HOUR) WHERE `created_at` IS NOT NULL;

-- Tabla: archivos
UPDATE `archivos` SET `created_at` = DATE_ADD(`created_at`, INTERVAL 5 HOUR) WHERE `created_at` IS NOT NULL;

-- Tabla: notificaciones_salientes
UPDATE `notificaciones_salientes` SET `created_at` = DATE_ADD(`created_at`, INTERVAL 5 HOUR) WHERE `created_at` IS NOT NULL;

COMMIT;

-- ============================================================
-- VERIFICACIÓN: Después de ejecutar, estos son los valores esperados:
-- Pedido id=1 (Uriel)    created_at → 2026-03-04 12:12:04
-- Pedido id=7 (FERNANDO) created_at → 2026-03-04 16:21:35
-- Pedido id=12 (Juan)    created_at → 2026-03-04 18:21:18
-- ============================================================
SELECT id, cliente_nombre, created_at, last_movement_at FROM pedidos ORDER BY id;
