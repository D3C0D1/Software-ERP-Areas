-- Respaldo Generado Automáticamente: 2026-03-05 01:11:04

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `archivos`;
CREATE TABLE `archivos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `entidad_tipo` varchar(50) NOT NULL,
  `entidad_id` int(10) unsigned NOT NULL,
  `nombre_archivo` varchar(255) NOT NULL,
  `ruta_almacenamiento` varchar(500) NOT NULL,
  `tipo_mime` varchar(50) DEFAULT NULL,
  `subido_por` int(10) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_archivo_usuario` (`subido_por`),
  KEY `idx_entidad` (`entidad_tipo`,`entidad_id`),
  CONSTRAINT `fk_archivo_usuario` FOREIGN KEY (`subido_por`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `archivos` (`id`, `entidad_tipo`, `entidad_id`, `nombre_archivo`, `ruta_almacenamiento`, `tipo_mime`, `subido_por`, `created_at`, `deleted_at`) VALUES ('1', 'pedido', '7', 'LOGOMISIONJUVENIL.rar', 'recep_7_1772659298_0.rar', 'application/x-compressed', '7', '2026-03-04 16:21:38', NULL);

DROP TABLE IF EXISTS `areas`;
CREATE TABLE `areas` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `icono` mediumtext DEFAULT NULL,
  `sla_horas` int(10) unsigned DEFAULT 24,
  `estado` tinyint(1) DEFAULT 1,
  `orden` int(10) unsigned DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `areas` (`id`, `nombre`, `descripcion`, `icono`, `sla_horas`, `estado`, `orden`, `created_at`, `updated_at`, `deleted_at`) VALUES ('1', 'Área de Corte', 'Estación inicial', '<svg width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><circle cx=\"6\" cy=\"6\" r=\"3\"></circle><circle cx=\"6\" cy=\"18\" r=\"3\"></circle><line x1=\"20\" y1=\"4\" x2=\"8.12\" y2=\"15.88\"></line><line x1=\"14.47\" y1=\"14.48\" x2=\"20\" y2=\"20\"></line><line x1=\"8.12\" y1=\"8.12\" x2=\"12\" y2=\"12\"></line></svg>', '24', '1', '1', '2026-02-23 20:48:33', '2026-03-04 20:28:05', NULL);
INSERT IGNORE INTO `areas` (`id`, `nombre`, `descripcion`, `icono`, `sla_horas`, `estado`, `orden`, `created_at`, `updated_at`, `deleted_at`) VALUES ('2', 'Área de Diseño', 'Estación de confección', '<svg width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><path d=\"M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z\"></path><polyline points=\"3.27 6.96 12 12.01 20.73 6.96\"></polyline><line x1=\"12\" y1=\"22.08\" x2=\"12\" y2=\"12\"></line></svg>', '48', '1', '2', '2026-02-23 20:48:33', '2026-02-25 17:50:27', NULL);
INSERT IGNORE INTO `areas` (`id`, `nombre`, `descripcion`, `icono`, `sla_horas`, `estado`, `orden`, `created_at`, `updated_at`, `deleted_at`) VALUES ('3', 'Empaque y verificación', 'Empaque', '<svg width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><polyline points=\"22 12 18 12 15 21 9 3 6 12 2 12\"></polyline></svg>', '12', '1', '3', '2026-02-23 20:48:33', '2026-03-04 11:30:43', NULL);
INSERT IGNORE INTO `areas` (`id`, `nombre`, `descripcion`, `icono`, `sla_horas`, `estado`, `orden`, `created_at`, `updated_at`, `deleted_at`) VALUES ('4', 'Corte Laser', 'Acrílico y MDF', '<svg width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><rect x=\"3\" y=\"3\" width=\"18\" height=\"18\" rx=\"2\" ry=\"2\"></rect><line x1=\"3\" y1=\"9\" x2=\"21\" y2=\"9\"></line><line x1=\"9\" y1=\"21\" x2=\"9\" y2=\"9\"></line></svg>', '24', '1', '4', '2026-02-23 21:59:15', '2026-03-04 11:30:20', NULL);
INSERT IGNORE INTO `areas` (`id`, `nombre`, `descripcion`, `icono`, `sla_horas`, `estado`, `orden`, `created_at`, `updated_at`, `deleted_at`) VALUES ('8', 'Impresion General', 'Eco, UV, Sublimación', '<svg width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><polyline points=\"6 9 6 2 18 2 18 9\"></polyline><path d=\"M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2\"></path><rect x=\"6\" y=\"14\" width=\"12\" height=\"8\"></rect></svg>', '24', '1', '5', '2026-03-04 04:11:40', '2026-03-04 11:28:28', NULL);
INSERT IGNORE INTO `areas` (`id`, `nombre`, `descripcion`, `icono`, `sla_horas`, `estado`, `orden`, `created_at`, `updated_at`, `deleted_at`) VALUES ('9', 'Impresion DTF', '', '<svg width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><polyline points=\"21 8 21 21 3 21 3 8\"></polyline><rect x=\"1\" y=\"3\" width=\"22\" height=\"5\"></rect><line x1=\"10\" y1=\"12\" x2=\"14\" y2=\"12\"></line></svg>', '24', '1', '6', '2026-03-04 07:30:55', '2026-03-04 09:17:53', NULL);
INSERT IGNORE INTO `areas` (`id`, `nombre`, `descripcion`, `icono`, `sla_horas`, `estado`, `orden`, `created_at`, `updated_at`, `deleted_at`) VALUES ('10', 'Diseño y Armado DTF', 'Edición y verificación DTF', '<svg width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><rect x=\"2\" y=\"3\" width=\"20\" height=\"14\" rx=\"2\" ry=\"2\"></rect><line x1=\"8\" y1=\"21\" x2=\"16\" y2=\"21\"></line><line x1=\"12\" y1=\"17\" x2=\"12\" y2=\"21\"></line></svg>', '24', '1', '7', '2026-03-04 11:27:02', '2026-03-04 20:27:59', NULL);
INSERT IGNORE INTO `areas` (`id`, `nombre`, `descripcion`, `icono`, `sla_horas`, `estado`, `orden`, `created_at`, `updated_at`, `deleted_at`) VALUES ('11', 'Tirajes y Litografía', '', '<svg width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><path d=\"M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z\"></path><path d=\"M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z\"></path></svg>', '24', '1', '8', '2026-03-04 11:35:00', '2026-03-04 20:28:13', NULL);
INSERT IGNORE INTO `areas` (`id`, `nombre`, `descripcion`, `icono`, `sla_horas`, `estado`, `orden`, `created_at`, `updated_at`, `deleted_at`) VALUES ('12', 'Laminación', '', '<svg width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><polyline points=\"21 8 21 21 3 21 3 8\"></polyline><rect x=\"1\" y=\"3\" width=\"22\" height=\"5\"></rect><line x1=\"10\" y1=\"12\" x2=\"14\" y2=\"12\"></line></svg>', '24', '1', '9', '2026-03-04 11:45:07', '2026-03-04 20:28:18', NULL);

DROP TABLE IF EXISTS `auditoria_logs`;
CREATE TABLE `auditoria_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` int(10) unsigned DEFAULT NULL,
  `entidad_tipo` varchar(50) NOT NULL,
  `entidad_id` int(10) unsigned NOT NULL,
  `accion` varchar(50) DEFAULT NULL,
  `descripcion_accion` varchar(255) NOT NULL,
  `data_anterior` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `data_nueva` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_audit_usuario` (`usuario_id`),
  KEY `idx_auditoria_entidad` (`entidad_tipo`,`entidad_id`),
  KEY `idx_auditoria_fecha` (`created_at`),
  CONSTRAINT `fk_audit_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=137 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('9', '1', 'configuracion', '0', 'actualizar', 'Modificó configuraciones: sonido_tema', NULL, NULL, '181.128.146.90', '2026-03-04 12:00:16');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('10', '1', 'configuracion', '0', 'actualizar', 'Modificó configuraciones: sonido_tema', NULL, NULL, '181.128.146.90', '2026-03-04 12:00:17');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('11', '1', 'configuracion', '0', 'actualizar', 'Modificó configuraciones: sonido_tema', NULL, NULL, '181.128.146.90', '2026-03-04 12:00:19');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('12', '1', 'configuracion', '0', 'actualizar', 'Modificó configuraciones: sonido_tema', NULL, NULL, '181.128.146.90', '2026-03-04 12:00:19');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('13', '1', 'configuracion', '0', 'actualizar', 'Modificó configuraciones: sonido_tema', NULL, NULL, '181.128.146.90', '2026-03-04 12:00:20');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('14', '1', 'configuracion', '0', 'actualizar', 'Modificó configuraciones: sonido_tema', NULL, NULL, '181.128.146.90', '2026-03-04 12:00:21');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('15', '1', 'configuracion', '0', 'actualizar', 'Modificó configuraciones: sonido_tema', NULL, NULL, '181.128.146.90', '2026-03-04 12:00:24');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('16', '1', 'configuracion', '0', 'actualizar', 'Modificó configuraciones: sonido_tema', NULL, NULL, '181.128.146.90', '2026-03-04 12:00:29');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('20', '1', 'configuracion', '0', 'actualizar', 'Modificó configuraciones: sms_crear, sms_finalizar', NULL, NULL, '181.128.146.90', '2026-03-04 12:02:56');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('21', '1', 'configuracion', '0', 'actualizar', 'Modificó configuraciones: sms_crear, sms_finalizar', NULL, NULL, '181.128.146.90', '2026-03-04 12:03:32');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('22', '7', 'configuracion', '0', 'actualizar', 'Modificó configuraciones: sonido_tema', NULL, NULL, '181.128.146.90', '2026-03-04 12:08:12');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('23', '7', 'configuracion', '0', 'actualizar', 'Modificó configuraciones: sonido_tema', NULL, NULL, '181.128.146.90', '2026-03-04 12:08:13');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('24', '7', 'configuracion', '0', 'actualizar', 'Modificó configuraciones: sonido_tema', NULL, NULL, '181.128.146.90', '2026-03-04 12:08:14');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('25', '7', 'configuracion', '0', 'actualizar', 'Modificó configuraciones: sonido_tema', NULL, NULL, '181.128.146.90', '2026-03-04 12:08:14');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('26', '7', 'configuracion', '0', 'actualizar', 'Modificó configuraciones: sonido_tema', NULL, NULL, '181.128.146.90', '2026-03-04 12:08:38');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('27', '7', 'configuracion', '0', 'actualizar', 'Modificó configuraciones: sonido_tema', NULL, NULL, '181.128.146.90', '2026-03-04 12:08:39');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('28', '7', 'configuracion', '0', 'actualizar', 'Modificó configuraciones: sonido_tema', NULL, NULL, '181.128.146.90', '2026-03-04 12:08:40');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('29', '7', 'configuracion', '0', 'actualizar', 'Modificó configuraciones: sonido_tema', NULL, NULL, '181.128.146.90', '2026-03-04 12:08:40');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('30', '7', 'configuracion', '0', 'actualizar', 'Modificó configuraciones: sonido_tema', NULL, NULL, '181.128.146.90', '2026-03-04 12:08:41');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('31', '7', 'pedido', '1', 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '181.128.146.90', '2026-03-04 12:12:06');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('32', NULL, 'pedido', '1', 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '191.108.103.24', '2026-03-04 12:12:45');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('33', '1', 'configuracion', '0', 'actualizar', 'Modificó configuraciones: sonido_tema', NULL, NULL, '181.128.146.90', '2026-03-04 12:20:32');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('34', '1', 'configuracion', '0', 'actualizar', 'Modificó configuraciones: sonido_tema', NULL, NULL, '181.128.146.90', '2026-03-04 12:20:33');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('35', '1', 'configuracion', '0', 'actualizar', 'Modificó configuraciones: sonido_tema', NULL, NULL, '181.128.146.90', '2026-03-04 12:20:34');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('36', '1', 'configuracion', '0', 'actualizar', 'Modificó configuraciones: sonido_tema', NULL, NULL, '181.128.146.90', '2026-03-04 12:20:35');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('37', '1', 'configuracion', '0', 'actualizar', 'Modificó configuraciones: sonido_tema', NULL, NULL, '181.128.146.90', '2026-03-04 12:20:35');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('38', '1', 'configuracion', '0', 'actualizar', 'Modificó configuraciones: sonido_tema', NULL, NULL, '181.128.146.90', '2026-03-04 12:20:36');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('39', '1', 'configuracion', '0', 'actualizar', 'Modificó configuraciones: sonido_tema', NULL, NULL, '181.128.146.90', '2026-03-04 12:20:36');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('40', '1', 'configuracion', '0', 'actualizar', 'Modificó configuraciones: sonido_tema', NULL, NULL, '181.128.146.90', '2026-03-04 12:20:37');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('41', '1', 'configuracion', '0', 'actualizar', 'Modificó configuraciones: sonido_tema', NULL, NULL, '181.128.146.90', '2026-03-04 12:20:37');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('42', '1', 'pedido', '1', 'actualizar', 'Traslado libre a proceso - Movimiento manual en el Kanban.', NULL, NULL, '181.128.146.90', '2026-03-04 12:21:15');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('43', '1', 'pedido', '1', 'actualizar', 'Traslado libre a preparado - Movimiento manual en el Kanban.', NULL, NULL, '181.128.146.90', '2026-03-04 12:21:17');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('44', '1', 'configuracion', '0', 'actualizar', 'Modificó configuraciones: sms_crear, sms_finalizar', NULL, NULL, '181.128.146.90', '2026-03-04 12:24:20');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('45', '1', 'usuario', '8', 'crear', 'Creó nuevo usuario', NULL, NULL, '181.128.146.90', '2026-03-04 12:33:51');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('46', '7', 'usuario', '9', 'crear', 'Creó nuevo usuario', NULL, NULL, '181.128.146.90', '2026-03-04 12:36:40');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('47', '7', 'pedido', '2', 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '181.128.146.90', '2026-03-04 12:49:22');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('48', '7', 'usuario', '10', 'crear', 'Creó nuevo usuario', NULL, NULL, '181.128.146.90', '2026-03-04 12:50:33');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('49', '7', 'usuario', '10', 'actualizar', 'Admin editó usuario', NULL, NULL, '181.128.146.90', '2026-03-04 12:57:35');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('50', '10', 'pedido', '2', 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '181.128.146.90', '2026-03-04 12:59:45');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('51', '10', 'pedido', '2', 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '181.128.146.90', '2026-03-04 13:00:04');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('52', '7', 'pedido', '2', 'actualizar', 'Actualizó datos del pedido en Recepción', NULL, NULL, '181.128.146.90', '2026-03-04 13:02:22');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('53', '10', 'pedido', '2', 'actualizar', 'Traslado libre a proceso - Movimiento manual en el Kanban.', NULL, NULL, '181.128.146.90', '2026-03-04 13:16:40');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('54', '10', 'pedido', '2', 'actualizar', 'Traslado libre a preparado - Movimiento manual en el Kanban.', NULL, NULL, '181.128.146.90', '2026-03-04 13:16:42');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('55', '10', 'pedido', '2', 'actualizar', 'Traslado libre a proceso - Movimiento manual en el Kanban.', NULL, NULL, '181.128.146.90', '2026-03-04 13:45:09');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('56', '10', 'pedido', '2', 'actualizar', 'Traslado libre a preparado - Movimiento manual en el Kanban.', NULL, NULL, '181.128.146.90', '2026-03-04 13:45:12');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('57', '10', 'pedido', '2', 'actualizar', 'Transferido - Pedido enviado al área ID: 8', NULL, NULL, '181.128.146.90', '2026-03-04 13:45:54');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('58', '7', 'usuario', '11', 'crear', 'Creó nuevo usuario', NULL, NULL, '191.108.103.24', '2026-03-04 14:10:38');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('59', '11', 'pedido', '3', 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '191.108.103.24', '2026-03-04 14:16:07');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('60', '11', 'pedido', '3', 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-04 14:16:43');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('61', '11', 'pedido', '3', 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.103.24', '2026-03-04 14:17:04');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('62', '11', 'pedido', '3', 'actualizar', 'Transferido - Pedido enviado al área ID: 9', NULL, NULL, '191.108.103.24', '2026-03-04 14:17:14');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('63', '7', 'pedido', '4', 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '181.128.146.90', '2026-03-04 14:34:10');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('64', NULL, 'pedido', '4', 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '2803:1800:5016:39b0:1899:b1e3:2c18:c766', '2026-03-04 14:34:34');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('65', '7', 'pedido', '4', 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '181.128.146.90', '2026-03-04 14:35:30');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('66', '7', 'pedido', '4', 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '181.128.146.90', '2026-03-04 14:35:31');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('67', NULL, 'pedido', '3', 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '2800:e6:4010:46e:90fd:70da:621:3953', '2026-03-04 14:52:54');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('68', '11', 'pedido', '5', 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '191.108.103.24', '2026-03-04 15:48:25');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('69', '11', 'pedido', '6', 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '191.108.103.24', '2026-03-04 15:50:54');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('70', NULL, 'pedido', '5', 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '2800:484:4272:4600:401d:e4d9:6723:4800', '2026-03-04 15:53:38');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('71', NULL, 'pedido', '6', 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '191.156.250.255', '2026-03-04 16:08:24');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('72', '7', 'pedido', '7', 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '181.128.146.90', '2026-03-04 16:21:38');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('73', NULL, 'pedido', '7', 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '2a03:2880:27ff::', '2026-03-04 16:21:49');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('74', NULL, 'pedido', '7', 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '2803:1800:5018:be85:1899:b5ef:28ce:ae2d', '2026-03-04 16:25:16');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('75', '10', 'pedido', '8', 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '181.128.146.90', '2026-03-04 16:42:07');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('76', NULL, 'pedido', '8', 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '74.125.210.132', '2026-03-04 16:42:11');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('77', '9', 'pedido', '8', 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-04 16:42:59');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('78', '9', 'pedido', '8', 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.103.24', '2026-03-04 16:44:16');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('79', '7', 'usuario', '9', 'actualizar', 'Admin editó usuario', NULL, NULL, '181.128.146.90', '2026-03-04 16:46:39');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('80', '7', 'usuario', '10', 'actualizar', 'Admin editó usuario', NULL, NULL, '181.128.146.90', '2026-03-04 16:46:53');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('81', '8', 'pedido', '9', 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '181.128.146.90', '2026-03-04 16:46:58');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('82', '7', 'usuario', '10', 'actualizar', 'Admin editó usuario', NULL, NULL, '181.128.146.90', '2026-03-04 16:47:09');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('83', '7', 'usuario', '8', 'actualizar', 'Admin editó usuario', NULL, NULL, '181.128.146.90', '2026-03-04 16:47:37');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('84', '9', 'pedido', '9', 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-04 16:48:09');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('85', '7', 'usuario', '11', 'actualizar', 'Admin editó usuario', NULL, NULL, '181.128.146.90', '2026-03-04 16:52:36');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('86', '7', 'pedido', '1', 'eliminar', 'Eliminó/canceló pedido en Recepción', NULL, NULL, '181.128.146.90', '2026-03-04 16:55:29');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('87', '7', 'pedido', '4', 'eliminar', 'Eliminó/canceló pedido en Recepción', NULL, NULL, '181.128.146.90', '2026-03-04 16:55:40');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('88', '9', 'pedido', '9', 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.103.24', '2026-03-04 17:05:06');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('89', '9', 'pedido', '9', 'actualizar', 'Transferido - Pedido enviado al área ID: 3', NULL, NULL, '191.108.103.24', '2026-03-04 17:05:25');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('90', '8', 'pedido', '9', 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '181.128.146.90', '2026-03-04 17:08:39');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('91', '8', 'pedido', '9', 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '181.128.146.90', '2026-03-04 17:08:44');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('92', '10', 'pedido', '10', 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '181.128.146.90', '2026-03-04 17:13:45');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('93', NULL, 'pedido', '10', 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '2803:1800:500c:581a:b802:1ff:fe31:728f', '2026-03-04 17:14:20');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('94', '8', 'pedido', '11', 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '181.128.146.90', '2026-03-04 17:14:35');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('95', NULL, 'pedido', '11', 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '2800:e2:407f:fa67:89ad:ef3c:9a12:c303', '2026-03-04 17:15:21');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('96', '8', 'pedido', '10', 'actualizar', 'Actualizó datos del pedido en Recepción', NULL, NULL, '181.128.146.90', '2026-03-04 17:15:23');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('97', '9', 'pedido', '11', 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-04 17:28:06');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('98', '9', 'pedido', '10', 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-04 17:28:12');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('99', NULL, 'pedido', '10', 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '2803:1800:500c:581a:b802:1ff:fe31:728f', '2026-03-04 17:33:53');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('100', NULL, 'pedido', '11', 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '2800:e2:407f:fa67:89ad:ef3c:9a12:c303', '2026-03-04 17:41:27');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('101', '9', 'pedido', '11', 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.103.24', '2026-03-04 17:47:40');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('102', '9', 'pedido', '11', 'actualizar', 'Transferido - Pedido enviado al área ID: 12', NULL, NULL, '191.108.103.24', '2026-03-04 17:47:51');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('103', '9', 'pedido', '11', 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-04 17:48:37');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('104', '9', 'pedido', '11', 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.103.24', '2026-03-04 17:50:51');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('105', '7', 'usuario', '12', 'crear', 'Creó nuevo usuario', NULL, NULL, '181.128.146.90', '2026-03-04 17:55:39');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('106', '9', 'pedido', '8', 'actualizar', 'Transferido - Pedido enviado al área ID: 12', NULL, NULL, '191.108.103.24', '2026-03-04 17:58:54');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('107', '9', 'pedido', '10', 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.103.24', '2026-03-04 18:14:44');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('108', '11', 'pedido', '12', 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '191.108.103.24', '2026-03-04 18:21:19');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('109', NULL, 'pedido', '12', 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '2803:1800:5015:831a:708f:49b5:7875:3fa6', '2026-03-04 18:23:27');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('110', '8', 'pedido', '2', 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '181.128.146.90', '2026-03-04 18:25:09');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('111', '8', 'pedido', '2', 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '181.128.146.90', '2026-03-04 18:25:12');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('112', NULL, 'pedido', '12', 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '190.60.51.156', '2026-03-04 18:34:47');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('113', NULL, 'pedido', '12', 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '190.61.46.138', '2026-03-04 18:38:42');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('114', NULL, 'pedido', '12', 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '190.60.51.156', '2026-03-04 18:42:18');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('115', NULL, 'pedido', '7', 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '2803:1800:5018:be85:1899:b5ef:28ce:ae2d', '2026-03-04 19:16:41');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('116', NULL, 'pedido', '7', 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '74.125.210.133', '2026-03-04 19:16:43');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('117', NULL, 'pedido', '7', 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '74.125.210.133', '2026-03-04 19:16:43');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('118', NULL, 'pedido', '7', 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '74.125.210.131', '2026-03-04 19:16:43');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('119', '1', 'pedido', '13', 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '181.128.146.90', '2026-03-04 20:21:16');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('120', NULL, 'pedido', '13', 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '181.128.146.90', '2026-03-04 20:21:33');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('121', NULL, 'pedido', '13', 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '181.128.146.90', '2026-03-04 20:22:57');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('122', '7', 'pedido', '14', 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '181.128.146.90', '2026-03-04 21:44:01');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('123', '7', 'pedido', '14', 'actualizar', 'Traslado libre a proceso - Movimiento manual en el Kanban.', NULL, NULL, '181.128.146.90', '2026-03-04 21:44:21');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('124', '7', 'pedido', '14', 'actualizar', 'Traslado libre a preparado - Movimiento manual en el Kanban.', NULL, NULL, '181.128.146.90', '2026-03-04 21:44:22');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('125', '7', 'configuracion', '0', 'actualizar', 'Modificó configuraciones: sonido_tema', NULL, NULL, '181.128.146.90', '2026-03-04 21:46:11');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('126', '7', 'configuracion', '0', 'actualizar', 'Modificó configuraciones: sonido_tema', NULL, NULL, '181.128.146.90', '2026-03-04 21:46:12');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('127', '7', 'configuracion', '0', 'actualizar', 'Modificó configuraciones: sonido_tema', NULL, NULL, '181.128.146.90', '2026-03-04 21:46:12');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('128', '7', 'configuracion', '0', 'actualizar', 'Modificó configuraciones: sonido_tema', NULL, NULL, '181.128.146.90', '2026-03-04 21:46:13');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('129', '7', 'configuracion', '0', 'actualizar', 'Modificó configuraciones: sonido_tema', NULL, NULL, '181.128.146.90', '2026-03-04 21:46:14');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('130', '7', 'pedido', '13', 'eliminar', 'Eliminó/canceló pedido en Recepción', NULL, NULL, '181.128.146.90', '2026-03-04 21:46:27');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('131', NULL, 'pedido', '5', 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '181.51.89.61', '2026-03-04 21:57:17');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('132', '7', 'pedido', '7', 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '181.128.146.90', '2026-03-04 22:03:32');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('133', '7', 'pedido', '7', 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '181.128.146.90', '2026-03-04 22:03:35');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('134', NULL, 'pedido', '10', 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '2803:b2a0:82:ddbe:850e:617e:d5cb:57b6', '2026-03-04 22:52:59');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('135', NULL, 'pedido', '8', 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '66.102.7.201', '2026-03-04 23:07:31');
INSERT IGNORE INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES ('136', '7', 'pedido', '14', 'eliminar', 'Eliminó/canceló pedido en Recepción', NULL, NULL, '2800:484:da81:3500:b128:78a1:cc62:a7c2', '2026-03-04 23:08:55');

DROP TABLE IF EXISTS `configuracion`;
CREATE TABLE `configuracion` (
  `clave` varchar(100) NOT NULL,
  `valor` mediumtext DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`clave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT IGNORE INTO `configuracion` (`clave`, `valor`, `updated_at`) VALUES ('auto_backup_diario', '1', '2026-02-26 21:14:16');
INSERT IGNORE INTO `configuracion` (`clave`, `valor`, `updated_at`) VALUES ('empresa_logo', 'data:image/webp;base64,UklGRhgKAABXRUJQVlA4TAwKAAAvd8AbEJX4tm3bTmvbtvT/nzxCI8eHKgQIMS7zWmw3g+0iTAQFAAANt9W9d4hTJI2TuqmVNMWW2v1lpmLe0tm2bfzZtm3b9lbbcXI3AbXGAIFW+MqKOj+7Oi9tpSMJnJANDl35Lwfq0ozeWkkVdHNCGKuj14hUbTGPgI2RqrlX+uGN7MemhOVsr1edUSG2RJiGFbV240LbECXsa2ptygK3gx61bhUSt8NA5LraTfzNoNWxMlbjA7cibl6trL0l2oqAJ9vKWA3ainp3DbmudjWsrSjB7rKtS72QzaiILPCdEbkiFrnbUUF5SDaA/uzKbdJ4Spu9IRcOZrDOU+w+gBtR4bhzikuh3YrdjlPcw7wVdZKbYdwKbLdOsf8PvBEBcrSdYnFt6QGGFUEk56ZQ4GOfyKbXhnCSb5N7Pvp2br91oU7VLHIzAoQ5xdhrdifczaD2EdlrZlU+IVtJ7kmv2xvirQg0lX1ltgTbish8Qq1cjd7BN0Iu89pao56B22Asav0qpGyDGVfQ7keyCQYhr+BhJXwLaDRfQasRuQVhOX8NL/+/b0DJ0HoFrUrAFoRmPssVPAzZgsK1SOv62mm0LagQdK5r4FibX9w3oQpbuAFvtl3QTne1nKZLwkZc+g/AkeXfSUgDbkJVUGtOalNuRNGfZvWtMO20txsRd89PM34jAsMTQbtUN3FiRNNQU4n8FZbQBCcoAfGP8luxOxwnsSvbhED+5nqpmWGhLp1PHdq1adVy16RRg/qHOrVqflhRp3+K3YIQyv3i6NX7RLwBP8Z9oq7QbtgGyNPSV8hMNyHXT+Qp6hp9JNsAQ1mvgc3EA7y+oPb1NXr7NiHXj+nuNehQGrAF7l8Bhy2h1waiu74+pAtktYUHYPPqXh5GOSHbqJBhZRxu8q2NDNfJ16qY7SKvzRTiw4pevtJ/13YGiPcOuZKXClsEHGBtaQA/OzRwXI5Nm5fmJCj4hGxt6CLpbXPN/Uu65ZDlSkSHHzAhm/wHvGNeMowDWv8dEEcE7UIx8ScKcTPi44QmhoU61PjsrCFkr9vgKgfqVFZv/Rf0JrD2GdXIugl9VnfxboYP9s065an6GcbPm2HFP+BscE5iPR/anCMIHrSKDDFIJg0JfBEmpSSl9PqI54W+CORGLcfgL7miXr+LAnCFSDfgSx4Vt8BTGouE++eM5XWcjnmwdQZTqZP+6GRt5Jc23xxTuh8hNM51VYzszabLX+cM4nwkqGgbvdHyhXyxaPXGpgKPBPAz30MNTBxIFi3e2EANXfDzZbQ97vnm7/MkqvfB5iGuNjL0wt1+8iFApOtsfZzZkedMaIarRi1Uaeh9Bk7nExJ1XIUhiCOEZvvNgVq4RB5Td7+MnpCqEFaw9uIPBzAnpIqn5734wxmuVQUq14FarF1xQFUFKtKIWqzNcOjM7hJ7Lz4WKNAdVHdrEFVVtJq7u5GMGrUwD631UYWwhWNZf9EHVFH60Sf6xKuqBPuOGtk/tT2GViF8wHOao5e7us8aY+cd2d3s1oY2IVVmo7r70xqpwqjkOczS3W3GhJRA1d3dOl20wAirPWZHdbdHXCrArI/BTzuMN9k+NcPD+KpAC5Go7pcPRoml2b1+dffLluBVoImsfUkmJ4gmpAqq3Lb7/f4MvaoK3/PhXqWYrbtZTMGsKhInX92tTVzhetjd7YuowCqoTNWo7nY8WBH70N3tF3VVlQjfurv95TMhxc2rHrV6aLdt9zMgXeUzISdTeHvEqu5uX0hrFK25u18GTMi3P4PpAVVVwewavMYr6YzdzWFG4PAy8QPVzSSz6p1h8FFGuAFVZ2gW95/ggqBYxEINcIt9LqhDww/mDHd/u7vpi7/aYa9ZI4cnRZnU3a2ZqmZ114nqbhOqzOvuZtCzxhnWz3m2oVuFtJkAfPFG2eueLxr2nrWtQnf9LKK1L9pnuJctg06H7rOOMw6/FczRwSv8JX9pZ8XraaPqoV3DnZAqTP080YnspffC/DnLg+Ecf6ADw6kO/UEx3RscP2BLLK+jUIkqBu2PqjjbyNDzbL77u7oD7sw5XAiB6f5ge52lYA4OSCMq0GQWVHdzqPm1Sm8edq7uD+rI8MGw3Mkjtp2pTB3asiLzubtbtRmCcA5gQuq+unof/Jb3WCzgAC5k4YxVJZupu3UpDqyqCubYYOuqzBlUU9aJl8F2Y3gZUTuqu70Jf0JGcl8Hs1elkKW7WfWfC8pDd9pH410kBE9zdQ3fkkv58Es2BDWJtbv/KlwVH/+6u39IBnQDf78ZPtWIOou393ln/WDv0VO8CdGLrbtZTMU5gOCkxmno7vaT+6pC2D+wOyuW4luEDTq6u92Py1lOZjY8oChVdnfrctsOJ335QnU3h3WwVZV0bd3dSM3+vGn86O5mVFwrYLEeZ0IKYSOyl//wqHWFbilrL/5wGOdyL9UWcaqqovSYYwm7+8LrJDwvzyKP6UhxtYNhCbvLPOsyD0btPllHBatZXg5/vuaYfDGDeEJOCzBEHdsZuDrJiJwrrN1H5rkPNVb81mn7cqWSRP8GnZDj2LRmOuv+j1v2mSaTPHBCRpi8fd/3us8UXLje+y6giAyi3/e95wpfqsG7EuyhAp7yrHTJA4/ctXcXEnxCRpi8fd/3x9ylEZhPjMC6ZjSscDCD1n8a+3Lw5/OxBHh7Ph8zHXpV9fui4PBjv++Xe9P77TwH7IAuSM6XUkXECwiNVvZDhz+EHW1Vvf2F3YfAR1XFl/LY18flhBskuOpB+MSoB8FbhQirHxXACT/wwD4wCxfvHRySG17FmQ7nxfnva+Auo4o40RivwoxT0nauQLhJ+/4ubG7Q0MIoLIIEC6knATq8ni3452JBZZDQBf+WI7X1e5MlUQ5pVIZKfqJTyZQiNIHxECJLqjL8IyVOCJU0qTQBA114caNOuPQQJT/CjJUhlTZOSRPyw1u6JDEfUfCRK8lIEeRy7vSCI5N9N+b7YjyllERe1KKK0DDKt0+ReidG9IRQcZcBf4WesAT+hBYoi/QYlSTDeO90uIMxSUySGHR5oU2IsoiMDBH4jFfS3mj0xi70SOIth1PJpnlyKVk0CriVJOpLBeif0mSmD514voWjryBlMmTR/MZnQuIVb5qq129C9AFLKNGRlwzRpsh8ysIenlVVskOUsohSHFBSE5A0KnGKYfWKJN4SYSWaig5zQkS/6YU9ISI/L4UvPeKImk6IIn5tAtcXJS33IbJ30YJ/ExZfqboLVU48pEgYD92koMmmvoubkAUSdRMiLy6ZRnNPenhJo3oTKyGBYiKN1y5JoJnUwiRTmvgUIfXD6OeluOBVFUH4hLRfiibkLl5QsPDCre8wwxApGju8cBHCdxL0QbBsaUJCVBjPrzgN2OTDj4j970+8v0WF8xMZ/gdcqG54YYYLE1S8ud4FFp8GKcV99KWOCzaYd208', '2026-02-25 22:36:57');
INSERT IGNORE INTO `configuracion` (`clave`, `valor`, `updated_at`) VALUES ('empresa_nombre', 'Banner', '2026-02-26 21:30:44');
INSERT IGNORE INTO `configuracion` (`clave`, `valor`, `updated_at`) VALUES ('fondo_login', '/public/img/fondos/fondo_login_1772678763.jpg', '2026-03-04 21:46:03');
INSERT IGNORE INTO `configuracion` (`clave`, `valor`, `updated_at`) VALUES ('icon_areas', '<svg width=\"22\" height=\"22\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"#6366f1\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><circle cx=\"12\" cy=\"12\" r=\"3\"/><path d=\"M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z\"/></svg>', '2026-02-26 22:01:19');
INSERT IGNORE INTO `configuracion` (`clave`, `valor`, `updated_at`) VALUES ('icon_configuracion', '<svg width=\"22\" height=\"22\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"#6366f1\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><circle cx=\"12\" cy=\"12\" r=\"3\"/><path d=\"M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z\"/></svg>', '2026-02-26 22:01:19');
INSERT IGNORE INTO `configuracion` (`clave`, `valor`, `updated_at`) VALUES ('icon_dashboard', '<svg width=\"22\" height=\"22\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"#6366f1\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><rect x=\"3\" y=\"3\" width=\"7\" height=\"7\"/><rect x=\"14\" y=\"3\" width=\"7\" height=\"7\"/><rect x=\"14\" y=\"14\" width=\"7\" height=\"7\"/><rect x=\"3\" y=\"14\" width=\"7\" height=\"7\"/></svg>', '2026-02-26 22:01:19');
INSERT IGNORE INTO `configuracion` (`clave`, `valor`, `updated_at`) VALUES ('icon_recepcion', '<svg width=\"22\" height=\"22\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"#6366f1\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path d=\"M22 12h-4l-3 9L9 3l-3 9H2\"/></svg>', '2026-02-26 22:01:19');
INSERT IGNORE INTO `configuracion` (`clave`, `valor`, `updated_at`) VALUES ('icon_reportes', '<svg width=\"22\" height=\"22\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"#6366f1\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path d=\"M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z\"/><polyline points=\"14 2 14 8 20 8\"/><line x1=\"16\" y1=\"13\" x2=\"8\" y2=\"13\"/><line x1=\"16\" y1=\"17\" x2=\"8\" y2=\"17\"/></svg>', '2026-02-26 22:01:19');
INSERT IGNORE INTO `configuracion` (`clave`, `valor`, `updated_at`) VALUES ('icon_reportes_pedidos', '<svg width=\"22\" height=\"22\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"#6366f1\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path d=\"M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2\"/><rect x=\"8\" y=\"2\" width=\"8\" height=\"4\" rx=\"1\" ry=\"1\"/></svg>', '2026-02-26 22:01:19');
INSERT IGNORE INTO `configuracion` (`clave`, `valor`, `updated_at`) VALUES ('icon_usuarios', '<svg width=\"22\" height=\"22\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"#6366f1\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path d=\"M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2\"/><circle cx=\"9\" cy=\"7\" r=\"4\"/><path d=\"M23 21v-2a4 4 0 0 0-3-3.87\"/><path d=\"M16 3.13a4 4 0 0 1 0 7.75\"/></svg>', '2026-02-26 22:01:19');
INSERT IGNORE INTO `configuracion` (`clave`, `valor`, `updated_at`) VALUES ('mostrar_credenciales', '0', '2026-02-28 17:43:18');
INSERT IGNORE INTO `configuracion` (`clave`, `valor`, `updated_at`) VALUES ('onurix_api_id', '7784', '2026-03-02 18:54:09');
INSERT IGNORE INTO `configuracion` (`clave`, `valor`, `updated_at`) VALUES ('onurix_api_key', 'b8db96bb2ed56e7ca15fa12a17fafbce4dbeedad69a62091e53a8', '2026-03-02 18:47:46');
INSERT IGNORE INTO `configuracion` (`clave`, `valor`, `updated_at`) VALUES ('sms_crear', 'informa al cliente {nombre}, que se ha creado su pedido {numero_pedido} exitosamente, puede consultar el estado en {link_seguimiento}  Gracias por confiar en nosotros.', '2026-03-04 12:24:20');
INSERT IGNORE INTO `configuracion` (`clave`, `valor`, `updated_at`) VALUES ('sms_finalizar', 'Informa que el cliente {nombre}, tiene listo su pedido {numero_pedido}, por favor acérquese a la oficina para reclamarlo.', '2026-03-04 12:24:20');
INSERT IGNORE INTO `configuracion` (`clave`, `valor`, `updated_at`) VALUES ('sonido_tema', 'neo', '2026-03-04 21:46:14');
INSERT IGNORE INTO `configuracion` (`clave`, `valor`, `updated_at`) VALUES ('ultima_exportacion_db', '2026-03-04', '2026-03-03 19:16:15');
INSERT IGNORE INTO `configuracion` (`clave`, `valor`, `updated_at`) VALUES ('whatsapp_activo', '1', '2026-03-03 18:57:14');
INSERT IGNORE INTO `configuracion` (`clave`, `valor`, `updated_at`) VALUES ('whatsapp_phone_sender_id', '3026084603', '2026-03-03 18:57:14');
INSERT IGNORE INTO `configuracion` (`clave`, `valor`, `updated_at`) VALUES ('whatsapp_template_id', '1957715494954382', '2026-03-03 18:57:14');
INSERT IGNORE INTO `configuracion` (`clave`, `valor`, `updated_at`) VALUES ('whatsapp_template_id_finalizar', '26142719632013996', '2026-03-03 18:57:14');
INSERT IGNORE INTO `configuracion` (`clave`, `valor`, `updated_at`) VALUES ('whatsapp_var_link', 'link', '2026-03-03 18:57:14');
INSERT IGNORE INTO `configuracion` (`clave`, `valor`, `updated_at`) VALUES ('whatsapp_var_nombre', 'nombre', '2026-03-03 18:57:14');

DROP TABLE IF EXISTS `devoluciones`;
CREATE TABLE `devoluciones` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pedido_id` int(10) unsigned NOT NULL,
  `usuario_id` int(10) unsigned NOT NULL,
  `motivo` text NOT NULL,
  `resolucion` text DEFAULT NULL,
  `estado` enum('abierta','en_revision','resuelta','rechazada') DEFAULT 'abierta',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_dev_pedido` (`pedido_id`),
  KEY `fk_dev_usuario` (`usuario_id`),
  CONSTRAINT `fk_dev_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_dev_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `movimientos_pedido`;
CREATE TABLE `movimientos_pedido` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pedido_id` int(10) unsigned NOT NULL,
  `usuario_id` int(10) unsigned DEFAULT NULL,
  `area_id` int(10) unsigned DEFAULT NULL,
  `accion` varchar(100) NOT NULL,
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_mp_usuario` (`usuario_id`),
  KEY `fk_mp_area` (`area_id`),
  KEY `idx_pedido_historial` (`pedido_id`),
  CONSTRAINT `fk_mp_area` FOREIGN KEY (`area_id`) REFERENCES `areas` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_mp_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_mp_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=82 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('1', '1', '7', NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-04 12:12:04');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('2', '1', '1', NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Area Impresion', '2026-03-04 12:20:48');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('3', '1', '1', '8', 'Traslado libre a proceso', 'Movimiento manual en el Kanban.', '2026-03-04 12:21:15');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('4', '1', '1', '8', 'Traslado libre a preparado', 'Movimiento manual en el Kanban.', '2026-03-04 12:21:17');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('5', '1', '1', '8', 'Completado', 'Pedido finalizado y completado', '2026-03-04 12:21:19');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('6', '2', '7', NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-04 12:49:20');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('7', '2', '7', NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Área de Diseño', '2026-03-04 12:49:35');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('8', '2', '10', '2', 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-04 12:59:45');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('9', '2', '10', '2', 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-04 13:00:04');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('10', '2', '7', '2', 'Editado', 'Datos del pedido actualizados desde Recepción', '2026-03-04 13:02:22');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('11', '2', '10', '2', 'Traslado libre a proceso', 'Movimiento manual en el Kanban.', '2026-03-04 13:16:40');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('12', '2', '10', '2', 'Traslado libre a preparado', 'Movimiento manual en el Kanban.', '2026-03-04 13:16:42');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('13', '2', '10', '2', 'Traslado libre a proceso', 'Movimiento manual en el Kanban.', '2026-03-04 13:45:09');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('14', '2', '10', '2', 'Traslado libre a preparado', 'Movimiento manual en el Kanban.', '2026-03-04 13:45:12');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('15', '2', '10', '2', 'Transferido', 'Pedido enviado al área ID: 8', '2026-03-04 13:45:54');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('16', '3', '11', NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-04 14:16:06');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('17', '3', '11', NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Área de Diseño', '2026-03-04 14:16:32');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('18', '3', '11', '2', 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-04 14:16:43');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('19', '3', '11', '2', 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-04 14:17:04');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('20', '3', '11', '2', 'Transferido', 'Pedido enviado al área ID: 9', '2026-03-04 14:17:14');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('21', '4', '7', NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-04 14:34:08');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('22', '4', '7', NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Área de Diseño', '2026-03-04 14:34:59');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('23', '4', '7', '2', 'Enviado a Área', 'Pedido enviado al área: Impresion General', '2026-03-04 14:35:17');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('24', '4', '7', '8', 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-04 14:35:30');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('25', '4', '7', '8', 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-04 14:35:31');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('26', '4', '7', '8', 'Completado', 'Pedido finalizado y completado', '2026-03-04 14:35:37');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('27', '5', '11', NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-04 15:48:24');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('28', '5', '11', NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Impresion DTF', '2026-03-04 15:48:53');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('29', '6', '11', NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-04 15:50:53');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('30', '7', '7', NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-04 16:21:35');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('31', '7', '7', NULL, 'Adjunto', 'Archivos adjuntados al crear: LOGOMISIONJUVENIL.rar ', '2026-03-04 16:21:38');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('32', '7', '7', NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Impresion DTF', '2026-03-04 16:23:24');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('33', '6', '7', NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Tirajes y Litografía', '2026-03-04 16:35:22');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('34', '6', '7', '11', 'Enviado a Área', 'Pedido enviado al área: Diseño y Armado DTF', '2026-03-04 16:36:01');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('35', '2', '7', '8', 'Enviado a Área', 'Pedido enviado al área: Tirajes y Litografía', '2026-03-04 16:36:15');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('36', '8', '10', NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-04 16:42:05');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('37', '8', '10', NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Impresion General', '2026-03-04 16:42:18');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('38', '8', '9', '8', 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-04 16:42:59');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('39', '8', '9', '8', 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-04 16:44:16');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('40', '9', '8', NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-04 16:46:55');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('41', '9', '8', NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Impresion General', '2026-03-04 16:47:23');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('42', '9', '9', '8', 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-04 16:48:09');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('43', '1', '7', '8', 'Cancelado', 'Pedido eliminado desde Recepción', '2026-03-04 16:55:29');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('44', '4', '7', '8', 'Cancelado', 'Pedido eliminado desde Recepción', '2026-03-04 16:55:40');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('45', '9', '9', '8', 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-04 17:05:06');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('46', '9', '9', '8', 'Transferido', 'Pedido enviado al área ID: 3', '2026-03-04 17:05:25');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('47', '9', '8', '3', 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-04 17:08:39');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('48', '9', '8', '3', 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-04 17:08:44');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('49', '9', '8', '3', 'Completado', 'Pedido finalizado y completado', '2026-03-04 17:08:49');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('50', '10', '10', NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-04 17:13:44');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('51', '11', '8', NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-04 17:14:34');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('52', '10', '8', NULL, 'Editado', 'Datos del pedido actualizados desde Recepción', '2026-03-04 17:15:23');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('53', '11', '8', NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Impresion General', '2026-03-04 17:15:31');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('54', '10', '8', NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Impresion General', '2026-03-04 17:15:38');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('55', '11', '9', '8', 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-04 17:28:06');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('56', '10', '9', '8', 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-04 17:28:12');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('57', '11', '9', '8', 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-04 17:47:40');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('58', '11', '9', '8', 'Transferido', 'Pedido enviado al área ID: 12', '2026-03-04 17:47:51');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('59', '11', '9', '12', 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-04 17:48:37');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('60', '11', '9', '12', 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-04 17:50:51');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('61', '11', '9', '12', 'Completado', 'Pedido finalizado y completado', '2026-03-04 17:50:57');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('62', '8', '9', '8', 'Transferido', 'Pedido enviado al área ID: 12', '2026-03-04 17:58:54');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('63', '10', '9', '8', 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-04 18:14:44');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('64', '10', '9', '8', 'Completado', 'Pedido finalizado y completado', '2026-03-04 18:17:39');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('65', '12', '11', NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-04 18:21:18');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('66', '2', '8', '11', 'Enviado a Área', 'Pedido enviado al área: Empaque y verificación', '2026-03-04 18:24:49');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('67', '2', '8', '3', 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-04 18:25:09');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('68', '2', '8', '3', 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-04 18:25:12');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('69', '2', '8', '3', 'Completado', 'Pedido finalizado y completado', '2026-03-04 18:25:15');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('70', '13', '1', NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-04 20:21:13');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('71', '14', '7', NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-04 21:43:58');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('72', '14', '7', NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Impresion DTF', '2026-03-04 21:44:14');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('73', '14', '7', '9', 'Traslado libre a proceso', 'Movimiento manual en el Kanban.', '2026-03-04 21:44:21');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('74', '14', '7', '9', 'Traslado libre a preparado', 'Movimiento manual en el Kanban.', '2026-03-04 21:44:22');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('75', '14', '7', '9', 'Completado', 'Pedido finalizado y completado', '2026-03-04 21:44:26');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('76', '13', '7', NULL, 'Cancelado', 'Pedido eliminado desde Recepción', '2026-03-04 21:46:27');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('77', '7', '7', '9', 'Enviado a Área', 'Pedido enviado al área: Impresion DTF', '2026-03-04 22:03:25');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('78', '7', '7', '9', 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-04 22:03:32');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('79', '7', '7', '9', 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-04 22:03:35');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('80', '7', '7', '9', 'Completado', 'Pedido finalizado y completado', '2026-03-04 22:03:42');
INSERT IGNORE INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES ('81', '14', '7', '9', 'Cancelado', 'Pedido eliminado desde Recepción', '2026-03-04 23:08:55');

DROP TABLE IF EXISTS `notificaciones_salientes`;
CREATE TABLE `notificaciones_salientes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `destinatario` varchar(100) NOT NULL,
  `tipo` enum('email','sms','sistema') NOT NULL,
  `asunto` varchar(255) DEFAULT NULL,
  `mensaje` text NOT NULL,
  `estado` enum('pendiente','enviado','fallido') DEFAULT 'pendiente',
  `intentos` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_estado_notif` (`estado`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `notificaciones_salientes` (`id`, `destinatario`, `tipo`, `asunto`, `mensaje`, `estado`, `intentos`, `created_at`, `updated_at`) VALUES ('1', '3184483187', 'sms', NULL, 'Hola eliza, tu pedido banner.com.co/seguimiento/XJB4YT ha sido creado. Gracias por confiar en Banner', 'pendiente', '0', '2026-02-25 12:16:46', '2026-02-25 12:16:46');

DROP TABLE IF EXISTS `pagos`;
CREATE TABLE `pagos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pedido_id` int(10) unsigned NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `metodo_pago` varchar(50) NOT NULL,
  `referencia` varchar(100) DEFAULT NULL,
  `fecha_pago` timestamp NULL DEFAULT current_timestamp(),
  `estado` enum('completado','pendiente','fallido','reembolsado') DEFAULT 'completado',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_pago_pedido` (`pedido_id`),
  CONSTRAINT `fk_pago_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `pedidos`;
CREATE TABLE `pedidos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `token_seguimiento` varchar(64) DEFAULT NULL,
  `cliente_nombre` varchar(150) NOT NULL,
  `cliente_email` varchar(100) DEFAULT NULL,
  `cliente_telefono` varchar(20) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `area_actual_id` int(10) unsigned DEFAULT NULL,
  `fase_actual` enum('recepcion','proceso','preparado') DEFAULT 'recepcion',
  `estado_pago` enum('pago_completo','abono','no_pago') DEFAULT 'no_pago',
  `prioridad` enum('prioridad','normal','largo') DEFAULT 'normal',
  `asignado_a_usuario_id` int(10) unsigned DEFAULT NULL,
  `estado` enum('pendiente','en_curso','completado','cancelado') DEFAULT 'pendiente',
  `fecha_entrega_esperada` date DEFAULT NULL,
  `total` decimal(10,2) DEFAULT 0.00,
  `abonado` decimal(10,2) DEFAULT 0.00,
  `last_movement_at` timestamp NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token_seguimiento` (`token_seguimiento`),
  KEY `fk_pedido_asignado` (`asignado_a_usuario_id`),
  KEY `idx_fase_actual` (`fase_actual`),
  KEY `idx_estado` (`estado`),
  KEY `idx_kanban` (`area_actual_id`,`fase_actual`),
  CONSTRAINT `fk_pedido_asignado` FOREIGN KEY (`asignado_a_usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `pedidos` (`id`, `token_seguimiento`, `cliente_nombre`, `cliente_email`, `cliente_telefono`, `descripcion`, `area_actual_id`, `fase_actual`, `estado_pago`, `prioridad`, `asignado_a_usuario_id`, `estado`, `fecha_entrega_esperada`, `total`, `abonado`, `last_movement_at`, `created_at`, `updated_at`, `deleted_at`) VALUES ('1', 'HOUCBG', 'Uriel', '', '3116623246', 'tttt', '8', 'preparado', 'no_pago', 'normal', '1', 'cancelado', NULL, '50000.00', '0.00', '2026-03-04 12:21:19', '2026-03-04 12:12:04', '2026-03-05 01:06:43', '2026-03-04 11:55:29');
INSERT IGNORE INTO `pedidos` (`id`, `token_seguimiento`, `cliente_nombre`, `cliente_email`, `cliente_telefono`, `descripcion`, `area_actual_id`, `fase_actual`, `estado_pago`, `prioridad`, `asignado_a_usuario_id`, `estado`, `fecha_entrega_esperada`, `total`, `abonado`, `last_movement_at`, `created_at`, `updated_at`, `deleted_at`) VALUES ('2', 'SLUXT2', 'Alexis', '', '3022404391', '5.000 Stikers de como marcar', '3', 'preparado', 'pago_completo', 'prioridad', '8', 'completado', NULL, '50000.00', '0.00', '2026-03-04 18:25:15', '2026-03-04 12:49:20', '2026-03-04 18:25:15', NULL);
INSERT IGNORE INTO `pedidos` (`id`, `token_seguimiento`, `cliente_nombre`, `cliente_email`, `cliente_telefono`, `descripcion`, `area_actual_id`, `fase_actual`, `estado_pago`, `prioridad`, `asignado_a_usuario_id`, `estado`, `fecha_entrega_esperada`, `total`, `abonado`, `last_movement_at`, `created_at`, `updated_at`, `deleted_at`) VALUES ('3', 'SA0RL3', 'Euclides', '', '3332644603', 'DTF 57X72 CM', '9', 'recepcion', 'no_pago', 'normal', NULL, 'pendiente', NULL, '18000.00', '0.00', '2026-03-04 14:17:14', '2026-03-04 14:16:06', '2026-03-04 14:17:14', NULL);
INSERT IGNORE INTO `pedidos` (`id`, `token_seguimiento`, `cliente_nombre`, `cliente_email`, `cliente_telefono`, `descripcion`, `area_actual_id`, `fase_actual`, `estado_pago`, `prioridad`, `asignado_a_usuario_id`, `estado`, `fecha_entrega_esperada`, `total`, `abonado`, `last_movement_at`, `created_at`, `updated_at`, `deleted_at`) VALUES ('4', 'SODI4E', 'Said', '', '3004352910', 'Pendon 100x70 con tubos', '8', 'preparado', 'abono', 'normal', '7', 'cancelado', NULL, '35000.00', '20000.00', '2026-03-04 14:35:37', '2026-03-04 14:34:08', '2026-03-05 01:06:43', '2026-03-04 11:55:40');
INSERT IGNORE INTO `pedidos` (`id`, `token_seguimiento`, `cliente_nombre`, `cliente_email`, `cliente_telefono`, `descripcion`, `area_actual_id`, `fase_actual`, `estado_pago`, `prioridad`, `asignado_a_usuario_id`, `estado`, `fecha_entrega_esperada`, `total`, `abonado`, `last_movement_at`, `created_at`, `updated_at`, `deleted_at`) VALUES ('5', 'PMTLQC', 'Marisleylis', '', '3206055184', 'DTF 58X128 CM', '9', 'recepcion', 'pago_completo', 'normal', NULL, 'pendiente', NULL, '31.00', '0.00', '2026-03-04 15:48:53', '2026-03-04 15:48:24', '2026-03-04 15:48:53', NULL);
INSERT IGNORE INTO `pedidos` (`id`, `token_seguimiento`, `cliente_nombre`, `cliente_email`, `cliente_telefono`, `descripcion`, `area_actual_id`, `fase_actual`, `estado_pago`, `prioridad`, `asignado_a_usuario_id`, `estado`, `fecha_entrega_esperada`, `total`, `abonado`, `last_movement_at`, `created_at`, `updated_at`, `deleted_at`) VALUES ('6', 'DGLV9X', 'Sandra', '', '3127447368', 'DTF 56X165 CM', '10', 'recepcion', 'no_pago', 'normal', NULL, 'pendiente', NULL, '41.00', '0.00', '2026-03-04 16:36:01', '2026-03-04 15:50:53', '2026-03-04 16:36:01', NULL);
INSERT IGNORE INTO `pedidos` (`id`, `token_seguimiento`, `cliente_nombre`, `cliente_email`, `cliente_telefono`, `descripcion`, `area_actual_id`, `fase_actual`, `estado_pago`, `prioridad`, `asignado_a_usuario_id`, `estado`, `fecha_entrega_esperada`, `total`, `abonado`, `last_movement_at`, `created_at`, `updated_at`, `deleted_at`) VALUES ('7', 'W642SR', 'FERNANDO', '', '3024063801', 'Impresion DTF, y marcada de suéter con logo misión juvenil', '9', 'preparado', 'pago_completo', 'normal', '7', 'completado', NULL, '16000.00', '0.00', '2026-03-04 22:03:42', '2026-03-04 16:21:35', '2026-03-04 22:03:42', NULL);
INSERT IGNORE INTO `pedidos` (`id`, `token_seguimiento`, `cliente_nombre`, `cliente_email`, `cliente_telefono`, `descripcion`, `area_actual_id`, `fase_actual`, `estado_pago`, `prioridad`, `asignado_a_usuario_id`, `estado`, `fecha_entrega_esperada`, `total`, `abonado`, `last_movement_at`, `created_at`, `updated_at`, `deleted_at`) VALUES ('8', '2PJ0C9', 'JHONATAN MAXIM', '', '3009835251', '250 IMPRESIÓN FUL COLOR TAMAÑO CARTA EN VINILO LAMINADO BRILLANTE\r\n4 ARCHIVOS DE 148X273 DESCARGAR EN TELEGRAM', '12', 'recepcion', 'pago_completo', 'normal', NULL, 'pendiente', NULL, '575000.00', '0.00', '2026-03-04 17:58:54', '2026-03-04 16:42:05', '2026-03-04 17:58:54', NULL);
INSERT IGNORE INTO `pedidos` (`id`, `token_seguimiento`, `cliente_nombre`, `cliente_email`, `cliente_telefono`, `descripcion`, `area_actual_id`, `fase_actual`, `estado_pago`, `prioridad`, `asignado_a_usuario_id`, `estado`, `fecha_entrega_esperada`, `total`, `abonado`, `last_movement_at`, `created_at`, `updated_at`, `deleted_at`) VALUES ('9', 'K4CI3Y', 'mono', '', '304 3931841', 'VIN SIN LAM 50X50', '3', 'preparado', 'no_pago', 'prioridad', '8', 'completado', NULL, '8000.00', '0.00', '2026-03-04 17:08:49', '2026-03-04 16:46:55', '2026-03-04 17:08:49', NULL);
INSERT IGNORE INTO `pedidos` (`id`, `token_seguimiento`, `cliente_nombre`, `cliente_email`, `cliente_telefono`, `descripcion`, `area_actual_id`, `fase_actual`, `estado_pago`, `prioridad`, `asignado_a_usuario_id`, `estado`, `fecha_entrega_esperada`, `total`, `abonado`, `last_movement_at`, `created_at`, `updated_at`, `deleted_at`) VALUES ('10', 'OFNZHR', 'Isamar', '', '316 7196702', 'STICKERS ISAMAR\r\nARCHIVO TAMAÑO DE 200MB EN IMPRESIÓN DIGITAL CON NOMBRE: 144X157 VIN Isamar2', '8', 'preparado', 'no_pago', 'prioridad', '9', 'completado', NULL, '45000.00', '0.00', '2026-03-04 18:17:39', '2026-03-04 17:13:44', '2026-03-04 18:17:39', NULL);
INSERT IGNORE INTO `pedidos` (`id`, `token_seguimiento`, `cliente_nombre`, `cliente_email`, `cliente_telefono`, `descripcion`, `area_actual_id`, `fase_actual`, `estado_pago`, `prioridad`, `asignado_a_usuario_id`, `estado`, `fecha_entrega_esperada`, `total`, `abonado`, `last_movement_at`, `created_at`, `updated_at`, `deleted_at`) VALUES ('11', 'P1WNCQ', 'JASSER', '', '319 6353431', 'VINILO LAMINADO 100X30', '12', 'preparado', 'no_pago', 'prioridad', '9', 'completado', NULL, '10000.00', '0.00', '2026-03-04 17:50:57', '2026-03-04 17:14:34', '2026-03-04 17:50:57', NULL);
INSERT IGNORE INTO `pedidos` (`id`, `token_seguimiento`, `cliente_nombre`, `cliente_email`, `cliente_telefono`, `descripcion`, `area_actual_id`, `fase_actual`, `estado_pago`, `prioridad`, `asignado_a_usuario_id`, `estado`, `fecha_entrega_esperada`, `total`, `abonado`, `last_movement_at`, `created_at`, `updated_at`, `deleted_at`) VALUES ('12', 'F2VIO8', 'Juan', '', '3044476390', 'DTF 40X8 CM', NULL, 'recepcion', 'no_pago', 'normal', NULL, 'pendiente', NULL, '10.00', '0.00', '2026-03-04 18:21:18', '2026-03-04 18:21:18', '2026-03-04 18:21:18', NULL);
INSERT IGNORE INTO `pedidos` (`id`, `token_seguimiento`, `cliente_nombre`, `cliente_email`, `cliente_telefono`, `descripcion`, `area_actual_id`, `fase_actual`, `estado_pago`, `prioridad`, `asignado_a_usuario_id`, `estado`, `fecha_entrega_esperada`, `total`, `abonado`, `last_movement_at`, `created_at`, `updated_at`, `deleted_at`) VALUES ('13', 'PM38J6', 'Daniel', '', '3184483187', 'Pedido de prueba', NULL, 'recepcion', 'no_pago', 'normal', NULL, 'cancelado', NULL, '100000.00', '0.00', '2026-03-04 20:21:13', '2026-03-04 20:21:13', '2026-03-04 21:46:27', '2026-03-04 21:46:27');
INSERT IGNORE INTO `pedidos` (`id`, `token_seguimiento`, `cliente_nombre`, `cliente_email`, `cliente_telefono`, `descripcion`, `area_actual_id`, `fase_actual`, `estado_pago`, `prioridad`, `asignado_a_usuario_id`, `estado`, `fecha_entrega_esperada`, `total`, `abonado`, `last_movement_at`, `created_at`, `updated_at`, `deleted_at`) VALUES ('14', 'YT09ZI', 'Uriel', '', '3184483187', 'hola', '9', 'preparado', 'no_pago', 'normal', '7', 'cancelado', NULL, '15.00', '0.00', '2026-03-04 21:44:26', '2026-03-04 21:43:58', '2026-03-04 23:08:55', '2026-03-04 23:08:55');

DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `estado` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `roles` (`id`, `nombre`, `descripcion`, `estado`, `created_at`, `updated_at`, `deleted_at`) VALUES ('1', 'Admin', 'Administrador Global del ERP', '1', '2026-02-23 20:48:33', '2026-02-23 20:48:33', NULL);
INSERT IGNORE INTO `roles` (`id`, `nombre`, `descripcion`, `estado`, `created_at`, `updated_at`, `deleted_at`) VALUES ('2', 'Operador', 'Operador de Planta o Almacén', '1', '2026-02-23 20:48:33', '2026-02-23 20:48:33', NULL);

DROP TABLE IF EXISTS `usuario_areas`;
CREATE TABLE `usuario_areas` (
  `usuario_id` int(10) unsigned NOT NULL,
  `area_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`usuario_id`,`area_id`),
  KEY `fk_ua_area` (`area_id`),
  CONSTRAINT `fk_ua_area` FOREIGN KEY (`area_id`) REFERENCES `areas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ua_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `usuario_areas` (`usuario_id`, `area_id`) VALUES ('9', '1');
INSERT IGNORE INTO `usuario_areas` (`usuario_id`, `area_id`) VALUES ('12', '1');
INSERT IGNORE INTO `usuario_areas` (`usuario_id`, `area_id`) VALUES ('8', '2');
INSERT IGNORE INTO `usuario_areas` (`usuario_id`, `area_id`) VALUES ('10', '2');
INSERT IGNORE INTO `usuario_areas` (`usuario_id`, `area_id`) VALUES ('12', '2');
INSERT IGNORE INTO `usuario_areas` (`usuario_id`, `area_id`) VALUES ('8', '3');
INSERT IGNORE INTO `usuario_areas` (`usuario_id`, `area_id`) VALUES ('9', '3');
INSERT IGNORE INTO `usuario_areas` (`usuario_id`, `area_id`) VALUES ('10', '3');
INSERT IGNORE INTO `usuario_areas` (`usuario_id`, `area_id`) VALUES ('12', '3');
INSERT IGNORE INTO `usuario_areas` (`usuario_id`, `area_id`) VALUES ('12', '4');
INSERT IGNORE INTO `usuario_areas` (`usuario_id`, `area_id`) VALUES ('8', '8');
INSERT IGNORE INTO `usuario_areas` (`usuario_id`, `area_id`) VALUES ('9', '8');
INSERT IGNORE INTO `usuario_areas` (`usuario_id`, `area_id`) VALUES ('10', '8');
INSERT IGNORE INTO `usuario_areas` (`usuario_id`, `area_id`) VALUES ('12', '8');
INSERT IGNORE INTO `usuario_areas` (`usuario_id`, `area_id`) VALUES ('11', '9');
INSERT IGNORE INTO `usuario_areas` (`usuario_id`, `area_id`) VALUES ('12', '9');
INSERT IGNORE INTO `usuario_areas` (`usuario_id`, `area_id`) VALUES ('11', '10');
INSERT IGNORE INTO `usuario_areas` (`usuario_id`, `area_id`) VALUES ('12', '10');
INSERT IGNORE INTO `usuario_areas` (`usuario_id`, `area_id`) VALUES ('12', '11');
INSERT IGNORE INTO `usuario_areas` (`usuario_id`, `area_id`) VALUES ('8', '12');
INSERT IGNORE INTO `usuario_areas` (`usuario_id`, `area_id`) VALUES ('9', '12');
INSERT IGNORE INTO `usuario_areas` (`usuario_id`, `area_id`) VALUES ('12', '12');

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE `usuarios` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rol_id` int(10) unsigned NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `foto_perfil` varchar(500) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `estado` tinyint(1) DEFAULT 1,
  `last_activity` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  `ver_precios` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `fk_usuario_rol` (`rol_id`),
  CONSTRAINT `fk_usuario_rol` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `usuarios` (`id`, `rol_id`, `nombre`, `email`, `foto_perfil`, `password_hash`, `estado`, `last_activity`, `created_at`, `updated_at`, `deleted_at`, `ver_precios`) VALUES ('1', '1', 'Admin Maestro', 'admin@erp.com', '/public/uploads/perfiles/u1_1772132094.jpg', '$2y$10$B9JRJnZk7RKJ7Ar/iBX/VudnXgQVv1zTuEHK11/4xsFRNzcurQGaq', '1', '2026-03-05 01:11:04', '2026-02-24 01:48:33', '2026-03-05 01:11:04', NULL, '0');
INSERT IGNORE INTO `usuarios` (`id`, `rol_id`, `nombre`, `email`, `foto_perfil`, `password_hash`, `estado`, `last_activity`, `created_at`, `updated_at`, `deleted_at`, `ver_precios`) VALUES ('7', '1', 'Jefe Uriel Calvo', 'uriel', NULL, '$2y$10$S9394S0cQusKeJx8VLETgOu/sbOTNlAOoQaPR1.WvBLFeusqR84ty', '1', '2026-03-04 23:13:00', '2026-03-02 19:44:28', '2026-03-04 23:13:00', NULL, '1');
INSERT IGNORE INTO `usuarios` (`id`, `rol_id`, `nombre`, `email`, `foto_perfil`, `password_hash`, `estado`, `last_activity`, `created_at`, `updated_at`, `deleted_at`, `ver_precios`) VALUES ('8', '2', 'Dissy', 'dissy', NULL, '$2y$10$wn415jV4/VrxiBjgw9e8EeD2tkkeT8AKgPTAsN48.fg6hw58iW7ai', '1', '2026-03-04 19:43:04', '2026-03-04 12:33:51', '2026-03-04 19:43:04', NULL, '1');
INSERT IGNORE INTO `usuarios` (`id`, `rol_id`, `nombre`, `email`, `foto_perfil`, `password_hash`, `estado`, `last_activity`, `created_at`, `updated_at`, `deleted_at`, `ver_precios`) VALUES ('9', '2', 'Isaid Banquez', 'isai', NULL, '$2y$10$XACvXMr.0HkNq9cIK2A1duHzu0Hi8BL0WpZYe3tXbRpouvGHA/yOW', '1', '2026-03-04 18:41:14', '2026-03-04 12:36:40', '2026-03-04 18:41:14', NULL, '0');
INSERT IGNORE INTO `usuarios` (`id`, `rol_id`, `nombre`, `email`, `foto_perfil`, `password_hash`, `estado`, `last_activity`, `created_at`, `updated_at`, `deleted_at`, `ver_precios`) VALUES ('10', '2', 'Valentina', 'valentin', NULL, '$2y$10$v8X2crRdT2k0IzJrtd33y.5t2uwrL7sgKjb1JZvboNEGAFhGvh0C6', '1', '2026-03-04 18:32:24', '2026-03-04 12:50:33', '2026-03-04 18:32:24', NULL, '0');
INSERT IGNORE INTO `usuarios` (`id`, `rol_id`, `nombre`, `email`, `foto_perfil`, `password_hash`, `estado`, `last_activity`, `created_at`, `updated_at`, `deleted_at`, `ver_precios`) VALUES ('11', '2', 'Arleis', 'arelis', NULL, '$2y$10$9oY0qOtOM8bWs3qawBQ46.Woa6GXMZMEtrfj6EbNvHBbhM6YfsCcq', '1', '2026-03-04 18:21:18', '2026-03-04 14:10:38', '2026-03-04 18:21:18', NULL, '1');
INSERT IGNORE INTO `usuarios` (`id`, `rol_id`, `nombre`, `email`, `foto_perfil`, `password_hash`, `estado`, `last_activity`, `created_at`, `updated_at`, `deleted_at`, `ver_precios`) VALUES ('12', '1', 'Spenzer', 'spenzer', NULL, '$2y$10$NSiK3h771J4cVdZTLU2upOZGRNpJPLHRng449hTWdIEpps.0gBnDq', '1', NULL, '2026-03-04 17:55:39', '2026-03-04 17:55:39', NULL, '1');

DROP TABLE IF EXISTS `workflow_transiciones`;
CREATE TABLE `workflow_transiciones` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `area_origen_id` int(10) unsigned NOT NULL,
  `area_destino_id` int(10) unsigned NOT NULL,
  `es_retroceso` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
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
