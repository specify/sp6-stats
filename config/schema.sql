-- MySQL dump 10.13  Distrib 5.7.39, for Linux (x86_64)
--
-- Host: bimysql.nhm.ku.edu    Database: feedback
-- ------------------------------------------------------
-- Server version       5.7.39-0ubuntu0.18.04.2-log

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

CREATE DATABASE IF NOT EXISTS `feedback`;
USE `feedback`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `feedback` (
  `FeedbackID` int(11) NOT NULL AUTO_INCREMENT,
  `TimestampCreated` datetime NOT NULL,
  `Subject` varchar(128) DEFAULT NULL,
  `Component` varchar(32) DEFAULT NULL,
  `Issue` varchar(128) DEFAULT NULL,
  `Comments` text,
  `Id` varchar(64) DEFAULT NULL,
  `OSName` varchar(32) DEFAULT NULL,
  `OSVersion` varchar(32) DEFAULT NULL,
  `JavaVersion` varchar(32) DEFAULT NULL,
  `JavaVendor` varchar(32) DEFAULT NULL,
  `AppVersion` varchar(32) DEFAULT NULL,
  `Collection` varchar(64) DEFAULT NULL,
  `Discipline` varchar(64) DEFAULT NULL,
  `Division` varchar(64) DEFAULT NULL,
  `Institution` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`FeedbackID`)
) ENGINE=InnoDB AUTO_INCREMENT=71 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;


CREATE DATABASE IF NOT EXISTS `stats`;
USE `stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `colstats` (
  `ColStatsID` int(11) NOT NULL AUTO_INCREMENT,
  `TimestampCreated` datetime NOT NULL,
  `TimestampModified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Id` varchar(64) DEFAULT NULL,
  `CountAmt` int(11) DEFAULT NULL,
  `ip` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`ColStatsID`)
) ENGINE=InnoDB AUTO_INCREMENT=34266 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;


CREATE DATABASE IF NOT EXISTS `exception`;
USE `exception`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `exception` (
  `ExceptionID` int(11) NOT NULL AUTO_INCREMENT,
  `TimestampCreated` datetime NOT NULL,
  `TaskName` varchar(64) DEFAULT NULL,
  `Title` varchar(64) DEFAULT NULL,
  `Bug` varchar(8) DEFAULT NULL,
  `Comments` text,
  `stacktrace` longtext,
  `ClassName` varchar(128) DEFAULT NULL,
  `Id` varchar(64) DEFAULT NULL,
  `OSName` varchar(32) DEFAULT NULL,
  `OSVersion` varchar(32) DEFAULT NULL,
  `JavaVersion` varchar(32) DEFAULT NULL,
  `JavaVendor` varchar(32) DEFAULT NULL,
  `UserName` varchar(32) DEFAULT NULL,
  `IP` varchar(32) DEFAULT NULL,
  `AppVersion` varchar(32) DEFAULT NULL,
  `collection` text,
  `discipline` text,
  `division` text,
  `institution` text,
  `DoIgnore` bit(1) DEFAULT b'0',
  PRIMARY KEY (`ExceptionID`)
) ENGINE=InnoDB AUTO_INCREMENT=630294 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
