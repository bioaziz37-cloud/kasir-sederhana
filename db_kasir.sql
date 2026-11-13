-- MySQL dump 10.13  Distrib 8.4.3, for Win64 (x86_64)
--
-- Host: localhost    Database: db_kasir
-- ------------------------------------------------------
-- Server version	8.4.3

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `detail_transaksi`
--

DROP TABLE IF EXISTS `detail_transaksi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `detail_transaksi` (
  `id` int NOT NULL AUTO_INCREMENT,
  `kode_transaksi` varchar(50) NOT NULL,
  `menu_id` int NOT NULL,
  `jumlah` int NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detail_transaksi`
--

LOCK TABLES `detail_transaksi` WRITE;
/*!40000 ALTER TABLE `detail_transaksi` DISABLE KEYS */;
INSERT INTO `detail_transaksi` VALUES (1,'TRX-20261013030919',3,1,3000.00),(2,'TRX-20261013030937',3,2,6000.00),(3,'TRX-20261014060928',3,1,3000.00),(4,'TRX-20261014060928',4,1,17000.00),(5,'TRX-20261014061853',3,1,3000.00),(6,'TRX-20261014061902',4,1,17000.00),(7,'TRX-20261014072558',3,2,6000.00),(8,'TRX-20261014075639',3,1,3000.00),(9,'TRX-20261014080116',3,1,3000.00),(10,'TRX-20261015024724',8,1,15000.00),(11,'TRX-20261015024724',3,1,3000.00),(12,'TRX-20261015024737',6,1,27000.00),(13,'TRX-20261015024737',4,1,17000.00),(14,'TRX-20261015024737',5,1,10000.00),(15,'TRX-20261015042413',6,1,27000.00),(16,'TRX-20261015042413',8,1,15000.00),(17,'TRX-20261028102751',10,1,17000.00),(18,'TRX-20261028102751',8,1,15000.00),(19,'TRX-20261109134204',11,1,14000.00),(20,'TRX-20261109150816151',10,1,17000.00),(21,'TRX-20261109150816151',11,2,34000.00),(22,'TRX-20261110030343415',11,2,28000.00),(23,'TRX-20261110030343415',8,1,14000.00),(24,'TRX-20261110030343415',3,1,14000.00);
/*!40000 ALTER TABLE `detail_transaksi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `menu`
--

DROP TABLE IF EXISTS `menu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `menu` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama_menu` varchar(100) NOT NULL,
  `harga` decimal(10,2) NOT NULL,
  `stok` int NOT NULL,
  `kategori` varchar(50) NOT NULL DEFAULT 'Makanan',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menu`
--

LOCK TABLES `menu` WRITE;
/*!40000 ALTER TABLE `menu` DISABLE KEYS */;
INSERT INTO `menu` VALUES (3,'Air Mineral',3000.00,5,'Minuman'),(4,'Nasi Goreng',17000.00,7,'Makanan'),(5,'Americano',10000.00,49,'Minuman'),(6,'Bakmi Ayam Charitsu',27000.00,48,'Makanan'),(7,'Nasi Ayam Blackpaper',23000.00,20,'Makanan'),(8,'Bubur Ayam Bangladesh',15000.00,46,'Makanan'),(9,'Lemon Ice',8000.00,40,'Minuman'),(13,'Kue Nastar',85000.00,10,'Makanan');
/*!40000 ALTER TABLE `menu` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transaksi`
--

DROP TABLE IF EXISTS `transaksi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `transaksi` (
  `id` int NOT NULL AUTO_INCREMENT,
  `kode_transaksi` varchar(50) NOT NULL,
  `nama_pemesan` varchar(100) NOT NULL DEFAULT '',
  `tanggal` datetime DEFAULT CURRENT_TIMESTAMP,
  `total_harga` decimal(10,2) NOT NULL,
  `bayar` decimal(10,2) NOT NULL,
  `kembalian` decimal(10,2) NOT NULL,
  `metode_pembayaran` varchar(50) DEFAULT 'Tunai',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transaksi`
--

LOCK TABLES `transaksi` WRITE;
/*!40000 ALTER TABLE `transaksi` DISABLE KEYS */;
INSERT INTO `transaksi` VALUES (1,'TRX-20261013030919','','2026-10-13 03:09:19',3000.00,4000.00,1000.00,'Tunai'),(2,'TRX-20261013030937','','2026-10-13 03:09:37',6000.00,20000.00,14000.00,'Tunai'),(3,'TRX-20261014060928','','2026-10-14 06:09:28',20000.00,30000.00,10000.00,'Tunai'),(4,'TRX-20261014061853','','2026-10-14 06:18:53',3000.00,4000.00,1000.00,'Tunai'),(5,'TRX-20261014061902','','2026-10-14 06:19:02',17000.00,20000.00,3000.00,'Tunai'),(6,'TRX-20261014072558','','2026-10-14 07:25:58',6000.00,10000.00,4000.00,'Tunai'),(7,'TRX-20261014075639','','2026-10-14 07:56:39',3000.00,3000.00,0.00,'QRIS'),(8,'TRX-20261014080116','','2026-10-14 08:01:16',3000.00,3000.00,0.00,'QRIS'),(9,'TRX-20261015024724','','2026-10-15 02:47:24',18000.00,20000.00,2000.00,'Tunai'),(10,'TRX-20261015024737','','2026-10-15 02:47:37',54000.00,60000.00,6000.00,'Tunai'),(11,'TRX-20261015042413','','2026-10-15 04:24:13',42000.00,50000.00,8000.00,'Tunai'),(12,'TRX-20261028102751','','2026-10-28 10:27:51',32000.00,600000.00,568000.00,'Tunai'),(13,'TRX-20261109134204','','2026-11-09 13:42:04',14000.00,14000.00,0.00,'QRIS'),(14,'TRX-20261109150816151','','2026-11-09 15:08:16',51000.00,51000.00,0.00,'QRIS'),(16,'TRX-20261110030343415','Raditya','2026-11-10 03:03:43',56000.00,100000.00,44000.00,'Tunai');
/*!40000 ALTER TABLE `transaksi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','$2y$10$2wWT/oP3aPeYSD6NGQGAb.klcWoleNdGOX8S7idMy5tCWGIT4C9au','Administrator Toko');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-10 13:58:01
