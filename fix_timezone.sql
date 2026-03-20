-- ============================================================
-- SCRIPT DE CORRECCIÓN DE ZONA HORARIA
-- Convierte fechas de UTC → Colombia (UTC-5)
-- Resta 5 horas a todas las columnas timestamp de cada tabla
-- 
-- ⚠️  IMPORTANTE: Ejecutar UNA SOLA VEZ.
--     Si se ejecuta de nuevo, las fechas quedarán incorrectas.
-- ============================================================

START TRANSACTION;

-- --------------------------------------------------------
-- Tabla: pedidos
-- Columnas: last_movement_at, created_at, updated_at, deleted_at
-- --------------------------------------------------------
UPDATE `pedidos` SET
    `last_movement_at` = DATE_SUB(`last_movement_at`, INTERVAL 5 HOUR) WHERE `last_movement_at` IS NOT NULL;
UPDATE `pedidos` SET
    `created_at` = DATE_SUB(`created_at`, INTERVAL 5 HOUR) WHERE `created_at` IS NOT NULL;
UPDATE `pedidos` SET
    `updated_at` = DATE_SUB(`updated_at`, INTERVAL 5 HOUR) WHERE `updated_at` IS NOT NULL;
UPDATE `pedidos` SET
    `deleted_at` = DATE_SUB(`deleted_at`, INTERVAL 5 HOUR) WHERE `deleted_at` IS NOT NULL;

-- --------------------------------------------------------
-- Tabla: movimientos_pedido
-- Columnas: created_at
-- --------------------------------------------------------
UPDATE `movimientos_pedido` SET
    `created_at` = DATE_SUB(`created_at`, INTERVAL 5 HOUR) WHERE `created_at` IS NOT NULL;

-- --------------------------------------------------------
-- Tabla: auditoria_logs
-- Columnas: created_at
-- --------------------------------------------------------
UPDATE `auditoria_logs` SET
    `created_at` = DATE_SUB(`created_at`, INTERVAL 5 HOUR) WHERE `created_at` IS NOT NULL;

-- --------------------------------------------------------
-- Tabla: areas
-- Columnas: created_at, updated_at, deleted_at
-- --------------------------------------------------------
UPDATE `areas` SET
    `created_at` = DATE_SUB(`created_at`, INTERVAL 5 HOUR) WHERE `created_at` IS NOT NULL;
UPDATE `areas` SET
    `updated_at` = DATE_SUB(`updated_at`, INTERVAL 5 HOUR) WHERE `updated_at` IS NOT NULL;
UPDATE `areas` SET
    `deleted_at` = DATE_SUB(`deleted_at`, INTERVAL 5 HOUR) WHERE `deleted_at` IS NOT NULL;

-- --------------------------------------------------------
-- Tabla: archivos
-- Columnas: created_at, deleted_at
-- --------------------------------------------------------
UPDATE `archivos` SET
    `created_at` = DATE_SUB(`created_at`, INTERVAL 5 HOUR) WHERE `created_at` IS NOT NULL;
UPDATE `archivos` SET
    `deleted_at` = DATE_SUB(`deleted_at`, INTERVAL 5 HOUR) WHERE `deleted_at` IS NOT NULL;

-- --------------------------------------------------------
-- Tabla: usuarios
-- Columnas: last_activity, created_at, updated_at, deleted_at
-- --------------------------------------------------------
UPDATE `usuarios` SET
    `last_activity` = DATE_SUB(`last_activity`, INTERVAL 5 HOUR) WHERE `last_activity` IS NOT NULL;
UPDATE `usuarios` SET
    `created_at` = DATE_SUB(`created_at`, INTERVAL 5 HOUR) WHERE `created_at` IS NOT NULL;
UPDATE `usuarios` SET
    `updated_at` = DATE_SUB(`updated_at`, INTERVAL 5 HOUR) WHERE `updated_at` IS NOT NULL;
UPDATE `usuarios` SET
    `deleted_at` = DATE_SUB(`deleted_at`, INTERVAL 5 HOUR) WHERE `deleted_at` IS NOT NULL;

-- --------------------------------------------------------
-- Tabla: roles
-- Columnas: created_at, updated_at, deleted_at
-- --------------------------------------------------------
UPDATE `roles` SET
    `created_at` = DATE_SUB(`created_at`, INTERVAL 5 HOUR) WHERE `created_at` IS NOT NULL;
UPDATE `roles` SET
    `updated_at` = DATE_SUB(`updated_at`, INTERVAL 5 HOUR) WHERE `updated_at` IS NOT NULL;
UPDATE `roles` SET
    `deleted_at` = DATE_SUB(`deleted_at`, INTERVAL 5 HOUR) WHERE `deleted_at` IS NOT NULL;

-- --------------------------------------------------------
-- Tabla: notificaciones_salientes
-- Columnas: created_at, updated_at
-- --------------------------------------------------------
UPDATE `notificaciones_salientes` SET
    `created_at` = DATE_SUB(`created_at`, INTERVAL 5 HOUR) WHERE `created_at` IS NOT NULL;
UPDATE `notificaciones_salientes` SET
    `updated_at` = DATE_SUB(`updated_at`, INTERVAL 5 HOUR) WHERE `updated_at` IS NOT NULL;

-- --------------------------------------------------------
-- Tabla: devoluciones
-- Columnas: created_at, updated_at
-- --------------------------------------------------------
UPDATE `devoluciones` SET
    `created_at` = DATE_SUB(`created_at`, INTERVAL 5 HOUR) WHERE `created_at` IS NOT NULL;
UPDATE `devoluciones` SET
    `updated_at` = DATE_SUB(`updated_at`, INTERVAL 5 HOUR) WHERE `updated_at` IS NOT NULL;

-- --------------------------------------------------------
-- Tabla: pagos
-- Columnas: fecha_pago, created_at, updated_at
-- --------------------------------------------------------
UPDATE `pagos` SET
    `fecha_pago` = DATE_SUB(`fecha_pago`, INTERVAL 5 HOUR) WHERE `fecha_pago` IS NOT NULL;
UPDATE `pagos` SET
    `created_at` = DATE_SUB(`created_at`, INTERVAL 5 HOUR) WHERE `created_at` IS NOT NULL;
UPDATE `pagos` SET
    `updated_at` = DATE_SUB(`updated_at`, INTERVAL 5 HOUR) WHERE `updated_at` IS NOT NULL;

-- --------------------------------------------------------
-- Tabla: configuracion
-- Columnas: updated_at
-- --------------------------------------------------------
UPDATE `configuracion` SET
    `updated_at` = DATE_SUB(`updated_at`, INTERVAL 5 HOUR) WHERE `updated_at` IS NOT NULL;

-- --------------------------------------------------------
-- Tabla: workflow_transiciones
-- Columnas: created_at
-- --------------------------------------------------------
UPDATE `workflow_transiciones` SET
    `created_at` = DATE_SUB(`created_at`, INTERVAL 5 HOUR) WHERE `created_at` IS NOT NULL;

COMMIT;

-- ============================================================
-- VERIFICACIÓN: Ejecuta esto después para confirmar
-- ============================================================
SELECT 
    id,
    cliente_nombre,
    created_at AS 'Fecha Creación (Corregida COL)',
    updated_at AS 'Última Actualización (Corregida COL)'
FROM pedidos
ORDER BY id;
