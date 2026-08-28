-- MySQL dump 10.13  Distrib 8.0.41, for Win64 (x86_64)
--
-- Host: localhost    Database: soft_kayrom
-- ------------------------------------------------------
-- Server version	8.0.41

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
-- Table structure for table `auditorias`
--

DROP TABLE IF EXISTS `auditorias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `auditorias` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int DEFAULT NULL,
  `accion` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `modulo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `registro_id` int DEFAULT NULL,
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_auditoria_usuario` (`usuario_id`),
  CONSTRAINT `fk_auditoria_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auditorias`
--

LOCK TABLES `auditorias` WRITE;
/*!40000 ALTER TABLE `auditorias` DISABLE KEYS */;
/*!40000 ALTER TABLE `auditorias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `carrito`
--

DROP TABLE IF EXISTS `carrito`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `carrito` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `repuesto_id` int NOT NULL,
  `cantidad` int NOT NULL DEFAULT '1',
  `fecha_agregado` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `repuesto_id` (`repuesto_id`),
  CONSTRAINT `carrito_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id`),
  CONSTRAINT `carrito_ibfk_2` FOREIGN KEY (`repuesto_id`) REFERENCES `repuestos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `carrito`
--

LOCK TABLES `carrito` WRITE;
/*!40000 ALTER TABLE `carrito` DISABLE KEYS */;
INSERT INTO `carrito` VALUES (1,4,4,5,'2026-06-29 15:16:35'),(2,4,3,3,'2026-06-29 15:16:51'),(5,11,5,1,'2026-06-29 15:39:19'),(8,4,5,4,'2026-06-30 16:40:11');
/*!40000 ALTER TABLE `carrito` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detalle_pedido`
--

DROP TABLE IF EXISTS `detalle_pedido`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `detalle_pedido` (
  `id` int NOT NULL AUTO_INCREMENT,
  `detalle_pedido` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `productos_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `productos_id` (`productos_id`),
  CONSTRAINT `detalle_pedido_ibfk_1` FOREIGN KEY (`productos_id`) REFERENCES `productos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_pedido`
--

LOCK TABLES `detalle_pedido` WRITE;
/*!40000 ALTER TABLE `detalle_pedido` DISABLE KEYS */;
INSERT INTO `detalle_pedido` VALUES (1,'Pedido urgente por falta de stock',7),(2,'Reposición mensual programada',1),(3,'Reposición por consumo alto en taller',3),(4,'Pedido preventivo antes de temporada',8),(5,'Reposición stock mínimo',12),(6,'Caja de herramientas',1),(7,'No recibirle este pedido',1);
/*!40000 ALTER TABLE `detalle_pedido` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `empleado`
--

DROP TABLE IF EXISTS `empleado`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `empleado` (
  `id` int NOT NULL AUTO_INCREMENT,
  `rango_trabajo` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `especialidad` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `persona_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `persona_id` (`persona_id`),
  CONSTRAINT `empleado_ibfk_1` FOREIGN KEY (`persona_id`) REFERENCES `persona` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `empleado`
--

LOCK TABLES `empleado` WRITE;
/*!40000 ALTER TABLE `empleado` DISABLE KEYS */;
INSERT INTO `empleado` VALUES (1,'Jefe de taller','Motor y transmisión',3),(2,'Mecánico','Suspensión y frenos',4),(3,'Mecánico','Electricidad',5),(4,'Administrativo','Gestión de stock',6),(5,'','',13),(6,'','',18),(7,'','',19);
/*!40000 ALTER TABLE `empleado` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `modelo_vehiculo`
--

DROP TABLE IF EXISTS `modelo_vehiculo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `modelo_vehiculo` (
  `id` int NOT NULL AUTO_INCREMENT,
  `modelo_vehiculo` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `anio_vehiculo` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `modelo_vehiculo`
--

LOCK TABLES `modelo_vehiculo` WRITE;
/*!40000 ALTER TABLE `modelo_vehiculo` DISABLE KEYS */;
INSERT INTO `modelo_vehiculo` VALUES (1,'Toyota Hilux',2021),(2,'Ford Ranger',2022),(3,'Volkswagen Amarok',2020),(4,'Renault Kangoo',2019),(5,'Honda CB 500',2023),(6,'Chevrolet S10',2021),(7,'Fiat Ducato',2020),(8,'Toyota Corolla',2022);
/*!40000 ALTER TABLE `modelo_vehiculo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `movimientos`
--

DROP TABLE IF EXISTS `movimientos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `movimientos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tipo` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cantidad` int DEFAULT '0',
  `fecha` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `movimientos`
--

LOCK TABLES `movimientos` WRITE;
/*!40000 ALTER TABLE `movimientos` DISABLE KEYS */;
INSERT INTO `movimientos` VALUES (1,'Entrada','Alta de repuesto: Frenos',2,'2026-04-28 19:24:38'),(2,'Entrada','Alta de repuesto: Frenos',2,'2026-04-28 20:09:42'),(3,'Entrada','Alta de repuesto: Suspensión  Hilux',2,'2026-05-22 18:33:42'),(4,'Entrada','Alta de repuesto: Frenos blablabla',0,'2026-06-09 14:38:43'),(5,'Entrada','Alta de repuesto: Aceite Xd',19,'2026-06-29 15:19:01');
/*!40000 ALTER TABLE `movimientos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pedidos`
--

DROP TABLE IF EXISTS `pedidos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pedidos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `fecha_pedidos` datetime DEFAULT NULL,
  `estado_pedido` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `responsable_pedido` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numero_unico` int DEFAULT NULL,
  `cantidad` int DEFAULT NULL,
  `detalle_pedido_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `detalle_pedido_id` (`detalle_pedido_id`),
  CONSTRAINT `pedidos_ibfk_1` FOREIGN KEY (`detalle_pedido_id`) REFERENCES `detalle_pedido` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pedidos`
--

LOCK TABLES `pedidos` WRITE;
/*!40000 ALTER TABLE `pedidos` DISABLE KEYS */;
INSERT INTO `pedidos` VALUES (2,'2024-04-03 11:30:00','Aprobado','Kayser',1002,20,2),(3,'2024-04-05 14:00:00','Entregado','Medina',1003,30,3),(4,'2024-04-08 08:45:00','Pendiente','Romero',1004,4,4),(8,'2026-06-09 14:40:07','En proceso','Gonzalo Gauna',4426,1,7);
/*!40000 ALTER TABLE `pedidos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permiso_usuario`
--

DROP TABLE IF EXISTS `permiso_usuario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permiso_usuario` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `modulo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_usuario_modulo` (`usuario_id`,`modulo`),
  CONSTRAINT `permiso_usuario_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permiso_usuario`
--

LOCK TABLES `permiso_usuario` WRITE;
/*!40000 ALTER TABLE `permiso_usuario` DISABLE KEYS */;
INSERT INTO `permiso_usuario` VALUES (15,1,'productos'),(8,1,'repuestos'),(1,1,'vehiculos'),(44,2,'repuestos'),(43,2,'vehiculos'),(34,3,'repuestos'),(33,3,'vehiculos'),(18,6,'productos'),(11,6,'repuestos'),(4,6,'vehiculos'),(19,8,'productos'),(12,8,'repuestos'),(5,8,'vehiculos'),(46,10,'repuestos'),(45,10,'vehiculos'),(32,13,'pedidos'),(31,13,'repuestos'),(30,13,'vehiculos'),(53,14,'pedidos'),(54,14,'proveedores'),(52,14,'repuestos'),(51,14,'vehiculos'),(56,16,'pedidos'),(57,16,'proveedores'),(55,16,'vehiculos'),(59,18,'repuestos'),(58,18,'vehiculos');
/*!40000 ALTER TABLE `permiso_usuario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `persona`
--

DROP TABLE IF EXISTS `persona`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `persona` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellido` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `domicilio` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dni` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono_persona` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `persona`
--

LOCK TABLES `persona` WRITE;
/*!40000 ALTER TABLE `persona` DISABLE KEYS */;
INSERT INTO `persona` VALUES (1,'Miguel','Romero','Av. 25 de Mayo 1250','40123456','3704-551234'),(2,'Marcos','Kayser','Av. Gutnisky 3800','41987654','3704-445566'),(3,'Carlos','Medina','Los Lapachos 540','35678901','3704-112233'),(4,'Lorena','Villalba','Ruta 81 km 4','38456789','3704-998877'),(5,'Roberto','Sosa','Bv. Centenario 200','29876543','3704-334455'),(6,'Nilda','Fernández','Calle Estrada 88','32109876','3704-667788'),(7,'Distribuidora','AutoPartes Norte','Ruta 11 km 8, Formosa','30712345678','3704-421000'),(8,'Repuestos','Del Chaco SRL','Av. 9 de Julio 540, FMA','30798765432','3704-422000'),(9,'Ivan','Kaiser','Eva Peron 898 qsy',NULL,NULL),(11,'Luis','Sanchez','123',NULL,NULL),(12,'Miguel','Juarez','123','',''),(13,'Pepe','Gomez','Calle 121','12320120','3213123'),(14,'Luis','sanchez','1234',NULL,NULL),(15,'MIGE','ROMERO','Calle 122','232323','233232233223'),(16,'Roberto','Juarez','Su casa 99','5566544','3204566'),(17,'Admin','A','111','499904994','32120'),(18,'aaaa','aaa','1888','31313','1122'),(19,'RICARDO','HUBER','Calle 122','49900000','400000'),(20,'RICARDO','HUBER','Calle 122','41987654','11222'),(21,'Migel','Romeror','Lote 111','66666666','3706666'),(22,'uuu','oiii','a222','463213','3213122'),(23,'a','a','',NULL,NULL),(24,'Cristian','Martearena','El potrillo qsy','46333877','3704899965'),(25,'MULQUI','GAUNA','Puerto Elsa','666666669','66669'),(26,'pp','ss','dsd22','222222222','1111111111111');
/*!40000 ALTER TABLE `persona` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `productos`
--

DROP TABLE IF EXISTS `productos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `productos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre_producto` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cantidad_producto` int NOT NULL DEFAULT '0',
  `costo_producto` decimal(10,2) NOT NULL DEFAULT '0.00',
  `tipo_producto` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `productos`
--

LOCK TABLES `productos` WRITE;
/*!40000 ALTER TABLE `productos` DISABLE KEYS */;
INSERT INTO `productos` VALUES (1,'Filtro de aceite',20,1850.00,'Filtro'),(2,'Filtro de aire',15,2200.00,'Filtro'),(3,'Aceite de motor 10W40 1L',50,3500.00,'Lubricante'),(4,'Pastillas de freno delan',12,8900.00,'Frenos'),(5,'Pastillas de freno tras.',8,7500.00,'Frenos'),(6,'Bujías NGK (x4)',25,4200.00,'Encendido'),(7,'Correa de distribución',6,12500.00,'Motor'),(8,'Amortiguador delantero',4,18000.00,'Suspensión'),(9,'Líquido de frenos DOT 4',30,1200.00,'Líquidos'),(10,'Refrigerante verde 1L',22,1600.00,'Líquidos'),(11,'Batería 12V 60Ah',5,32000.00,'Eléctrico'),(12,'Bomba de agua',3,14500.00,'Motor'),(13,'Frenos1',2,15000.00,'Freno');
/*!40000 ALTER TABLE `productos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `proveedor`
--

DROP TABLE IF EXISTS `proveedor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `proveedor` (
  `id` int NOT NULL AUTO_INCREMENT,
  `cuit` bigint DEFAULT NULL,
  `nombre_proveedor` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefono` bigint DEFAULT NULL,
  `email` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `persona_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `persona_id` (`persona_id`),
  CONSTRAINT `proveedor_ibfk_1` FOREIGN KEY (`persona_id`) REFERENCES `persona` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `proveedor`
--

LOCK TABLES `proveedor` WRITE;
/*!40000 ALTER TABLE `proveedor` DISABLE KEYS */;
INSERT INTO `proveedor` VALUES (1,30712345678,'AutoPartes Norte SRL',3704421000,'ventas@autopartesnorte.com',7),(2,30798765432,'Repuestos Del Chaco',3704422000,'pedidos@repuestoschaco.com',8),(4,999999999,'Ramon Diaz',370499999,'rmon@gmail.com',NULL),(5,80000000000000,'Mauro Lombardo',37048755589,'miguelangelromero@xdgmail.com',NULL);
/*!40000 ALTER TABLE `proveedor` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `repuestos`
--

DROP TABLE IF EXISTS `repuestos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `repuestos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `categoria` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stock` int DEFAULT '0',
  `stock_minimo` int DEFAULT '5',
  `precio` decimal(10,2) DEFAULT '0.00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `repuestos`
--

LOCK TABLES `repuestos` WRITE;
/*!40000 ALTER TABLE `repuestos` DISABLE KEYS */;
INSERT INTO `repuestos` VALUES (2,'Frenos','Frenos',0,5,1500.00),(3,'Suspensión  Hilux','Suspensión',2,5,150000.00),(4,'Frenos blablabla','Frenos',15,5,9999.00),(5,'Aceite Xd','Lubricantes',19,5,25000.00);
/*!40000 ALTER TABLE `repuestos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock`
--

DROP TABLE IF EXISTS `stock`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `stock` (
  `id` int NOT NULL AUTO_INCREMENT,
  `alerta_stockBajo` int NOT NULL DEFAULT '5',
  `cantidad_disponible` int NOT NULL DEFAULT '0',
  `productos_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `productos_id` (`productos_id`),
  CONSTRAINT `stock_ibfk_1` FOREIGN KEY (`productos_id`) REFERENCES `productos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock`
--

LOCK TABLES `stock` WRITE;
/*!40000 ALTER TABLE `stock` DISABLE KEYS */;
INSERT INTO `stock` VALUES (1,5,20,1),(2,5,15,2),(3,10,50,3),(4,5,12,4),(5,5,3,5),(6,8,25,6),(7,3,2,7),(8,3,1,8),(9,8,30,9),(10,8,22,10),(11,3,5,11),(12,3,0,12),(13,5,2,13);
/*!40000 ALTER TABLE `stock` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tipo_vehiculo`
--

DROP TABLE IF EXISTS `tipo_vehiculo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tipo_vehiculo` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tipo_vehiculo` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tipo_vehiculo`
--

LOCK TABLES `tipo_vehiculo` WRITE;
/*!40000 ALTER TABLE `tipo_vehiculo` DISABLE KEYS */;
INSERT INTO `tipo_vehiculo` VALUES (1,'Automóvil'),(2,'Camioneta'),(3,'Camión'),(4,'Moto'),(5,'Utilitario');
/*!40000 ALTER TABLE `tipo_vehiculo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuario`
--

DROP TABLE IF EXISTS `usuario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuario` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre_usuario` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contrasena_usuario` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `persona_id` int DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `token_recuperacion` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `token_expiracion` datetime DEFAULT NULL,
  `rol` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT 'empleado',
  PRIMARY KEY (`id`),
  KEY `persona_id` (`persona_id`),
  CONSTRAINT `usuario_ibfk_1` FOREIGN KEY (`persona_id`) REFERENCES `persona` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuario`
--

LOCK TABLES `usuario` WRITE;
/*!40000 ALTER TABLE `usuario` DISABLE KEYS */;
INSERT INTO `usuario` VALUES (1,'mromero','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',1,1,'mromero@kayrom.com',NULL,NULL,'empleado'),(2,'mkayser','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',1,2,'mkayser@kayrom.com',NULL,NULL,'empleado'),(3,'cmedina','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',1,3,'cmedina@kayrom.com',NULL,NULL,'Primo de Mige'),(4,'kayseriv','$2y$10$j3MnT.fTJ6jq6f0XiJsw1.jGQ.v78e6O7itmeSsKANrwDX1qdF2bu',1,9,'maquitosk05@gmail.com',NULL,NULL,'admin'),(6,'lsanchez','$2y$10$OPwcvocVn.nrRu03lWOtm.jfTl96tS5qfJI25zMyeu0ami6FflOia',1,11,'luissanchez@gmail.com',NULL,NULL,'empleado'),(7,'mjuarez','$2y$10$7IbAdVIr1Tjc8D9mwbRFjOpLcBlorKJf3JoUtExydxWedJl0SYBN6',0,12,'miguelj@hotmail.com',NULL,NULL,'admin'),(8,'lsanchezz','$2y$10$tjjrDuBX4SPsPuXh2OD2WucoKGBSMaahjrSXCzeSefhZ4sJAB.ouO',1,14,'lsanchez@gmail.com',NULL,NULL,'encargado_repuesto'),(9,'juarez','$2y$10$r5rKoDv/4jUfcldAYRvhre.55g/HlwfGw5MxhgOYG.7s9JRmejztu',0,15,'mige@kayrom.com',NULL,NULL,'admin'),(10,'rjuarez','$2y$10$o7Pb/fTfAfe8.TIMYzHA0e5dTO3k/lIV1EOE.dCpuFSRgqlrbovU6',1,16,'rjuarez@g.com',NULL,NULL,'Prueba'),(11,'admin','$2y$10$RuunxiAqm0lVyomusSfcOuof31AuZ5/zhIlKRDyX1CArncFHwPZKq',1,17,'admin@admin123.com',NULL,NULL,'admin'),(12,'rhuber','$2y$10$yMU1B5WeAe3TE/cOEl6di.C6BRFvV47fiYaAYCZd3XcxoEUoIsKzu',1,20,'rhub@gmail.com',NULL,NULL,'empleado'),(13,'mije','$2y$10$2U4wOJtV1aE.GbYUzU8hdeRoQWzAI9v5XHIriVfocd1he/lDAhh3K',1,21,'mromero@gmail.com',NULL,NULL,'empleado'),(14,'uoi','$2y$10$YhnP79uddLCdZucUvdkWi.P46nzi3uALBAF4sH5VSTZACMBYzw1Q2',1,22,'u@gmail.com',NULL,NULL,'Hermana de Mulqui'),(15,'ai','$2y$10$aJZId7jQfKmSQ7y6pg6jH.rtSM1tsLpxODbjZENIfbTxbQHCneN/a',1,23,'a@gmail.com',NULL,NULL,'empleado'),(16,'Crismat','$2y$10$38ZjPh7jqRS08u69Xzqa4ulL4SZhWEATuUEufizxv/vkKJAu.OjHG',1,24,'matimartearena@gmail.com',NULL,NULL,'empleado'),(17,'gmulqui','$2y$10$Pd1FqQTStBaEHEICd10Zzut.5Tp3jvDzb0j1a2I8S0cSZqQzp6j7.',1,25,'gaunamulqui@gmail.com',NULL,NULL,'empleado'),(18,'ppguapo','$2y$10$8DwQCrePtFXXR4si8mByM.CPNV1a6kMj04rBVe41sSfCw/APVl1ni',1,26,'aaaaaaaa@gmail.com',NULL,NULL,'empleado');
/*!40000 ALTER TABLE `usuario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vehiculo`
--

DROP TABLE IF EXISTS `vehiculo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vehiculo` (
  `id` int NOT NULL AUTO_INCREMENT,
  `cantidad_vehiculo` int DEFAULT '0',
  `patente` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numero_chasis` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numero_motor` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_ingreso` datetime DEFAULT NULL,
  `compatibilidad_repuestos_id` int DEFAULT '0',
  `tipo_vehiculo_id` int DEFAULT NULL,
  `modelo_vehiculo_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tipo_vehiculo_id` (`tipo_vehiculo_id`),
  KEY `modelo_vehiculo_id` (`modelo_vehiculo_id`),
  CONSTRAINT `vehiculo_ibfk_1` FOREIGN KEY (`tipo_vehiculo_id`) REFERENCES `tipo_vehiculo` (`id`),
  CONSTRAINT `vehiculo_ibfk_2` FOREIGN KEY (`modelo_vehiculo_id`) REFERENCES `modelo_vehiculo` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vehiculo`
--

LOCK TABLES `vehiculo` WRITE;
/*!40000 ALTER TABLE `vehiculo` DISABLE KEYS */;
INSERT INTO `vehiculo` VALUES (2,1,'EF 456 GH','CH556677889900','MT112233','2024-01-22 00:00:00',0,2,2),(3,1,'IJ 789 KL','CH223344556677','MT445566','2023-11-05 00:00:00',0,2,3),(4,1,'MN 012 OP','CH334455667788','MT778899','2024-02-14 00:00:00',0,5,4),(5,1,'QR 345 ST','CH445566778899','MT001122','2023-09-30 00:00:00',0,4,5),(7,1,'YZ 901 AB','CH667788990022','MT667788','2024-04-10 00:00:00',0,1,8),(10,1,'AA22BC','123EE2','243321','2026-05-22 00:00:00',0,1,5),(11,1,'YZ 901 A2','99999999999999991','243321','2026-05-23 00:00:00',0,1,7);
/*!40000 ALTER TABLE `vehiculo` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-08-28 20:21:01
