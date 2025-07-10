-- MySQL dump 10.13  Distrib 8.0.38, for Win64 (x86_64)
--
-- Host: localhost    Database: datasena_db
-- ------------------------------------------------------
-- Server version	9.0.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `admin`
--

DROP TABLE IF EXISTS `admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tipo_documento` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `numero_documento` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombres` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellidos` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nickname` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `correo_electronico` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contrasena` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rol_id` int NOT NULL,
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `estado_habilitacion` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Activo',
  PRIMARY KEY (`id`),
  UNIQUE KEY `numero_documento` (`numero_documento`),
  UNIQUE KEY `nickname` (`nickname`),
  UNIQUE KEY `correo_electronico` (`correo_electronico`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin`
--

LOCK TABLES `admin` WRITE;
/*!40000 ALTER TABLE `admin` DISABLE KEYS */;
INSERT INTO `admin` VALUES (1,'CC','1010101010','lizeth','salazar','lizethsalazar','admin@gmail.com','$2y$10$R639twqURz9NcURXXt7BleWg6FCPOJ7WokeTSVXzIFSNX.MmprTpC',1,'2025-07-03 06:20:51','Activo'),(2,'TI','1040708050','inge','niero','admin 1','awd@gmail.com','$2y$10$QCTFFMEBYZkqyBI4yBNxmO0qMMVOSVqRtCd37lzIFlAczcvpv51zK',1,'2025-07-03 15:36:57','Activo'),(3,'TI','1010101111','camilo','salazar','admin2','sdfsdfs@gmail.com','$2y$10$Vs6knEYri1GCL0cYxQQ74OroSUml6stkxMqWVpttx75hVHGrssS9q',1,'2025-07-09 03:56:48','Activo');
/*!40000 ALTER TABLE `admin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `diagnostico_empresarial`
--

DROP TABLE IF EXISTS `diagnostico_empresarial`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `diagnostico_empresarial` (
  `id` int NOT NULL AUTO_INCREMENT,
  `empresa` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nit` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sector` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tamano` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ubicacion` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `empleados` int NOT NULL,
  `contrataciones` int NOT NULL,
  `contrato_frecuente` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tiene_proceso` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `perfiles_definidos` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `publicacion` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `aprendices` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `programa_apoyo` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `perfiles_necesarios` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `infraestructura` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apoyo_seleccion` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `beneficios` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `diagnostico_empresarial`
--

LOCK TABLES `diagnostico_empresarial` WRITE;
/*!40000 ALTER TABLE `diagnostico_empresarial` DISABLE KEYS */;
INSERT INTO `diagnostico_empresarial` VALUES (1,'assa','123098123','Agroindustria','Microempresa (1-10)','ase',900,3,'Aprendices SENA','Sí','Sí','Redes sociales','Sí','Sí','base de datos','Sí','Sí','Sí','2025-07-03 17:35:15'),(2,'assa','123098123','Agroindustria','Microempresa (1-10)','ase',900,3,'Aprendices SENA','Sí','Sí','Redes sociales','Sí','Sí','base de datos','Sí','Sí','Sí','2025-07-03 17:38:42'),(3,'assa','123098123','Agroindustria','Microempresa (1-10)','ase',900,3,'Aprendices SENA','Sí','Sí','Redes sociales','Sí','Sí','base de datos','Sí','Sí','Sí','2025-07-03 17:40:04'),(4,'assa','123098123','Agroindustria','Microempresa (1-10)','ase',900,3,'Aprendices SENA','Sí','Sí','Redes sociales','Sí','Sí','base de datos','Sí','Sí','Sí','2025-07-03 17:41:17'),(5,'assa','123456','Tecnología','Microempresa (1-10)','ase',123,123,'Fijo','Sí','Sí','Redes sociales','Sí','Sí','base de datos','Sí','Sí','Sí','2025-07-03 17:41:45'),(6,'assa','123456','Tecnología','Microempresa (1-10)','ase',123,123,'Fijo','Sí','Sí','Redes sociales','Sí','Sí','base de datos','Sí','Sí','Sí','2025-07-03 17:44:41'),(7,'rer44','2342342','Servicios','Microempresa (1-10)','qsed324',234,234,'Fijo','Sí','Sí','Redes sociales','Sí','Sí','Textiles, Logística','Sí','Sí','Sí','2025-07-06 04:32:14'),(8,'rer44','2342342','Servicios','Microempresa (1-10)','qsed324',234,234,'Fijo','Sí','Sí','Redes sociales','Sí','Sí','Textiles, Logística','Sí','Sí','Sí','2025-07-06 04:32:33'),(9,'assa','123456','Agroindustria','Microempresa (1-10)','qsed324',234,234,'Fijo','Sí','Sí','Redes sociales','Sí','Sí','Soporte técnico','Sí','Sí','Sí','2025-07-06 04:32:56'),(10,'assa','123456','Agroindustria','Microempresa (1-10)','qsed324',234,234,'Fijo','Sí','Sí','Redes sociales','Sí','Sí','Soporte técnico','Sí','Sí','Sí','2025-07-06 04:37:10'),(11,'123','123','Agroindustria','Microempresa (1-10)','123',123,123,'Fijo','Sí','Sí','Redes sociales','Sí','Sí','Programación','Sí','Sí','Sí','2025-07-06 04:37:43'),(12,'123','123','Agroindustria','Microempresa (1-10)','123',123,123,'Fijo','Sí','Sí','Redes sociales','Sí','Sí','Mecánica','Sí','Sí','Sí','2025-07-06 04:38:37'),(13,'123','123','Tecnología','Microempresa (1-10)','123',123,123,'Fijo','Sí','Sí','Redes sociales','Sí','Sí','Textiles','Sí','Sí','Sí','2025-07-09 04:17:10'),(14,'123','123','Agroindustria','Microempresa (1-10)','123',123,123,'Fijo','Sí','Sí','Redes sociales','Sí','Sí','Diseño gráfico','Sí','Sí','Sí','2025-07-09 04:17:27'),(15,'123','123','Agroindustria','Microempresa (1-10)','123',123,123,'Fijo','Sí','Sí','Redes sociales','Sí','Sí','Mecánica','Sí','Sí','Sí','2025-07-09 04:26:57'),(16,'123','123','Agroindustria','Microempresa (1-10)','123',123,123,'Fijo','Sí','Sí','Redes sociales','Sí','Sí','Programación, Textiles','Sí','Sí','Sí','2025-07-09 04:27:16'),(17,'123','123','Agroindustria','Microempresa (1-10)','123',123,123,'Fijo','Sí','Sí','Redes sociales','Sí','Sí','Programación, Textiles','Sí','Sí','Sí','2025-07-09 04:28:37'),(18,'123','123','Agroindustria','Microempresa (1-10)','123',123,123,'Fijo','Sí','Sí','Redes sociales','Sí','Sí','Programación, Textiles','Sí','Sí','Sí','2025-07-09 04:29:48');
/*!40000 ALTER TABLE `diagnostico_empresarial` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `empresas`
--

DROP TABLE IF EXISTS `empresas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `empresas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tipo_documento` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `numero_identidad` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nickname` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefono` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `correo` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `direccion` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `actividad_economica` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT '1',
  `contrasena` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `estado_habilitacion` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Activo',
  PRIMARY KEY (`id`),
  UNIQUE KEY `numero_identidad` (`numero_identidad`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `empresas`
--

LOCK TABLES `empresas` WRITE;
/*!40000 ALTER TABLE `empresas` DISABLE KEYS */;
INSERT INTO `empresas` VALUES (1,'RUT','1010101999','las hijas de eva','1010101321','yo99@gmail.com','carrera123','cositas123',1,'$2y$10$qXTxRA0YbxRhLDTRmg7Ij./D3MgH.fMr0PJO1wQZmctI0W/ymCG3a','2025-07-03 05:34:20','Activo'),(2,'NIT','1020304050','DIAN','1020304050','pele@gmail.com','en todas partes','lavado de dinero',1,'$2y$10$ozyvj5xEhQSBbhIWQnuJ1OkY8wES.NbFV2AAXEv3G8AfSVh5ZrnDW','2025-07-03 15:28:02','Activo'),(3,'NIT','9876549871','la floristeria','3216549877','juan@gmail.com','noseewq','lavado de dinero',1,'$2y$10$4v/aR6sSlEULPQlTonjXkuRmpQQdaUCaYuwL1o/2VD1uVVANJh5qu','2025-07-09 03:50:36','Inactivo');
/*!40000 ALTER TABLE `empresas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inicio_super_admin`
--

DROP TABLE IF EXISTS `inicio_super_admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inicio_super_admin` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contrasena` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inicio_super_admin`
--

LOCK TABLES `inicio_super_admin` WRITE;
/*!40000 ALTER TABLE `inicio_super_admin` DISABLE KEYS */;
INSERT INTO `inicio_super_admin` VALUES (1,'superadmin','123');
/*!40000 ALTER TABLE `inicio_super_admin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `programas`
--

DROP TABLE IF EXISTS `programas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `programas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre_programa` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_programa` enum('Tecnico','Tecnologo','Operario') COLLATE utf8mb4_unicode_ci NOT NULL,
  `numero_ficha` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `duracion_programa` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT '2 años',
  `activacion` enum('activo','inactivo') COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `numero_ficha` (`numero_ficha`)
) ENGINE=InnoDB AUTO_INCREMENT=131 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `programas`
--

LOCK TABLES `programas` WRITE;
/*!40000 ALTER TABLE `programas` DISABLE KEYS */;
INSERT INTO `programas` VALUES (81,'Análisis y Desarrollo de Software','Tecnologo','202501','2 años','activo','2025-07-09 04:26:08'),(82,'Programación de Software','Tecnico','202502','1 año','activo','2025-07-09 04:26:08'),(83,'Diseño e Implementación de Bases de Datos','Tecnico','202503','1 año','activo','2025-07-09 04:26:08'),(84,'Desarrollo de Aplicaciones Móviles','Tecnico','202504','1 año','activo','2025-07-09 04:26:08'),(85,'Desarrollo Web Front-End','Tecnico','202505','1 año','activo','2025-07-09 04:26:08'),(86,'Producción de Confecciones Industriales','Tecnico','202506','1 año','activo','2025-07-09 04:26:08'),(87,'Diseño para la Industria de la Moda','Tecnologo','202507','2 años','activo','2025-07-09 04:26:08'),(88,'Patronaje Industrial de Prendas de Vestir','Tecnico','202508','1 año','activo','2025-07-09 04:26:08'),(89,'Costura Industrial','Operario','202509','6 meses','activo','2025-07-09 04:26:08'),(90,'Bordado y Acabado Textil','Operario','202510','6 meses','activo','2025-07-09 04:26:08'),(91,'Mantenimiento de Motores Diésel','Tecnico','202511','1 año','activo','2025-07-09 04:26:08'),(92,'Mecánica Automotriz','Tecnico','202512','1 año','activo','2025-07-09 04:26:08'),(93,'Mecánica de Motos','Tecnico','202513','1 año','activo','2025-07-09 04:26:08'),(94,'Mecatrónica Industrial','Tecnologo','202514','2 años','activo','2025-07-09 04:26:08'),(95,'Soldadura en Platina','Operario','202515','6 meses','activo','2025-07-09 04:26:08'),(96,'Logística Empresarial','Tecnologo','202516','2 años','activo','2025-07-09 04:26:08'),(97,'Gestión de Almacén','Tecnico','202517','1 año','activo','2025-07-09 04:26:08'),(98,'Gestión de Inventarios','Tecnico','202518','1 año','activo','2025-07-09 04:26:08'),(99,'Alistamiento de Carga para Despacho','Operario','202519','6 meses','activo','2025-07-09 04:26:08'),(100,'Transporte y Distribución de Mercancías','Operario','202520','6 meses','activo','2025-07-09 04:26:08'),(101,'Contabilidad y Finanzas','Tecnologo','202521','2 años','activo','2025-07-09 04:26:08'),(102,'Contabilidad General','Tecnico','202522','1 año','activo','2025-07-09 04:26:08'),(103,'Registro de Transacciones Contables','Operario','202523','6 meses','activo','2025-07-09 04:26:08'),(104,'Apoyo Administrativo y Financiero','Tecnico','202524','1 año','activo','2025-07-09 04:26:08'),(105,'Gestión Financiera','Tecnologo','202525','2 años','activo','2025-07-09 04:26:08'),(106,'Instalaciones Eléctricas Residenciales','Tecnico','202526','1 año','activo','2025-07-09 04:26:08'),(107,'Electromecánica Industrial','Tecnologo','202527','2 años','activo','2025-07-09 04:26:08'),(108,'Mantenimiento de Sistemas Eléctricos','Tecnico','202528','1 año','activo','2025-07-09 04:26:08'),(109,'Automatización de Procesos Eléctricos','Tecnologo','202529','2 años','activo','2025-07-09 04:26:08'),(110,'Electricidad Básica','Operario','202530','6 meses','activo','2025-07-09 04:26:08'),(111,'Diseño Gráfico Digital','Tecnologo','202531','2 años','activo','2025-07-09 04:26:08'),(112,'Diseño de Piezas Publicitarias','Tecnico','202532','1 año','activo','2025-07-09 04:26:08'),(113,'Edición de Imágenes Digitales','Operario','202533','6 meses','activo','2025-07-09 04:26:08'),(114,'Diseño Editorial','Tecnico','202534','1 año','activo','2025-07-09 04:26:08'),(115,'Producción Multimedia','Tecnologo','202535','2 años','activo','2025-07-09 04:26:08'),(116,'Soporte Técnico de Sistemas','Tecnico','202536','1 año','activo','2025-07-09 04:26:08'),(117,'Mantenimiento de Equipos de Cómputo','Tecnico','202537','1 año','activo','2025-07-09 04:26:08'),(118,'Redes de Datos','Tecnico','202538','1 año','activo','2025-07-09 04:26:08'),(119,'Infraestructura de TI','Tecnologo','202539','2 años','activo','2025-07-09 04:26:08'),(120,'Instalación de Software y Hardware','Operario','202540','6 meses','activo','2025-07-09 04:26:08'),(121,'Seguridad y Salud en el Trabajo','Tecnologo','202541','2 años','activo','2025-07-09 04:26:08'),(122,'Prevención de Riesgos Laborales','Tecnico','202542','1 año','activo','2025-07-09 04:26:08'),(123,'Brigadista en SST','Operario','202543','6 meses','activo','2025-07-09 04:26:08'),(124,'Gestión de Seguridad Industrial','Tecnologo','202544','2 años','activo','2025-07-09 04:26:08'),(125,'Normas SST para Empresas','Tecnico','202545','1 año','activo','2025-07-09 04:26:08'),(126,'Gestión Administrativa','Tecnologo','202546','2 años','activo','2025-07-09 04:26:08'),(127,'Asistencia Administrativa','Tecnico','202547','1 año','activo','2025-07-09 04:26:08'),(128,'Secretariado Ejecutivo','Tecnico','202548','1 año','activo','2025-07-09 04:26:08'),(129,'Gestión Documental','Tecnico','202549','1 año','activo','2025-07-09 04:26:08'),(130,'Administración de Recursos Humanos','Tecnologo','202550','2 años','activo','2025-07-09 04:26:08');
/*!40000 ALTER TABLE `programas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reportes`
--

DROP TABLE IF EXISTS `reportes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reportes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tipo_reporte` enum('empresa','administrador','programa') COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_referenciado` int NOT NULL,
  `observacion` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_reporte` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reportes`
--

LOCK TABLES `reportes` WRITE;
/*!40000 ALTER TABLE `reportes` DISABLE KEYS */;
INSERT INTO `reportes` VALUES (1,'empresa',1,'dsdsdsdsdsd','2025-07-06 03:48:46'),(2,'programa',2,'wwww','2025-07-06 04:00:11'),(3,'empresa',2,'sdsdsd','2025-07-06 04:00:58'),(4,'programa',2,'iyfgiyjgb','2025-07-06 04:04:27'),(5,'empresa',3,'incumplimiento ','2025-07-09 04:13:43');
/*!40000 ALTER TABLE `reportes` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-07-09 11:06:28
