-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 14-08-2025 a las 04:27:13
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `sistema_rh`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `departamentos`
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
-- Volcado de datos para la tabla `departamentos`
--

INSERT INTO `departamentos` (`id`, `nombre`, `descripcion`, `sede_id`, `responsable_id`, `estado`, `creado_en`, `actualizado_en`) VALUES
(1, 'VENTAS', 'ESTE DEPARTAMENTO TIENE LA FUNCION DE TODO LO RELACIONADADO A VENTAS, CLIENTES, FACTURACION Y ALTA DE PEDIDOS', 4, NULL, 'activo', '2025-07-08 06:01:53', '2025-07-23 02:56:08'),
(3, 'SISTEMAS', 'SISTEMAS', 2, NULL, 'activo', '2025-07-08 06:55:11', '2025-07-08 06:55:11'),
(4, 'RECURSOS HUMANOS', 'RECURSO HUMANO', 2, NULL, 'activo', '2025-07-08 06:55:41', '2025-07-08 06:55:41'),
(5, 'CONTABILIDAD', 'CONTADORES', 4, NULL, 'activo', '2025-07-08 06:55:59', '2025-07-08 06:55:59'),
(6, 'MARKETING', 'PROMOCIONES Y AYUDA', 2, NULL, 'activo', '2025-07-08 06:56:46', '2025-07-08 06:56:46'),
(7, 'ALMACEN', 'ALMACÉN Y CONTROL DE INVENTARIO, ADMINISTRA LOS LOTES DE TEQUILA RECIBIDOS, CONTROLA FECHAS DE CADUCIDAD Y ROTACIÓN DEL PRODUCTO. ASI COMO LOS EMBARQUES Y ENTREGAS DEL PRODUCTO', 2, NULL, 'activo', '2025-07-08 06:57:34', '2025-07-29 02:25:53'),
(8, 'PRODUCCION', 'PRODUCIR', 2, NULL, 'activo', '2025-07-08 06:58:11', '2025-07-18 02:18:54'),
(9, 'SISTEMAS', 'MANTENIMIENTO DE EQUIPO', 3, NULL, 'activo', '2025-07-08 07:48:45', '2025-07-29 02:28:34'),
(10, 'VENTAS', 'VENTA DE COSAS', 10, NULL, 'activo', '2025-07-08 07:53:51', '2025-08-05 04:56:02'),
(11, 'VENTAS', 'ESTABLECE CONTACTO CON DISTRIBUIDORES, LICORERíAS, TIENDAS Y CLIENTES MAYORISTAS.', 2, NULL, 'activo', '2025-07-15 23:00:17', '2025-07-23 07:14:24'),
(12, 'VENTAS', 'SSS', 7, NULL, 'activo', '2025-07-23 07:43:18', '2025-07-29 02:30:51'),
(13, 'ALMACEN', 'SSSS', 3, NULL, '', '2025-07-23 07:52:51', '2025-07-31 06:17:51'),
(14, 'ALMACEN', 'SS', 7, NULL, 'activo', '2025-07-23 08:33:07', '2025-08-05 04:55:58'),
(15, 'VENTAS', 'SS', 3, NULL, 'activo', '2025-07-23 08:40:09', '2025-07-23 08:40:09'),
(16, 'VEMTAS', 'SSSS', 5, NULL, 'activo', '2025-07-25 07:44:31', '2025-07-25 07:44:31'),
(17, 'VENTAS', 'SSS', 8, NULL, 'activo', '2025-07-25 08:43:26', '2025-07-25 08:43:26'),
(18, 'VENTAS', 'SSS', 6, NULL, 'activo', '2025-07-25 08:44:19', '2025-07-25 08:44:19'),
(19, 'VENTAS', 'SS', 5, NULL, 'activo', '2025-07-25 08:44:53', '2025-07-25 08:44:53'),
(20, 'COMPRAS', 'DDD', 11, NULL, 'activo', '2025-07-25 06:18:36', '2025-07-25 08:48:17'),
(21, 'COMPRAS', 'SSS', 3, NULL, 'activo', '2025-07-25 07:16:13', '2025-07-25 07:16:13'),
(22, 'COMPRAS', 'SSSS', 7, NULL, 'activo', '2025-07-25 07:18:03', '2025-07-29 02:30:55'),
(23, 'ALMACEN', 'ALMACENA', 11, NULL, 'activo', '2025-07-25 07:24:23', '2025-07-29 02:38:12'),
(24, 'SISTEMAS', 'SSS', 11, NULL, 'activo', '2025-07-25 07:29:00', '2025-07-25 08:48:30'),
(25, 'ALMACEN', 'SSS', 10, NULL, 'activo', '2025-07-25 07:43:29', '2025-07-29 02:33:15'),
(26, 'PRODUCCION', 'ENBOTELLADO, ETIQUETADO Y EMBALAJE DEL PRODUCTO', 7, NULL, 'activo', '2025-07-29 02:34:45', '2025-07-29 02:34:45'),
(27, 'LOGISTICA Y TRASPORTES', 'ORGANIZACION Y TRANSPORTACION DEL PRODUCTO TERMINADO', 7, NULL, 'activo', '2025-07-29 02:36:03', '2025-07-29 02:36:03');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sedes`
--

CREATE TABLE `sedes` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
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
-- Volcado de datos para la tabla `sedes`
--

INSERT INTO `sedes` (`id`, `nombre`, `domicilio`, `numero`, `interior`, `colonia`, `municipio`, `estado`, `cp`, `telefono`, `fecha_registro`, `activo`) VALUES
(1, 'SUCURSAL RIO NILO', 'AVENIDA RIO NILO', '7377', '4', 'LOMAS DE LA SOLEDAD', 'TONALA', 'JALISCO', '44870', '3345654334', '2025-06-25 02:48:23', 1),
(2, 'SUCURSAL GUADALAJARA', 'AV JUÁREZ', '652-690', '3', 'ZONA CENTRO', 'GUADALAJARA', 'JALISCO', '44100', '3481255983', '2025-06-25 03:01:22', 1),
(3, 'SUCURSAL AGUASCALIENTES', 'JOSE MARIA MORELOS', '213', '3', 'CENTRO', 'AGUASCALIENTES', 'AGUASCALIENTES', '10002', '3334445665', '2025-06-25 05:30:39', 1),
(4, 'SUCURSAL MONTERREY', 'LEONA VICARIO', '1123', '122', 'COLINAS', 'MONTERREY', 'NUEVO LEON', '64000', '3345654330', '2025-06-25 21:20:27', 1),
(5, 'SUCURSAL LEON', 'LEONES', '123', '2', 'LAS AMERICAS', 'LEON', 'GUANAJUATO', '47182', '2587413697', '2025-06-26 04:02:40', 1),
(6, 'SUCURSAL COLIMA', 'CAMINO REAL', '15978', '', 'PALMAS', 'COLIMA', 'COLIMA', '14789', '3698521477', '2025-06-26 04:31:42', 1),
(7, 'SUCURSAL ARANDAS', 'LEONA VICARIO', '1894', '11', 'CENTRO', 'ARANDAS', 'JALISCO', '47180', '3336985524', '2025-07-15 23:03:45', 1),
(8, 'SUCURSAL CDMX NORTE', 'AV. REFORMA', '789', 'OFICINA 12', 'LINDA VISTA', 'GUSTAVO A. MADERO', 'CIUDAD DE MEXICO', '07300', '5554321098', '2025-07-18 03:26:42', 1),
(9, 'SUCURSAL PUEBLA', 'AV. REFORMA', '321', '', 'LA PAZ', 'PUEBLA', 'PUEBLA', '72160', '2229876543', '2025-07-18 03:27:55', 1),
(10, 'SUCURSAL CANCUN', 'BLVD. KULKULCAN', '101', 'OFICINA 11', 'ZONA HOTELERA', 'BENITO JUAREZ', 'QUINTANA ROO', '77500', '9981234567', '2025-07-18 03:29:41', 1),
(11, 'SUCURSAL TIJUANA', 'LOS LEONES', '12365', '12', 'LAS FLORES', 'MONTERREY', 'BAJA CALIFORNIA', '12345', '1234567899', '2025-07-23 07:49:59', 1),
(12, 'SUCURSAL MICHOACAN', 'CALLE SIN NOMBRE', '2154', '21', 'LAS COLONIAS', 'ZAMORA', 'MICHOACAN', '12345', '1234444444', '2025-07-29 02:45:09', 1),
(13, 'SUCURSAL LAJA', 'CALLE', '1', '1', 'COLONIAL', 'MUNDO', 'JALISCO', '12345', '1234567899', '2025-07-29 23:27:38', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
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
  `departamento_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `usuario`, `contrasena`, `rol`, `nombre_completo`, `numero_empleado`, `correo`, `telefono`, `estado`, `fotografia`, `fecha_registro`, `sede_id`, `departamento_id`) VALUES
(1, 'admin', '$2y$10$Td35RNEnT4e9thjrbh41Qu2Fk/gorMnkEpv8Hb0W25h/gwyTdHiea', 'admin', 'ADMINISTRADOR', 'EM213', 'administrador@gmail.com', '', 'activo', '6854c56b3dbfe_imagen.jpeg', '2025-06-20 02:20:27', NULL, NULL),
(2, 'victor', '$2y$10$BGLTkqMHoUjnuVri.We79OkVOZDc2ydZalPrDIGxc4LZlXkIFcGwa', 'rh', 'VICTOR DANIEL GONZALEZ GARCIA ', 'ADMI12', 'victorgonzaga18@gmail.com', '', 'activo', '6854c76003d7c_rostro1.jpg', '2025-06-20 02:28:48', NULL, NULL),
(3, 'bryan ', '$2y$10$V2144zJUUNGzO6IUCFmBCueLt1FzmgInOeSTxzXNyN/gXUYw4/7Zu', 'gerente', 'BRYAN RAMIREZ', '111111', 'bryan.ramirez9289@alumnos.udg.mx', '', 'activo', '6854ddf966f2a_rostro1.jpg', '2025-06-20 04:05:13', 2, NULL),
(4, 'luis', '$2y$10$ZtNqTmrRIEHpgQQUuXlNxuVEhQ1j.Z35xBXkSkdVkzN0044IkgRvC', 'jefe_area', 'LUIS JESUS ESCAREñO GARCIA', '222222', 'luis.egarcia@alumnos.udg.mx', '', 'activo', '68551a670bd42_imagen.jpeg', '2025-06-20 04:16:58', NULL, NULL),
(14, 'MANUEL', '$2y$10$5J3eRPb0L1Yfnw0qN2/dIugriHeImZijRYCp8R915/vw2n2Srr7pe', 'jefe_area', 'ARMANDO PULIDO GOMEZ', '095432SS', 'administradoradministrador@gmail.com', '3214567899', 'activo', NULL, '2025-08-05 06:19:38', 3, 21),
(15, 'MARIA', '$2y$10$0P8Sitdw0mWVCiGlLSwsye16oYAtI.IRt7fs6SeblJQFOYYeJ91da', 'rh', 'MARIA ROSALES ROJAS', '095432122', 'administradoradministrador@gmail.com', '3214567899', 'activo', NULL, '2025-08-05 06:31:33', 2, 4),
(16, 'VERO', '$2y$10$WQoRG9z63w7E68fbeejSu.ontWQGdxrTaBl3TqBYpfjntiohsIXoS', 'rh', 'VERONICA GONZALEZ GARCIA', '333333', 'administradoradministrador@gmail.com', '1234567898', 'activo', NULL, '2025-08-05 06:57:28', 2, 4),
(17, 'JOSE', '$2y$10$1H7/.ISr0r5gN54CjszsauTJuJshlR7/tFTY62x5tqSNDQAzz/bfq', 'jefe_area', 'JOSE', '095432', 'administradoradministrador@gmail.com', '555555555', 'activo', NULL, '2025-08-12 04:31:38', 3, 15),
(18, 'MARTIN', '$2y$10$wi3ykGqZrafgg5etBxbrUOVkx1hJ/02wTRqz9AYE7f2g5IFePBiQK', 'jefe_area', 'LUIS MARTIN CARDENAS', '1478522', 'administradoradministrador@gmail.com', '258258258', 'activo', NULL, '2025-08-12 04:33:26', 3, 15);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `departamentos`
--
ALTER TABLE `departamentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sede_id` (`sede_id`),
  ADD KEY `responsable_id` (`responsable_id`);

--
-- Indices de la tabla `sedes`
--
ALTER TABLE `sedes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario` (`usuario`),
  ADD UNIQUE KEY `numero_empleado` (`numero_empleado`),
  ADD KEY `fk_usuario_sede` (`sede_id`),
  ADD KEY `fk_usuario_departamento` (`departamento_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `departamentos`
--
ALTER TABLE `departamentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT de la tabla `sedes`
--
ALTER TABLE `sedes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `departamentos`
--
ALTER TABLE `departamentos`
  ADD CONSTRAINT `departamentos_ibfk_1` FOREIGN KEY (`sede_id`) REFERENCES `sedes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `departamentos_ibfk_2` FOREIGN KEY (`responsable_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `usuarios`
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
