-- phpMyAdmin SQL Dump
-- version 4.9.7
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 19-01-2023 a las 14:26:01
-- Versión del servidor: 5.7.40-cll-lve
-- Versión de PHP: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Base de datos: `pronetco_app`
--

--
-- Volcado de datos para la tabla `accion`
--

INSERT INTO `accion` (`id`, `descripcion`) VALUES
(1, 'Consulta Estado de Cuenta'),
(2, 'Consulta Aviso de Cobro'),
(3, 'Consulta Datos Personales'),
(4, 'Consulta Junta de Condominio'),
(5, 'Consulta Mensajes Recibidos'),
(6, 'Consulta Estado Cuenta Inmueble'),
(7, 'Cambio de Clave'),
(8, 'Inicio Registro Pago'),
(9, 'Completar Registro Pago'),
(10, 'Autorizar Prerecibo'),
(11, 'Ver Cartelera'),
(12, 'Ver Cancelación de Gastos'),
(13, 'Consultar Histórico de Operaciones'),
(14, 'Actualización Datos Personales'),
(15, 'Consulta Soportes de Facturación'),
(16, 'Entrar a Autorizar Prerecibo'),
(17, 'Ver'),
(18, 'Autorizar Prerecibo');

--
-- Volcado de datos para la tabla `bancos`
--

INSERT INTO `bancos` (`id`, `codigo`, `nombre`, `inactivo`) VALUES
(1, '0001', 'Central de Venezuela', 0),
(2, '0003', 'Industrial de Venezuela', 0),
(3, '0102', 'Venezuela ', 0),
(4, '0104', 'Venezolano de Crédito', 0),
(5, '0105', 'Mercantil', 0),
(6, '0108', 'Provincial', 0),
(7, '0114', 'Bancaribe', 0),
(8, '0115', 'Exterior', 0),
(9, '0116', 'Occidental de Descuento', 0),
(10, '0128', 'Caroní', 0),
(11, '0134', 'Banesco', 0),
(12, '0137', 'Sofitasa', 0),
(13, '0138', 'Plaza', 0),
(14, '0146', 'Gente Emprendedora', 0),
(15, '0149', 'Pueblo Soberano', 0),
(16, '0151', 'BFC Banco Fondo Común', 0),
(17, '0156', '100% Banco', 0),
(18, '0157', 'DelSur', 0),
(19, '0163', 'Tesoro', 0),
(20, '0166', 'Agrícola de Venezuela', 0),
(21, '0168', 'Bancrecer', 0),
(22, '0169', 'Mi Banco', 0),
(23, '0171', 'Activo', 0),
(24, '0172', 'Bancamiga', 0),
(25, '0173', 'Internacional de Desarrollo', 0),
(26, '0174', 'Banplus', 0),
(27, '0175', 'Bicentenario', 0),
(28, '0176', 'Espirito Santo', 0),
(29, '0177', 'Fuerza Armada Nacional Bolivariana', 0),
(30, '0190', 'Citibank', 0),
(31, '0191', 'BNC', 0),
(32, '0601', 'Instituto Municipal de Crédito Popular', 0),
(33, '9999', 'Portal de pagos Mercantil', 0);
COMMIT;
