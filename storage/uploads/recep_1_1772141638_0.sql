-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 26-02-2026 a las 01:25:00
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
(1, 'pedido', 7, '.DeepLearningwithPython.pdf.icloud', 'recep_7_1772039806_0.icloud', 'application/octet-stream', 1, '2026-02-25 17:16:46', NULL);

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
(1, 'Área de Corte', 'Estación inicial', '<svg width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><path d=\"M12 2v20\"></path><path d=\"M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6\"></path></svg>', 24, 1, 1, '2026-02-24 01:48:33', '2026-02-25 22:50:34', NULL),
(2, 'Área de Diseño', 'Estación de confección', '<svg width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><path d=\"M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z\"></path><polyline points=\"3.27 6.96 12 12.01 20.73 6.96\"></polyline><line x1=\"12\" y1=\"22.08\" x2=\"12\" y2=\"12\"></line></svg>', 48, 1, 2, '2026-02-24 01:48:33', '2026-02-25 22:50:27', NULL),
(3, 'Área de Empaque', 'Empaque', '<svg width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><circle cx=\"12\" cy=\"12\" r=\"10\"></circle><line x1=\"2\" y1=\"12\" x2=\"22\" y2=\"12\"></line><path d=\"M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z\"></path></svg>', 12, 1, 3, '2026-02-24 01:48:33', '2026-02-25 22:50:39', NULL),
(4, 'Area de Prueba', 'TestTest', '<svg width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><rect x=\"3\" y=\"3\" width=\"18\" height=\"18\" rx=\"2\" ry=\"2\"></rect><line x1=\"3\" y1=\"9\" x2=\"21\" y2=\"9\"></line><line x1=\"9\" y1=\"21\" x2=\"9\" y2=\"9\"></line></svg>', 24, 1, 4, '2026-02-24 02:59:15', '2026-02-25 22:52:43', NULL),
(5, 'Verdureria', '', '<svg width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><path d=\"M18 6 7 17l-5-5\"></path><path d=\"m22 10-7.5 7.5L13 16\"></path></svg>', 24, 1, 5, '2026-02-24 18:58:35', '2026-02-25 22:52:45', NULL),
(6, 'area de patacones', '', '<svg width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><rect x=\"2\" y=\"3\" width=\"20\" height=\"14\" rx=\"2\" ry=\"2\"></rect><line x1=\"8\" y1=\"21\" x2=\"16\" y2=\"21\"></line><line x1=\"12\" y1=\"17\" x2=\"12\" y2=\"21\"></line></svg>', 24, 1, 6, '2026-02-25 22:53:07', '2026-02-25 22:53:14', NULL);

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
(1, 1, 'pedido', 6, 'actualizar', 'Traslado libre a recepcion - Movimiento manual en el Kanban.', NULL, NULL, '127.0.0.1', '2026-02-25 17:48:03'),
(2, 1, 'configuracion', 0, 'actualizar', 'Modificó configuraciones: icon_dashboard, icon_recepcion, icon_reportes, icon_reportes_pedidos, icon_usuarios, icon_areas, icon_configuracion', NULL, NULL, '127.0.0.1', '2026-02-25 18:35:53'),
(3, 1, 'configuracion', 0, 'actualizar', 'Modificó configuraciones: icon_dashboard, icon_recepcion, icon_reportes, icon_reportes_pedidos, icon_usuarios, icon_areas, icon_configuracion', NULL, NULL, '127.0.0.1', '2026-02-25 18:35:57'),
(4, 1, 'configuracion', 0, 'actualizar', 'Modificó configuraciones: icon_dashboard, icon_recepcion, icon_reportes, icon_reportes_pedidos, icon_usuarios, icon_areas, icon_configuracion', NULL, NULL, '127.0.0.1', '2026-02-25 18:36:02'),
(5, 1, 'configuracion', 0, 'actualizar', 'Modificó configuraciones: icon_dashboard, icon_recepcion, icon_reportes, icon_reportes_pedidos, icon_usuarios, icon_areas, icon_configuracion', NULL, NULL, '127.0.0.1', '2026-02-25 18:37:46'),
(6, 1, 'pedido', 6, 'actualizar', 'Traslado libre a proceso - Movimiento manual en el Kanban.', NULL, NULL, '127.0.0.1', '2026-02-25 18:38:07'),
(7, 1, 'pedido', 5, 'actualizar', 'Traslado libre a preparado - Movimiento manual en el Kanban.', NULL, NULL, '127.0.0.1', '2026-02-25 18:38:07'),
(8, 1, 'pedido', 1, 'actualizar', 'Traslado libre a proceso - Movimiento manual en el Kanban.', NULL, NULL, '127.0.0.1', '2026-02-25 18:38:08'),
(9, 1, 'pedido', 6, 'actualizar', 'Traslado libre a recepcion - Movimiento manual en el Kanban.', NULL, NULL, '127.0.0.1', '2026-02-25 18:38:10'),
(10, 1, 'pedido', 1, 'actualizar', 'Marcado como Preparado - El operador terminó el trabajo del área.', NULL, NULL, '127.0.0.1', '2026-02-25 18:38:12'),
(11, 1, 'configuracion', 0, 'actualizar', 'Modificó configuraciones: icon_dashboard, icon_recepcion, icon_reportes, icon_reportes_pedidos, icon_usuarios, icon_areas, icon_configuracion', NULL, NULL, '127.0.0.1', '2026-02-25 20:07:17'),
(12, 1, 'configuracion', 0, 'actualizar', 'Modificó configuraciones: icon_dashboard, icon_recepcion, icon_reportes, icon_reportes_pedidos, icon_usuarios, icon_areas, icon_configuracion', NULL, NULL, '127.0.0.1', '2026-02-25 21:24:41'),
(13, 1, 'configuracion', 0, 'actualizar', 'Modificó configuraciones: icon_dashboard, icon_recepcion, icon_reportes, icon_reportes_pedidos, icon_usuarios, icon_areas, icon_configuracion', NULL, NULL, '127.0.0.1', '2026-02-25 21:37:06'),
(14, 1, 'configuracion', 0, 'actualizar', 'Modificó configuraciones: empresa_nombre, empresa_logo', NULL, NULL, '127.0.0.1', '2026-02-25 22:35:16'),
(15, 1, 'configuracion', 0, 'actualizar', 'Modificó configuraciones: empresa_nombre, empresa_logo', NULL, NULL, '127.0.0.1', '2026-02-25 22:36:57'),
(16, 1, 'pedido', 1, 'actualizar', 'Transferido - Pedido enviado al área ID: 6', NULL, NULL, '127.0.0.1', '2026-02-25 22:53:26'),
(17, NULL, 'pedido', 7, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '127.0.0.1', '2026-02-26 00:13:03'),
(18, NULL, 'pedido', 7, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '127.0.0.1', '2026-02-26 00:13:11'),
(19, 1, 'pedido', 8, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '181.128.99.196', '2026-02-26 00:18:48'),
(20, NULL, 'pedido', 8, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '181.128.99.196', '2026-02-26 00:18:57'),
(21, NULL, 'pedido', 7, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '181.128.99.196', '2026-02-26 00:26:29'),
(22, 1, 'pedido', 9, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '181.128.99.196', '2026-02-26 00:28:48'),
(23, NULL, 'pedido', 9, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '181.128.99.196', '2026-02-26 00:29:03'),
(24, NULL, 'pedido', 9, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '181.128.99.196', '2026-02-26 00:29:55'),
(25, 1, 'pedido', 10, 'crear', 'Creó un pedido nuevo en Recepción', NULL, NULL, '181.128.99.196', '2026-02-26 00:39:38'),
(26, NULL, 'pedido', 10, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '181.128.99.196', '2026-02-26 00:40:00'),
(27, NULL, 'pedido', 10, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '181.128.99.196', '2026-02-26 00:42:40'),
(28, NULL, 'pedido', 10, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', NULL, NULL, '181.128.99.196', '2026-02-26 00:44:17'),
(29, 1, 'pedido', 10, 'actualizar', 'Traslado libre a proceso - Movimiento manual en el Kanban.', NULL, NULL, '181.128.99.196', '2026-02-26 00:44:59');

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
('empresa_logo', 'data:image/webp;base64,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', '2026-02-25 22:36:57'),
('empresa_nombre', 'BANNER', '2026-02-25 22:36:57'),
('fondo_login', '/public/img/fondos/fondo_login_1772066552.jpg', '2026-02-26 00:42:32'),
('icon_areas', '<svg width=\"22\" height=\"22\" viewBox=\"0 0 24 24\"><circle cx=\"12\" cy=\"12\" r=\"9\" fill=\"rgba(244,114,182,0.2)\"/><circle cx=\"12\" cy=\"12\" r=\"3\" fill=\"#f472b6\"/><path d=\"M12 3v2M12 19v2M3 12h2M19 12h2\" stroke=\"#f472b6\" stroke-width=\"2\" stroke-linecap=\"round\"/></svg>', '2026-02-25 21:37:06'),
('icon_configuracion', '<svg width=\"22\" height=\"22\" viewBox=\"0 0 24 24\"><circle cx=\"12\" cy=\"12\" r=\"9\" fill=\"rgba(244,114,182,0.15)\"/><path d=\"M12 7v6M12 17h.01\" stroke=\"#f472b6\" stroke-width=\"3\" stroke-linecap=\"round\"/></svg>', '2026-02-25 21:37:06'),
('icon_dashboard', '<svg width=\"22\" height=\"22\" viewBox=\"0 0 24 24\"><rect x=\"3\" y=\"3\" width=\"7\" height=\"7\" rx=\"1\" fill=\"rgba(244,114,182,0.3)\"/><rect x=\"14\" y=\"3\" width=\"7\" height=\"7\" rx=\"1\" fill=\"rgba(244,114,182,0.3)\"/><rect x=\"14\" y=\"14\" width=\"7\" height=\"7\" rx=\"1\" fill=\"#f472b6\"/><rect x=\"3\" y=\"14\" width=\"7\" height=\"7\" rx=\"1\" fill=\"#f472b6\"/></svg>', '2026-02-25 21:37:06'),
('icon_recepcion', '<svg width=\"22\" height=\"22\" viewBox=\"0 0 24 24\"><path d=\"M4 6h16v12H4z\" fill=\"rgba(244,114,182,0.25)\"/><rect x=\"2\" y=\"4\" width=\"20\" height=\"4\" rx=\"1\" fill=\"#f472b6\"/></svg>', '2026-02-25 21:37:06'),
('icon_reportes', '<svg width=\"22\" height=\"22\" viewBox=\"0 0 24 24\"><path d=\"M6 4h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z\" fill=\"rgba(244,114,182,0.2)\"/><path d=\"M8 9h8M8 13h5\" stroke=\"#f472b6\" stroke-width=\"2\" stroke-linecap=\"round\"/></svg>', '2026-02-25 21:37:06'),
('icon_reportes_pedidos', '<svg width=\"22\" height=\"22\" viewBox=\"0 0 24 24\"><path d=\"M7 2h10a2 2 0 0 1 2 2v16a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2z\" fill=\"rgba(244,114,182,0.25)\"/><rect x=\"9\" y=\"1\" width=\"6\" height=\"3\" rx=\"1\" fill=\"#f472b6\"/></svg>', '2026-02-25 21:37:06'),
('icon_usuarios', '<svg width=\"22\" height=\"22\" viewBox=\"0 0 24 24\"><circle cx=\"12\" cy=\"8\" r=\"4\" fill=\"rgba(244,114,182,0.3)\"/><path d=\"M4 20c0-3.3 3.6-6 8-6s8 2.7 8 6\" stroke=\"#f472b6\" stroke-width=\"2\" stroke-linecap=\"round\" fill=\"none\"/></svg>', '2026-02-25 21:37:06'),
('mostrar_credenciales', '1', '2026-02-25 21:24:41'),
('onurix_api_id', '7389', '2026-02-24 15:37:46'),
('onurix_api_key', 'baf0076e7d995fc544c21cea4fdf898ce00612f268dc5f38c3565', '2026-02-24 15:37:46'),
('sms_crear', 'Hola {nombre}, tu pedido {link_seguimiento} ha sido creado. Gracias por confiar en {empresa}', '2026-02-24 16:32:24'),
('sms_finalizar', 'Hola {nombre}, su pedido ha sido terminado, ya lo puede recoger en {empresa}', '2026-02-24 16:32:24');

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
  `usuario_id` int(10) UNSIGNED NOT NULL,
  `area_id` int(10) UNSIGNED DEFAULT NULL,
  `accion` varchar(100) NOT NULL,
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `movimientos_pedido`
--

INSERT INTO `movimientos_pedido` (`id`, `pedido_id`, `usuario_id`, `area_id`, `accion`, `observaciones`, `created_at`) VALUES
(1, 1, 2, 1, 'Inicia Proceso', 'Operador toma el pedido.', '2026-02-24 02:43:07'),
(2, 2, 1, 1, 'Traslado libre a recepcion', 'Movimiento manual en el Kanban.', '2026-02-24 03:49:30'),
(3, 2, 1, 1, 'Traslado libre a proceso', 'Movimiento manual en el Kanban.', '2026-02-24 03:49:44'),
(4, 2, 1, 1, 'Traslado libre a recepcion', 'Movimiento manual en el Kanban.', '2026-02-24 03:50:48'),
(5, 2, 1, 1, 'Inicia Proceso', 'Operador toma el pedido.', '2026-02-24 03:50:51'),
(6, 2, 1, 1, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-02-24 03:50:52'),
(7, 2, 1, 1, 'Traslado libre a proceso', 'Movimiento manual en el Kanban.', '2026-02-24 03:51:01'),
(8, 2, 1, 1, 'Traslado libre a recepcion', 'Movimiento manual en el Kanban.', '2026-02-24 03:51:03'),
(9, 3, 1, 1, 'Traslado libre a proceso', 'Movimiento manual en el Kanban.', '2026-02-24 03:51:05'),
(10, 4, 1, 2, 'Traslado libre a proceso', 'Movimiento manual en el Kanban.', '2026-02-24 04:38:08'),
(11, 4, 1, 2, 'Traslado libre a preparado', 'Movimiento manual en el Kanban.', '2026-02-24 04:38:10'),
(12, 4, 1, 2, 'Traslado libre a proceso', 'Movimiento manual en el Kanban.', '2026-02-24 04:38:12'),
(13, 4, 1, 2, 'Traslado libre a recepcion', 'Movimiento manual en el Kanban.', '2026-02-24 04:38:13'),
(14, 4, 1, 2, 'Traslado libre a proceso', 'Movimiento manual en el Kanban.', '2026-02-24 04:38:18'),
(15, 4, 1, 2, 'Traslado libre a recepcion', 'Movimiento manual en el Kanban.', '2026-02-24 04:38:22'),
(16, 3, 1, 1, 'Traslado libre a preparado', 'Movimiento manual en el Kanban.', '2026-02-24 04:38:33'),
(17, 1, 1, 1, 'Traslado libre a preparado', 'Movimiento manual en el Kanban.', '2026-02-24 14:33:01'),
(18, 1, 1, 1, 'Traslado libre a proceso', 'Movimiento manual en el Kanban.', '2026-02-24 14:33:03'),
(19, 1, 1, 1, 'Traslado libre a preparado', 'Movimiento manual en el Kanban.', '2026-02-24 14:34:00'),
(20, 1, 1, 1, 'Traslado libre a proceso', 'Movimiento manual en el Kanban.', '2026-02-24 14:34:03'),
(21, 2, 1, 1, 'Traslado libre a proceso', 'Movimiento manual en el Kanban.', '2026-02-24 14:34:14'),
(22, 1, 1, 1, 'Traslado libre a preparado', 'Movimiento manual en el Kanban.', '2026-02-24 14:34:17'),
(23, 1, 1, 1, 'Traslado libre a recepcion', 'Movimiento manual en el Kanban.', '2026-02-24 16:39:44'),
(24, 1, 1, 1, 'Inicia Proceso', 'Operador toma el pedido.', '2026-02-24 16:40:38'),
(25, 3, 1, 1, 'Transferido', 'Pedido enviado al área ID: 2', '2026-02-24 16:57:19'),
(26, 2, 1, 1, 'Traslado libre a preparado', 'Movimiento manual en el Kanban.', '2026-02-24 16:58:23'),
(27, 2, 1, 1, 'Transferido', 'Pedido enviado al área ID: 3', '2026-02-24 16:58:31'),
(28, 2, 1, 3, 'Traslado libre a proceso', 'Movimiento manual en el Kanban.', '2026-02-24 16:58:35'),
(29, 2, 1, 3, 'Traslado libre a preparado', 'Movimiento manual en el Kanban.', '2026-02-24 16:58:36'),
(30, 2, 1, 3, 'Transferido', 'Pedido enviado al área ID: 4', '2026-02-24 16:58:42'),
(31, 1, 1, 1, 'Traslado libre a recepcion', 'Movimiento manual en el Kanban.', '2026-02-24 17:22:37'),
(32, 1, 1, 1, 'Traslado libre a proceso', 'Movimiento manual en el Kanban.', '2026-02-24 17:22:39'),
(33, 1, 1, 1, 'Traslado libre a preparado', 'Movimiento manual en el Kanban.', '2026-02-24 17:22:40'),
(34, 5, 1, 1, 'Creado', 'Pedido ingresado desde Recepción', '2026-02-24 18:43:23'),
(35, 5, 1, 1, 'Editado', 'Datos del pedido actualizados desde Recepción', '2026-02-24 18:44:48'),
(36, 5, 1, 1, 'Traslado libre a proceso', 'Movimiento manual en el Kanban.', '2026-02-24 18:47:24'),
(37, 2, 1, 4, 'Inicia Proceso', 'Operador toma el pedido.', '2026-02-24 18:58:40'),
(38, 2, 1, 4, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-02-24 18:58:41'),
(39, 5, 1, 1, 'Traslado libre a recepcion', 'Movimiento manual en el Kanban.', '2026-02-25 15:10:25'),
(40, 5, 1, 1, 'Traslado libre a proceso', 'Movimiento manual en el Kanban.', '2026-02-25 15:10:27'),
(41, 6, 1, 1, 'Creado', 'Pedido ingresado desde Recepción', '2026-02-25 15:11:40'),
(42, 6, 1, 1, 'Traslado libre a proceso', 'Movimiento manual en el Kanban.', '2026-02-25 16:36:22'),
(43, 6, 1, 1, 'Enviado a Área', 'Pedido enviado al área: Área de Corte', '2026-02-25 16:53:32'),
(44, 6, 1, 1, 'Inicia Proceso', 'Operador toma el pedido.', '2026-02-25 16:53:37'),
(45, 7, 1, 1, 'Creado', 'Pedido ingresado desde Recepción', '2026-02-25 17:16:46'),
(46, 7, 1, 1, 'Adjunto', 'Archivos adjuntados al crear: .DeepLearningwithPython.pdf.icloud ', '2026-02-25 17:16:46'),
(47, 7, 1, 1, 'Enviado a Área', 'Pedido enviado al área: Área de Empaque', '2026-02-25 17:17:01'),
(48, 7, 1, 3, 'Inicia Proceso', 'Operador toma el pedido.', '2026-02-25 17:17:04'),
(49, 6, 1, 1, 'Traslado libre a recepcion', 'Movimiento manual en el Kanban.', '2026-02-25 17:48:03'),
(50, 6, 1, 1, 'Traslado libre a proceso', 'Movimiento manual en el Kanban.', '2026-02-25 18:38:07'),
(51, 5, 1, 1, 'Traslado libre a preparado', 'Movimiento manual en el Kanban.', '2026-02-25 18:38:07'),
(52, 1, 1, 1, 'Traslado libre a proceso', 'Movimiento manual en el Kanban.', '2026-02-25 18:38:08'),
(53, 6, 1, 1, 'Traslado libre a recepcion', 'Movimiento manual en el Kanban.', '2026-02-25 18:38:10'),
(54, 1, 1, 1, 'Marcado como Preparado', 'El operador terminó el trabajo del área.', '2026-02-25 18:38:12'),
(55, 1, 1, 1, 'Transferido', 'Pedido enviado al área ID: 6', '2026-02-25 22:53:26'),
(56, 8, 1, 1, 'Creado', 'Pedido ingresado desde Recepción', '2026-02-26 00:18:48'),
(57, 9, 1, 1, 'Creado', 'Pedido ingresado desde Recepción', '2026-02-26 00:28:47'),
(58, 10, 1, 1, 'Creado', 'Pedido ingresado desde Recepción', '2026-02-26 00:39:38'),
(59, 10, 1, 1, 'Traslado libre a proceso', 'Movimiento manual en el Kanban.', '2026-02-26 00:44:59');

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
(1, NULL, 'Nike Inc.', NULL, NULL, NULL, 6, 'recepcion', 'no_pago', 'normal', NULL, 'pendiente', NULL, 0.00, 0.00, '2026-02-25 22:53:26', '2026-02-24 01:48:33', '2026-02-25 22:53:26', NULL),
(2, NULL, 'Adidas Corp.', NULL, NULL, NULL, 4, 'preparado', 'no_pago', 'normal', 1, 'en_curso', NULL, 0.00, 0.00, '2026-02-24 18:58:41', '2026-02-24 01:48:33', '2026-02-24 18:58:41', NULL),
(3, NULL, 'Under Armour', NULL, NULL, NULL, 2, 'recepcion', 'no_pago', 'normal', NULL, 'en_curso', NULL, 0.00, 0.00, '2026-02-24 16:57:19', '2026-02-24 01:48:33', '2026-02-24 16:57:19', NULL),
(4, NULL, 'Puma SE', NULL, NULL, NULL, 2, 'recepcion', 'no_pago', 'normal', NULL, 'pendiente', NULL, 0.00, 0.00, '2026-02-24 04:38:22', '2026-02-24 01:48:33', '2026-02-24 04:38:22', NULL),
(5, NULL, 'Admin Pricing Tester', 'test@example.com', '123123', '', 1, 'preparado', 'pago_completo', 'normal', 1, 'pendiente', NULL, 95000.00, 95000.00, '2026-02-25 18:38:07', '2026-02-24 18:43:23', '2026-02-25 18:38:07', NULL),
(6, NULL, 'daniel andres florez atencia', '', '3184483187', 'banner', 1, 'recepcion', 'abono', 'normal', NULL, 'pendiente', NULL, 200000.00, 50000.00, '2026-02-25 18:38:10', '2026-02-25 15:11:40', '2026-02-25 18:38:10', NULL),
(7, 'XJB4YT', 'eliza', '', '3184483187', 'hola', 3, 'proceso', 'abono', 'normal', 1, 'pendiente', NULL, 14000.00, 100.00, '2026-02-25 17:17:04', '2026-02-25 17:16:46', '2026-02-25 17:17:04', NULL),
(8, '9MGLT3', 'asdasdasd', '', '3162573607', 'asd', 1, 'recepcion', 'no_pago', 'normal', NULL, 'pendiente', NULL, 0.00, 0.00, '2026-02-26 00:18:48', '2026-02-26 00:18:48', '2026-02-26 00:18:48', NULL),
(9, 'TSO8X3', 'onuriss', '', '3162573607', '', 1, 'recepcion', 'no_pago', 'normal', NULL, 'pendiente', NULL, 0.00, 0.00, '2026-02-26 00:28:47', '2026-02-26 00:28:47', '2026-02-26 00:28:47', NULL),
(10, '15YUSR', 'queso', '', '3162573607', 'asd', 1, 'proceso', 'no_pago', 'normal', 1, 'pendiente', NULL, 0.00, 0.00, '2026-02-26 00:44:59', '2026-02-26 00:39:38', '2026-02-26 00:44:59', NULL),
(11, NULL, 'Test', NULL, NULL, NULL, NULL, 'recepcion', 'no_pago', 'normal', NULL, 'pendiente', NULL, 0.00, 0.00, '2026-02-26 01:01:44', '2026-02-26 01:01:44', '2026-02-26 01:01:44', NULL);

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

INSERT INTO `usuarios` (`id`, `rol_id`, `nombre`, `email`, `password_hash`, `estado`, `last_activity`, `created_at`, `updated_at`, `deleted_at`, `ver_precios`) VALUES
(1, 1, 'Admin Maestro', 'admin@erp.com', '$2y$10$7lRV9q/.NvmNA.cjbaPM2e.ffpHabwOpGtOLL0MnQg1Yqw7RYocgi', 1, '2026-02-26 01:17:05', '2026-02-24 01:48:33', '2026-02-26 01:17:05', NULL, 0),
(2, 2, 'Juan Pérez', 'juan@erp.com', '$2y$10$hyCDeWapa/JM2DSF.J2KiuN4hacjpIWgw.p5u8VRmYBSLTWAlJFLK', 1, '2026-02-24 18:41:31', '2026-02-24 01:48:33', '2026-02-24 18:41:31', NULL, 0);

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
(2, 1),
(2, 2),
(2, 3);

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `areas`
--
ALTER TABLE `areas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `auditoria_logs`
--
ALTER TABLE `auditoria_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT de la tabla `devoluciones`
--
ALTER TABLE `devoluciones`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `movimientos_pedido`
--
ALTER TABLE `movimientos_pedido`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
  ADD CONSTRAINT `fk_mp_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE;

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
