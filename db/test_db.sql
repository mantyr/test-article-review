-- MySQL dump 10.13  Distrib 5.5.40, for Linux (i686)
--
-- Host: localhost    Database: test_db
-- ------------------------------------------------------
-- Server version	5.5.39-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `_article`
--

DROP TABLE IF EXISTS `_article`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `_article` (
  `article_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `creator_id` int(10) unsigned NOT NULL,
  `updater_id` int(10) unsigned NOT NULL,
  `title` varchar(200) NOT NULL,
  `body_text` text NOT NULL,
  `create_date` datetime DEFAULT '0000-00-00 00:00:00',
  `update_date` datetime DEFAULT '0000-00-00 00:00:00',
  `is_public` enum('ok','no') NOT NULL DEFAULT 'no',
  `type` enum('','article','review') NOT NULL DEFAULT 'review',
  PRIMARY KEY (`article_id`),
  KEY `key_creator_id` (`creator_id`),
  KEY `key_updater_id` (`updater_id`),
  KEY `key_title` (`title`),
  KEY `key_create_date` (`create_date`),
  KEY `key_update_date` (`update_date`),
  KEY `key_is_public` (`is_public`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `_article`
--

LOCK TABLES `_article` WRITE;
/*!40000 ALTER TABLE `_article` DISABLE KEYS */;
INSERT INTO `_article` VALUES (1,10,10,'title_1','body_text_1','2014-12-17 23:23:34','2014-12-17 23:23:34','no','review'),(2,10,10,'title_2','body_text_2','2014-12-17 23:23:34','2014-12-17 23:23:34','no','review'),(3,10,10,'title_3','body_text_3','2014-12-17 23:23:34','2014-12-17 23:23:34','no','review'),(4,10,20,'title_4_update','body_text_4_update','2014-12-17 23:23:34','2014-12-17 23:23:37','no','review'),(5,10,12,'title_5_update','body_text_5_update','2014-12-17 23:23:34','2014-12-17 23:23:37','no','review'),(6,10,12,'title_6_update','body_text_6_update','2014-12-17 23:23:34','2014-12-17 23:23:37','no','review'),(7,10,12,'title_7_update','body_text_7_update','2014-12-17 23:23:35','2014-12-17 23:23:37','no','review'),(8,10,10,'title_8','body_text_8','2014-12-17 23:23:35','2014-12-17 23:23:35','no','review'),(9,10,10,'title_9','body_text_9','2014-12-17 23:23:35','2014-12-17 23:23:35','no','review');
/*!40000 ALTER TABLE `_article` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `_article_draft`
--

DROP TABLE IF EXISTS `_article_draft`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `_article_draft` (
  `article_id` int(10) unsigned NOT NULL,
  `creator_id` int(10) unsigned NOT NULL,
  `create_date` datetime DEFAULT '0000-00-00 00:00:00',
  `title` varchar(200) NOT NULL,
  `body_text` text NOT NULL,
  PRIMARY KEY (`article_id`,`creator_id`),
  KEY `key_article_id` (`article_id`),
  KEY `key_creator_id` (`creator_id`),
  KEY `key_create_date` (`create_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `_article_draft`
--

LOCK TABLES `_article_draft` WRITE;
/*!40000 ALTER TABLE `_article_draft` DISABLE KEYS */;
INSERT INTO `_article_draft` VALUES (2,14,'2014-12-17 23:23:39','title_2_draft','body_text_2_draft'),(3,14,'2014-12-17 23:23:39','title_3_draft','body_text_3_draft'),(4,14,'2014-12-17 23:42:21','title_4_draft_update_2','body_text_4_draft_update_2'),(4,18,'2014-12-17 23:42:21','title_4_draft_update_2','body_text_4_draft_update_2');
/*!40000 ALTER TABLE `_article_draft` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `_article_history`
--

DROP TABLE IF EXISTS `_article_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `_article_history` (
  `history_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `article_id` int(10) unsigned NOT NULL,
  `creator_id` int(10) unsigned NOT NULL,
  `create_date` datetime DEFAULT '0000-00-00 00:00:00',
  `title` varchar(200) NOT NULL,
  `body_text` text NOT NULL,
  `is_public` enum('','ok','no') NOT NULL DEFAULT '',
  PRIMARY KEY (`history_id`),
  KEY `key_article_id` (`article_id`),
  KEY `key_creator_id` (`creator_id`),
  KEY `key_create_date` (`create_date`),
  KEY `key_title` (`title`),
  KEY `key_is_public` (`is_public`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `_article_history`
--

LOCK TABLES `_article_history` WRITE;
/*!40000 ALTER TABLE `_article_history` DISABLE KEYS */;
INSERT INTO `_article_history` VALUES (1,1,10,'2014-12-17 23:23:34','title_1','body_text_1',''),(2,2,10,'2014-12-17 23:23:34','title_2','body_text_2',''),(3,3,10,'2014-12-17 23:23:34','title_3','body_text_3',''),(4,4,10,'2014-12-17 23:23:34','title_4','body_text_4',''),(5,5,10,'2014-12-17 23:23:34','title_5','body_text_5',''),(6,6,10,'2014-12-17 23:23:34','title_6','body_text_6',''),(7,7,10,'2014-12-17 23:23:35','title_7','body_text_7',''),(8,8,10,'2014-12-17 23:23:35','title_8','body_text_8',''),(9,9,10,'2014-12-17 23:23:35','title_9','body_text_9',''),(10,4,12,'2014-12-17 23:23:37','title_4_update','body_text_4_update',''),(11,5,12,'2014-12-17 23:23:37','title_5_update','body_text_5_update',''),(12,6,12,'2014-12-17 23:23:37','title_6_update','body_text_6_update',''),(13,7,12,'2014-12-17 23:23:37','title_7_update','body_text_7_update',''),(15,4,20,'2014-12-17 23:56:15','','','ok'),(16,4,20,'2014-12-17 23:58:28','','','ok'),(17,4,20,'2014-12-17 23:59:34','','','no');
/*!40000 ALTER TABLE `_article_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `_role`
--

DROP TABLE IF EXISTS `_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `_role` (
  `role_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `_role`
--

LOCK TABLES `_role` WRITE;
/*!40000 ALTER TABLE `_role` DISABLE KEYS */;
/*!40000 ALTER TABLE `_role` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `_users`
--

DROP TABLE IF EXISTS `_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `_users` (
  `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `first_name` text NOT NULL,
  `last_name` text NOT NULL,
  `login` text NOT NULL,
  `password` text NOT NULL,
  `status` set('','deleted','aproved','banned','hidden') NOT NULL DEFAULT '',
  PRIMARY KEY (`user_id`),
  KEY `key_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `_users`
--

LOCK TABLES `_users` WRITE;
/*!40000 ALTER TABLE `_users` DISABLE KEYS */;
/*!40000 ALTER TABLE `_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `_users_role`
--

DROP TABLE IF EXISTS `_users_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `_users_role` (
  `user_id` int(10) unsigned NOT NULL,
  `role_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`user_id`,`role_id`),
  KEY `key_user_id` (`user_id`),
  KEY `key_role_id` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `_users_role`
--

LOCK TABLES `_users_role` WRITE;
/*!40000 ALTER TABLE `_users_role` DISABLE KEYS */;
/*!40000 ALTER TABLE `_users_role` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2014-12-18  3:06:43
