-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 11-07-2025 a las 05:03:11
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
(1, 'VENTAS', 'ESTE DEPARTAMENTO TIENE LA FUNCION DE TODO LO RELACIONADADO A VENTAS, CLIENTES, FACTURACION Y ALTA DE PEDIDOS', 3, NULL, 'activo', '2025-07-08 06:01:53', '2025-07-08 06:01:53'),
(3, 'SISTEMAS', 'SISTEMAS', 2, NULL, 'activo', '2025-07-08 06:55:11', '2025-07-08 06:55:11'),
(4, 'RECURSOS HUMANOS', 'RECURSO HUMANO', 2, NULL, 'activo', '2025-07-08 06:55:41', '2025-07-08 06:55:41'),
(5, 'CONTABILIDAD', 'CONTADORES', 4, NULL, 'activo', '2025-07-08 06:55:59', '2025-07-08 06:55:59'),
(6, 'MARKETING', 'PROMOCIONES Y AYUDA', 2, NULL, 'activo', '2025-07-08 06:56:46', '2025-07-08 06:56:46'),
(7, 'CALIDAD', 'RESGUARDO DE LAS NORMAS DE CALIDAD LA CALIDAD SE REFIERE A LAS CARACTERÍSTICAS DE UN PRODUCTO O SERVICIO QUE LO HACEN VALIOSO Y SATISFACTORIO PARA EL CLIENTE, CUMPLIENDO CON SUS NECESIDADES Y EXPECTATIVAS. ES UN CONCEPTO AMPLIO QUE PUEDE SER TANTO OBJETIVO (MEDIBLE) COMO SUBJETIVO (BASADO EN LA PERCEPCIÓN). \r\nEN TÉRMINOS MÁS ESPECÍFICOS, LA CALIDAD PUEDE ENTENDERSE COMO:\r\nGRADO DE CUMPLIMIENTO DE REQUISITOS:\r\nUN PRODUCTO O SERVICIO SE CONSIDERA DE ALTA CALIDAD SI CUMPLE CON LAS ESPECIFICACIONES Y ESTÁNDARES ESTABLECIDOS, TANTO POR EL PRODUCTOR COMO POR EL CLIENTE. \r\nADECUACIÓN PARA EL USO:\r\nLA CALIDAD TAMBIÉN SE RELACIONA CON LA CAPACIDAD DE UN PRODUCTO O SERVICIO PARA CUMPLIR CON SU FUNCIÓN Y SATISFACER LAS NECESIDADES DEL USUARIO. \r\nSATISFACCIÓN DEL CLIENTE:\r\nLA CALIDAD SE MANIFIESTA EN LA PERCEPCIÓN Y SATISFACCIÓN DEL CLIENTE CON EL PRODUCTO O SERVICIO, INCLUYENDO ASPECTOS COMO LA DURABILIDAD, EL DESEMPEÑO, LA CONFIABILIDAD, ETC. \r\nMEJORA CONTINUA:\r\nLA GESTIÓN DE LA CALIDAD IMPLICA UN ENFOQUE CONTINUO EN LA MEJORA DE PROCESOS Y PRODUCTOS PARA SATISFACER LAS NECESIDADES CAMBIANTES DE LOS CLIENTES Y DEL MERCADO.', 2, NULL, 'activo', '2025-07-08 06:57:34', '2025-07-08 06:57:34'),
(8, 'PRODUCCION', 'SSSS', 2, NULL, 'activo', '2025-07-08 06:58:11', '2025-07-08 06:58:11'),
(9, 'VENTAS', 'SSS', 3, NULL, 'activo', '2025-07-08 07:48:45', '2025-07-08 07:48:45'),
(10, 'VENTAS', 'SS', 3, NULL, 'activo', '2025-07-08 07:53:51', '2025-07-08 07:53:51');

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
(3, 'SUCURSAL AGUASCALIENTES', 'JOSE MARIA MORELOS', '213', '3', 'CENTRO', 'AGUASCALIENTES', 'AGUASCALIENTES', '20000', '3334445665', '2025-06-25 05:30:39', 1),
(4, 'SUCURSAL MONTERREY', 'LEONA VICARIO', '1123', '122', 'COLINAS', 'MONTERREY', 'NUEVO LEON', '64000', '3345654330', '2025-06-25 21:20:27', 1),
(5, 'SUCURSAL LEON', 'LEONES', '123', '2', 'LAS AMERICAS', 'LEON', 'GUANAJUATO', '47182', '2587413697', '2025-06-26 04:02:40', 1),
(6, 'SUCURSAL COLIMA', 'CAMINO REAL', '15978', '', 'PALMAS', 'COLIMA', 'COLIMA', '14789', '3698521477', '2025-06-26 04:31:42', 1);

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
  `departamento` varchar(100) DEFAULT NULL,
  `sede` varchar(100) DEFAULT NULL,
  `numero_empleado` varchar(20) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `estado` enum('activo','inactivo') DEFAULT 'activo',
  `fotografia` varchar(255) DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `usuario`, `contrasena`, `rol`, `nombre_completo`, `departamento`, `sede`, `numero_empleado`, `correo`, `estado`, `fotografia`, `fecha_registro`) VALUES
(1, 'admin', '$2y$10$Td35RNEnT4e9thjrbh41Qu2Fk/gorMnkEpv8Hb0W25h/gwyTdHiea', 'admin', 'ADMINISTRADOR', 'SISTEMAS ', 'GUADALAJARA', 'EM213', 'administrador@gmail.com', 'activo', '6854c56b3dbfe_imagen.jpeg', '2025-06-20 02:20:27'),
(2, 'victor', '$2y$10$BGLTkqMHoUjnuVri.We79OkVOZDc2ydZalPrDIGxc4LZlXkIFcGwa', 'admin', 'VICTOR DANIEL GONZALEZ GARCIA ', 'SISTEMAS ', 'GUADALAJARA', 'ADMI12', 'victorgonzaga18@gmail.com', 'activo', '6854c76003d7c_rostro1.jpg', '2025-06-20 02:28:48'),
(3, 'bryan ', '$2y$10$V2144zJUUNGzO6IUCFmBCueLt1FzmgInOeSTxzXNyN/gXUYw4/7Zu', 'gerente', 'BRYAN RAMIREZ', 'GERENTE REGIONAL ', 'GUADALAJARA ', '111111', 'bryan.ramirez9289@alumnos.udg.mx', 'activo', '6854ddf966f2a_rostro1.jpg', '2025-06-20 04:05:13'),
(4, 'luis', '$2y$10$ZtNqTmrRIEHpgQQUuXlNxuVEhQ1j.Z35xBXkSkdVkzN0044IkgRvC', 'rh', 'LUIS JESUS ESCAREñO GARCIA', 'RECURSOS HUMANOS ', 'GUADALAJARA', '222222', 'luis.egarcia@alumnos.udg.mx', 'activo', '68551a670bd42_imagen.jpeg', '2025-06-20 04:16:58'),
(5, 'VERO', '$2y$10$6Nsg6hfK3ACramV9oqoam.H51c/0Ki4gl2tuPWQu.eBZ90i00uHz.', 'jefe_area', 'VERONICA GONZALEZ GARCIA', 'RECURSOS HUMANOS', 'GUADALAJARA', '789456', 'vertonica@gmail.com', 'activo', '68576b44c8e93_imagen.jpeg', '2025-06-22 02:32:36'),
(6, 'JOSE ', '$2y$10$/k0zfiw1sslXSgomJylqEeY8rPfojwuHvMGQT4HrR/XN8djaUqxkm', 'jefe_area', 'JUAN JOSE CABRERA MARTINEZ', 'CONTABILIDAD', 'MONTERREY', '369258', 'juan@gmial.com', 'activo', '68576c80726f9_rostro1.jpg', '2025-06-22 02:37:52'),
(7, 'carlos', '$2y$10$vHgsymYW8gWwKfz87XWlPeIAIxVMfur103mTIf9Pl4xpBelMlX3yW', 'jefe_area', 'CARLOS TORRES GARCIA', 'VENTAS', 'CD MEXICO ', '147852', 'carlos@gmial.com', 'activo', '68576d2517f02_rostro1.jpg', '2025-06-22 02:40:37'),
(8, 'maria', '$2y$10$6cRUKmzGeC0tjASnHrZbYed1eY2pafhI0DrP5cmxrMEM68j7Uh8Tq', 'jefe_area', 'MARIA DEL CARMEN  GARCIA ', 'MARKETING', 'BAJA CALIFORNIA ', '258741', 'maria@gmail.com', 'activo', '68576d97eea70_rostro1.jpg', '2025-06-22 02:42:31'),
(9, 'rosa', '$2y$10$XKwZ6DJs4Cfo1JPXJ4nS7.bWtD8d8mQ9ykHBlAE1HgFfcKjl.sAOO', 'jefe_area', 'ROSA GARCIA ', 'CALIDAD', 'GUADALAJARA ', '753421', 'rosa@gmail.com', 'activo', '68576e0329bbe_rostro1.jpg', '2025-06-22 02:44:19'),
(10, 'armando', '$2y$10$0xP7lUBy1J8bKfJWQJB5yedTSaABQQ8bv9RbHODCPKleacaWxoyPS', 'rh', 'ARMANDO PULIDO GOMEZ', 'SISTEMAS ', 'Guadalajara ', '974213', 'administrador@gmail.com', 'activo', '6859face7b2eb_imagen.jpeg', '2025-06-24 01:09:34'),
(11, 'monse', '$2y$10$GfJnsM1rgJqnC/SblNbGGurHT7OCb/Lwahz4TcfAyevcNUPz9EnVu', 'jefe_area', 'MONSERRAT ESCAREñO GARCIA', 'PRODUCCION ', 'GUADALAJARA', '234234', 'monse@gmail.com', 'activo', '6859fb5564902_rostro1.jpg', '2025-06-24 01:11:49');

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
  ADD UNIQUE KEY `numero_empleado` (`numero_empleado`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `departamentos`
--
ALTER TABLE `departamentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `sedes`
--
ALTER TABLE `sedes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `departamentos`
--
ALTER TABLE `departamentos`
  ADD CONSTRAINT `departamentos_ibfk_1` FOREIGN KEY (`sede_id`) REFERENCES `sedes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `departamentos_ibfk_2` FOREIGN KEY (`responsable_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
