-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql306.infinityfree.com
-- Generation Time: Oct 18, 2025 at 07:42 PM
-- Server version: 11.4.7-MariaDB
-- PHP Version: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `if0_40170523_sistema_rh`
--

-- --------------------------------------------------------

--
-- Table structure for table `departamentos`
--

CREATE TABLE `departamentos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text NOT NULL,
  `sede_id` int(11) NOT NULL,
  `responsable_id` int(11) DEFAULT NULL,
  `estado` enum('activo','inactivo') DEFAULT 'activo',
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_en` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departamentos`
--

INSERT INTO `departamentos` (`id`, `nombre`, `descripcion`, `sede_id`, `responsable_id`, `estado`, `creado_en`, `actualizado_en`) VALUES
(1, 'VENTAS', 'ESTE DEPARTAMENTO TIENE LA FUNCION DE TODO LO RELACIONADADO A VENTAS, CLIENTES, FACTURACION Y ALTA DE PEDIDOS', 4, NULL, 'activo', '2025-07-08 06:01:53', '2025-07-23 02:56:08'),
(3, 'SISTEMAS', 'SISTEMAS', 2, NULL, 'activo', '2025-07-08 06:55:11', '2025-07-08 06:55:11'),
(4, 'RECURSOS HUMANOS', 'RECURSO HUMANO', 2, NULL, 'activo', '2025-07-08 06:55:41', '2025-07-08 06:55:41'),
(5, 'CONTABILIDAD', 'CONTADORES', 4, NULL, 'activo', '2025-07-08 06:55:59', '2025-07-08 06:55:59'),
(6, 'MARKETING', 'PROMOCIONES Y AYUDA', 2, NULL, 'activo', '2025-07-08 06:56:46', '2025-07-08 06:56:46'),
(7, 'ALMACEN', 'ALMACÉN Y CONTROL DE INVENTARIO, ADMINISTRA LOS LOTES DE TEQUILA RECIBIDOS, CONTROLA FECHAS DE CADUCIDAD Y ROTACIÓN DEL PRODUCTO. ASI COMO LOS EMBARQUES Y ENTREGAS DEL PRODUCTO', 2, NULL, 'activo', '2025-07-08 06:57:34', '2025-09-28 00:55:53'),
(8, 'PRODUCCION', 'PRODUCIR', 2, NULL, 'activo', '2025-07-08 06:58:11', '2025-07-18 02:18:54'),
(9, 'SISTEMAS', 'MANTENIMIENTO DE EQUIPO', 3, NULL, 'activo', '2025-07-08 07:48:45', '2025-09-14 00:00:33'),
(27, 'LOGISTICA Y TRASPORTES', 'ORGANIZACION Y TRANSPORTACION DEL PRODUCTO TERMINADO', 7, 3, 'activo', '2025-07-29 02:36:03', '2025-09-28 00:55:53'),
(28, 'MANTENIMIENTO', 'SE ENCARGA DEL MANTENIMIENTO DE LOS EDIFICIOS Y INFRAESTRUCTURA DE LA EMPRESA', 7, 4, 'activo', '2025-09-14 21:42:11', '2025-09-28 00:57:30');

-- --------------------------------------------------------

--
-- Table structure for table `notificaciones`
--

CREATE TABLE `notificaciones` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `mensaje` varchar(255) NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `leida` tinyint(1) NOT NULL DEFAULT 0,
  `expira_en` datetime DEFAULT NULL,
  `creado_en` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `postulantes_por_vacante`
--

CREATE TABLE `postulantes_por_vacante` (
  `id` int(11) NOT NULL,
  `solicitud_id` int(11) NOT NULL,
  `Nombre completo` varchar(200) NOT NULL,
  `Telefono` varchar(30) DEFAULT NULL,
  `Correo` varchar(160) NOT NULL,
  `RecruitmentStrategy` enum('Recomendado','Portales de empleo','Headhunting') NOT NULL,
  `Carrera` varchar(200) NOT NULL,
  `EducationLevel` enum('Preparatoria','Licenciatura','Maestría','Doctorado') NOT NULL,
  `ExperienceYears` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `Área de experiencia` text DEFAULT NULL,
  `Competencias técnicas` text DEFAULT NULL,
  `Inglés_requerido` enum('NO','BASICO','INTERMEDIO','AVANZADO','NATIVO') NOT NULL DEFAULT 'NO',
  `Inglés_postulante` enum('NO','BASICO','INTERMEDIO','AVANZADO','NATIVO') NOT NULL DEFAULT 'NO',
  `PersonalityScore` tinyint(3) UNSIGNED DEFAULT NULL,
  `SkillScore` tinyint(3) UNSIGNED DEFAULT NULL,
  `InterviewScore` tinyint(3) UNSIGNED DEFAULT NULL,
  `Puntaje de evaluación` tinyint(3) UNSIGNED DEFAULT NULL,
  `Viabilidad` enum('ALTA','MEDIA','BAJA') DEFAULT NULL,
  `PosiciónRanking` int(10) UNSIGNED DEFAULT NULL,
  `Decisión_Final` enum('CONTRATADO','NO_CONTRATADO','PENDIENTE') NOT NULL DEFAULT 'PENDIENTE',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `postulantes_por_vacante`
--

INSERT INTO `postulantes_por_vacante` (`id`, `solicitud_id`, `Nombre completo`, `Telefono`, `Correo`, `RecruitmentStrategy`, `Carrera`, `EducationLevel`, `ExperienceYears`, `Área de experiencia`, `Competencias técnicas`, `Inglés_requerido`, `Inglés_postulante`, `PersonalityScore`, `SkillScore`, `InterviewScore`, `Puntaje de evaluación`, `Viabilidad`, `PosiciónRanking`, `Decisión_Final`, `created_at`, `updated_at`) VALUES
(1, 13, 'MARIO ALBERTO', '3248524396', 'jefedearea@mail.com', 'Recomendado', 'ADMINISTRACION', 'Licenciatura', 1, 'LOGISTICA', 'PAQUETERIA OFICCE', 'BASICO', 'NATIVO', 80, 90, 90, 87, 'ALTA', 1, 'PENDIENTE', '2025-09-28 22:19:45', '2025-09-29 00:49:48'),
(2, 13, 'CARLOS EDUARDO ALVAREZ GARCIA', '3481234567', 'carlos@gmail.com', 'Recomendado', '', 'Preparatoria', 2, 'ADMINISTRATIVA | LOGISTICA | ATENCION A CLIENTES', 'PAQUETERIA OFICCE, LIDERAZGO', 'BASICO', 'BASICO', 80, 90, 90, 87, 'ALTA', 2, 'PENDIENTE', '2025-09-28 23:34:13', '2025-09-29 00:46:56'),
(3, 13, 'RAUL HERNANDEZ MENDEZ', '3369988774', 'raul4324@gmail.com', 'Recomendado', 'ADMINISTRACION', 'Licenciatura', 3, 'AREAS ADMINISTRATIVAS', 'EXCEL, LIDERAZGO, CERTIFICACIONES,', 'BASICO', 'NO', NULL, NULL, NULL, NULL, NULL, 3, 'PENDIENTE', '2025-09-29 01:50:39', '2025-09-29 01:50:55');

-- --------------------------------------------------------

--
-- Table structure for table `sedes`
--

CREATE TABLE `sedes` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `gerente_id` int(11) DEFAULT NULL,
  `domicilio` varchar(150) NOT NULL,
  `numero` varchar(10) NOT NULL,
  `interior` varchar(10) DEFAULT NULL,
  `colonia` varchar(100) NOT NULL,
  `municipio` varchar(100) NOT NULL,
  `estado` varchar(100) NOT NULL,
  `cp` varchar(10) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sedes`
--

INSERT INTO `sedes` (`id`, `nombre`, `gerente_id`, `domicilio`, `numero`, `interior`, `colonia`, `municipio`, `estado`, `cp`, `telefono`, `fecha_registro`, `activo`) VALUES
(1, 'SUCURSAL RIO NILO', NULL, 'AVENIDA RIO NILO', '7377', '4', 'LOMAS DE LA SOLEDAD', 'TONALA', 'JALISCO', '44870', '3345654334', '2025-06-25 02:48:23', 1),
(2, 'SUCURSAL GUADALAJARA', NULL, 'AV JUÁREZ', '652-690', '3', 'ZONA CENTRO', 'GUADALAJARA', 'JALISCO', '44100', '3481255983', '2025-06-25 03:01:22', 1),
(3, 'SUCURSAL AGUASCALIENTES', NULL, 'JOSE MARIA MORELOS', '213', '3', 'CENTRO', 'AGUASCALIENTES', 'AGUASCALIENTES', '10002', '3334445665', '2025-06-25 05:30:39', 1),
(4, 'SUCURSAL MONTERREY', NULL, 'LEONA VICARIO', '1123', '122', 'COLINAS', 'MONTERREY', 'NUEVO LEON', '64000', '3345654330', '2025-06-25 21:20:27', 1),
(5, 'SUCURSAL LEON', NULL, 'LEONES', '123', '2', 'LAS AMERICAS', 'LEON', 'GUANAJUATO', '47182', '2587413697', '2025-06-26 04:02:40', 1),
(6, 'SUCURSAL COLIMA', NULL, 'CAMINO REAL', '15978', '', 'PALMAS', 'COLIMA', 'COLIMA', '14789', '3698521477', '2025-06-26 04:31:42', 1),
(7, 'SUCURSAL ARANDAS', 2, 'LEONA VICARIO', '1894', '11', 'CENTRO', 'ARANDAS', 'JALISCO', '47180', '3336985524', '2025-07-15 23:03:45', 1),
(8, 'SUCURSAL CDMX NORTE', NULL, 'AV. REFORMA', '789', 'OFICINA 12', 'LINDA VISTA', 'GUSTAVO A. MADERO', 'CIUDAD DE MEXICO', '07300', '5554321098', '2025-07-18 03:26:42', 1),
(9, 'SUCURSAL PUEBLA', NULL, 'AV. REFORMA', '321', '', 'LA PAZ', 'PUEBLA', 'PUEBLA', '72160', '2229876543', '2025-07-18 03:27:55', 1),
(10, 'SUCURSAL CANCUN', NULL, 'BLVD. KULKULCAN', '101', 'OFICINA 11', 'ZONA HOTELERA', 'BENITO JUAREZ', 'QUINTANA ROO', '77500', '9981234567', '2025-07-18 03:29:41', 1),
(11, 'SUCURSAL LAREDO', NULL, 'LOS LEONES', '12365', '12', 'LAS FLORES', 'MEXICALI', 'BAJA CALIFORNIA', '12345', '1234567899', '2025-07-23 07:49:59', 1),
(12, 'SUCURSAL MICHOACAN', NULL, 'CALLE SIN NOMBRE', '2154', '21', 'LAS COLONIAS', 'ZAMORA', 'MICHOACAN', '12345', '1234444444', '2025-07-29 02:45:09', 1),
(13, 'SUCURSAL LAJA', 25, 'CALLE', '1', '1', 'COLONIAL', 'MUNDO', 'JALISCO', '12345', '1234567899', '2025-07-29 23:27:38', 1),
(14, 'SUCURSAL  TIJUANA', NULL, 'AV GONZALEZ GALLO', '1231', '12-PISO2', 'CORTEZ', 'TIJUANA', 'BAJA CALIFORNIA', '22000', '1212323443', '2025-09-11 05:12:40', 1),
(15, 'SUCURSAL OAXACA', NULL, 'MATAMOROS', '214', '', 'CENTRO', 'OAXACA DE JUAREZ', 'OAXACA', '68000', '1234567898', '2025-09-14 03:33:23', 1),
(16, 'LA PERLA', NULL, 'LEONA VICARIO', '198', '251', 'CENTRO', 'GUADALAJARA', 'JALISCO', '47180', '3481252596', '2025-09-14 08:11:59', 0);

-- --------------------------------------------------------

--
-- Table structure for table `solicitudes`
--

CREATE TABLE `solicitudes` (
  `id` int(11) NOT NULL,
  `autor_id` int(11) NOT NULL,
  `sede_id` int(11) NOT NULL,
  `departamento_id` int(11) NOT NULL,
  `titulo` varchar(160) DEFAULT NULL,
  `puesto` varchar(120) NOT NULL,
  `vacantes` int(11) NOT NULL DEFAULT 1,
  `fecha_ingreso_deseada` date DEFAULT NULL,
  `tipo_contrato` varchar(40) DEFAULT NULL,
  `modalidad` varchar(20) DEFAULT NULL,
  `horario` varchar(100) DEFAULT NULL,
  `salario_min` decimal(12,2) DEFAULT NULL,
  `salario_max` decimal(12,2) DEFAULT NULL,
  `escolaridad_min` tinyint(4) DEFAULT NULL,
  `carrera_estudiada` text DEFAULT NULL,
  `experiencia_anios` int(11) DEFAULT NULL,
  `area_experiencia` text DEFAULT NULL,
  `ingles_combo` varchar(20) DEFAULT NULL,
  `competencias_json` text DEFAULT NULL,
  `motivo` varchar(30) DEFAULT NULL,
  `reemplazo_de` varchar(120) DEFAULT NULL,
  `prioridad` varchar(20) NOT NULL DEFAULT 'NORMAL',
  `autorizada_por` int(11) DEFAULT NULL,
  `autorizada_en` datetime DEFAULT NULL,
  `rechazada_por` int(11) DEFAULT NULL,
  `rechazada_en` datetime DEFAULT NULL,
  `rechazo_motivo` text DEFAULT NULL,
  `justificacion` text NOT NULL,
  `responsabilidades` text DEFAULT NULL,
  `estado_actual` enum('ENVIADA','EN_REV_GER','APROBADA','BUSCANDO','EN_ENTREVISTA','EN_DECISION','CERRADA','RECHAZADA') NOT NULL DEFAULT 'ENVIADA',
  `creada_en` datetime NOT NULL DEFAULT current_timestamp(),
  `actualizado_en` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `solicitudes`
--

INSERT INTO `solicitudes` (`id`, `autor_id`, `sede_id`, `departamento_id`, `titulo`, `puesto`, `vacantes`, `fecha_ingreso_deseada`, `tipo_contrato`, `modalidad`, `horario`, `salario_min`, `salario_max`, `escolaridad_min`, `carrera_estudiada`, `experiencia_anios`, `area_experiencia`, `ingles_combo`, `competencias_json`, `motivo`, `reemplazo_de`, `prioridad`, `autorizada_por`, `autorizada_en`, `rechazada_por`, `rechazada_en`, `rechazo_motivo`, `justificacion`, `responsabilidades`, `estado_actual`, `creada_en`, `actualizado_en`) VALUES
(12, 4, 7, 28, 'VACANTE EN MANTENIMIENTO', 'VENDEOR', 1, '2025-10-18', NULL, NULL, NULL, NULL, NULL, 2, 'INGENIERIA MECANICA | INGENIERIA MECATRONICA | INGENIERIA INDUSTRIAL | INGENIERIA ELECTRICA | INGENIERIA ELECTRONICA', 2, 'MANTENIMIENTO | SOPORTE', 'BASICO', '[\"TOMA DE DECISIONES\",\"INNOVACION\",\"GESTION DEL TIEMPO\",\"ANALISIS\",\"RESOLUCION DE PROBLEMAS\",\"TRABAJO EN EQUIPO\"]', 'CRECIMIENTO', NULL, 'NORMAL', NULL, NULL, NULL, NULL, NULL, 'SE NECESITA MAS PERSONAL PARA EL AREA DE MANTENIMETO', 'REPARACION Y MANTENIMIETO DE INSTALACIONES COMO APOYO EN AREAS CRITICAS DE LA EMPRESA', 'BUSCANDO', '2025-09-27 19:03:04', '2025-09-28 02:15:14'),
(13, 3, 7, 27, 'REMPLAZO DE CAPTURISTA', 'ADMINISTRATIVO', 1, '2025-12-06', 'DETERMINADO', 'PRESENCIAL', 'L-S 8-5', '2900.00', '3400.00', 2, 'ADMINISTRACION DE EMPRESAS | LOGISTICA | CIENCIAS DE LA COMPUTACION', NULL, 'ADMINISTRATIVA | LOGISTICA | ATENCION A CLIENTES', 'BASICO', '[\"ANALISIS\",\"PLANEACION\",\"TRABAJO EN EQUIPO\"]', 'REEMPLAZO', 'IGENIERO JUAN ESCUTIA', 'NORMAL', NULL, NULL, NULL, NULL, NULL, 'EL ING YA NOS AVISO DE SU RENUNCIA ESPERAMOS UN MES ANTES DE LA FECHA DESEADA DE INGRESO PARA CAPACITAR A SU REMPLAZO', 'CAPTURA Y GESTION DE DOCUMENTOS', 'EN_ENTREVISTA', '2025-09-27 19:13:37', '2025-09-28 23:04:19'),
(14, 4, 7, 28, 'AYUDANTE DE MANTENIMIENTO', 'VENTAS', 1, '2025-11-29', 'INDETERMINADO', 'PRESENCIAL', 'L-S 8-5', '3000.00', '45000.00', 1, 'INGENIERIA MECANICA', NULL, 'MANTENIMIENTO', '0', '[\"TRABAJO EN EQUIPO\",\"INNOVACION\",\"RESOLUCION DE PROBLEMAS\"]', 'CRECIMIENTO', NULL, 'URGENTE', NULL, NULL, NULL, NULL, NULL, 'EL PERSONAL DE MANTENIMIETO REQUIERE PERSONAL PARA APOYAR AL AREA', 'APOYO AL PERSONAL DE MANTENIMIENTO', 'ENVIADA', '2025-09-28 13:48:32', NULL),
(15, 3, 7, 27, 'PERSONAL DE ALMACEN', 'MONTACARGUISTA', 3, '2025-10-11', 'DETERMINADO', 'PRESENCIAL', 'L-S 8-5', '1800.00', '1900.00', 1, NULL, 2, 'LOGISTICA | ALMACEN | CADENA DE SUMINISTRO | OPERACIONES', '0', '[\"TRABAJO EN EQUIPO\",\"COMUNICACION EFECTIVA\",\"ANALISIS\",\"ORGANIZACION\",\"ADAPTABILIDAD\",\"GESTION DEL TIEMPO\"]', 'CRECIMIENTO', NULL, 'URGENTE', NULL, NULL, NULL, NULL, NULL, 'SE REQUIERE PERSONAL YA QUE EL PERSONAL ANTERIOR RENUNCIO', NULL, 'APROBADA', '2025-09-29 01:39:33', '2025-09-29 01:42:28');

-- --------------------------------------------------------

--
-- Table structure for table `solicitudes_comentarios`
--

CREATE TABLE `solicitudes_comentarios` (
  `id` int(11) NOT NULL,
  `solicitud_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `comentario` text NOT NULL,
  `creado_en` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `solicitudes_comentarios`
--

INSERT INTO `solicitudes_comentarios` (`id`, `solicitud_id`, `usuario_id`, `comentario`, `creado_en`) VALUES
(2, 12, 4, 'el suldo depende de los que autorice gerencia o en su caso rh', '2025-09-27 19:04:17'),
(3, 12, 2, 'muy bien por mi parte si es neceario contar con mas personal el sueldo lo dejo a decision de rh dependiendo del candidato', '2025-09-27 19:06:36'),
(4, 12, 2, 'APROBADA por Gerente.', '2025-09-27 19:06:40'),
(5, 13, 1, 'APROBADA por Gerente.', '2025-09-27 23:35:18'),
(6, 13, 16, 'RH inició la búsqueda de candidatos.', '2025-09-28 01:03:08'),
(7, 12, 1, 'RH inició la búsqueda de candidatos.', '2025-09-28 02:15:14'),
(8, 15, 2, 'APROBADA por Gerente.', '2025-09-29 01:42:28');

-- --------------------------------------------------------

--
-- Table structure for table `solicitudes_seguimiento`
--

CREATE TABLE `solicitudes_seguimiento` (
  `id` int(11) NOT NULL,
  `solicitud_id` int(11) NOT NULL,
  `estado_anterior` varchar(20) DEFAULT NULL,
  `estado_nuevo` varchar(20) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `nota` text DEFAULT NULL,
  `creado_en` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `solicitudes_seguimiento`
--

INSERT INTO `solicitudes_seguimiento` (`id`, `solicitud_id`, `estado_anterior`, `estado_nuevo`, `usuario_id`, `nota`, `creado_en`) VALUES
(12, 12, NULL, 'ENVIADA', 4, 'Solicitud enviada por el autor', '2025-09-27 19:03:04'),
(13, 13, NULL, 'ENVIADA', 3, 'Solicitud enviada por el autor', '2025-09-27 19:13:37'),
(14, 14, NULL, 'ENVIADA', 4, 'Solicitud enviada por el autor', '2025-09-28 13:48:32'),
(15, 15, NULL, 'ENVIADA', 3, 'Solicitud enviada por el autor', '2025-09-29 01:39:33');

-- --------------------------------------------------------

--
-- Table structure for table `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `usuario` varchar(50) DEFAULT NULL,
  `contrasena` varchar(255) DEFAULT NULL,
  `rol` enum('admin','rh','gerente','jefe_area') NOT NULL,
  `nombre_completo` varchar(100) DEFAULT NULL,
  `numero_empleado` varchar(20) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `telefono` varchar(15) NOT NULL,
  `estado` enum('activo','inactivo') DEFAULT 'activo',
  `fotografia` varchar(255) DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `sede_id` int(11) DEFAULT NULL,
  `departamento_id` int(11) DEFAULT NULL,
  `uniq_gerente_sede` int(11) GENERATED ALWAYS AS (case when `rol` = 'gerente' and `estado` = 'activo' then `sede_id` else NULL end) VIRTUAL,
  `uniq_jefe_sede` int(11) GENERATED ALWAYS AS (case when `rol` = 'jefe_area' and `estado` = 'activo' then `sede_id` else NULL end) VIRTUAL,
  `uniq_jefe_dep` int(11) GENERATED ALWAYS AS (case when `rol` = 'jefe_area' and `estado` = 'activo' then `departamento_id` else NULL end) VIRTUAL,
  `failed_attempts` int(11) NOT NULL DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `last_failed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `usuarios`
--

INSERT INTO `usuarios` (`id`, `usuario`, `contrasena`, `rol`, `nombre_completo`, `numero_empleado`, `correo`, `telefono`, `estado`, `fotografia`, `fecha_registro`, `sede_id`, `departamento_id`, `failed_attempts`, `locked_until`, `last_login`, `last_failed_at`) VALUES
(1, 'ADMIN', '$2y$10$Td35RNEnT4e9thjrbh41Qu2Fk/gorMnkEpv8Hb0W25h/gwyTdHiea', 'admin', 'ADMINISTRADOR', 'EM213', 'administrador@gmail.com', '', 'activo', '20250910_071908_91848cd3.jpg', '2025-06-20 02:20:27', NULL, NULL, 0, NULL, '2025-10-18 16:10:37', '2025-09-28 15:24:06'),
(2, 'VICTOR', '$2y$10$BGLTkqMHoUjnuVri.We79OkVOZDc2ydZalPrDIGxc4LZlXkIFcGwa', 'gerente', 'VICTOR DANIEL GONZALEZ GARCIA', 'ADMI12', 'victorgonzaga18@gmail.com', '', 'activo', NULL, '2025-06-20 02:28:48', 7, NULL, 0, NULL, '2025-09-29 01:56:48', NULL),
(3, 'BRYAN', '$2y$10$V2144zJUUNGzO6IUCFmBCueLt1FzmgInOeSTxzXNyN/gXUYw4/7Zu', 'jefe_area', 'BRYAN RAMIREZ', '111111', 'bryan.ramirez9289@alumnos.udg.mx', '', 'activo', '20250910_071948_55a51df0.jpg', '2025-06-20 04:05:13', 7, 27, 0, NULL, '2025-09-29 01:36:29', NULL),
(4, 'LUIS', '$2y$10$ZtNqTmrRIEHpgQQUuXlNxuVEhQ1j.Z35xBXkSkdVkzN0044IkgRvC', 'jefe_area', 'LUIS JESUS ESCAREñO GARCIA', '222222', 'luis.egarcia@alumnos.udg.mx', '3214567899', 'activo', '20250928_025750_9d45cf7a.jpg', '2025-06-20 04:16:58', 7, 28, 0, NULL, '2025-09-29 01:21:15', NULL),
(16, 'VERO', '$2y$10$WQoRG9z63w7E68fbeejSu.ontWQGdxrTaBl3TqBYpfjntiohsIXoS', 'rh', 'VERONICA GONZALEZ GARCIA', '333333', 'administradoradministrador@gmail.com', '1234567898', 'activo', '20250928_025806_de09c81b.jpg', '2025-08-05 06:57:28', 7, NULL, 0, NULL, '2025-09-29 01:57:19', '2025-09-27 19:13:55'),
(25, 'MARTIN', '$2y$10$ig2wiwUyB/zU9PIlcyzwkOehNafnLkZX7777Wv38kb7fqRpyrN4.q', 'gerente', 'MARTIN LOPEZ LOPEZ', '215488', 'martin@gmial.com', '3312456987', 'activo', '20250929_092709_7b212da1.png', '2025-09-29 07:27:09', 13, NULL, 0, NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `departamentos`
--
ALTER TABLE `departamentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sede_id` (`sede_id`),
  ADD KEY `responsable_id` (`responsable_id`);

--
-- Indexes for table `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notif_user` (`usuario_id`),
  ADD KEY `idx_notif_leida` (`leida`);

--
-- Indexes for table `postulantes_por_vacante`
--
ALTER TABLE `postulantes_por_vacante`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_solicitud_correo` (`solicitud_id`,`Correo`),
  ADD KEY `idx_solicitud` (`solicitud_id`);

--
-- Indexes for table `sedes`
--
ALTER TABLE `sedes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`),
  ADD KEY `fk_sedes_gerente` (`gerente_id`);

--
-- Indexes for table `solicitudes`
--
ALTER TABLE `solicitudes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sede` (`sede_id`),
  ADD KEY `idx_depto` (`departamento_id`),
  ADD KEY `idx_estado` (`estado_actual`),
  ADD KEY `idx_autor` (`autor_id`);

--
-- Indexes for table `solicitudes_comentarios`
--
ALTER TABLE `solicitudes_comentarios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sc_sol` (`solicitud_id`),
  ADD KEY `idx_sc_user` (`usuario_id`);

--
-- Indexes for table `solicitudes_seguimiento`
--
ALTER TABLE `solicitudes_seguimiento`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sg_sol` (`solicitud_id`),
  ADD KEY `idx_sg_user` (`usuario_id`);

--
-- Indexes for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario` (`usuario`),
  ADD UNIQUE KEY `numero_empleado` (`numero_empleado`),
  ADD UNIQUE KEY `uq_gerente_sede` (`uniq_gerente_sede`),
  ADD UNIQUE KEY `uq_jefe_sede_dep` (`uniq_jefe_sede`,`uniq_jefe_dep`),
  ADD UNIQUE KEY `uq_usuario` (`usuario`),
  ADD UNIQUE KEY `uq_num_empleado` (`numero_empleado`),
  ADD KEY `fk_usuario_sede` (`sede_id`),
  ADD KEY `fk_usuario_departamento` (`departamento_id`),
  ADD KEY `idx_usuarios_estado` (`estado`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `departamentos`
--
ALTER TABLE `departamentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `postulantes_por_vacante`
--
ALTER TABLE `postulantes_por_vacante`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `sedes`
--
ALTER TABLE `sedes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `solicitudes`
--
ALTER TABLE `solicitudes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `solicitudes_comentarios`
--
ALTER TABLE `solicitudes_comentarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `solicitudes_seguimiento`
--
ALTER TABLE `solicitudes_seguimiento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `departamentos`
--
ALTER TABLE `departamentos`
  ADD CONSTRAINT `departamentos_ibfk_1` FOREIGN KEY (`sede_id`) REFERENCES `sedes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `departamentos_ibfk_2` FOREIGN KEY (`responsable_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD CONSTRAINT `fk_notif_user` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Constraints for table `postulantes_por_vacante`
--
ALTER TABLE `postulantes_por_vacante`
  ADD CONSTRAINT `fk_ppv_solicitud` FOREIGN KEY (`solicitud_id`) REFERENCES `solicitudes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `sedes`
--
ALTER TABLE `sedes`
  ADD CONSTRAINT `fk_sedes_gerente` FOREIGN KEY (`gerente_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `solicitudes`
--
ALTER TABLE `solicitudes`
  ADD CONSTRAINT `fk_sol_autor` FOREIGN KEY (`autor_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `fk_sol_dep` FOREIGN KEY (`departamento_id`) REFERENCES `departamentos` (`id`),
  ADD CONSTRAINT `fk_sol_sede` FOREIGN KEY (`sede_id`) REFERENCES `sedes` (`id`);

--
-- Constraints for table `solicitudes_comentarios`
--
ALTER TABLE `solicitudes_comentarios`
  ADD CONSTRAINT `fk_sc_sol` FOREIGN KEY (`solicitud_id`) REFERENCES `solicitudes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_sc_user` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Constraints for table `solicitudes_seguimiento`
--
ALTER TABLE `solicitudes_seguimiento`
  ADD CONSTRAINT `fk_sg_sol` FOREIGN KEY (`solicitud_id`) REFERENCES `solicitudes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_sg_user` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Constraints for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `fk_usuario_departamento` FOREIGN KEY (`departamento_id`) REFERENCES `departamentos` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_usuario_sede` FOREIGN KEY (`sede_id`) REFERENCES `sedes` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_usuarios_departamento_id` FOREIGN KEY (`departamento_id`) REFERENCES `departamentos` (`id`),
  ADD CONSTRAINT `fk_usuarios_sede_id` FOREIGN KEY (`sede_id`) REFERENCES `sedes` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
