-- ============================================================
-- CORRECCIÓN FINAL HOSTINGER: SUMA 5 HORAS
-- Igual que el fix local: revierte la doble resta
-- Ejecutar UNA SOLA VEZ en phpMyAdmin de Hostinger
-- ============================================================

SET SQL_SAFE_UPDATES = 0;
START TRANSACTION;

-- Tabla: pedidos
UPDATE `pedidos` SET
    `created_at`       = DATE_ADD(`created_at`,       INTERVAL 5 HOUR),
    `updated_at`       = DATE_ADD(`updated_at`,       INTERVAL 5 HOUR),
    `last_movement_at` = DATE_ADD(`last_movement_at`, INTERVAL 5 HOUR)
WHERE `created_at` IS NOT NULL;

UPDATE `pedidos` SET
    `deleted_at` = DATE_ADD(`deleted_at`, INTERVAL 5 HOUR)
WHERE `deleted_at` IS NOT NULL;

-- Tabla: movimientos_pedido
UPDATE `movimientos_pedido` SET
    `created_at` = DATE_ADD(`created_at`, INTERVAL 5 HOUR)
WHERE `created_at` IS NOT NULL;

-- Tabla: auditoria_logs
UPDATE `auditoria_logs` SET
    `created_at` = DATE_ADD(`created_at`, INTERVAL 5 HOUR)
WHERE `created_at` IS NOT NULL;

-- Tabla: areas
UPDATE `areas` SET
    `created_at` = DATE_ADD(`created_at`, INTERVAL 5 HOUR),
    `updated_at` = DATE_ADD(`updated_at`, INTERVAL 5 HOUR)
WHERE `created_at` IS NOT NULL;

UPDATE `areas` SET
    `deleted_at` = DATE_ADD(`deleted_at`, INTERVAL 5 HOUR)
WHERE `deleted_at` IS NOT NULL;

-- Tabla: archivos
UPDATE `archivos` SET
    `created_at` = DATE_ADD(`created_at`, INTERVAL 5 HOUR)
WHERE `created_at` IS NOT NULL;

UPDATE `archivos` SET
    `deleted_at` = DATE_ADD(`deleted_at`, INTERVAL 5 HOUR)
WHERE `deleted_at` IS NOT NULL;

-- Tabla: usuarios
UPDATE `usuarios` SET
    `created_at`    = DATE_ADD(`created_at`,    INTERVAL 5 HOUR),
    `updated_at`    = DATE_ADD(`updated_at`,    INTERVAL 5 HOUR),
    `last_activity` = DATE_ADD(`last_activity`, INTERVAL 5 HOUR)
WHERE `created_at` IS NOT NULL;

UPDATE `usuarios` SET
    `deleted_at` = DATE_ADD(`deleted_at`, INTERVAL 5 HOUR)
WHERE `deleted_at` IS NOT NULL;

-- Tabla: roles
UPDATE `roles` SET
    `created_at` = DATE_ADD(`created_at`, INTERVAL 5 HOUR),
    `updated_at` = DATE_ADD(`updated_at`, INTERVAL 5 HOUR)
WHERE `created_at` IS NOT NULL;

-- Tabla: notificaciones_salientes
UPDATE `notificaciones_salientes` SET
    `created_at` = DATE_ADD(`created_at`, INTERVAL 5 HOUR),
    `updated_at` = DATE_ADD(`updated_at`, INTERVAL 5 HOUR)
WHERE `created_at` IS NOT NULL;

-- Tabla: pagos
UPDATE `pagos` SET
    `fecha_pago` = DATE_ADD(`fecha_pago`, INTERVAL 5 HOUR),
    `created_at` = DATE_ADD(`created_at`, INTERVAL 5 HOUR),
    `updated_at` = DATE_ADD(`updated_at`, INTERVAL 5 HOUR)
WHERE `created_at` IS NOT NULL;

-- Tabla: devoluciones
UPDATE `devoluciones` SET
    `created_at` = DATE_ADD(`created_at`, INTERVAL 5 HOUR),
    `updated_at` = DATE_ADD(`updated_at`, INTERVAL 5 HOUR)
WHERE `created_at` IS NOT NULL;

-- Tabla: configuracion
UPDATE `configuracion` SET
    `updated_at` = DATE_ADD(`updated_at`, INTERVAL 5 HOUR)
WHERE `updated_at` IS NOT NULL;

-- Tabla: workflow_transiciones
UPDATE `workflow_transiciones` SET
    `created_at` = DATE_ADD(`created_at`, INTERVAL 5 HOUR)
WHERE `created_at` IS NOT NULL;

COMMIT;
SET SQL_SAFE_UPDATES = 1;

-- ============================================================
-- VERIFICACIÓN: Valores esperados tras ejecutar
-- FERNANDO (id=7) → created_at: 2026-03-04 16:21:xx
-- Juan (id=12)    → created_at: 2026-03-04 18:21:xx
-- ============================================================
SELECT id, cliente_nombre, created_at, last_movement_at
FROM pedidos ORDER BY id;
