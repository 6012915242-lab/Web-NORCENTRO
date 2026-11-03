-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:58475
-- Tiempo de generación: 03-11-2026 a las 15:34:58
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `norcentro_web`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id` int(10) UNSIGNED NOT NULL,
  `dni` char(8) NOT NULL,
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `telefono` varchar(20) NOT NULL DEFAULT '',
  `direccion` varchar(180) NOT NULL DEFAULT '',
  `correo` varchar(120) NOT NULL DEFAULT '',
  `estado` enum('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id`, `dni`, `nombres`, `apellidos`, `telefono`, `direccion`, `correo`, `estado`, `creado_en`) VALUES
(1, '72123456', 'Juan', 'Pérez Salazar', '999111222', 'Bagua', 'juan.perez@example.com', 'Activo', '2026-09-28 00:57:04'),
(2, '73456789', 'María', 'López Torres', '999333444', 'Bagua Grande', 'maria.lopez@example.com', 'Activo', '2026-09-28 00:57:04'),
(21, '56655464', 'yomar', 'lujan', '987567876', 'jr.libertad', 'no@gmail.com', 'Activo', '2026-09-29 20:15:21');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `creditos`
--

CREATE TABLE `creditos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `venta_id` bigint(20) UNSIGNED NOT NULL,
  `inicial` decimal(10,2) NOT NULL DEFAULT 0.00,
  `monto_financiado` decimal(10,2) NOT NULL,
  `porcentaje_recargo` decimal(5,2) NOT NULL DEFAULT 0.00,
  `importe_recargo` decimal(10,2) NOT NULL DEFAULT 0.00,
  `numero_cuotas` smallint(5) UNSIGNED NOT NULL,
  `saldo` decimal(10,2) NOT NULL,
  `estado` enum('Activo','Cancelado') NOT NULL DEFAULT 'Activo',
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ;

--
-- Volcado de datos para la tabla `creditos`
--

INSERT INTO `creditos` (`id`, `venta_id`, `inicial`, `monto_financiado`, `numero_cuotas`, `saldo`, `estado`, `creado_en`) VALUES
(2, 2, 0.00, 11900.00, 6, 11400.00, 'Activo', '2026-09-28 19:49:14');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cuotas`
--

CREATE TABLE `cuotas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `credito_id` bigint(20) UNSIGNED NOT NULL,
  `numero` smallint(5) UNSIGNED NOT NULL,
  `fecha_vencimiento` date NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `pagado` decimal(10,2) NOT NULL DEFAULT 0.00,
  `estado` enum('Pendiente','Pagada','Vencida') NOT NULL DEFAULT 'Pendiente'
) ;

--
-- Volcado de datos para la tabla `cuotas`
--

INSERT INTO `cuotas` (`id`, `credito_id`, `numero`, `fecha_vencimiento`, `monto`, `pagado`, `estado`) VALUES
(4, 2, 1, '2026-10-28', 1983.33, 500.00, 'Pendiente'),
(5, 2, 2, '2026-11-28', 1983.33, 0.00, 'Pendiente'),
(6, 2, 3, '2026-12-28', 1983.33, 0.00, 'Pendiente'),
(7, 2, 4, '2027-01-28', 1983.33, 0.00, 'Pendiente'),
(8, 2, 5, '2027-02-28', 1983.33, 0.00, 'Pendiente'),
(9, 2, 6, '2027-03-28', 1983.35, 0.00, 'Pendiente');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `marcas`
--

CREATE TABLE `marcas` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `nombre` varchar(60) NOT NULL,
  `estado` enum('Activo','Inactivo') NOT NULL DEFAULT 'Activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `marcas`
--

INSERT INTO `marcas` (`id`, `nombre`, `estado`) VALUES
(1, 'Bajaj', 'Activo'),
(2, 'Honda', 'Inactivo'),
(3, 'KTM', 'Activo'),
(4, 'Suzuki', 'Activo'),
(5, 'Yamaha', 'Activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `motos`
--

CREATE TABLE `motos` (
  `id` int(10) UNSIGNED NOT NULL,
  `marca_id` smallint(5) UNSIGNED NOT NULL,
  `modelo` varchar(100) NOT NULL,
  `cilindrada` smallint(5) UNSIGNED NOT NULL,
  `color` varchar(50) NOT NULL DEFAULT 'Varios',
  `precio` decimal(10,2) NOT NULL,
  `descripcion` text NOT NULL,
  `anio` year(4) NOT NULL,
  `imagen` varchar(255) NOT NULL,
  `estado` enum('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ;

--
-- Volcado de datos para la tabla `motos`
--

INSERT INTO `motos` (`id`, `marca_id`, `modelo`, `cilindrada`, `color`, `precio`, `descripcion`, `anio`, `imagen`, `estado`, `creado_en`) VALUES
(1, 1, 'Pulsar NS200', 200, 'Rojo / gris', 13169.00, 'Pulsar NS200 FI UG2 2026: naked deportiva de 199.5 cc, con tablero digital, horquilla invertida, iluminación LED y embrague antirrebote.', '2026', 'img/motos/bajaj-ns200/rojo-gris-01.jpg', 'Activo', '2026-09-28 00:57:04'),
(2, 1, 'Pulsar NS160', 160, 'Rojo / gris', 11900.00, 'Naked de 160.3 cc con motor DTS-i refrigerado por aceite, frenos de disco y postura streetfighter para el uso urbano.', '2026', 'img/motos/bajaj-ns160/rojo-gris-01.png', 'Activo', '2026-09-28 00:57:04'),
(3, 2, 'CB190R', 190, 'Negro', 13900.00, 'Naked de estilo deportivo para uso urbano y desplazamientos cotidianos.', '2026', 'img/motos/Honda CB190R.jpg', 'Inactivo', '2026-09-28 00:57:04'),
(4, 3, 'Duke 200', 199, 'Gris oscuro', 14000.00, 'Naked deportiva compacta con motor monocilíndrico DOHC de 199 cc, 25 HP, ABS, caja de seis velocidades y suspensión WP telescópica.', '2026', 'img/motos/ktm-duke200/oficial-ktm-gris-01.png', 'Activo', '2026-09-28 00:57:04'),
(5, 4, 'Gixxer 150', 155, 'Azul', 9000.00, 'Moto urbana deportiva de 155 cc y 13.6 HP, con frenos de disco, caja de cinco velocidades, tanque de 12 litros y postura ágil para ciudad.', '2026', 'img/motos/suzuki-gixxer150/azul-01.jpg', 'Activo', '2026-09-28 00:57:04'),
(6, 5, 'FZ25', 249, 'Azul', 12500.00, 'Naked urbana de 249 cc y 20 HP, con tanque de 14 litros, transmisión de cinco velocidades y frenos de disco para el uso diario.', '2026', 'img/motos/yamaha-fz25/azul-01.png', 'Activo', '2026-09-28 00:57:04'),
(9, 1, 'Pulsar NS400Z', 349, 'Rojo / negro', 19141.00, 'Naked deportiva de 349 cc con motor refrigerado por líquido, acelerador Ride-by-Wire, cuatro modos de manejo, pantalla TFT y ABS de doble canal.', '2026', 'img/motos/bajaj-ns400z/rojo-negro-01.jpg', 'Activo', '2026-09-28 01:35:56'),
(52, 5, 'YZF-R15 ABS', 155, 'Azul', 17337.00, 'Deportiva de 155 cc con VVA, ABS, control de tracción y conectividad Y-Connect.', '2026', 'img/motos/yamaha-r15/azul-01.png', 'Activo', '2026-09-28 03:03:12'),
(53, 5, 'MT-09 ABS', 888, 'Azul', 60517.00, 'Hyper naked de tres cilindros con 117.3 HP, ABS y tablero TFT de 5 pulgadas con conectividad.', '2026', 'img/motos/yamaha-mt09/azul-01.png', 'Activo', '2026-09-28 03:03:12'),
(54, 5, 'MT-15', 155, 'Azul', 16147.00, 'Naked urbana de 155 cc con VVA, 19 HP, faro LED y diseño compacto de la familia MT.', '2026', 'img/motos/yamaha-mt15/azul-01.png', 'Activo', '2026-09-28 03:03:12');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `moto_colores`
--

CREATE TABLE `moto_colores` (
  `id` int(10) UNSIGNED NOT NULL,
  `moto_id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(60) NOT NULL,
  `codigo_hex` char(7) NOT NULL DEFAULT '#cccccc',
  `orden` smallint(5) UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `moto_colores`
--

INSERT INTO `moto_colores` (`id`, `moto_id`, `nombre`, `codigo_hex`, `orden`) VALUES
(38, 1, 'Rojo / gris', '#c92836', 1),
(39, 1, 'Negro / rojo', '#282828', 2),
(40, 1, 'Azul / gris', '#2864a7', 3),
(41, 1, 'Blanco / rojo', '#efefef', 4),
(42, 2, 'Rojo / gris', '#c92836', 1),
(43, 2, 'Negro / rojo', '#282828', 2),
(44, 2, 'Azul / gris', '#2864a7', 3),
(45, 9, 'Rojo / negro', '#d3232a', 1),
(46, 9, 'Negro / rojo', '#282828', 2),
(47, 9, 'Amarillo / gris', '#d4d10c', 3),
(48, 9, 'Blanco / rojo', '#efefef', 4),
(49, 4, 'Gris oscuro', '#44474b', 1),
(50, 4, 'Naranja', '#f47721', 2),
(51, 5, 'Azul', '#1663a8', 1),
(52, 5, 'Negro', '#222222', 2),
(53, 6, 'Azul', '#194e91', 1),
(54, 53, 'Azul', '#2362a4', 1),
(55, 54, 'Azul', '#2362a4', 1),
(56, 52, 'Azul', '#2362a4', 1),
(57, 52, 'Negro', '#22252a', 2),
(58, 52, 'Blanco / azul', '#e7e8eb', 3),
(59, 52, 'Rojo', '#c82332', 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `moto_color_imagenes`
--

CREATE TABLE `moto_color_imagenes` (
  `id` int(10) UNSIGNED NOT NULL,
  `color_id` int(10) UNSIGNED NOT NULL,
  `ruta` varchar(255) NOT NULL,
  `texto_alt` varchar(180) NOT NULL DEFAULT '',
  `orden` smallint(5) UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `moto_color_imagenes`
--

INSERT INTO `moto_color_imagenes` (`id`, `color_id`, `ruta`, `texto_alt`, `orden`) VALUES
(1295, 42, 'img/motos/bajaj-ns160/motocorp-rojo-gris-01.png', 'Bajaj Pulsar NS160 rojo / gris, fotografía de catálogo 01', 1),
(1296, 42, 'img/motos/bajaj-ns160/motocorp-rojo-gris-02.png', 'Bajaj Pulsar NS160 rojo / gris, fotografía de catálogo 02', 2),
(1297, 42, 'img/motos/bajaj-ns160/motocorp-rojo-gris-03.png', 'Bajaj Pulsar NS160 rojo / gris, fotografía de catálogo 03', 3),
(1298, 42, 'img/motos/bajaj-ns160/motocorp-rojo-gris-04.png', 'Bajaj Pulsar NS160 rojo / gris, fotografía de catálogo 04', 4),
(1299, 42, 'img/motos/bajaj-ns160/motocorp-rojo-gris-05.png', 'Bajaj Pulsar NS160 rojo / gris, fotografía de catálogo 05', 5),
(1300, 42, 'img/motos/bajaj-ns160/motocorp-rojo-gris-06.png', 'Bajaj Pulsar NS160 rojo / gris, fotografía de catálogo 06', 6),
(1301, 42, 'img/motos/bajaj-ns160/motocorp-rojo-gris-07.png', 'Bajaj Pulsar NS160 rojo / gris, fotografía de catálogo 07', 7),
(1302, 42, 'img/motos/bajaj-ns160/motocorp-rojo-gris-08.png', 'Bajaj Pulsar NS160 rojo / gris, fotografía de catálogo 08', 8),
(1303, 43, 'img/motos/bajaj-ns160/motocorp-negro-rojo-01.png', 'Bajaj Pulsar NS160 negro / rojo, fotografía de catálogo 01', 1),
(1304, 43, 'img/motos/bajaj-ns160/motocorp-negro-rojo-02.png', 'Bajaj Pulsar NS160 negro / rojo, fotografía de catálogo 02', 2),
(1305, 43, 'img/motos/bajaj-ns160/motocorp-negro-rojo-03.png', 'Bajaj Pulsar NS160 negro / rojo, fotografía de catálogo 03', 3),
(1306, 43, 'img/motos/bajaj-ns160/motocorp-negro-rojo-04.png', 'Bajaj Pulsar NS160 negro / rojo, fotografía de catálogo 04', 4),
(1307, 43, 'img/motos/bajaj-ns160/motocorp-negro-rojo-05.png', 'Bajaj Pulsar NS160 negro / rojo, fotografía de catálogo 05', 5),
(1308, 43, 'img/motos/bajaj-ns160/motocorp-negro-rojo-06.png', 'Bajaj Pulsar NS160 negro / rojo, fotografía de catálogo 06', 6),
(1309, 43, 'img/motos/bajaj-ns160/motocorp-negro-rojo-07.png', 'Bajaj Pulsar NS160 negro / rojo, fotografía de catálogo 07', 7),
(1310, 43, 'img/motos/bajaj-ns160/motocorp-negro-rojo-08.png', 'Bajaj Pulsar NS160 negro / rojo, fotografía de catálogo 08', 8),
(1311, 44, 'img/motos/bajaj-ns160/motocorp-azul-gris-01.png', 'Bajaj Pulsar NS160 azul / gris, fotografía de catálogo 01', 1),
(1312, 44, 'img/motos/bajaj-ns160/motocorp-azul-gris-02.png', 'Bajaj Pulsar NS160 azul / gris, fotografía de catálogo 02', 2),
(1313, 44, 'img/motos/bajaj-ns160/motocorp-azul-gris-03.png', 'Bajaj Pulsar NS160 azul / gris, fotografía de catálogo 03', 3),
(1314, 44, 'img/motos/bajaj-ns160/motocorp-azul-gris-04.png', 'Bajaj Pulsar NS160 azul / gris, fotografía de catálogo 04', 4),
(1315, 44, 'img/motos/bajaj-ns160/motocorp-azul-gris-05.png', 'Bajaj Pulsar NS160 azul / gris, fotografía de catálogo 05', 5),
(1316, 44, 'img/motos/bajaj-ns160/motocorp-azul-gris-06.png', 'Bajaj Pulsar NS160 azul / gris, fotografía de catálogo 06', 6),
(1317, 44, 'img/motos/bajaj-ns160/motocorp-azul-gris-07.png', 'Bajaj Pulsar NS160 azul / gris, fotografía de catálogo 07', 7),
(1318, 38, 'img/motos/bajaj-ns200/motocorp-rojo-gris-01.jpg', 'Bajaj Pulsar NS200 rojo / gris, fotografía de catálogo 01', 1),
(1319, 38, 'img/motos/bajaj-ns200/motocorp-rojo-gris-02.jpg', 'Bajaj Pulsar NS200 rojo / gris, fotografía de catálogo 02', 2),
(1320, 38, 'img/motos/bajaj-ns200/motocorp-rojo-gris-03.jpg', 'Bajaj Pulsar NS200 rojo / gris, fotografía de catálogo 03', 3),
(1321, 38, 'img/motos/bajaj-ns200/motocorp-rojo-gris-04.jpg', 'Bajaj Pulsar NS200 rojo / gris, fotografía de catálogo 04', 4),
(1322, 38, 'img/motos/bajaj-ns200/motocorp-rojo-gris-05.jpg', 'Bajaj Pulsar NS200 rojo / gris, fotografía de catálogo 05', 5),
(1323, 38, 'img/motos/bajaj-ns200/motocorp-rojo-gris-06.jpg', 'Bajaj Pulsar NS200 rojo / gris, fotografía de catálogo 06', 6),
(1324, 38, 'img/motos/bajaj-ns200/motocorp-rojo-gris-07.jpg', 'Bajaj Pulsar NS200 rojo / gris, fotografía de catálogo 07', 7),
(1325, 38, 'img/motos/bajaj-ns200/motocorp-rojo-gris-08.jpg', 'Bajaj Pulsar NS200 rojo / gris, fotografía de catálogo 08', 8),
(1326, 39, 'img/motos/bajaj-ns200/motocorp-negro-rojo-01.jpg', 'Bajaj Pulsar NS200 negro / rojo, fotografía de catálogo 01', 1),
(1327, 39, 'img/motos/bajaj-ns200/motocorp-negro-rojo-02.jpg', 'Bajaj Pulsar NS200 negro / rojo, fotografía de catálogo 02', 2),
(1328, 39, 'img/motos/bajaj-ns200/motocorp-negro-rojo-03.jpg', 'Bajaj Pulsar NS200 negro / rojo, fotografía de catálogo 03', 3),
(1329, 39, 'img/motos/bajaj-ns200/motocorp-negro-rojo-04.jpg', 'Bajaj Pulsar NS200 negro / rojo, fotografía de catálogo 04', 4),
(1330, 39, 'img/motos/bajaj-ns200/motocorp-negro-rojo-05.jpg', 'Bajaj Pulsar NS200 negro / rojo, fotografía de catálogo 05', 5),
(1331, 39, 'img/motos/bajaj-ns200/motocorp-negro-rojo-06.jpg', 'Bajaj Pulsar NS200 negro / rojo, fotografía de catálogo 06', 6),
(1332, 39, 'img/motos/bajaj-ns200/motocorp-negro-rojo-07.jpg', 'Bajaj Pulsar NS200 negro / rojo, fotografía de catálogo 07', 7),
(1333, 39, 'img/motos/bajaj-ns200/motocorp-negro-rojo-08.jpg', 'Bajaj Pulsar NS200 negro / rojo, fotografía de catálogo 08', 8),
(1334, 40, 'img/motos/bajaj-ns200/motocorp-azul-gris-01.jpg', 'Bajaj Pulsar NS200 azul / gris, fotografía de catálogo 01', 1),
(1335, 40, 'img/motos/bajaj-ns200/motocorp-azul-gris-02.jpg', 'Bajaj Pulsar NS200 azul / gris, fotografía de catálogo 02', 2),
(1336, 40, 'img/motos/bajaj-ns200/motocorp-azul-gris-03.jpg', 'Bajaj Pulsar NS200 azul / gris, fotografía de catálogo 03', 3),
(1337, 40, 'img/motos/bajaj-ns200/motocorp-azul-gris-04.jpg', 'Bajaj Pulsar NS200 azul / gris, fotografía de catálogo 04', 4),
(1338, 40, 'img/motos/bajaj-ns200/motocorp-azul-gris-05.jpg', 'Bajaj Pulsar NS200 azul / gris, fotografía de catálogo 05', 5),
(1339, 40, 'img/motos/bajaj-ns200/motocorp-azul-gris-06.jpg', 'Bajaj Pulsar NS200 azul / gris, fotografía de catálogo 06', 6),
(1340, 40, 'img/motos/bajaj-ns200/motocorp-azul-gris-07.jpg', 'Bajaj Pulsar NS200 azul / gris, fotografía de catálogo 07', 7),
(1341, 40, 'img/motos/bajaj-ns200/motocorp-azul-gris-08.jpg', 'Bajaj Pulsar NS200 azul / gris, fotografía de catálogo 08', 8),
(1342, 41, 'img/motos/bajaj-ns200/motocorp-blanco-rojo-01.jpg', 'Bajaj Pulsar NS200 blanco / rojo, fotografía de catálogo 01', 1),
(1343, 41, 'img/motos/bajaj-ns200/motocorp-blanco-rojo-02.jpg', 'Bajaj Pulsar NS200 blanco / rojo, fotografía de catálogo 02', 2),
(1344, 41, 'img/motos/bajaj-ns200/motocorp-blanco-rojo-03.jpg', 'Bajaj Pulsar NS200 blanco / rojo, fotografía de catálogo 03', 3),
(1345, 41, 'img/motos/bajaj-ns200/motocorp-blanco-rojo-04.jpg', 'Bajaj Pulsar NS200 blanco / rojo, fotografía de catálogo 04', 4),
(1346, 41, 'img/motos/bajaj-ns200/motocorp-blanco-rojo-05.jpg', 'Bajaj Pulsar NS200 blanco / rojo, fotografía de catálogo 05', 5),
(1347, 41, 'img/motos/bajaj-ns200/motocorp-blanco-rojo-06.jpg', 'Bajaj Pulsar NS200 blanco / rojo, fotografía de catálogo 06', 6),
(1348, 41, 'img/motos/bajaj-ns200/motocorp-blanco-rojo-07.jpg', 'Bajaj Pulsar NS200 blanco / rojo, fotografía de catálogo 07', 7),
(1349, 41, 'img/motos/bajaj-ns200/motocorp-blanco-rojo-08.jpg', 'Bajaj Pulsar NS200 blanco / rojo, fotografía de catálogo 08', 8),
(1350, 45, 'img/motos/bajaj-ns400z/motocorp-rojo-negro-01.jpg', 'Bajaj Pulsar NS400Z rojo / negro, fotografía de catálogo 01', 1),
(1351, 45, 'img/motos/bajaj-ns400z/motocorp-rojo-negro-02.jpg', 'Bajaj Pulsar NS400Z rojo / negro, fotografía de catálogo 02', 2),
(1352, 45, 'img/motos/bajaj-ns400z/motocorp-rojo-negro-03.jpg', 'Bajaj Pulsar NS400Z rojo / negro, fotografía de catálogo 03', 3),
(1353, 45, 'img/motos/bajaj-ns400z/motocorp-rojo-negro-04.jpg', 'Bajaj Pulsar NS400Z rojo / negro, fotografía de catálogo 04', 4),
(1354, 45, 'img/motos/bajaj-ns400z/motocorp-rojo-negro-05.jpg', 'Bajaj Pulsar NS400Z rojo / negro, fotografía de catálogo 05', 5),
(1355, 45, 'img/motos/bajaj-ns400z/motocorp-rojo-negro-06.png', 'Bajaj Pulsar NS400Z rojo / negro, fotografía de catálogo 06', 6),
(1356, 45, 'img/motos/bajaj-ns400z/motocorp-rojo-negro-07.jpg', 'Bajaj Pulsar NS400Z rojo / negro, fotografía de catálogo 07', 7),
(1357, 45, 'img/motos/bajaj-ns400z/motocorp-rojo-negro-08.png', 'Bajaj Pulsar NS400Z rojo / negro, fotografía de catálogo 08', 8),
(1358, 46, 'img/motos/bajaj-ns400z/motocorp-negro-rojo-01.jpg', 'Bajaj Pulsar NS400Z negro / rojo, fotografía de catálogo 01', 1),
(1359, 46, 'img/motos/bajaj-ns400z/motocorp-negro-rojo-02.jpg', 'Bajaj Pulsar NS400Z negro / rojo, fotografía de catálogo 02', 2),
(1360, 46, 'img/motos/bajaj-ns400z/motocorp-negro-rojo-03.jpg', 'Bajaj Pulsar NS400Z negro / rojo, fotografía de catálogo 03', 3),
(1361, 46, 'img/motos/bajaj-ns400z/motocorp-negro-rojo-04.jpg', 'Bajaj Pulsar NS400Z negro / rojo, fotografía de catálogo 04', 4),
(1362, 46, 'img/motos/bajaj-ns400z/motocorp-negro-rojo-05.jpg', 'Bajaj Pulsar NS400Z negro / rojo, fotografía de catálogo 05', 5),
(1363, 46, 'img/motos/bajaj-ns400z/motocorp-negro-rojo-06.jpg', 'Bajaj Pulsar NS400Z negro / rojo, fotografía de catálogo 06', 6),
(1364, 46, 'img/motos/bajaj-ns400z/motocorp-negro-rojo-07.jpg', 'Bajaj Pulsar NS400Z negro / rojo, fotografía de catálogo 07', 7),
(1365, 46, 'img/motos/bajaj-ns400z/motocorp-negro-rojo-08.jpg', 'Bajaj Pulsar NS400Z negro / rojo, fotografía de catálogo 08', 8),
(1366, 46, 'img/motos/bajaj-ns400z/motocorp-negro-rojo-09.jpg', 'Bajaj Pulsar NS400Z negro / rojo, fotografía de catálogo 09', 9),
(1367, 46, 'img/motos/bajaj-ns400z/motocorp-negro-rojo-10.jpg', 'Bajaj Pulsar NS400Z negro / rojo, fotografía de catálogo 10', 10),
(1368, 47, 'img/motos/bajaj-ns400z/motocorp-amarillo-gris-01.jpg', 'Bajaj Pulsar NS400Z amarillo / gris, fotografía de catálogo 01', 1),
(1369, 47, 'img/motos/bajaj-ns400z/motocorp-amarillo-gris-02.jpg', 'Bajaj Pulsar NS400Z amarillo / gris, fotografía de catálogo 02', 2),
(1370, 47, 'img/motos/bajaj-ns400z/motocorp-amarillo-gris-03.jpg', 'Bajaj Pulsar NS400Z amarillo / gris, fotografía de catálogo 03', 3),
(1371, 47, 'img/motos/bajaj-ns400z/motocorp-amarillo-gris-04.jpg', 'Bajaj Pulsar NS400Z amarillo / gris, fotografía de catálogo 04', 4),
(1372, 47, 'img/motos/bajaj-ns400z/motocorp-amarillo-gris-05.jpg', 'Bajaj Pulsar NS400Z amarillo / gris, fotografía de catálogo 05', 5),
(1373, 47, 'img/motos/bajaj-ns400z/motocorp-amarillo-gris-06.png', 'Bajaj Pulsar NS400Z amarillo / gris, fotografía de catálogo 06', 6),
(1374, 47, 'img/motos/bajaj-ns400z/motocorp-amarillo-gris-07.jpg', 'Bajaj Pulsar NS400Z amarillo / gris, fotografía de catálogo 07', 7),
(1375, 47, 'img/motos/bajaj-ns400z/motocorp-amarillo-gris-08.png', 'Bajaj Pulsar NS400Z amarillo / gris, fotografía de catálogo 08', 8),
(1376, 48, 'img/motos/bajaj-ns400z/motocorp-blanco-rojo-01.jpg', 'Bajaj Pulsar NS400Z blanco / rojo, fotografía de catálogo 01', 1),
(1377, 48, 'img/motos/bajaj-ns400z/motocorp-blanco-rojo-02.jpg', 'Bajaj Pulsar NS400Z blanco / rojo, fotografía de catálogo 02', 2),
(1378, 48, 'img/motos/bajaj-ns400z/motocorp-blanco-rojo-03.jpg', 'Bajaj Pulsar NS400Z blanco / rojo, fotografía de catálogo 03', 3),
(1379, 48, 'img/motos/bajaj-ns400z/motocorp-blanco-rojo-04.jpg', 'Bajaj Pulsar NS400Z blanco / rojo, fotografía de catálogo 04', 4),
(1380, 48, 'img/motos/bajaj-ns400z/motocorp-blanco-rojo-05.jpg', 'Bajaj Pulsar NS400Z blanco / rojo, fotografía de catálogo 05', 5),
(1381, 48, 'img/motos/bajaj-ns400z/motocorp-blanco-rojo-06.png', 'Bajaj Pulsar NS400Z blanco / rojo, fotografía de catálogo 06', 6),
(1382, 48, 'img/motos/bajaj-ns400z/motocorp-blanco-rojo-07.png', 'Bajaj Pulsar NS400Z blanco / rojo, fotografía de catálogo 07', 7),
(1383, 48, 'img/motos/bajaj-ns400z/motocorp-blanco-rojo-08.jpg', 'Bajaj Pulsar NS400Z blanco / rojo, fotografía de catálogo 08', 8),
(1384, 49, 'img/motos/ktm-duke200/oficial-ktm-gris-01.png', 'KTM 200 Duke gris, vista lateral derecha', 1),
(1385, 49, 'img/motos/ktm-duke200/oficial-ktm-gris-02.png', 'KTM 200 Duke gris, vista trasera derecha', 2),
(1386, 49, 'img/motos/ktm-duke200/oficial-ktm-gris-03.png', 'KTM 200 Duke gris, vista trasera', 3),
(1387, 49, 'img/motos/ktm-duke200/oficial-ktm-gris-04.png', 'KTM 200 Duke gris, vista trasera izquierda', 4),
(1388, 49, 'img/motos/ktm-duke200/oficial-ktm-gris-05.png', 'KTM 200 Duke gris, vista lateral izquierda', 5),
(1389, 49, 'img/motos/ktm-duke200/oficial-ktm-gris-06.png', 'KTM 200 Duke gris, vista delantera izquierda', 6),
(1390, 49, 'img/motos/ktm-duke200/oficial-ktm-gris-07.png', 'KTM 200 Duke gris, vista frontal', 7),
(1391, 49, 'img/motos/ktm-duke200/oficial-ktm-gris-08.png', 'KTM 200 Duke gris, vista delantera derecha', 8),
(1392, 50, 'img/motos/ktm-duke200/naranja-01.png', 'KTM Duke 200 naranja, vista lateral derecha', 1),
(1393, 50, 'img/motos/ktm-duke200/naranja-02.png', 'KTM Duke 200 naranja, vista lateral izquierda', 2),
(1394, 51, 'img/motos/suzuki-gixxer150/motocorp-azul-01.jpg', 'Suzuki Gixxer 150 azul, vista lateral', 1),
(1395, 52, 'img/motos/suzuki-gixxer150/motocorp-negro-01.jpg', 'Suzuki Gixxer 150 negro, vista lateral', 1),
(1396, 51, 'img/motos/suzuki-gixxer150/cusap-gixxer-02.jpg', 'Suzuki Gixxer 150 azul, vista tres cuartos frontal', 2),
(1397, 52, 'img/motos/suzuki-gixxer150/cusap-gixxer-01.jpg', 'Suzuki Gixxer 150 negro, vista lateral urbana', 2),
(1398, 52, 'img/motos/suzuki-gixxer150/cusap-gixxer-03.jpg', 'Suzuki Gixxer 150 negro, vista lateral en marcha', 3),
(1399, 52, 'img/motos/suzuki-gixxer150/cusap-gixxer-04.jpg', 'Suzuki Gixxer 150 negro, detalle trasero', 4),
(1400, 53, 'img/motos/yamaha-fz25/azul-01.png', 'Yamaha FZ25 azul, fotografía de catálogo 01', 1),
(1401, 53, 'img/motos/yamaha-fz25/azul-02.png', 'Yamaha FZ25 azul, fotografía de catálogo 02', 2),
(1402, 53, 'img/motos/yamaha-fz25/azul-03.png', 'Yamaha FZ25 azul, fotografía de catálogo 03', 3),
(1403, 53, 'img/motos/yamaha-fz25/azul-04.png', 'Yamaha FZ25 azul, fotografía de catálogo 04', 4),
(1404, 53, 'img/motos/yamaha-fz25/azul-05.png', 'Yamaha FZ25 azul, fotografía de catálogo 05', 5),
(1405, 53, 'img/motos/yamaha-fz25/oficial-fz25-angulo-01.png', 'Yamaha FZ25 azul, vista de tres cuartos frontal en carretera', 6),
(1406, 53, 'img/motos/yamaha-fz25/oficial-fz25-angulo-02.png', 'Yamaha FZ25 azul, vista frontal en carretera', 7),
(1407, 53, 'img/motos/yamaha-fz25/oficial-fz25-angulo-03.png', 'Yamaha FZ25 azul, vista lateral en carretera', 8),
(1408, 56, 'img/motos/yamaha-r15/azul-01.png', 'Yamaha YZF-R15 ABS azul, fotografía de catálogo 01', 1),
(1409, 56, 'img/motos/yamaha-r15/azul-02.png', 'Yamaha YZF-R15 ABS azul, vista lateral de catálogo', 2),
(1410, 56, 'img/motos/yamaha-r15/oficial-r15-azul-lateral-01.jpg', 'Yamaha YZF-R15 ABS azul, vista lateral en pista', 3),
(1411, 56, 'img/motos/yamaha-r15/oficial-r15-azul-frontal-ruta-01.png', 'Yamaha YZF-R15 ABS azul, vista de tres cuartos en pista', 4),
(1412, 57, 'img/motos/yamaha-r15/negro-01.png', 'Yamaha YZF-R15 ABS negro, vista de catálogo', 1),
(1413, 57, 'img/motos/yamaha-r15/oficial-r15-negro-ruta-01.png', 'Yamaha YZF-R15 ABS negro, vista en carretera', 2),
(1414, 58, 'img/motos/yamaha-r15/blanco-azul-01.png', 'Yamaha YZF-R15 ABS blanco / azul, vista de catálogo', 1),
(1415, 58, 'img/motos/yamaha-r15/oficial-r15-blanco-azul-ruta-01.png', 'Yamaha YZF-R15 ABS blanco / azul, vista en carretera', 2),
(1416, 59, 'img/motos/yamaha-r15/rojo-01.png', 'Yamaha YZF-R15 ABS rojo, vista de catálogo', 1),
(1417, 59, 'img/motos/yamaha-r15/oficial-r15-rojo-noche-01.png', 'Yamaha YZF-R15 ABS rojo, vista nocturna en carretera', 2),
(1418, 54, 'img/motos/yamaha-mt09/azul-01.png', 'Yamaha MT-09 ABS azul, fotografía de catálogo 01', 1),
(1419, 54, 'img/motos/yamaha-mt09/azul-02.png', 'Yamaha MT-09 ABS azul, fotografía de catálogo 02', 2),
(1420, 54, 'img/motos/yamaha-mt09/azul-03.png', 'Yamaha MT-09 ABS azul, fotografía de catálogo 03', 3),
(1421, 54, 'img/motos/yamaha-mt09/azul-04.png', 'Yamaha MT-09 ABS azul, fotografía de catálogo 04', 4),
(1422, 54, 'img/motos/yamaha-mt09/azul-05.png', 'Yamaha MT-09 ABS azul, fotografía de catálogo 05', 5),
(1423, 54, 'img/motos/yamaha-mt09/oficial-mt09-ciudad-01.png', 'Yamaha MT-09 ABS azul, vista frontal en ciudad', 6),
(1424, 54, 'img/motos/yamaha-mt09/oficial-mt09-ciudad-02.png', 'Yamaha MT-09 ABS azul, vista lateral en ciudad', 7),
(1425, 54, 'img/motos/yamaha-mt09/oficial-mt09-ciudad-03.png', 'Yamaha MT-09 ABS azul, vista de tres cuartos en ciudad', 8),
(1426, 55, 'img/motos/yamaha-mt15/azul-01.png', 'Yamaha MT-15 azul, vista de catálogo', 1),
(1427, 55, 'img/motos/yamaha-mt15/azul-05.png', 'Yamaha MT-15 azul, vista lateral de catálogo', 2),
(1428, 55, 'img/motos/yamaha-mt15/galeria-14.jpg', 'Yamaha MT-15 azul, vista nocturna de tres cuartos', 3),
(1429, 55, 'img/motos/yamaha-mt15/galeria-18.jpg', 'Yamaha MT-15 azul, vista nocturna trasera', 4),
(1430, 55, 'img/motos/yamaha-mt15/galeria-19.jpg', 'Yamaha MT-15 azul, vista lateral en movimiento', 5),
(1431, 55, 'img/motos/yamaha-mt15/oficial-mt15-ciudad-01.png', 'Yamaha MT-15 azul, vista de tres cuartos en ciudad', 6),
(1432, 55, 'img/motos/yamaha-mt15/oficial-mt15-ciudad-02.png', 'Yamaha MT-15 azul, vista frontal nocturna', 7),
(1433, 55, 'img/motos/yamaha-mt15/oficial-mt15-ciudad-03.png', 'Yamaha MT-15 azul, vista frontal en túnel', 8),
(1434, 55, 'img/motos/yamaha-mt15/oficial-mt15-lateral-04.jpg', 'Yamaha MT-15 azul, vista lateral en ciudad', 9),
(1435, 55, 'img/motos/yamaha-mt15/oficial-mt15-marcha-05.jpg', 'Yamaha MT-15 azul, vista lateral en marcha', 10);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `moto_especificaciones`
--

CREATE TABLE `moto_especificaciones` (
  `id` int(10) UNSIGNED NOT NULL,
  `moto_id` int(10) UNSIGNED NOT NULL,
  `grupo` varchar(80) NOT NULL DEFAULT 'General',
  `nombre` varchar(100) NOT NULL,
  `valor` varchar(255) NOT NULL,
  `orden` smallint(5) UNSIGNED NOT NULL DEFAULT 1,
  `fuente_url` varchar(500) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `moto_especificaciones`
--

INSERT INTO `moto_especificaciones` (`id`, `moto_id`, `grupo`, `nombre`, `valor`, `orden`, `fuente_url`) VALUES
(250, 52, 'Motor', 'Cilindrada', '155 cc', 1, 'https://www.yamaha-motor.com.pe/yzf-r15-abs-600007'),
(251, 52, 'Motor', 'Potencia', '18.1 HP @ 10,000 rpm', 2, 'https://www.yamaha-motor.com.pe/yzf-r15-abs-600007'),
(252, 52, 'Motor', 'Torque', '14.2 Nm @ 7,500 rpm', 3, 'https://www.yamaha-motor.com.pe/yzf-r15-abs-600007'),
(253, 52, 'Tecnolog├¡a', 'Asistencias', 'ABS, VVA y control de tracci├│n', 4, 'https://www.yamaha-motor.com.pe/yzf-r15-abs-600007'),
(254, 52, 'Conectividad', 'Sistema', 'Y-Connect', 5, 'https://www.yamaha-motor.com.pe/yzf-r15-abs-600007'),
(255, 53, 'Motor', 'Cilindrada', '888 cc', 1, 'https://www.yamaha-motor.com.pe/mt-09-600022'),
(256, 53, 'Motor', 'Potencia', '117.3 HP @ 10,000 rpm', 2, 'https://www.yamaha-motor.com.pe/mt-09-600022'),
(257, 53, 'Motor', 'Torque', '93 Nm @ 7,000 rpm', 3, 'https://www.yamaha-motor.com.pe/mt-09-600022'),
(258, 53, 'Motor', 'Configuraci├│n', '3 cilindros DOHC, refrigeraci├│n l├¡quida', 4, 'https://www.yamaha-motor.com.pe/mt-09-600022'),
(259, 53, 'Tecnolog├¡a', 'Panel', 'TFT de 5 pulgadas con conectividad', 5, 'https://www.yamaha-motor.com.pe/mt-09-600022'),
(260, 54, 'Motor', 'Cilindrada', '155 cc', 1, 'https://www.yamaha-motor.com.pe/mt-15-600020'),
(261, 54, 'Motor', 'Potencia', '19 HP @ 10,000 rpm', 2, 'https://www.yamaha-motor.com.pe/mt-15-600020'),
(262, 54, 'Motor', 'Torque', '14.7 Nm @ 8,500 rpm', 3, 'https://www.yamaha-motor.com.pe/mt-15-600020'),
(263, 54, 'Tecnolog├¡a', 'Motor', 'SOHC con VVA y refrigeraci├│n l├¡quida', 4, 'https://www.yamaha-motor.com.pe/mt-15-600020'),
(264, 2, 'Seguridad', 'Frenos', 'Disco con ABS', 5, 'https://motocorp.pe/moto-pulsar-160ug2abs-rojo-gris-160ns-ug2abs-rg.html'),
(265, 2, 'Transmisión', 'Cambios', '5 velocidades', 4, 'https://motocorp.pe/moto-pulsar-160ug2abs-rojo-gris-160ns-ug2abs-rg.html'),
(266, 2, 'Motor', 'Torque', '14.6 Nm', 3, 'https://motocorp.pe/moto-pulsar-160ug2abs-rojo-gris-160ns-ug2abs-rg.html'),
(267, 2, 'Motor', 'Potencia', '17 HP', 2, 'https://motocorp.pe/moto-pulsar-160ug2abs-rojo-gris-160ns-ug2abs-rg.html'),
(268, 2, 'Motor', 'Cilindrada', '160.3 cc', 1, 'https://motocorp.pe/moto-pulsar-160ug2abs-rojo-gris-160ns-ug2abs-rg.html'),
(269, 1, 'Motor', 'Embrague', 'Antirrebote (slipper clutch)', 8, 'https://somosmoto.pe/bajaj-pulsar/naked/ns200-fi-ug2-2026/QGJ4'),
(270, 1, 'Tecnología', 'Tablero', 'Digital', 7, 'https://somosmoto.pe/bajaj-pulsar/naked/ns200-fi-ug2-2026/QGJ4'),
(271, 1, 'Suspensión', 'Delantera', 'Horquilla invertida (USD)', 6, 'https://somosmoto.pe/bajaj-pulsar/naked/ns200-fi-ug2-2026/QGJ4'),
(272, 1, 'Seguridad', 'Frenos', 'Discos con ABS', 5, 'https://somosmoto.pe/bajaj-pulsar/naked/ns200-fi-ug2-2026/QGJ4'),
(273, 1, 'Transmisión', 'Cambios', '6 velocidades', 4, 'https://somosmoto.pe/bajaj-pulsar/naked/ns200-fi-ug2-2026/QGJ4'),
(274, 1, 'Motor', 'Torque', '18.6 Nm @ 8,000 rpm', 3, 'https://somosmoto.pe/bajaj-pulsar/naked/ns200-fi-ug2-2026/QGJ4'),
(275, 1, 'Motor', 'Potencia', '24.12 HP', 2, 'https://somosmoto.pe/bajaj-pulsar/naked/ns200-fi-ug2-2026/QGJ4'),
(276, 1, 'Motor', 'Cilindrada', '199.5 cc', 1, 'https://somosmoto.pe/bajaj-pulsar/naked/ns200-fi-ug2-2026/QGJ4'),
(277, 9, 'Suspensión', 'Delantera', 'Horquilla invertida de 43 mm', 8, 'https://motocorp.pe/motocicleta-pulsar-rojo-negro-ns400-ns400-rn.html'),
(278, 9, 'Seguridad', 'Frenos', 'ABS de doble canal', 7, 'https://motocorp.pe/motocicleta-pulsar-rojo-negro-ns400-ns400-rn.html'),
(279, 9, 'Tecnología', 'Panel', 'Pantalla TFT de 5 pulgadas', 6, 'https://motocorp.pe/motocicleta-pulsar-rojo-negro-ns400-ns400-rn.html'),
(280, 9, 'Tecnología', 'Modos de manejo', 'Road, Rain, Sport y Off-road', 5, 'https://motocorp.pe/motocicleta-pulsar-rojo-negro-ns400-ns400-rn.html'),
(281, 9, 'Motor', 'Enfriamiento', 'Refrigeración líquida', 4, 'https://motocorp.pe/motocicleta-pulsar-rojo-negro-ns400-ns400-rn.html'),
(282, 9, 'Motor', 'Torque', '33.2 Nm @ 7,500 rpm', 3, 'https://motocorp.pe/motocicleta-pulsar-rojo-negro-ns400-ns400-rn.html'),
(283, 9, 'Motor', 'Potencia', '40.6 PS @ 9,000 rpm', 2, 'https://motocorp.pe/motocicleta-pulsar-rojo-negro-ns400-ns400-rn.html'),
(284, 9, 'Motor', 'Cilindrada', '349 cc', 1, 'https://motocorp.pe/motocicleta-pulsar-rojo-negro-ns400-ns400-rn.html'),
(285, 4, 'Suspensión', 'Delantera', 'WP telescópica', 8, 'https://www.ktm.com/es-gt/models/naked-bike/2026-ktm-200-duke.html'),
(286, 4, 'Seguridad', 'Frenos', 'Disco ABS', 7, 'https://www.ktm.com/es-gt/models/naked-bike/2026-ktm-200-duke.html'),
(287, 4, 'Capacidad', 'Tanque', '13.4 litros', 6, 'https://www.ktm.com/es-gt/models/naked-bike/2026-ktm-200-duke.html'),
(288, 4, 'Transmisión', 'Cambios', '6 velocidades', 5, 'https://www.ktm.com/es-gt/models/naked-bike/2026-ktm-200-duke.html'),
(289, 4, 'Dimensiones', 'Peso', '134 kg', 4, 'https://www.ktm.com/es-gt/models/naked-bike/2026-ktm-200-duke.html'),
(290, 4, 'Motor', 'Tipo', 'Monocilíndrico DOHC', 3, 'https://www.ktm.com/es-gt/models/naked-bike/2026-ktm-200-duke.html'),
(291, 4, 'Motor', 'Potencia', '25 HP', 2, 'https://www.ktm.com/es-gt/models/naked-bike/2026-ktm-200-duke.html'),
(292, 4, 'Motor', 'Cilindrada', '199 cc', 1, 'https://www.ktm.com/es-gt/models/naked-bike/2026-ktm-200-duke.html'),
(293, 5, 'Seguridad', 'Frenos', 'Disco delantero y trasero', 8, 'https://motocorp.pe/moto-gixxer-150-naked-azul-suzuki-gsx150-naked-az.html'),
(294, 5, 'Capacidad', 'Tanque', '12 litros', 7, 'https://motocorp.pe/moto-gixxer-150-naked-azul-suzuki-gsx150-naked-az.html'),
(295, 5, 'Dimensiones', 'Peso', '141 kg', 6, 'https://motocorp.pe/moto-gixxer-150-naked-azul-suzuki-gsx150-naked-az.html'),
(296, 5, 'Transmisión', 'Cambios', '5 velocidades', 5, 'https://motocorp.pe/moto-gixxer-150-naked-azul-suzuki-gsx150-naked-az.html'),
(297, 5, 'Motor', 'Refrigeración', 'Por aire', 4, 'https://motocorp.pe/moto-gixxer-150-naked-azul-suzuki-gsx150-naked-az.html'),
(298, 5, 'Motor', 'Torque', '13.8 Nm @ 6,000 rpm', 3, 'https://motocorp.pe/moto-gixxer-150-naked-azul-suzuki-gsx150-naked-az.html'),
(299, 5, 'Motor', 'Potencia', '13.41 HP @ 8,000 rpm', 2, 'https://motocorp.pe/moto-gixxer-150-naked-azul-suzuki-gsx150-naked-az.html'),
(300, 5, 'Motor', 'Cilindrada', '154.9 cc', 1, 'https://motocorp.pe/moto-gixxer-150-naked-azul-suzuki-gsx150-naked-az.html'),
(301, 6, 'Suspensión', 'Delantera', 'Telescópica', 8, 'https://www.yamaha-motor.com.pe/fz25-abs-connected-600035'),
(302, 6, 'Seguridad', 'Frenos', 'ABS doble canal y discos', 7, 'https://www.yamaha-motor.com.pe/fz25-abs-connected-600035'),
(303, 6, 'Capacidad', 'Tanque', '14 litros', 6, 'https://www.yamaha-motor.com.pe/fz25-abs-connected-600035'),
(304, 6, 'Transmisión', 'Cambios', '5 velocidades', 5, 'https://www.yamaha-motor.com.pe/fz25-abs-connected-600035'),
(305, 6, 'Motor', 'Tecnología', 'Blue Core, inyección electrónica', 4, 'https://www.yamaha-motor.com.pe/fz25-abs-connected-600035'),
(306, 6, 'Motor', 'Torque', '20 Nm @ 6,000 rpm', 3, 'https://www.yamaha-motor.com.pe/fz25-abs-connected-600035'),
(307, 6, 'Motor', 'Potencia', '20.65 HP @ 8,000 rpm', 2, 'https://www.yamaha-motor.com.pe/fz25-abs-connected-600035'),
(308, 6, 'Motor', 'Cilindrada', '249 cc', 1, 'https://www.yamaha-motor.com.pe/fz25-abs-connected-600035'),
(327, 53, 'Motor', 'Configuración', '3 cilindros DOHC, refrigeración líquida', 4, 'https://www.yamaha-motor.com.pe/mt-09-600022');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `moto_imagenes`
--

CREATE TABLE `moto_imagenes` (
  `id` int(10) UNSIGNED NOT NULL,
  `moto_id` int(10) UNSIGNED NOT NULL,
  `ruta` varchar(255) NOT NULL,
  `texto_alt` varchar(180) NOT NULL DEFAULT '',
  `orden` smallint(5) UNSIGNED NOT NULL DEFAULT 1,
  `es_principal` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `moto_imagenes`
--

INSERT INTO `moto_imagenes` (`id`, `moto_id`, `ruta`, `texto_alt`, `orden`, `es_principal`) VALUES
(3, 3, 'img/motos/Honda CB190R.jpg', 'Honda CB190R', 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ofertas`
--

CREATE TABLE `ofertas` (
  `id` int(10) UNSIGNED NOT NULL,
  `moto_id` int(10) UNSIGNED NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `descripcion` varchar(255) NOT NULL DEFAULT '',
  `precio_normal` decimal(10,2) NOT NULL,
  `precio_oferta` decimal(10,2) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `estado` enum('Activa','Inactiva','Vencida') NOT NULL DEFAULT 'Activa',
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ;

--
-- Volcado de datos para la tabla `ofertas`
--

INSERT INTO `ofertas` (`id`, `moto_id`, `titulo`, `descripcion`, `precio_normal`, `precio_oferta`, `fecha_inicio`, `fecha_fin`, `estado`, `creado_en`) VALUES
(1, 1, 'Precio especial', 'Precio promocional sujeto a vigencia y disponibilidad. Confirma los detalles con Norcentro.', 13169.00, 12649.00, '2026-09-27', '2026-10-02', 'Vencida', '2026-09-28 00:57:04'),
(4, 4, 'Oferta Norcentro', 'Consulta en tienda la vigencia y disponibilidad de esta promoción.', 14000.00, 12900.00, '2026-09-27', '2026-10-04', 'Vencida', '2026-09-28 01:24:54'),
(5, 6, 'Oferta Norcentro', 'Consulta en tienda la vigencia y disponibilidad de esta promoción.', 12500.00, 11900.00, '2026-09-27', '2026-10-04', 'Vencida', '2026-09-28 01:24:54'),
(6, 1, 'Precio especial', 'Precio promocional sujeto a vigencia y disponibilidad. Confirma los detalles con Norcentro.', 13169.00, 12649.00, '2026-09-29', '2026-10-04', 'Vencida', '2026-09-29 19:08:32');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos`
--

CREATE TABLE `pagos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `credito_id` bigint(20) UNSIGNED NOT NULL,
  `cuota_id` bigint(20) UNSIGNED DEFAULT NULL,
  `monto` decimal(10,2) NOT NULL,
  `fecha` date NOT NULL,
  `concepto` varchar(80) NOT NULL
) ;

--
-- Volcado de datos para la tabla `pagos`
--

INSERT INTO `pagos` (`id`, `credito_id`, `cuota_id`, `monto`, `fecha`, `concepto`) VALUES
(3, 2, 4, 500.00, '2026-09-29', 'Abono de cuota');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `request_id` char(36) NOT NULL,
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `dni` char(8) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `correo` varchar(120) NOT NULL,
  `modalidad` enum('Contado','Cr├®dito') NOT NULL,
  `inicial` decimal(10,2) NOT NULL DEFAULT 0.00,
  `numero_cuotas` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `credito_autorizado_en` datetime DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `estado` enum('Pendiente de pago','En revisión de crédito','Crédito aprobado','Crédito no aprobado','Pagado','Cancelado','Error de pago') NOT NULL DEFAULT 'Pendiente de pago',
  `mp_order_id` varchar(80) DEFAULT NULL,
  `checkout_url` varchar(1000) DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `vence_en` datetime DEFAULT NULL
) ;

--
-- Volcado de datos para la tabla `pedidos`
--

INSERT INTO `pedidos` (`id`, `request_id`, `nombres`, `apellidos`, `dni`, `telefono`, `correo`, `modalidad`, `inicial`, `numero_cuotas`, `credito_autorizado_en`, `subtotal`, `total`, `estado`, `mp_order_id`, `checkout_url`, `creado_en`, `vence_en`) VALUES
(1, '82598e20-cd26-486b-af9b-6df11e221e18', 'para', 'ruiz', '87654323', '456798765', 'notiene@gmail.com', '', 500.00, 12, '2026-09-29 08:49:20', 9000.00, 9000.00, 'En revisión de crédito', NULL, NULL, '2026-09-29 13:49:20', NULL),
(2, '39e4932c-2abf-42f0-98e7-db548affa400', 'frank erick', 'altamirano', '60031679', '900286035', 'altamiranofrank6@gmail.com', '', 0.00, 0, '2026-09-29 15:39:22', 12900.00, 12900.00, 'En revisión de crédito', NULL, NULL, '2026-09-29 20:39:22', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedido_items`
--

CREATE TABLE `pedido_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `pedido_id` bigint(20) UNSIGNED NOT NULL,
  `moto_id` int(10) UNSIGNED NOT NULL,
  `color_id` int(10) UNSIGNED DEFAULT NULL,
  `cantidad` smallint(5) UNSIGNED NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ;

--
-- Volcado de datos para la tabla `pedido_items`
--

INSERT INTO `pedido_items` (`id`, `pedido_id`, `moto_id`, `color_id`, `cantidad`, `precio_unitario`, `subtotal`) VALUES
(1, 1, 5, 51, 1, 9000.00, 9000.00),
(2, 2, 4, 49, 1, 12900.00, 12900.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedido_unidades`
--

CREATE TABLE `pedido_unidades` (
  `pedido_item_id` bigint(20) UNSIGNED NOT NULL,
  `unidad_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `nombre` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id`, `nombre`) VALUES
(1, 'Administrador'),
(2, 'Vendedor');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `unidades_moto`
--

CREATE TABLE `unidades_moto` (
  `id` int(10) UNSIGNED NOT NULL,
  `moto_id` int(10) UNSIGNED NOT NULL,
  `codigo_serie` varchar(50) NOT NULL,
  `estado` enum('Disponible','Reservada','Vendida') NOT NULL DEFAULT 'Disponible',
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `unidades_moto`
--

INSERT INTO `unidades_moto` (`id`, `moto_id`, `codigo_serie`, `estado`, `creado_en`) VALUES
(1, 1, 'NC-NS200-001', 'Disponible', '2026-09-28 00:57:04'),
(2, 1, 'NC-NS200-002', 'Disponible', '2026-09-28 00:57:04'),
(3, 2, 'NC-NS160-001', 'Vendida', '2026-09-28 00:57:04'),
(4, 3, 'NC-CB190-001', 'Disponible', '2026-09-28 00:57:04'),
(5, 4, 'NC-DUKE200-001', 'Disponible', '2026-09-28 00:57:04'),
(6, 5, 'NC-GIX150-001', 'Disponible', '2026-09-28 00:57:04'),
(7, 6, 'NC-FZ25-001', 'Disponible', '2026-09-28 00:57:04'),
(13, 9, 'NC-NS400Z-001', 'Disponible', '2026-09-28 01:35:56'),
(20, 53, 'NC-YAMAHA-53-001', 'Disponible', '2026-09-28 03:03:12'),
(21, 54, 'NC-YAMAHA-54-001', 'Disponible', '2026-09-28 03:03:12'),
(22, 52, 'NC-YAMAHA-52-001', 'Disponible', '2026-09-28 03:03:12');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(10) UNSIGNED NOT NULL,
  `rol_id` tinyint(3) UNSIGNED NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `estado` enum('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `rol_id`, `usuario`, `password_hash`, `nombre`, `estado`, `creado_en`) VALUES
(1, 1, 'admin', '$2y$10$G44jtYfys4zHTPoycxlmTe5Fv7CWqkylvSsMs999wZmAPQyjbHpGq', 'Administrador Norcentro', 'Activo', '2026-09-28 00:57:04'),
(2, 2, 'vendedor', '$2y$10$G44jtYfys4zHTPoycxlmTe5Fv7CWqkylvSsMs999wZmAPQyjbHpGq', 'Vendedor de prueba', 'Activo', '2026-09-28 00:57:04'),
(14, 1, 'admin_prueba', 'frank123', 'Administrador \r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n', 'Activo', '2026-09-28 19:41:39'),
(15, 1, 'admin.norcentro', '$2y$10$qzqxLCLTniAsten2RZlnI.81DsjKVQ8YLtw1C9wbVR2xplA6DfcfO', 'Administrador Norcentro', 'Activo', '2026-09-29 19:26:00'),
(16, 2, 'vendedor.norcentro', '$2y$10$u5Q6tXJi8k8h98SM8LNn5OLUhS621n4NNWIZ2po9ssjnkdB9cLGLC', 'Vendedor Norcentro', 'Activo', '2026-09-29 19:26:00'),
(17, 1, 'frank', '$2y$10$3Pt4QETH4KIWEKKOuOKAYuiQ7V//mxcAztzTd.P/WJDglfAKT6QvC', 'Frank', 'Activo', '2026-09-29 23:05:35'),
(18, 1, 'Christian123', '$2y$10$i9yarxV1q57txV..klGs1.CvJ4VyeEeNiU2WWEtIGGdftOc6atyBS', 'Christian', 'Activo', '2026-09-29 23:15:58'),
(19, 2, 'fabian', '$2y$10$PU6eDsxvSyTNgVJ3N5csq.MqEQAQ5QGUZHMuxTME1ze4eJ6qEqNay', 'Fabián', 'Activo', '2026-09-30 12:59:53');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas`
--

CREATE TABLE `ventas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `cliente_id` int(10) UNSIGNED NOT NULL,
  `usuario_id` int(10) UNSIGNED NOT NULL,
  `unidad_id` int(10) UNSIGNED NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `descuento` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL,
  `modalidad` enum('Contado','Crédito') NOT NULL,
  `estado` enum('Confirmada','Anulada') NOT NULL DEFAULT 'Confirmada',
  `fecha` datetime NOT NULL DEFAULT current_timestamp()
) ;

--
-- Volcado de datos para la tabla `ventas`
--

INSERT INTO `ventas` (`id`, `cliente_id`, `usuario_id`, `unidad_id`, `precio`, `descuento`, `total`, `modalidad`, `estado`, `fecha`) VALUES
(2, 2, 14, 3, 11900.00, 0.00, 11900.00, 'Crédito', 'Confirmada', '2026-09-28 14:49:14');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `dni` (`dni`);

--
-- Indices de la tabla `creditos`
--
ALTER TABLE `creditos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `venta_id` (`venta_id`);

--
-- Indices de la tabla `cuotas`
--
ALTER TABLE `cuotas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_cuota_numero` (`credito_id`,`numero`);

--
-- Indices de la tabla `marcas`
--
ALTER TABLE `marcas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `motos`
--
ALTER TABLE `motos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_motos_marca_modelo` (`marca_id`,`modelo`);

--
-- Indices de la tabla `moto_colores`
--
ALTER TABLE `moto_colores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_moto_colores_nombre` (`moto_id`,`nombre`);

--
-- Indices de la tabla `moto_color_imagenes`
--
ALTER TABLE `moto_color_imagenes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_moto_color_imagenes_ruta` (`color_id`,`ruta`);

--
-- Indices de la tabla `moto_especificaciones`
--
ALTER TABLE `moto_especificaciones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_moto_especificaciones_nombre` (`moto_id`,`nombre`);

--
-- Indices de la tabla `moto_imagenes`
--
ALTER TABLE `moto_imagenes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_moto_imagenes_ruta` (`moto_id`,`ruta`);

--
-- Indices de la tabla `ofertas`
--
ALTER TABLE `ofertas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_oferta_moto_titulo_inicio` (`moto_id`,`titulo`,`fecha_inicio`);

--
-- Indices de la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_pagos_creditos` (`credito_id`),
  ADD KEY `fk_pagos_cuotas` (`cuota_id`);

--
-- Indices de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `request_id` (`request_id`),
  ADD UNIQUE KEY `mp_order_id` (`mp_order_id`);

--
-- Indices de la tabla `pedido_items`
--
ALTER TABLE `pedido_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_pedido_items_pedidos` (`pedido_id`),
  ADD KEY `fk_pedido_items_motos` (`moto_id`),
  ADD KEY `fk_pedido_items_colores` (`color_id`);

--
-- Indices de la tabla `pedido_unidades`
--
ALTER TABLE `pedido_unidades`
  ADD PRIMARY KEY (`pedido_item_id`,`unidad_id`),
  ADD UNIQUE KEY `unidad_id` (`unidad_id`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `unidades_moto`
--
ALTER TABLE `unidades_moto`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo_serie` (`codigo_serie`),
  ADD KEY `fk_unidades_motos` (`moto_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario` (`usuario`),
  ADD KEY `fk_usuarios_roles` (`rol_id`);

--
-- Indices de la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unidad_id` (`unidad_id`),
  ADD KEY `fk_ventas_clientes` (`cliente_id`),
  ADD KEY `fk_ventas_usuarios` (`usuario_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `creditos`
--
ALTER TABLE `creditos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cuotas`
--
ALTER TABLE `cuotas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `marcas`
--
ALTER TABLE `marcas`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT de la tabla `motos`
--
ALTER TABLE `motos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `moto_colores`
--
ALTER TABLE `moto_colores`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=86;

--
-- AUTO_INCREMENT de la tabla `moto_color_imagenes`
--
ALTER TABLE `moto_color_imagenes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1436;

--
-- AUTO_INCREMENT de la tabla `moto_especificaciones`
--
ALTER TABLE `moto_especificaciones`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=329;

--
-- AUTO_INCREMENT de la tabla `moto_imagenes`
--
ALTER TABLE `moto_imagenes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `ofertas`
--
ALTER TABLE `ofertas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pagos`
--
ALTER TABLE `pagos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pedido_items`
--
ALTER TABLE `pedido_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `unidades_moto`
--
ALTER TABLE `unidades_moto`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `ventas`
--
ALTER TABLE `ventas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `creditos`
--
ALTER TABLE `creditos`
  ADD CONSTRAINT `fk_creditos_ventas` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`);

--
-- Filtros para la tabla `cuotas`
--
ALTER TABLE `cuotas`
  ADD CONSTRAINT `fk_cuotas_creditos` FOREIGN KEY (`credito_id`) REFERENCES `creditos` (`id`);

--
-- Filtros para la tabla `motos`
--
ALTER TABLE `motos`
  ADD CONSTRAINT `fk_motos_marcas` FOREIGN KEY (`marca_id`) REFERENCES `marcas` (`id`);

--
-- Filtros para la tabla `moto_colores`
--
ALTER TABLE `moto_colores`
  ADD CONSTRAINT `fk_moto_colores_motos` FOREIGN KEY (`moto_id`) REFERENCES `motos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `moto_color_imagenes`
--
ALTER TABLE `moto_color_imagenes`
  ADD CONSTRAINT `fk_moto_color_imagenes_color` FOREIGN KEY (`color_id`) REFERENCES `moto_colores` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `moto_especificaciones`
--
ALTER TABLE `moto_especificaciones`
  ADD CONSTRAINT `fk_moto_especificaciones_motos` FOREIGN KEY (`moto_id`) REFERENCES `motos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `moto_imagenes`
--
ALTER TABLE `moto_imagenes`
  ADD CONSTRAINT `fk_moto_imagenes_motos` FOREIGN KEY (`moto_id`) REFERENCES `motos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `ofertas`
--
ALTER TABLE `ofertas`
  ADD CONSTRAINT `fk_ofertas_motos` FOREIGN KEY (`moto_id`) REFERENCES `motos` (`id`);

--
-- Filtros para la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD CONSTRAINT `fk_pagos_creditos` FOREIGN KEY (`credito_id`) REFERENCES `creditos` (`id`),
  ADD CONSTRAINT `fk_pagos_cuotas` FOREIGN KEY (`cuota_id`) REFERENCES `cuotas` (`id`);

--
-- Filtros para la tabla `pedido_items`
--
ALTER TABLE `pedido_items`
  ADD CONSTRAINT `fk_pedido_items_colores` FOREIGN KEY (`color_id`) REFERENCES `moto_colores` (`id`),
  ADD CONSTRAINT `fk_pedido_items_motos` FOREIGN KEY (`moto_id`) REFERENCES `motos` (`id`),
  ADD CONSTRAINT `fk_pedido_items_pedidos` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`);

--
-- Filtros para la tabla `pedido_unidades`
--
ALTER TABLE `pedido_unidades`
  ADD CONSTRAINT `fk_pedido_unidades_items` FOREIGN KEY (`pedido_item_id`) REFERENCES `pedido_items` (`id`),
  ADD CONSTRAINT `fk_pedido_unidades_unidades` FOREIGN KEY (`unidad_id`) REFERENCES `unidades_moto` (`id`);

--
-- Filtros para la tabla `unidades_moto`
--
ALTER TABLE `unidades_moto`
  ADD CONSTRAINT `fk_unidades_motos` FOREIGN KEY (`moto_id`) REFERENCES `motos` (`id`);

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `fk_usuarios_roles` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`);

--
-- Filtros para la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD CONSTRAINT `fk_ventas_clientes` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`),
  ADD CONSTRAINT `fk_ventas_unidades` FOREIGN KEY (`unidad_id`) REFERENCES `unidades_moto` (`id`),
  ADD CONSTRAINT `fk_ventas_usuarios` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
