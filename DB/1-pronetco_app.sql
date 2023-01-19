-- phpMyAdmin SQL Dump
-- version 4.9.7
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 19-01-2023 a las 14:23:04
-- Versión del servidor: 5.7.40-cll-lve
-- Versión de PHP: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `pronetco_app`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `accion`
--

CREATE TABLE `accion` (
  `id` int(11) NOT NULL,
  `descripcion` varchar(50) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `administradoras`
--

CREATE TABLE `administradoras` (
  `id` int(11) NOT NULL,
  `codigo` varchar(4) COLLATE utf8_bin NOT NULL,
  `nombre` varchar(50) COLLATE utf8_bin NOT NULL,
  `email` varchar(50) COLLATE utf8_bin NOT NULL,
  `fecha_actualizacion` timestamp NULL DEFAULT NULL,
  `inactivo` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asambleas`
--

CREATE TABLE `asambleas` (
  `id` int(11) NOT NULL,
  `id_asamblea` int(11) NOT NULL,
  `id_inmueble` varchar(4) NOT NULL,
  `cod_admin` varchar(4) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `archivo_soporte` varchar(60) NOT NULL,
  `archivo_acta` varchar(60) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bancos`
--

CREATE TABLE `bancos` (
  `id` int(11) NOT NULL,
  `codigo` varchar(4) NOT NULL,
  `nombre` varchar(60) NOT NULL,
  `inactivo` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bitacora`
--

CREATE TABLE `bitacora` (
  `id` int(11) NOT NULL,
  `id_sesion` int(11) NOT NULL,
  `id_accion` int(11) NOT NULL,
  `descripcion` varchar(100) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `caja`
--

CREATE TABLE `caja` (
  `id` int(11) NOT NULL,
  `cod_admin` varchar(4) NOT NULL,
  `id_inmueble` int(11) NOT NULL,
  `id_apto` int(11) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `caja_forma_pago`
--

CREATE TABLE `caja_forma_pago` (
  `id` int(11) NOT NULL,
  `id_caja` int(11) NOT NULL,
  `forma_pago` varchar(13) NOT NULL,
  `banco` varchar(20) NOT NULL,
  `cuenta` varchar(20) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `monto` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `caja_recibo`
--

CREATE TABLE `caja_recibo` (
  `id` int(11) NOT NULL,
  `id_caja` int(11) NOT NULL,
  `id_recibo` int(11) NOT NULL,
  `monto` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cancelacion_gastos`
--

CREATE TABLE `cancelacion_gastos` (
  `id` int(11) NOT NULL,
  `fecha_movimiento` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `monto` double NOT NULL DEFAULT '0',
  `descripcion` varchar(200) NOT NULL,
  `cod_admin` varchar(4) NOT NULL,
  `id_inmueble` varchar(4) NOT NULL,
  `id_apto` varchar(10) NOT NULL,
  `periodo` varchar(10) NOT NULL,
  `numero_factura` varchar(10) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cargo_jc`
--

CREATE TABLE `cargo_jc` (
  `id` int(11) NOT NULL,
  `descripcion` varchar(100) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cartelera`
--

CREATE TABLE `cartelera` (
  `id` int(11) NOT NULL,
  `cod_admin` varchar(4) NOT NULL,
  `id_inmueble` varchar(4) NOT NULL,
  `detalle` text NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cobranza_mensual`
--

CREATE TABLE `cobranza_mensual` (
  `id` int(11) NOT NULL,
  `cod_admin` varchar(4) NOT NULL,
  `id_inmueble` varchar(10) NOT NULL,
  `periodo` date NOT NULL,
  `monto` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `facturacion_mensual`
--

CREATE TABLE `facturacion_mensual` (
  `id` int(11) NOT NULL,
  `cod_admin` varchar(4) NOT NULL,
  `id_inmueble` varchar(10) NOT NULL,
  `periodo` date NOT NULL,
  `facturado` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `facturas`
--

CREATE TABLE `facturas` (
  `cod_admin` varchar(4) NOT NULL,
  `apto` varchar(10) NOT NULL,
  `id_inmueble` varchar(4) NOT NULL,
  `numero_factura` varchar(15) NOT NULL,
  `periodo` datetime NOT NULL,
  `facturado` double DEFAULT NULL,
  `abonado` double DEFAULT NULL,
  `fecha` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `facturado_usd` double DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `factura_detalle`
--

CREATE TABLE `factura_detalle` (
  `id` int(11) NOT NULL,
  `id_factura` int(11) NOT NULL,
  `cod_admin` varchar(4) NOT NULL,
  `detalle` varchar(100) DEFAULT NULL,
  `codigo_gasto` varchar(50) NOT NULL,
  `comun` double DEFAULT NULL,
  `monto` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `fondos`
--

CREATE TABLE `fondos` (
  `id` int(1) NOT NULL,
  `cod_admin` varchar(4) NOT NULL,
  `id_inmueble` varchar(4) NOT NULL,
  `codigo_gasto` varchar(6) NOT NULL,
  `descripcion` varchar(80) NOT NULL,
  `saldo` double NOT NULL DEFAULT '0',
  `mostrar` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `fondos_movimiento`
--

CREATE TABLE `fondos_movimiento` (
  `id` int(11) NOT NULL,
  `id_fondo` int(11) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `tipo` varchar(2) NOT NULL,
  `concepto` varchar(100) NOT NULL,
  `debe` double NOT NULL DEFAULT '0',
  `haber` double NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `grupo`
--

CREATE TABLE `grupo` (
  `id` int(11) NOT NULL,
  `cod_admin` varchar(4) NOT NULL,
  `id_inmueble` varchar(4) NOT NULL,
  `descripcion` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `grupo_propietario`
--

CREATE TABLE `grupo_propietario` (
  `id` int(11) NOT NULL,
  `id_grupo` int(11) NOT NULL,
  `id_inmueble` varchar(4) NOT NULL,
  `apto` varchar(8) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historico_avisos_cobro`
--

CREATE TABLE `historico_avisos_cobro` (
  `id` int(11) NOT NULL,
  `cod_admin` varchar(4) NOT NULL,
  `id_inmueble` varchar(4) NOT NULL,
  `apto` varchar(10) NOT NULL,
  `numero_factura` varchar(15) NOT NULL,
  `periodo` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inmueble`
--

CREATE TABLE `inmueble` (
  `id` varchar(4) NOT NULL,
  `nombre_inmueble` varchar(100) DEFAULT NULL,
  `deuda` double DEFAULT NULL,
  `fondo_reserva` double DEFAULT NULL,
  `beneficiario` varchar(100) DEFAULT NULL,
  `banco` varchar(50) DEFAULT NULL,
  `numero_cuenta` varchar(50) DEFAULT NULL,
  `supervision` tinyint(1) DEFAULT '0',
  `RIF` varchar(12) DEFAULT NULL,
  `cod_admin` varchar(4) NOT NULL,
  `moneda` varchar(4) DEFAULT 'Bs',
  `meses_mora` int(11) DEFAULT '0',
  `porc_mora` double DEFAULT '0',
  `unidad` varchar(30) DEFAULT 'UNIDAD FAMILIAR',
  `facturacion_usd` tinyint(1) DEFAULT '0',
  `redondea_usd` tinyint(1) DEFAULT '0',
  `tasa_cambio` double DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inmueble_cuenta`
--

CREATE TABLE `inmueble_cuenta` (
  `id` int(11) NOT NULL,
  `cod_admin` varchar(4) NOT NULL,
  `id_inmueble` varchar(4) NOT NULL,
  `banco` varchar(60) NOT NULL,
  `numero_cuenta` varchar(20) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inmueble_deuda_confidencial`
--

CREATE TABLE `inmueble_deuda_confidencial` (
  `cod_admin` varchar(4) NOT NULL,
  `id_inmueble` varchar(4) NOT NULL,
  `apto` varchar(10) NOT NULL,
  `propietario` varchar(60) NOT NULL,
  `recibos` int(11) NOT NULL,
  `deuda` double NOT NULL,
  `deuda_usd` double DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `junta_condominio`
--

CREATE TABLE `junta_condominio` (
  `id` int(11) NOT NULL,
  `id_cargo` int(11) NOT NULL,
  `cod_admin` varchar(4) NOT NULL,
  `id_inmueble` varchar(4) NOT NULL DEFAULT '1',
  `cedula` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mensajes`
--

CREATE TABLE `mensajes` (
  `id` int(11) NOT NULL,
  `cod_admin` varchar(4) NOT NULL,
  `cedula` int(11) NOT NULL,
  `asunto` varchar(60) NOT NULL,
  `contenido` text NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `leido` tinyint(1) NOT NULL,
  `eliminado` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimiento_caja`
--

CREATE TABLE `movimiento_caja` (
  `id` int(11) NOT NULL,
  `fecha_movimiento` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `numero_recibo` varchar(20) NOT NULL,
  `forma_pago` varchar(20) NOT NULL,
  `monto` double NOT NULL DEFAULT '0',
  `cuenta` varchar(50) NOT NULL,
  `descripcion` varchar(200) NOT NULL,
  `cod_admin` varchar(4) NOT NULL,
  `id_inmueble` varchar(4) NOT NULL,
  `id_apto` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificacion`
--

CREATE TABLE `notificacion` (
  `id` int(11) NOT NULL,
  `descripcion` varchar(100) DEFAULT NULL,
  `id_inmueble` varchar(10) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `fecha_registro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificacion_propietario`
--

CREATE TABLE `notificacion_propietario` (
  `id` int(11) NOT NULL,
  `id_notificacion` int(11) NOT NULL,
  `cedula` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos`
--

CREATE TABLE `pagos` (
  `id` int(11) NOT NULL,
  `cod_admin` varchar(4) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `tipo_pago` enum('d','t','c','tdc','tdb') NOT NULL,
  `numero_documento` varchar(50) NOT NULL,
  `fecha_documento` datetime NOT NULL,
  `monto` double NOT NULL,
  `banco_origen` varchar(50) DEFAULT NULL,
  `banco_destino` varchar(50) NOT NULL,
  `numero_cuenta` varchar(50) NOT NULL,
  `estatus` enum('p','a','r') NOT NULL,
  `email` varchar(50) NOT NULL,
  `enviado` tinyint(1) NOT NULL DEFAULT '0',
  `telefono` varchar(50) NOT NULL,
  `confirmacion` tinyint(1) NOT NULL,
  `soporte` varchar(150) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pago_detalle`
--

CREATE TABLE `pago_detalle` (
  `id` int(11) NOT NULL,
  `id_pago` int(11) NOT NULL,
  `id_factura` varchar(15) NOT NULL,
  `periodo` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `id_inmueble` varchar(4) NOT NULL,
  `id_apto` varchar(10) NOT NULL,
  `monto` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pago_recibo`
--

CREATE TABLE `pago_recibo` (
  `id_pago` int(11) NOT NULL,
  `n_recibo` varchar(20) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pago_usuario`
--

CREATE TABLE `pago_usuario` (
  `id` int(11) NOT NULL,
  `id_pago` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `prerecibo`
--

CREATE TABLE `prerecibo` (
  `id` int(11) NOT NULL,
  `cod_admin` varchar(4) NOT NULL DEFAULT '004',
  `id_inmueble` varchar(4) NOT NULL,
  `documento` varchar(100) NOT NULL,
  `aprobado` tinyint(1) NOT NULL DEFAULT '0',
  `periodo` date NOT NULL,
  `aprobado_por` varchar(60) DEFAULT NULL,
  `fecha_aprobado` date DEFAULT NULL,
  `notificacion` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `propiedades`
--

CREATE TABLE `propiedades` (
  `id` int(11) NOT NULL,
  `cedula` int(11) NOT NULL,
  `cod_admin` varchar(4) NOT NULL,
  `id_inmueble` varchar(4) NOT NULL,
  `apto` varchar(10) NOT NULL,
  `alicuota` double DEFAULT NULL,
  `meses_pendiente` int(11) DEFAULT NULL,
  `deuda_total` double DEFAULT NULL,
  `deuda_usd` double NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `propietarios`
--

CREATE TABLE `propietarios` (
  `id` int(11) NOT NULL,
  `cedula` int(11) NOT NULL,
  `nombre` varchar(100) CHARACTER SET utf8 NOT NULL,
  `telefono1` varchar(50) NOT NULL,
  `telefono2` varchar(50) DEFAULT NULL,
  `telefono3` varchar(50) DEFAULT NULL,
  `email` varchar(60) DEFAULT NULL,
  `email_alternativo` varchar(60) DEFAULT NULL,
  `direccion` varchar(300) DEFAULT NULL,
  `clave` varchar(7) NOT NULL,
  `modificado` tinyint(1) NOT NULL DEFAULT '0',
  `cambio_clave` tinyint(1) NOT NULL DEFAULT '0',
  `validate` tinyint(1) NOT NULL DEFAULT '0',
  `recibos` int(11) NOT NULL DEFAULT '0',
  `cod_admin` varchar(4) NOT NULL,
  `id_facebook` varchar(20) DEFAULT NULL,
  `id_google` varchar(20) DEFAULT NULL,
  `baja` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sesion`
--

CREATE TABLE `sesion` (
  `id` int(11) NOT NULL,
  `cod_admin` varchar(4) NOT NULL,
  `cedula` int(11) NOT NULL,
  `inicio` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `fin` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `accion`
--
ALTER TABLE `accion`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `administradoras`
--
ALTER TABLE `administradoras`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`);

--
-- Indices de la tabla `asambleas`
--
ALTER TABLE `asambleas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `bancos`
--
ALTER TABLE `bancos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `bitacora`
--
ALTER TABLE `bitacora`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cedula` (`id_sesion`),
  ADD KEY `id_accion` (`id_accion`);

--
-- Indices de la tabla `caja`
--
ALTER TABLE `caja`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `caja_forma_pago`
--
ALTER TABLE `caja_forma_pago`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `caja_recibo`
--
ALTER TABLE `caja_recibo`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `cancelacion_gastos`
--
ALTER TABLE `cancelacion_gastos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ix_recibo` (`cod_admin`,`id_inmueble`,`id_apto`,`periodo`);

--
-- Indices de la tabla `cargo_jc`
--
ALTER TABLE `cargo_jc`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `cartelera`
--
ALTER TABLE `cartelera`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `cobranza_mensual`
--
ALTER TABLE `cobranza_mensual`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `inmueble_periodo` (`id_inmueble`,`periodo`);

--
-- Indices de la tabla `facturacion_mensual`
--
ALTER TABLE `facturacion_mensual`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `inmueble_periodo` (`id_inmueble`,`periodo`,`cod_admin`) USING BTREE;

--
-- Indices de la tabla `facturas`
--
ALTER TABLE `facturas`
  ADD PRIMARY KEY (`cod_admin`,`apto`,`id_inmueble`,`periodo`),
  ADD KEY `id_inmueble` (`id_inmueble`),
  ADD KEY `apto` (`apto`);

--
-- Indices de la tabla `factura_detalle`
--
ALTER TABLE `factura_detalle`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_factura` (`id_factura`);

--
-- Indices de la tabla `fondos`
--
ALTER TABLE `fondos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `condominio_cuenta` (`id_inmueble`,`codigo_gasto`,`cod_admin`) USING BTREE;

--
-- Indices de la tabla `fondos_movimiento`
--
ALTER TABLE `fondos_movimiento`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `grupo`
--
ALTER TABLE `grupo`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `grupo_propietario`
--
ALTER TABLE `grupo_propietario`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `historico_avisos_cobro`
--
ALTER TABLE `historico_avisos_cobro`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `inmueble`
--
ALTER TABLE `inmueble`
  ADD PRIMARY KEY (`id`,`cod_admin`) USING BTREE;

--
-- Indices de la tabla `inmueble_cuenta`
--
ALTER TABLE `inmueble_cuenta`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_inmueble` (`id_inmueble`,`banco`,`numero_cuenta`);

--
-- Indices de la tabla `inmueble_deuda_confidencial`
--
ALTER TABLE `inmueble_deuda_confidencial`
  ADD PRIMARY KEY (`id_inmueble`,`apto`);

--
-- Indices de la tabla `junta_condominio`
--
ALTER TABLE `junta_condominio`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_cargo` (`id_cargo`),
  ADD KEY `cedula` (`cedula`),
  ADD KEY `id_inmueble` (`id_inmueble`);

--
-- Indices de la tabla `mensajes`
--
ALTER TABLE `mensajes`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `movimiento_caja`
--
ALTER TABLE `movimiento_caja`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `notificacion`
--
ALTER TABLE `notificacion`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `notificacion_propietario`
--
ALTER TABLE `notificacion_propietario`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `notificacion_propietario` (`id_notificacion`,`cedula`);

--
-- Indices de la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `pago_detalle`
--
ALTER TABLE `pago_detalle`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_factura` (`id_factura`),
  ADD KEY `id_inmueble` (`id_inmueble`),
  ADD KEY `id_apto` (`id_apto`);

--
-- Indices de la tabla `pago_usuario`
--
ALTER TABLE `pago_usuario`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `prerecibo`
--
ALTER TABLE `prerecibo`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `inmueble_periodo` (`id_inmueble`,`periodo`);

--
-- Indices de la tabla `propiedades`
--
ALTER TABLE `propiedades`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `propietarios`
--
ALTER TABLE `propietarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `propietario` (`cedula`,`email`,`cod_admin`,`clave`) USING BTREE;

--
-- Indices de la tabla `sesion`
--
ALTER TABLE `sesion`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `accion`
--
ALTER TABLE `accion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `administradoras`
--
ALTER TABLE `administradoras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `asambleas`
--
ALTER TABLE `asambleas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `bancos`
--
ALTER TABLE `bancos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `bitacora`
--
ALTER TABLE `bitacora`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `caja`
--
ALTER TABLE `caja`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `caja_forma_pago`
--
ALTER TABLE `caja_forma_pago`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `caja_recibo`
--
ALTER TABLE `caja_recibo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cancelacion_gastos`
--
ALTER TABLE `cancelacion_gastos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cargo_jc`
--
ALTER TABLE `cargo_jc`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cartelera`
--
ALTER TABLE `cartelera`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cobranza_mensual`
--
ALTER TABLE `cobranza_mensual`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `facturacion_mensual`
--
ALTER TABLE `facturacion_mensual`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `factura_detalle`
--
ALTER TABLE `factura_detalle`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `fondos`
--
ALTER TABLE `fondos`
  MODIFY `id` int(1) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `fondos_movimiento`
--
ALTER TABLE `fondos_movimiento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `grupo_propietario`
--
ALTER TABLE `grupo_propietario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `historico_avisos_cobro`
--
ALTER TABLE `historico_avisos_cobro`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `inmueble_cuenta`
--
ALTER TABLE `inmueble_cuenta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `junta_condominio`
--
ALTER TABLE `junta_condominio`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `mensajes`
--
ALTER TABLE `mensajes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `movimiento_caja`
--
ALTER TABLE `movimiento_caja`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `notificacion`
--
ALTER TABLE `notificacion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `notificacion_propietario`
--
ALTER TABLE `notificacion_propietario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pagos`
--
ALTER TABLE `pagos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pago_detalle`
--
ALTER TABLE `pago_detalle`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pago_usuario`
--
ALTER TABLE `pago_usuario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `prerecibo`
--
ALTER TABLE `prerecibo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `propiedades`
--
ALTER TABLE `propiedades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `propietarios`
--
ALTER TABLE `propietarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `sesion`
--
ALTER TABLE `sesion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
