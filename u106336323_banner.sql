-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 07-03-2026 a las 00:01:20
-- Versión del servidor: 11.8.3-MariaDB-log
-- Versión de PHP: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `u106336323_banner`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `archivos`
--

CREATE TABLE `archivos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `entidad_tipo` varchar(50) NOT NULL,
  `entidad_id` int(10) UNSIGNED NOT NULL,
  `nombre_archivo` varchar(255) NOT NULL,
  `ruta_almacenamiento` varchar(500) NOT NULL,
  `tipo_mime` varchar(50) DEFAULT NULL,
  `subido_por` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `archivos`
--

INSERT INTO `archivos` (`id`, `entidad_tipo`, `entidad_id`, `nombre_archivo`, `ruta_almacenamiento`, `tipo_mime`, `subido_por`, `created_at`, `deleted_at`) VALUES
(1, 'pedido', 7, 'LOGOMISIONJUVENIL.rar', 'recep_7_1772659298_0.rar', 'application/x-compressed', 7, '2026-03-04 21:21:38', NULL),
(2, 'pedido', 15, 'Documentosintitulo.docx', 'recep_15_1772718272_0.docx', 'application/vnd.openxmlformats-officedocument.word', 1, '2026-03-05 13:44:32', NULL),
(3, 'pedido', 25, 'MOCKUPUNIFORMES_FIINAL.jpg', 'recep_25_1772728526_0.jpg', 'image/jpeg', 10, '2026-03-05 16:35:26', NULL),
(4, 'pedido', 34, 'MOCKUPUNIFORMES2.jpg', 'recep_34_1772731885_0.jpg', 'image/jpeg', 10, '2026-03-05 17:31:25', NULL),
(5, 'pedido', 39, 'KeinerMiranda.pdf', 'recep_39_1772737484_0.pdf', 'application/pdf', 1, '2026-03-05 19:04:44', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `areas`
--

CREATE TABLE `areas` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `icono` mediumtext DEFAULT NULL,
  `sla_horas` int(10) UNSIGNED DEFAULT 24,
  `estado` tinyint(1) DEFAULT 1,
  `orden` int(10) UNSIGNED DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `areas`
--

INSERT INTO `areas` (`id`, `nombre`, `descripcion`, `icono`, `sla_horas`, `estado`, `orden`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Área de Corte', 'Estación inicial', '<svg width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><circle cx=\"6\" cy=\"6\" r=\"3\"></circle><circle cx=\"6\" cy=\"18\" r=\"3\"></circle><line x1=\"20\" y1=\"4\" x2=\"8.12\" y2=\"15.88\"></line><line x1=\"14.47\" y1=\"14.48\" x2=\"20\" y2=\"20\"></line><line x1=\"8.12\" y1=\"8.12\" x2=\"12\" y2=\"12\"></line></svg>', 24, 1, 1, '2026-02-24 01:48:33', '2026-03-05 01:28:05', NULL),
(2, 'Área de Diseño', 'Estación de confección', '<svg width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><path d=\"M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z\"></path><polyline points=\"3.27 6.96 12 12.01 20.73 6.96\"></polyline><line x1=\"12\" y1=\"22.08\" x2=\"12\" y2=\"12\"></line></svg>', 48, 1, 2, '2026-02-24 01:48:33', '2026-02-25 22:50:27', NULL),
(3, 'Empaque y verificación', 'Empaque', '<svg width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><polyline points=\"22 12 18 12 15 21 9 3 6 12 2 12\"></polyline></svg>', 12, 1, 3, '2026-02-24 01:48:33', '2026-03-04 16:30:43', NULL),
(4, 'Corte Laser', 'Acrílico y MDF', '<svg width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><rect x=\"3\" y=\"3\" width=\"18\" height=\"18\" rx=\"2\" ry=\"2\"></rect><line x1=\"3\" y1=\"9\" x2=\"21\" y2=\"9\"></line><line x1=\"9\" y1=\"21\" x2=\"9\" y2=\"9\"></line></svg>', 24, 1, 4, '2026-02-24 02:59:15', '2026-03-04 16:30:20', NULL),
(8, 'Impresion General', 'Eco, UV, Sublimación', '<svg width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><polyline points=\"6 9 6 2 18 2 18 9\"></polyline><path d=\"M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2\"></path><rect x=\"6\" y=\"14\" width=\"12\" height=\"8\"></rect></svg>', 24, 1, 5, '2026-03-04 09:11:40', '2026-03-04 16:28:28', NULL),
(9, 'Impresion DTF', '', '<svg width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><polyline points=\"21 8 21 21 3 21 3 8\"></polyline><rect x=\"1\" y=\"3\" width=\"22\" height=\"5\"></rect><line x1=\"10\" y1=\"12\" x2=\"14\" y2=\"12\"></line></svg>', 24, 1, 6, '2026-03-04 12:30:55', '2026-03-04 14:17:53', NULL),
(10, 'Diseño y Armado DTF', 'Edición y verificación DTF', '<svg width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><rect x=\"2\" y=\"3\" width=\"20\" height=\"14\" rx=\"2\" ry=\"2\"></rect><line x1=\"8\" y1=\"21\" x2=\"16\" y2=\"21\"></line><line x1=\"12\" y1=\"17\" x2=\"12\" y2=\"21\"></line></svg>', 24, 1, 7, '2026-03-04 16:27:02', '2026-03-05 01:27:59', NULL),
(11, 'Tirajes y Litografía', '', '<svg width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><path d=\"M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z\"></path><path d=\"M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z\"></path></svg>', 24, 1, 8, '2026-03-04 16:35:00', '2026-03-05 01:28:13', NULL),
(12, 'Laminación', '', '<svg width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><polyline points=\"21 8 21 21 3 21 3 8\"></polyline><rect x=\"1\" y=\"3\" width=\"22\" height=\"5\"></rect><line x1=\"10\" y1=\"12\" x2=\"14\" y2=\"12\"></line></svg>', 24, 1, 9, '2026-03-04 16:45:07', '2026-03-05 01:28:18', NULL),
(13, 'Confección', '', '🧵', 24, 1, 10, '2026-03-05 16:13:08', '2026-03-05 17:36:08', NULL),
(14, 'Acabados', '', '🎁', 24, 1, 11, '2026-03-05 16:17:53', '2026-03-05 16:17:53', NULL),
(15, 'BORDADO', '', '🪡', 24, 1, 12, '2026-03-05 17:36:00', '2026-03-05 17:36:00', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `auditoria_logs`
--

CREATE TABLE `auditoria_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `usuario_id` int(10) UNSIGNED DEFAULT NULL,
  `entidad_tipo` varchar(50) NOT NULL,
  `entidad_id` int(10) UNSIGNED NOT NULL,
  `accion` varchar(50) DEFAULT NULL,
  `descripcion_accion` varchar(255) NOT NULL,
  `data_anterior` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `data_nueva` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `auditoria_logs`
--

INSERT INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES
(9, 1, 'configuracion', 0, 'actualizar', 'Modificó configuraciones: sonido_tema', NULL, NULL, '181.128.146.90', '2026-03-04 17:00:16'),
(10, 1, 'configuracion', 0, 'actualizar', 'Modificó configuraciones: sonido_tema', NULL, NULL, '181.128.146.90', '2026-03-04 17:00:17'),
(11, 1, 'configuracion', 0, 'actualizar', 'Modificó configuraciones: sonido_tema', NULL, NULL, '181.128.146.90', '2026-03-04 17:00:19'),
(12, 1, 'configuracion', 0, 'actualizar', 'Modificó configuraciones: sonido_tema', NULL, NULL, '181.128.146.90', '2026-03-04 17:00:19'),
(13, 1, 'configuracion', 0, 'actualizar', 'Modificó configuraciones: sonido_tema', NULL, NULL, '181.128.146.90', '2026-03-04 17:00:20'),
(14, 1, 'configuracion', 0, 'actualizar', 'Modificó configuraciones: sonido_tema', NULL, NULL, '181.128.146.90', '2026-03-04 17:00:21'),
(15, 1, 'configuracion', 0, 'actualizar', 'Modificó configuraciones: sonido_tema', NULL, NULL, '181.128.146.90', '2026-03-04 17:00:24'),
(16, 1, 'configuracion', 0, 'actualizar', 'Modificó configuraciones: sonido_tema', NULL, NULL, '181.128.146.90', '2026-03-04 17:00:29'),
(20, 1, 'configuracion', 0, 'actualizar', 'Modificó configuraciones: sms_crear, sms_finalizar', NULL, NULL, '181.128.146.90', '2026-03-04 17:02:56'),
(21, 1, 'configuracion', 0, 'actualizar', 'Modificó configuraciones: sms_crear, sms_finalizar', NULL, NULL, '181.128.146.90', '2026-03-04 17:03:32'),
(22, 7, 'configuracion', 0, 'actualizar', 'Modificó configuraciones: sonido_tema', NULL, NULL, '181.128.146.90', '2026-03-04 17:08:12'),
(23, 7, 'configuracion', 0, 'actualizar', 'Modificó configuraciones: sonido_tema', NULL, NULL, '181.128.146.90', '2026-03-04 17:08:13'),
(24, 7, 'configuracion', 0, 'actualizar', 'Modificó configuraciones: sonido_tema', NULL, NULL, '181.128.146.90', '2026-03-04 17:08:14'),
(25, 7, 'configuracion', 0, 'actualizar', 'Modificó configuraciones: sonido_tema', NULL, NULL, '181.128.146.90', '2026-03-04 17:08:14'),
(26, 7, 'configuracion', 0, 'actualizar', 'Modificó configuraciones: sonido_tema', NULL, NULL, '181.128.146.90', '2026-03-04 17:08:38'),
(27, 7, 'configuracion', 0, 'actualizar', 'Modificó configuraciones: sonido_tema', NULL, NULL, '181.128.146.90', '2026-03-04 17:08:39'),
(28, 7, 'configuracion', 0, 'actualizar', 'Modificó configuraciones: sonido_tema', NULL, NULL, '181.128.146.90', '2026-03-04 17:08:40'),
(29, 7, 'configuracion', 0, 'actualizar', 'Modificó configuraciones: sonido_tema', NULL, NULL, '181.128.146.90', '2026-03-04 17:08:40'),
(30, 7, 'configuracion', 0, 'actualizar', 'Modificó configuraciones: sonido_tema', NULL, NULL, '181.128.146.90', '2026-03-04 17:08:41'),
(31, 7, 'pedido', 1, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '181.128.146.90', '2026-03-04 17:12:06'),
(32, NULL, 'pedido', 1, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '191.108.103.24', '2026-03-04 17:12:45'),
(33, 1, 'configuracion', 0, 'actualizar', 'Modificó configuraciones: sonido_tema', NULL, NULL, '181.128.146.90', '2026-03-04 17:20:32'),
(34, 1, 'configuracion', 0, 'actualizar', 'Modificó configuraciones: sonido_tema', NULL, NULL, '181.128.146.90', '2026-03-04 17:20:33'),
(35, 1, 'configuracion', 0, 'actualizar', 'Modificó configuraciones: sonido_tema', NULL, NULL, '181.128.146.90', '2026-03-04 17:20:34'),
(36, 1, 'configuracion', 0, 'actualizar', 'Modificó configuraciones: sonido_tema', NULL, NULL, '181.128.146.90', '2026-03-04 17:20:35'),
(37, 1, 'configuracion', 0, 'actualizar', 'Modificó configuraciones: sonido_tema', NULL, NULL, '181.128.146.90', '2026-03-04 17:20:35'),
(38, 1, 'configuracion', 0, 'actualizar', 'Modificó configuraciones: sonido_tema', NULL, NULL, '181.128.146.90', '2026-03-04 17:20:36'),
(39, 1, 'configuracion', 0, 'actualizar', 'Modificó configuraciones: sonido_tema', NULL, NULL, '181.128.146.90', '2026-03-04 17:20:36'),
(40, 1, 'configuracion', 0, 'actualizar', 'Modificó configuraciones: sonido_tema', NULL, NULL, '181.128.146.90', '2026-03-04 17:20:37'),
(41, 1, 'configuracion', 0, 'actualizar', 'Modificó configuraciones: sonido_tema', NULL, NULL, '181.128.146.90', '2026-03-04 17:20:37'),
(42, 1, 'pedido', 1, 'actualizar', 'Traslado libre a proceso - Movimiento manual en el Kanban.', NULL, NULL, '181.128.146.90', '2026-03-04 17:21:15'),
(43, 1, 'pedido', 1, 'actualizar', 'Traslado libre a preparado - Movimiento manual en el Kanban.', NULL, NULL, '181.128.146.90', '2026-03-04 17:21:17'),
(44, 1, 'configuracion', 0, 'actualizar', 'Modificó configuraciones: sms_crear, sms_finalizar', NULL, NULL, '181.128.146.90', '2026-03-04 17:24:20'),
(45, 1, 'usuario', 8, 'crear', 'Creó nuevo usuario', NULL, NULL, '181.128.146.90', '2026-03-04 17:33:51'),
(46, 7, 'usuario', 9, 'crear', 'Creó nuevo usuario', NULL, NULL, '181.128.146.90', '2026-03-04 17:36:40'),
(47, 7, 'pedido', 2, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '181.128.146.90', '2026-03-04 17:49:22'),
(48, 7, 'usuario', 10, 'crear', 'Creó nuevo usuario', NULL, NULL, '181.128.146.90', '2026-03-04 17:50:33'),
(49, 7, 'usuario', 10, 'actualizar', 'Admin editó usuario', NULL, NULL, '181.128.146.90', '2026-03-04 17:57:35'),
(50, 10, 'pedido', 2, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '181.128.146.90', '2026-03-04 17:59:45'),
(51, 10, 'pedido', 2, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '181.128.146.90', '2026-03-04 18:00:04'),
(52, 7, 'pedido', 2, 'actualizar', 'Actualizó datos del pedido en Recepción', NULL, NULL, '181.128.146.90', '2026-03-04 18:02:22'),
(53, 10, 'pedido', 2, 'actualizar', 'Traslado libre a proceso - Movimiento manual en el Kanban.', NULL, NULL, '181.128.146.90', '2026-03-04 18:16:40'),
(54, 10, 'pedido', 2, 'actualizar', 'Traslado libre a preparado - Movimiento manual en el Kanban.', NULL, NULL, '181.128.146.90', '2026-03-04 18:16:42'),
(55, 10, 'pedido', 2, 'actualizar', 'Traslado libre a proceso - Movimiento manual en el Kanban.', NULL, NULL, '181.128.146.90', '2026-03-04 18:45:09'),
(56, 10, 'pedido', 2, 'actualizar', 'Traslado libre a preparado - Movimiento manual en el Kanban.', NULL, NULL, '181.128.146.90', '2026-03-04 18:45:12'),
(57, 10, 'pedido', 2, 'actualizar', 'Transferido - Pedido enviado al área ID: 8', NULL, NULL, '181.128.146.90', '2026-03-04 18:45:54'),
(58, 7, 'usuario', 11, 'crear', 'Creó nuevo usuario', NULL, NULL, '191.108.103.24', '2026-03-04 19:10:38'),
(59, 11, 'pedido', 3, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '191.108.103.24', '2026-03-04 19:16:07'),
(60, 11, 'pedido', 3, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-04 19:16:43'),
(61, 11, 'pedido', 3, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.103.24', '2026-03-04 19:17:04'),
(62, 11, 'pedido', 3, 'actualizar', 'Transferido - Pedido enviado al área ID: 9', NULL, NULL, '191.108.103.24', '2026-03-04 19:17:14'),
(63, 7, 'pedido', 4, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '181.128.146.90', '2026-03-04 19:34:10'),
(64, NULL, 'pedido', 4, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '2803:1800:5016:39b0:1899:b1e3:2c18:c766', '2026-03-04 19:34:34'),
(65, 7, 'pedido', 4, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '181.128.146.90', '2026-03-04 19:35:30'),
(66, 7, 'pedido', 4, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '181.128.146.90', '2026-03-04 19:35:31'),
(67, NULL, 'pedido', 3, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '2800:e6:4010:46e:90fd:70da:621:3953', '2026-03-04 19:52:54'),
(68, 11, 'pedido', 5, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '191.108.103.24', '2026-03-04 20:48:25'),
(69, 11, 'pedido', 6, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '191.108.103.24', '2026-03-04 20:50:54'),
(70, NULL, 'pedido', 5, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '2800:484:4272:4600:401d:e4d9:6723:4800', '2026-03-04 20:53:38'),
(71, NULL, 'pedido', 6, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '191.156.250.255', '2026-03-04 21:08:24'),
(72, 7, 'pedido', 7, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '181.128.146.90', '2026-03-04 21:21:38'),
(73, NULL, 'pedido', 7, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '2a03:2880:27ff::', '2026-03-04 21:21:49'),
(74, NULL, 'pedido', 7, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '2803:1800:5018:be85:1899:b5ef:28ce:ae2d', '2026-03-04 21:25:16'),
(75, 10, 'pedido', 8, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '181.128.146.90', '2026-03-04 21:42:07'),
(76, NULL, 'pedido', 8, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '74.125.210.132', '2026-03-04 21:42:11'),
(77, 9, 'pedido', 8, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-04 21:42:59'),
(78, 9, 'pedido', 8, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.103.24', '2026-03-04 21:44:16'),
(79, 7, 'usuario', 9, 'actualizar', 'Admin editó usuario', NULL, NULL, '181.128.146.90', '2026-03-04 21:46:39'),
(80, 7, 'usuario', 10, 'actualizar', 'Admin editó usuario', NULL, NULL, '181.128.146.90', '2026-03-04 21:46:53'),
(81, 8, 'pedido', 9, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '181.128.146.90', '2026-03-04 21:46:58'),
(82, 7, 'usuario', 10, 'actualizar', 'Admin editó usuario', NULL, NULL, '181.128.146.90', '2026-03-04 21:47:09'),
(83, 7, 'usuario', 8, 'actualizar', 'Admin editó usuario', NULL, NULL, '181.128.146.90', '2026-03-04 21:47:37'),
(84, 9, 'pedido', 9, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-04 21:48:09'),
(85, 7, 'usuario', 11, 'actualizar', 'Admin editó usuario', NULL, NULL, '181.128.146.90', '2026-03-04 21:52:36'),
(86, 7, 'pedido', 1, 'eliminar', 'Eliminó/canceló pedido en Recepción', NULL, NULL, '181.128.146.90', '2026-03-04 21:55:29'),
(87, 7, 'pedido', 4, 'eliminar', 'Eliminó/canceló pedido en Recepción', NULL, NULL, '181.128.146.90', '2026-03-04 21:55:40'),
(88, 9, 'pedido', 9, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.103.24', '2026-03-04 22:05:06'),
(89, 9, 'pedido', 9, 'actualizar', 'Transferido - Pedido enviado al área ID: 3', NULL, NULL, '191.108.103.24', '2026-03-04 22:05:25'),
(90, 8, 'pedido', 9, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '181.128.146.90', '2026-03-04 22:08:39'),
(91, 8, 'pedido', 9, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '181.128.146.90', '2026-03-04 22:08:44'),
(92, 10, 'pedido', 10, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '181.128.146.90', '2026-03-04 22:13:45'),
(93, NULL, 'pedido', 10, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '2803:1800:500c:581a:b802:1ff:fe31:728f', '2026-03-04 22:14:20'),
(94, 8, 'pedido', 11, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '181.128.146.90', '2026-03-04 22:14:35'),
(95, NULL, 'pedido', 11, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '2800:e2:407f:fa67:89ad:ef3c:9a12:c303', '2026-03-04 22:15:21'),
(96, 8, 'pedido', 10, 'actualizar', 'Actualizó datos del pedido en Recepción', NULL, NULL, '181.128.146.90', '2026-03-04 22:15:23'),
(97, 9, 'pedido', 11, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-04 22:28:06'),
(98, 9, 'pedido', 10, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-04 22:28:12'),
(99, NULL, 'pedido', 10, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '2803:1800:500c:581a:b802:1ff:fe31:728f', '2026-03-04 22:33:53'),
(100, NULL, 'pedido', 11, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '2800:e2:407f:fa67:89ad:ef3c:9a12:c303', '2026-03-04 22:41:27'),
(101, 9, 'pedido', 11, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.103.24', '2026-03-04 22:47:40'),
(102, 9, 'pedido', 11, 'actualizar', 'Transferido - Pedido enviado al área ID: 12', NULL, NULL, '191.108.103.24', '2026-03-04 22:47:51'),
(103, 9, 'pedido', 11, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-04 22:48:37'),
(104, 9, 'pedido', 11, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.103.24', '2026-03-04 22:50:51'),
(105, 7, 'usuario', 12, 'crear', 'Creó nuevo usuario', NULL, NULL, '181.128.146.90', '2026-03-04 22:55:39'),
(106, 9, 'pedido', 8, 'actualizar', 'Transferido - Pedido enviado al área ID: 12', NULL, NULL, '191.108.103.24', '2026-03-04 22:58:54'),
(107, 9, 'pedido', 10, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.103.24', '2026-03-04 23:14:44'),
(108, 11, 'pedido', 12, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '191.108.103.24', '2026-03-04 23:21:19'),
(109, NULL, 'pedido', 12, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '2803:1800:5015:831a:708f:49b5:7875:3fa6', '2026-03-04 23:23:27'),
(110, 8, 'pedido', 2, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '181.128.146.90', '2026-03-04 23:25:09'),
(111, 8, 'pedido', 2, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '181.128.146.90', '2026-03-04 23:25:12'),
(112, NULL, 'pedido', 12, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '190.60.51.156', '2026-03-04 23:34:47'),
(113, NULL, 'pedido', 12, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '190.61.46.138', '2026-03-04 23:38:42'),
(114, NULL, 'pedido', 12, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '190.60.51.156', '2026-03-04 23:42:18'),
(115, NULL, 'pedido', 7, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '2803:1800:5018:be85:1899:b5ef:28ce:ae2d', '2026-03-05 00:16:41'),
(116, NULL, 'pedido', 7, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '74.125.210.133', '2026-03-05 00:16:43'),
(117, NULL, 'pedido', 7, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '74.125.210.133', '2026-03-05 00:16:43'),
(118, NULL, 'pedido', 7, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '74.125.210.131', '2026-03-05 00:16:43'),
(119, 1, 'pedido', 13, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '181.128.146.90', '2026-03-05 01:21:16'),
(120, NULL, 'pedido', 13, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '181.128.146.90', '2026-03-05 01:21:33'),
(121, NULL, 'pedido', 13, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '181.128.146.90', '2026-03-05 01:22:57'),
(122, 7, 'pedido', 14, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '181.128.146.90', '2026-03-05 02:44:01'),
(123, 7, 'pedido', 14, 'actualizar', 'Traslado libre a proceso - Movimiento manual en el Kanban.', NULL, NULL, '181.128.146.90', '2026-03-05 02:44:21'),
(124, 7, 'pedido', 14, 'actualizar', 'Traslado libre a preparado - Movimiento manual en el Kanban.', NULL, NULL, '181.128.146.90', '2026-03-05 02:44:22'),
(125, 7, 'configuracion', 0, 'actualizar', 'Modificó configuraciones: sonido_tema', NULL, NULL, '181.128.146.90', '2026-03-05 02:46:11'),
(126, 7, 'configuracion', 0, 'actualizar', 'Modificó configuraciones: sonido_tema', NULL, NULL, '181.128.146.90', '2026-03-05 02:46:12'),
(127, 7, 'configuracion', 0, 'actualizar', 'Modificó configuraciones: sonido_tema', NULL, NULL, '181.128.146.90', '2026-03-05 02:46:12'),
(128, 7, 'configuracion', 0, 'actualizar', 'Modificó configuraciones: sonido_tema', NULL, NULL, '181.128.146.90', '2026-03-05 02:46:13'),
(129, 7, 'configuracion', 0, 'actualizar', 'Modificó configuraciones: sonido_tema', NULL, NULL, '181.128.146.90', '2026-03-05 02:46:14'),
(130, 7, 'pedido', 13, 'eliminar', 'Eliminó/canceló pedido en Recepción', NULL, NULL, '181.128.146.90', '2026-03-05 02:46:27'),
(131, NULL, 'pedido', 5, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '181.51.89.61', '2026-03-05 02:57:17'),
(132, 7, 'pedido', 7, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '181.128.146.90', '2026-03-05 03:03:32'),
(133, 7, 'pedido', 7, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '181.128.146.90', '2026-03-05 03:03:35'),
(134, NULL, 'pedido', 10, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '2803:b2a0:82:ddbe:850e:617e:d5cb:57b6', '2026-03-05 03:52:59'),
(135, NULL, 'pedido', 8, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '66.102.7.201', '2026-03-05 04:07:31'),
(136, 7, 'pedido', 14, 'eliminar', 'Eliminó/canceló pedido en Recepción', NULL, NULL, '2800:484:da81:3500:b128:78a1:cc62:a7c2', '2026-03-05 04:08:55'),
(137, NULL, 'pedido', 13, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '2800:484:da81:3500:2179:a5da:80f8:d7c2', '2026-03-05 06:17:34'),
(138, 1, 'pedido', 15, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '2800:484:426c:dc00:74d9:b06e:d6b:5b', '2026-03-05 13:44:32'),
(139, 1, 'pedido', 15, 'actualizar', 'Traslado libre a proceso - Movimiento manual en el Kanban.', NULL, NULL, '2800:484:426c:dc00:74d9:b06e:d6b:5b', '2026-03-05 13:44:45'),
(140, 1, 'pedido', 15, 'actualizar', 'Traslado libre a preparado - Movimiento manual en el Kanban.', NULL, NULL, '2800:484:426c:dc00:74d9:b06e:d6b:5b', '2026-03-05 13:44:46'),
(141, 1, 'pedido', 15, 'actualizar', 'Traslado libre a proceso - Movimiento manual en el Kanban.', NULL, NULL, '2800:484:426c:dc00:74d9:b06e:d6b:5b', '2026-03-05 13:44:48'),
(142, 1, 'pedido', 15, 'actualizar', 'Traslado libre a recepcion - Movimiento manual en el Kanban.', NULL, NULL, '2800:484:426c:dc00:74d9:b06e:d6b:5b', '2026-03-05 13:44:49'),
(143, 1, 'usuario', 13, 'crear', 'Creó nuevo usuario', NULL, NULL, '2800:484:426c:dc00:74d9:b06e:d6b:5b', '2026-03-05 13:45:48'),
(144, 13, 'pedido', 15, 'actualizar', 'Traslado libre a proceso - Movimiento manual en el Kanban.', NULL, NULL, '2800:484:426c:dc00:74d9:b06e:d6b:5b', '2026-03-05 13:46:16'),
(145, 13, 'pedido', 15, 'actualizar', 'Traslado libre a preparado - Movimiento manual en el Kanban.', NULL, NULL, '2800:484:426c:dc00:74d9:b06e:d6b:5b', '2026-03-05 13:46:17'),
(146, NULL, 'pedido', 15, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '2800:484:426c:dc00:5938:5686:7192:700e', '2026-03-05 13:47:40'),
(147, 1, 'pedido', 15, 'eliminar', 'Eliminó/canceló pedido en Recepción', NULL, NULL, '2800:484:426c:dc00:74d9:b06e:d6b:5b', '2026-03-05 13:48:44'),
(148, 8, 'pedido', 16, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '181.128.146.90', '2026-03-05 13:51:54'),
(149, 8, 'pedido', 17, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '181.128.146.90', '2026-03-05 13:57:53'),
(150, NULL, 'pedido', 17, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '74.125.210.131', '2026-03-05 13:58:00'),
(151, NULL, 'pedido', 17, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '2803:1800:51ce:e11:f4cf:c4ff:fe16:f3a4', '2026-03-05 13:58:43'),
(152, NULL, 'pedido', 16, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '2803:42d0:4:179c:65c9:4e16:c215:3f9', '2026-03-05 14:08:08'),
(153, 8, 'pedido', 18, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '181.128.146.90', '2026-03-05 14:08:11'),
(154, NULL, 'pedido', 18, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '191.108.77.195', '2026-03-05 14:14:12'),
(155, 9, 'pedido', 16, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-05 14:22:38'),
(156, 9, 'pedido', 17, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-05 14:22:39'),
(157, 9, 'pedido', 18, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-05 14:22:41'),
(158, 8, 'pedido', 19, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '181.128.146.90', '2026-03-05 14:31:49'),
(159, NULL, 'pedido', 19, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '74.125.210.133', '2026-03-05 14:31:52'),
(160, 9, 'pedido', 19, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-05 14:43:45'),
(161, 9, 'pedido', 19, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.103.24', '2026-03-05 14:43:48'),
(162, 9, 'pedido', 19, 'actualizar', 'Transferido - Pedido enviado al área ID: 12', NULL, NULL, '191.108.103.24', '2026-03-05 14:43:52'),
(163, 9, 'pedido', 17, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.103.24', '2026-03-05 14:43:59'),
(164, 9, 'pedido', 17, 'actualizar', 'Transferido - Pedido enviado al área ID: 12', NULL, NULL, '191.108.103.24', '2026-03-05 14:44:03'),
(165, 9, 'pedido', 16, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.103.24', '2026-03-05 15:01:11'),
(166, 9, 'pedido', 16, 'actualizar', 'Transferido - Pedido enviado al área ID: 12', NULL, NULL, '191.108.103.24', '2026-03-05 15:01:16'),
(167, 9, 'pedido', 18, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.103.24', '2026-03-05 15:01:20'),
(168, 9, 'pedido', 18, 'actualizar', 'Transferido - Pedido enviado al área ID: 12', NULL, NULL, '191.108.103.24', '2026-03-05 15:01:24'),
(169, 9, 'pedido', 19, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-05 15:01:29'),
(170, 9, 'pedido', 17, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-05 15:01:35'),
(171, 9, 'pedido', 16, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-05 15:01:41'),
(172, 9, 'pedido', 19, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.103.24', '2026-03-05 15:03:49'),
(173, 9, 'pedido', 17, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.103.24', '2026-03-05 15:04:02'),
(174, 7, 'usuario', 14, 'crear', 'Creó nuevo usuario', NULL, NULL, '191.108.91.122', '2026-03-05 15:06:09'),
(175, 8, 'pedido', 20, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '181.128.146.90', '2026-03-05 15:53:15'),
(176, 9, 'pedido', 18, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-05 15:57:00'),
(177, 9, 'pedido', 18, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.103.24', '2026-03-05 15:57:01'),
(178, 9, 'pedido', 20, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-05 15:57:10'),
(179, 9, 'pedido', 20, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.103.24', '2026-03-05 16:03:20'),
(180, 9, 'pedido', 20, 'actualizar', 'Transferido - Pedido enviado al área ID: 12', NULL, NULL, '191.108.103.24', '2026-03-05 16:03:26'),
(181, 7, 'usuario', 8, 'actualizar', 'Admin editó usuario', NULL, NULL, '181.128.146.90', '2026-03-05 16:18:16'),
(182, 7, 'usuario', 12, 'actualizar', 'Admin editó usuario', NULL, NULL, '181.128.146.90', '2026-03-05 16:19:14'),
(183, 7, 'usuario', 14, 'actualizar', 'Admin editó usuario', NULL, NULL, '181.128.146.90', '2026-03-05 16:19:34'),
(184, 8, 'pedido', 21, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '181.128.146.90', '2026-03-05 16:21:36'),
(185, NULL, 'pedido', 21, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '74.125.210.131', '2026-03-05 16:21:44'),
(186, 7, 'pedido', 22, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '181.128.146.90', '2026-03-05 16:22:50'),
(187, 8, 'pedido', 23, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '181.128.146.90', '2026-03-05 16:24:13'),
(188, NULL, 'pedido', 23, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '74.125.210.131', '2026-03-05 16:24:17'),
(189, 7, 'pedido', 24, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '181.128.146.90', '2026-03-05 16:24:22'),
(190, NULL, 'pedido', 23, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '190.61.43.109', '2026-03-05 16:24:40'),
(191, NULL, 'pedido', 23, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '190.61.43.109', '2026-03-05 16:26:01'),
(192, 10, 'pedido', 25, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '181.128.146.90', '2026-03-05 16:35:26'),
(193, 9, 'pedido', 21, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-05 16:35:58'),
(194, 9, 'pedido', 23, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-05 16:36:00'),
(195, 9, 'pedido', 20, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-05 16:43:30'),
(196, 9, 'pedido', 20, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.103.24', '2026-03-05 16:43:31'),
(197, 10, 'pedido', 25, 'actualizar', 'Actualizó datos del pedido en Recepción', NULL, NULL, '181.128.146.90', '2026-03-05 16:45:16'),
(198, 7, 'pedido', 24, 'actualizar', 'Actualizó datos del pedido en Recepción', NULL, NULL, '181.128.146.90', '2026-03-05 16:51:34'),
(199, 7, 'pedido', 24, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '181.128.146.90', '2026-03-05 16:51:48'),
(200, 7, 'pedido', 22, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '181.128.146.90', '2026-03-05 16:51:54'),
(201, 8, 'pedido', 26, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '181.128.146.90', '2026-03-05 16:55:41'),
(202, NULL, 'pedido', 26, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '74.125.210.132', '2026-03-05 16:55:45'),
(203, NULL, 'pedido', 26, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '66.102.7.199', '2026-03-05 16:55:54'),
(204, 11, 'pedido', 5, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-05 17:01:34'),
(205, 11, 'pedido', 3, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-05 17:01:37'),
(206, 11, 'pedido', 6, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-05 17:01:39'),
(207, 11, 'pedido', 12, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-05 17:01:40'),
(208, 9, 'pedido', 26, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-05 17:04:50'),
(209, 9, 'pedido', 16, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.103.24', '2026-03-05 17:05:15'),
(210, 11, 'pedido', 27, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '191.108.103.24', '2026-03-05 17:07:43'),
(211, NULL, 'pedido', 27, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '74.125.210.132', '2026-03-05 17:07:46'),
(212, 11, 'pedido', 27, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-05 17:07:58'),
(213, NULL, 'pedido', 16, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '2800:484:428e:9900:1d54:332a:d6a8:389f', '2026-03-05 17:08:54'),
(214, NULL, 'pedido', 26, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '190.61.43.109', '2026-03-05 17:11:36'),
(215, 7, 'pedido', 28, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '181.128.146.90', '2026-03-05 17:13:54'),
(216, NULL, 'pedido', 28, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '74.125.210.131', '2026-03-05 17:13:59'),
(217, 11, 'pedido', 29, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '191.108.103.24', '2026-03-05 17:15:08'),
(218, NULL, 'pedido', 29, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '74.125.210.131', '2026-03-05 17:15:20'),
(219, 11, 'pedido', 30, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '191.108.103.24', '2026-03-05 17:17:21'),
(220, 7, 'pedido', 29, 'actualizar', 'Actualizó datos del pedido en Recepción', NULL, NULL, '181.128.146.90', '2026-03-05 17:18:10'),
(221, 11, 'pedido', 31, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '191.108.103.24', '2026-03-05 17:20:07'),
(222, NULL, 'pedido', 31, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '74.125.210.132', '2026-03-05 17:20:12'),
(223, 11, 'pedido', 30, 'actualizar', 'Actualizó datos del pedido en Recepción', NULL, NULL, '191.108.103.24', '2026-03-05 17:20:54'),
(224, NULL, 'pedido', 31, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '66.102.7.199', '2026-03-05 17:20:56'),
(225, 11, 'pedido', 6, 'actualizar', 'Actualizó datos del pedido en Recepción', NULL, NULL, '191.108.103.24', '2026-03-05 17:21:15'),
(226, 11, 'pedido', 5, 'actualizar', 'Actualizó datos del pedido en Recepción', NULL, NULL, '191.108.103.24', '2026-03-05 17:21:41'),
(227, 11, 'pedido', 12, 'actualizar', 'Actualizó datos del pedido en Recepción', NULL, NULL, '191.108.103.24', '2026-03-05 17:22:10'),
(228, 10, 'pedido', 32, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '181.128.146.90', '2026-03-05 17:25:17'),
(229, 11, 'pedido', 33, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '191.108.103.24', '2026-03-05 17:26:31'),
(230, 7, 'usuario', 10, 'actualizar', 'Admin editó usuario', NULL, NULL, '181.128.146.90', '2026-03-05 17:27:36'),
(231, NULL, 'pedido', 32, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '179.19.142.124', '2026-03-05 17:28:34'),
(232, 10, 'pedido', 34, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '181.128.146.90', '2026-03-05 17:31:25'),
(233, NULL, 'pedido', 34, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '2a03:2880:11ff:51::', '2026-03-05 17:31:36'),
(234, NULL, 'pedido', 34, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '201.219.216.141', '2026-03-05 17:32:01'),
(235, NULL, 'pedido', 31, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '2800:e2:4080:7ca:89e3:d6cd:a661:4944', '2026-03-05 17:32:54'),
(236, NULL, 'pedido', 31, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '74.125.210.132', '2026-03-05 17:32:57'),
(237, NULL, 'pedido', 31, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '74.125.210.132', '2026-03-05 17:32:57'),
(238, NULL, 'pedido', 31, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '74.125.210.131', '2026-03-05 17:32:57'),
(239, 7, 'pedido', 35, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '181.128.146.90', '2026-03-05 17:34:34'),
(240, 8, 'pedido', 36, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '181.128.146.90', '2026-03-05 17:35:58'),
(241, NULL, 'pedido', 36, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '74.125.210.130', '2026-03-05 17:36:03'),
(242, 8, 'pedido', 37, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '181.128.146.90', '2026-03-05 17:37:08'),
(243, NULL, 'pedido', 36, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '2803:1800:5019:54b9:1899:fbd7:f34c:cdbf', '2026-03-05 17:37:15'),
(244, NULL, 'pedido', 37, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '74.125.210.132', '2026-03-05 17:37:21'),
(245, NULL, 'pedido', 37, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '181.78.83.202', '2026-03-05 17:37:43'),
(246, NULL, 'pedido', 37, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '74.125.210.131', '2026-03-05 17:37:45'),
(247, NULL, 'pedido', 37, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '74.125.210.132', '2026-03-05 17:37:45'),
(248, NULL, 'pedido', 37, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '74.125.210.130', '2026-03-05 17:37:46'),
(249, 8, 'pedido', 38, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '181.128.146.90', '2026-03-05 17:43:58'),
(250, NULL, 'pedido', 38, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '74.125.210.133', '2026-03-05 17:44:07'),
(251, NULL, 'pedido', 16, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '2800:484:428e:9900:1d54:332a:d6a8:389f', '2026-03-05 17:51:41'),
(252, NULL, 'pedido', 30, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '191.108.108.24', '2026-03-05 17:58:20'),
(253, 9, 'pedido', 23, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '2800:e6:4010:35c1:de8:7b1c:67f4:d65a', '2026-03-05 18:13:03'),
(254, 9, 'pedido', 26, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '2800:e6:4010:35c1:de8:7b1c:67f4:d65a', '2026-03-05 18:13:06'),
(255, 9, 'pedido', 23, 'actualizar', 'Transferido - Pedido enviado al área ID: 12', NULL, NULL, '2800:e6:4010:35c1:de8:7b1c:67f4:d65a', '2026-03-05 18:13:12'),
(256, 9, 'pedido', 26, 'actualizar', 'Transferido - Pedido enviado al área ID: 12', NULL, NULL, '2800:e6:4010:35c1:de8:7b1c:67f4:d65a', '2026-03-05 18:13:18'),
(257, 7, 'pedido', 35, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '181.128.146.90', '2026-03-05 18:28:27'),
(258, NULL, 'pedido', 6, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '190.61.46.45', '2026-03-05 18:31:09'),
(259, NULL, 'pedido', 32, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '179.19.142.124', '2026-03-05 18:40:56'),
(260, NULL, 'pedido', 1, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '179.19.56.1', '2026-03-05 18:46:54'),
(261, NULL, 'pedido', 1, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '66.249.83.101', '2026-03-05 18:46:56'),
(262, NULL, 'pedido', 1, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '66.249.83.100', '2026-03-05 18:46:56'),
(263, NULL, 'pedido', 1, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '66.249.83.100', '2026-03-05 18:46:56'),
(264, 9, 'pedido', 38, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-05 18:57:08'),
(265, 9, 'pedido', 37, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-05 18:57:14'),
(266, 9, 'pedido', 36, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-05 18:57:15'),
(267, 9, 'pedido', 32, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-05 18:57:17'),
(268, 10, 'pedido', 28, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '181.128.146.90', '2026-03-05 18:58:23'),
(269, 10, 'pedido', 28, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '181.128.146.90', '2026-03-05 18:59:34'),
(270, 10, 'pedido', 28, 'actualizar', 'Transferido - Pedido enviado al área ID: 8', NULL, NULL, '181.128.146.90', '2026-03-05 18:59:45'),
(271, 9, 'pedido', 38, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.103.24', '2026-03-05 19:03:15'),
(272, 9, 'pedido', 38, 'actualizar', 'Transferido - Pedido enviado al área ID: 12', NULL, NULL, '191.108.103.24', '2026-03-05 19:03:23'),
(273, 9, 'pedido', 28, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-05 19:03:44'),
(274, 1, 'pedido', 39, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '181.128.146.90', '2026-03-05 19:04:44'),
(275, 1, 'pedido', 39, 'actualizar', 'Traslado libre a preparado - Movimiento manual en el Kanban.', NULL, NULL, '181.128.146.90', '2026-03-05 19:06:29'),
(276, 1, 'pedido', 39, 'actualizar', 'Transferido - Pedido enviado al área ID: 15', NULL, NULL, '181.128.146.90', '2026-03-05 19:06:32'),
(277, 8, 'pedido', 40, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '181.128.146.90', '2026-03-05 19:18:45'),
(278, NULL, 'pedido', 40, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '74.125.210.132', '2026-03-05 19:18:51'),
(279, 1, 'pedido', 39, 'eliminar', 'Eliminó/canceló pedido en Recepción', NULL, NULL, '2800:484:426c:dc00:b156:3771:5e91:3e2b', '2026-03-05 19:23:39'),
(280, 9, 'pedido', 40, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-05 19:35:00'),
(281, NULL, 'pedido', 6, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '191.156.246.161', '2026-03-05 19:36:25'),
(282, NULL, 'pedido', 19, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '66.102.7.201', '2026-03-05 19:44:10'),
(283, 9, 'pedido', 21, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.103.24', '2026-03-05 19:46:59'),
(284, NULL, 'pedido', 21, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '2800:e6:4000:13a8:546:fb95:67df:48b5', '2026-03-05 19:48:27'),
(285, NULL, 'pedido', 37, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '191.156.245.223', '2026-03-05 19:55:31'),
(286, NULL, 'pedido', 37, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '74.125.210.132', '2026-03-05 19:55:35'),
(287, NULL, 'pedido', 37, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '74.125.210.133', '2026-03-05 19:55:35'),
(288, 10, 'pedido', 25, 'actualizar', 'Actualizó datos del pedido en Recepción', NULL, NULL, '181.128.146.90', '2026-03-05 19:59:54'),
(289, 10, 'pedido', 25, 'actualizar', 'Actualizó datos del pedido en Recepción', NULL, NULL, '181.128.146.90', '2026-03-05 20:00:37'),
(290, 7, 'usuario', 10, 'actualizar', 'Admin editó usuario', NULL, NULL, '181.128.146.90', '2026-03-05 20:12:14'),
(291, 7, 'usuario', 10, 'actualizar', 'Admin editó usuario', NULL, NULL, '181.128.146.90', '2026-03-05 20:12:31'),
(292, 7, 'pedido', 35, 'actualizar', 'Actualizó datos del pedido en Recepción', NULL, NULL, '181.128.146.90', '2026-03-05 20:14:52'),
(293, 8, 'pedido', 41, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '181.128.146.90', '2026-03-05 20:15:07'),
(294, NULL, 'pedido', 41, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '74.125.210.131', '2026-03-05 20:15:19'),
(295, NULL, 'pedido', 33, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '181.51.89.108', '2026-03-05 20:15:25'),
(296, 7, 'pedido', 42, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '181.128.146.90', '2026-03-05 20:15:38'),
(297, 7, 'pedido', 42, 'actualizar', 'Actualizó datos del pedido en Recepción', NULL, NULL, '181.128.146.90', '2026-03-05 20:16:15'),
(298, NULL, 'pedido', 41, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '190.61.43.109', '2026-03-05 20:16:32'),
(299, 7, 'pedido', 42, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '181.128.146.90', '2026-03-05 20:16:45'),
(300, 7, 'pedido', 35, 'actualizar', 'Actualizó datos del pedido en Recepción', NULL, NULL, '181.128.146.90', '2026-03-05 20:18:47'),
(301, NULL, 'pedido', 34, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '201.219.216.141', '2026-03-05 20:18:52'),
(302, 7, 'pedido', 42, 'actualizar', 'Actualizó datos del pedido en Recepción', NULL, NULL, '181.128.146.90', '2026-03-05 20:19:33'),
(303, 7, 'pedido', 35, 'actualizar', 'Actualizó datos del pedido en Recepción', NULL, NULL, '181.128.146.90', '2026-03-05 20:19:54'),
(304, 8, 'pedido', 43, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '181.128.146.90', '2026-03-05 20:32:42'),
(305, 9, 'pedido', 36, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.103.24', '2026-03-05 20:36:45'),
(306, 9, 'pedido', 36, 'actualizar', 'Transferido - Pedido enviado al área ID: 1', NULL, NULL, '191.108.103.24', '2026-03-05 20:36:53'),
(307, 9, 'pedido', 36, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-05 20:37:00'),
(308, 9, 'pedido', 26, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-05 20:40:28'),
(309, 9, 'pedido', 26, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.103.24', '2026-03-05 20:40:30'),
(310, 9, 'pedido', 23, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-05 20:40:35'),
(311, 9, 'pedido', 38, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-05 20:40:36'),
(312, 9, 'pedido', 23, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.103.24', '2026-03-05 20:40:42'),
(313, 9, 'pedido', 38, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.103.24', '2026-03-05 20:41:05'),
(314, NULL, 'pedido', 26, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '190.61.43.109', '2026-03-05 20:41:31'),
(315, 7, 'pedido', 44, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '181.128.146.90', '2026-03-05 20:46:38'),
(316, 7, 'pedido', 45, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '181.128.146.90', '2026-03-05 20:47:26'),
(317, 8, 'pedido', 46, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '181.128.146.90', '2026-03-05 20:48:08'),
(318, 9, 'pedido', 41, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-05 20:50:04'),
(319, 9, 'pedido', 46, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-05 20:50:05'),
(320, 9, 'pedido', 43, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-05 20:50:07'),
(321, 9, 'pedido', 37, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.103.24', '2026-03-05 20:53:07'),
(322, 9, 'pedido', 37, 'actualizar', 'Transferido - Pedido enviado al área ID: 1', NULL, NULL, '191.108.103.24', '2026-03-05 20:53:11'),
(323, NULL, 'pedido', 32, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '179.19.7.135', '2026-03-05 20:55:46'),
(324, 10, 'pedido', 47, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '181.128.146.90', '2026-03-05 20:57:47'),
(325, NULL, 'pedido', 47, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '74.125.210.133', '2026-03-05 20:57:52'),
(326, 11, 'pedido', 48, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '191.108.103.24', '2026-03-05 20:58:49'),
(327, NULL, 'pedido', 48, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '74.125.210.133', '2026-03-05 20:58:54'),
(328, 11, 'pedido', 49, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '191.108.103.24', '2026-03-05 21:01:50'),
(329, NULL, 'pedido', 49, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '186.169.147.45', '2026-03-05 21:02:05'),
(330, 11, 'pedido', 5, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.103.24', '2026-03-05 21:03:08'),
(331, 11, 'pedido', 48, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-05 21:03:48'),
(332, NULL, 'pedido', 32, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '179.19.7.135', '2026-03-05 21:08:47'),
(333, 8, 'pedido', 41, 'actualizar', 'Actualizó datos del pedido en Recepción', NULL, NULL, '181.128.146.90', '2026-03-05 21:09:28'),
(334, 8, 'pedido', 50, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '181.128.146.90', '2026-03-05 21:14:50'),
(335, NULL, 'pedido', 47, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '2803:1800:5010:c2b4:536e:4f24:df3b:e954', '2026-03-05 21:15:38'),
(336, NULL, 'pedido', 47, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '66.249.83.99', '2026-03-05 21:15:41'),
(337, NULL, 'pedido', 47, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '66.249.83.136', '2026-03-05 21:15:41'),
(338, NULL, 'pedido', 47, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '66.249.83.136', '2026-03-05 21:15:41'),
(339, 9, 'pedido', 41, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.103.24', '2026-03-05 21:20:52'),
(340, 11, 'pedido', 6, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.103.24', '2026-03-05 21:20:56'),
(341, 7, 'pedido', 40, 'actualizar', 'Actualizó datos del pedido en Recepción', NULL, NULL, '181.128.146.90', '2026-03-05 21:20:58'),
(342, 9, 'pedido', 41, 'actualizar', 'Transferido - Pedido enviado al área ID: 14', NULL, NULL, '191.108.103.24', '2026-03-05 21:21:04'),
(343, 11, 'pedido', 48, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-05 21:21:13'),
(344, 11, 'pedido', 49, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-05 21:21:19'),
(345, 11, 'pedido', 29, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-05 21:21:41'),
(346, 11, 'pedido', 30, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-05 21:21:44'),
(347, 11, 'pedido', 31, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-05 21:21:47'),
(348, 11, 'pedido', 33, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-05 21:21:49'),
(349, 11, 'pedido', 3, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-05 21:21:51'),
(350, 11, 'pedido', 51, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '191.108.103.24', '2026-03-05 21:24:20'),
(351, NULL, 'pedido', 51, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '2800:e6:4010:f771:4531:7a3e:9496:cedc', '2026-03-05 21:24:49'),
(352, 11, 'pedido', 48, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.103.24', '2026-03-05 21:25:51'),
(353, NULL, 'pedido', 47, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '191.95.20.208', '2026-03-05 21:25:53'),
(354, 11, 'pedido', 30, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.103.24', '2026-03-05 21:25:57'),
(355, NULL, 'pedido', 51, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '74.125.210.132', '2026-03-05 21:26:00'),
(356, 11, 'pedido', 3, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.103.24', '2026-03-05 21:26:01'),
(357, NULL, 'pedido', 3, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '2800:e6:4010:f654:c18:741b:45b1:12ff', '2026-03-05 21:27:13'),
(358, 9, 'pedido', 47, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-05 21:28:45'),
(359, 9, 'pedido', 50, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-05 21:28:47'),
(360, 9, 'pedido', 32, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.103.24', '2026-03-05 21:29:40'),
(361, 9, 'pedido', 32, 'actualizar', 'Transferido - Pedido enviado al área ID: 12', NULL, NULL, '191.108.103.24', '2026-03-05 21:29:44'),
(362, NULL, 'pedido', 33, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '181.51.89.108', '2026-03-05 21:39:29'),
(363, NULL, 'pedido', 37, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '66.102.7.200', '2026-03-05 21:40:25'),
(364, 9, 'pedido', 36, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.103.24', '2026-03-05 21:50:21'),
(365, NULL, 'pedido', 36, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '2803:1800:5019:54b9:1899:fbd7:f34c:cdbf', '2026-03-05 21:50:51');
INSERT INTO `auditoria_logs` (`id`, `usuario_id`, `entidad_tipo`, `entidad_id`, `accion`, `descripcion_accion`, `data_anterior`, `data_nueva`, `ip_address`, `created_at`) VALUES
(366, 9, 'pedido', 37, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-05 21:51:01'),
(367, 11, 'pedido', 27, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.103.24', '2026-03-05 21:51:43'),
(368, 9, 'pedido', 40, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.103.24', '2026-03-05 21:53:54'),
(369, 11, 'pedido', 51, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-05 21:56:46'),
(370, 9, 'pedido', 43, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.103.24', '2026-03-05 22:02:04'),
(371, 9, 'pedido', 43, 'actualizar', 'Transferido - Pedido enviado al área ID: 14', NULL, NULL, '191.108.103.24', '2026-03-05 22:02:09'),
(372, 11, 'pedido', 29, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.103.24', '2026-03-05 22:03:23'),
(373, 8, 'pedido', 43, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '181.128.146.90', '2026-03-05 22:27:49'),
(374, 8, 'pedido', 43, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '181.128.146.90', '2026-03-05 22:27:54'),
(375, 9, 'pedido', 32, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-05 22:34:36'),
(376, 9, 'pedido', 32, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.103.24', '2026-03-05 22:34:39'),
(377, NULL, 'pedido', 32, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '2800:484:4287:e880:59eb:58bc:865a:7678', '2026-03-05 22:41:36'),
(378, 11, 'pedido', 49, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.103.24', '2026-03-05 22:44:46'),
(379, 11, 'pedido', 31, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.103.24', '2026-03-05 22:44:52'),
(380, 11, 'pedido', 51, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.103.24', '2026-03-05 22:45:01'),
(381, NULL, 'pedido', 43, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '2800:e2:c180:1377:39f4:fe7a:a586:a593', '2026-03-05 22:45:55'),
(382, NULL, 'pedido', 31, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '2800:e2:4080:7ca:713c:2a25:54c8:52aa', '2026-03-05 22:48:01'),
(383, NULL, 'pedido', 32, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '2800:484:4287:e880:59eb:58bc:865a:7678', '2026-03-05 22:56:26'),
(384, NULL, 'pedido', 40, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '191.108.120.212', '2026-03-05 23:10:05'),
(385, 11, 'pedido', 33, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.103.24', '2026-03-05 23:33:55'),
(386, 11, 'pedido', 52, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '191.108.103.24', '2026-03-05 23:35:04'),
(387, 11, 'pedido', 52, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-05 23:35:20'),
(388, NULL, 'pedido', 47, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '66.102.6.134', '2026-03-05 23:38:44'),
(389, NULL, 'pedido', 52, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '191.156.225.10', '2026-03-05 23:53:55'),
(390, 11, 'pedido', 52, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.103.24', '2026-03-05 23:58:15'),
(391, 9, 'pedido', 46, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.103.24', '2026-03-06 00:03:06'),
(392, 9, 'pedido', 46, 'actualizar', 'Transferido - Pedido enviado al área ID: 12', NULL, NULL, '191.108.103.24', '2026-03-06 00:03:10'),
(393, 9, 'pedido', 50, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.103.24', '2026-03-06 00:03:15'),
(394, 9, 'pedido', 28, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.103.24', '2026-03-06 00:17:00'),
(395, 9, 'pedido', 28, 'actualizar', 'Transferido - Pedido enviado al área ID: 12', NULL, NULL, '191.108.103.24', '2026-03-06 00:17:16'),
(396, 11, 'pedido', 53, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '191.108.103.24', '2026-03-06 00:27:45'),
(397, NULL, 'pedido', 53, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '74.125.210.132', '2026-03-06 00:27:48'),
(398, NULL, 'pedido', 53, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '2a03:2880:13ff::', '2026-03-06 00:27:58'),
(399, 11, 'pedido', 53, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-06 00:41:20'),
(400, NULL, 'pedido', 15, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '2800:484:da81:3500:d4b:eec8:5ed0:2307', '2026-03-06 03:27:44'),
(401, NULL, 'pedido', 17, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '66.102.7.200', '2026-03-06 07:07:21'),
(402, NULL, 'pedido', 38, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '66.102.6.134', '2026-03-06 07:37:03'),
(403, NULL, 'pedido', 33, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '181.51.89.108', '2026-03-06 11:27:11'),
(404, NULL, 'pedido', 41, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '66.102.6.134', '2026-03-06 12:02:53'),
(405, NULL, 'pedido', 49, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '143.105.99.193', '2026-03-06 12:28:46'),
(406, NULL, 'pedido', 13, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '2800:484:426c:dc00:f451:23a8:5310:b00c', '2026-03-06 13:28:25'),
(407, 8, 'pedido', 54, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '181.128.146.90', '2026-03-06 13:50:04'),
(408, NULL, 'pedido', 54, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '74.125.210.131', '2026-03-06 13:50:10'),
(409, 8, 'pedido', 41, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '181.128.146.90', '2026-03-06 13:50:30'),
(410, NULL, 'pedido', 54, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '66.102.6.135', '2026-03-06 13:50:33'),
(411, 8, 'pedido', 41, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '181.128.146.90', '2026-03-06 13:50:33'),
(412, 8, 'pedido', 46, 'actualizar', 'Actualizó datos del pedido en Recepción', NULL, NULL, '181.128.146.90', '2026-03-06 13:51:04'),
(413, 8, 'pedido', 55, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '181.128.146.90', '2026-03-06 13:58:26'),
(414, NULL, 'pedido', 55, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '74.125.210.133', '2026-03-06 13:58:46'),
(415, NULL, 'pedido', 55, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '66.102.6.128', '2026-03-06 13:59:05'),
(416, NULL, 'pedido', 55, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '2803:1800:5100:84fc:189a:136d:5386:ee4e', '2026-03-06 14:01:03'),
(417, NULL, 'pedido', 55, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '66.249.93.194', '2026-03-06 14:01:07'),
(418, NULL, 'pedido', 55, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '74.125.210.133', '2026-03-06 14:01:07'),
(419, NULL, 'pedido', 55, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '74.125.210.131', '2026-03-06 14:01:07'),
(420, NULL, 'pedido', 37, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '181.78.83.202', '2026-03-06 14:10:02'),
(421, 9, 'pedido', 46, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-06 14:11:41'),
(422, 9, 'pedido', 46, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.103.24', '2026-03-06 14:11:43'),
(423, NULL, 'pedido', 55, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '2803:1800:5100:84fc:189a:136d:5386:ee4e', '2026-03-06 14:23:38'),
(424, 11, 'pedido', 56, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '191.108.103.24', '2026-03-06 14:24:13'),
(425, NULL, 'pedido', 56, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '74.125.210.131', '2026-03-06 14:24:50'),
(426, 8, 'pedido', 57, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '181.128.146.90', '2026-03-06 14:25:58'),
(427, NULL, 'pedido', 57, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '74.125.210.132', '2026-03-06 14:26:50'),
(428, 11, 'pedido', 58, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '191.108.103.24', '2026-03-06 14:27:05'),
(429, 8, 'pedido', 59, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '181.128.146.90', '2026-03-06 14:27:30'),
(430, 8, 'pedido', 37, 'actualizar', 'Actualizó datos del pedido en Recepción', NULL, NULL, '181.128.146.90', '2026-03-06 14:28:12'),
(431, NULL, 'pedido', 59, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '74.125.210.131', '2026-03-06 14:28:38'),
(432, 11, 'pedido', 60, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '191.108.103.24', '2026-03-06 14:32:39'),
(433, 11, 'pedido', 61, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '191.108.103.24', '2026-03-06 14:34:29'),
(434, NULL, 'pedido', 61, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '74.125.210.132', '2026-03-06 14:36:19'),
(435, 11, 'pedido', 62, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '191.108.103.24', '2026-03-06 14:36:40'),
(436, NULL, 'pedido', 62, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '74.125.210.133', '2026-03-06 14:38:10'),
(437, NULL, 'pedido', 62, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '66.102.6.134', '2026-03-06 14:38:28'),
(438, NULL, 'pedido', 62, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '2803:1800:5100:84fc:189a:136d:5386:ee4e', '2026-03-06 14:39:47'),
(439, 11, 'pedido', 63, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '191.108.103.24', '2026-03-06 14:39:52'),
(440, 11, 'pedido', 12, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.103.24', '2026-03-06 14:40:30'),
(441, NULL, 'pedido', 12, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '190.61.46.138', '2026-03-06 14:40:56'),
(442, NULL, 'pedido', 63, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '191.156.241.18', '2026-03-06 14:41:30'),
(443, 11, 'pedido', 64, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '191.108.103.24', '2026-03-06 14:42:02'),
(444, NULL, 'pedido', 64, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '74.125.210.132', '2026-03-06 14:42:05'),
(445, NULL, 'pedido', 54, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '181.49.246.202', '2026-03-06 14:44:23'),
(446, 10, 'pedido', 34, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '181.128.146.90', '2026-03-06 14:46:00'),
(447, 10, 'pedido', 34, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '181.128.146.90', '2026-03-06 14:46:04'),
(448, 10, 'pedido', 34, 'actualizar', 'Transferido - Pedido enviado al área ID: 8', NULL, NULL, '181.128.146.90', '2026-03-06 14:46:31'),
(449, NULL, 'pedido', 12, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '190.61.46.138', '2026-03-06 14:46:37'),
(450, 9, 'pedido', 54, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-06 14:59:16'),
(451, 11, 'pedido', 65, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '191.108.103.24', '2026-03-06 15:03:30'),
(452, NULL, 'pedido', 58, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '179.19.188.10', '2026-03-06 15:11:05'),
(453, 11, 'pedido', 65, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-06 15:17:44'),
(454, 11, 'pedido', 56, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-06 15:18:45'),
(455, 11, 'pedido', 63, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-06 15:18:46'),
(456, 11, 'pedido', 64, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-06 15:18:49'),
(457, 11, 'pedido', 62, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-06 15:18:51'),
(458, 11, 'pedido', 60, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-06 15:18:53'),
(459, 11, 'pedido', 58, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-06 15:18:54'),
(460, 11, 'pedido', 61, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-06 15:18:56'),
(461, 9, 'pedido', 55, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-06 15:31:08'),
(462, 9, 'pedido', 57, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-06 15:31:09'),
(463, 9, 'pedido', 59, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-06 15:31:11'),
(464, 9, 'pedido', 34, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-06 15:31:12'),
(465, 8, 'pedido', 66, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '181.128.146.90', '2026-03-06 15:40:16'),
(466, NULL, 'pedido', 66, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '74.125.210.133', '2026-03-06 15:40:22'),
(467, 10, 'pedido', 25, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '181.128.146.90', '2026-03-06 15:41:16'),
(468, 8, 'pedido', 67, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '181.128.146.90', '2026-03-06 15:44:01'),
(469, NULL, 'pedido', 67, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '74.125.210.131', '2026-03-06 15:47:29'),
(470, NULL, 'pedido', 66, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '2800:e6:4000:13a8:6a3:c537:1535:1637', '2026-03-06 15:50:09'),
(471, NULL, 'pedido', 67, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '191.156.43.248', '2026-03-06 15:51:02'),
(472, NULL, 'pedido', 67, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '74.125.210.131', '2026-03-06 15:51:04'),
(473, NULL, 'pedido', 67, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '74.125.210.133', '2026-03-06 15:51:04'),
(474, NULL, 'pedido', 67, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '74.125.210.133', '2026-03-06 15:51:04'),
(475, NULL, 'pedido', 21, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '2800:e6:4000:13a8:4525:c8f2:9173:9787', '2026-03-06 16:02:18'),
(476, 11, 'pedido', 65, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.103.24', '2026-03-06 16:02:21'),
(477, 9, 'pedido', 55, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.103.24', '2026-03-06 16:03:06'),
(478, 9, 'pedido', 55, 'actualizar', 'Transferido - Pedido enviado al área ID: 14', NULL, NULL, '191.108.103.24', '2026-03-06 16:03:12'),
(479, 9, 'pedido', 67, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-06 16:03:28'),
(480, 9, 'pedido', 66, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-06 16:03:35'),
(481, 7, 'usuario', 9, 'actualizar', 'Admin editó usuario', NULL, NULL, '181.128.146.90', '2026-03-06 16:08:20'),
(482, 7, 'pedido', 24, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '181.128.146.90', '2026-03-06 16:08:42'),
(483, 7, 'pedido', 22, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '181.128.146.90', '2026-03-06 16:08:44'),
(484, 9, 'pedido', 67, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.103.24', '2026-03-06 16:11:13'),
(485, 9, 'pedido', 67, 'actualizar', 'Transferido - Pedido enviado al área ID: 14', NULL, NULL, '191.108.103.24', '2026-03-06 16:11:18'),
(486, 11, 'pedido', 68, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '191.108.103.24', '2026-03-06 16:11:22'),
(487, NULL, 'pedido', 68, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '74.125.210.131', '2026-03-06 16:11:26'),
(488, 9, 'pedido', 67, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-06 16:11:43'),
(489, 11, 'pedido', 64, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.103.24', '2026-03-06 16:11:46'),
(490, 9, 'pedido', 55, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-06 16:11:47'),
(491, 11, 'pedido', 63, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.103.24', '2026-03-06 16:11:51'),
(492, 11, 'pedido', 58, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.103.24', '2026-03-06 16:11:53'),
(493, 11, 'pedido', 60, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.103.24', '2026-03-06 16:11:57'),
(494, 8, 'pedido', 69, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '181.128.146.90', '2026-03-06 16:11:58'),
(495, 11, 'pedido', 56, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.103.24', '2026-03-06 16:12:01'),
(496, NULL, 'pedido', 69, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '191.156.240.103', '2026-03-06 16:12:20'),
(497, NULL, 'pedido', 67, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '191.156.32.93', '2026-03-06 16:13:04'),
(498, 8, 'pedido', 70, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '181.128.146.90', '2026-03-06 16:14:07'),
(499, NULL, 'pedido', 68, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '2800:e6:4010:4d22:8922:fe67:ffa4:feaa', '2026-03-06 16:22:27'),
(500, NULL, 'pedido', 57, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '2800:e2:c100:388:5f7d:d5d5:fc68:871d', '2026-03-06 16:23:00'),
(501, NULL, 'pedido', 63, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '190.61.46.45', '2026-03-06 16:24:50'),
(502, 9, 'pedido', 67, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.103.24', '2026-03-06 16:24:51'),
(503, 9, 'pedido', 55, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.103.24', '2026-03-06 16:24:53'),
(504, 11, 'pedido', 68, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-06 16:31:19'),
(505, 11, 'pedido', 62, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.103.24', '2026-03-06 16:31:24'),
(506, 9, 'pedido', 69, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-06 16:33:18'),
(507, 9, 'pedido', 70, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-06 16:33:25'),
(508, 9, 'pedido', 57, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.103.24', '2026-03-06 16:39:40'),
(509, 9, 'pedido', 66, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.103.24', '2026-03-06 16:39:46'),
(510, 9, 'pedido', 57, 'actualizar', 'Transferido - Pedido enviado al área ID: 12', NULL, NULL, '191.108.103.24', '2026-03-06 16:42:58'),
(511, 9, 'pedido', 66, 'actualizar', 'Transferido - Pedido enviado al área ID: 12', NULL, NULL, '191.108.103.24', '2026-03-06 16:43:01'),
(512, 11, 'pedido', 61, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.103.24', '2026-03-06 17:06:20'),
(513, 11, 'pedido', 68, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.103.24', '2026-03-06 17:06:28'),
(514, 11, 'pedido', 55, 'actualizar', 'Actualizó datos del pedido en Recepción', NULL, NULL, '191.108.103.24', '2026-03-06 17:09:23'),
(515, 11, 'pedido', 61, 'actualizar', 'Actualizó datos del pedido en Recepción', NULL, NULL, '191.108.103.24', '2026-03-06 17:09:36'),
(516, 11, 'pedido', 60, 'actualizar', 'Actualizó datos del pedido en Recepción', NULL, NULL, '191.108.103.24', '2026-03-06 17:10:42'),
(517, 11, 'pedido', 63, 'actualizar', 'Actualizó datos del pedido en Recepción', NULL, NULL, '191.108.103.24', '2026-03-06 17:11:02'),
(518, 11, 'pedido', 53, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.103.24', '2026-03-06 17:15:03'),
(519, NULL, 'pedido', 53, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '2a03:2880:27ff:5::', '2026-03-06 17:15:29'),
(520, 11, 'pedido', 71, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '191.108.103.24', '2026-03-06 17:23:18'),
(521, 11, 'pedido', 52, 'actualizar', 'Actualizó datos del pedido en Recepción', NULL, NULL, '191.108.103.24', '2026-03-06 17:31:34'),
(522, NULL, 'pedido', 71, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '74.125.210.133', '2026-03-06 17:38:47'),
(523, NULL, 'pedido', 71, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '45.238.146.34', '2026-03-06 17:45:49'),
(524, 11, 'pedido', 72, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '191.108.103.24', '2026-03-06 17:48:47'),
(525, NULL, 'pedido', 72, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '74.125.210.132', '2026-03-06 17:48:56'),
(526, NULL, 'pedido', 66, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '190.250.129.85', '2026-03-06 18:51:43'),
(527, NULL, 'pedido', 68, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '66.102.7.199', '2026-03-06 18:59:52'),
(528, 11, 'pedido', 73, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '191.108.103.24', '2026-03-06 19:18:28'),
(529, NULL, 'pedido', 73, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '2803:b2a0:82:fb4:11c9:fdbd:8409:38e4', '2026-03-06 19:18:59'),
(530, 8, 'pedido', 74, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '181.128.146.90', '2026-03-06 19:30:56'),
(531, NULL, 'pedido', 74, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '74.125.210.132', '2026-03-06 19:31:15'),
(532, NULL, 'pedido', 56, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '190.60.51.156', '2026-03-06 19:31:17'),
(533, 9, 'pedido', 74, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '181.128.146.90', '2026-03-06 19:37:23'),
(534, 9, 'pedido', 69, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '181.128.146.90', '2026-03-06 19:37:44'),
(535, 9, 'pedido', 70, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '181.128.146.90', '2026-03-06 19:38:03'),
(536, 9, 'pedido', 70, 'actualizar', 'Transferido - Pedido enviado al área ID: 1', NULL, NULL, '181.128.146.90', '2026-03-06 19:38:07'),
(537, 11, 'pedido', 72, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-06 19:49:27'),
(538, 11, 'pedido', 75, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '191.108.103.24', '2026-03-06 19:51:24'),
(539, 7, 'pedido', 70, 'actualizar', 'Actualizó datos del pedido en Recepción', NULL, NULL, '191.108.103.24', '2026-03-06 19:52:09'),
(540, 8, 'pedido', 76, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '181.128.146.90', '2026-03-06 19:52:50'),
(541, 11, 'pedido', 75, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-06 19:53:14'),
(542, 11, 'pedido', 71, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-06 19:53:18'),
(543, 11, 'pedido', 73, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.103.24', '2026-03-06 19:53:20'),
(544, 8, 'pedido', 22, 'actualizar', 'Actualizó datos del pedido en Recepción', NULL, NULL, '181.128.146.90', '2026-03-06 19:53:40'),
(545, NULL, 'pedido', 75, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '181.78.73.106', '2026-03-06 19:54:19'),
(546, 8, 'pedido', 11, 'actualizar', 'Actualizó datos del pedido en Recepción', NULL, NULL, '181.128.146.90', '2026-03-06 19:55:53'),
(547, 7, 'pedido', 55, 'actualizar', 'Actualizó datos del pedido en Recepción', NULL, NULL, '191.108.103.24', '2026-03-06 19:56:11'),
(548, 11, 'pedido', 40, 'actualizar', 'Actualizó datos del pedido en Recepción', NULL, NULL, '191.108.103.24', '2026-03-06 19:56:31'),
(549, 8, 'pedido', 24, 'actualizar', 'Actualizó datos del pedido en Recepción', NULL, NULL, '181.128.146.90', '2026-03-06 19:56:48'),
(550, 11, 'pedido', 33, 'actualizar', 'Actualizó datos del pedido en Recepción', NULL, NULL, '191.108.103.24', '2026-03-06 19:57:02'),
(551, 7, 'pedido', 68, 'actualizar', 'Actualizó datos del pedido en Recepción', NULL, NULL, '191.108.103.24', '2026-03-06 19:57:05'),
(552, 8, 'pedido', 36, 'actualizar', 'Actualizó datos del pedido en Recepción', NULL, NULL, '181.128.146.90', '2026-03-06 19:57:12'),
(553, 8, 'pedido', 10, 'actualizar', 'Actualizó datos del pedido en Recepción', NULL, NULL, '181.128.146.90', '2026-03-06 19:57:26'),
(554, 8, 'pedido', 9, 'actualizar', 'Actualizó datos del pedido en Recepción', NULL, NULL, '181.128.146.90', '2026-03-06 19:57:40'),
(555, 8, 'pedido', 20, 'actualizar', 'Actualizó datos del pedido en Recepción', NULL, NULL, '181.128.146.90', '2026-03-06 19:57:55'),
(556, 8, 'pedido', 21, 'actualizar', 'Actualizó datos del pedido en Recepción', NULL, NULL, '181.128.146.90', '2026-03-06 19:58:12'),
(557, 8, 'pedido', 32, 'actualizar', 'Actualizó datos del pedido en Recepción', NULL, NULL, '181.128.146.90', '2026-03-06 19:58:34'),
(558, 7, 'pedido', 12, 'eliminar', 'Eliminó/canceló pedido en Recepción', NULL, NULL, '191.108.103.24', '2026-03-06 19:59:20'),
(559, 7, 'pedido', 31, 'eliminar', 'Eliminó/canceló pedido en Recepción', NULL, NULL, '191.108.103.24', '2026-03-06 19:59:28'),
(560, 8, 'pedido', 16, 'actualizar', 'Actualizó datos del pedido en Recepción', NULL, NULL, '181.128.146.90', '2026-03-06 20:00:51'),
(561, 8, 'pedido', 62, 'actualizar', 'Actualizó datos del pedido en Recepción', NULL, NULL, '181.128.146.90', '2026-03-06 20:01:11'),
(562, 8, 'pedido', 41, 'actualizar', 'Actualizó datos del pedido en Recepción', NULL, NULL, '181.128.146.90', '2026-03-06 20:01:27'),
(563, 8, 'pedido', 43, 'actualizar', 'Actualizó datos del pedido en Recepción', NULL, NULL, '181.128.146.90', '2026-03-06 20:01:42'),
(564, 9, 'pedido', 74, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '181.128.146.90', '2026-03-06 20:02:05'),
(565, 8, 'pedido', 23, 'actualizar', 'Actualizó datos del pedido en Recepción', NULL, NULL, '181.128.146.90', '2026-03-06 20:02:12'),
(566, 9, 'pedido', 74, 'actualizar', 'Transferido - Pedido enviado al área ID: 14', NULL, NULL, '181.128.146.90', '2026-03-06 20:02:18'),
(567, 11, 'pedido', 29, 'actualizar', 'Actualizó datos del pedido en Recepción', NULL, NULL, '191.108.103.24', '2026-03-06 20:03:04'),
(568, 9, 'pedido', 76, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '181.128.146.90', '2026-03-06 20:06:34'),
(569, 9, 'pedido', 28, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '181.128.146.90', '2026-03-06 20:06:52'),
(570, 9, 'pedido', 57, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '181.128.146.90', '2026-03-06 20:06:56'),
(571, 9, 'pedido', 66, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '181.128.146.90', '2026-03-06 20:06:59'),
(572, 9, 'pedido', 57, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '181.128.146.90', '2026-03-06 20:07:02'),
(573, 9, 'pedido', 66, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '181.128.146.90', '2026-03-06 20:07:11'),
(574, 7, 'pedido', 77, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '191.108.88.47', '2026-03-06 20:10:59'),
(575, 7, 'pedido', 77, 'actualizar', 'Actualizó datos del pedido en Recepción', NULL, NULL, '191.108.88.53', '2026-03-06 20:12:53'),
(576, 9, 'pedido', 76, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '181.128.146.90', '2026-03-06 20:23:28'),
(577, 9, 'pedido', 76, 'actualizar', 'Transferido - Pedido enviado al área ID: 14', NULL, NULL, '181.128.146.90', '2026-03-06 20:23:32'),
(578, 9, 'pedido', 76, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '181.128.146.90', '2026-03-06 20:23:41'),
(579, 9, 'pedido', 76, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '181.128.146.90', '2026-03-06 20:23:43'),
(580, 7, 'pedido', 42, 'actualizar', 'Actualizó datos del pedido en Recepción', NULL, NULL, '181.128.146.90', '2026-03-06 20:29:17'),
(581, 7, 'pedido', 77, 'actualizar', 'Actualizó datos del pedido en Recepción', NULL, NULL, '181.128.146.90', '2026-03-06 20:30:18'),
(582, 7, 'pedido', 77, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '181.128.146.90', '2026-03-06 20:31:12'),
(583, 7, 'pedido', 77, 'actualizar', 'Actualizó datos del pedido en Recepción', NULL, NULL, '181.128.146.90', '2026-03-06 20:31:34'),
(584, 7, 'pedido', 42, 'actualizar', 'Actualizó datos del pedido en Recepción', NULL, NULL, '181.128.146.90', '2026-03-06 20:31:47'),
(585, 7, 'pedido', 44, 'actualizar', 'Actualizó datos del pedido en Recepción', NULL, NULL, '181.128.146.90', '2026-03-06 20:32:20'),
(586, 7, 'pedido', 35, 'actualizar', 'Actualizó datos del pedido en Recepción', NULL, NULL, '181.128.146.90', '2026-03-06 20:33:12'),
(587, 11, 'pedido', 72, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.102.128', '2026-03-06 20:33:17'),
(588, 7, 'pedido', 45, 'actualizar', 'Actualizó datos del pedido en Recepción', NULL, NULL, '181.128.146.90', '2026-03-06 20:34:00'),
(589, 7, 'pedido', 44, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '181.128.146.90', '2026-03-06 20:34:08'),
(590, 7, 'pedido', 45, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '181.128.146.90', '2026-03-06 20:34:10'),
(591, 11, 'pedido', 75, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.102.128', '2026-03-06 20:35:24'),
(592, 7, 'pedido', 78, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '181.128.146.90', '2026-03-06 20:36:05'),
(593, 7, 'pedido', 78, 'actualizar', 'Actualizó datos del pedido en Recepción', NULL, NULL, '181.128.146.90', '2026-03-06 20:37:34'),
(594, 7, 'pedido', 45, 'actualizar', 'Actualizó datos del pedido en Recepción', NULL, NULL, '181.128.146.90', '2026-03-06 20:39:11'),
(595, 11, 'pedido', 73, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.102.128', '2026-03-06 20:39:32'),
(596, 11, 'pedido', 71, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.102.128', '2026-03-06 20:39:32'),
(597, NULL, 'pedido', 73, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '2803:b2a0:82:fb4:11c9:fdbd:8409:38e4', '2026-03-06 20:40:06'),
(598, NULL, 'pedido', 73, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '2803:b2a0:82:fb4:11c9:fdbd:8409:38e4', '2026-03-06 20:49:41'),
(599, 7, 'pedido', 79, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '181.128.146.90', '2026-03-06 20:56:50'),
(600, NULL, 'pedido', 56, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '66.102.6.128', '2026-03-06 21:05:59'),
(601, 9, 'pedido', 70, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '181.128.146.90', '2026-03-06 21:30:18'),
(602, 9, 'pedido', 70, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '181.128.146.90', '2026-03-06 21:30:20'),
(603, 10, 'pedido', 80, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '181.128.146.90', '2026-03-06 21:41:31'),
(604, NULL, 'pedido', 80, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '74.125.210.131', '2026-03-06 21:41:36'),
(605, NULL, 'pedido', 80, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '170.239.205.210', '2026-03-06 21:42:12'),
(606, NULL, 'pedido', 57, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '2800:e2:c100:388:a1a4:9517:b9a0:70eb', '2026-03-06 21:44:24'),
(607, 10, 'pedido', 81, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '181.128.146.90', '2026-03-06 22:17:43'),
(608, NULL, 'pedido', 81, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '181.51.89.120', '2026-03-06 22:20:25'),
(609, NULL, 'pedido', 52, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '191.156.225.10', '2026-03-06 22:22:16'),
(610, 11, 'pedido', 82, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '191.108.102.128', '2026-03-06 22:28:58'),
(611, NULL, 'pedido', 82, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '74.125.210.133', '2026-03-06 22:29:08'),
(612, 11, 'pedido', 80, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.102.128', '2026-03-06 22:29:17'),
(613, NULL, 'pedido', 82, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '191.108.86.241', '2026-03-06 22:33:43'),
(614, NULL, 'pedido', 82, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '191.108.86.241', '2026-03-06 22:33:57'),
(615, 11, 'pedido', 83, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '191.108.102.128', '2026-03-06 22:34:53'),
(616, NULL, 'pedido', 83, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '74.125.210.131', '2026-03-06 22:34:53'),
(617, 11, 'pedido', 82, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.102.128', '2026-03-06 22:35:07'),
(618, 11, 'pedido', 83, 'actualizar', 'Inicia Proceso - Operador toma el pedido.', NULL, NULL, '191.108.102.128', '2026-03-06 22:35:09'),
(619, NULL, 'pedido', 80, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '74.125.210.133', '2026-03-06 22:46:07'),
(620, NULL, 'pedido', 80, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '74.125.210.131', '2026-03-06 22:46:07'),
(621, NULL, 'pedido', 80, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '74.125.210.133', '2026-03-06 22:46:08'),
(622, NULL, 'pedido', 80, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '170.239.205.210', '2026-03-06 22:46:22'),
(623, NULL, 'pedido', 83, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '186.85.9.36', '2026-03-06 23:04:01'),
(624, NULL, 'pedido', 29, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '74.125.210.131', '2026-03-06 23:13:08'),
(625, NULL, 'pedido', 59, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '66.102.7.200', '2026-03-06 23:17:58'),
(626, 11, 'pedido', 80, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.102.128', '2026-03-06 23:21:43'),
(627, 11, 'pedido', 82, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.102.128', '2026-03-06 23:21:45'),
(628, 11, 'pedido', 83, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '191.108.102.128', '2026-03-06 23:21:47'),
(629, 7, 'pedido', 37, 'actualizar', 'Traslado libre a preparado - Movimiento manual en el Kanban.', NULL, NULL, '181.128.146.90', '2026-03-06 23:22:02'),
(630, NULL, 'pedido', 83, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '2a03:2880:11ff:47::', '2026-03-06 23:22:50'),
(631, NULL, 'pedido', 83, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '186.85.9.36', '2026-03-06 23:27:50'),
(632, NULL, 'pedido', 47, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '179.19.148.129', '2026-03-06 23:43:11');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion`
--

CREATE TABLE `configuracion` (
  `clave` varchar(100) NOT NULL,
  `valor` mediumtext DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `configuracion`
--

INSERT INTO `configuracion` (`clave`, `valor`, `updated_at`) VALUES
('auto_backup_diario', '1', '2026-02-27 02:14:16'),
('empresa_logo', 'data:image/webp;base64,UklGRhgKAABXRUJQVlA4TAwKAAAvd8AbEJX4tm3bTmvbtvT/nzxCI8eHKgQIMS7zWmw3g+0iTAQFAAANt9W9d4hTJI2TuqmVNMWW2v1lpmLe0tm2bfzZtm3b9lbbcXI3AbXGAIFW+MqKOj+7Oi9tpSMJnJANDl35Lwfq0ozeWkkVdHNCGKuj14hUbTGPgI2RqrlX+uGN7MemhOVsr1edUSG2RJiGFbV240LbECXsa2ptygK3gx61bhUSt8NA5LraTfzNoNWxMlbjA7cibl6trL0l2oqAJ9vKWA3ainp3DbmudjWsrSjB7rKtS72QzaiILPCdEbkiFrnbUUF5SDaA/uzKbdJ4Spu9IRcOZrDOU+w+gBtR4bhzikuh3YrdjlPcw7wVdZKbYdwKbLdOsf8PvBEBcrSdYnFt6QGGFUEk56ZQ4GOfyKbXhnCSb5N7Pvp2br91oU7VLHIzAoQ5xdhrdifczaD2EdlrZlU+IVtJ7kmv2xvirQg0lX1ltgTbish8Qq1cjd7BN0Iu89pao56B22Asav0qpGyDGVfQ7keyCQYhr+BhJXwLaDRfQasRuQVhOX8NL/+/b0DJ0HoFrUrAFoRmPssVPAzZgsK1SOv62mm0LagQdK5r4FibX9w3oQpbuAFvtl3QTne1nKZLwkZc+g/AkeXfSUgDbkJVUGtOalNuRNGfZvWtMO20txsRd89PM34jAsMTQbtUN3FiRNNQU4n8FZbQBCcoAfGP8luxOxwnsSvbhED+5nqpmWGhLp1PHdq1adVy16RRg/qHOrVqflhRp3+K3YIQyv3i6NX7RLwBP8Z9oq7QbtgGyNPSV8hMNyHXT+Qp6hp9JNsAQ1mvgc3EA7y+oPb1NXr7NiHXj+nuNehQGrAF7l8Bhy2h1waiu74+pAtktYUHYPPqXh5GOSHbqJBhZRxu8q2NDNfJ16qY7SKvzRTiw4pevtJ/13YGiPcOuZKXClsEHGBtaQA/OzRwXI5Nm5fmJCj4hGxt6CLpbXPN/Uu65ZDlSkSHHzAhm/wHvGNeMowDWv8dEEcE7UIx8ScKcTPi44QmhoU61PjsrCFkr9vgKgfqVFZv/Rf0JrD2GdXIugl9VnfxboYP9s065an6GcbPm2HFP+BscE5iPR/anCMIHrSKDDFIJg0JfBEmpSSl9PqI54W+CORGLcfgL7miXr+LAnCFSDfgSx4Vt8BTGouE++eM5XWcjnmwdQZTqZP+6GRt5Jc23xxTuh8hNM51VYzszabLX+cM4nwkqGgbvdHyhXyxaPXGpgKPBPAz30MNTBxIFi3e2EANXfDzZbQ97vnm7/MkqvfB5iGuNjL0wt1+8iFApOtsfZzZkedMaIarRi1Uaeh9Bk7nExJ1XIUhiCOEZvvNgVq4RB5Td7+MnpCqEFaw9uIPBzAnpIqn5734wxmuVQUq14FarF1xQFUFKtKIWqzNcOjM7hJ7Lz4WKNAdVHdrEFVVtJq7u5GMGrUwD631UYWwhWNZf9EHVFH60Sf6xKuqBPuOGtk/tT2GViF8wHOao5e7us8aY+cd2d3s1oY2IVVmo7r70xqpwqjkOczS3W3GhJRA1d3dOl20wAirPWZHdbdHXCrArI/BTzuMN9k+NcPD+KpAC5Go7pcPRoml2b1+dffLluBVoImsfUkmJ4gmpAqq3Lb7/f4MvaoK3/PhXqWYrbtZTMGsKhInX92tTVzhetjd7YuowCqoTNWo7nY8WBH70N3tF3VVlQjfurv95TMhxc2rHrV6aLdt9zMgXeUzISdTeHvEqu5uX0hrFK25u18GTMi3P4PpAVVVwewavMYr6YzdzWFG4PAy8QPVzSSz6p1h8FFGuAFVZ2gW95/ggqBYxEINcIt9LqhDww/mDHd/u7vpi7/aYa9ZI4cnRZnU3a2ZqmZ114nqbhOqzOvuZtCzxhnWz3m2oVuFtJkAfPFG2eueLxr2nrWtQnf9LKK1L9pnuJctg06H7rOOMw6/FczRwSv8JX9pZ8XraaPqoV3DnZAqTP080YnspffC/DnLg+Ecf6ADw6kO/UEx3RscP2BLLK+jUIkqBu2PqjjbyNDzbL77u7oD7sw5XAiB6f5ge52lYA4OSCMq0GQWVHdzqPm1Sm8edq7uD+rI8MGw3Mkjtp2pTB3asiLzubtbtRmCcA5gQuq+unof/Jb3WCzgAC5k4YxVJZupu3UpDqyqCubYYOuqzBlUU9aJl8F2Y3gZUTuqu70Jf0JGcl8Hs1elkKW7WfWfC8pDd9pH410kBE9zdQ3fkkv58Es2BDWJtbv/KlwVH/+6u39IBnQDf78ZPtWIOou393ln/WDv0VO8CdGLrbtZTMU5gOCkxmno7vaT+6pC2D+wOyuW4luEDTq6u92Py1lOZjY8oChVdnfrctsOJ335QnU3h3WwVZV0bd3dSM3+vGn86O5mVFwrYLEeZ0IKYSOyl//wqHWFbilrL/5wGOdyL9UWcaqqovSYYwm7+8LrJDwvzyKP6UhxtYNhCbvLPOsyD0btPllHBatZXg5/vuaYfDGDeEJOCzBEHdsZuDrJiJwrrN1H5rkPNVb81mn7cqWSRP8GnZDj2LRmOuv+j1v2mSaTPHBCRpi8fd/3us8UXLje+y6giAyi3/e95wpfqsG7EuyhAp7yrHTJA4/ctXcXEnxCRpi8fd/3x9ylEZhPjMC6ZjSscDCD1n8a+3Lw5/OxBHh7Ph8zHXpV9fui4PBjv++Xe9P77TwH7IAuSM6XUkXECwiNVvZDhz+EHW1Vvf2F3YfAR1XFl/LY18flhBskuOpB+MSoB8FbhQirHxXACT/wwD4wCxfvHRySG17FmQ7nxfnva+Auo4o40RivwoxT0nauQLhJ+/4ubG7Q0MIoLIIEC6knATq8ni3452JBZZDQBf+WI7X1e5MlUQ5pVIZKfqJTyZQiNIHxECJLqjL8IyVOCJU0qTQBA114caNOuPQQJT/CjJUhlTZOSRPyw1u6JDEfUfCRK8lIEeRy7vSCI5N9N+b7YjyllERe1KKK0DDKt0+ReidG9IRQcZcBf4WesAT+hBYoi/QYlSTDeO90uIMxSUySGHR5oU2IsoiMDBH4jFfS3mj0xi70SOIth1PJpnlyKVk0CriVJOpLBeif0mSmD514voWjryBlMmTR/MZnQuIVb5qq129C9AFLKNGRlwzRpsh8ysIenlVVskOUsohSHFBSE5A0KnGKYfWKJN4SYSWaig5zQkS/6YU9ISI/L4UvPeKImk6IIn5tAtcXJS33IbJ30YJ/ExZfqboLVU48pEgYD92koMmmvoubkAUSdRMiLy6ZRnNPenhJo3oTKyGBYiKN1y5JoJnUwiRTmvgUIfXD6OeluOBVFUH4hLRfiibkLl5QsPDCre8wwxApGju8cBHCdxL0QbBsaUJCVBjPrzgN2OTDj4j970+8v0WF8xMZ/gdcqG54YYYLE1S8ud4FFp8GKcV99KWOCzaYd208', '2026-02-26 03:36:57'),
('empresa_nombre', 'Banner', '2026-02-27 02:30:44'),
('fondo_login', '/public/img/fondos/fondo_login_1772678763.jpg', '2026-03-05 02:46:03'),
('icon_areas', '<svg width=\"22\" height=\"22\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"#6366f1\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><circle cx=\"12\" cy=\"12\" r=\"3\"/><path d=\"M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z\"/></svg>', '2026-02-27 03:01:19'),
('icon_configuracion', '<svg width=\"22\" height=\"22\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"#6366f1\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><circle cx=\"12\" cy=\"12\" r=\"3\"/><path d=\"M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z\"/></svg>', '2026-02-27 03:01:19'),
('icon_dashboard', '<svg width=\"22\" height=\"22\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"#6366f1\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><rect x=\"3\" y=\"3\" width=\"7\" height=\"7\"/><rect x=\"14\" y=\"3\" width=\"7\" height=\"7\"/><rect x=\"14\" y=\"14\" width=\"7\" height=\"7\"/><rect x=\"3\" y=\"14\" width=\"7\" height=\"7\"/></svg>', '2026-02-27 03:01:19'),
('icon_recepcion', '<svg width=\"22\" height=\"22\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"#6366f1\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path d=\"M22 12h-4l-3 9L9 3l-3 9H2\"/></svg>', '2026-02-27 03:01:19'),
('icon_reportes', '<svg width=\"22\" height=\"22\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"#6366f1\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path d=\"M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z\"/><polyline points=\"14 2 14 8 20 8\"/><line x1=\"16\" y1=\"13\" x2=\"8\" y2=\"13\"/><line x1=\"16\" y1=\"17\" x2=\"8\" y2=\"17\"/></svg>', '2026-02-27 03:01:19'),
('icon_reportes_pedidos', '<svg width=\"22\" height=\"22\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"#6366f1\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path d=\"M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2\"/><rect x=\"8\" y=\"2\" width=\"8\" height=\"4\" rx=\"1\" ry=\"1\"/></svg>', '2026-02-27 03:01:19'),
('icon_usuarios', '<svg width=\"22\" height=\"22\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"#6366f1\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path d=\"M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2\"/><circle cx=\"9\" cy=\"7\" r=\"4\"/><path d=\"M23 21v-2a4 4 0 0 0-3-3.87\"/><path d=\"M16 3.13a4 4 0 0 1 0 7.75\"/></svg>', '2026-02-27 03:01:19'),
('mostrar_credenciales', '0', '2026-02-28 22:43:18'),
('onurix_api_id', '7784', '2026-03-02 23:54:09'),
('onurix_api_key', 'b8db96bb2ed56e7ca15fa12a17fafbce4dbeedad69a62091e53a8', '2026-03-02 23:47:46'),
('sms_crear', 'informa al cliente {nombre}, que se ha creado su pedido {numero_pedido} exitosamente, puede consultar el estado en {link_seguimiento}  Gracias por confiar en nosotros.', '2026-03-04 17:24:20'),
('sms_finalizar', 'Informa que el cliente {nombre}, tiene listo su pedido {numero_pedido}, por favor acérquese a la oficina para reclamarlo.', '2026-03-04 17:24:20'),
('sonido_tema', 'neo', '2026-03-05 02:46:14'),
('ultima_exportacion_db', '2026-03-06', '2026-03-06 16:04:37'),
('whatsapp_activo', '1', '2026-03-03 23:57:14'),
('whatsapp_phone_sender_id', '3026084603', '2026-03-03 23:57:14'),
('whatsapp_template_id', '1957715494954382', '2026-03-03 23:57:14'),
('whatsapp_template_id_finalizar', '26142719632013996', '2026-03-03 23:57:14'),
('whatsapp_var_link', 'link', '2026-03-03 23:57:14'),
('whatsapp_var_nombre', 'nombre', '2026-03-03 23:57:14');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `devoluciones`
--

CREATE TABLE `devoluciones` (
  `id` int(10) UNSIGNED NOT NULL,
  `pedido_id` int(10) UNSIGNED NOT NULL,
  `usuario_id` int(10) UNSIGNED NOT NULL,
  `motivo` text NOT NULL,
  `resolucion` text DEFAULT NULL,
  `estado` enum('abierta','en_revision','resuelta','rechazada') DEFAULT 'abierta',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimientos_pedido`
--

CREATE TABLE `movimientos_pedido` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `pedido_id` int(10) UNSIGNED NOT NULL,
  `usuario_id` int(10) UNSIGNED DEFAULT NULL,
  `area_id` int(10) UNSIGNED DEFAULT NULL,
  `accion` varchar(100) NOT NULL,
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `movimientos_pedido`
--

INSERT INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES
(1, 1, 7, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-04 17:12:04'),
(2, 1, 1, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Area Impresion', '2026-03-04 17:20:48'),
(3, 1, 1, 8, 'Traslado libre a proceso', 'Movimiento manual en el Kanban.', '2026-03-04 17:21:15'),
(4, 1, 1, 8, 'Traslado libre a preparado', 'Movimiento manual en el Kanban.', '2026-03-04 17:21:17'),
(5, 1, 1, 8, 'Completado', 'Pedido finalizado y completado', '2026-03-04 17:21:19'),
(6, 2, 7, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-04 17:49:20'),
(7, 2, 7, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Área de Diseño', '2026-03-04 17:49:35'),
(8, 2, 10, 2, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-04 17:59:45'),
(9, 2, 10, 2, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-04 18:00:04'),
(10, 2, 7, 2, 'Editado', 'Datos del pedido actualizados desde Recepción', '2026-03-04 18:02:22'),
(11, 2, 10, 2, 'Traslado libre a proceso', 'Movimiento manual en el Kanban.', '2026-03-04 18:16:40'),
(12, 2, 10, 2, 'Traslado libre a preparado', 'Movimiento manual en el Kanban.', '2026-03-04 18:16:42'),
(13, 2, 10, 2, 'Traslado libre a proceso', 'Movimiento manual en el Kanban.', '2026-03-04 18:45:09'),
(14, 2, 10, 2, 'Traslado libre a preparado', 'Movimiento manual en el Kanban.', '2026-03-04 18:45:12'),
(15, 2, 10, 2, 'Transferido', 'Pedido enviado al área ID: 8', '2026-03-04 18:45:54'),
(16, 3, 11, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-04 19:16:06'),
(17, 3, 11, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Área de Diseño', '2026-03-04 19:16:32'),
(18, 3, 11, 2, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-04 19:16:43'),
(19, 3, 11, 2, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-04 19:17:04'),
(20, 3, 11, 2, 'Transferido', 'Pedido enviado al área ID: 9', '2026-03-04 19:17:14'),
(21, 4, 7, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-04 19:34:08'),
(22, 4, 7, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Área de Diseño', '2026-03-04 19:34:59'),
(23, 4, 7, 2, 'Enviado a Área', 'Pedido enviado al área: Impresion General', '2026-03-04 19:35:17'),
(24, 4, 7, 8, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-04 19:35:30'),
(25, 4, 7, 8, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-04 19:35:31'),
(26, 4, 7, 8, 'Completado', 'Pedido finalizado y completado', '2026-03-04 19:35:37'),
(27, 5, 11, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-04 20:48:24'),
(28, 5, 11, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Impresion DTF', '2026-03-04 20:48:53'),
(29, 6, 11, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-04 20:50:53'),
(30, 7, 7, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-04 21:21:35'),
(31, 7, 7, NULL, 'Adjunto', 'Archivos adjuntados al crear: LOGOMISIONJUVENIL.rar ', '2026-03-04 21:21:38'),
(32, 7, 7, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Impresion DTF', '2026-03-04 21:23:24'),
(33, 6, 7, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Tirajes y Litografía', '2026-03-04 21:35:22'),
(34, 6, 7, 11, 'Enviado a Área', 'Pedido enviado al área: Diseño y Armado DTF', '2026-03-04 21:36:01'),
(35, 2, 7, 8, 'Enviado a Área', 'Pedido enviado al área: Tirajes y Litografía', '2026-03-04 21:36:15'),
(36, 8, 10, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-04 21:42:05'),
(37, 8, 10, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Impresion General', '2026-03-04 21:42:18'),
(38, 8, 9, 8, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-04 21:42:59'),
(39, 8, 9, 8, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-04 21:44:16'),
(40, 9, 8, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-04 21:46:55'),
(41, 9, 8, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Impresion General', '2026-03-04 21:47:23'),
(42, 9, 9, 8, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-04 21:48:09'),
(43, 1, 7, 8, 'Cancelado', 'Pedido eliminado desde Recepción', '2026-03-04 21:55:29'),
(44, 4, 7, 8, 'Cancelado', 'Pedido eliminado desde Recepción', '2026-03-04 21:55:40'),
(45, 9, 9, 8, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-04 22:05:06'),
(46, 9, 9, 8, 'Transferido', 'Pedido enviado al área ID: 3', '2026-03-04 22:05:25'),
(47, 9, 8, 3, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-04 22:08:39'),
(48, 9, 8, 3, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-04 22:08:44'),
(49, 9, 8, 3, 'Completado', 'Pedido finalizado y completado', '2026-03-04 22:08:49'),
(50, 10, 10, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-04 22:13:44'),
(51, 11, 8, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-04 22:14:34'),
(52, 10, 8, NULL, 'Editado', 'Datos del pedido actualizados desde Recepción', '2026-03-04 22:15:23'),
(53, 11, 8, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Impresion General', '2026-03-04 22:15:31'),
(54, 10, 8, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Impresion General', '2026-03-04 22:15:38'),
(55, 11, 9, 8, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-04 22:28:06'),
(56, 10, 9, 8, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-04 22:28:12'),
(57, 11, 9, 8, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-04 22:47:40'),
(58, 11, 9, 8, 'Transferido', 'Pedido enviado al área ID: 12', '2026-03-04 22:47:51'),
(59, 11, 9, 12, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-04 22:48:37'),
(60, 11, 9, 12, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-04 22:50:51'),
(61, 11, 9, 12, 'Completado', 'Pedido finalizado y completado', '2026-03-04 22:50:57'),
(62, 8, 9, 8, 'Transferido', 'Pedido enviado al área ID: 12', '2026-03-04 22:58:54'),
(63, 10, 9, 8, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-04 23:14:44'),
(64, 10, 9, 8, 'Completado', 'Pedido finalizado y completado', '2026-03-04 23:17:39'),
(65, 12, 11, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-04 23:21:18'),
(66, 2, 8, 11, 'Enviado a Área', 'Pedido enviado al área: Empaque y verificación', '2026-03-04 23:24:49'),
(67, 2, 8, 3, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-04 23:25:09'),
(68, 2, 8, 3, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-04 23:25:12'),
(69, 2, 8, 3, 'Completado', 'Pedido finalizado y completado', '2026-03-04 23:25:15'),
(70, 13, 1, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-05 01:21:13'),
(71, 14, 7, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-05 02:43:58'),
(72, 14, 7, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Impresion DTF', '2026-03-05 02:44:14'),
(73, 14, 7, 9, 'Traslado libre a proceso', 'Movimiento manual en el Kanban.', '2026-03-05 02:44:21'),
(74, 14, 7, 9, 'Traslado libre a preparado', 'Movimiento manual en el Kanban.', '2026-03-05 02:44:22'),
(75, 14, 7, 9, 'Completado', 'Pedido finalizado y completado', '2026-03-05 02:44:26'),
(76, 13, 7, NULL, 'Cancelado', 'Pedido eliminado desde Recepción', '2026-03-05 02:46:27'),
(77, 7, 7, 9, 'Enviado a Área', 'Pedido enviado al área: Impresion DTF', '2026-03-05 03:03:25'),
(78, 7, 7, 9, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-05 03:03:32'),
(79, 7, 7, 9, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-05 03:03:35'),
(80, 7, 7, 9, 'Completado', 'Pedido finalizado y completado', '2026-03-05 03:03:42'),
(81, 14, 7, 9, 'Cancelado', 'Pedido eliminado desde Recepción', '2026-03-05 04:08:55'),
(82, 15, 1, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-05 13:44:29'),
(83, 15, 1, NULL, 'Adjunto', 'Archivos adjuntados al crear: Documentosintitulo.docx ', '2026-03-05 13:44:32'),
(84, 15, 1, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Área de Corte', '2026-03-05 13:44:40'),
(85, 15, 1, 1, 'Traslado libre a proceso', 'Movimiento manual en el Kanban.', '2026-03-05 13:44:45'),
(86, 15, 1, 1, 'Traslado libre a preparado', 'Movimiento manual en el Kanban.', '2026-03-05 13:44:46'),
(87, 15, 1, 1, 'Traslado libre a proceso', 'Movimiento manual en el Kanban.', '2026-03-05 13:44:48'),
(88, 15, 1, 1, 'Traslado libre a recepcion', 'Movimiento manual en el Kanban.', '2026-03-05 13:44:49'),
(89, 15, 13, 1, 'Traslado libre a proceso', 'Movimiento manual en el Kanban.', '2026-03-05 13:46:16'),
(90, 15, 13, 1, 'Traslado libre a preparado', 'Movimiento manual en el Kanban.', '2026-03-05 13:46:17'),
(91, 15, 13, 1, 'Completado', 'Pedido finalizado y completado', '2026-03-05 13:46:20'),
(92, 15, 1, 1, 'Cancelado', 'Pedido eliminado desde Recepción', '2026-03-05 13:48:44'),
(93, 16, 8, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-05 13:51:53'),
(94, 16, 8, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Impresion General', '2026-03-05 13:52:21'),
(95, 12, 8, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Impresion DTF', '2026-03-05 13:52:39'),
(96, 17, 8, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-05 13:57:52'),
(97, 17, 8, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Impresion General', '2026-03-05 13:58:02'),
(98, 18, 8, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-05 14:08:08'),
(99, 18, 8, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Impresion General', '2026-03-05 14:08:29'),
(100, 16, 9, 8, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-05 14:22:38'),
(101, 17, 9, 8, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-05 14:22:39'),
(102, 18, 9, 8, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-05 14:22:41'),
(103, 19, 8, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-05 14:31:48'),
(104, 19, 8, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Impresion General', '2026-03-05 14:31:54'),
(105, 19, 9, 8, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-05 14:43:45'),
(106, 19, 9, 8, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-05 14:43:48'),
(107, 19, 9, 8, 'Transferido', 'Pedido enviado al área ID: 12', '2026-03-05 14:43:52'),
(108, 17, 9, 8, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-05 14:43:59'),
(109, 17, 9, 8, 'Transferido', 'Pedido enviado al área ID: 12', '2026-03-05 14:44:03'),
(110, 16, 9, 8, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-05 15:01:11'),
(111, 16, 9, 8, 'Transferido', 'Pedido enviado al área ID: 12', '2026-03-05 15:01:16'),
(112, 18, 9, 8, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-05 15:01:20'),
(113, 18, 9, 8, 'Transferido', 'Pedido enviado al área ID: 12', '2026-03-05 15:01:24'),
(114, 19, 9, 12, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-05 15:01:29'),
(115, 17, 9, 12, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-05 15:01:35'),
(116, 16, 9, 12, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-05 15:01:41'),
(117, 19, 9, 12, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-05 15:03:49'),
(118, 19, 9, 12, 'Completado', 'Pedido finalizado y completado', '2026-03-05 15:03:54'),
(119, 17, 9, 12, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-05 15:04:02'),
(120, 17, 9, 12, 'Completado', 'Pedido finalizado y completado', '2026-03-05 15:04:18'),
(121, 20, 8, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-05 15:53:12'),
(122, 20, 8, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Impresion General', '2026-03-05 15:53:21'),
(123, 18, 9, 12, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-05 15:57:00'),
(124, 18, 9, 12, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-05 15:57:01'),
(125, 18, 9, 12, 'Completado', 'Pedido finalizado y completado', '2026-03-05 15:57:04'),
(126, 20, 9, 8, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-05 15:57:10'),
(127, 20, 9, 8, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-05 16:03:20'),
(128, 20, 9, 8, 'Transferido', 'Pedido enviado al área ID: 12', '2026-03-05 16:03:26'),
(129, 21, 8, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-05 16:21:35'),
(130, 21, 8, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Impresion General', '2026-03-05 16:21:44'),
(131, 22, 7, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-05 16:22:50'),
(132, 22, 7, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Acabados', '2026-03-05 16:23:12'),
(133, 23, 8, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-05 16:24:12'),
(134, 24, 7, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-05 16:24:22'),
(135, 23, 8, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Impresion General', '2026-03-05 16:24:36'),
(136, 24, 8, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Acabados', '2026-03-05 16:24:48'),
(137, 24, 7, 14, 'Enviado a Área', 'Pedido enviado al área: Acabados', '2026-03-05 16:26:31'),
(138, 25, 10, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-05 16:35:26'),
(139, 25, 10, NULL, 'Adjunto', 'Archivos adjuntados al crear: MOCKUPUNIFORMES_FIINAL.jpg ', '2026-03-05 16:35:26'),
(140, 21, 9, 8, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-05 16:35:58'),
(141, 23, 9, 8, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-05 16:36:00'),
(142, 20, 9, 12, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-05 16:43:30'),
(143, 20, 9, 12, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-05 16:43:31'),
(144, 20, 9, 12, 'Completado', 'Pedido finalizado y completado', '2026-03-05 16:43:35'),
(145, 25, 10, NULL, 'Editado', 'Datos del pedido actualizados desde Recepción', '2026-03-05 16:45:16'),
(146, 25, 10, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Área de Diseño', '2026-03-05 16:50:05'),
(147, 24, 7, 14, 'Editado', 'Datos del pedido actualizados desde Recepción', '2026-03-05 16:51:34'),
(148, 24, 7, 14, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-05 16:51:48'),
(149, 22, 7, 14, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-05 16:51:54'),
(150, 26, 8, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-05 16:55:40'),
(151, 26, 8, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Impresion General', '2026-03-05 16:55:50'),
(152, 5, 11, 9, 'Enviado a Área', 'Pedido enviado al área: Impresion DTF', '2026-03-05 17:00:49'),
(153, 3, 11, 9, 'Enviado a Área', 'Pedido enviado al área: Impresion DTF', '2026-03-05 17:01:00'),
(154, 12, 11, 9, 'Enviado a Área', 'Pedido enviado al área: Impresion DTF', '2026-03-05 17:01:09'),
(155, 6, 11, 10, 'Enviado a Área', 'Pedido enviado al área: Impresion DTF', '2026-03-05 17:01:16'),
(156, 5, 11, 9, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-05 17:01:34'),
(157, 3, 11, 9, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-05 17:01:37'),
(158, 6, 11, 9, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-05 17:01:39'),
(159, 12, 11, 9, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-05 17:01:40'),
(160, 26, 9, 8, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-05 17:04:50'),
(161, 16, 9, 12, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-05 17:05:15'),
(162, 16, 9, 12, 'Completado', 'Pedido finalizado y completado', '2026-03-05 17:05:26'),
(163, 27, 11, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-05 17:07:41'),
(164, 27, 11, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Impresion DTF', '2026-03-05 17:07:52'),
(165, 27, 11, 9, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-05 17:07:58'),
(166, 28, 7, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-05 17:13:53'),
(167, 28, 7, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Área de Diseño', '2026-03-05 17:14:02'),
(168, 29, 11, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-05 17:15:07'),
(169, 8, 11, 12, 'Enviado a Área', 'Pedido enviado al área: Impresion DTF', '2026-03-05 17:15:23'),
(170, 30, 11, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-05 17:17:20'),
(171, 29, 11, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Impresion DTF', '2026-03-05 17:17:44'),
(172, 30, 11, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Impresion DTF', '2026-03-05 17:17:59'),
(173, 29, 7, 9, 'Editado', 'Datos del pedido actualizados desde Recepción', '2026-03-05 17:18:10'),
(174, 31, 11, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-05 17:20:06'),
(175, 31, 11, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Impresion DTF', '2026-03-05 17:20:15'),
(176, 30, 11, 9, 'Editado', 'Datos del pedido actualizados desde Recepción', '2026-03-05 17:20:54'),
(177, 6, 11, 9, 'Editado', 'Datos del pedido actualizados desde Recepción', '2026-03-05 17:21:15'),
(178, 5, 11, 9, 'Editado', 'Datos del pedido actualizados desde Recepción', '2026-03-05 17:21:41'),
(179, 12, 11, 9, 'Editado', 'Datos del pedido actualizados desde Recepción', '2026-03-05 17:22:10'),
(180, 32, 10, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-05 17:25:14'),
(181, 32, 10, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Impresion General', '2026-03-05 17:25:31'),
(182, 33, 11, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-05 17:26:30'),
(183, 34, 10, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-05 17:31:23'),
(184, 34, 10, NULL, 'Adjunto', 'Archivos adjuntados al crear: MOCKUPUNIFORMES2.jpg ', '2026-03-05 17:31:25'),
(185, 34, 10, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Área de Diseño', '2026-03-05 17:32:18'),
(186, 35, 7, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-05 17:34:34'),
(187, 35, 7, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Bordado y Confección', '2026-03-05 17:34:47'),
(188, 36, 8, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-05 17:35:57'),
(189, 36, 8, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Impresion General', '2026-03-05 17:36:06'),
(190, 37, 8, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-05 17:37:07'),
(191, 37, 8, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Impresion General', '2026-03-05 17:37:14'),
(192, 38, 8, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-05 17:43:57'),
(193, 38, 8, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Impresion General', '2026-03-05 17:44:04'),
(194, 23, 9, 8, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-05 18:13:03'),
(195, 26, 9, 8, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-05 18:13:06'),
(196, 23, 9, 8, 'Transferido', 'Pedido enviado al área ID: 12', '2026-03-05 18:13:12'),
(197, 26, 9, 8, 'Transferido', 'Pedido enviado al área ID: 12', '2026-03-05 18:13:18'),
(198, 35, 7, 13, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-05 18:28:27'),
(199, 38, 9, 8, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-05 18:57:08'),
(200, 37, 9, 8, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-05 18:57:14'),
(201, 36, 9, 8, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-05 18:57:15'),
(202, 32, 9, 8, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-05 18:57:17'),
(203, 28, 10, 2, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-05 18:58:23'),
(204, 28, 10, 2, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-05 18:59:34'),
(205, 28, 10, 2, 'Transferido', 'Pedido enviado al área ID: 8', '2026-03-05 18:59:45'),
(206, 38, 9, 8, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-05 19:03:15'),
(207, 38, 9, 8, 'Transferido', 'Pedido enviado al área ID: 12', '2026-03-05 19:03:23'),
(208, 28, 9, 8, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-05 19:03:44'),
(209, 39, 1, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-05 19:04:41'),
(210, 39, 1, NULL, 'Adjunto', 'Archivos adjuntados al crear: KeinerMiranda.pdf ', '2026-03-05 19:04:44'),
(211, 39, 1, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Área de Diseño', '2026-03-05 19:05:13'),
(212, 39, 1, 2, 'Traslado libre a preparado', 'Movimiento manual en el Kanban.', '2026-03-05 19:06:29'),
(213, 39, 1, 2, 'Transferido', 'Pedido enviado al área ID: 15', '2026-03-05 19:06:32'),
(214, 40, 8, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-05 19:18:44'),
(215, 40, 8, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Impresion General', '2026-03-05 19:18:53'),
(216, 39, 1, 15, 'Cancelado', 'Pedido eliminado desde Recepción', '2026-03-05 19:23:39'),
(217, 40, 9, 8, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-05 19:35:00'),
(218, 21, 9, 8, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-05 19:46:59'),
(219, 21, 9, 8, 'Completado', 'Pedido finalizado y completado', '2026-03-05 19:47:16'),
(220, 25, 10, 2, 'Editado', 'Datos del pedido actualizados desde Recepción', '2026-03-05 19:59:54'),
(221, 25, 10, 2, 'Editado', 'Datos del pedido actualizados desde Recepción', '2026-03-05 20:00:37'),
(222, 35, 7, 13, 'Editado', 'Datos del pedido actualizados desde Recepción', '2026-03-05 20:14:52'),
(223, 41, 8, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-05 20:15:03'),
(224, 41, 8, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Impresion General', '2026-03-05 20:15:13'),
(225, 42, 7, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-05 20:15:38'),
(226, 42, 7, NULL, 'Editado', 'Datos del pedido actualizados desde Recepción', '2026-03-05 20:16:15'),
(227, 42, 7, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Confección', '2026-03-05 20:16:23'),
(228, 42, 7, 13, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-05 20:16:45'),
(229, 35, 7, 13, 'Editado', 'Datos del pedido actualizados desde Recepción', '2026-03-05 20:18:47'),
(230, 42, 7, 13, 'Editado', 'Datos del pedido actualizados desde Recepción', '2026-03-05 20:19:33'),
(231, 35, 7, 13, 'Editado', 'Datos del pedido actualizados desde Recepción', '2026-03-05 20:19:54'),
(232, 43, 8, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-05 20:32:39'),
(233, 43, 8, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Impresion General', '2026-03-05 20:32:49'),
(234, 36, 9, 8, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-05 20:36:45'),
(235, 36, 9, 8, 'Transferido', 'Pedido enviado al área ID: 1', '2026-03-05 20:36:53'),
(236, 36, 9, 1, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-05 20:37:00'),
(237, 26, 9, 12, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-05 20:40:28'),
(238, 26, 9, 12, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-05 20:40:30'),
(239, 26, 9, 12, 'Completado', 'Pedido finalizado y completado', '2026-03-05 20:40:32'),
(240, 23, 9, 12, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-05 20:40:35'),
(241, 38, 9, 12, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-05 20:40:36'),
(242, 23, 9, 12, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-05 20:40:42'),
(243, 23, 9, 12, 'Completado', 'Pedido finalizado y completado', '2026-03-05 20:40:57'),
(244, 38, 9, 12, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-05 20:41:05'),
(245, 38, 9, 12, 'Completado', 'Pedido finalizado y completado', '2026-03-05 20:41:22'),
(246, 44, 7, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-05 20:46:38'),
(247, 45, 7, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-05 20:47:26'),
(248, 46, 8, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-05 20:48:05'),
(249, 46, 8, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Impresion General', '2026-03-05 20:48:16'),
(250, 41, 9, 8, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-05 20:50:04'),
(251, 46, 9, 8, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-05 20:50:05'),
(252, 43, 9, 8, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-05 20:50:07'),
(253, 37, 9, 8, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-05 20:53:07'),
(254, 37, 9, 8, 'Transferido', 'Pedido enviado al área ID: 1', '2026-03-05 20:53:11'),
(255, 33, 11, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Impresion DTF', '2026-03-05 20:57:38'),
(256, 47, 10, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-05 20:57:46'),
(257, 48, 11, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-05 20:58:48'),
(258, 47, 10, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Impresion General', '2026-03-05 20:58:48'),
(259, 48, 11, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Impresion DTF', '2026-03-05 20:59:05'),
(260, 49, 11, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-05 21:01:48'),
(261, 5, 11, 9, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-05 21:03:08'),
(262, 5, 11, 9, 'Completado', 'Pedido finalizado y completado', '2026-03-05 21:03:20'),
(263, 48, 11, 9, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-05 21:03:48'),
(264, 41, 8, 8, 'Editado', 'Datos del pedido actualizados desde Recepción', '2026-03-05 21:09:28'),
(265, 50, 8, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-05 21:14:48'),
(266, 50, 8, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Impresion General', '2026-03-05 21:15:05'),
(267, 49, 11, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Impresion DTF', '2026-03-05 21:19:46'),
(268, 3, 11, 9, 'Enviado a Área', 'Pedido enviado al área: Impresion DTF', '2026-03-05 21:20:12'),
(269, 48, 11, 9, 'Enviado a Área', 'Pedido enviado al área: Impresion DTF', '2026-03-05 21:20:42'),
(270, 41, 9, 8, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-05 21:20:52'),
(271, 6, 11, 9, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-05 21:20:56'),
(272, 40, 7, 8, 'Editado', 'Datos del pedido actualizados desde Recepción', '2026-03-05 21:20:58'),
(273, 6, 11, 9, 'Completado', 'Pedido finalizado y completado', '2026-03-05 21:21:02'),
(274, 41, 9, 8, 'Transferido', 'Pedido enviado al área ID: 14', '2026-03-05 21:21:04'),
(275, 48, 11, 9, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-05 21:21:13'),
(276, 45, 7, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Confección', '2026-03-05 21:21:15'),
(277, 49, 11, 9, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-05 21:21:19'),
(278, 44, 7, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Confección', '2026-03-05 21:21:21'),
(279, 29, 11, 9, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-05 21:21:41'),
(280, 30, 11, 9, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-05 21:21:44'),
(281, 31, 11, 9, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-05 21:21:47'),
(282, 33, 11, 9, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-05 21:21:49'),
(283, 3, 11, 9, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-05 21:21:51'),
(284, 51, 11, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-05 21:24:19'),
(285, 51, 11, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Impresion DTF', '2026-03-05 21:24:28'),
(286, 48, 11, 9, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-05 21:25:51'),
(287, 30, 11, 9, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-05 21:25:57'),
(288, 3, 11, 9, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-05 21:26:01'),
(289, 3, 11, 9, 'Completado', 'Pedido finalizado y completado', '2026-03-05 21:26:16'),
(290, 48, 11, 9, 'Completado', 'Pedido finalizado y completado', '2026-03-05 21:26:38'),
(291, 47, 9, 8, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-05 21:28:45'),
(292, 50, 9, 8, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-05 21:28:47'),
(293, 32, 9, 8, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-05 21:29:40'),
(294, 32, 9, 8, 'Transferido', 'Pedido enviado al área ID: 12', '2026-03-05 21:29:44'),
(295, 30, 11, 9, 'Completado', 'Pedido finalizado y completado', '2026-03-05 21:37:11'),
(296, 36, 9, 1, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-05 21:50:21'),
(297, 36, 9, 1, 'Completado', 'Pedido finalizado y completado', '2026-03-05 21:50:27'),
(298, 37, 9, 1, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-05 21:51:01'),
(299, 27, 11, 9, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-05 21:51:43'),
(300, 27, 11, 9, 'Completado', 'Pedido finalizado y completado', '2026-03-05 21:51:58'),
(301, 40, 9, 8, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-05 21:53:54'),
(302, 51, 11, 9, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-05 21:56:46'),
(303, 44, 8, 13, 'Enviado a Área', 'Pedido enviado al área: Confección', '2026-03-05 21:58:09'),
(304, 43, 9, 8, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-05 22:02:04'),
(305, 43, 9, 8, 'Transferido', 'Pedido enviado al área ID: 14', '2026-03-05 22:02:09'),
(306, 29, 11, 9, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-05 22:03:23'),
(307, 29, 11, 9, 'Completado', 'Pedido finalizado y completado', '2026-03-05 22:06:32'),
(308, 43, 8, 14, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-05 22:27:49'),
(309, 43, 8, 14, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-05 22:27:54'),
(310, 43, 8, 14, 'Completado', 'Pedido finalizado y completado', '2026-03-05 22:27:57'),
(311, 32, 9, 12, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-05 22:34:36'),
(312, 32, 9, 12, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-05 22:34:39'),
(313, 32, 9, 12, 'Completado', 'Pedido finalizado y completado', '2026-03-05 22:34:42'),
(314, 49, 11, 9, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-05 22:44:46'),
(315, 49, 11, 9, 'Completado', 'Pedido finalizado y completado', '2026-03-05 22:44:49'),
(316, 31, 11, 9, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-05 22:44:52'),
(317, 51, 11, 9, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-05 22:45:01'),
(318, 51, 11, 9, 'Completado', 'Pedido finalizado y completado', '2026-03-05 22:45:12'),
(319, 31, 11, 9, 'Completado', 'Pedido finalizado y completado', '2026-03-05 22:45:33'),
(320, 40, 9, 8, 'Completado', 'Pedido finalizado y completado', '2026-03-05 23:08:53'),
(321, 33, 11, 9, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-05 23:33:55'),
(322, 33, 11, 9, 'Completado', 'Pedido finalizado y completado', '2026-03-05 23:33:58'),
(323, 52, 11, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-05 23:35:03'),
(324, 52, 11, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Impresion DTF', '2026-03-05 23:35:12'),
(325, 52, 11, 9, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-05 23:35:20'),
(326, 52, 11, 9, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-05 23:58:15'),
(327, 52, 11, 9, 'Completado', 'Pedido finalizado y completado', '2026-03-05 23:58:18'),
(328, 46, 9, 8, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-06 00:03:06'),
(329, 46, 9, 8, 'Transferido', 'Pedido enviado al área ID: 12', '2026-03-06 00:03:10'),
(330, 50, 9, 8, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-06 00:03:15'),
(331, 50, 9, 8, 'Completado', 'Pedido finalizado y completado', '2026-03-06 00:03:22'),
(332, 28, 9, 8, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-06 00:17:00'),
(333, 28, 9, 8, 'Transferido', 'Pedido enviado al área ID: 12', '2026-03-06 00:17:16'),
(334, 53, 11, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-06 00:27:42'),
(335, 53, 11, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Diseño y Armado DTF', '2026-03-06 00:27:53'),
(336, 53, 11, 10, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-06 00:41:20'),
(337, 54, 8, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-06 13:50:03'),
(338, 54, 8, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Impresion General', '2026-03-06 13:50:10'),
(339, 41, 8, 14, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-06 13:50:30'),
(340, 41, 8, 14, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-06 13:50:33'),
(341, 41, 8, 14, 'Completado', 'Pedido finalizado y completado', '2026-03-06 13:50:36'),
(342, 46, 8, 12, 'Editado', 'Datos del pedido actualizados desde Recepción', '2026-03-06 13:51:04'),
(343, 55, 8, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-06 13:58:24'),
(344, 55, 8, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Impresion General', '2026-03-06 13:58:33'),
(345, 46, 9, 12, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-06 14:11:41'),
(346, 46, 9, 12, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-06 14:11:43'),
(347, 46, 9, 12, 'Completado', 'Pedido finalizado y completado', '2026-03-06 14:11:48'),
(348, 56, 11, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-06 14:24:12'),
(349, 57, 8, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-06 14:25:57'),
(350, 57, 8, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Impresion General', '2026-03-06 14:26:04'),
(351, 58, 11, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-06 14:27:03'),
(352, 59, 8, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-06 14:27:29'),
(353, 59, 8, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Impresion General', '2026-03-06 14:27:35'),
(354, 37, 8, 1, 'Editado', 'Datos del pedido actualizados desde Recepción', '2026-03-06 14:28:12'),
(355, 60, 11, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-06 14:32:38'),
(356, 61, 11, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-06 14:34:26'),
(357, 62, 11, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-06 14:36:38'),
(358, 63, 11, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-06 14:39:51'),
(359, 12, 11, 9, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-06 14:40:30'),
(360, 12, 11, 9, 'Completado', 'Pedido finalizado y completado', '2026-03-06 14:40:33'),
(361, 64, 11, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-06 14:41:59'),
(362, 34, 10, 2, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-06 14:46:00'),
(363, 34, 10, 2, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-06 14:46:04'),
(364, 34, 10, 2, 'Transferido', 'Pedido enviado al área ID: 8', '2026-03-06 14:46:31'),
(365, 54, 9, 8, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-06 14:59:16'),
(366, 65, 11, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-06 15:03:29'),
(367, 65, 11, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Impresion DTF', '2026-03-06 15:03:39'),
(368, 65, 11, 9, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-06 15:17:44'),
(369, 56, 11, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Impresion DTF', '2026-03-06 15:17:56'),
(370, 64, 11, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Impresion DTF', '2026-03-06 15:18:03'),
(371, 63, 11, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Impresion DTF', '2026-03-06 15:18:09'),
(372, 62, 11, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Impresion DTF', '2026-03-06 15:18:17'),
(373, 61, 11, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Impresion DTF', '2026-03-06 15:18:25'),
(374, 60, 11, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Impresion DTF', '2026-03-06 15:18:33'),
(375, 58, 11, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Impresion DTF', '2026-03-06 15:18:39'),
(376, 56, 11, 9, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-06 15:18:45'),
(377, 63, 11, 9, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-06 15:18:46'),
(378, 64, 11, 9, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-06 15:18:49'),
(379, 62, 11, 9, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-06 15:18:51'),
(380, 60, 11, 9, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-06 15:18:53'),
(381, 58, 11, 9, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-06 15:18:54'),
(382, 61, 11, 9, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-06 15:18:56'),
(383, 55, 9, 8, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-06 15:31:08'),
(384, 57, 9, 8, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-06 15:31:09'),
(385, 59, 9, 8, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-06 15:31:11'),
(386, 34, 9, 8, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-06 15:31:12'),
(387, 66, 8, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-06 15:40:15'),
(388, 66, 8, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Impresion General', '2026-03-06 15:40:21'),
(389, 25, 10, 2, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-06 15:41:16'),
(390, 67, 8, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-06 15:43:58'),
(391, 67, 8, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Impresion General', '2026-03-06 15:44:09'),
(392, 65, 11, 9, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-06 16:02:21'),
(393, 65, 11, 9, 'Completado', 'Pedido finalizado y completado', '2026-03-06 16:02:24'),
(394, 55, 9, 8, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-06 16:03:06'),
(395, 55, 9, 8, 'Transferido', 'Pedido enviado al área ID: 14', '2026-03-06 16:03:12'),
(396, 67, 9, 8, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-06 16:03:28'),
(397, 66, 9, 8, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-06 16:03:35'),
(398, 24, 7, 14, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-06 16:08:42'),
(399, 22, 7, 14, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-06 16:08:44'),
(400, 24, 7, 14, 'Completado', 'Pedido finalizado y completado', '2026-03-06 16:08:48'),
(401, 22, 7, 14, 'Completado', 'Pedido finalizado y completado', '2026-03-06 16:09:09'),
(402, 67, 9, 8, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-06 16:11:13'),
(403, 67, 9, 8, 'Transferido', 'Pedido enviado al área ID: 14', '2026-03-06 16:11:18'),
(404, 68, 11, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-06 16:11:21'),
(405, 68, 11, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Impresion DTF', '2026-03-06 16:11:30'),
(406, 67, 9, 14, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-06 16:11:43'),
(407, 64, 11, 9, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-06 16:11:46'),
(408, 55, 9, 14, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-06 16:11:47'),
(409, 63, 11, 9, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-06 16:11:51'),
(410, 58, 11, 9, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-06 16:11:53'),
(411, 69, 8, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-06 16:11:57'),
(412, 60, 11, 9, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-06 16:11:57'),
(413, 56, 11, 9, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-06 16:12:01'),
(414, 69, 8, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Impresion General', '2026-03-06 16:12:04'),
(415, 64, 11, 9, 'Completado', 'Pedido finalizado y completado', '2026-03-06 16:12:13'),
(416, 58, 11, 9, 'Completado', 'Pedido finalizado y completado', '2026-03-06 16:12:35'),
(417, 56, 11, 9, 'Completado', 'Pedido finalizado y completado', '2026-03-06 16:12:58'),
(418, 70, 8, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-06 16:14:06'),
(419, 70, 8, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Impresion General', '2026-03-06 16:14:13'),
(420, 60, 11, 9, 'Completado', 'Pedido finalizado y completado', '2026-03-06 16:14:37'),
(421, 63, 11, 9, 'Completado', 'Pedido finalizado y completado', '2026-03-06 16:15:00'),
(422, 67, 9, 14, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-06 16:24:51'),
(423, 55, 9, 14, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-06 16:24:53'),
(424, 67, 9, 14, 'Completado', 'Pedido finalizado y completado', '2026-03-06 16:24:56'),
(425, 55, 9, 14, 'Completado', 'Pedido finalizado y completado', '2026-03-06 16:25:18'),
(426, 68, 11, 9, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-06 16:31:19'),
(427, 62, 11, 9, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-06 16:31:24'),
(428, 62, 11, 9, 'Completado', 'Pedido finalizado y completado', '2026-03-06 16:31:29'),
(429, 69, 9, 8, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-06 16:33:18'),
(430, 70, 9, 8, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-06 16:33:25'),
(431, 57, 9, 8, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-06 16:39:40'),
(432, 66, 9, 8, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-06 16:39:46'),
(433, 57, 9, 8, 'Transferido', 'Pedido enviado al área ID: 12', '2026-03-06 16:42:58'),
(434, 66, 9, 8, 'Transferido', 'Pedido enviado al área ID: 12', '2026-03-06 16:43:01'),
(435, 61, 11, 9, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-06 17:06:20'),
(436, 61, 11, 9, 'Completado', 'Pedido finalizado y completado', '2026-03-06 17:06:22'),
(437, 68, 11, 9, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-06 17:06:28'),
(438, 68, 11, 9, 'Completado', 'Pedido finalizado y completado', '2026-03-06 17:06:47'),
(439, 55, 11, 14, 'Editado', 'Datos del pedido actualizados desde Recepción', '2026-03-06 17:09:23'),
(440, 61, 11, 9, 'Editado', 'Datos del pedido actualizados desde Recepción', '2026-03-06 17:09:36'),
(441, 60, 11, 9, 'Editado', 'Datos del pedido actualizados desde Recepción', '2026-03-06 17:10:42'),
(442, 63, 11, 9, 'Editado', 'Datos del pedido actualizados desde Recepción', '2026-03-06 17:11:02'),
(443, 53, 11, 10, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-06 17:15:03'),
(444, 53, 11, 10, 'Completado', 'Pedido finalizado y completado', '2026-03-06 17:15:15'),
(445, 71, 11, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-06 17:23:17'),
(446, 52, 11, 9, 'Editado', 'Datos del pedido actualizados desde Recepción', '2026-03-06 17:31:34'),
(447, 72, 11, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-06 17:48:46'),
(448, 73, 11, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-06 19:18:25'),
(449, 74, 8, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-06 19:30:54'),
(450, 74, 8, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Impresion General', '2026-03-06 19:31:04'),
(451, 74, 9, 8, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-06 19:37:23'),
(452, 69, 9, 8, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-06 19:37:44'),
(453, 69, 9, 8, 'Completado', 'Pedido finalizado y completado', '2026-03-06 19:37:52'),
(454, 70, 9, 8, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-06 19:38:03'),
(455, 70, 9, 8, 'Transferido', 'Pedido enviado al área ID: 1', '2026-03-06 19:38:07'),
(456, 72, 11, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Impresion DTF', '2026-03-06 19:49:24'),
(457, 72, 11, 9, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-06 19:49:27'),
(458, 71, 11, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Impresion DTF', '2026-03-06 19:49:36'),
(459, 73, 11, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Impresion DTF', '2026-03-06 19:49:42'),
(460, 75, 11, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-06 19:51:21'),
(461, 70, 7, 1, 'Editado', 'Datos del pedido actualizados desde Recepción', '2026-03-06 19:52:09'),
(462, 76, 8, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-06 19:52:49'),
(463, 76, 8, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Impresion General', '2026-03-06 19:52:55'),
(464, 75, 11, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Impresion DTF', '2026-03-06 19:53:01'),
(465, 75, 11, 9, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-06 19:53:14'),
(466, 71, 11, 9, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-06 19:53:18'),
(467, 73, 11, 9, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-06 19:53:20'),
(468, 22, 8, 14, 'Editado', 'Datos del pedido actualizados desde Recepción', '2026-03-06 19:53:40'),
(469, 11, 8, 12, 'Editado', 'Datos del pedido actualizados desde Recepción', '2026-03-06 19:55:53'),
(470, 55, 7, 14, 'Editado', 'Datos del pedido actualizados desde Recepción', '2026-03-06 19:56:11'),
(471, 40, 11, 8, 'Editado', 'Datos del pedido actualizados desde Recepción', '2026-03-06 19:56:31'),
(472, 24, 8, 14, 'Editado', 'Datos del pedido actualizados desde Recepción', '2026-03-06 19:56:48'),
(473, 33, 11, 9, 'Editado', 'Datos del pedido actualizados desde Recepción', '2026-03-06 19:57:02'),
(474, 68, 7, 9, 'Editado', 'Datos del pedido actualizados desde Recepción', '2026-03-06 19:57:05'),
(475, 36, 8, 1, 'Editado', 'Datos del pedido actualizados desde Recepción', '2026-03-06 19:57:12'),
(476, 10, 8, 8, 'Editado', 'Datos del pedido actualizados desde Recepción', '2026-03-06 19:57:26'),
(477, 9, 8, 3, 'Editado', 'Datos del pedido actualizados desde Recepción', '2026-03-06 19:57:40'),
(478, 20, 8, 12, 'Editado', 'Datos del pedido actualizados desde Recepción', '2026-03-06 19:57:55'),
(479, 21, 8, 8, 'Editado', 'Datos del pedido actualizados desde Recepción', '2026-03-06 19:58:12'),
(480, 32, 8, 12, 'Editado', 'Datos del pedido actualizados desde Recepción', '2026-03-06 19:58:34'),
(481, 12, 7, 9, 'Cancelado', 'Pedido eliminado desde Recepción', '2026-03-06 19:59:20'),
(482, 31, 7, 9, 'Cancelado', 'Pedido eliminado desde Recepción', '2026-03-06 19:59:28'),
(483, 16, 8, 12, 'Editado', 'Datos del pedido actualizados desde Recepción', '2026-03-06 20:00:51'),
(484, 62, 8, 9, 'Editado', 'Datos del pedido actualizados desde Recepción', '2026-03-06 20:01:11'),
(485, 41, 8, 14, 'Editado', 'Datos del pedido actualizados desde Recepción', '2026-03-06 20:01:27'),
(486, 43, 8, 14, 'Editado', 'Datos del pedido actualizados desde Recepción', '2026-03-06 20:01:42'),
(487, 74, 9, 8, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-06 20:02:05'),
(488, 23, 8, 12, 'Editado', 'Datos del pedido actualizados desde Recepción', '2026-03-06 20:02:12'),
(489, 74, 9, 8, 'Transferido', 'Pedido enviado al área ID: 14', '2026-03-06 20:02:18'),
(490, 29, 11, 9, 'Editado', 'Datos del pedido actualizados desde Recepción', '2026-03-06 20:03:04'),
(491, 76, 9, 8, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-06 20:06:34'),
(492, 28, 9, 12, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-06 20:06:52'),
(493, 57, 9, 12, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-06 20:06:56'),
(494, 66, 9, 12, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-06 20:06:59'),
(495, 57, 9, 12, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-06 20:07:02'),
(496, 57, 9, 12, 'Completado', 'Pedido finalizado y completado', '2026-03-06 20:07:06'),
(497, 66, 9, 12, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-06 20:07:11'),
(498, 77, 7, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-06 20:10:59'),
(499, 77, 7, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Confección', '2026-03-06 20:11:24'),
(500, 77, 7, 13, 'Editado', 'Datos del pedido actualizados desde Recepción', '2026-03-06 20:12:53'),
(501, 66, 9, 12, 'Completado', 'Pedido finalizado y completado', '2026-03-06 20:23:12'),
(502, 76, 9, 8, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-06 20:23:28'),
(503, 76, 9, 8, 'Transferido', 'Pedido enviado al área ID: 14', '2026-03-06 20:23:32'),
(504, 76, 9, 14, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-06 20:23:41'),
(505, 76, 9, 14, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-06 20:23:43'),
(506, 76, 9, 14, 'Completado', 'Pedido finalizado y completado', '2026-03-06 20:23:46'),
(507, 42, 7, 13, 'Editado', 'Datos del pedido actualizados desde Recepción', '2026-03-06 20:29:17'),
(508, 77, 7, 13, 'Editado', 'Datos del pedido actualizados desde Recepción', '2026-03-06 20:30:18'),
(509, 77, 7, 13, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-06 20:31:12'),
(510, 77, 7, 13, 'Editado', 'Datos del pedido actualizados desde Recepción', '2026-03-06 20:31:34'),
(511, 42, 7, 13, 'Editado', 'Datos del pedido actualizados desde Recepción', '2026-03-06 20:31:47'),
(512, 44, 7, 13, 'Editado', 'Datos del pedido actualizados desde Recepción', '2026-03-06 20:32:20'),
(513, 35, 7, 13, 'Editado', 'Datos del pedido actualizados desde Recepción', '2026-03-06 20:33:12'),
(514, 72, 11, 9, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-06 20:33:17'),
(515, 72, 11, 9, 'Completado', 'Pedido finalizado y completado', '2026-03-06 20:33:28'),
(516, 45, 7, 13, 'Editado', 'Datos del pedido actualizados desde Recepción', '2026-03-06 20:34:00'),
(517, 44, 7, 13, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-06 20:34:08');
INSERT INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES
(518, 45, 7, 13, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-06 20:34:10'),
(519, 75, 11, 9, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-06 20:35:24'),
(520, 78, 7, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-06 20:36:05'),
(521, 78, 7, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Confección', '2026-03-06 20:36:28'),
(522, 78, 7, 13, 'Editado', 'Datos del pedido actualizados desde Recepción', '2026-03-06 20:37:34'),
(523, 75, 11, 9, 'Completado', 'Pedido finalizado y completado', '2026-03-06 20:38:18'),
(524, 45, 7, 13, 'Editado', 'Datos del pedido actualizados desde Recepción', '2026-03-06 20:39:11'),
(525, 73, 11, 9, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-06 20:39:32'),
(526, 71, 11, 9, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-06 20:39:32'),
(527, 73, 11, 9, 'Completado', 'Pedido finalizado y completado', '2026-03-06 20:48:48'),
(528, 71, 11, 9, 'Completado', 'Pedido finalizado y completado', '2026-03-06 20:49:19'),
(529, 79, 7, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-06 20:56:50'),
(530, 79, 7, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Confección', '2026-03-06 20:56:57'),
(531, 70, 9, 1, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-06 21:30:18'),
(532, 70, 9, 1, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-06 21:30:20'),
(533, 70, 9, 1, 'Completado', 'Pedido finalizado y completado', '2026-03-06 21:30:22'),
(534, 80, 10, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-06 21:41:31'),
(535, 80, 10, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Impresion DTF', '2026-03-06 21:42:01'),
(536, 81, 10, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-06 22:17:39'),
(537, 82, 11, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-06 22:28:57'),
(538, 80, 11, 9, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-06 22:29:17'),
(539, 82, 11, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Impresion DTF', '2026-03-06 22:29:51'),
(540, 83, 11, NULL, 'Creado', 'Pedido ingresado desde Recepción', '2026-03-06 22:34:50'),
(541, 83, 11, NULL, 'Enviado a Área', 'Pedido enviado desde Recepción al área: Impresion DTF', '2026-03-06 22:35:00'),
(542, 82, 11, 9, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-06 22:35:07'),
(543, 83, 11, 9, 'Inicia Proceso', 'Operador toma el pedido.', '2026-03-06 22:35:09'),
(544, 80, 11, 9, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-06 23:21:43'),
(545, 82, 11, 9, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-06 23:21:45'),
(546, 83, 11, 9, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-03-06 23:21:47'),
(547, 80, 11, 9, 'Completado', 'Pedido finalizado y completado', '2026-03-06 23:21:50'),
(548, 37, 7, 1, 'Traslado libre a preparado', 'Movimiento manual en el Kanban.', '2026-03-06 23:22:02'),
(549, 37, 7, 1, 'Completado', 'Pedido finalizado y completado', '2026-03-06 23:22:09'),
(550, 82, 11, 9, 'Completado', 'Pedido finalizado y completado', '2026-03-06 23:22:21'),
(551, 83, 11, 9, 'Completado', 'Pedido finalizado y completado', '2026-03-06 23:22:43');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones_salientes`
--

CREATE TABLE `notificaciones_salientes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `destinatario` varchar(100) NOT NULL,
  `tipo` enum('email','sms','sistema') NOT NULL,
  `asunto` varchar(255) DEFAULT NULL,
  `mensaje` text NOT NULL,
  `estado` enum('pendiente','enviado','fallido') DEFAULT 'pendiente',
  `intentos` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `notificaciones_salientes`
--

INSERT INTO `notificaciones_salientes` (`id`, `destinatario`, `tipo`, `asunto`, `mensaje`, `estado`, `intentos`, `created_at`, `updated_at`) VALUES
(1, '3184483187', 'sms', NULL, 'Hola eliza, tu pedido banner.com.co/seguimiento/XJB4YT ha sido creado. Gracias por confiar en Banner', 'pendiente', 0, '2026-02-25 17:16:46', '2026-02-25 17:16:46');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos`
--

CREATE TABLE `pagos` (
  `id` int(10) UNSIGNED NOT NULL,
  `pedido_id` int(10) UNSIGNED NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `metodo_pago` varchar(50) NOT NULL,
  `referencia` varchar(100) DEFAULT NULL,
  `fecha_pago` timestamp NULL DEFAULT current_timestamp(),
  `estado` enum('completado','pendiente','fallido','reembolsado') DEFAULT 'completado',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `id` int(10) UNSIGNED NOT NULL,
  `token_seguimiento` varchar(64) DEFAULT NULL,
  `cliente_nombre` varchar(150) NOT NULL,
  `cliente_email` varchar(100) DEFAULT NULL,
  `cliente_telefono` varchar(20) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `area_actual_id` int(10) UNSIGNED DEFAULT NULL,
  `fase_actual` enum('recepcion','proceso','preparado') DEFAULT 'recepcion',
  `estado_pago` enum('pago_completo','abono','no_pago') DEFAULT 'no_pago',
  `prioridad` enum('prioridad','normal','largo') DEFAULT 'normal',
  `asignado_a_usuario_id` int(10) UNSIGNED DEFAULT NULL,
  `estado` enum('pendiente','en_curso','completado','cancelado') DEFAULT 'pendiente',
  `fecha_entrega_esperada` date DEFAULT NULL,
  `total` decimal(10,2) DEFAULT 0.00,
  `abonado` decimal(10,2) DEFAULT 0.00,
  `last_movement_at` timestamp NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `pedidos`
--

INSERT INTO `pedidos` (`id`, `token_seguimiento`, `cliente_nombre`, `cliente_email`, `cliente_telefono`, `descripcion`, `area_actual_id`, `fase_actual`, `estado_pago`, `prioridad`, `asignado_a_usuario_id`, `estado`, `fecha_entrega_esperada`, `total`, `abonado`, `last_movement_at`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'HOUCBG', 'Uriel', '', '3116623246', 'tttt', 8, 'preparado', 'no_pago', 'normal', 1, 'cancelado', NULL, 50000.00, 0.00, '2026-03-04 17:21:19', '2026-03-04 17:12:04', '2026-03-05 06:06:43', '2026-03-04 16:55:29'),
(2, 'SLUXT2', 'Alexis', '', '3022404391', '5.000 Stikers de como marcar', 3, 'preparado', 'pago_completo', 'prioridad', 8, 'completado', NULL, 50000.00, 0.00, '2026-03-04 23:25:15', '2026-03-04 17:49:20', '2026-03-04 23:25:15', NULL),
(3, 'SA0RL3', 'Euclides', '', '3332644603', 'DTF 57X72 CM', 9, 'preparado', 'no_pago', 'normal', 11, 'completado', NULL, 18000.00, 0.00, '2026-03-05 21:26:16', '2026-03-04 19:16:06', '2026-03-05 21:26:16', NULL),
(4, 'SODI4E', 'Said', '', '3004352910', 'Pendon 100x70 con tubos', 8, 'preparado', 'abono', 'normal', 7, 'cancelado', NULL, 35000.00, 20000.00, '2026-03-04 19:35:37', '2026-03-04 19:34:08', '2026-03-05 06:06:43', '2026-03-04 16:55:40'),
(5, 'PMTLQC', 'Marisleylis', '', '3206055184', 'DTF 58X128 CM', 9, 'preparado', 'pago_completo', 'normal', 11, 'completado', NULL, 31000.00, 0.00, '2026-03-05 21:03:20', '2026-03-04 20:48:24', '2026-03-05 21:03:20', NULL),
(6, 'DGLV9X', 'Sandra', '', '3127447368', 'DTF 56X165 CM', 9, 'preparado', 'no_pago', 'normal', 11, 'completado', NULL, 41000.00, 0.00, '2026-03-05 21:21:02', '2026-03-04 20:50:53', '2026-03-05 21:21:02', NULL),
(7, 'W642SR', 'FERNANDO', '', '3024063801', 'Impresion DTF, y marcada de suéter con logo misión juvenil', 9, 'preparado', 'pago_completo', 'normal', 7, 'completado', NULL, 16000.00, 0.00, '2026-03-05 03:03:42', '2026-03-04 21:21:35', '2026-03-05 03:03:42', NULL),
(8, '2PJ0C9', 'JHONATAN MAXIM', '', '3009835251', '250 IMPRESIÓN FUL COLOR TAMAÑO CARTA EN VINILO LAMINADO BRILLANTE\r\n4 ARCHIVOS DE 148X273 DESCARGAR EN TELEGRAM', 9, 'recepcion', 'pago_completo', 'normal', NULL, 'pendiente', NULL, 575000.00, 0.00, '2026-03-05 17:15:23', '2026-03-04 21:42:05', '2026-03-05 17:15:23', NULL),
(9, 'K4CI3Y', 'mono', '', '304 3931841', 'VIN SIN LAM 50X50', 3, 'preparado', 'pago_completo', 'prioridad', 8, 'completado', NULL, 8000.00, 0.00, '2026-03-04 22:08:49', '2026-03-04 21:46:55', '2026-03-06 19:57:40', NULL),
(10, 'OFNZHR', 'Isamar', '', '316 7196702', 'STICKERS ISAMAR\r\nARCHIVO TAMAÑO DE 200MB EN IMPRESIÓN DIGITAL CON NOMBRE: 144X157 VIN Isamar2', 8, 'preparado', 'pago_completo', 'prioridad', 9, 'completado', NULL, 45000.00, 0.00, '2026-03-04 23:17:39', '2026-03-04 22:13:44', '2026-03-06 19:57:26', NULL),
(11, 'P1WNCQ', 'JASSER', '', '319 6353431', 'VINILO LAMINADO 100X30', 12, 'preparado', 'pago_completo', 'prioridad', 9, 'completado', NULL, 10000.00, 0.00, '2026-03-04 22:50:57', '2026-03-04 22:14:34', '2026-03-06 19:55:53', NULL),
(12, 'F2VIO8', 'Juan', '', '3044476390', 'DTF 40X8 CM', 9, 'preparado', 'no_pago', 'normal', 11, 'cancelado', NULL, 10000.00, 0.00, '2026-03-06 14:40:33', '2026-03-04 23:21:18', '2026-03-06 19:59:20', '2026-03-06 19:59:20'),
(13, 'PM38J6', 'Daniel', '', '3184483187', 'Pedido de prueba', NULL, 'recepcion', 'no_pago', 'normal', NULL, 'cancelado', NULL, 100000.00, 0.00, '2026-03-05 01:21:13', '2026-03-05 01:21:13', '2026-03-05 02:46:27', '2026-03-05 02:46:27'),
(14, 'YT09ZI', 'Uriel', '', '3184483187', 'hola', 9, 'preparado', 'no_pago', 'normal', 7, 'cancelado', NULL, 15.00, 0.00, '2026-03-05 02:44:26', '2026-03-05 02:43:58', '2026-03-05 04:08:55', '2026-03-05 04:08:55'),
(15, 'XK6WGB', 'Daniel Atencia', '', '3184483187', 'DTF', 1, 'preparado', 'abono', 'normal', 13, 'cancelado', NULL, 16000.00, 3000.00, '2026-03-05 13:46:20', '2026-03-05 13:44:29', '2026-03-05 13:48:44', '2026-03-05 13:48:44'),
(16, 'B6QMIH', 'JUAN DIEGO', '', '311 8783260', 'BANNER CON OJALES 200X130 Y VINILO LAMINADO 112X67 (NO ESTAN COBRADOS LOS BOTONES)', 12, 'preparado', 'pago_completo', 'prioridad', 9, 'completado', NULL, 66000.00, 0.00, '2026-03-05 17:05:26', '2026-03-05 13:51:53', '2026-03-06 20:00:51', NULL),
(17, '15FKBE', 'luis bustamante', '', '302 5292144', 'VINILO LAMINADO MATE 72X46', 12, 'preparado', 'no_pago', 'normal', 9, 'completado', NULL, 10000.00, 0.00, '2026-03-05 15:04:18', '2026-03-05 13:57:52', '2026-03-05 15:04:18', NULL),
(18, '3OG4XY', 'ERNESTO LEAR', '', '300 8053552', 'TRANSP LAM BLANCO 97X61 Y BANER 102X183', 12, 'preparado', 'no_pago', 'normal', 9, 'completado', NULL, 44000.00, 0.00, '2026-03-05 15:57:04', '2026-03-05 14:08:08', '2026-03-05 15:57:04', NULL),
(19, '0CFRY8', 'SANDRA', '', '304 6155065', 'VINILO LAM MATE 84X25', 12, 'preparado', 'no_pago', 'prioridad', 9, 'completado', NULL, 10000.00, 0.00, '2026-03-05 15:03:54', '2026-03-05 14:31:48', '2026-03-05 15:03:54', NULL),
(20, 'TLZ9QR', 'quirofano', '', '300 3559797', 'vin lam mate quirofano 40x60', 12, 'preparado', 'pago_completo', 'prioridad', 9, 'completado', NULL, 9.00, 0.00, '2026-03-05 16:43:35', '2026-03-05 15:53:12', '2026-03-06 19:57:55', NULL),
(21, 'UXMAGT', 'ESTEBAN DOMINGUEZ', '', '311 2780177', 'VINILO BRILLANTE SIN LAM 105X185(5 VECES) 105X202', 8, 'preparado', 'pago_completo', 'prioridad', 9, 'completado', NULL, 166.00, 0.00, '2026-03-05 19:47:16', '2026-03-05 16:21:35', '2026-03-06 19:58:12', NULL),
(22, 'QY4PLR', 'JUAN DIEGO', '', '', 'ELABORACIÓN DE 35 BOTONES', 14, 'preparado', 'pago_completo', 'prioridad', 7, 'completado', NULL, 42000.00, 0.00, '2026-03-06 16:09:09', '2026-03-05 16:22:50', '2026-03-06 19:53:40', NULL),
(23, 'F25SE9', 'ISAAC ESCAÑO', '', '302 4302840', '82X80 VIN LAM BRILL', 12, 'preparado', 'pago_completo', 'prioridad', 9, 'completado', NULL, 20000.00, 0.00, '2026-03-05 20:40:57', '2026-03-05 16:24:12', '2026-03-06 20:02:12', NULL),
(24, '6JSB51', 'CLIENTE REGISTRADURIA', '', '', '100 BOTONES PUBLICITARIOS Y 6 REATAS A $8.000', 14, 'preparado', 'pago_completo', 'normal', 7, 'completado', NULL, 178000.00, 90000.00, '2026-03-06 16:08:48', '2026-03-05 16:24:22', '2026-03-06 19:56:48', NULL),
(25, 'PMQNVO', 'Heyleen Uniformes', '', '300 8809744', '37 Uniformes deportivos masculinos con talla varias: S=5, M=18, L=7, XL=6, XXL=1\r\n37 uniformes $1.665.000\r\n5 suéteres $175.000\r\n16 pantalonetas $240.000', 2, 'proceso', 'abono', 'largo', 10, 'pendiente', NULL, 2080000.00, 791000.00, '2026-03-06 15:41:16', '2026-03-05 16:35:26', '2026-03-06 15:41:16', NULL),
(26, 'B0DZ7X', 'ISAAC ESCAÑO', '', '3024302840', 'tornasol lam brill 46x104', 12, 'preparado', 'no_pago', 'normal', 9, 'completado', NULL, 23000.00, 0.00, '2026-03-05 20:40:32', '2026-03-05 16:55:40', '2026-03-05 20:40:32', NULL),
(27, '8SXUEP', 'Emiro Urzola Vector 9', '', '3003390394', 'DTF 55x288 cm', 9, 'preparado', 'no_pago', 'prioridad', 11, 'completado', NULL, 58000.00, 0.00, '2026-03-05 21:51:58', '2026-03-05 17:07:41', '2026-03-05 21:51:58', NULL),
(28, 'JG4HFM', 'Jhonatan Maxim', '', '3009835251', '200 Círculos Maxim de piso 50x50 laminados', 12, 'proceso', 'pago_completo', 'normal', 9, 'pendiente', NULL, 1900000.00, 0.00, '2026-03-06 20:06:52', '2026-03-05 17:13:53', '2026-03-06 20:06:52', NULL),
(29, 'P35TWI', 'Tatiana Arroyo', '', '3107117687', 'DTF 58X99 cm', 9, 'preparado', 'pago_completo', 'normal', 11, 'completado', NULL, 25000.00, 0.00, '2026-03-05 22:06:32', '2026-03-05 17:15:07', '2026-03-06 20:03:04', NULL),
(30, 'J51XK2', 'Grama', '', '3012223280', 'DTF 55X31 cm', 9, 'preparado', 'no_pago', 'normal', 11, 'completado', NULL, 10000.00, 0.00, '2026-03-05 21:37:11', '2026-03-05 17:17:20', '2026-03-05 21:37:11', NULL),
(31, '81A39I', 'Estampado Caribe', '', '3004686803', 'DTF 58X201 cm', 9, 'preparado', 'no_pago', 'normal', 11, 'cancelado', NULL, 50000.00, 0.00, '2026-03-05 22:45:33', '2026-03-05 17:20:06', '2026-03-06 19:59:28', '2026-03-06 19:59:28'),
(32, '8CNLQJ', 'Luz Karina', '', '318 3955053', '23 imágenes organizados en un tamaño total de 146X109 VIN LAM BRILLANTE Luz Karina\r\nArchivo adjunto en Telegram con un peso de 88.5MB', 12, 'preparado', 'pago_completo', 'normal', 9, 'completado', NULL, 50000.00, 0.00, '2026-03-05 22:34:42', '2026-03-05 17:25:14', '2026-03-06 19:58:34', NULL),
(33, 'DAC0GQ', 'Adriana Miranda', '', '3043944967', 'DTF 58x172 CM', 9, 'preparado', 'pago_completo', 'normal', 11, 'completado', NULL, 43000.00, 0.00, '2026-03-05 23:33:58', '2026-03-05 17:26:30', '2026-03-06 19:57:02', NULL),
(34, 'SVGWRH', 'Keiner Miranda', '', '300 6473226', '15 Uniformes deportivos femeninos con talla varias: M=4, XL=2, S=6, XS=2, L=1', 8, 'proceso', 'abono', 'normal', 9, 'pendiente', NULL, 675000.00, 337000.00, '2026-03-06 15:31:12', '2026-03-05 17:31:23', '2026-03-06 15:31:12', NULL),
(35, 'DCVH61', 'ELIZABETH 50 BUZOS L MAXIM', '', '3116623246', '50 BUZOS TALLA L Costurera, Elizabeth, Pendiente por llevarles, pecho izquierdo, espalda y mangas', 13, 'proceso', 'pago_completo', 'normal', 7, 'pendiente', NULL, 750000.00, 0.00, '2026-03-05 18:28:27', '2026-03-05 17:34:34', '2026-03-06 20:33:12', NULL),
(36, 'IP4XR0', 'CAMILA CASTRO', '', '3008981205', 'VINILO TROQUELADO 99X158', 1, 'preparado', 'pago_completo', 'normal', 9, 'completado', NULL, 50000.00, 0.00, '2026-03-05 21:50:27', '2026-03-05 17:35:57', '2026-03-06 19:57:12', NULL),
(37, '5ID6H2', 'MARIA', '', '301 6628149', 'VINILO TROQUELADO 99X54', 1, 'preparado', 'no_pago', 'normal', 9, 'completado', NULL, 80000.00, 0.00, '2026-03-06 23:22:09', '2026-03-05 17:37:07', '2026-03-06 23:22:09', NULL),
(38, 'TEDUG7', 'mirna cardozo', '', '316 4894713', 'vinilo laminado 88x29', 12, 'preparado', 'pago_completo', 'prioridad', 9, 'completado', NULL, 15000.00, 0.00, '2026-03-05 20:41:22', '2026-03-05 17:43:57', '2026-03-05 20:41:22', NULL),
(39, '3D1ESL', 'Prueba', '', '3184483187', 'prueba', 15, 'recepcion', 'no_pago', 'normal', NULL, 'cancelado', NULL, 90.00, 0.00, '2026-03-05 19:06:32', '2026-03-05 19:04:41', '2026-03-05 19:23:39', '2026-03-05 19:23:39'),
(40, '7NTKUA', 'HECTOR RIVERA', '', '300 7518209', '2.000 STICKERS', 8, 'preparado', 'pago_completo', 'normal', 9, 'completado', NULL, 960000.00, 0.00, '2026-03-05 23:08:53', '2026-03-05 19:18:44', '2026-03-06 19:56:31', NULL),
(41, 'BDV3GW', 'JORGE NAVARRO', '', '314 5111500', 'BANER CON TUBOS 70X100 Y BANER CON TUBOS 80X120 y baner con tubos 150x100', 14, 'preparado', 'pago_completo', 'normal', 8, 'completado', NULL, 68000.00, 0.00, '2026-03-06 13:50:36', '2026-03-05 20:15:03', '2026-03-06 20:01:27', NULL),
(42, '7TQ1ZR', 'YESENIA 50 BUZOS L MAXIM', '', '3116623246', '5O BUZOS TALLA L, COSTURERA YESENIA. , Pendiente por llevarles, pecho izquierdo, espalda y mangas', 13, 'proceso', 'pago_completo', 'normal', 7, 'pendiente', NULL, 750000.00, 0.00, '2026-03-05 20:16:45', '2026-03-05 20:15:38', '2026-03-06 20:31:47', NULL),
(43, 'PZCY4S', 'DAVID FLOREZ T', '', '317 4190189', 'BANER CON OJALES 150X70', 14, 'preparado', 'pago_completo', 'prioridad', 8, 'completado', NULL, 22000.00, 0.00, '2026-03-05 22:27:57', '2026-03-05 20:32:39', '2026-03-06 20:01:42', NULL),
(44, 'PIJRWE', 'CARMEN 50 BUZOS M - MAXIM', '', '3116623246', '5O BUZOS TALLA M, COSTURERA CARMEN. , Pendiente por llevarles, pecho izquierdo, espalda y mangas', 13, 'proceso', 'pago_completo', 'largo', 7, 'pendiente', NULL, 750000.00, 0.00, '2026-03-06 20:34:08', '2026-03-05 20:46:38', '2026-03-06 20:34:08', NULL),
(45, '2UOY9G', 'MARU 50 BUZOS XL MAXIM', '', '3116623246', '5O BUZOS TALLA XL, COSTURERA MARIA EUGENIA. , ya tiene todas las piezas', 13, 'proceso', 'pago_completo', 'largo', 7, 'pendiente', NULL, 1.00, 0.00, '2026-03-06 20:34:10', '2026-03-05 20:47:26', '2026-03-06 20:39:11', NULL),
(46, '9LF5UB', 'FELIX', '', '320 7974055', 'TRANSPARENTE LAMINADO Y SIN LAMINAR, Y VINILO BLANCO LAMINADO EN MATE', 12, 'preparado', 'pago_completo', 'normal', 9, 'completado', NULL, 454000.00, 0.00, '2026-03-06 14:11:48', '2026-03-05 20:48:05', '2026-03-06 14:11:48', NULL),
(47, 'BKIAFD', 'Karelys Blanco', '', '302 6070825', '80 Cinta escarapela Maxim\r\nArchivo adjunto en Telegram como:(157X212 Escarapela maxim sublimación) con un tamaño de 74.2MB', 8, 'proceso', 'pago_completo', 'prioridad', 9, 'pendiente', NULL, 300000.00, 0.00, '2026-03-05 21:28:45', '2026-03-05 20:57:46', '2026-03-05 21:28:45', NULL),
(48, 'JI61O9', 'Euclides', '', '3332644603', 'DTF 57X31 CM', 9, 'preparado', 'no_pago', 'normal', 11, 'completado', NULL, 10000.00, 0.00, '2026-03-05 21:26:38', '2026-03-05 20:58:48', '2026-03-05 21:26:38', NULL),
(49, 'YZVC7X', 'Melissa', '', '3217792765', 'DTF 58X133 CM', 9, 'preparado', 'no_pago', 'normal', 11, 'completado', NULL, 33000.00, 0.00, '2026-03-05 22:44:49', '2026-03-05 21:01:48', '2026-03-05 22:44:49', NULL),
(50, 'WIYVBM', 'mello calcomania', '', '300 8973749', 'VINILO LAMINADO Y TORNASOL (CUENTA FACTURADA SEMANALMENTE) 134X61 150X177', 8, 'preparado', 'pago_completo', 'normal', 9, 'completado', NULL, 1000.00, 0.00, '2026-03-06 00:03:22', '2026-03-05 21:14:48', '2026-03-06 00:03:22', NULL),
(51, 'TCU2E7', 'Milton', '', '3208470427', 'DTF 58X84 CM', 9, 'preparado', 'no_pago', 'normal', 11, 'completado', NULL, 17000.00, 0.00, '2026-03-05 22:45:12', '2026-03-05 21:24:19', '2026-03-05 22:45:12', NULL),
(52, '8SH4CL', 'Urban', '', '3147192027', 'DTF 58X65 CM', 9, 'preparado', 'pago_completo', 'normal', 11, 'completado', NULL, 17000.00, 0.00, '2026-03-05 23:58:18', '2026-03-05 23:35:03', '2026-03-06 17:31:34', NULL),
(53, 'WUDR9J', 'Alvaro Payares', '', '3243974898', 'Organización en DTF', 10, 'preparado', 'no_pago', 'normal', 11, 'completado', NULL, 11000.00, 0.00, '2026-03-06 17:15:15', '2026-03-06 00:27:42', '2026-03-06 17:15:15', NULL),
(54, 'X9QMUD', 'NICOLAS PEREZ', '', '321 6289474', 'vinilos y baner de ciudades, ya cotizados previamente.', 8, 'proceso', 'no_pago', 'normal', 9, 'pendiente', NULL, 1000.00, 0.00, '2026-03-06 14:59:16', '2026-03-06 13:50:03', '2026-03-06 14:59:16', NULL),
(55, 'A209PL', 'JESUS BERRIO', '', '301 2289585', 'BANER CON TUBOS 70X100', 14, 'preparado', 'pago_completo', 'normal', 9, 'completado', NULL, 17000.00, 0.00, '2026-03-06 16:25:18', '2026-03-06 13:58:24', '2026-03-06 19:56:11', NULL),
(56, '2ETDY1', 'Gabriel', '', '3117967649', 'DTF 58X50 CM', 9, 'preparado', 'no_pago', 'normal', 11, 'completado', NULL, 10000.00, 0.00, '2026-03-06 16:12:58', '2026-03-06 14:24:12', '2026-03-06 16:12:58', NULL),
(57, '3LI6GW', 'anderson', '', '301 7122077', 'vin lam brill 100x34', 12, 'preparado', 'no_pago', 'normal', 9, 'completado', NULL, 10000.00, 0.00, '2026-03-06 20:07:06', '2026-03-06 14:25:57', '2026-03-06 20:07:06', NULL),
(58, '85QJAG', 'Orlan Martinez', '', '3003541822', 'DTF 58X40', 9, 'preparado', 'no_pago', 'normal', 11, 'completado', NULL, 10000.00, 0.00, '2026-03-06 16:12:35', '2026-03-06 14:27:03', '2026-03-06 16:12:35', NULL),
(59, 'KJWFMX', 'juan carlos garrido', '', '301 7122077', 'canva 70x100', 8, 'proceso', 'no_pago', 'prioridad', 9, 'pendiente', NULL, 6000.00, 0.00, '2026-03-06 15:31:11', '2026-03-06 14:27:29', '2026-03-06 15:31:11', NULL),
(60, '6I9XDY', 'Junior Publicidad', '', '3182356656', 'DTF 58X80 CM', 9, 'preparado', 'pago_completo', 'normal', 11, 'completado', NULL, 20000.00, 0.00, '2026-03-06 16:14:37', '2026-03-06 14:32:38', '2026-03-06 17:10:42', NULL),
(61, 'VRNBAW', 'Omer', '', '3242678253', 'DTF 58X390 CM', 9, 'preparado', 'no_pago', 'normal', 11, 'completado', NULL, 70000.00, 0.00, '2026-03-06 17:06:22', '2026-03-06 14:34:26', '2026-03-06 17:09:36', NULL),
(62, '9AB7NC', 'Jesús Berrio', '', '3012289585', 'DTF 56X114 CM', 9, 'preparado', 'pago_completo', 'normal', 11, 'completado', NULL, 28000.00, 0.00, '2026-03-06 16:31:29', '2026-03-06 14:36:38', '2026-03-06 20:01:11', NULL),
(63, 'XD2ZCH', 'Sandra', '', '3127447368', 'DTF 57X62 CM', 9, 'preparado', 'pago_completo', 'normal', 11, 'completado', NULL, 15000.00, 0.00, '2026-03-06 16:15:00', '2026-03-06 14:39:51', '2026-03-06 17:11:02', NULL),
(64, 'M307ES', 'Alvaro Payares', '', '3243974898', 'DTF 57X29 CM', 9, 'preparado', 'no_pago', 'normal', 11, 'completado', NULL, 11000.00, 0.00, '2026-03-06 16:12:13', '2026-03-06 14:41:59', '2026-03-06 16:12:13', NULL),
(65, 'MJXN1K', 'Lear', '', '3008053552', 'DTF 58X35 CM', 9, 'preparado', 'pago_completo', 'normal', 11, 'completado', NULL, 10000.00, 0.00, '2026-03-06 16:02:24', '2026-03-06 15:03:29', '2026-03-06 16:02:24', NULL),
(66, 'GAPIQL', 'ESTEBAN DOMINGUEZ', '', '311 2780177', 'VIN LAM BRILL  105X35', 12, 'preparado', 'no_pago', 'normal', 9, 'completado', NULL, 10000.00, 0.00, '2026-03-06 20:23:12', '2026-03-06 15:40:15', '2026-03-06 20:23:12', NULL),
(67, 'P4AYH6', 'IVAN MONTERROZA', '', '312 6238251', 'PENDON CON TUBOS 70X100', 14, 'preparado', 'pago_completo', 'prioridad', 9, 'completado', NULL, 30000.00, 0.00, '2026-03-06 16:24:56', '2026-03-06 15:43:58', '2026-03-06 16:24:56', NULL),
(68, '45HUMF', 'Euclides', '', '3332644603', 'DTF 58X73 CM', 9, 'preparado', 'pago_completo', 'normal', 11, 'completado', NULL, 18000.00, 0.00, '2026-03-06 17:06:47', '2026-03-06 16:11:21', '2026-03-06 19:57:05', NULL),
(69, 'ZS3UIO', 'YENDER', '', '300 8201892', 'BANER 100X200 Y VINILO 71X115', 8, 'preparado', 'no_pago', 'normal', 9, 'completado', NULL, 48000.00, 0.00, '2026-03-06 19:37:52', '2026-03-06 16:11:57', '2026-03-06 19:37:52', NULL),
(70, '7QBL21', 'MARCELA', '', '321 6391796', 'UN MILLAR DE STICKERS 5X5', 1, 'preparado', 'no_pago', 'normal', 9, 'completado', NULL, 80000.00, 0.00, '2026-03-06 21:30:22', '2026-03-06 16:14:06', '2026-03-06 21:30:22', NULL),
(71, '8YL2JE', 'Jesús Martínez', '', '3012517570', 'DTF 58X35 CM', 9, 'preparado', 'no_pago', 'normal', 11, 'completado', NULL, 10000.00, 0.00, '2026-03-06 20:49:19', '2026-03-06 17:23:17', '2026-03-06 20:49:19', NULL),
(72, 'TNO6RL', 'Tatiana Arroyo', '', '3107117687', 'DTF 58X246 CM', 9, 'preparado', 'no_pago', 'normal', 11, 'completado', NULL, 61000.00, 0.00, '2026-03-06 20:33:28', '2026-03-06 17:48:46', '2026-03-06 20:33:28', NULL),
(73, 'XVNOID', 'Karen Araujo', '', '3207225365', 'Organización de DTF*', 9, 'preparado', 'no_pago', 'normal', 11, 'completado', NULL, 20000.00, 0.00, '2026-03-06 20:48:48', '2026-03-06 19:18:25', '2026-03-06 20:48:48', NULL),
(74, 'F9UPR1', 'DANNA', '', '323 4502055', 'ELABORACIÓN DE 6 CARTAS', 14, 'recepcion', 'abono', 'normal', NULL, 'pendiente', NULL, 96.00, 48.00, '2026-03-06 20:02:18', '2026-03-06 19:30:54', '2026-03-06 20:02:18', NULL),
(75, 'YALERU', 'Martin Sampayo', '', '3012435925', 'DTF 55x144 cm', 9, 'preparado', 'no_pago', 'normal', 11, 'completado', NULL, 36000.00, 0.00, '2026-03-06 20:38:18', '2026-03-06 19:51:21', '2026-03-06 20:38:18', NULL),
(76, 'KTJ65O', 'CAMILO', '', '310 3685772', 'BANER CON TUBOS 50X70', 14, 'preparado', 'no_pago', 'normal', 9, 'completado', NULL, 11000.00, 0.00, '2026-03-06 20:23:46', '2026-03-06 19:52:49', '2026-03-06 20:23:46', NULL),
(77, 'VURJ2K', 'YESENIA 50 BUZOS L MAXIM', '', '3116623246', 'PAQUETE DE CHAQUETAS TALLA L - COSTURERA YESENIA', 13, 'proceso', 'pago_completo', 'normal', 7, 'pendiente', NULL, 1.00, 0.00, '2026-03-06 20:31:12', '2026-03-06 20:10:59', '2026-03-06 20:31:34', NULL),
(78, '0CKEIQ', 'MARU 50 BUZOS XL MAXIM', '', '3116623246', '5O BUZOS TALLA XL, COSTURERA MARIA EUGENIA. , Pendiente por llevarles, pecho izquierdo y espalda', 13, 'recepcion', 'pago_completo', 'normal', NULL, 'pendiente', NULL, 1.00, 0.00, '2026-03-06 20:36:28', '2026-03-06 20:36:05', '2026-03-06 20:37:34', NULL),
(79, 'ZNPTK8', 'REINA 30 BUZOS M y 1 XL MAXIN', '', '3116623246', 'TODOS LOS BUZOS SON NEGROS, HAY PENDIENTES ALGUNAS PIEZAS, JESUS LAS VA A CORTAS', 13, 'recepcion', 'pago_completo', 'normal', NULL, 'pendiente', NULL, 1.00, 0.00, '2026-03-06 20:56:57', '2026-03-06 20:56:50', '2026-03-06 20:56:57', NULL),
(80, 'L4H2T8', 'Fray Mercado Mercado', '', '301 4862004', 'DTF con medida de 56X61 Fray\r\nAdjunto en Telegram con el siguiente nombre: 56X61 Fray\r\nPesa: 31.8MB', 9, 'preparado', 'no_pago', 'normal', 11, 'completado', NULL, 17000.00, 0.00, '2026-03-06 23:21:50', '2026-03-06 21:41:31', '2026-03-06 23:21:50', NULL),
(81, 'XW9C35', 'Daniela Palmet', '', '320 8424226', '100 tarjeta doble cara de 9X5\r\nAdjunto en Telegram en Archivos Corel con nombre: \"Tarjeta doble cara-Daniela\" con tamaño de 364.9KB', NULL, 'recepcion', 'pago_completo', 'normal', NULL, 'pendiente', NULL, 30000.00, 0.00, '2026-03-06 22:17:39', '2026-03-06 22:17:39', '2026-03-06 22:17:39', NULL),
(82, 'M2WHFO', 'Fernando Blanco', '', '3194584472', 'DTF 58X61 CM', 9, 'preparado', 'no_pago', 'normal', 11, 'completado', NULL, 15000.00, 0.00, '2026-03-06 23:22:21', '2026-03-06 22:28:57', '2026-03-06 23:22:21', NULL),
(83, '0XA7Q8', 'Nicolas Perez', '', '3216289474', 'DTF 58x101 cm', 9, 'preparado', 'no_pago', 'normal', 11, 'completado', NULL, 25000.00, 0.00, '2026-03-06 23:22:43', '2026-03-06 22:34:50', '2026-03-06 23:22:43', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `estado` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id`, `nombre`, `descripcion`, `estado`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Admin', 'Administrador Global del ERP', 1, '2026-02-24 01:48:33', '2026-02-24 01:48:33', NULL),
(2, 'Operador', 'Operador de Planta o Almacén', 1, '2026-02-24 01:48:33', '2026-02-24 01:48:33', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(10) UNSIGNED NOT NULL,
  `rol_id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `foto_perfil` varchar(500) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `estado` tinyint(1) DEFAULT 1,
  `last_activity` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  `ver_precios` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `rol_id`, `nombre`, `email`, `foto_perfil`, `password_hash`, `estado`, `last_activity`, `created_at`, `updated_at`, `deleted_at`, `ver_precios`) VALUES
(1, 1, 'Admin Maestro', 'admin@erp.com', '/public/uploads/perfiles/u1_1772132094.jpg', '$2y$10$B9JRJnZk7RKJ7Ar/iBX/VudnXgQVv1zTuEHK11/4xsFRNzcurQGaq', 1, '2026-03-06 18:37:44', '2026-02-24 06:48:33', '2026-03-06 18:37:44', NULL, 0),
(7, 1, 'Jefe Uriel Calvo', 'uriel', NULL, '$2y$10$S9394S0cQusKeJx8VLETgOu/sbOTNlAOoQaPR1.WvBLFeusqR84ty', 1, '2026-03-07 00:00:15', '2026-03-03 00:44:28', '2026-03-07 00:00:15', NULL, 1),
(8, 2, 'Dissy', 'dissy', NULL, '$2y$10$wn415jV4/VrxiBjgw9e8EeD2tkkeT8AKgPTAsN48.fg6hw58iW7ai', 1, '2026-03-06 20:02:12', '2026-03-04 17:33:51', '2026-03-06 20:02:12', NULL, 1),
(9, 2, 'Isaid Banquez', 'isai', NULL, '$2y$10$XACvXMr.0HkNq9cIK2A1duHzu0Hi8BL0WpZYe3tXbRpouvGHA/yOW', 1, '2026-03-07 00:01:18', '2026-03-04 17:36:40', '2026-03-07 00:01:18', NULL, 0),
(10, 2, 'Valentina', 'valentin', NULL, '$2y$10$v8X2crRdT2k0IzJrtd33y.5t2uwrL7sgKjb1JZvboNEGAFhGvh0C6', 1, '2026-03-06 22:23:15', '2026-03-04 17:50:33', '2026-03-06 22:23:15', NULL, 1),
(11, 2, 'Arelis', 'arelis', NULL, '$2y$10$9oY0qOtOM8bWs3qawBQ46.Woa6GXMZMEtrfj6EbNvHBbhM6YfsCcq', 1, '2026-03-06 23:22:44', '2026-03-04 19:10:38', '2026-03-06 23:22:44', NULL, 1),
(12, 1, 'Spenzer', 'spenzer', NULL, '$2y$10$NSiK3h771J4cVdZTLU2upOZGRNpJPLHRng449hTWdIEpps.0gBnDq', 1, NULL, '2026-03-04 22:55:39', '2026-03-04 22:55:39', NULL, 1),
(13, 2, 'TEST', 'TEST', NULL, '$2y$10$IuU743IXCcXPtJvvdMMoieYcW.7o3ydqBujylz8IljoBGzSDbM4..', 1, '2026-03-05 13:46:25', '2026-03-05 13:45:48', '2026-03-05 13:46:25', NULL, 0),
(14, 2, 'Maria Rebollo', 'maria', NULL, '$2y$10$2t3PM.OH4A94m923Gt1jYOH4NuMlv0MF1EkbxmpN3nOmEouvEaH5m', 1, '2026-03-05 15:12:40', '2026-03-05 15:06:09', '2026-03-05 15:12:40', NULL, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario_areas`
--

CREATE TABLE `usuario_areas` (
  `usuario_id` int(10) UNSIGNED NOT NULL,
  `area_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuario_areas`
--

INSERT INTO `usuario_areas` (`usuario_id`, `area_id`) VALUES
(9, 1),
(12, 1),
(13, 1),
(14, 1),
(8, 2),
(10, 2),
(12, 2),
(14, 2),
(8, 3),
(9, 3),
(10, 3),
(12, 3),
(14, 3),
(12, 4),
(14, 4),
(8, 8),
(9, 8),
(10, 8),
(12, 8),
(14, 8),
(11, 9),
(12, 9),
(14, 9),
(11, 10),
(12, 10),
(14, 10),
(12, 11),
(14, 11),
(8, 12),
(9, 12),
(10, 12),
(12, 12),
(14, 12),
(12, 13),
(14, 13),
(8, 14),
(9, 14),
(10, 14),
(12, 14),
(14, 14);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `workflow_transiciones`
--

CREATE TABLE `workflow_transiciones` (
  `id` int(10) UNSIGNED NOT NULL,
  `area_origen_id` int(10) UNSIGNED NOT NULL,
  `area_destino_id` int(10) UNSIGNED NOT NULL,
  `es_retroceso` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `workflow_transiciones`
--

INSERT INTO `workflow_transiciones` (`id`, `area_origen_id`, `area_destino_id`, `es_retroceso`, `created_at`) VALUES
(1, 1, 2, 0, '2026-02-24 01:48:33'),
(2, 2, 3, 0, '2026-02-24 01:48:33'),
(3, 2, 1, 1, '2026-02-24 01:48:33'),
(4, 3, 2, 1, '2026-02-24 01:48:33');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `archivos`
--
ALTER TABLE `archivos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_archivo_usuario` (`subido_por`),
  ADD KEY `idx_entidad` (`entidad_tipo`,`entidad_id`);

--
-- Indices de la tabla `areas`
--
ALTER TABLE `areas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `auditoria_logs`
--
ALTER TABLE `auditoria_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_audit_usuario` (`usuario_id`),
  ADD KEY `idx_auditoria_entidad` (`entidad_tipo`,`entidad_id`),
  ADD KEY `idx_auditoria_fecha` (`created_at`);

--
-- Indices de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  ADD PRIMARY KEY (`clave`);

--
-- Indices de la tabla `devoluciones`
--
ALTER TABLE `devoluciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_dev_pedido` (`pedido_id`),
  ADD KEY `fk_dev_usuario` (`usuario_id`);

--
-- Indices de la tabla `movimientos_pedido`
--
ALTER TABLE `movimientos_pedido`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_mp_usuario` (`usuario_id`),
  ADD KEY `fk_mp_area` (`area_id`),
  ADD KEY `idx_pedido_historial` (`pedido_id`);

--
-- Indices de la tabla `notificaciones_salientes`
--
ALTER TABLE `notificaciones_salientes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_estado_notif` (`estado`);

--
-- Indices de la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_pago_pedido` (`pedido_id`);

--
-- Indices de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token_seguimiento` (`token_seguimiento`),
  ADD KEY `fk_pedido_asignado` (`asignado_a_usuario_id`),
  ADD KEY `idx_fase_actual` (`fase_actual`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_kanban` (`area_actual_id`,`fase_actual`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_usuario_rol` (`rol_id`);

--
-- Indices de la tabla `usuario_areas`
--
ALTER TABLE `usuario_areas`
  ADD PRIMARY KEY (`usuario_id`,`area_id`),
  ADD KEY `fk_ua_area` (`area_id`);

--
-- Indices de la tabla `workflow_transiciones`
--
ALTER TABLE `workflow_transiciones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ux_origen_destino` (`area_origen_id`,`area_destino_id`),
  ADD KEY `fk_wt_destino` (`area_destino_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `archivos`
--
ALTER TABLE `archivos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `areas`
--
ALTER TABLE `areas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `auditoria_logs`
--
ALTER TABLE `auditoria_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=633;

--
-- AUTO_INCREMENT de la tabla `devoluciones`
--
ALTER TABLE `devoluciones`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `movimientos_pedido`
--
ALTER TABLE `movimientos_pedido`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=552;

--
-- AUTO_INCREMENT de la tabla `notificaciones_salientes`
--
ALTER TABLE `notificaciones_salientes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `pagos`
--
ALTER TABLE `pagos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `workflow_transiciones`
--
ALTER TABLE `workflow_transiciones`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `archivos`
--
ALTER TABLE `archivos`
  ADD CONSTRAINT `fk_archivo_usuario` FOREIGN KEY (`subido_por`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `auditoria_logs`
--
ALTER TABLE `auditoria_logs`
  ADD CONSTRAINT `fk_audit_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `devoluciones`
--
ALTER TABLE `devoluciones`
  ADD CONSTRAINT `fk_dev_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_dev_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `movimientos_pedido`
--
ALTER TABLE `movimientos_pedido`
  ADD CONSTRAINT `fk_mp_area` FOREIGN KEY (`area_id`) REFERENCES `areas` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_mp_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_mp_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD CONSTRAINT `fk_pago_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `fk_pedido_asignado` FOREIGN KEY (`asignado_a_usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `fk_usuario_rol` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuario_areas`
--
ALTER TABLE `usuario_areas`
  ADD CONSTRAINT `fk_ua_area` FOREIGN KEY (`area_id`) REFERENCES `areas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ua_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `workflow_transiciones`
--
ALTER TABLE `workflow_transiciones`
  ADD CONSTRAINT `fk_wt_destino` FOREIGN KEY (`area_destino_id`) REFERENCES `areas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_wt_origen` FOREIGN KEY (`area_origen_id`) REFERENCES `areas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
