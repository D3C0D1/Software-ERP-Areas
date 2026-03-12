-- Respaldo Generado Automáticamente: 2026-02-26 21:33:39

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `archivos`;
CREATE TABLE `archivos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `entidad_tipo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entidad_id` int unsigned NOT NULL,
  `nombre_archivo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ruta_almacenamiento` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_mime` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subido_por` int unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_archivo_usuario` (`subido_por`),
  KEY `idx_entidad` (`entidad_tipo`,`entidad_id`),
  CONSTRAINT `fk_archivo_usuario` FOREIGN KEY (`subido_por`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `areas`;
CREATE TABLE `areas` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `icono` mediumtext COLLATE utf8mb4_unicode_ci,
  `sla_horas` int unsigned DEFAULT '24',
  `estado` tinyint(1) DEFAULT '1',
  `orden` int unsigned DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `areas` (`id`, `nombre`, `descripcion`, `icono`, `sla_horas`, `estado`, `orden`, `created_at`, `updated_at`, `deleted_at`) VALUES ('1', 'Área de Corte', 'Estación inicial', '<svg width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><path d=\"M12 2v20\"></path><path d=\"M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6\"></path></svg>', '24', '1', '1', '2026-02-23 20:48:33', '2026-02-25 17:50:34', NULL);
INSERT IGNORE INTO `areas` (`id`, `nombre`, `descripcion`, `icono`, `sla_horas`, `estado`, `orden`, `created_at`, `updated_at`, `deleted_at`) VALUES ('2', 'Área de Diseño', 'Estación de confección', '<svg width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><path d=\"M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z\"></path><polyline points=\"3.27 6.96 12 12.01 20.73 6.96\"></polyline><line x1=\"12\" y1=\"22.08\" x2=\"12\" y2=\"12\"></line></svg>', '48', '1', '2', '2026-02-23 20:48:33', '2026-02-25 17:50:27', NULL);
INSERT IGNORE INTO `areas` (`id`, `nombre`, `descripcion`, `icono`, `sla_horas`, `estado`, `orden`, `created_at`, `updated_at`, `deleted_at`) VALUES ('3', 'Área de Empaque', 'Empaque', '<svg width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><circle cx=\"12\" cy=\"12\" r=\"10\"></circle><line x1=\"2\" y1=\"12\" x2=\"22\" y2=\"12\"></line><path d=\"M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z\"></path></svg>', '12', '1', '3', '2026-02-23 20:48:33', '2026-02-25 17:50:39', NULL);
INSERT IGNORE INTO `areas` (`id`, `nombre`, `descripcion`, `icono`, `sla_horas`, `estado`, `orden`, `created_at`, `updated_at`, `deleted_at`) VALUES ('4', 'Area de Prueba', 'TestTest', '<svg width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><rect x=\"3\" y=\"3\" width=\"18\" height=\"18\" rx=\"2\" ry=\"2\"></rect><line x1=\"3\" y1=\"9\" x2=\"21\" y2=\"9\"></line><line x1=\"9\" y1=\"21\" x2=\"9\" y2=\"9\"></line></svg>', '24', '1', '4', '2026-02-23 21:59:15', '2026-02-25 17:52:43', NULL);

DROP TABLE IF EXISTS `auditoria_logs`;
CREATE TABLE `auditoria_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` int unsigned DEFAULT NULL,
  `entidad_tipo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entidad_id` int unsigned NOT NULL,
  `accion` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `descripcion_accion` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `data_anterior` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `data_nueva` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_audit_usuario` (`usuario_id`),
  KEY `idx_auditoria_entidad` (`entidad_tipo`,`entidad_id`),
  KEY `idx_auditoria_fecha` (`created_at`),
  CONSTRAINT `fk_audit_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `configuracion`;
CREATE TABLE `configuracion` (
  `clave` varchar(100) NOT NULL,
  `valor` mediumtext,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`clave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT IGNORE INTO `configuracion` (`clave`, `valor`, `updated_at`) VALUES ('auto_backup_diario', '1', '2026-02-26 16:33:39');
INSERT IGNORE INTO `configuracion` (`clave`, `valor`, `updated_at`) VALUES ('empresa_logo', 'data:image/webp;base64,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', '2026-02-25 17:36:57');
INSERT IGNORE INTO `configuracion` (`clave`, `valor`, `updated_at`) VALUES ('empresa_nombre', 'BANNER', '2026-02-25 17:36:57');
INSERT IGNORE INTO `configuracion` (`clave`, `valor`, `updated_at`) VALUES ('fondo_login', '/public/img/fondos/fondo_login_1772066552.jpg', '2026-02-25 19:42:32');
INSERT IGNORE INTO `configuracion` (`clave`, `valor`, `updated_at`) VALUES ('icon_areas', '<svg width=\"22\" height=\"22\" viewBox=\"0 0 24 24\"><circle cx=\"12\" cy=\"12\" r=\"9\" fill=\"rgba(244,114,182,0.2)\"/><circle cx=\"12\" cy=\"12\" r=\"3\" fill=\"#f472b6\"/><path d=\"M12 3v2M12 19v2M3 12h2M19 12h2\" stroke=\"#f472b6\" stroke-width=\"2\" stroke-linecap=\"round\"/></svg>', '2026-02-25 16:37:06');
INSERT IGNORE INTO `configuracion` (`clave`, `valor`, `updated_at`) VALUES ('icon_configuracion', '<svg width=\"22\" height=\"22\" viewBox=\"0 0 24 24\"><circle cx=\"12\" cy=\"12\" r=\"9\" fill=\"rgba(244,114,182,0.15)\"/><path d=\"M12 7v6M12 17h.01\" stroke=\"#f472b6\" stroke-width=\"3\" stroke-linecap=\"round\"/></svg>', '2026-02-25 16:37:06');
INSERT IGNORE INTO `configuracion` (`clave`, `valor`, `updated_at`) VALUES ('icon_dashboard', '<svg width=\"22\" height=\"22\" viewBox=\"0 0 24 24\"><rect x=\"3\" y=\"3\" width=\"7\" height=\"7\" rx=\"1\" fill=\"rgba(244,114,182,0.3)\"/><rect x=\"14\" y=\"3\" width=\"7\" height=\"7\" rx=\"1\" fill=\"rgba(244,114,182,0.3)\"/><rect x=\"14\" y=\"14\" width=\"7\" height=\"7\" rx=\"1\" fill=\"#f472b6\"/><rect x=\"3\" y=\"14\" width=\"7\" height=\"7\" rx=\"1\" fill=\"#f472b6\"/></svg>', '2026-02-25 16:37:06');
INSERT IGNORE INTO `configuracion` (`clave`, `valor`, `updated_at`) VALUES ('icon_recepcion', '<svg width=\"22\" height=\"22\" viewBox=\"0 0 24 24\"><path d=\"M4 6h16v12H4z\" fill=\"rgba(244,114,182,0.25)\"/><rect x=\"2\" y=\"4\" width=\"20\" height=\"4\" rx=\"1\" fill=\"#f472b6\"/></svg>', '2026-02-25 16:37:06');
INSERT IGNORE INTO `configuracion` (`clave`, `valor`, `updated_at`) VALUES ('icon_reportes', '<svg width=\"22\" height=\"22\" viewBox=\"0 0 24 24\"><path d=\"M6 4h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z\" fill=\"rgba(244,114,182,0.2)\"/><path d=\"M8 9h8M8 13h5\" stroke=\"#f472b6\" stroke-width=\"2\" stroke-linecap=\"round\"/></svg>', '2026-02-25 16:37:06');
INSERT IGNORE INTO `configuracion` (`clave`, `valor`, `updated_at`) VALUES ('icon_reportes_pedidos', '<svg width=\"22\" height=\"22\" viewBox=\"0 0 24 24\"><path d=\"M7 2h10a2 2 0 0 1 2 2v16a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2z\" fill=\"rgba(244,114,182,0.25)\"/><rect x=\"9\" y=\"1\" width=\"6\" height=\"3\" rx=\"1\" fill=\"#f472b6\"/></svg>', '2026-02-25 16:37:06');
INSERT IGNORE INTO `configuracion` (`clave`, `valor`, `updated_at`) VALUES ('icon_usuarios', '<svg width=\"22\" height=\"22\" viewBox=\"0 0 24 24\"><circle cx=\"12\" cy=\"8\" r=\"4\" fill=\"rgba(244,114,182,0.3)\"/><path d=\"M4 20c0-3.3 3.6-6 8-6s8 2.7 8 6\" stroke=\"#f472b6\" stroke-width=\"2\" stroke-linecap=\"round\" fill=\"none\"/></svg>', '2026-02-25 16:37:06');
INSERT IGNORE INTO `configuracion` (`clave`, `valor`, `updated_at`) VALUES ('mostrar_credenciales', '1', '2026-02-25 16:24:41');
INSERT IGNORE INTO `configuracion` (`clave`, `valor`, `updated_at`) VALUES ('onurix_api_id', '7389', '2026-02-24 10:37:46');
INSERT IGNORE INTO `configuracion` (`clave`, `valor`, `updated_at`) VALUES ('onurix_api_key', 'baf0076e7d995fc544c21cea4fdf898ce00612f268dc5f38c3565', '2026-02-24 10:37:46');
INSERT IGNORE INTO `configuracion` (`clave`, `valor`, `updated_at`) VALUES ('sms_crear', 'Hola {nombre}, tu pedido {link_seguimiento} ha sido creado. Gracias por confiar en {empresa}', '2026-02-24 11:32:24');
INSERT IGNORE INTO `configuracion` (`clave`, `valor`, `updated_at`) VALUES ('sms_finalizar', 'Hola {nombre}, su pedido ha sido terminado, ya lo puede recoger en {empresa}', '2026-02-24 11:32:24');
INSERT IGNORE INTO `configuracion` (`clave`, `valor`, `updated_at`) VALUES ('ultima_exportacion_db', '', '2026-02-26 16:33:39');

DROP TABLE IF EXISTS `devoluciones`;
CREATE TABLE `devoluciones` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `pedido_id` int unsigned NOT NULL,
  `usuario_id` int unsigned NOT NULL,
  `motivo` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `resolucion` text COLLATE utf8mb4_unicode_ci,
  `estado` enum('abierta','en_revision','resuelta','rechazada') COLLATE utf8mb4_unicode_ci DEFAULT 'abierta',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_dev_pedido` (`pedido_id`),
  KEY `fk_dev_usuario` (`usuario_id`),
  CONSTRAINT `fk_dev_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_dev_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `movimientos_pedido`;
CREATE TABLE `movimientos_pedido` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `pedido_id` int unsigned NOT NULL,
  `usuario_id` int unsigned NOT NULL,
  `area_id` int unsigned DEFAULT NULL,
  `accion` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_mp_usuario` (`usuario_id`),
  KEY `fk_mp_area` (`area_id`),
  KEY `idx_pedido_historial` (`pedido_id`),
  CONSTRAINT `fk_mp_area` FOREIGN KEY (`area_id`) REFERENCES `areas` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_mp_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_mp_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `notificaciones_salientes`;
CREATE TABLE `notificaciones_salientes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `destinatario` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` enum('email','sms','sistema') COLLATE utf8mb4_unicode_ci NOT NULL,
  `asunto` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mensaje` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` enum('pendiente','enviado','fallido') COLLATE utf8mb4_unicode_ci DEFAULT 'pendiente',
  `intentos` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_estado_notif` (`estado`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `notificaciones_salientes` (`id`, `destinatario`, `tipo`, `asunto`, `mensaje`, `estado`, `intentos`, `created_at`, `updated_at`) VALUES ('1', '3184483187', 'sms', NULL, 'Hola eliza, tu pedido banner.com.co/seguimiento/XJB4YT ha sido creado. Gracias por confiar en Banner', 'pendiente', '0', '2026-02-25 12:16:46', '2026-02-25 12:16:46');

DROP TABLE IF EXISTS `pagos`;
CREATE TABLE `pagos` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `pedido_id` int unsigned NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `metodo_pago` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `referencia` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_pago` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `estado` enum('completado','pendiente','fallido','reembolsado') COLLATE utf8mb4_unicode_ci DEFAULT 'completado',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_pago_pedido` (`pedido_id`),
  CONSTRAINT `fk_pago_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `pedidos`;
CREATE TABLE `pedidos` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `token_seguimiento` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cliente_nombre` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cliente_email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cliente_telefono` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `area_actual_id` int unsigned DEFAULT NULL,
  `fase_actual` enum('recepcion','proceso','preparado') COLLATE utf8mb4_unicode_ci DEFAULT 'recepcion',
  `estado_pago` enum('pago_completo','abono','no_pago') COLLATE utf8mb4_unicode_ci DEFAULT 'no_pago',
  `prioridad` enum('prioridad','normal','largo') COLLATE utf8mb4_unicode_ci DEFAULT 'normal',
  `asignado_a_usuario_id` int unsigned DEFAULT NULL,
  `estado` enum('pendiente','en_curso','completado','cancelado') COLLATE utf8mb4_unicode_ci DEFAULT 'pendiente',
  `fecha_entrega_esperada` date DEFAULT NULL,
  `total` decimal(10,2) DEFAULT '0.00',
  `abonado` decimal(10,2) DEFAULT '0.00',
  `last_movement_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token_seguimiento` (`token_seguimiento`),
  KEY `fk_pedido_asignado` (`asignado_a_usuario_id`),
  KEY `idx_fase_actual` (`fase_actual`),
  KEY `idx_estado` (`estado`),
  KEY `idx_kanban` (`area_actual_id`,`fase_actual`),
  CONSTRAINT `fk_pedido_asignado` FOREIGN KEY (`asignado_a_usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `roles` (`id`, `nombre`, `descripcion`, `estado`, `created_at`, `updated_at`, `deleted_at`) VALUES ('1', 'Admin', 'Administrador Global del ERP', '1', '2026-02-23 20:48:33', '2026-02-23 20:48:33', NULL);
INSERT IGNORE INTO `roles` (`id`, `nombre`, `descripcion`, `estado`, `created_at`, `updated_at`, `deleted_at`) VALUES ('2', 'Operador', 'Operador de Planta o Almacén', '1', '2026-02-23 20:48:33', '2026-02-23 20:48:33', NULL);

DROP TABLE IF EXISTS `usuario_areas`;
CREATE TABLE `usuario_areas` (
  `usuario_id` int unsigned NOT NULL,
  `area_id` int unsigned NOT NULL,
  PRIMARY KEY (`usuario_id`,`area_id`),
  KEY `fk_ua_area` (`area_id`),
  CONSTRAINT `fk_ua_area` FOREIGN KEY (`area_id`) REFERENCES `areas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ua_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `usuario_areas` (`usuario_id`, `area_id`) VALUES ('6', '1');
INSERT IGNORE INTO `usuario_areas` (`usuario_id`, `area_id`) VALUES ('5', '2');
INSERT IGNORE INTO `usuario_areas` (`usuario_id`, `area_id`) VALUES ('6', '2');
INSERT IGNORE INTO `usuario_areas` (`usuario_id`, `area_id`) VALUES ('5', '3');
INSERT IGNORE INTO `usuario_areas` (`usuario_id`, `area_id`) VALUES ('6', '3');
INSERT IGNORE INTO `usuario_areas` (`usuario_id`, `area_id`) VALUES ('6', '4');

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE `usuarios` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `rol_id` int unsigned NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `foto_perfil` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` tinyint(1) DEFAULT '1',
  `last_activity` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `ver_precios` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `fk_usuario_rol` (`rol_id`),
  CONSTRAINT `fk_usuario_rol` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `usuarios` (`id`, `rol_id`, `nombre`, `email`, `foto_perfil`, `password_hash`, `estado`, `last_activity`, `created_at`, `updated_at`, `deleted_at`, `ver_precios`) VALUES ('1', '1', 'Admin Maestro', 'admin@erp.com', NULL, '$2y$10$7lRV9q/.NvmNA.cjbaPM2e.ffpHabwOpGtOLL0MnQg1Yqw7RYocgi', '1', '2026-02-26 16:33:39', '2026-02-23 20:48:33', '2026-02-26 16:33:39', NULL, '0');
INSERT IGNORE INTO `usuarios` (`id`, `rol_id`, `nombre`, `email`, `foto_perfil`, `password_hash`, `estado`, `last_activity`, `created_at`, `updated_at`, `deleted_at`, `ver_precios`) VALUES ('2', '2', 'Juan Pérez', 'juan@erp.com', NULL, '$2y$10$hyCDeWapa/JM2DSF.J2KiuN4hacjpIWgw.p5u8VRmYBSLTWAlJFLK', '1', '2026-02-26 13:37:05', '2026-02-23 20:48:33', '2026-02-26 13:37:05', NULL, '0');
INSERT IGNORE INTO `usuarios` (`id`, `rol_id`, `nombre`, `email`, `foto_perfil`, `password_hash`, `estado`, `last_activity`, `created_at`, `updated_at`, `deleted_at`, `ver_precios`) VALUES ('5', '2', 'daniel', 'daniel@gmail.com', NULL, '$2y$10$UWnFDbSANZ9dGCULunVge.he5mOjkZDIA9lre0zNIM7YQB903Qdae', '1', NULL, '2026-02-26 13:28:05', '2026-02-26 13:28:05', NULL, '0');
INSERT IGNORE INTO `usuarios` (`id`, `rol_id`, `nombre`, `email`, `foto_perfil`, `password_hash`, `estado`, `last_activity`, `created_at`, `updated_at`, `deleted_at`, `ver_precios`) VALUES ('6', '2', 'ana', 'kalua', NULL, '$2y$10$YsCH13VpL27G1.ltCKO/be9NQINwK79wGkP29irT9ziGwAV07E5Qm', '1', '2026-02-26 13:49:31', '2026-02-26 13:47:50', '2026-02-26 13:49:31', NULL, '1');

DROP TABLE IF EXISTS `workflow_transiciones`;
CREATE TABLE `workflow_transiciones` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `area_origen_id` int unsigned NOT NULL,
  `area_destino_id` int unsigned NOT NULL,
  `es_retroceso` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ux_origen_destino` (`area_origen_id`,`area_destino_id`),
  KEY `fk_wt_destino` (`area_destino_id`),
  CONSTRAINT `fk_wt_destino` FOREIGN KEY (`area_destino_id`) REFERENCES `areas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_wt_origen` FOREIGN KEY (`area_origen_id`) REFERENCES `areas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `workflow_transiciones` (`id`, `area_origen_id`, `area_destino_id`, `es_retroceso`, `created_at`) VALUES ('1', '1', '2', '0', '2026-02-23 20:48:33');
INSERT IGNORE INTO `workflow_transiciones` (`id`, `area_origen_id`, `area_destino_id`, `es_retroceso`, `created_at`) VALUES ('2', '2', '3', '0', '2026-02-23 20:48:33');
INSERT IGNORE INTO `workflow_transiciones` (`id`, `area_origen_id`, `area_destino_id`, `es_retroceso`, `created_at`) VALUES ('3', '2', '1', '1', '2026-02-23 20:48:33');
INSERT IGNORE INTO `workflow_transiciones` (`id`, `area_origen_id`, `area_destino_id`, `es_retroceso`, `created_at`) VALUES ('4', '3', '2', '1', '2026-02-23 20:48:33');

SET FOREIGN_KEY_CHECKS=1;
