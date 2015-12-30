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
-- Table structure for table `dnssec_keys`
--

DROP TABLE IF EXISTS `dnssec_keys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dnssec_keys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dszone` int(11) NOT NULL,
  `filename` varchar(50) NOT NULL,
  `fkey` text,
  `fprivate` text,
  `archive` varchar(3) DEFAULT NULL,
  `refresh` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `id` (`id`),
  KEY `keyzon` (`dszone`),
  KEY `arch` (`archive`),
  KEY `filen` (`filename`),
  CONSTRAINT `fkdskeys` FOREIGN KEY (`dszone`) REFERENCES `dnssec_zones` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dnssec_keys`
--

LOCK TABLES `dnssec_keys` WRITE;
/*!40000 ALTER TABLE `dnssec_keys` DISABLE KEYS */;
/*!40000 ALTER TABLE `dnssec_keys` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dnssec_zones`
--

DROP TABLE IF EXISTS `dnssec_zones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dnssec_zones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `zone` int(11) NOT NULL,
  `krf` text NOT NULL,
  `dsset` text NOT NULL,
  UNIQUE KEY `id` (`id`),
  KEY `dsnam` (`zone`),
  CONSTRAINT `fkdszones` FOREIGN KEY (`zone`) REFERENCES `zones` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dnssec_zones`
--

LOCK TABLES `dnssec_zones` WRITE;
/*!40000 ALTER TABLE `dnssec_zones` DISABLE KEYS */;
/*!40000 ALTER TABLE `dnssec_zones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `options`
--

DROP TABLE IF EXISTS `options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `options` (
  `prefkey` varchar(20) NOT NULL,
  `preftype` varchar(6) NOT NULL DEFAULT '',
  `prefval` varchar(255) DEFAULT NULL,
  UNIQUE KEY `prefkey` (`prefkey`),
  KEY `ptype` (`preftype`),
  KEY `pval` (`prefval`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `options`
--

LOCK TABLES `options` WRITE;
/*!40000 ALTER TABLE `options` DISABLE KEYS */;
INSERT INTO `options` VALUES ('A','record','on'),('A6','record','off'),('AAAA','record','off'),('AFSDB','record','off'),('APL','record','off'),('ATMA','record','off'),('AXFR','record','off'),('CERT','record','off'),('CNAME','record','on'),('DNAME','record','off'),('DNSKEY','record','off'),('DS','record','off'),('EID','record','off'),('GPOS','record','off'),('HINFO','record','off'),('hostmaster','normal','postmaster.your.ns'),('ISDN','record','off'),('IXFR','record','off'),('KEY','record','off'),('KX','record','off'),('LOC','record','off'),('MAILB','record','off'),('master','normal','0.0.0.0'),('MINFO','record','off'),('MX','record','on'),('NAPTR','record','off'),('NIMLOC','record','off'),('NS','record','on'),('NSAP','record','off'),('NSAP-PTR','record','off'),('NSEC','record','off'),('NXT','record','off'),('OPT','record','off'),('prins','normal','your.master.ns'),('PTR','record','off'),('PX','record','off'),('range','normal','10'),('RP','record','off'),('RRSIG','record','off'),('RT','record','off'),('secns','normal','your.sec.ns'),('SIG','record','off'),('SINK','record','off'),('SRV','record','on'),('SSHFP','record','off'),('TKEY','record','off'),('TSIG','record','off'),('TXT','record','on'),('WKS','record','off'),('X25','record','off');
/*!40000 ALTER TABLE `options` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `records`
--

DROP TABLE IF EXISTS `records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `zone` int(11) NOT NULL DEFAULT '0',
  `host` varchar(128) NOT NULL,
  `ttl` int(11) DEFAULT NULL,
  `type` varchar(8) NOT NULL,
  `pri` int(11) NOT NULL DEFAULT '0',
  `destination` varchar(4096) DEFAULT NULL,
  UNIQUE KEY `id` (`id`),
  KEY `reczone` (`zone`),
  KEY `rech` (`host`),
  CONSTRAINT `fkrecords` FOREIGN KEY (`zone`) REFERENCES `zones` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `records`
--

LOCK TABLES `records` WRITE;
/*!40000 ALTER TABLE `records` DISABLE KEYS */;
/*!40000 ALTER TABLE `records` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `slave_zones`
--

DROP TABLE IF EXISTS `slave_zones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `slave_zones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `master` varchar(128) DEFAULT NULL,
  `owner` int(11) NOT NULL DEFAULT '0',
  `updated` varchar(3) NOT NULL DEFAULT 'yes',
  `valid` varchar(3) NOT NULL DEFAULT 'may',
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `sznam` (`name`),
  KEY `szupd` (`updated`),
  KEY `szow` (`owner`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `slave_zones`
--

LOCK TABLES `slave_zones` WRITE;
/*!40000 ALTER TABLE `slave_zones` DISABLE KEYS */;
/*!40000 ALTER TABLE `slave_zones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(32) NOT NULL,
  `realname` varchar(50) DEFAULT NULL,
  `password` varchar(32) NOT NULL,
  `admin` varchar(3) NOT NULL DEFAULT 'no',
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `usnam` (`username`),
  KEY `uspass` (`password`),
  KEY `admin` (`admin`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','Administrator','3c99cbdb5c15684e4fc190f4f17e443c','yes');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `zones`
--

DROP TABLE IF EXISTS `zones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `zones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `pri_dns` varchar(128) DEFAULT NULL,
  `sec_dns` varchar(128) DEFAULT NULL,
  `serial` int(11) NOT NULL DEFAULT '0',
  `refresh` int(11) NOT NULL DEFAULT '604800',
  `retry` int(11) NOT NULL DEFAULT '86400',
  `expire` int(11) NOT NULL DEFAULT '2419200',
  `ttl` int(11) NOT NULL DEFAULT '604800',
  `owner` int(11) NOT NULL DEFAULT '1',
  `valid` varchar(3) NOT NULL DEFAULT 'may',
  `updated` varchar(3) NOT NULL DEFAULT 'yes',
  `secured` varchar(3) NOT NULL DEFAULT 'no',
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `zonnam` (`name`),
  KEY `zonval` (`valid`),
  KEY `zonow` (`owner`),
  KEY `zonupd` (`updated`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `zones`
--

LOCK TABLES `zones` WRITE;
/*!40000 ALTER TABLE `zones` DISABLE KEYS */;
/*!40000 ALTER TABLE `zones` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2015-12-26 22:28:06
