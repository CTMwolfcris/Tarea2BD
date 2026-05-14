-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 14-05-2026 a las 04:53:55
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
-- Base de datos: `tarea2_bd`
--

DELIMITER $$
--
-- Procedimientos
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_registrar_evaluacion` (IN `p_postulacion_id` INT, IN `p_evaluador_rut` VARCHAR(12), IN `p_comentario` TEXT, IN `p_nuevo_estado` INT)   BEGIN
  DECLARE EXIT HANDLER FOR SQLEXCEPTION
  BEGIN
    ROLLBACK;
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error al registrar evaluacion';
  END;

  START TRANSACTION;
    -- Registrar la evaluacion
    INSERT INTO evaluacion (EV_Postulacion_ID, EV_Evaluador_Rut, EV_Comentario, EV_Estado_Nuevo)
    VALUES (p_postulacion_id, p_evaluador_rut, p_comentario, p_nuevo_estado);

    -- Actualizar estado de la postulacion
    UPDATE postulacion
    SET P_Estado_ID = p_nuevo_estado
    WHERE P_Id = p_postulacion_id;
  COMMIT;
END$$

--
-- Funciones
--
CREATE DEFINER=`root`@`localhost` FUNCTION `fn_total_semanas` (`p_id` INT) RETURNS INT(11) DETERMINISTIC BEGIN
  DECLARE total INT;
  SELECT COALESCE(SUM(ET_Semanas), 0) INTO total
  FROM etapa
  WHERE ET_Postulacion_ID = p_id;
  RETURN total;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asignacion_evaluador`
--

CREATE TABLE `asignacion_evaluador` (
  `AE_Id` int(11) NOT NULL,
  `AE_Postulacion_ID` int(11) NOT NULL,
  `AE_Evaluador_Rut` varchar(12) NOT NULL,
  `AE_Fecha_Asignacion` datetime NOT NULL DEFAULT current_timestamp(),
  `AE_Activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `asignacion_evaluador`
--

INSERT INTO `asignacion_evaluador` (`AE_Id`, `AE_Postulacion_ID`, `AE_Evaluador_Rut`, `AE_Fecha_Asignacion`, `AE_Activo`) VALUES
(1, 1, '08.555.444-2', '2026-03-03 08:00:00', 1),
(2, 2, '12.555.666-K', '2026-03-07 08:00:00', 1),
(3, 3, '12.555.666-K', '2026-03-12 08:00:00', 1),
(4, 4, '14.777.888-9', '2026-03-14 08:00:00', 1),
(5, 5, '08.555.444-2', '2026-03-17 08:00:00', 1),
(6, 6, '08.555.444-2', '2026-03-20 08:00:00', 1),
(7, 7, '14.777.888-9', '2026-03-22 08:00:00', 1),
(8, 8, '12.555.666-K', '2026-03-24 08:00:00', 1),
(9, 9, '08.555.444-2', '2026-03-27 08:00:00', 1),
(10, 10, '14.777.888-9', '2026-03-30 08:00:00', 1),
(11, 11, '12.555.666-K', '2026-04-03 08:00:00', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `campus`
--

CREATE TABLE `campus` (
  `C_Id` int(11) NOT NULL,
  `C_Nombre` varchar(100) NOT NULL,
  `C_Calle` varchar(100) NOT NULL,
  `C_ID_Region` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `campus`
--

INSERT INTO `campus` (`C_Id`, `C_Nombre`, `C_Calle`, `C_ID_Region`) VALUES
(1, 'Campus Casa Central Valparaiso', 'Avenida España 1680', 5),
(2, 'Campus San Joaquin', 'Avenida Vicuña Mackenna 3939', 13),
(3, 'Campus Vitacura', 'Avenida Santa María 6400', 13),
(4, 'Sede Viña Del Mar', 'Avenida Federico Santa María 6090', 5),
(5, 'Sede Concepcion', 'Arteaga Alemparte 943', 8);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empresaexterna`
--

CREATE TABLE `empresaexterna` (
  `E_Rut` varchar(12) NOT NULL,
  `E_Nombre` varchar(100) NOT NULL,
  `E_NombreRepresentante` varchar(100) NOT NULL,
  `E_MailRepresentante` varchar(100) NOT NULL,
  `E_NumeroRepresentante` varchar(12) NOT NULL,
  `E_Tamaño` int(11) NOT NULL,
  `E_ConvenioUSM` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `empresaexterna`
--

INSERT INTO `empresaexterna` (`E_Rut`, `E_Nombre`, `E_NombreRepresentante`, `E_MailRepresentante`, `E_NumeroRepresentante`, `E_Tamaño`, `E_ConvenioUSM`) VALUES
('76.123.456-7', 'Codelco', 'Juan Minero', 'j.minero@codelco.cl', '988887777', 3, 1),
('76.843.468-1', 'Meta Platforms Chile SpA', 'Mark Zuckerberg', 'termsofservice@meta.com', '900000000', 3, 0),
('77.333.222-1', 'TecnoPyme', 'Ana Bits', 'a.bits@tecnopyme.cl', '944443333', 1, 0),
('81.999.000-4', 'EcoEnergia', 'Raul Solar', 'r.solar@eco.cl', '955554444', 2, 1),
('88.444.555-2', 'NotCo', 'Karina Alimento', 'k.alimento@notco.com', '966665555', 2, 0),
('92.333.444-5', 'Indra Company Chile', 'Gerardo Tecnico', 'g.tecnico@indracompany.com', '933334444', 3, 1),
('96.777.888-K', 'Cornershop', 'Luis Entrega', 'l.entrega@uber.com', '911112222', 3, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `equipotrabajo`
--

CREATE TABLE `equipotrabajo` (
  `EQ_Rut_Integrante` varchar(12) NOT NULL,
  `EQ_Id_Postulacion` int(11) NOT NULL,
  `EQ_AreaEspecializacion` varchar(50) NOT NULL,
  `EQ_Rol` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `equipotrabajo`
--

INSERT INTO `equipotrabajo` (`EQ_Rut_Integrante`, `EQ_Id_Postulacion`, `EQ_AreaEspecializacion`, `EQ_Rol`) VALUES
('07.666.555-4', 4, 'Web', 'Asesor'),
('07.666.555-4', 9, 'Grid', 'Investigador'),
('08.555.444-2', 4, 'Web', 'Investigador'),
('08.555.444-2', 9, 'Grid', 'Director'),
('10.222.111-4', 5, 'Renovable', 'Asesor'),
('10.222.111-4', 10, 'Aereo', 'Asesor'),
('10.333.444-1', 2, 'Bio', 'Investigador'),
('10.333.444-1', 11, 'Salud', 'Director'),
('10.432.111-2', 2, 'Bio', 'Asesor'),
('10.432.111-2', 6, 'IA', 'Investigador'),
('10.888.777-6', 5, 'Renovable', 'Director'),
('10.888.777-6', 10, 'Aereo', 'Director'),
('11.222.333-4', 3, 'Log', 'Investigador'),
('11.222.333-4', 7, 'Bio', 'Investigador'),
('11.777.666-9', 5, 'Renovable', 'Investigador'),
('11.777.666-9', 10, 'Aereo', 'Investigador'),
('11.999.888-0', 2, 'Bio', 'Director'),
('11.999.888-0', 8, 'Ciber', 'Asesor'),
('12.555.666-K', 3, 'Log', 'Director'),
('12.555.666-K', 7, 'Bio', 'Director'),
('14.555.666-7', 1, 'IA', 'Director'),
('14.555.666-7', 6, 'IA', 'Director'),
('14.555.666-7', 11, 'IA', 'Investigador'),
('14.777.888-9', 3, 'Log', 'Asesor'),
('14.777.888-9', 7, 'Bio', 'Asesor'),
('14.777.888-9', 8, 'Ciber', 'Director'),
('15.111.222-3', 4, 'Web', 'Director'),
('15.111.222-3', 8, 'Ciber', 'Investigador'),
('15.222.333-K', 1, 'IA', 'Investigador'),
('15.222.333-K', 11, 'Datos', 'Asesor'),
('18.222.333-4', 1, 'IA', 'Asesor'),
('18.222.333-4', 6, 'IA', 'Asesor'),
('19.112.554-1', 4, 'Dev', 'Desarrollador'),
('19.112.554-1', 8, 'Dev', 'Desarrollador'),
('19.332.887-K', 3, 'Doc', 'Documentacion'),
('19.442.881-5', 5, 'QA', 'Tester'),
('19.442.881-5', 9, 'QA', 'Tester'),
('19.455.102-3', 1, 'Dev', 'Desarrollador'),
('19.455.102-3', 6, 'Dev', 'Desarrollador'),
('19.455.102-3', 10, 'Dev', 'Desarrollador'),
('19.554.223-4', 4, 'Dev', 'Desarrollador'),
('19.554.223-4', 8, 'Dev', 'Desarrollador'),
('19.776.442-9', 2, 'Dev', 'Desarrollador'),
('19.776.442-9', 7, 'Dev', 'Desarrollador'),
('19.776.442-9', 11, 'Dev', 'Desarrollador'),
('19.882.334-K', 1, 'QA', 'Tester'),
('19.882.334-K', 6, 'QA', 'Tester'),
('19.887.332-0', 5, 'Sup', 'Apoyo'),
('19.887.332-0', 10, 'Sup', 'Apoyo'),
('19.992.110-6', 2, 'Dev', 'Desarrollador'),
('19.992.110-6', 7, 'Dev', 'Desarrollador'),
('19.992.110-6', 11, 'Dev', 'Desarrollador'),
('20.122.544-1', 1, 'Doc', 'Documentacion'),
('20.122.544-1', 6, 'Doc', 'Documentacion'),
('20.221.776-8', 4, 'Doc', 'Documentacion'),
('20.221.776-8', 8, 'Doc', 'Documentacion'),
('20.221.776-8', 10, 'Doc', 'Documentacion'),
('20.344.981-2', 1, 'Dev', 'Desarrollador'),
('20.344.981-2', 6, 'Dev', 'Desarrollador'),
('20.443.112-5', 3, 'QA', 'Tester'),
('20.443.112-5', 10, 'QA', 'Tester'),
('20.551.887-4', 2, 'QA', 'Tester'),
('20.551.887-4', 7, 'QA', 'Tester'),
('20.551.887-4', 11, 'QA', 'Tester'),
('20.554.112-6', 5, 'Dev', 'Desarrollador'),
('20.665.119-0', 3, 'Sup', 'Apoyo'),
('20.776.443-2', 5, 'Dev', 'Desarrollador'),
('20.776.443-2', 9, 'Dev', 'Desarrollador'),
('20.883.221-7', 2, 'Sup', 'Apoyo'),
('20.883.221-7', 7, 'Sup', 'Apoyo'),
('21.001.223-9', 5, 'Doc', 'Documentacion'),
('21.001.223-9', 9, 'Doc', 'Documentacion'),
('21.002.331-5', 1, 'Sup', 'Apoyo'),
('21.002.331-5', 6, 'Sup', 'Apoyo'),
('21.002.331-5', 11, 'Dev', 'Desarrollador'),
('21.055.443-2', 3, 'Dev', 'Desarrollador'),
('21.055.443-2', 10, 'Dev', 'Desarrollador'),
('21.112.443-8', 2, 'Doc', 'Documentacion'),
('21.112.443-8', 7, 'Doc', 'Documentacion'),
('21.112.443-8', 11, 'Doc', 'Documentacion'),
('21.119.882-7', 4, 'QA', 'Tester'),
('21.119.882-7', 8, 'QA', 'Tester'),
('21.221.998-3', 3, 'Dev', 'Desarrollador'),
('21.332.001-K', 4, 'Sup', 'Apoyo'),
('21.332.001-K', 8, 'Sup', 'Apoyo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estadopostulacion`
--

CREATE TABLE `estadopostulacion` (
  `EP_Id` int(11) NOT NULL,
  `EP_Nombre` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estadopostulacion`
--

INSERT INTO `estadopostulacion` (`EP_Id`, `EP_Nombre`) VALUES
(1, 'Borrador'),
(2, 'Enviada'),
(3, 'En Revision'),
(4, 'Aprobada'),
(5, 'Rechazada'),
(6, 'Cerrada');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `etapa`
--

CREATE TABLE `etapa` (
  `ET_Id` int(11) NOT NULL,
  `ET_Nombre` varchar(100) NOT NULL,
  `ET_Semanas` int(11) NOT NULL,
  `ET_Entregable` varchar(100) NOT NULL,
  `ET_Postulacion_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `etapa`
--

INSERT INTO `etapa` (`ET_Id`, `ET_Nombre`, `ET_Semanas`, `ET_Entregable`, `ET_Postulacion_ID`) VALUES
(1, 'Diseno IA', 4, 'Arquitectura', 1),
(2, 'Entrenamiento', 8, 'Modelo', 1),
(3, 'Pruebas', 3, 'Reporte', 1),
(4, 'Investigacion NotCo', 5, 'Viabilidad', 2),
(5, 'Desarrollo', 7, 'Muestras', 2),
(6, 'QA Sensorial', 3, 'Informe', 2),
(7, 'Requisitos App', 3, 'Especificacion', 3),
(8, 'Desarrollo App', 6, 'Beta', 3),
(9, 'Despliegue', 2, 'Reporte', 3),
(10, 'UX/UI Pyme', 3, 'Mockups', 4),
(11, 'Desarrollo Web', 5, 'Portal', 4),
(12, 'Capacitacion', 2, 'Manual', 4),
(13, 'Factibilidad Solar', 4, 'Estudio', 5),
(14, 'Diseno Hardware', 8, 'Planos', 5),
(15, 'Prototipo', 4, 'Funcional', 5),
(16, 'Normativa Vuelo', 3, 'Permisos', 6),
(17, 'Armado Dron', 6, 'Hardware', 6),
(18, 'Prueba Faena', 4, 'Video', 6),
(19, 'Modelado Quimico', 5, 'Estructura', 7),
(20, 'Simulacion', 6, 'Datos', 7),
(21, 'Validacion', 3, 'Informe Final', 7),
(22, 'Auditoria Indra', 4, 'Vulnerabilidades', 8),
(23, 'Hardening', 6, 'Sistemas OK', 8),
(24, 'Pentesting', 4, 'Reporte Final', 8),
(25, 'Diseno Red Grid', 4, 'Topologia', 9),
(26, 'Despliegue Sensores', 6, 'Red Piloto', 9),
(27, 'Analisis Datos', 4, 'Dashboard', 9),
(28, 'Analisis Trafico Aereo', 5, 'Modelo Datos', 10),
(29, 'Desarrollo Sistema IA', 8, 'Sistema Beta', 10),
(30, 'Pruebas en Simulador', 4, 'Certificado Pruebas', 10),
(31, 'Recoleccion de Datos', 4, 'Dataset Limpio', 11),
(32, 'Entrenamiento Red Neuronal', 10, 'Modelo .h5', 11),
(33, 'Validacion Clinica', 6, 'Informe Medico', 11),
(34, 'Diseno Borrador Meta', 3, 'Wireframes', 12);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `evaluacion`
--

CREATE TABLE `evaluacion` (
  `EV_Id` int(11) NOT NULL,
  `EV_Postulacion_ID` int(11) NOT NULL,
  `EV_Evaluador_Rut` varchar(12) NOT NULL,
  `EV_Fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `EV_Comentario` text NOT NULL,
  `EV_Estado_Nuevo` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `evaluacion`
--

INSERT INTO `evaluacion` (`EV_Id`, `EV_Postulacion_ID`, `EV_Evaluador_Rut`, `EV_Fecha`, `EV_Comentario`, `EV_Estado_Nuevo`) VALUES
(1, 1, '08.555.444-2', '2026-03-05 10:00:00', 'Proyecto solido con buen respaldo tecnico. Se aprueba.', 4),
(2, 3, '12.555.666-K', '2026-03-15 11:00:00', 'Algoritmo bien planteado. Presupuesto razonable.', 4),
(3, 4, '14.777.888-9', '2026-03-17 09:00:00', 'Presupuesto excesivo para el alcance propuesto.', 5),
(4, 6, '08.555.444-2', '2026-03-23 14:00:00', 'Tecnologia innovadora con respaldo regulatorio.', 4),
(5, 8, '12.555.666-K', '2026-03-27 10:00:00', 'Cumple estandares de seguridad requeridos.', 4),
(6, 10, '14.777.888-9', '2026-04-02 09:00:00', 'Sistema complejo bien documentado.', 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `integrantes`
--

CREATE TABLE `integrantes` (
  `I_Rut` varchar(12) NOT NULL,
  `I_Nombre` varchar(100) NOT NULL,
  `I_Tipo_ID` int(11) NOT NULL,
  `I_Campus` int(11) NOT NULL,
  `I_Email` varchar(100) NOT NULL,
  `I_Telefono` varchar(15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `integrantes`
--

INSERT INTO `integrantes` (`I_Rut`, `I_Nombre`, `I_Tipo_ID`, `I_Campus`, `I_Email`, `I_Telefono`) VALUES
('07.666.555-4', 'Luis Hevia', 1, 1, 'luis.hevia@usm.cl', '+56912345601'),
('08.555.444-2', 'Hernan Astudillo', 1, 1, 'hernan.astudillo@usm.cl', '+56912345602'),
('10.222.111-4', 'Xavier Bonnaire', 1, 1, 'xavier.bonnaire@usm.cl', '+56912345603'),
('10.333.444-1', 'Federico Meza', 1, 1, 'federico.meza@usm.cl', '+56912345604'),
('10.432.111-2', 'Agustin Gonzalez', 1, 1, 'agustin.gonzalez@usm.cl', '+56912345605'),
('10.888.777-6', 'Mauricio Solar', 1, 2, 'mauricio.solar@usm.cl', '+56912345606'),
('11.222.333-4', 'Carlos Castro', 1, 1, 'carlos.castro@usm.cl', '+56912345607'),
('11.777.666-9', 'Ricardo Nanculef', 1, 1, 'ricardo.nanculef@usm.cl', '+56912345608'),
('11.999.888-0', 'Jose Luis Marti', 1, 2, 'jose.marti@usm.cl', '+56912345609'),
('12.555.666-K', 'Andrea Vasquez', 1, 2, 'andrea.vasquez@usm.cl', '+56912345610'),
('14.555.666-7', 'Ricardo Salas', 1, 1, 'ricardo.salas@usm.cl', '+56912345611'),
('14.777.888-9', 'Claudio Torres', 1, 2, 'claudio.torres@usm.cl', '+56912345612'),
('15.111.222-3', 'Diego Aracena', 1, 1, 'diego.aracena@usm.cl', '+56912345613'),
('15.222.333-K', 'Mauricio Figueroa', 1, 2, 'mauricio.figueroa@usm.cl', '+56912345614'),
('18.222.333-4', 'Claudio Lobos', 1, 1, 'claudio.lobos@usm.cl', '+56912345615'),
('19.112.554-1', 'Felipe Carcamo', 2, 1, 'f.car@usm.cl', '+56912345616'),
('19.332.887-K', 'Andres Valdes', 2, 1, 'a.val@usm.cl', '+56912345617'),
('19.442.881-5', 'Emilia Santibanez', 2, 2, 'e.san@usm.cl', '+56912345618'),
('19.455.102-3', 'Matias Lagos', 2, 1, 'm.lagos@usm.cl', '+56912345619'),
('19.554.223-4', 'Catalina Farias', 2, 2, 'c.far@usm.cl', '+56912345620'),
('19.776.442-9', 'Florencia Saavedra', 2, 2, 'f.saav@usm.cl', '+56912345621'),
('19.882.334-K', 'Valentina Paz', 2, 1, 'v.paz@usm.cl', '+56912345622'),
('19.887.332-0', 'Renato Zuniga', 2, 1, 'r.zun@usm.cl', '+56912345623'),
('19.992.110-6', 'Maximiliano Leiva', 2, 1, 'm.lei@usm.cl', '+56912345624'),
('20.122.544-1', 'Lucas Mardones', 2, 2, 'l.mar@usm.cl', '+56912345625'),
('20.221.776-8', 'Constanza Jara', 2, 2, 'c.jar@usm.cl', '+56912345626'),
('20.344.981-2', 'Isidora Vera', 2, 2, 'i.vera@usm.cl', '+56912345627'),
('20.443.112-5', 'Fernanda Rojas', 2, 2, 'f.roj@usm.cl', '+56912345628'),
('20.551.887-4', 'Vicente Guerra', 2, 1, 'v.guerra@usm.cl', '+56912345629'),
('20.554.112-6', 'Pascale Ibanez', 2, 2, 'p.iba@usm.cl', '+56912345630'),
('20.665.119-0', 'Cristobal Pena', 2, 1, 'c.pen@usm.cl', '+56912345631'),
('20.776.443-2', 'Bastian Rivas', 2, 1, 'b.riv@usm.cl', '+56912345632'),
('20.883.221-7', 'Martina Escobar', 2, 2, 'm.esco@usm.cl', '+56912345633'),
('21.001.223-9', 'Ignacio Tello', 2, 1, 'i.tel@usm.cl', '+56912345634'),
('21.002.331-5', 'Benjamin Toro', 2, 1, 'b.toro@usm.cl', '+56912345635'),
('21.055.443-2', 'Sofia Henriquez', 2, 2, 's.hen@usm.cl', '+56912345636'),
('21.112.443-8', 'Antonia Fuenzalida', 2, 2, 'a.fuen@usm.cl', '+56912345637'),
('21.119.882-7', 'Gabriel Godoy', 2, 1, 'g.god@usm.cl', '+56912345638'),
('21.221.998-3', 'Joaquin Araya', 2, 1, 'j.ara@usm.cl', '+56912345639'),
('21.332.001-K', 'Javiera Orellana', 2, 2, 'j.ore@usm.cl', '+56912345640');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `postulacion`
--

CREATE TABLE `postulacion` (
  `P_Id` int(11) NOT NULL,
  `P_Codigo_interno` varchar(50) NOT NULL,
  `P_Nombre` varchar(100) NOT NULL,
  `P_Presupuesto` int(11) NOT NULL,
  `P_Fecha` date NOT NULL,
  `P_Descripcion` text NOT NULL,
  `P_Objetivo` text DEFAULT NULL,
  `P_Solucion` text DEFAULT NULL,
  `P_Resultados_Esperados` text DEFAULT NULL,
  `P_Otros_Documentos` varchar(255) DEFAULT NULL,
  `P_Iniciativa_ID` int(11) NOT NULL,
  `P_Responsable1_Rut` varchar(12) NOT NULL,
  `P_Responsable2_Rut` varchar(12) NOT NULL,
  `P_Empresa_Rut` varchar(12) NOT NULL,
  `P_Region_Realizar` int(11) NOT NULL,
  `P_Region_Impacto` int(11) NOT NULL,
  `P_Estado_ID` int(11) NOT NULL DEFAULT 1,
  `P_ID_Campus` int(11) NOT NULL,
  `P_Fecha_Envio` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `postulacion`
--

INSERT INTO `postulacion` (`P_Id`, `P_Codigo_interno`, `P_Nombre`, `P_Presupuesto`, `P_Fecha`, `P_Descripcion`, `P_Objetivo`, `P_Solucion`, `P_Resultados_Esperados`, `P_Otros_Documentos`, `P_Iniciativa_ID`, `P_Responsable1_Rut`, `P_Responsable2_Rut`, `P_Empresa_Rut`, `P_Region_Realizar`, `P_Region_Impacto`, `P_Estado_ID`, `P_ID_Campus`, `P_Fecha_Envio`) VALUES
(1, 'proy-001', 'Mineria Inteligente', 15000000, '2026-03-01', 'IA para optimizar operaciones en faenas mineras de Codelco', 'Implementar IA en operaciones de extraccion', 'Sistema de vision computacional para deteccion de fallas', 'Reduccion de 30% en tiempos de parada', 'anexo_tecnico_codelco.pdf', 1, '14.555.666-7', '15.222.333-K', '76.123.456-7', 2, 2, 4, 1, '2026-03-02 10:00:00'),
(2, 'proy-002', 'Alimentos Pro', 12000000, '2026-03-05', 'Formulacion de nuevos alimentos plant-based para NotCo', 'Desarrollar formula proteica de origen vegetal', 'Analisis quimico y pruebas sensoriales de laboratorio', '3 nuevas formulaciones certificadas', 'cert_sanitario.pdf', 2, '15.222.333-K', '18.222.333-4', '88.444.555-2', 13, 13, 3, 2, '2026-03-06 09:00:00'),
(3, 'proy-003', 'App Despacho', 8000000, '2026-03-10', 'Optimizacion de rutas para Cornershop', 'Reducir tiempo promedio de despacho', 'Algoritmo de ruteo con IA y datos en tiempo real', 'Reduccion de 20% en tiempos de entrega', 'api_docs.zip', 1, '14.555.666-7', '11.999.888-0', '96.777.888-K', 5, 13, 4, 1, '2026-03-11 11:00:00'),
(4, 'proy-004', 'Web Pyme', 5000000, '2026-03-12', 'Portal e-commerce para TecnoPyme', 'Digitalizar ventas de pyme local', 'Desarrollo web con carrito de compras y pagos', 'Plataforma operativa con 100 productos cargados', 'plan_negocios.pdf', 1, '18.222.333-4', '10.333.444-1', '77.333.222-1', 5, 5, 5, 1, '2026-03-13 08:00:00'),
(5, 'proy-005', 'Solar Valpo', 25000000, '2026-03-15', 'Sistema de energia renovable solar para campus', 'Instalar paneles solares en techo del campus', 'Diseño electrico e instalacion de 200 paneles', 'Ahorro de 40% en consumo electrico', 'impacto_ambiental.pdf', 2, '11.999.888-0', '14.555.666-7', '81.999.000-4', 5, 5, 3, 2, '2026-03-16 10:30:00'),
(6, 'proy-006', 'Drones Codelco', 30000000, '2026-03-18', 'Monitoreo de faenas mineras con drones autonomos', 'Reducir riesgo en inspeccion de areas peligrosas', 'Flota de drones con sensores y IA a bordo', 'Inspeccion completa de faena en menos de 2 horas', 'permisos_dgac.pdf', 1, '14.555.666-7', '10.432.111-2', '76.123.456-7', 2, 3, 4, 1, '2026-03-19 09:00:00'),
(7, 'proy-007', 'Moleculas Not', 9000000, '2026-03-20', 'Analisis quimico de moleculas de origen vegetal', 'Mapear moleculas con potencial proteico', 'Simulacion computacional y validacion experimental', 'Base de datos de 500 moleculas documentadas', 'estudio_quimico.pdf', 2, '12.555.666-K', '11.222.333-4', '88.444.555-2', 13, 13, 3, 2, '2026-03-21 08:00:00'),
(8, 'proy-008', 'Ciberseguridad Trenes', 28000000, '2026-03-22', 'Proteccion de infraestructura critica ferroviaria', 'Asegurar sistemas SCADA de red ferroviaria', 'Auditoria, hardening y pentesting de sistemas', 'Certificacion ISO 27001 para sistemas criticos', 'manual_iso27001.pdf', 1, '14.777.888-9', '15.111.222-3', '92.333.444-5', 13, 13, 4, 2, '2026-03-23 10:00:00'),
(9, 'proy-009', 'Grid Inteligente', 14000000, '2026-03-25', 'Smart Grids para distribucion eficiente de energia', 'Modernizar red de distribucion electrica USM', 'Despliegue de sensores IoT y plataforma de monitoreo', 'Reduccion de perdidas de energia en 25%', 'diagrama_electrico.dwg', 1, '08.555.444-2', '07.666.555-4', '81.999.000-4', 13, 5, 3, 1, '2026-03-26 09:00:00'),
(10, 'proy-010', 'Control Trafico Aereo', 45000000, '2026-03-28', 'Optimizacion de control de trafico aereo con Indra', 'Mejorar precision y eficiencia del control aereo', 'Sistema de IA para prediccion de flujos de trafico', 'Reduccion de 15% en retrasos en aeropuerto SCL', 'cert_indra_global.pdf', 2, '10.888.777-6', '11.777.666-9', '92.333.444-5', 13, 13, 4, 2, '2026-03-29 11:00:00'),
(11, 'proy-011', 'IA en Salud Publica', 22000000, '2026-04-01', 'Deteccion temprana de enfermedades con IA', 'Reducir tiempo de diagnostico en hospitales publicos', 'Red neuronal entrenada con datos anonimizados', 'Precision de 90% en deteccion temprana', 'anexo_salud.pdf', 1, '10.333.444-1', '14.555.666-7', '92.333.444-5', 13, 13, 3, 2, '2026-04-02 09:00:00'),
(12, 'proy-012', 'Borrador Meta Analytics', 3000000, '2026-04-10', 'Plataforma de analisis de datos para Meta', 'Analizar patrones de comportamiento de usuarios', 'Dashboard con metricas en tiempo real', NULL, NULL, 1, '15.222.333-K', '18.222.333-4', '76.843.468-1', 13, 13, 1, 2, NULL);

--
-- Disparadores `postulacion`
--
DELIMITER $$
CREATE TRIGGER `trg_fecha_envio` BEFORE UPDATE ON `postulacion` FOR EACH ROW BEGIN
  -- Si cambia a estado "Enviada" (EP_Id=2) y aun no tiene fecha de envio
  IF NEW.P_Estado_ID = 2 AND OLD.P_Estado_ID = 1 AND OLD.P_Fecha_Envio IS NULL THEN
    SET NEW.P_Fecha_Envio = NOW();
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `region`
--

CREATE TABLE `region` (
  `R_ID` int(11) NOT NULL,
  `R_Nombre` varchar(50) NOT NULL,
  `R_Enumeracion` varchar(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `region`
--

INSERT INTO `region` (`R_ID`, `R_Nombre`, `R_Enumeracion`) VALUES
(1, 'Region Tarapaca', 'I'),
(2, 'Region Antofagasta', 'II'),
(3, 'Region Atacama', 'III'),
(4, 'Region Coquimbo', 'IV'),
(5, 'Region Valparaiso', 'V'),
(6, 'Region O\'Higgins', 'VI'),
(7, 'Region Maule', 'VII'),
(8, 'Region BioBio', 'VIII'),
(9, 'Region Araucania', 'IX'),
(10, 'Region Los Lagos', 'X'),
(11, 'Region Aysen', 'XI'),
(12, 'Region Magallanes', 'XII'),
(13, 'Region Metropolitana', 'RM'),
(14, 'Region Los Rios', 'XIV'),
(15, 'Region Arica y Parinacota', 'XV'),
(16, 'Region Nuble', 'XVI');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tamañoempresa`
--

CREATE TABLE `tamañoempresa` (
  `T_Id` int(11) NOT NULL,
  `T_Tamaño` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tamañoempresa`
--

INSERT INTO `tamañoempresa` (`T_Id`, `T_Tamaño`) VALUES
(1, 'Microempresa'),
(2, 'Mediana'),
(3, 'Grande');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipoiniciativa`
--

CREATE TABLE `tipoiniciativa` (
  `TI_Id` int(11) NOT NULL,
  `TI_Tipo` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipoiniciativa`
--

INSERT INTO `tipoiniciativa` (`TI_Id`, `TI_Tipo`) VALUES
(1, 'Nueva'),
(2, 'Existente');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipopersona`
--

CREATE TABLE `tipopersona` (
  `TP_Id` int(11) NOT NULL,
  `TP_Tipo` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipopersona`
--

INSERT INTO `tipopersona` (`TP_Id`, `TP_Tipo`) VALUES
(1, 'Profesor'),
(2, 'Estudiante');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `U_Id` int(11) NOT NULL,
  `U_Rut` varchar(12) NOT NULL,
  `U_Nombre` varchar(100) NOT NULL,
  `U_Email` varchar(100) NOT NULL,
  `U_Password` varchar(255) NOT NULL,
  `U_Rol` enum('postulante','coordinador','administrador') NOT NULL,
  `U_Activo` tinyint(1) NOT NULL DEFAULT 1,
  `U_Fecha_Creacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`U_Id`, `U_Rut`, `U_Nombre`, `U_Email`, `U_Password`, `U_Rol`, `U_Activo`, `U_Fecha_Creacion`) VALUES
(1, '14.555.666-7', 'Ricardo Salas', 'ricardo.salas@usm.cl', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'postulante', 1, '2026-05-13 18:59:08'),
(2, '15.222.333-K', 'Mauricio Figueroa', 'mauricio.figueroa@usm.cl', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'postulante', 1, '2026-05-13 18:59:08'),
(3, '18.222.333-4', 'Claudio Lobos', 'claudio.lobos@usm.cl', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'postulante', 1, '2026-05-13 18:59:08'),
(4, '11.999.888-0', 'Jose Marti', 'jose.marti@usm.cl', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'postulante', 1, '2026-05-13 18:59:08'),
(5, '10.888.777-6', 'Mauricio Solar', 'mauricio.solar@usm.cl', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'postulante', 1, '2026-05-13 18:59:08'),
(6, '08.555.444-2', 'Hernan Astudillo', 'hernan.astudillo@usm.cl', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'coordinador', 1, '2026-05-13 18:59:08'),
(7, '12.555.666-K', 'Andrea Vasquez', 'andrea.vasquez@usm.cl', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'coordinador', 1, '2026-05-13 18:59:08'),
(8, '14.777.888-9', 'Claudio Torres', 'claudio.torres@usm.cl', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'coordinador', 1, '2026-05-13 18:59:08'),
(9, '99.999.999-9', 'Admin CT-USM', 'admin@ctusm.cl', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'administrador', 1, '2026-05-13 18:59:08');

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_postulaciones`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vista_postulaciones` (
`P_Id` int(11)
,`P_Codigo_interno` varchar(50)
,`P_Nombre` varchar(100)
,`P_Presupuesto` int(11)
,`P_Fecha` date
,`P_Fecha_Envio` datetime
,`Estado` varchar(50)
,`P_Estado_ID` int(11)
,`TipoIniciativa` varchar(50)
,`Campus` varchar(100)
,`RegionRealizar` varchar(50)
,`RegionImpacto` varchar(50)
,`Empresa` varchar(100)
,`EmpresaRut` varchar(12)
,`TamanoEmpresa` varchar(50)
,`ConvenioUSM` tinyint(1)
,`Responsable1` varchar(100)
,`P_Responsable1_Rut` varchar(12)
,`Responsable2` varchar(100)
,`P_Responsable2_Rut` varchar(12)
);

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_postulaciones`
--
DROP TABLE IF EXISTS `vista_postulaciones`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_postulaciones`  AS SELECT `p`.`P_Id` AS `P_Id`, `p`.`P_Codigo_interno` AS `P_Codigo_interno`, `p`.`P_Nombre` AS `P_Nombre`, `p`.`P_Presupuesto` AS `P_Presupuesto`, `p`.`P_Fecha` AS `P_Fecha`, `p`.`P_Fecha_Envio` AS `P_Fecha_Envio`, `ep`.`EP_Nombre` AS `Estado`, `p`.`P_Estado_ID` AS `P_Estado_ID`, `ti`.`TI_Tipo` AS `TipoIniciativa`, `c`.`C_Nombre` AS `Campus`, `r1`.`R_Nombre` AS `RegionRealizar`, `r2`.`R_Nombre` AS `RegionImpacto`, `e`.`E_Nombre` AS `Empresa`, `e`.`E_Rut` AS `EmpresaRut`, `te`.`T_Tamaño` AS `TamanoEmpresa`, `e`.`E_ConvenioUSM` AS `ConvenioUSM`, concat(`i1`.`I_Nombre`) AS `Responsable1`, `p`.`P_Responsable1_Rut` AS `P_Responsable1_Rut`, concat(`i2`.`I_Nombre`) AS `Responsable2`, `p`.`P_Responsable2_Rut` AS `P_Responsable2_Rut` FROM (((((((((`postulacion` `p` join `estadopostulacion` `ep` on(`p`.`P_Estado_ID` = `ep`.`EP_Id`)) join `tipoiniciativa` `ti` on(`p`.`P_Iniciativa_ID` = `ti`.`TI_Id`)) join `campus` `c` on(`p`.`P_ID_Campus` = `c`.`C_Id`)) join `region` `r1` on(`p`.`P_Region_Realizar` = `r1`.`R_ID`)) join `region` `r2` on(`p`.`P_Region_Impacto` = `r2`.`R_ID`)) join `empresaexterna` `e` on(`p`.`P_Empresa_Rut` = `e`.`E_Rut`)) join `tamañoempresa` `te` on(`e`.`E_Tamaño` = `te`.`T_Id`)) join `integrantes` `i1` on(`p`.`P_Responsable1_Rut` = `i1`.`I_Rut`)) join `integrantes` `i2` on(`p`.`P_Responsable2_Rut` = `i2`.`I_Rut`)) ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `asignacion_evaluador`
--
ALTER TABLE `asignacion_evaluador`
  ADD PRIMARY KEY (`AE_Id`),
  ADD KEY `ae_postulacion` (`AE_Postulacion_ID`);

--
-- Indices de la tabla `campus`
--
ALTER TABLE `campus`
  ADD PRIMARY KEY (`C_Id`),
  ADD KEY `campus_ibfk_1` (`C_ID_Region`);

--
-- Indices de la tabla `empresaexterna`
--
ALTER TABLE `empresaexterna`
  ADD PRIMARY KEY (`E_Rut`),
  ADD KEY `empresaexterna_ibfk_1` (`E_Tamaño`);

--
-- Indices de la tabla `equipotrabajo`
--
ALTER TABLE `equipotrabajo`
  ADD PRIMARY KEY (`EQ_Rut_Integrante`,`EQ_Id_Postulacion`),
  ADD KEY `equipotrabajo_ibfk_2` (`EQ_Id_Postulacion`);

--
-- Indices de la tabla `estadopostulacion`
--
ALTER TABLE `estadopostulacion`
  ADD PRIMARY KEY (`EP_Id`);

--
-- Indices de la tabla `etapa`
--
ALTER TABLE `etapa`
  ADD PRIMARY KEY (`ET_Id`),
  ADD KEY `etapa_ibfk_1` (`ET_Postulacion_ID`);

--
-- Indices de la tabla `evaluacion`
--
ALTER TABLE `evaluacion`
  ADD PRIMARY KEY (`EV_Id`),
  ADD KEY `ev_postulacion` (`EV_Postulacion_ID`),
  ADD KEY `ev_evaluador` (`EV_Evaluador_Rut`),
  ADD KEY `ev_estado` (`EV_Estado_Nuevo`);

--
-- Indices de la tabla `integrantes`
--
ALTER TABLE `integrantes`
  ADD PRIMARY KEY (`I_Rut`),
  ADD KEY `integrantes_ibfk_1` (`I_Campus`),
  ADD KEY `integrantes_ibfk_2` (`I_Tipo_ID`);

--
-- Indices de la tabla `postulacion`
--
ALTER TABLE `postulacion`
  ADD PRIMARY KEY (`P_Id`),
  ADD UNIQUE KEY `P_Codigo_interno` (`P_Codigo_interno`),
  ADD KEY `postulacion_ibfk_1` (`P_Responsable1_Rut`),
  ADD KEY `postulacion_ibfk_2` (`P_Responsable2_Rut`),
  ADD KEY `postulacion_ibfk_3` (`P_Empresa_Rut`),
  ADD KEY `postulacion_ibfk_4` (`P_Region_Realizar`),
  ADD KEY `postulacion_ibfk_5` (`P_Region_Impacto`),
  ADD KEY `postulacion_ibfk_6` (`P_Estado_ID`),
  ADD KEY `postulacion_ibfk_7` (`P_Iniciativa_ID`),
  ADD KEY `postulacion_ibfk_8` (`P_ID_Campus`);

--
-- Indices de la tabla `region`
--
ALTER TABLE `region`
  ADD PRIMARY KEY (`R_ID`);

--
-- Indices de la tabla `tamañoempresa`
--
ALTER TABLE `tamañoempresa`
  ADD PRIMARY KEY (`T_Id`);

--
-- Indices de la tabla `tipoiniciativa`
--
ALTER TABLE `tipoiniciativa`
  ADD PRIMARY KEY (`TI_Id`);

--
-- Indices de la tabla `tipopersona`
--
ALTER TABLE `tipopersona`
  ADD PRIMARY KEY (`TP_Id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`U_Id`),
  ADD UNIQUE KEY `U_Rut` (`U_Rut`),
  ADD UNIQUE KEY `U_Email` (`U_Email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `asignacion_evaluador`
--
ALTER TABLE `asignacion_evaluador`
  MODIFY `AE_Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `campus`
--
ALTER TABLE `campus`
  MODIFY `C_Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `estadopostulacion`
--
ALTER TABLE `estadopostulacion`
  MODIFY `EP_Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `etapa`
--
ALTER TABLE `etapa`
  MODIFY `ET_Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT de la tabla `evaluacion`
--
ALTER TABLE `evaluacion`
  MODIFY `EV_Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `postulacion`
--
ALTER TABLE `postulacion`
  MODIFY `P_Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `region`
--
ALTER TABLE `region`
  MODIFY `R_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `tamañoempresa`
--
ALTER TABLE `tamañoempresa`
  MODIFY `T_Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `tipoiniciativa`
--
ALTER TABLE `tipoiniciativa`
  MODIFY `TI_Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `tipopersona`
--
ALTER TABLE `tipopersona`
  MODIFY `TP_Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `U_Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `asignacion_evaluador`
--
ALTER TABLE `asignacion_evaluador`
  ADD CONSTRAINT `ae_postulacion` FOREIGN KEY (`AE_Postulacion_ID`) REFERENCES `postulacion` (`P_Id`);

--
-- Filtros para la tabla `campus`
--
ALTER TABLE `campus`
  ADD CONSTRAINT `campus_ibfk_1` FOREIGN KEY (`C_ID_Region`) REFERENCES `region` (`R_ID`);

--
-- Filtros para la tabla `empresaexterna`
--
ALTER TABLE `empresaexterna`
  ADD CONSTRAINT `empresaexterna_ibfk_1` FOREIGN KEY (`E_Tamaño`) REFERENCES `tamañoempresa` (`T_Id`);

--
-- Filtros para la tabla `equipotrabajo`
--
ALTER TABLE `equipotrabajo`
  ADD CONSTRAINT `equipotrabajo_ibfk_1` FOREIGN KEY (`EQ_Rut_Integrante`) REFERENCES `integrantes` (`I_Rut`),
  ADD CONSTRAINT `equipotrabajo_ibfk_2` FOREIGN KEY (`EQ_Id_Postulacion`) REFERENCES `postulacion` (`P_Id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `etapa`
--
ALTER TABLE `etapa`
  ADD CONSTRAINT `etapa_ibfk_1` FOREIGN KEY (`ET_Postulacion_ID`) REFERENCES `postulacion` (`P_Id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `evaluacion`
--
ALTER TABLE `evaluacion`
  ADD CONSTRAINT `ev_estado` FOREIGN KEY (`EV_Estado_Nuevo`) REFERENCES `estadopostulacion` (`EP_Id`),
  ADD CONSTRAINT `ev_postulacion` FOREIGN KEY (`EV_Postulacion_ID`) REFERENCES `postulacion` (`P_Id`);

--
-- Filtros para la tabla `integrantes`
--
ALTER TABLE `integrantes`
  ADD CONSTRAINT `integrantes_ibfk_1` FOREIGN KEY (`I_Campus`) REFERENCES `campus` (`C_Id`),
  ADD CONSTRAINT `integrantes_ibfk_2` FOREIGN KEY (`I_Tipo_ID`) REFERENCES `tipopersona` (`TP_Id`);

--
-- Filtros para la tabla `postulacion`
--
ALTER TABLE `postulacion`
  ADD CONSTRAINT `postulacion_ibfk_1` FOREIGN KEY (`P_Responsable1_Rut`) REFERENCES `integrantes` (`I_Rut`),
  ADD CONSTRAINT `postulacion_ibfk_2` FOREIGN KEY (`P_Responsable2_Rut`) REFERENCES `integrantes` (`I_Rut`),
  ADD CONSTRAINT `postulacion_ibfk_3` FOREIGN KEY (`P_Empresa_Rut`) REFERENCES `empresaexterna` (`E_Rut`),
  ADD CONSTRAINT `postulacion_ibfk_4` FOREIGN KEY (`P_Region_Realizar`) REFERENCES `region` (`R_ID`),
  ADD CONSTRAINT `postulacion_ibfk_5` FOREIGN KEY (`P_Region_Impacto`) REFERENCES `region` (`R_ID`),
  ADD CONSTRAINT `postulacion_ibfk_6` FOREIGN KEY (`P_Estado_ID`) REFERENCES `estadopostulacion` (`EP_Id`),
  ADD CONSTRAINT `postulacion_ibfk_7` FOREIGN KEY (`P_Iniciativa_ID`) REFERENCES `tipoiniciativa` (`TI_Id`),
  ADD CONSTRAINT `postulacion_ibfk_8` FOREIGN KEY (`P_ID_Campus`) REFERENCES `campus` (`C_Id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
