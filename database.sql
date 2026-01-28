-- MySQL dump 10.13  Distrib 8.0.44, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: medivault
-- ------------------------------------------------------
-- Server version	8.0.44

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
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `category_id` int NOT NULL AUTO_INCREMENT,
  `category_name` varchar(50) NOT NULL,
  PRIMARY KEY (`category_id`),
  UNIQUE KEY `category_name` (`category_name`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (3,'Capsule'),(7,'Cream'),(6,'Eye Drop'),(5,'Inhaler'),(4,'Injection'),(10,'Ointment'),(9,'Suppository'),(8,'Suspension'),(2,'Syrup'),(1,'Tablet');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `coupons`
--

DROP TABLE IF EXISTS `coupons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `coupons` (
  `coupon_id` int NOT NULL AUTO_INCREMENT,
  `coupon_code` varchar(20) NOT NULL,
  `discount_percent` int NOT NULL,
  `expiry_date` date NOT NULL,
  `min_order_amount` decimal(10,2) DEFAULT '0.00',
  `status` enum('active','expired') DEFAULT 'active',
  PRIMARY KEY (`coupon_id`),
  UNIQUE KEY `coupon_code` (`coupon_code`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `coupons`
--

LOCK TABLES `coupons` WRITE;
/*!40000 ALTER TABLE `coupons` DISABLE KEYS */;
INSERT INTO `coupons` VALUES (3,'save10',10,'2026-01-31',500.00,'active'),(4,'SAVE50',50,'2026-01-31',1000.00,'active');
/*!40000 ALTER TABLE `coupons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `drug_interactions`
--

DROP TABLE IF EXISTS `drug_interactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `drug_interactions` (
  `interaction_id` int NOT NULL AUTO_INCREMENT,
  `medicine_id_1` int DEFAULT NULL,
  `medicine_id_2` int DEFAULT NULL,
  `severity` enum('Low','Moderate','High') NOT NULL,
  `warning_description` text,
  PRIMARY KEY (`interaction_id`),
  KEY `medicine_id_1` (`medicine_id_1`),
  KEY `medicine_id_2` (`medicine_id_2`),
  CONSTRAINT `drug_interactions_ibfk_1` FOREIGN KEY (`medicine_id_1`) REFERENCES `medicines` (`medicine_id`),
  CONSTRAINT `drug_interactions_ibfk_2` FOREIGN KEY (`medicine_id_2`) REFERENCES `medicines` (`medicine_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `drug_interactions`
--

LOCK TABLES `drug_interactions` WRITE;
/*!40000 ALTER TABLE `drug_interactions` DISABLE KEYS */;
INSERT INTO `drug_interactions` VALUES (1,1,2,'Low','নাপা এবং সেকলো একসাথে নেওয়া নিরাপদ, তবে দীর্ঘমেয়াদী ব্যবহারের আগে ডাক্তার দেখান।'),(2,8,9,'High','Azithromycin এবং Ciprofloxacin একসাথে নিলে হার্টের ছন্দে সমস্যা হতে পারে।'),(3,2,5,'Moderate','দুটি আলাদা গ্যাস্ট্রিকের ওষুধ (Omeprazole ও Esomeprazole) একসাথে খাওয়ার প্রয়োজন নেই।'),(4,1,4,'Low','জ্বর এবং এলার্জির ওষুধ একসাথে নিলে কিছুটা ঝিমঝিম ভাব হতে পারে।'),(5,3,10,'Moderate','ইনহেলার এবং চোখের ড্রপ ব্যবহারের সময় রক্তচাপ চেক করুন।'),(6,7,9,'High','Metronidazole এবং Ciprofloxacin এর কম্বিনেশনে লিভারে চাপ সৃষ্টি করতে পারে।'),(7,5,8,'Moderate','এন্টিবায়োটিক ব্যবহারের সময় এন্টাসিড ওষুধের কার্যকারিতা কমিয়ে দিতে পারে।'),(8,1,7,'Low','প্যারাসিটামল এবং মেট্রোনিডাজল একসাথে নেওয়া সাধারণত নিরাপদ।'),(9,6,4,'High','Tofen এবং Fenadin একসাথে নিলে তীব্র ঘুম বা তন্দ্রাচ্ছন্ন ভাব হতে পারে।'),(10,2,9,'Moderate','গ্যাস্ট্রিকের ওষুধ নির্দিষ্ট কিছু এন্টিবায়োটিকের শোষণ কমিয়ে দেয়।');
/*!40000 ALTER TABLE `drug_interactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory_batches`
--

DROP TABLE IF EXISTS `inventory_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventory_batches` (
  `batch_id` int NOT NULL AUTO_INCREMENT,
  `medicine_id` int DEFAULT NULL,
  `batch_number` varchar(50) NOT NULL,
  `expiry_date` date NOT NULL,
  `quantity_instock` int NOT NULL,
  `purchase_price` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`batch_id`),
  KEY `medicine_id` (`medicine_id`),
  CONSTRAINT `inventory_batches_ibfk_1` FOREIGN KEY (`medicine_id`) REFERENCES `medicines` (`medicine_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_batches`
--

LOCK TABLES `inventory_batches` WRITE;
/*!40000 ALTER TABLE `inventory_batches` DISABLE KEYS */;
INSERT INTO `inventory_batches` VALUES (1,1,'NP-001','2027-12-31',493,12.00),(2,2,'SC-202','2026-06-15',299,5.00),(3,3,'SR-999','2026-11-20',31,780.00),(4,4,'FN-45','2027-05-10',200,8.00),(5,5,'MX-88','2027-01-01',400,6.00),(6,6,'TF-12','2026-08-30',99,55.00),(7,7,'AM-07','2028-02-28',1000,3.00),(8,8,'ZT-500','2026-10-10',150,30.00),(9,9,'CP-101','2027-03-20',249,9.50),(10,10,'VS-11','2026-12-25',80,120.00),(11,11,'BAT-979','2026-01-31',78,10.00),(12,1,'BAT-E3CD9','2026-12-31',18,5.00),(23,12,'BAT-7A08C','2027-11-01',496,240.00),(24,13,'BAT-E082C','2027-01-01',499,90.00);
/*!40000 ALTER TABLE `inventory_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `medicine_reviews`
--

DROP TABLE IF EXISTS `medicine_reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `medicine_reviews` (
  `review_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `medicine_id` int NOT NULL,
  `rating` int NOT NULL,
  `comment` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`review_id`),
  UNIQUE KEY `unique_user_medicine` (`user_id`,`medicine_id`),
  KEY `medicine_id` (`medicine_id`),
  CONSTRAINT `medicine_reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `medicine_reviews_ibfk_2` FOREIGN KEY (`medicine_id`) REFERENCES `medicines` (`medicine_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `medicine_reviews`
--

LOCK TABLES `medicine_reviews` WRITE;
/*!40000 ALTER TABLE `medicine_reviews` DISABLE KEYS */;
INSERT INTO `medicine_reviews` VALUES (1,11,1,5,'best','2026-01-27 23:37:09'),(7,9,1,5,'Excellent service! The delivery was super fast and the medicine is authentic.','2026-01-27 23:36:17'),(8,10,2,4,'Good product, but the delivery took a bit longer than expected.','2026-01-26 23:36:17'),(9,12,3,5,'Very effective medicine. Thanks to MediVault for genuine products.','2026-01-25 23:36:17');
/*!40000 ALTER TABLE `medicine_reviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `medicines`
--

DROP TABLE IF EXISTS `medicines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `medicines` (
  `medicine_id` int NOT NULL AUTO_INCREMENT,
  `medicine_name` varchar(100) NOT NULL,
  `generic_name` varchar(100) NOT NULL,
  `indications` text,
  `category_id` int DEFAULT NULL,
  `supplier_id` int DEFAULT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `min_stock_level` int DEFAULT '10',
  `medicine_image` varchar(255) DEFAULT 'default_medicine.jpg',
  `requires_prescription` tinyint(1) DEFAULT '0',
  `description` text,
  `side_effects` text,
  PRIMARY KEY (`medicine_id`),
  KEY `category_id` (`category_id`),
  KEY `supplier_id` (`supplier_id`),
  CONSTRAINT `medicines_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`),
  CONSTRAINT `medicines_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `medicines`
--

LOCK TABLES `medicines` WRITE;
/*!40000 ALTER TABLE `medicines` DISABLE KEYS */;
INSERT INTO `medicines` VALUES (1,'Napa Extend','Paracetamol','Fever, Cold, Headache, Body Pain',1,1,20.00,50,'napa.jpg',0,'Napa (Paracetamol) is an analgesic and antipyretic. It is used to treat many conditions such as headache, muscle aches, arthritis, backache, toothaches, colds, and fevers. It relieves pain in mild arthritis but has no effect on the underlying inflammation and swelling of the joint.','In rare cases, Napa may cause allergic reactions like skin rash, itching or hives, swelling of the face, lips, or tongue. Other rare side effects include nausea, stomach pain, loss of appetite, dark urine, and clay-colored stools.'),(2,'Seclo 20','Omeprazole','Gastric, Heartburn, Acidity, Gas',3,2,7.00,100,'seclo.jpg',0,'This medicine is a proton pump inhibitor (PPI). It decreases the amount of acid produced in the stomach. It is used to treat symptoms of gastroesophageal reflux disease (GERD) and other conditions caused by excess stomach acid. It is also used to promote healing of erosive esophagitis.','Common side effects include stomach pain, gas, nausea, vomiting, and diarrhea. Headache is also common. Long-term use may increase the risk of bone fractures and vitamin B-12 deficiency.'),(3,'Seretide 250','Salmeterol','General illness, follow doctor advice',5,4,850.00,10,'seretide.jpg',0,'Seretide 250 is a pharmaceutical product manufactured to high-quality standards. It is used for the treatment of specific medical conditions as prescribed by a healthcare professional. Please consult your doctor for precise dosage and administration instructions.','Like all medicines, this product can cause side effects, although not everybody gets them. Common side effects may include mild gastrointestinal discomfort. If you experience any severe reactions, stop use and contact your doctor immediately.'),(4,'Fenadin 120','Fexofenadine','General illness, follow doctor advice',1,3,10.00,40,'fenadine.jpg',0,'Fenadin 120 is a pharmaceutical product manufactured to high-quality standards. It is used for the treatment of specific medical conditions as prescribed by a healthcare professional. Please consult your doctor for precise dosage and administration instructions.','Like all medicines, this product can cause side effects, although not everybody gets them. Common side effects may include mild gastrointestinal discomfort. If you experience any severe reactions, stop use and contact your doctor immediately.'),(5,'Maxpro 20','Esomeprazole','General illness, follow doctor advice',1,4,8.00,60,'maxpro.jpg',0,'Maxpro 20 is a pharmaceutical product manufactured to high-quality standards. It is used for the treatment of specific medical conditions as prescribed by a healthcare professional. Please consult your doctor for precise dosage and administration instructions.','Like all medicines, this product can cause side effects, although not everybody gets them. Common side effects may include mild gastrointestinal discomfort. If you experience any severe reactions, stop use and contact your doctor immediately.'),(6,'Tofen Syrup','Ketotifen','General illness, follow doctor advice',2,3,65.00,20,'tofen.jpg',0,'Tofen Syrup is a pharmaceutical product manufactured to high-quality standards. It is used for the treatment of specific medical conditions as prescribed by a healthcare professional. Please consult your doctor for precise dosage and administration instructions.','Like all medicines, this product can cause side effects, although not everybody gets them. Common side effects may include mild gastrointestinal discomfort. If you experience any severe reactions, stop use and contact your doctor immediately.'),(7,'Amodis 400','Metronidazole','General illness, follow doctor advice',1,2,4.00,80,'amodis.jpg',0,'Amodis 400 is a pharmaceutical product manufactured to high-quality standards. It is used for the treatment of specific medical conditions as prescribed by a healthcare professional. Please consult your doctor for precise dosage and administration instructions.','Like all medicines, this product can cause side effects, although not everybody gets them. Common side effects may include mild gastrointestinal discomfort. If you experience any severe reactions, stop use and contact your doctor immediately.'),(8,'Zithrin 500','Azithromycin','General illness, follow doctor advice',3,2,35.00,30,'zithrin.jpg',0,'Zithrin 500 is a pharmaceutical product manufactured to high-quality standards. It is used for the treatment of specific medical conditions as prescribed by a healthcare professional. Please consult your doctor for precise dosage and administration instructions.','Like all medicines, this product can cause side effects, although not everybody gets them. Common side effects may include mild gastrointestinal discomfort. If you experience any severe reactions, stop use and contact your doctor immediately.'),(9,'Ciprocin 500','Ciprofloxacin','General illness, follow doctor advice',1,2,12.00,25,'ciprocin.jpg',1,'Ciprocin 500 is a pharmaceutical product manufactured to high-quality standards. It is used for the treatment of specific medical conditions as prescribed by a healthcare professional. Please consult your doctor for precise dosage and administration instructions.','Like all medicines, this product can cause side effects, although not everybody gets them. Common side effects may include mild gastrointestinal discomfort. If you experience any severe reactions, stop use and contact your doctor immediately.'),(10,'Visine Eye Drop','Tetrahydrozoline','General illness, follow doctor advice',6,6,150.00,15,'visine.jpg',0,'Visine Eye Drop is a pharmaceutical product manufactured to high-quality standards. It is used for the treatment of specific medical conditions as prescribed by a healthcare professional. Please consult your doctor for precise dosage and administration instructions.','Like all medicines, this product can cause side effects, although not everybody gets them. Common side effects may include mild gastrointestinal discomfort. If you experience any severe reactions, stop use and contact your doctor immediately.'),(11,'Napa Extra','Paracetamol','Fever, Cold, Headache, Body Pain',3,NULL,10.00,100,'napaextra.jpg',1,'Napa (Paracetamol) is an analgesic and antipyretic. It is used to treat many conditions such as headache, muscle aches, arthritis, backache, toothaches, colds, and fevers. It relieves pain in mild arthritis but has no effect on the underlying inflammation and swelling of the joint.','In rare cases, Napa may cause allergic reactions like skin rash, itching or hives, swelling of the face, lips, or tongue. Other rare side effects include nausea, stomach pain, loss of appetite, dark urine, and clay-colored stools.'),(12,'Budicort','Budesonide BP',NULL,5,3,250.00,100,'budicort.jpg',1,NULL,NULL),(13,'Basok','Caugh Syrup','Cold, Caugh',2,5,100.00,100,'basok.jpg',0,'Basok Syrup is a herbal formulation traditionally used to relieve cough and respiratory discomfort. It contains Basok (Justicia adhatoda), a well-known medicinal plant that helps soothe the airways and support healthy breathing.\r\n\r\nThis syrup is effective in relieving dry cough, productive cough, chest congestion, throat irritation, and cold-related cough. Basok helps loosen mucus, making it easier to expel, while also calming irritation in the throat and bronchial passages.\r\n\r\nUses:\r\n\r\nDry and wet cough\r\nChest congestion\r\nCold-related cough\r\nThroat irritation\r\nBreathing discomfort due to mucus buildup\r\n\r\nHow It Works:\r\n\r\nBasok acts as a natural expectorant and bronchodilator. It helps reduce mucus thickness, clears airways, and supports smoother breathing.\r\n\r\nDirections for Use:\r\n\r\nUse as directed by a physician. The dosage may vary depending on age and condition.\r\n\r\nSide Effects:\r\n\r\nGenerally well tolerated when used as recommended. If any unusual symptoms occur, discontinue use and consult a healthcare professional.\r\n\r\nPrecautions:\r\n\r\nPregnant or breastfeeding women and individuals with chronic medical conditions should consult a doctor before use. Keep out of reach of children.',NULL);
/*!40000 ALTER TABLE `medicines` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_items` (
  `order_item_id` int NOT NULL AUTO_INCREMENT,
  `order_id` int DEFAULT NULL,
  `medicine_id` int DEFAULT NULL,
  `quantity` int NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  PRIMARY KEY (`order_item_id`),
  KEY `order_id` (`order_id`),
  KEY `medicine_id` (`medicine_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`medicine_id`) REFERENCES `medicines` (`medicine_id`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
INSERT INTO `order_items` VALUES (1,1,1,10,15.00,150.00),(2,2,2,50,7.00,350.00),(3,3,3,1,850.00,850.00),(4,4,5,8,8.00,64.00),(5,5,9,10,12.00,120.00),(6,6,1,20,15.00,300.00),(7,7,6,1,65.00,65.00),(8,8,8,14,35.00,490.00),(9,9,2,140,7.00,980.00),(10,10,8,1,35.00,35.00),(11,11,1,1,15.00,15.00),(12,11,3,1,850.00,850.00),(13,12,1,1,15.00,15.00),(14,12,2,1,7.00,7.00),(15,13,3,1,850.00,850.00),(16,15,1,5,15.00,75.00),(17,15,3,10,850.00,8500.00),(18,20,1,5,20.00,100.00),(19,25,9,1,12.00,12.00),(20,30,1,1,20.00,20.00),(21,32,11,1,10.00,10.00),(22,34,11,20,10.00,200.00),(23,34,3,1,850.00,850.00),(24,36,3,1,850.00,850.00),(25,38,3,1,850.00,850.00),(29,42,1,1,20.00,20.00),(30,43,1,25,20.00,500.00),(31,44,3,2,850.00,1700.00),(32,45,3,1,850.00,850.00),(34,47,12,4,250.00,1000.00);
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `order_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `customer_name` varchar(100) DEFAULT NULL,
  `customer_phone` varchar(15) DEFAULT NULL,
  `order_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` enum('Cash','Card','Mobile_Banking') DEFAULT 'Cash',
  `order_status` enum('Pending','Confirmed','Shipped','Delivered','Cancelled') DEFAULT 'Delivered',
  `order_type` enum('Online','Offline') NOT NULL,
  `prescription_id` int DEFAULT NULL,
  PRIMARY KEY (`order_id`),
  KEY `user_id` (`user_id`),
  KEY `prescription_id` (`prescription_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`prescription_id`) REFERENCES `prescriptions` (`prescription_id`)
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (1,4,NULL,NULL,'2026-01-14 08:10:21',150.00,'Mobile_Banking','Delivered','Online',1),(2,6,NULL,NULL,'2026-01-14 08:10:21',450.00,'Cash','Delivered','Offline',3),(3,5,NULL,NULL,'2026-01-14 08:10:21',850.00,'Card','Shipped','Online',2),(4,7,NULL,NULL,'2026-01-14 08:10:21',70.00,'Cash','Delivered','Offline',4),(5,8,NULL,NULL,'2026-01-14 08:10:21',120.00,'Mobile_Banking','Pending','Online',5),(6,9,NULL,NULL,'2026-01-14 08:10:21',300.00,'Cash','Delivered','Offline',6),(7,10,NULL,NULL,'2026-01-14 08:10:21',65.00,'Mobile_Banking','Confirmed','Online',7),(8,4,NULL,NULL,'2026-01-14 08:10:21',500.00,'Cash','Delivered','Online',1),(9,6,NULL,NULL,'2026-01-14 08:10:21',1000.00,'Cash','Delivered','Offline',3),(10,5,NULL,NULL,'2026-01-14 08:10:21',35.00,'Card','Pending','Online',2),(11,11,NULL,NULL,'2026-01-26 06:22:38',865.00,'Cash','Confirmed','Online',NULL),(12,11,NULL,NULL,'2026-01-26 06:23:38',22.00,'Cash','Delivered','Online',NULL),(13,11,NULL,NULL,'2026-01-26 06:56:22',850.00,'Cash','Delivered','Online',NULL),(15,11,NULL,NULL,'2026-01-26 19:45:29',8575.00,'Cash','Pending','Online',NULL),(20,1,NULL,NULL,'2026-01-26 21:40:31',100.00,'Cash','Pending','Online',NULL),(25,11,NULL,NULL,'2026-01-26 22:13:05',12.00,'Cash','Pending','Online',NULL),(29,11,NULL,NULL,'2026-01-27 00:26:51',0.00,'Cash','Cancelled','Online',NULL),(30,11,NULL,NULL,'2026-01-27 00:26:51',0.00,'Cash','Cancelled','Online',NULL),(31,11,NULL,NULL,'2026-01-27 01:18:18',10.00,'Cash','Confirmed','Online',NULL),(32,11,NULL,NULL,'2026-01-27 01:18:18',10.00,'Cash','Cancelled','Online',NULL),(33,11,NULL,NULL,'2026-01-27 01:35:13',945.00,'Cash','Pending','Online',NULL),(34,11,NULL,NULL,'2026-01-27 01:35:13',945.00,'Cash','Pending','Online',NULL),(35,11,NULL,NULL,'2026-01-27 01:35:49',850.00,'Cash','Pending','Online',NULL),(36,11,NULL,NULL,'2026-01-27 01:35:49',850.00,'Cash','Pending','Online',NULL),(37,11,NULL,NULL,'2026-01-27 01:40:11',850.00,'Cash','Pending','Online',NULL),(38,11,NULL,NULL,'2026-01-27 01:40:11',850.00,'Cash','Pending','Online',NULL),(42,12,NULL,NULL,'2026-01-27 20:11:58',20.00,'Cash','Pending','Online',12),(43,12,NULL,NULL,'2026-01-27 20:13:06',500.00,'Cash','Pending','Online',12),(44,12,NULL,NULL,'2026-01-27 20:30:32',1700.00,'Cash','Pending','Online',12),(45,12,NULL,NULL,'2026-01-27 20:34:29',765.00,'Cash','Delivered','Online',12),(47,11,NULL,NULL,'2026-01-28 07:22:57',500.00,'Cash','Delivered','Online',NULL);
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `prescriptions`
--

DROP TABLE IF EXISTS `prescriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `prescriptions` (
  `prescription_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `order_id` int DEFAULT NULL,
  `doctor_name` varchar(100) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `uploaded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`prescription_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `prescriptions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `prescriptions`
--

LOCK TABLES `prescriptions` WRITE;
/*!40000 ALTER TABLE `prescriptions` DISABLE KEYS */;
INSERT INTO `prescriptions` VALUES (1,4,NULL,'Dr. Mahbub Rahman','uploads/presc_kamal_001.jpg','2026-01-14 08:18:27'),(2,5,NULL,'Dr. Shamima Nasrin','uploads/presc_abir_002.jpg','2026-01-14 08:18:27'),(3,6,NULL,'Dr. Azharul Haque','uploads/presc_mofiz_003.jpg','2026-01-14 08:18:27'),(4,7,NULL,'Dr. Farhana Yasmin','uploads/presc_laila_004.jpg','2026-01-14 08:18:27'),(5,8,NULL,'Dr. SM Zakir','uploads/presc_sonia_005.jpg','2026-01-14 08:18:27'),(6,9,NULL,'Dr. Nusrat Jahan','uploads/presc_tanvir_006.jpg','2026-01-14 08:18:27'),(7,10,NULL,'Dr. Tanvir Anjum','uploads/presc_anika_007.jpg','2026-01-14 08:18:27'),(8,4,NULL,'Dr. Rezwan Ahmed','uploads/presc_kamal_008.jpg','2026-01-14 08:18:27'),(9,5,NULL,'Dr. Sadiya Afrin','uploads/presc_abir_009.jpg','2026-01-14 08:18:27'),(10,8,NULL,'Dr. Kamal Uddin','uploads/presc_sonia_010.jpg','2026-01-14 08:18:27'),(12,12,NULL,'Dr Abc','assets/prescriptions/1769544575_apple.jpg','2026-01-27 20:09:35');
/*!40000 ALTER TABLE `prescriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_purchases`
--

DROP TABLE IF EXISTS `stock_purchases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `stock_purchases` (
  `purchase_id` int NOT NULL AUTO_INCREMENT,
  `supplier_id` int DEFAULT NULL,
  `medicine_id` int DEFAULT NULL,
  `quantity` int NOT NULL,
  `purchase_price` decimal(10,2) NOT NULL,
  `purchase_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`purchase_id`),
  KEY `supplier_id` (`supplier_id`),
  KEY `medicine_id` (`medicine_id`),
  CONSTRAINT `stock_purchases_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`),
  CONSTRAINT `stock_purchases_ibfk_2` FOREIGN KEY (`medicine_id`) REFERENCES `medicines` (`medicine_id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_purchases`
--

LOCK TABLES `stock_purchases` WRITE;
/*!40000 ALTER TABLE `stock_purchases` DISABLE KEYS */;
INSERT INTO `stock_purchases` VALUES (1,1,11,100,10.00,'2026-01-26 20:07:59'),(2,1,1,50,5.00,'2026-01-26 20:22:38'),(13,3,12,500,240.00,'2026-01-27 22:02:38'),(14,5,13,500,90.00,'2026-01-27 22:21:42');
/*!40000 ALTER TABLE `stock_purchases` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `suppliers`
--

DROP TABLE IF EXISTS `suppliers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `suppliers` (
  `supplier_id` int NOT NULL AUTO_INCREMENT,
  `supplier_name` varchar(100) NOT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `address` text,
  PRIMARY KEY (`supplier_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `suppliers`
--

LOCK TABLES `suppliers` WRITE;
/*!40000 ALTER TABLE `suppliers` DISABLE KEYS */;
INSERT INTO `suppliers` VALUES (1,'Beximco Pharma','Mr. Asif','01711000001','asif@beximco.com',NULL),(2,'Square Pharma','Mr. Karim','01822000002','karim@square.com',NULL),(3,'Incepta Pharma','Mr. Zaman','01933000003','zaman@incepta.com',NULL),(4,'Renata Limited','Mr. Rahim','01744000004','rahim@renata.com',NULL),(5,'ACI Health','Mr. Shuvo','01855000005','shuvo@aci.com',NULL),(6,'Aristopharma','Mr. Hasan','01966000006','hasan@aristo.com',NULL),(7,'Opsonin Pharma','Mr. Rakib','01677000007','rakib@opsonin.com',NULL),(8,'Eskayef (SK+F)','Mr. Tanvir','01588000008','tanvir@skf.com',NULL),(9,'Radiant Pharma','Mr. Jalil','01799000009','jalil@radiant.com',NULL),(10,'Sanofi Bangladesh','Mr. Faisal','01811000010','faisal@sanofi.com',NULL);
/*!40000 ALTER TABLE `suppliers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `role` enum('admin','pharmacist','patient','walk-in','user') DEFAULT 'user',
  `phone` varchar(15) NOT NULL,
  `address` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'System Admin','admin@medivault.com','$2y$10$2bK2tUVwcYOPFZKh98zD6edIKuwPswK9p3mWH1lsW9Cl7i6tMWcgm','admin','01700112233','Dhaka HQ','2026-01-14 08:10:21'),(2,'Rahim Ahmed','rahim@email.com','hash_staff1','pharmacist','01800112233','Dhaka Branch','2026-01-14 08:10:21'),(3,'Sultana Akter','sultana@email.com','hash_staff2','pharmacist','01900112233','Dhaka Branch','2026-01-14 08:10:21'),(4,'Kamal Hossain','kamal@gmail.com','hash_p1','patient','01711111111','Uttara, Dhaka','2026-01-14 08:10:21'),(5,'Abir Hasan','abir@gmail.com','hash_p2','patient','01722222222','Banani, Dhaka','2026-01-14 08:10:21'),(6,'Mofizul Islam',NULL,NULL,'walk-in','01733333333','Mirpur, Dhaka','2026-01-14 08:10:21'),(7,'Laila Begum',NULL,NULL,'walk-in','01744444444','Savar, Dhaka','2026-01-14 08:10:21'),(8,'Sonia Mirza','sonia@gmail.com','hash_p5','patient','01755555555','Chittagong','2026-01-14 08:10:21'),(9,'Tanvir Ahmed',NULL,NULL,'walk-in','01766666666','Narayanganj','2026-01-14 08:10:21'),(10,'Anika Tabassum','anika@gmail.com','hash_p7','patient','01777777777','Khulna','2026-01-14 08:10:21'),(11,'Badhon Saha','badhon4863@gmail.com','$2y$10$afsbqdMi7lMaKWvL1cxPpON5q7TmGzd3E5yRVNbs8PQXOORe8xif.','patient','01735414228','Khulna','2026-01-18 18:22:35'),(12,'Topu','tpu@hello.com','$2y$10$jv82ve70pDzGVydyPs4wKuO57RmbZcKH5P4Tp616hhoNTAvoGoRne','patient','01735414229','Khulna','2026-01-18 18:32:13'),(13,'No','no@gmail.com','$2y$10$Tzoh/H3coIJ.I0w.TrpXEeWtFuIzzacwEFAhcfQ3B4iuUSe/EX2ea','patient','01777777777','Dhaka','2026-01-28 06:30:53'),(14,'Abc','abc@gmail.com','$2y$10$Sg/eANuR3u.JHhQb8srJS.1o/OvIFq7IOiVGDIayt4ewEu69dPve.','patient','01234567891','Dhaka','2026-01-28 06:41:24');
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

-- Dump completed on 2026-01-29  2:36:51
