-- ============================================================
-- SCRIPT CORRECCIÓN HOSTINGER: UTC → Colombia (UTC-5)
-- Restar 5 horas a todos los registros.
-- ⚠️ Ejecutar UNA SOLA VEZ en phpMyAdmin de Hostinger.
-- ============================================================

SET time_zone = '-05:00';
SET SQL_SAFE_UPDATES = 0;

START TRANSACTION;

-- --------------------------------------------------------
-- Tabla: pedidos
-- Se actualiza TODO en un solo UPDATE por fila para evitar
-- que MySQL auto-actualice el campo updated_at
-- --------------------------------------------------------
UPDATE `pedidos` SET
    `created_at`       = DATE_SUB(`created_at`,       INTERVAL 5 HOUR),
    `updated_at`       = DATE_SUB(`updated_at`,       INTERVAL 5 HOUR),
    `last_movement_at` = DATE_SUB(`last_movement_at`, INTERVAL 5 HOUR)
WHERE `created_at` IS NOT NULL;

UPDATE `pedidos` SET
    `deleted_at` = DATE_SUB(`deleted_at`, INTERVAL 5 HOUR)
WHERE `deleted_at` IS NOT NULL;

-- --------------------------------------------------------
-- Tabla: movimientos_pedido
-- --------------------------------------------------------
UPDATE `movimientos_pedido` SET
    `created_at` = DATE_SUB(`created_at`, INTERVAL 5 HOUR)
WHERE `created_at` IS NOT NULL;

-- --------------------------------------------------------
-- Tabla: auditoria_logs
-- --------------------------------------------------------
UPDATE `auditoria_logs` SET
    `created_at` = DATE_SUB(`created_at`, INTERVAL 5 HOUR)
WHERE `created_at` IS NOT NULL;

-- --------------------------------------------------------
-- Tabla: areas
-- --------------------------------------------------------
UPDATE `areas` SET
    `created_at` = DATE_SUB(`created_at`, INTERVAL 5 HOUR),
    `updated_at` = DATE_SUB(`updated_at`, INTERVAL 5 HOUR)
WHERE `created_at` IS NOT NULL;

UPDATE `areas` SET
    `deleted_at` = DATE_SUB(`deleted_at`, INTERVAL 5 HOUR)
WHERE `deleted_at` IS NOT NULL;

-- --------------------------------------------------------
-- Tabla: archivos
-- --------------------------------------------------------
UPDATE `archivos` SET
    `created_at` = DATE_SUB(`created_at`, INTERVAL 5 HOUR)
WHERE `created_at` IS NOT NULL;

UPDATE `archivos` SET
    `deleted_at` = DATE_SUB(`deleted_at`, INTERVAL 5 HOUR)
WHERE `deleted_at` IS NOT NULL;

-- --------------------------------------------------------
-- Tabla: usuarios
-- --------------------------------------------------------
UPDATE `usuarios` SET
    `created_at`    = DATE_SUB(`created_at`,    INTERVAL 5 HOUR),
    `updated_at`    = DATE_SUB(`updated_at`,    INTERVAL 5 HOUR),
    `last_activity` = DATE_SUB(`last_activity`, INTERVAL 5 HOUR)
WHERE `created_at` IS NOT NULL;

UPDATE `usuarios` SET
    `deleted_at` = DATE_SUB(`deleted_at`, INTERVAL 5 HOUR)
WHERE `deleted_at` IS NOT NULL;

-- --------------------------------------------------------
-- Tabla: roles
-- --------------------------------------------------------
UPDATE `roles` SET
    `created_at` = DATE_SUB(`created_at`, INTERVAL 5 HOUR),
    `updated_at` = DATE_SUB(`updated_at`, INTERVAL 5 HOUR)
WHERE `created_at` IS NOT NULL;

-- --------------------------------------------------------
-- Tabla: notificaciones_salientes
-- --------------------------------------------------------
UPDATE `notificaciones_salientes` SET
    `created_at` = DATE_SUB(`created_at`, INTERVAL 5 HOUR),
    `updated_at` = DATE_SUB(`updated_at`, INTERVAL 5 HOUR)
WHERE `created_at` IS NOT NULL;

-- --------------------------------------------------------
-- Tabla: pagos
-- --------------------------------------------------------
UPDATE `pagos` SET
    `fecha_pago`  = DATE_SUB(`fecha_pago`,  INTERVAL 5 HOUR),
    `created_at`  = DATE_SUB(`created_at`,  INTERVAL 5 HOUR),
    `updated_at`  = DATE_SUB(`updated_at`,  INTERVAL 5 HOUR)
WHERE `created_at` IS NOT NULL;

-- --------------------------------------------------------
-- Tabla: devoluciones
-- --------------------------------------------------------
UPDATE `devoluciones` SET
    `created_at` = DATE_SUB(`created_at`, INTERVAL 5 HOUR),
    `updated_at` = DATE_SUB(`updated_at`, INTERVAL 5 HOUR)
WHERE `created_at` IS NOT NULL;

-- --------------------------------------------------------
-- Tabla: configuracion
-- --------------------------------------------------------
UPDATE `configuracion` SET
    `updated_at` = DATE_SUB(`updated_at`, INTERVAL 5 HOUR)
WHERE `updated_at` IS NOT NULL;

-- --------------------------------------------------------
-- Tabla: workflow_transiciones
-- --------------------------------------------------------
UPDATE `workflow_transiciones` SET
    `created_at` = DATE_SUB(`created_at`, INTERVAL 5 HOUR)
WHERE `created_at` IS NOT NULL;

COMMIT;

SET SQL_SAFE_UPDATES = 1;

-- ============================================================
-- VERIFICACIÓN FINAL: Las horas esperadas para pedidos son:
-- id=2  Alexis    → created_at: 2026-03-04 12:49:xx
-- id=7  FERNANDO  → created_at: 2026-03-04 16:21:xx
-- id=12 Juan      → created_at: 2026-03-04 18:21:xx
-- ============================================================
SELECT id, cliente_nombre, created_at, updated_at, last_movement_at
FROM pedidos
ORDER BY id;
