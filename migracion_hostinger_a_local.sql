-- ============================================================
--  SCRIPT DE MIGRACIÓN: Hostinger → Local (erp_mvc)
--  Generado: 2026-03-10
--  Objetivo: Igualar estructura LOCAL con HOSTINGER y traer
--            filas nuevas del 10/03 que solo existen en Hostinger
-- ============================================================
-- INSTRUCCIONES:
--   1. Abre phpMyAdmin en http://localhost/phpmyadmin
--   2. Selecciona la base de datos local (erp_mvc)
--   3. Ve a la pestaña "SQL" y pega todo este script
--   4. Haz clic en "Continuar / Ejecutar"
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- PARTE 1: ESTRUCTURA — Columnas que faltan en LOCAL
-- ============================================================

-- La tabla `usuarios` en LOCAL ya tiene `crear_enviar_pedidos`
-- y `devolver_pedidos`. Hostinger NO las tiene.
-- → El script de abajo las añade a Hostinger si corres esto allá,
--   pero como lo corres en LOCAL, verificamos que existan.

-- (Seguro: usa IF NOT EXISTS vía ALTER IGNORE o columna condicional)
-- Añadir columnas faltantes en LOCAL (por si acaso no existen):

ALTER TABLE `usuarios`
  MODIFY COLUMN `ver_precios` tinyint(1) DEFAULT 0,
  MODIFY COLUMN `editar_pedidos` tinyint(1) NOT NULL DEFAULT 0;

-- Añadir crear_enviar_pedidos si NO existe (local ya la tiene, esto es for hostinger)
-- Si corres en LOCAL no hará daño (columna ya existe → error ignorado con IF NOT EXISTS workaround):
SET @col_exists = (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'usuarios'
    AND COLUMN_NAME = 'crear_enviar_pedidos'
);
-- Nota: MySQL no permite IF en scripts directos, usa el bloque de abajo como referencia.
-- Si la columna NO existe en el destino, ejecuta:
--   ALTER TABLE `usuarios` ADD COLUMN `crear_enviar_pedidos` tinyint(1) DEFAULT 0 AFTER `editar_pedidos`;
--   ALTER TABLE `usuarios` ADD COLUMN `devolver_pedidos` tinyint(1) DEFAULT 0 AFTER `crear_enviar_pedidos`;


-- ============================================================
-- PARTE 2: DATOS NUEVOS — Pedidos del 10/03 que tiene Hostinger
--          y LOCALMENTE no existen (IDs 133 en adelante del 10/03)
-- ============================================================
-- Se usa INSERT IGNORE para no duplicar si ya existen.

INSERT IGNORE INTO `pedidos`
  (`id`, `token_seguimiento`, `cliente_nombre`, `cliente_email`, `cliente_telefono`,
   `descripcion`, `area_actual_id`, `fase_actual`, `estado_pago`, `prioridad`,
   `asignado_a_usuario_id`, `estado`, `fecha_entrega_esperada`, `total`, `abonado`,
   `last_movement_at`, `created_at`, `updated_at`, `deleted_at`, `fue_editado`, `entregado`)
VALUES
-- Pedido 132: En Hostinger está más actualizado (fue_editado=1, area=8)
-- Usamos REPLACE para actualizar si ya existe con datos viejos:
(132, 'VTWBGR', 'Juan Jiemenez', '', '324 9028266', '50 Stickers vin troquelado',
 8, 'recepcion', 'pago_completo', 'prioridad', NULL, 'pendiente',
 NULL, 20000.00, 0.00,
 '2026-03-10 13:36:13', '2026-03-09 21:43:59', '2026-03-10 13:41:31', NULL, 1, 0),

-- Pedidos NUEVOS del 10/03 (solo en Hostinger):
(133, 'Z4OAMH', 'HECTOR RIVERA', '', '300 7518209',
 'VINILO LAMINADO BRILL 126X76\r\nVINILO SIN LAMINAR 114X120',
 8, 'recepcion', 'no_pago', 'prioridad', NULL, 'pendiente', '2026-03-10',
 51000.00, 0.00,
 '2026-03-10 13:39:26', '2026-03-10 13:39:17', '2026-03-10 14:57:10', NULL, 1, 0),

(134, 'HADPMQ', 'JOHN', '', '311 4206740',
 'TRASLUCIDO SIN LAMINAR 500X50',
 8, 'recepcion', 'no_pago', 'prioridad', NULL, 'pendiente', '2026-03-10',
 70000.00, 0.00,
 '2026-03-10 13:40:29', '2026-03-10 13:40:09', '2026-03-10 13:40:29', NULL, 1, 0),

(135, 'AD1X4K', 'JUNIOR PUBLICIDAD', '', '318 2356656',
 'VINILO SIN LAMINAR 100X177',
 8, 'recepcion', 'no_pago', 'prioridad', NULL, 'pendiente', '2026-03-10',
 30000.00, 0.00,
 '2026-03-10 14:18:21', '2026-03-10 14:18:11', '2026-03-10 14:18:21', NULL, 0, 0),

(136, 'V7H8WI', 'MELLO CALCOMANIA', '', '',
 'VINILOS LAMINADOS, TORNASOL LAMINADO Y VINILO LAMINADO EN ESCARCHADO',
 8, 'recepcion', 'no_pago', 'prioridad', NULL, 'pendiente', '2026-03-10',
 1000.00, 0.00,
 '2026-03-10 14:19:23', '2026-03-10 14:19:17', '2026-03-10 14:19:23', NULL, 0, 0),

(137, 'CI74VH', 'PAOLA ROAS', '', '311 6500545',
 'PENDON CON TUBOS 70X100',
 8, 'recepcion', 'no_pago', 'prioridad', NULL, 'pendiente', '2026-03-10',
 30000.00, 0.00,
 '2026-03-10 14:29:43', '2026-03-10 14:29:35', '2026-03-10 14:29:43', NULL, 0, 0),

(138, 'UYLKWJ', 'ERNESTO LEAR', '', '300 8053552',
 'VINILO LAMINADO BRILLANTE 96X187',
 8, 'recepcion', 'no_pago', 'prioridad', NULL, 'pendiente', '2026-03-10',
 54000.00, 0.00,
 '2026-03-10 14:37:24', '2026-03-10 14:37:18', '2026-03-10 14:37:24', NULL, 0, 0),

(139, '5JFHC9', 'DC PAPELERIA', '', '301 3455111',
 'BANER 70x100',
 8, 'recepcion', 'no_pago', 'prioridad', NULL, 'pendiente', '2026-03-10',
 10000.00, 0.00,
 '2026-03-10 14:56:23', '2026-03-10 14:56:13', '2026-03-10 14:56:23', NULL, 0, 0),

(140, 'JBTI2W', 'LILIANA CHIMÁ', '', '321 7422450',
 'VINILO SIN LAMINAR 100X6',
 8, 'recepcion', 'no_pago', 'prioridad', NULL, 'pendiente', '2026-03-10',
 7000.00, 0.00,
 '2026-03-10 15:08:56', '2026-03-10 15:08:47', '2026-03-10 15:08:56', NULL, 0, 0),

(141, 'HI0WBE', 'Martin Sampayo', '', '3135770553',
 'DTF 44X22 CM',
 9, 'preparado', 'no_pago', 'normal', 11, 'completado', '2026-03-12',
 10000.00, 0.00,
 '2026-03-10 16:24:50', '2026-03-10 16:24:28', '2026-03-10 16:24:50', NULL, 0, 0),

(142, 'BI2GL4', 'Grama', '', '3012223280',
 'DTF 54X27 CM',
 9, 'preparado', 'no_pago', 'normal', 11, 'completado', '2026-03-12',
 10000.00, 0.00,
 '2026-03-10 16:41:43', '2026-03-10 16:26:21', '2026-03-10 16:41:43', NULL, 0, 0),

(143, '0Q3KRI', 'Jesús Martínez', '', '3012517570',
 'DTF 58X24 CM',
 9, 'preparado', 'no_pago', 'normal', 11, 'completado', '2026-03-12',
 10000.00, 0.00,
 '2026-03-10 16:41:10', '2026-03-10 16:27:24', '2026-03-10 16:41:10', NULL, 0, 0),

(144, '7F6G3M', 'Euclides', '', '3332644603',
 'DTF 57X65 CM',
 9, 'preparado', 'no_pago', 'normal', 11, 'completado', '2026-03-12',
 16000.00, 0.00,
 '2026-03-10 17:41:56', '2026-03-10 16:28:34', '2026-03-10 17:41:56', NULL, 0, 0),

(145, 'TKF4LG', 'Sandra', '', '3127447368',
 'DTF 57X100 CM',
 9, 'preparado', 'no_pago', 'normal', 11, 'completado', '2026-03-12',
 25000.00, 0.00,
 '2026-03-10 17:41:28', '2026-03-10 16:30:05', '2026-03-10 17:41:28', NULL, 0, 0),

(146, 'JTR5BN', 'ESTEBAN DOMINGUEZ', '', '',
 'BANER LAM BRILL CON ESPACIOS 70X100',
 8, 'recepcion', 'no_pago', 'prioridad', NULL, 'pendiente', '2026-03-10',
 20000.00, 0.00,
 '2026-03-10 16:31:00', '2026-03-10 16:30:53', '2026-03-10 16:31:00', NULL, 0, 0),

(147, 'VY71HS', 'JUAN DIEGO', '', '311 8783260',
 'BANER CON TUBOS 70X100',
 8, 'recepcion', 'no_pago', 'prioridad', NULL, 'pendiente', '2026-03-10',
 15000.00, 0.00,
 '2026-03-10 16:34:29', '2026-03-10 16:34:22', '2026-03-10 16:34:29', NULL, 0, 0),

(148, 'Z7AVE8', 'Fernando Blanco', '', '3194584472',
 'DTF 58X553 CM',
 9, 'preparado', 'no_pago', 'normal', 11, 'completado', NULL,
 111000.00, 0.00,
 '2026-03-10 17:25:26', '2026-03-10 16:37:18', '2026-03-10 17:25:26', NULL, 0, 0),

(149, 'F4U1AJ', 'Guillermo Crear', '', '3017171090',
 'DTF 58X26 CM',
 9, 'preparado', 'no_pago', 'normal', 11, 'completado', '2026-03-12',
 10000.00, 0.00,
 '2026-03-10 16:48:03', '2026-03-10 16:47:41', '2026-03-10 16:48:03', NULL, 0, 0);

-- ============================================================
-- PARTE 3: Actualizar pedido 122 (en Hostinger tiene updated 10/03)
-- ============================================================
UPDATE `pedidos`
SET
  `estado`     = 'completado',
  `fase_actual` = 'preparado',
  `updated_at` = '2026-03-10 14:07:04'
WHERE `id` = 122
  AND `updated_at` < '2026-03-10 14:07:04';

-- Actualizar pedido 125 (en Hostinger actualizado el 10/03)
UPDATE `pedidos`
SET
  `estado`     = 'completado',
  `fase_actual` = 'preparado',
  `updated_at` = '2026-03-10 14:06:41'
WHERE `id` = 125
  AND `updated_at` < '2026-03-10 14:06:41';

-- ============================================================
-- PARTE 4: AUTO_INCREMENT — Alinear al valor correcto
-- ============================================================
-- Después de insertar hasta el ID 149, ajusta el auto_increment:
ALTER TABLE `pedidos` AUTO_INCREMENT = 150;

-- ============================================================
-- PARTE 5: SCRIPT PARA CORRER EN HOSTINGER (phpMyAdmin Hostinger)
--          para añadir columnas que le faltan a Hostinger
-- ============================================================
-- ⚠️ SOLO corre esto en phpMyAdmin de HOSTINGER, NO en local:
--
-- ALTER TABLE `usuarios`
--   ADD COLUMN `crear_enviar_pedidos` tinyint(1) DEFAULT 0
--   AFTER `editar_pedidos`;
--
-- ALTER TABLE `usuarios`
--   ADD COLUMN `devolver_pedidos` tinyint(1) DEFAULT 0
--   AFTER `crear_enviar_pedidos`;
--
-- UPDATE `usuarios` SET `crear_enviar_pedidos` = 0, `devolver_pedidos` = 0;
--

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- FIN DEL SCRIPT
-- ============================================================
