/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19-12.3.2-MariaDB, for debian-linux-gnu (aarch64)
--
-- Host: localhost    Database: senayan
-- ------------------------------------------------------
-- Server version	12.3.2-MariaDB-ubu2404

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;

--
-- Table structure for table `backup_log`
--

DROP TABLE IF EXISTS `backup_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `backup_log` (
  `backup_log_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `backup_time` datetime NOT NULL,
  `backup_file` text DEFAULT NULL,
  PRIMARY KEY (`backup_log_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `backup_log`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `backup_log` WRITE;
/*!40000 ALTER TABLE `backup_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `backup_log` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `biblio`
--

DROP TABLE IF EXISTS `biblio`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `biblio` (
  `biblio_id` int(11) NOT NULL AUTO_INCREMENT,
  `gmd_id` int(3) DEFAULT NULL,
  `title` text NOT NULL,
  `sor` varchar(200) DEFAULT NULL,
  `edition` varchar(50) DEFAULT NULL,
  `isbn_issn` varchar(32) DEFAULT NULL,
  `publisher_id` int(11) DEFAULT NULL,
  `publish_year` varchar(20) DEFAULT NULL,
  `collation` varchar(50) DEFAULT NULL,
  `series_title` varchar(200) DEFAULT NULL,
  `call_number` varchar(50) DEFAULT NULL,
  `language_id` char(5) DEFAULT 'en',
  `source` varchar(10) DEFAULT NULL,
  `publish_place_id` int(11) DEFAULT NULL,
  `classification` varchar(40) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `image` varchar(100) DEFAULT NULL,
  `file_att` varchar(255) DEFAULT NULL,
  `opac_hide` smallint(1) DEFAULT 0,
  `promoted` smallint(1) DEFAULT 0,
  `labels` text DEFAULT NULL,
  `frequency_id` int(11) NOT NULL DEFAULT 0,
  `spec_detail_info` text DEFAULT NULL,
  `content_type_id` int(11) DEFAULT NULL,
  `media_type_id` int(11) DEFAULT NULL,
  `carrier_type_id` int(11) DEFAULT NULL,
  `input_date` datetime DEFAULT NULL,
  `last_update` datetime DEFAULT NULL,
  `uid` int(11) DEFAULT NULL,
  PRIMARY KEY (`biblio_id`),
  KEY `references_idx` (`gmd_id`,`publisher_id`,`language_id`,`publish_place_id`),
  KEY `publisher_id` (`publisher_id`),
  KEY `classification` (`classification`),
  KEY `biblio_flag_idx` (`opac_hide`,`promoted`),
  KEY `rda_idx` (`content_type_id`,`media_type_id`,`carrier_type_id`),
  KEY `uid` (`uid`),
  FULLTEXT KEY `title_ft_idx` (`title`,`series_title`),
  FULLTEXT KEY `notes_ft_idx` (`notes`),
  FULLTEXT KEY `labels` (`labels`)
) ENGINE=MyISAM AUTO_INCREMENT=70 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `biblio`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `biblio` WRITE;
/*!40000 ALTER TABLE `biblio` DISABLE KEYS */;
INSERT INTO `biblio` VALUES
(1,1,'PHP 5 for dummies',NULL,NULL,'0764541668',1,'2004','xiv, 392 p. : ill. ; 24 cm.','For dummies','005.13/3-22 Jan p','en',NULL,1,'005.13/3 22',NULL,'php5_dummies.jpg',NULL,0,0,NULL,0,NULL,NULL,NULL,NULL,'2007-11-29 15:36:50','2007-11-29 16:26:59',NULL),
(2,1,'Linux In a Nutshell',NULL,'Fifth Edition','9780596009304',2,'2005','xiv, 925 p. : ill. ; 23 cm.','In a Nutshell','005.4/32-22 Ell l','en',NULL,2,'005.4/32 22',NULL,'linux_in_a_nutshell.jpg',NULL,0,0,NULL,0,NULL,NULL,NULL,NULL,'2007-11-29 15:53:35','2007-11-29 16:26:10',NULL),
(3,1,'The Definitive Guide to MySQL 5',NULL,NULL,'9781590595350',3,'2005','784p.','Definitive Guide Series','005.75/85-22 Kof d','en',NULL,NULL,'005.75/85 22',NULL,'mysql_def_guide.jpg',NULL,0,0,NULL,0,NULL,NULL,NULL,NULL,'2007-11-29 16:01:08','2007-11-29 16:26:33',NULL),
(4,1,'Cathedral and the Bazaar: Musings on Linux and Open Source by an Accidental Revolutionary',NULL,NULL,'0-596-00108-8',2,'2001','208p.',NULL,'005.4/3222 Ray c','en',NULL,2,'005.4/32 22','The Cathedral & the Bazaar is a must for anyone who cares about the future of the computer industry or the dynamics of the information economy. This revised and expanded paperback edition includes new material on open source developments in 1999 and 2000. Raymond\'s clear and effective writing style accurately describing the benefits of open source software has been key to its success. (Source: http://safari.oreilly.com/0596001088)','cathedral_bazaar.jpg','cathedral-bazaar.pdf',0,0,NULL,0,NULL,NULL,NULL,NULL,'2007-11-29 16:14:44','2007-11-29 16:25:43',NULL),
(5,1,'Producing open source software : how to run a successful free software project',NULL,'1st ed.','9780596007591',2,'2005','xx, 279 p. ; 24 cm.',NULL,'005.1-22 Fog p','en',NULL,2,'005.1 22','Includes index.','producing_oss.jpg',NULL,0,0,NULL,0,NULL,NULL,NULL,NULL,'2007-11-29 16:20:45','2007-11-29 16:31:21',NULL),
(6,1,'PostgreSQL : a comprehensive guide to building, programming, and administering PostgreSQL databases',NULL,'1st ed.','0735712573',4,'2003','xvii, 790 p. : ill. ; 23cm.','DeveloperÃ¢â‚¬â„¢s library','005.75/85-22 Kor p','en',NULL,3,'005.75/85 22','PostgreSQL is the world\'s most advanced open-source database. PostgreSQL is the most comprehensive, in-depth, and easy-to-read guide to this award-winning database. This book starts with a thorough overview of SQL, a description of all PostgreSQL data types, and a complete explanation of PostgreSQL commands.','postgresql.jpg',NULL,0,0,NULL,0,NULL,NULL,NULL,NULL,'2007-11-29 16:29:33','2026-08-10 18:09:33',NULL),
(7,1,'Web application architecture : principles, protocols, and practices',NULL,NULL,'0471486566',5,'2003','xi, 357 p. : ill. ; 23 cm.',NULL,'005.7/2-21 Leo w','en',NULL,1,'005.7/2 21','An in-depth examination of the core concepts and general principles of Web application development.\r\nThis book uses examples from specific technologies (e.g., servlet API or XSL), without promoting or endorsing particular platforms or APIs. Such knowledge is critical when designing and debugging complex systems. This conceptual understanding makes it easier to learn new APIs that arise in the rapidly changing Internet environment.','webapp_arch.jpg',NULL,0,0,NULL,0,NULL,NULL,NULL,NULL,'2007-11-29 16:41:57','2007-11-29 16:32:46',NULL),
(8,1,'Ajax : creating Web pages with asynchronous JavaScript and XML',NULL,NULL,'9780132272674',6,'2007','xxii, 384 p. : ill. ; 24 cm.','Bruce PerensÃ¢â‚¬â„¢ Open Source series','006.7/86-22 Woy a','en',NULL,4,'006.7/86 22','Using Ajax, you can build Web applications with the sophistication and usability of traditional desktop applications and you can do it using standards and open source software. Now, for the first time, there\'s an easy, example-driven guide to Ajax for every Web and open source developer, regardless of experience.','ajax.jpg',NULL,0,0,NULL,0,NULL,NULL,NULL,NULL,'2007-11-29 16:47:20','2026-08-10 18:09:33',NULL),
(9,1,'The organization of information',NULL,'2nd ed.','1563089769',7,'2004','xxvii, 417 p. : ill. ; 27 cm.','Library and information science text series','025-22 Tay o','en',NULL,5,'025 22','A basic textbook for students of library and information studies, and a guide for practicing school library media specialists. Describes the impact of global forces and the school district on the development and operation of a media center, the technical and human side of management, programmatic activities, supportive services to students, and the quality and quantity of resources available to support programs.','organization_information.jpg',NULL,0,0,NULL,0,NULL,NULL,NULL,NULL,'2007-11-29 16:54:12','2007-11-29 16:27:20',NULL),
(10,1,'Library and Information Center Management',NULL,'7th ed.','9781591584063',7,'2007','xxviii, 492 p. : ill. ; 27 cm.','Library and information science text series','025.1-22 Stu l','en',NULL,5,'025.1 22',NULL,'library_info_center.JPG',NULL,0,0,NULL,0,NULL,NULL,NULL,NULL,'2007-11-29 16:58:51','2007-11-29 16:27:40',NULL),
(11,1,'Information Architecture for the World Wide Web: Designing Large-Scale Web Sites',NULL,'2nd ed.','9780596000356',2,'2002','500p.',NULL,'006.7-22 Mor i','en',NULL,6,'006.7 22','Information Architecture for the World Wide Web is about applying the principles of architecture and library science to web site design. Each website is like a public building, available for tourists and regulars alike to breeze through at their leisure. The job of the architect is to set up the framework for the site to make it comfortable and inviting for people to visit, relax in, and perhaps even return to someday.','information_arch.jpg',NULL,0,0,NULL,0,NULL,NULL,NULL,NULL,'2007-11-29 17:26:14','2007-11-29 16:32:25',NULL),
(12,1,'Corruption and development',NULL,NULL,'9780714649023',8,'1998','166 p. : ill. ; 22 cm.',NULL,'364.1 Rob c','en',NULL,7,'364.1/322/091724 21','The articles assembled in this volume offer a fresh approach to analysing the problem of corruption in developing countries and the k means to tackle the phenomenon.','corruption_development.jpg',NULL,0,0,NULL,0,NULL,NULL,NULL,NULL,'2007-11-29 17:45:30','2007-11-29 16:20:53',NULL),
(13,1,'Corruption and development : the anti-corruption campaigns',NULL,NULL,'0230525504',9,'2007','310p.',NULL,'364.1 Bra c','en',NULL,8,'364.1/323091724 22','This book provides a multidisciplinary interrogation of the global anti-corruption campaigns of the last ten years, arguing that while some positive change is observable, the period is also replete with perverse consequences and unintended outcomes','corruption_development_anti_campaign.jpg',NULL,0,0,NULL,0,NULL,NULL,NULL,NULL,'2007-11-29 17:49:49','2007-11-29 16:19:48',NULL),
(14,1,'Pigs at the trough : how corporate greed and political corruption are undermining America',NULL,NULL,'1400047714',10,'2003','275 p. ; 22 cm.',NULL,'364.1323 Huf p','en',NULL,8,'364.1323',NULL,'pigs_at_trough.jpg',NULL,0,0,NULL,0,NULL,NULL,NULL,NULL,'2007-11-29 17:56:00','2007-11-29 16:18:33',NULL),
(15,1,'Lords of poverty : the power, prestige, and corruption of the international aid business',NULL,NULL,'9780871134691',11,'1994','xvi, 234 p. ; 22 cm.',NULL,'338.9 Han l','en',NULL,8,'338.9/1/091724 20','Lords of Poverty is a case study in betrayals of a public trust. The shortcomings of aid are numerous, and serious enough to raise questions about the viability of the practice at its most fundamental levels. Hancocks report is thorough, deeply shocking, and certain to cause critical reevaluation of the governments motives in giving foreign aid, and of the true needs of our intended beneficiaries.','lords_of_poverty.jpg',NULL,0,0,NULL,0,NULL,NULL,NULL,NULL,'2007-11-29 18:08:13','2007-11-29 16:13:11',NULL),
(16,1,'Под игото','Иван Вазов',NULL,'9789541805123',12,'1894','изд., 44 см',NULL,'886.7-31 ВАЗ','bg',NULL,9,'886.7-31',NULL,NULL,NULL,0,1,NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(17,1,'Немили-недраги','Иван Вазов',NULL,'9789544712045',13,'1883','изд., 47 см',NULL,'886.7-32 ВАЗ','bg',NULL,9,'886.7-32',NULL,NULL,NULL,0,0,NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(18,1,'Разкази','Елин Пелин',NULL,'9789544712121',13,'1904','изд., 49 см',NULL,'886.7-32 ПЕЛ','bg',NULL,9,'886.7-32',NULL,NULL,NULL,0,0,NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(19,1,'Гераците','Елин Пелин',NULL,'9789540907777',14,'1911','изд., 51 см',NULL,'886.7-31 ПЕЛ','bg',NULL,9,'886.7-31',NULL,NULL,NULL,0,0,NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(20,1,'Старопланински легенди','Йордан Йовков',NULL,'9789542608811',17,'1927','изд., 52 см',NULL,'886.7-32 ЙОВ','bg',NULL,10,'886.7-32',NULL,NULL,NULL,0,0,NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(21,1,'Чифликът край границата','Йордан Йовков',NULL,'9789542812345',16,'1934','изд., 54 см',NULL,'886.7-31 ЙОВ','bg',NULL,9,'886.7-31',NULL,NULL,NULL,0,0,NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(22,1,'Тютюн','Димитър Димов',NULL,'9789541805567',12,'1951','изд., 55 см',NULL,'886.7-31 ДИМ','bg',NULL,9,'886.7-31',NULL,NULL,NULL,0,1,NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(23,1,'Антихрист','Емилиян Станев',NULL,'9789544712900',13,'1970','изд., 58 см',NULL,'886.7-31 СТА','bg',NULL,9,'886.7-31',NULL,NULL,NULL,0,0,NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(24,1,'Крадецът на праскови','Емилиян Станев',NULL,'9789540908888',14,'1948','изд., 59 см',NULL,'886.7-32 СТА','bg',NULL,9,'886.7-32',NULL,NULL,NULL,0,0,NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(25,1,'Време разделно','Антон Дончев',NULL,'9789540901234',14,'1964','изд., 61 см',NULL,'886.7-31 ДОН','bg',NULL,9,'886.7-31',NULL,NULL,NULL,0,0,NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(26,1,'Физика на тъгата','Георги Господинов',NULL,'9789544912223',22,'2011','изд., 63 см',NULL,'886.7-31 ГОС','bg',NULL,10,'886.7-31',NULL,NULL,NULL,0,1,NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(27,1,'Естествен роман','Георги Господинов',NULL,'9789544911111',22,'1999','изд., 65 см',NULL,'886.7-31 ГОС','bg',NULL,10,'886.7-31',NULL,NULL,NULL,0,0,NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(28,1,'Хайка за вълци','Ивайло Петров',NULL,'9789545291234',15,'1986','изд., 66 см',NULL,'886.7-31 ПЕТ','bg',NULL,9,'886.7-31',NULL,NULL,NULL,0,0,NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(29,1,'Нощем с белите коне','Павел Вежинов',NULL,'9789544713333',13,'1975','изд., 67 см',NULL,'886.7-31 ВЕЖ','bg',NULL,9,'886.7-31',NULL,NULL,NULL,0,0,NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(30,1,'Бариерата','Павел Вежинов',NULL,'9789542609999',17,'1976','изд., 68 см',NULL,'886.7-32 ВЕЖ','bg',NULL,9,'886.7-32',NULL,NULL,NULL,0,0,NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(31,1,'Стихотворения','Христо Ботев',NULL,'9789541806000',12,'1875','изд., 70 см',NULL,'886.7-1 БОТ','bg',NULL,9,'886.7-1',NULL,NULL,NULL,0,0,NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(32,1,'Безсъници','Пейо Яворов',NULL,'9789544714000',13,'1907','изд., 72 см',NULL,'886.7-1 ЯВО','bg',NULL,9,'886.7-1',NULL,NULL,NULL,0,0,NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(33,1,'Стихотворения','Димчо Дебелянов',NULL,'9789540902222',14,'1920','изд., 73 см',NULL,'886.7-1 ДЕБ','bg',NULL,9,'886.7-1',NULL,NULL,NULL,0,0,NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(34,1,'Пътувания','Блага Димитрова',NULL,'9789545292345',15,'1965','изд., 74 см',NULL,'886.7-1 ДИМ','bg',NULL,9,'886.7-1',NULL,NULL,NULL,0,0,NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(35,1,'Бай Ганьо','Алеко Константинов',NULL,'9789541806345',12,'1895','изд., 75 см',NULL,'886.7-32 КОН','bg',NULL,9,'886.7-32',NULL,NULL,NULL,0,0,NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(36,1,'Дядовата ръкавичка','Ран Босилек',NULL,'9789542720011',21,'1936','изд., 77 см',NULL,'087.5 БОС','bg',NULL,9,'087.5',NULL,NULL,NULL,0,0,NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(37,1,'Патиланско царство','Ран Босилек',NULL,'9789546600022',25,'1938','изд., 80 см',NULL,'087.5 БОС','bg',NULL,9,'087.5',NULL,NULL,NULL,0,0,NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(38,1,'Пипи Дългото чорапче','Астрид Линдгрен',NULL,'9789546601111',25,'1945','изд., 82 см',NULL,'839.7-93 ЛИН','bg',NULL,9,'839.7-93',NULL,NULL,NULL,0,0,NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(39,1,'Емил от Льонеберя','Астрид Линдгрен',NULL,'9789546602222',25,'1963','изд., 84 см',NULL,'839.7-93 ЛИН','bg',NULL,9,'839.7-93',NULL,NULL,NULL,0,0,NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(40,1,'Хари Потър и Философският камък','Джоан Роулинг',NULL,'9789542704455',24,'1997','изд., 86 см',NULL,'820-93 РОУ','bg',NULL,9,'820-93',NULL,NULL,NULL,0,1,NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(41,1,'Хари Потър и Стаята на тайните','Джоан Роулинг',NULL,'9789542704462',24,'1998','изд., 89 см',NULL,'820-93 РОУ','bg',NULL,9,'820-93',NULL,NULL,NULL,0,0,NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(42,1,'Хобитът','Джон Р. Р. Толкин',NULL,'9789542705566',24,'1937','изд., 91 см',NULL,'820-93 ТОЛ','bg',NULL,9,'820-93',NULL,NULL,NULL,0,0,NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(43,1,'Малкият принц','Антоан дьо Сент-Екзюпери',NULL,'9789545272233',23,'1943','изд., 93 см',NULL,'840-93 СЕН','bg',NULL,9,'840-93',NULL,NULL,NULL,0,1,NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(44,1,'Хвърчащата класна стая','Ерих Кестнер',NULL,'9789546603333',25,'1933','изд., 96 см',NULL,'830-93 КЕС','bg',NULL,9,'830-93',NULL,NULL,NULL,0,0,NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(45,1,'Приключенията на Том Сойер','Марк Твен',NULL,'9789546604444',25,'1876','изд., 97 см',NULL,'820(73)-93 ТВЕ','bg',NULL,9,'820(73)-93',NULL,NULL,NULL,0,0,NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(46,1,'Бяла приказка','Валери Петров',NULL,'9789542720099',21,'1978','изд., 99 см',NULL,'886.7-93 ПЕТ','bg',NULL,9,'886.7-93',NULL,NULL,NULL,0,0,NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(47,1,'Математика за 7. клас','Колектив',NULL,'9789540136001',20,'2018','изд., 101 см',NULL,'51(075.3) КОЛ','bg',NULL,9,'51(075.3)',NULL,NULL,NULL,0,0,NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(48,1,'Български език за 7. клас','Колектив',NULL,'9789540136018',20,'2018','изд., 25 см',NULL,'808.67(075.3) КОЛ','bg',NULL,9,'808.67(075.3)',NULL,NULL,NULL,0,0,NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(49,1,'История и цивилизации за 8. клас','Колектив',NULL,'9789540135025',20,'2017','изд., 29 см',NULL,'93(075.3) КОЛ','bg',NULL,9,'93(075.3)',NULL,NULL,NULL,0,0,NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(50,1,'Биология и здравно образование за 9. клас','Колектив',NULL,'9789540137032',20,'2018','изд., 32 см',NULL,'57(075.3) КОЛ','bg',NULL,9,'57(075.3)',NULL,NULL,NULL,0,0,NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(51,1,'Физика и астрономия за 10. клас','Колектив',NULL,'9789540138049',20,'2019','изд., 35 см',NULL,'53(075.3) КОЛ','bg',NULL,9,'53(075.3)',NULL,NULL,NULL,0,0,NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(52,1,'География и икономика за 9. клас','Колектив',NULL,'9789540137056',20,'2018','изд., 38 см',NULL,'91(075.3) КОЛ','bg',NULL,9,'91(075.3)',NULL,NULL,NULL,0,0,NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(53,1,'Български тълковен речник','Колектив',NULL,'9789540200011',NULL,'2012','изд., 40 см',NULL,'808.67(038) КОЛ','bg',NULL,9,'808.67(038)',NULL,NULL,NULL,0,0,NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(54,1,'Правописен речник на българския език','Колектив',NULL,'9789548283022',NULL,'2016','изд., 42 см',NULL,'808.67(038) КОЛ','bg',NULL,9,'808.67(038)',NULL,NULL,NULL,0,0,NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(55,1,'Английско-български речник','Колектив',NULL,'9789545287733',19,'2010','изд., 44 см',NULL,'802.0-3(038) КОЛ','bg',NULL,9,'802.0-3(038)',NULL,NULL,NULL,0,0,NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(56,1,'Енциклопедия България','Колектив',NULL,'9789545280044',19,'2005','изд., 46 см',NULL,'030(497.2) КОЛ','bg',NULL,9,'030(497.2)',NULL,NULL,NULL,0,0,NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(57,1,'Атлас на света','Колектив',NULL,'9789545289955',19,'2014','изд., 47 см',NULL,'912(100) КОЛ','bg',NULL,9,'912(100)',NULL,NULL,NULL,0,0,NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(58,1,'История на България','Колектив',NULL,'9786191524001',18,'2015','изд., 48 см',NULL,'949.72 КОЛ','bg',NULL,9,'949.72',NULL,NULL,NULL,0,0,NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(59,1,'Философия. Въведение','Колектив',NULL,'9786191523018',18,'2013','изд., 50 см',NULL,'1(075) КОЛ','bg',NULL,9,'1(075)',NULL,NULL,NULL,0,0,NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(60,1,'Кратка история на времето','Колектив',NULL,'9786191524025',18,'2014','изд., 51 см',NULL,'52 КОЛ','bg',NULL,9,'52',NULL,NULL,NULL,0,0,NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(61,1,'История на родния край','Колектив',NULL,'9786192660011',26,'2019','изд., 52 см',NULL,'908(497.2) КОЛ','bg',NULL,12,'908(497.2)',NULL,NULL,NULL,0,0,NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(62,1,'Народни песни от нашия край','Колектив',NULL,'9786192660028',26,'2017','изд., 53 см',NULL,'398.8(497.2) КОЛ','bg',NULL,12,'398.8(497.2)',NULL,NULL,NULL,0,0,NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(63,1,'The Old Man and the Sea','Колектив',NULL,'9780684801223',15,'1952','изд., 54 см',NULL,'820(73)-31 КОЛ','en',NULL,9,'820(73)-31',NULL,NULL,NULL,0,0,NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(64,1,'Престъпление и наказание','Фьодор Достоевски',NULL,'9789545293456',15,'1866','изд., 55 см',NULL,'882-31 ДОС','ru',NULL,9,'882-31',NULL,NULL,NULL,0,0,NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(65,1,'Сто години самота','Габриел Гарсия Маркес',NULL,'9789548227667',NULL,'1967','изд., 57 см',NULL,'860(861)-31 МАР','es',NULL,9,'860(861)-31',NULL,NULL,NULL,0,0,NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(66,1,'Списание Читалище (2025, бр. 1-12)','Колектив',NULL,NULL,NULL,'2025','изд., 58 см',NULL,'05 КОЛ','bg',NULL,9,'05',NULL,NULL,NULL,0,0,NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(67,1,'Списание Родознание (2025)','Колектив',NULL,NULL,NULL,'2025','изд., 59 см',NULL,'05 КОЛ','bg',NULL,9,'05',NULL,NULL,NULL,0,0,NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(68,31,'Под игото (DVD, игрален филм)','Колектив',NULL,NULL,26,'2020','изд., 60 см',NULL,'791.4 КОЛ','bg',NULL,9,'791.4',NULL,NULL,NULL,0,0,NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(69,29,'Българска народна музика (CD)','Колектив',NULL,NULL,26,'2018','изд., 61 см',NULL,'78(497.2) КОЛ','bg',NULL,9,'78(497.2)',NULL,NULL,NULL,0,0,NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL);
/*!40000 ALTER TABLE `biblio` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `biblio_attachment`
--

DROP TABLE IF EXISTS `biblio_attachment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `biblio_attachment` (
  `biblio_id` int(11) NOT NULL,
  `file_id` int(11) NOT NULL,
  `placement` enum('link','popup','embed') DEFAULT NULL,
  `access_type` enum('public','private') NOT NULL,
  `access_limit` text DEFAULT NULL,
  KEY `biblio_id` (`biblio_id`),
  KEY `file_id` (`file_id`),
  KEY `biblio_id_2` (`biblio_id`,`file_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `biblio_attachment`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `biblio_attachment` WRITE;
/*!40000 ALTER TABLE `biblio_attachment` DISABLE KEYS */;
/*!40000 ALTER TABLE `biblio_attachment` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `biblio_author`
--

DROP TABLE IF EXISTS `biblio_author`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `biblio_author` (
  `biblio_id` int(11) NOT NULL DEFAULT 0,
  `author_id` int(11) NOT NULL DEFAULT 0,
  `level` int(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`biblio_id`,`author_id`),
  KEY `biblio_id` (`biblio_id`),
  KEY `author_id` (`author_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `biblio_author`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `biblio_author` WRITE;
/*!40000 ALTER TABLE `biblio_author` DISABLE KEYS */;
INSERT INTO `biblio_author` VALUES
(1,1,1),
(2,2,1),
(2,3,2),
(2,4,2),
(2,5,2),
(2,6,2),
(3,7,1),
(3,8,2),
(4,9,1),
(5,10,1),
(6,11,1),
(6,12,2),
(7,13,1),
(7,14,2),
(8,15,1),
(9,16,1),
(10,17,1),
(10,18,2),
(11,19,1),
(11,20,2),
(12,21,1),
(13,22,1),
(14,23,1),
(15,24,1),
(16,25,1),
(17,25,1),
(18,27,1),
(19,27,1),
(20,28,1),
(21,28,1),
(22,38,1),
(23,37,1),
(24,37,1),
(25,39,1),
(26,40,1),
(27,40,1),
(28,33,1),
(29,34,1),
(30,34,1),
(31,26,1),
(32,29,1),
(33,30,1),
(34,35,1),
(35,31,1),
(36,42,1),
(37,42,1),
(38,43,1),
(39,43,1),
(40,44,1),
(41,44,1),
(42,45,1),
(43,46,1),
(44,47,1),
(45,48,1),
(46,36,1),
(47,51,1),
(48,51,1),
(49,51,1),
(50,51,1),
(51,51,1),
(52,51,1),
(53,51,1),
(54,51,1),
(55,51,1),
(56,51,1),
(57,51,1),
(58,51,1),
(59,51,1),
(60,51,1),
(61,51,1),
(62,51,1),
(63,51,1),
(64,49,1),
(65,50,1),
(66,51,1),
(67,51,1),
(68,51,1),
(69,51,1);
/*!40000 ALTER TABLE `biblio_author` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `biblio_custom`
--

DROP TABLE IF EXISTS `biblio_custom`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `biblio_custom` (
  `biblio_id` int(11) NOT NULL,
  PRIMARY KEY (`biblio_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci COMMENT='one to one relation with real biblio table';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `biblio_custom`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `biblio_custom` WRITE;
/*!40000 ALTER TABLE `biblio_custom` DISABLE KEYS */;
/*!40000 ALTER TABLE `biblio_custom` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `biblio_log`
--

DROP TABLE IF EXISTS `biblio_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `biblio_log` (
  `biblio_log_id` int(11) NOT NULL AUTO_INCREMENT,
  `biblio_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `realname` varchar(100) NOT NULL,
  `title` text NOT NULL,
  `ip` varchar(200) NOT NULL,
  `action` varchar(50) NOT NULL,
  `affectedrow` varchar(50) NOT NULL,
  `rawdata` text NOT NULL,
  `additional_information` text NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`biblio_log_id`),
  KEY `realname` (`realname`),
  KEY `biblio_id` (`biblio_id`),
  KEY `user_id` (`user_id`),
  KEY `ip` (`ip`),
  KEY `action` (`action`),
  KEY `affectedrow` (`affectedrow`),
  KEY `date` (`date`),
  FULLTEXT KEY `title` (`title`),
  FULLTEXT KEY `rawdata` (`rawdata`),
  FULLTEXT KEY `additional_information` (`additional_information`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `biblio_log`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `biblio_log` WRITE;
/*!40000 ALTER TABLE `biblio_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `biblio_log` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `biblio_mark`
--

DROP TABLE IF EXISTS `biblio_mark`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `biblio_mark` (
  `id` varchar(32) NOT NULL,
  `member_id` varchar(20) NOT NULL,
  `biblio_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  UNIQUE KEY `id` (`id`),
  KEY `member_id_idx` (`member_id`),
  KEY `biblio_id_idx` (`biblio_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `biblio_mark`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `biblio_mark` WRITE;
/*!40000 ALTER TABLE `biblio_mark` DISABLE KEYS */;
/*!40000 ALTER TABLE `biblio_mark` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `biblio_relation`
--

DROP TABLE IF EXISTS `biblio_relation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `biblio_relation` (
  `biblio_id` int(11) NOT NULL DEFAULT 0,
  `rel_biblio_id` int(11) NOT NULL DEFAULT 0,
  `rel_type` int(1) DEFAULT 1,
  PRIMARY KEY (`biblio_id`,`rel_biblio_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `biblio_relation`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `biblio_relation` WRITE;
/*!40000 ALTER TABLE `biblio_relation` DISABLE KEYS */;
/*!40000 ALTER TABLE `biblio_relation` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `biblio_topic`
--

DROP TABLE IF EXISTS `biblio_topic`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `biblio_topic` (
  `biblio_id` int(11) NOT NULL DEFAULT 0,
  `topic_id` int(11) NOT NULL DEFAULT 0,
  `level` int(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`biblio_id`,`topic_id`),
  KEY `biblio_id` (`biblio_id`),
  KEY `topic_id` (`topic_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `biblio_topic`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `biblio_topic` WRITE;
/*!40000 ALTER TABLE `biblio_topic` DISABLE KEYS */;
INSERT INTO `biblio_topic` VALUES
(1,1,1),
(1,2,2),
(2,3,1),
(2,4,2),
(2,5,2),
(3,1,1),
(3,6,2),
(3,7,2),
(4,4,1),
(4,8,2),
(5,8,1),
(5,9,2),
(6,1,1),
(6,7,2),
(7,2,1),
(7,10,2),
(8,1,1),
(8,2,2),
(9,11,1),
(9,12,2),
(9,13,2),
(10,11,1),
(10,14,2),
(12,15,1),
(12,16,2),
(13,15,1),
(14,15,1),
(15,15,1),
(15,17,2),
(16,19,1),
(16,18,1),
(16,25,1),
(17,22,1),
(17,18,1),
(18,21,1),
(18,18,1),
(19,22,1),
(19,18,1),
(20,21,1),
(20,18,1),
(20,25,1),
(21,19,1),
(21,18,1),
(22,19,1),
(22,18,1),
(22,25,1),
(23,19,1),
(23,18,1),
(24,22,1),
(24,18,1),
(25,19,1),
(25,26,1),
(26,19,1),
(26,18,1),
(27,19,1),
(27,18,1),
(28,19,1),
(28,18,1),
(29,19,1),
(29,18,1),
(30,22,1),
(30,18,1),
(31,20,1),
(31,25,1),
(31,18,1),
(32,20,1),
(32,18,1),
(33,20,1),
(33,18,1),
(34,20,1),
(34,36,1),
(35,21,1),
(35,25,1),
(35,18,1),
(36,23,1),
(36,24,1),
(37,23,1),
(37,24,1),
(38,24,1),
(38,23,1),
(39,24,1),
(40,35,1),
(40,24,1),
(41,35,1),
(41,24,1),
(42,35,1),
(42,24,1),
(43,24,1),
(43,25,1),
(44,24,1),
(45,24,1),
(45,25,1),
(46,23,1),
(46,24,1),
(47,29,1),
(48,34,1),
(49,26,1),
(50,28,1),
(51,30,1),
(52,27,1),
(53,32,1),
(54,32,1),
(55,32,1),
(55,34,1),
(56,33,1),
(56,26,1),
(57,27,1),
(58,26,1),
(59,31,1),
(60,30,1),
(61,38,1),
(61,26,1),
(62,38,1),
(62,20,1),
(63,34,1),
(63,19,1),
(64,19,1),
(64,25,1),
(65,19,1),
(65,25,1),
(66,38,1),
(67,38,1),
(67,26,1),
(68,37,1),
(69,38,1);
/*!40000 ALTER TABLE `biblio_topic` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `name` varchar(64) NOT NULL,
  `contents` text NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `expired_at` datetime DEFAULT NULL,
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `comment`
--

DROP TABLE IF EXISTS `comment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `comment` (
  `comment_id` int(11) NOT NULL AUTO_INCREMENT,
  `biblio_id` int(11) NOT NULL,
  `member_id` varchar(20) NOT NULL,
  `comment` text NOT NULL,
  `input_date` datetime DEFAULT NULL,
  `last_update` datetime DEFAULT NULL,
  PRIMARY KEY (`comment_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comment`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `comment` WRITE;
/*!40000 ALTER TABLE `comment` DISABLE KEYS */;
/*!40000 ALTER TABLE `comment` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `content`
--

DROP TABLE IF EXISTS `content`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `content` (
  `content_id` int(11) NOT NULL AUTO_INCREMENT,
  `content_title` varchar(255) NOT NULL,
  `content_desc` text NOT NULL,
  `content_path` varchar(20) NOT NULL,
  `is_news` smallint(1) DEFAULT NULL,
  `is_draft` smallint(1) DEFAULT 0,
  `publish_date` date DEFAULT NULL,
  `input_date` datetime NOT NULL,
  `last_update` datetime NOT NULL,
  `content_ownpage` enum('1','2') NOT NULL DEFAULT '1',
  PRIMARY KEY (`content_id`),
  UNIQUE KEY `content_path` (`content_path`),
  FULLTEXT KEY `content_title` (`content_title`),
  FULLTEXT KEY `content_desc` (`content_desc`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `content`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `content` WRITE;
/*!40000 ALTER TABLE `content` DISABLE KEYS */;
INSERT INTO `content` VALUES
(1,'Library Information','<h3>Contact Information</h3>\r\n<p><strong>Address :</strong> <br /> Jenderal Sudirman Road, Senayan, Jakarta, Indonesia - Postal Code : 10270 <br /> <strong>Phone Number :</strong> <br /> (021) 5711144 <br /> <strong>Fax Number :</strong> <br /> (021) 5711144</p>\r\n<h3>Opening Hours</h3>\r\n<p><strong>Monday - Friday :</strong> <br /> Open : 08.00 AM<br /> Break : 12.00 - 13.00 PM<br /> Close : 20.00 PM <br /> <strong>Saturday  :</strong> <br /> Open : 08.00 AM<br /> Break : 12.00 - 13.00 PM<br /> Close : 17.00 PM</p>\r\n<h3>Collections</h3>\r\n<p>We have many types of collections in our library, range from Fictions to Sciences Material, from printed material to digital collections such CD-ROM, CD, VCD and DVD. We also collect daily serials publications such as newspaper and also monthly serials such as magazines.</p>\r\n<h3>Library Membership</h3>\r\n<p>To be able to loan our library collections, you must first become library member. There is terms and conditions that you must obey.</p>','libinfo',NULL,0,NULL,'2009-09-13 19:48:16','2009-09-13 19:48:16','1'),
(2,'Help On Usage','<h3>Searching</h3>\r\n<p>There is 2 method available on searching library catalog. The first one is <strong>SIMPLE SEARCH</strong>, which is the simplest method on searching catalog, you just enter any keyword, either it contained in document titles, authors name or subjects. You can supply more than one keywords in Simple Search method and it will expanding your search results.</p>\r\n<p>&nbsp;</p>\r\n<p><strong>ADVANCED SEARCH</strong>, lets you define keywords in more specific fields. If you want your keywords only contained in title field, then type your keyword in Title field and the system will scope it search only on <strong>Title</strong> field, not in other fields. Location field lets you narrowing search results by specific location, so only collection that exists in selected location get fetched by system.</p>','help',NULL,0,NULL,'2009-09-13 19:48:16','2009-09-13 19:48:16','1'),
(3,'Welcome To Admin Page','<div class=\"container admin_home\">\r\n<div class=\"row\">\r\n<div class=\"col-xs-6 col-md-4\">\r\n<h3>Bibliography</h3>\r\n<div class=\"row\">\r\n<div class=\"col-sm-2\"><a class=\"icon biblioIcon notAJAX\" href=\"index.php?mod=bibliography\"></a></div>\r\n<div class=\"col-sm-8\">The Bibliography module lets you manage your library bibliographical data. It also include collection items management to manage a copies of your library collection so it can be used in library circulation.</div>\r\n</div>\r\n</div>\r\n<div class=\"col-xs-6 col-md-4\">\r\n<h3>Circulation</h3>\r\n<div class=\"row\">\r\n<div class=\"col-sm-2\"><a class=\"icon circulationIcon notAJAX\" href=\"index.php?mod=circulation\"></a></div>\r\n<div class=\"col-sm-8\">The Circulation module is used for doing library circulation transaction such as collection loans and return. In this module you can also create loan rules that will be used in loan transaction proccess.</div>\r\n</div>\r\n</div>\r\n<div class=\"col-xs-6 col-md-4\">\r\n<h3>Membership</h3>\r\n<div class=\"row\">\r\n<div class=\"col-sm-2\"><a class=\"icon memberIcon notAJAX\" href=\"index.php?mod=membership\"></a></div>\r\n<div class=\"col-sm-8\">The Membership module lets you manage library members such adding, updating and also removing. You can also manage membership type in this module.</div>\r\n</div>\r\n</div>\r\n</div>\r\n<div class=\"row\">\r\n<div class=\"col-xs-6 col-md-4\">\r\n<h3>Stock Take</h3>\r\n<div class=\"row\">\r\n<div class=\"col-sm-2\"><a class=\"icon stockTakeIcon notAJAX\" href=\"index.php?mod=stock_take\"></a></div>\r\n<div class=\"col-sm-8\">The Stock Take module is the easy way to do Stock Opname for your library collections. Follow several steps that ease your pain in Stock Opname proccess.</div>\r\n</div>\r\n</div>\r\n<div class=\"col-xs-6 col-md-4\">\r\n<h3>Serial Control</h3>\r\n<div class=\"row\">\r\n<div class=\"col-sm-2\"><a class=\"icon serialIcon notAJAX\" href=\"index.php?mod=serial_control\"></a></div>\r\n<div class=\"col-sm-8\">Serial Control module help you manage library\'s serial publication subscription. You can track issues for each subscription.</div>\r\n</div>\r\n</div>\r\n<div class=\"col-xs-6 col-md-4\">\r\n<h3>Reporting</h3>\r\n<div class=\"row\">\r\n<div class=\"col-sm-2\"><a class=\"icon reportIcon notAJAX\" href=\"index.php?mod=reporting\"></a></div>\r\n<div class=\"col-sm-8\">Reporting lets you view various type of reports regardings membership data, circulation data and bibliographic data. All compiled on-the-fly from current library database.</div>\r\n</div>\r\n</div>\r\n</div>\r\n<div class=\"row\">\r\n<div class=\"col-xs-6 col-md-4\">\r\n<h3>Master File</h3>\r\n<div class=\"row\">\r\n<div class=\"col-sm-2\"><a class=\"icon masterFileIcon notAJAX\" href=\"index.php?mod=master_file\"></a></div>\r\n<div class=\"col-sm-8\">The Master File modules lets you manage referential data that will be used by another modules. It include Authority File management such as Authority, Subject/Topic List, GMD and other data.</div>\r\n</div>\r\n</div>\r\n<div class=\"col-xs-6 col-md-4\">\r\n<h3>System</h3>\r\n<div class=\"row\">\r\n<div class=\"col-sm-2\"><a class=\"icon systemIcon notAJAX\" href=\"index.php?mod=system\"></a></div>\r\n<div class=\"col-sm-8\">The System module is used to configure application globally, manage index, manage librarian, and also backup database.</div>\r\n</div>\r\n</div>\r\n</div>\r\n</div>','adminhome',NULL,0,NULL,'2009-09-13 19:48:16','2009-09-13 22:02:11','1'),
(4,'Homepage Info','<p>Welcome To <strong>Senayan Library\'s</strong> Online Public Access Catalog (OPAC). Use OPAC to search collection in our library.</p>','headerinfo',NULL,0,NULL,'2009-09-13 19:48:16','2009-09-13 19:48:16','1'),
(5,'Tentang SLiMS','<p><!--intro_awal--><strong>SLiMS</strong> adalah akronim dari Senayan Library Management System. Awalnya dikembangkan oleh Perpustakaan Kementerian Pendidikan Nasional untuk menggantikan Alice (http://www2.softlinkint.com). Tujuan utamanya agar Perpustakaan Kemdiknas mempunyai kebebasan untuk menggunakan, mempelajari, memodifikasi dan mendistribusikan perangkat lunak yang digunakan. SLiMS, maka dirilis dengan lisensi GPL dan sekarang pengembangan SLiMS dilakukan oleh komunitas penggunanya.<!--intro_akhir--></p>\r\n<p><strong>Asal Mula</strong></p>\r\n<p>Setelah beroperasi 50 tahun lebih, karena beberapa alasan Perpustakaan BC Indonesia yang telah selama bertahun-tahun menjadi andalan layanan BC di Indonesia harus ditutup. Pengelola BC Indonesia kemudian berinisiatif untuk menghibahkan pengelolaan aset perpustakaanya ke tangan institusi pemerintah. Dalam hal ini, institusi pemerintah yang dianggap sesuai bidangnya dan strategis tempatnya, adalah Departemen Pendidikan Nasional (Depdiknas). Yang dihibahkan tidak hanya koleksi, tetapi juga rak koleksi, hardware (server dan workstation) serta sistem termasuk untuk aplikasi manajemen administrasi perpustakaan (Alice).</p>\r\n<p>Seiring dengan berjalannya waktu, manajemen Perpustakaan Depdiknas mulai menghadapi beberapa kendala dalam penggunaan sistem Alice. Pertama, keterbatasan dalam menambahkan fitur-fitur baru. Antara lain: kebutuhan manajemen serial, meng-online-kan katalog di web dan kustomisasi report yang sering berubah-ubah kebutuhannya. Penambahan fitur jika harus meminta modul resmi dari developer Alice, berarti membutuhkan dana tambahan yang tidak kecil. Apalagi tidak ada distributor resminya di Indonesia sehingga harus mengharapkan support dari Inggris. Ditambah lagi beberapa persyaratan yang membutuhkan infrastruktur biaya mahal seperti dedicated public IP agar bisa meng-online-kan Alice di web.<br /><br />Saat itu untuk mengatasi sebagian kebutuhan (utamanya kustomisasi report), dilakukan dengan ujicoba mengakses langsung database yang disimpan dalam format DBase. Terkadang berhasil terkadang tidak karena struktur datanya proprietary dan kompleks serta jumlah rekodnya banyak. Untuk mempelajari struktur database, dicoba melakukan kontak via email ke developer Alice. Tetapi tidak ada respon sama sekali. Disini muncul masalah kedua. Sulitnya mempelajari lebih mendalam cara kerja perangkat lunak Alice. Karena Alice merupakan sistem proprietary yang serba tertutup, segala sesuatunya sangat tergantung vendor. Dibutuhkan sejumlah uang untuk mendapatkan layanan resmi untuk kustomisasi.<br /><br />Perpustakaan Depdiknas salah satu tupoksinya adalah melakukan koordinasi pengelolaan perpustakaan unit kerja dibawah lingkungan Depdiknas. Dalam implementasinya, seringkali muncul kebutuhan untuk bisa mendistribusikan perangkat lunak sistem perpustakaan ke berbagai unit kerja tersebut. Disini masalah ketiga: sulit (atau tidak mungkin) untuk melakukan redistribusi sistem Alice. Alice merupakan perangkat lunak yang secara lisensi tidak memungkinkan diredistribusi oleh pengelola Perpustakaan Depdiknas secara bebas. Semuanya harus ijin dan membutuhkan biaya.<br /><br />November 2006, perpustakaan dihadapkan oleh sebuah masalah mendasar. Sistem Alice tiba-tiba tidak bisa digunakan. Ternyata Alice yang digunakan selama ini diimplementasikan dengan sistem sewa. Pantas saja biayanya relatif murah. Tiap tahun pengguna harus membayar kembali untuk memperpanjang masa sewa pakainya. Tetapi yang mengkhawatirkan adalah fakta bahwa perpustakaan harus menyimpan semua informasi penting dan kritikal di sebuah sistem yang tidak pernah dimiliki. Yang kalau lupa atau tidak mau membayar sewa lagi, hilanglah akses terhadap data kita sendiri. Konyol sekali. Itu sama saja dengan bunuh diri kalau masih tergantung dengan sistem berlisensi seperti itu.<br /><br />Akhirnya pengelola Perpustakaan Depdiknas me-review kembali penggunaan sistem Alice di perpustakaan Depdiknas. Beberapa poin pentingnya antara lain:<br />&bull;&nbsp;&nbsp;&nbsp; Alice memang handal (reliable), tapi punya banyak keterbatasan. Biaya sewanya memang relatif murah, tetapi kalau membutuhkan support tambahan, baik sederhana ataupun kompleks, sangat tergantung dengan developer Alice yang berpusat di Inggris. Butuh biaya yang kalau di total juga tidak murah.<br />&bull;&nbsp;&nbsp;&nbsp; Model lisensi proprietary yang digunakan developer Alice tidak cocok dengan kondisi kebanyakan perpustakaan di Indonesia. Padahal pengelola Perpustakaan Depdiknas sebagai koordinator banyak perpustakaan di lingkungan Depdiknas, punya kepentingan untuk bisa dengan bebas melakukan banyak hal terhadap software yang digunakan.<br />&bull;&nbsp;&nbsp;&nbsp; Menyimpan data penting dan kritikal untuk operasional perpustakaan di suatu software yang proprietary dan menggunakan sistem sewa, dianggap sesuatu yang konyol dan mengancam independensi dan keberlangsungan perpustakaan itu sendiri.<br />&bull;&nbsp;&nbsp;&nbsp; Alice berjalan diatas sistem operasi Windows yang juga proprietary padahal pengelola Perpustakaan Depdiknas ingin beralih menggunakan Sistem Operasi open source (seperti GNU/Linux dan FreeBSD).<br />&bull;&nbsp;&nbsp;&nbsp; Masalah devisa negara yang terbuang untuk membayar software yang tidak pernah dimiliki.<br />&bull;&nbsp;&nbsp;&nbsp; Intinya: pengelola Perpustakaan Depdiknas ingin menggunakan software yang memberikan dan menjamin kebebasan untuk: menggunakan, mempelajari, memodifikasi dan melakukan redistribusi. Lisensi Alice tidak memungkinkan untuk itu.<br /><br />Setelah memutuskan untuk hijrah menggunakan sistem yang lain, maka langkah berikutnya adalah mencari sistem yang ada untuk digunakan atau mengembangkan sendiri sistem yang dibutuhkan. Beberapa pertimbangan yang harus dipenuhi:<br />&bull;&nbsp;&nbsp;&nbsp; Dirilis dibawah lisensi yang menjamin kebebasan untuk: menggunakan, mempelajari, memodifikasi dan melakukan redistribusi. Model lisensi open source (www.openosurce.org) dianggap sebagai model yang paling ideal dan sesuai.<br />&bull;&nbsp;&nbsp;&nbsp; Teknologi yang digunakan untuk membangun sistem juga harus berlisensi open source.<br />&bull;&nbsp;&nbsp;&nbsp; Teknologi yang digunakan haruslah teknologi yang relatif mudah dipelajari oleh pengelola perpustakaan Depdiknas yang berlatarbelakang pendidiknas pustakawan, seperti PHP (scripting language) dan MySQL (database). Jika tidak menguasai sisi teknis teknologi, maka akan terjebak kembali terhadap ketergantungan pada developer.<br /><br />Langkah berikutnya adalah melakukan banding software sistem perpustakaan open source yang bisa diperoleh di internet. Beberapa software yang dicoba antara lain: phpMyLibrary, OpenBiblio, KOHA, EverGreen. Pengelola perpustakaan Depdiknas merasa tidak cocok dengan software yang ada, dengan beberapa alasan:<br />&bull;&nbsp;&nbsp;&nbsp; Desain aplikasi dan database yang tidak baik atau kurang menerapkan secara serius prinsip-prinsip pengembangan aplikasi dan database yang baik sesuai dengan teori yang ada (PHPMyLibrary, OpenBiblio).<br />&bull;&nbsp;&nbsp;&nbsp; Menggunakan teknologi yang sulit dikuasai oleh pengelola perpustakaan Depdiknas (KOHA dan EverGreen dikembangkan menggunakan Perl dan C++ Language yang relatif lebih sulit dipelajari).<br />&bull;&nbsp;&nbsp;&nbsp; Beberapa sudah tidak aktif atau lama sekali tidak di rilis versi terbarunya (PHPMyLibrary dan OpenBiblio).<br /><br />Karena tidak menemukan sistem yang dibutuhkan, maka diputuskan untuk mengembangkan sendiri aplikasi sistem perpustakaan yang dibutuhkan. Dalam dunia pengembangan software, salah satu best practice-nya adalah memberikan nama kode (codename) pengembangan. Nama kode berbeda dengan nama aplikasinya itu sendiri. Nama kode biasanya berbeda-beda tiap versi. Misalnya kode nama &ldquo;Hardy Heron&rdquo; untuk Ubuntu Linux 8.04 dan &ldquo;Jaunty Jackalope&rdquo; untuk Ubuntu Linux 9.04. Pengelola perpustakaan Depdiknas Untuk versi awal (1.0) aplikasi yang akan dikembangkan, memberikan nama kode &ldquo;Senayan&rdquo;. Alasannya sederhana, karena awal dikembangkan di perpustakaan Depdiknas yang berlokasi di Senayan. Apalagi Perpustakaan Depdiknas mempunyai brand sebagai library@senayan. Belakangan karena dirasa nama &ldquo;Senayan&rdquo; dirasa cocok dan punya nilai marketing yang bagus, maka nama &ldquo;Senayan&rdquo; dijadikan nama resmi aplikasi sistem perpustakaan yang dikembangkan.<br /><br />Mengembangkan Senayan<br /><br />Sebelum mulai mengembangkan Senayan, ada beberapa keputusan desain aplikasi yang harus dibuat. Aspek desain ini penting diantaranya untuk pengambilankeputusan dari berbagai masukan yang datang dari komunitas. Antara lain:<br /><br />Pertama,&nbsp; Senayan adalah aplikasi untuk kebutuhan administrasi dan konten perpustakaan (Library Automation System). Senayan didesain untuk kebutuhan skala menengah maupun besar. Cocok untuk perpustakaan yang memiliki koleksi, anggota dan staf banyak di lingkungan jaringan, baik itu lokal (intranet) dan internet.<br /><br />Kedua, Senayan dibangun dengan memperhatikan best practice dalam pengembangan software seperti dalam hal penulisan source code, dokumentasi, dan desain database.<br /><br />Ketiga, Senayan dirancang untuk compliant dengan standar pengelolaan koleksi di perpustakaan. Untuk standar pengatalogan minimal memenuhi syarat AACR 2 level 2 (Anglo-American Cataloging Rules). Kebutuhan untuk kesesuaian dengan standar di perpustakaan terus berkembang dan pengelola perpustakaan Depdiknas dan developer Senayan berkomitmen untuk terus mengembangkan Senayan agar mengikuti standar-standar tersebut.<br /><br />Keempat, Senayan didesain agar bisa juga menjadi middleware bagi aplikasi lain untuk menggunakan data yang ada didalam Senayan. Untuk itu Senayan akan menyediakan API (application programming Interface) yang berbasis web service.<br /><br />Kelima, Senayan merupakan aplikasi yang cross-platform, baik dari sisi aplikasinya itu sendiri dan akses terhadap aplikasi. Untuk itu basis yang paling tepat ada basis web.<br /><br />Keenam, teknologi yang digunakan untuk membangun Senayan, haruslah terbukti bisa diinstall di banyak platform sistem operasi, berlisensi open source dan mudah dipelajari oleh pengelola perpustakaan Depdiknas. Diputuskan untuk menggunakan PHP (www.php.net) untuk web scripting languange dan MySQL (www.mysql.com) untuk server database.<br /><br />Ketujuh, diputuskan untuk mengembangkan library PHP sendiri yang didesain spesifik untuk kebutuhan membangun library automation system. Tidak menggunakan library PHP yang sudah terkenal seperti PEAR (pear.php.net) karena alasan penguasaan terhadap teknologi dan kesederhanaan. Library tersebut diberinama &ldquo;simbio&rdquo;.<br /><br />Kedelapan, untuk mempercepat proses pengembangan, beberapa modul atau fungsi yang dibutuhkan yang dirasa terlalu lama dan rumit untuk dikembangkan sendiri, akan menggunakan software open source yang berlisensi open source juga. Misalnya: flowplayer untuk dukungan multimedia, jQuery untuk dukungan AJAX (Asynchronous Javascript and XML), genbarcode untuk dukungan pembuatan barcode, PHPThumb untuk dukungan generate image on-the-fly, tinyMCE untuk web-based text editor, dan lain-lain.<br /><br />Kesembilan, untuk menjaga spirit open source, proses pengembangan Senayan dilakukan dengan infrastruktur yang berbasis open source. Misalnya: server web menggunakan Apache, server produksi menggunakan OS Linux Centos dan OpenSuse, para developer melakukan pengembangan dengan OS Ubuntu Linux, manajemen source code menggunakan software git, dan lain-lain.<br /><br />Kesepuluh, Senayan dirilis ke masyarakat umum dengan lisensi GNU/GPL versi 3 yang menjamin kebebasan penggunanya untuk mempelajari, menggunakan, memodifikasi dan redistribusi Senayan.<br /><br />Kesebelas, para developer dan pengelola perpustakaan Depdiknas berkomitmen untuk terus mengembangkan Senayan dan menjadikannya salah satu contoh software perpustakaan yang open source, berbasis di indonesia dan menjadi salah satu contoh bagi model pengembangan open source yang terbukti berjalan dengan baik.<br /><br />Keduabelas, model pengembangan Senayan adalah open source yang artinya setiap orang dipersilahkan memberikan kontribusinya. Baik dari sisi pemrogaman, template, dokumentasi, dan lain-lain. Tentu saja ada mekanisme mana kontribusi yang bagus untuk dimasukkan dalam rilis resmi, mana yang tidak. Mengacu ke dokumen &hellip; (TAMBAHKAN DENGAN TULISAN ERIC S RAYMOND)<br /><br />Model pengembangan senayan<br /><br />Pengembangan Senayan awalnya diinisiasi oleh pengelola Perpustakaan Depdiknas. Tetapi sekarang komunitas pengembang Senayan (Senayan Developer Community) yang lebih banyak mengambil peran dalam mengembangkan Senayan. Beberapa hal dibawah ini merupakan kultur yang dibangun dalam mengembangkan Senayan:<br />1.&nbsp;&nbsp;&nbsp; Meritokrasi. Siapa saja bisa berkontribusi. Mereka yang banyak memberikan kontribusi, akan mendapatkan privilege lebih dibandingkan yang lain.<br />2.&nbsp;&nbsp;&nbsp; Minimal punya concern terhadap pengembangan perpustakaan. Contoh lain: berlatar belakang pendidikan ilmu perpustakaan dan informasi, bekerja di perpustakaan, mengelola perpustakaan, dan lain-lain. Diharapkan dengan kondisi ini, sense of librarianship melekat di tiap developer/pengguna Senayan. Sejauh ini, semua developer senayan merupakan pustakawan atau berlatarbelakang pendidikan kepustakawanan (Information and Librarianship).<br />3.&nbsp;&nbsp;&nbsp; Release early, release often, and listen to your customer. Release early artinya setiap perbaikan dan penambahan fitur, secepat mungkin dirilis ke publik. Diharapkan bugs yang ada, bisa cepat ditemukan oleh komunitas, dilaporkan ke developer, untuk kemudian dirilis perbaikannya. Release often, artinya sesering mungkin memberikan update perbaikan bugs dan penambahan fitur. Ini &ldquo;memaksa&rdquo; developer Senayan untuk terus kreatif menambahkan fitur Senayan. Release often juga membuat pengguna berkeyakinan bahwa Senayan punya sustainability yang baik dan terus aktif dikembangkan. Selain itu, release often juga mempunyai dampak pemasaran. Pengguna dan calon pengguna, selalu diingatkan tentang keberadaan Senayan. Tentunya dengan cara yang elegan, yaitu rilis-rilis Senayan. Sejak dirilis ke publi pertama kali November 2007 sampai Juli 2009 (kurang lebih 20 bulan) telah dirilis 18 rilis resmi Senayan. Listen to your customer. Developer Senayan selalu berusaha mengakomodasi kebutuhan pengguna baik yang masuk melalui report di mailing list, ataupun melalui bugs tracking system. Tentu tidak semua masukan diakomodasi, harus disesuaikan dengan desain dan roadmap pengembangan Senayan.<br />4.&nbsp;&nbsp;&nbsp; Dokumentasi. Developer Senayan meyakini pentingnya dokumentasi yang baik dalam mensukseskan implementasi Senayan dibanyak tempat. Karena itu pengembang Senayan mempunyai tim khusus yang bertanggungjawab yang mengembangkan dokumentasi Senayan agar terus uo-to-date mengikuti rilis terbaru.<br />5.&nbsp;&nbsp;&nbsp; Agar ada percepatan dalam pengembangan dan untuk mengakrabkan antar pengembang Senayan, minimal setahun sekali diadakan Senayan Developers Day yang mengumpulkan para developer Senayan dari berbagai kota, dan melakukan coding bersama-sama.<br />Fitur Senayan<br />Sebagai sebuah Sistem Automasi Perpustakaan yang terintergrasi, modul-modul yang telah terdapat di SENAYAN adalah sebagai berikut:<br />Modul Pengatalogan (Cataloging Module)<br />1)&nbsp;&nbsp;&nbsp; Compliance dengan standar AACR2 (Anglo-American Cataloging Rules).<br />2)&nbsp;&nbsp;&nbsp; Fitur untuk membuat, mengedit, dan menghapus data bibliografi sesuai dengan standar deskripsi bibliografi AACR2 level ke dua.<br />3)&nbsp;&nbsp;&nbsp; Mendukung pengelolaan koleksi dalam berbagai macam format seperti monograph, terbitan berseri, audio visual, dsb.<br />4)&nbsp;&nbsp;&nbsp; Mendukung penyimpanan data bibliografi dari situs di Internet.<br />5)&nbsp;&nbsp;&nbsp; Mendukung penggunaan Barcode.<br />6)&nbsp;&nbsp;&nbsp; Manajemen item koleksi untuk dokumen dengan banyak kopi dan format yang berbeda.<br />7)&nbsp;&nbsp;&nbsp; Mendukung format XML untuk pertukaran data dengan menggunakan standar metadata MODS (Metadata Object Description Schema).<br />8)&nbsp;&nbsp;&nbsp; Pencetakan Barcode item/kopi koleksi Built-in.<br />9)&nbsp;&nbsp;&nbsp; Pencetakan Label Punggung koleksi Built-in.<br />10)&nbsp;&nbsp;&nbsp; Pengambilan data katalog melalui protokol Z3950 ke database koleksi Library of Congress.<br />11)&nbsp;&nbsp;&nbsp; Pengelolaan koleksi yang hilang, dalam perbaikan, dan rusak serta pencatatan statusnya untuk dilakukan pergantian/perbaikan terhadap koleksi.<br />12)&nbsp;&nbsp;&nbsp; Daftar kendali untuk pengarang (baik pengarang orang, badan/lembaga, dan pertemuan) sebagai standar konsistensi penuliasn<br />13)&nbsp;&nbsp;&nbsp; Pengaturan hak akses pengelolaan data bibliografi hanya untuk staf yang berhak.<br /><br />Modul Penelusuran (OPAC/Online Public Access catalog Module)<br />1)&nbsp;&nbsp;&nbsp; Pencarian sederhana.<br />2)&nbsp;&nbsp;&nbsp; Pencarian tingkat lanjut (Advanced).<br />3)&nbsp;&nbsp;&nbsp; Dukungan penggunaan Boolean\'s Logic dan implementasi CQL (Common Query Language).<br />4)&nbsp;&nbsp;&nbsp; OPAC Web Services berbasis XML.<br />5)&nbsp;&nbsp;&nbsp; Mendukung akses OPAC melalui peralatan portabel (mobile device)<br />6)&nbsp;&nbsp;&nbsp; Menampilkan informasi lengkap tetang status koleksi di perpustakaan, tanggal pengembalian, dan pemesanan item/koleksi<br />7)&nbsp;&nbsp;&nbsp; Detil informasi juga menampilkan gambar sampul buku, lampiran dalam format elektronik yang tersedia (jika ada) serta fasilitas menampilkan koleksi audio dan visual.<br />8)&nbsp;&nbsp;&nbsp; Menyediakan hyperlink tambahan untuk pencarian lanjutan berdasarkan penulis, dan subjek.<br /><br />Modul Sirkulasi (Circulation Module)<br />1)&nbsp;&nbsp;&nbsp; Mampu memproses peminjaman dan pengembalian koleksi secara efisien, efektif dan aman.<br />2)&nbsp;&nbsp;&nbsp; Mendukung fitur reservasi koleksi yang sedang dipinjam, termasuk reminder/pemberitahuan-nya.<br />3)&nbsp;&nbsp;&nbsp; Mendukung fitur manajemen denda. Dilengkapi fleksibilitas untuk pemakai membayar denda secara cicilan.<br />4)&nbsp;&nbsp;&nbsp; Mendukung fitur reminder untuk berbagai keperluan seperti melakukan black list terhadap pemakai yang bermasalah atau habis keanggotaannya.<br />5)&nbsp;&nbsp;&nbsp; Mendukung fitur pengkalenderan (calendaring) untuk diintegrasikan dengan penghitungan masa peminjaman, denda, dan lain-lain.<br />6)&nbsp;&nbsp;&nbsp; Memungkinkan penentuan hari-hari libur non-standar yang spesifik.<br />7)&nbsp;&nbsp;&nbsp; Dukungan terhadap ragam jenis tipe pemakai dengan masa pinjam beragam untuk berbagai jenis keanggotaan.<br />8)&nbsp;&nbsp;&nbsp; Menyimpan histori peminjaman anggota.<br />9)&nbsp;&nbsp;&nbsp; Mendukung pembuatan peraturan peminjaman yang sangat rinci dengan mengkombinasikan parameter keanggotaan, jenis koleksi, dan gmd selain aturan peminjaman standar berdasarkan jenis keanggotaan<br /><br />Modul Manajemen Keanggotaan (Membership Management Module)<br />1)&nbsp;&nbsp;&nbsp; Memungkinkan beragam tipe pemakai dengan ragam jenis kategori peminjaman, ragam jenis keanggotaan dan pembedaan setiap layanan sirkulasi dalam jumlah koleksi serta lama peminjaman untuk jenis koleksi untuk setiap jenis/kategori.<br />2)&nbsp;&nbsp;&nbsp; Dukungan terhadap input menggunakan barcode reader<br />3)&nbsp;&nbsp;&nbsp; Memungkinkan untuk menyimpan informasi preferensi pemakai atau subject interest.<br />4)&nbsp;&nbsp;&nbsp; Memungkinkan untuk menyimpan informasi tambahan untuk keperluan reminder pada saat transaksi.<br />5)&nbsp;&nbsp;&nbsp; Memungkinkan menyimpan informasi detail pemakai yang lebih lengkap.<br />6)&nbsp;&nbsp;&nbsp; Pencarian informasi anggota minimal berdasarkan nomor dan nama anggota.<br />7)&nbsp;&nbsp;&nbsp; Pembuatan kartu anggota yang dilengkapi dengan barcode untuk transaksi peminjaman.<br /><br />Modul Inventarisasi Koleksi (Stocktaking Module)<br />1)&nbsp;&nbsp;&nbsp; Proses inventarisasi koleksi bisa dilakukan secara bertahap dan parsial tanpa harus menutup layanan perpustakaan secara keseluruhan.<br />2)&nbsp;&nbsp;&nbsp; Proses inventarisasi bisa dilakukan secara efisien dan efektif.<br />3)&nbsp;&nbsp;&nbsp; Terdapat pilihan untuk menghapus data secara otomatis pada saat akhir proses inventarisasi terhadap koleksi yang dianggap hilang.<br /><br />Modul Statistik/Pelaporan (Report Module)<br />1)&nbsp;&nbsp;&nbsp; Meliputi pelaporan untuk semua modul-modul yang tersedia di Senayan.<br />2)&nbsp;&nbsp;&nbsp; Laporan Judul.<br />3)&nbsp;&nbsp;&nbsp; Laporan Items/Kopi koleksi.<br />4)&nbsp;&nbsp;&nbsp; Laporan Keanggotaan.<br />5)&nbsp;&nbsp;&nbsp; Laporan jumlah koleksi berdasarkan klasifikasi.<br />6)&nbsp;&nbsp;&nbsp; Laporan Keterlambatan.<br />7)&nbsp;&nbsp;&nbsp; Berbagai macam statistik seperti statistik koleksi, peminjaman, keanggotaan, keterpakaian koleksi.<br />8)&nbsp;&nbsp;&nbsp; Tampilan laporan yang sudah didesain printer-friendly, sehingga memudahkan untuk dicetak.<br />9)&nbsp;&nbsp;&nbsp; Filter data yang lengkap untuk setiap laporan.<br />10)&nbsp;&nbsp;&nbsp; API untuk pelaporan yang relatif mudah dipelajari untuk membuat custom report baru.<br /><br />Modul Manajemen Terbitan Berseri (Serial Control)<br />1)&nbsp;&nbsp;&nbsp; Manajemen data langganan.<br />2)&nbsp;&nbsp;&nbsp; Manajemen data Kardex.<br />3)&nbsp;&nbsp;&nbsp; Manajemen tracking data terbitan yang akan terbit dan yang sudah ada.<br />4)&nbsp;&nbsp;&nbsp; Memungkinkan tracking data terbitan berseri yang jadwal terbitnya tidak teratur (pengaturan yang fleksibel).<br /><br />Modul Lain-lain<br />1)&nbsp;&nbsp;&nbsp; Dukungan antar muka yang multi bahasa (internasionalisasi) dengan Gettext.<br />2)&nbsp;&nbsp;&nbsp; Dukungan terhadap penggunaan huruf bukan latin untuk pengisian data dan pencarian.<br /><br />Roadmap Pengembangan Senayan<br />SENAYAN akan terus dikembangkan oleh para pengembangnya beserta komunitas pengguna SENAYAN lainnya. Berikut adalah Roadmap pengembangan SENAYAN ke depannya:<br /><br />Pengembangan aplikasi:<br />1.&nbsp;&nbsp;&nbsp; Kompatibilitas dengan MARC dan standar pertukaran data yang komplit. Contoh implementasinya:<br />&bull;&nbsp;&nbsp;&nbsp; Memastikan bahwa format data bibliografi kompatibel dengan MARC secara lebih baik (minimal MARC light).<br />&bull;&nbsp;&nbsp;&nbsp; Dukungan terhadap RFID.<br />&bull;&nbsp;&nbsp;&nbsp; Fitur untuk impor / ekspor rekod dari The Online Computer Library Centre (OCLC), Research Libraries Information Network (RLIN), vendor sistem lain yang compliant dengan MARC.<br />&bull;&nbsp;&nbsp;&nbsp; Validasi data ISBN menggunakan modulus seven.<br />&bull;&nbsp;&nbsp;&nbsp; Dukungan terhadap standar di perpustakaan, seperti: Library of Congress Subject Headings, Library of Congress Classification, ALA filing rules, International Standard Bibliographic Description, ANSI Standard for Bibliographic Information Exchange on magnetic tape, Common communication format (ISO 2709).<br />2.&nbsp;&nbsp;&nbsp; Katalog induk/bersama (union catalog)<br />3.&nbsp;&nbsp;&nbsp; Implementasi Thesaurus. Contoh implementasinya:<br />&bull;&nbsp;&nbsp;&nbsp; Pemanfaatan tesaurus untuk proses pengatalogan.<br />&bull;&nbsp;&nbsp;&nbsp; Pemanfaatan tesaurus untuk proses pencarian, misalnya memberikan advis pencarian menggunakan knowledge base yang dibangun dengan sistem tesaurus.<br />4.&nbsp;&nbsp;&nbsp; Implementasi Library 2.0. Contoh implementasinya:<br />&bull;&nbsp;&nbsp;&nbsp; User bisa login dan mempunyai halaman personalisasi.<br />&bull;&nbsp;&nbsp;&nbsp; User bisa melakukan reservasi koleksi dan memperpanjang peminjaman.<br />&bull;&nbsp;&nbsp;&nbsp; User bisa melakukan komunikasi dengan pustakawan via messaging system.<br />&bull;&nbsp;&nbsp;&nbsp; User bisa melakukan tagging, rekomendasi koleksi dan menyimpannya didalam daftar koleksi favoritnya.<br />&bull;&nbsp;&nbsp;&nbsp; User bisa memberikan komentar terhadap koleksi.<br />&bull;&nbsp;&nbsp;&nbsp; Pustakawan bisa memasukkan preferensi pemakai didalam data keanggotaan. Preferensi tersebut bisa dimanfaatkan salah satunya untuk men-generate semacam daftar koleksi terpilih untuk dicetak atau ditampilkan ketika user login.<br />5.&nbsp;&nbsp;&nbsp; Peningkatan dukungan manajemen konten digital dan entri analitikal<br /><br />Pengembangan basis komunitas pengguna:<br />&bull;&nbsp;&nbsp;&nbsp; Membangun komunitas pengguna di berbagai kota <br />&bull;&nbsp;&nbsp;&nbsp; Mengadakan Senayan Developers Day untuk silaturahmi antar developer Senayan, update dokumentasi, penambahan fitur baru dan bug fixing dan mencari bibit pengembang yang baru.<br />&bull;&nbsp;&nbsp;&nbsp; Workshop/Seminar Nasional Tahunan<br />&bull;&nbsp;&nbsp;&nbsp; Jam Sessions rutin setiap 3 bulan</p>','about_slims',NULL,0,NULL,'2010-08-28 23:29:55','2010-11-12 18:21:01','1'),
(6,'Modul yang Tersedia','<p><!--intro_awal-->Sebagai sebuah Sistem Automasi Perpustakaan yang terintergrasi, modul-modul yang telah terdapat di SENAYAN antara lain: pengatalogan/bibliografi, keanggotaan, sirkulasi, masterfile, stock opname (inventarisasi koleksi), pelaporan/reporting, manajemen kontrol serial, digital library, dan lain-lain.<!--intro_akhir--></p>\r\n<p>Modul Pengatalogan (Cataloging Module)<br />1)&nbsp;&nbsp;&nbsp; Compliance dengan standar AACR2 (Anglo-American Cataloging Rules).<br />2)&nbsp;&nbsp;&nbsp; Fitur untuk membuat, mengedit, dan menghapus data bibliografi sesuai dengan standar deskripsi bibliografi AACR2 level ke dua.<br />3)&nbsp;&nbsp;&nbsp; Mendukung pengelolaan koleksi dalam berbagai macam format seperti monograph, terbitan berseri, audio visual, dsb.<br />4)&nbsp;&nbsp;&nbsp; Mendukung penyimpanan data bibliografi dari situs di Internet.<br />5)&nbsp;&nbsp;&nbsp; Mendukung penggunaan Barcode.<br />6)&nbsp;&nbsp;&nbsp; Manajemen item koleksi untuk dokumen dengan banyak kopi dan format yang berbeda.<br />7)&nbsp;&nbsp;&nbsp; Mendukung format XML untuk pertukaran data dengan menggunakan standar metadata MODS (Metadata Object Description Schema).<br />8)&nbsp;&nbsp;&nbsp; Pencetakan Barcode item/kopi koleksi Built-in.<br />9)&nbsp;&nbsp;&nbsp; Pencetakan Label Punggung koleksi Built-in.<br />10)&nbsp;&nbsp;&nbsp; Pengambilan data katalog melalui protokol Z3950 ke database koleksi Library of Congress.<br />11)&nbsp;&nbsp;&nbsp; Pengelolaan koleksi yang hilang, dalam perbaikan, dan rusak serta pencatatan statusnya untuk dilakukan pergantian/perbaikan terhadap koleksi.<br />12)&nbsp;&nbsp;&nbsp; Daftar kendali untuk pengarang (baik pengarang orang, badan/lembaga, dan pertemuan) sebagai standar konsistensi penuliasn<br />13)&nbsp;&nbsp;&nbsp; Pengaturan hak akses pengelolaan data bibliografi hanya untuk staf yang berhak.<br /><br />Modul Penelusuran (OPAC/Online Public Access catalog Module)<br />1)&nbsp;&nbsp;&nbsp; Pencarian sederhana.<br />2)&nbsp;&nbsp;&nbsp; Pencarian tingkat lanjut (Advanced).<br />3)&nbsp;&nbsp;&nbsp; Dukungan penggunaan Boolean\'s Logic dan implementasi CQL (Common Query Language).<br />4)&nbsp;&nbsp;&nbsp; OPAC Web Services berbasis XML.<br />5)&nbsp;&nbsp;&nbsp; Mendukung akses OPAC melalui peralatan portabel (mobile device)<br />6)&nbsp;&nbsp;&nbsp; Menampilkan informasi lengkap tetang status koleksi di perpustakaan, tanggal pengembalian, dan pemesanan item/koleksi<br />7)&nbsp;&nbsp;&nbsp; Detil informasi juga menampilkan gambar sampul buku, lampiran dalam format elektronik yang tersedia (jika ada) serta fasilitas menampilkan koleksi audio dan visual.<br />8)&nbsp;&nbsp;&nbsp; Menyediakan hyperlink tambahan untuk pencarian lanjutan berdasarkan penulis, dan subjek.<br /><br />Modul Sirkulasi (Circulation Module)<br />1)&nbsp;&nbsp;&nbsp; Mampu memproses peminjaman dan pengembalian koleksi secara efisien, efektif dan aman.<br />2)&nbsp;&nbsp;&nbsp; Mendukung fitur reservasi koleksi yang sedang dipinjam, termasuk reminder/pemberitahuan-nya.<br />3)&nbsp;&nbsp;&nbsp; Mendukung fitur manajemen denda. Dilengkapi fleksibilitas untuk pemakai membayar denda secara cicilan.<br />4)&nbsp;&nbsp;&nbsp; Mendukung fitur reminder untuk berbagai keperluan seperti melakukan black list terhadap pemakai yang bermasalah atau habis keanggotaannya.<br />5)&nbsp;&nbsp;&nbsp; Mendukung fitur pengkalenderan (calendaring) untuk diintegrasikan dengan penghitungan masa peminjaman, denda, dan lain-lain.<br />6)&nbsp;&nbsp;&nbsp; Memungkinkan penentuan hari-hari libur non-standar yang spesifik.<br />7)&nbsp;&nbsp;&nbsp; Dukungan terhadap ragam jenis tipe pemakai dengan masa pinjam beragam untuk berbagai jenis keanggotaan.<br />8)&nbsp;&nbsp;&nbsp; Menyimpan histori peminjaman anggota.<br />9)&nbsp;&nbsp;&nbsp; Mendukung pembuatan peraturan peminjaman yang sangat rinci dengan mengkombinasikan parameter keanggotaan, jenis koleksi, dan gmd selain aturan peminjaman standar berdasarkan jenis keanggotaan<br /><br />Modul Manajemen Keanggotaan (Membership Management Module)<br />1)&nbsp;&nbsp;&nbsp; Memungkinkan beragam tipe pemakai dengan ragam jenis kategori peminjaman, ragam jenis keanggotaan dan pembedaan setiap layanan sirkulasi dalam jumlah koleksi serta lama peminjaman untuk jenis koleksi untuk setiap jenis/kategori.<br />2)&nbsp;&nbsp;&nbsp; Dukungan terhadap input menggunakan barcode reader<br />3)&nbsp;&nbsp;&nbsp; Memungkinkan untuk menyimpan informasi preferensi pemakai atau subject interest.<br />4)&nbsp;&nbsp;&nbsp; Memungkinkan untuk menyimpan informasi tambahan untuk keperluan reminder pada saat transaksi.<br />5)&nbsp;&nbsp;&nbsp; Memungkinkan menyimpan informasi detail pemakai yang lebih lengkap.<br />6)&nbsp;&nbsp;&nbsp; Pencarian informasi anggota minimal berdasarkan nomor dan nama anggota.<br />7)&nbsp;&nbsp;&nbsp; Pembuatan kartu anggota yang dilengkapi dengan barcode untuk transaksi peminjaman.<br /><br />Modul Inventarisasi Koleksi (Stocktaking Module)<br />1)&nbsp;&nbsp;&nbsp; Proses inventarisasi koleksi bisa dilakukan secara bertahap dan parsial tanpa harus menutup layanan perpustakaan secara keseluruhan.<br />2)&nbsp;&nbsp;&nbsp; Proses inventarisasi bisa dilakukan secara efisien dan efektif.<br />3)&nbsp;&nbsp;&nbsp; Terdapat pilihan untuk menghapus data secara otomatis pada saat akhir proses inventarisasi terhadap koleksi yang dianggap hilang.<br /><br />Modul Statistik/Pelaporan (Report Module)<br />1)&nbsp;&nbsp;&nbsp; Meliputi pelaporan untuk semua modul-modul yang tersedia di Senayan.<br />2)&nbsp;&nbsp;&nbsp; Laporan Judul.<br />3)&nbsp;&nbsp;&nbsp; Laporan Items/Kopi koleksi.<br />4)&nbsp;&nbsp;&nbsp; Laporan Keanggotaan.<br />5)&nbsp;&nbsp;&nbsp; Laporan jumlah koleksi berdasarkan klasifikasi.<br />6)&nbsp;&nbsp;&nbsp; Laporan Keterlambatan.<br />7)&nbsp;&nbsp;&nbsp; Berbagai macam statistik seperti statistik koleksi, peminjaman, keanggotaan, keterpakaian koleksi.<br />8)&nbsp;&nbsp;&nbsp; Tampilan laporan yang sudah didesain printer-friendly, sehingga memudahkan untuk dicetak.<br />9)&nbsp;&nbsp;&nbsp; Filter data yang lengkap untuk setiap laporan.<br />10)&nbsp;&nbsp;&nbsp; API untuk pelaporan yang relatif mudah dipelajari untuk membuat custom report baru.<br /><br />Modul Manajemen Terbitan Berseri (Serial Control)<br />1)&nbsp;&nbsp;&nbsp; Manajemen data langganan.<br />2)&nbsp;&nbsp;&nbsp; Manajemen data Kardex.<br />3)&nbsp;&nbsp;&nbsp; Manajemen tracking data terbitan yang akan terbit dan yang sudah ada.<br />4)&nbsp;&nbsp;&nbsp; Memungkinkan tracking data terbitan berseri yang jadwal terbitnya tidak teratur (pengaturan yang fleksibel).<br /><br />Modul Lain-lain<br />1)&nbsp;&nbsp;&nbsp; Dukungan antar muka yang multi bahasa (internasionalisasi) dengan Gettext.<br />2)&nbsp;&nbsp;&nbsp; Dukungan terhadap penggunaan huruf bukan latin untuk pengisian data dan pencarian.</p>','modul_tersedia',NULL,0,NULL,'2010-08-29 04:03:09','2010-08-29 04:05:49','1'),
(7,'Lisensi SLiMS','<p><!--intro_awal--><strong>SLiMS</strong> dilisensikan dibawah GNU/GPL (http://www.gnu.org/licenses/gpl.html) untuk menjamin kebebasan pengguna dalam menggunakannya. GNU General Public License (disingkat GNU GPL atau cukup GPL) merupakan suatu lisensi perangkat lunak bebas yang aslinya ditulis oleh Richard Stallman untuk proyek GNU. Lisensi GPL memberikan penerima salinan perangkat lunak hak dari perangkat lunak bebas dan menggunakan copyleft&nbsp; untuk memastikan kebebasan yang sama diterapkan pada versi berikutnya dari karya tersebut.<!--intro_akhir--></p>\r\n<p>&nbsp;GNU LESSER GENERAL PUBLIC LICENSE<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Version 3, 29 June 2007<br /><br />&nbsp;Copyright (C) 2007 Free Software Foundation, Inc. &lt;http://fsf.org/&gt;<br />&nbsp;Everyone is permitted to copy and distribute verbatim copies<br />&nbsp;of this license document, but changing it is not allowed.<br /><br /><br />&nbsp; This version of the GNU Lesser General Public License incorporates<br />the terms and conditions of version 3 of the GNU General Public<br />License, supplemented by the additional permissions listed below.<br /><br />&nbsp; 0. Additional Definitions.<br /><br />&nbsp; As used herein, \"this License\" refers to version 3 of the GNU Lesser<br />General Public License, and the \"GNU GPL\" refers to version 3 of the GNU<br />General Public License.<br /><br />&nbsp; \"The Library\" refers to a covered work governed by this License,<br />other than an Application or a Combined Work as defined below.<br /><br />&nbsp; An \"Application\" is any work that makes use of an interface provided<br />by the Library, but which is not otherwise based on the Library.<br />Defining a subclass of a class defined by the Library is deemed a mode<br />of using an interface provided by the Library.<br /><br />&nbsp; A \"Combined Work\" is a work produced by combining or linking an<br />Application with the Library.&nbsp; The particular version of the Library<br />with which the Combined Work was made is also called the \"Linked<br />Version\".<br /><br />&nbsp; The \"Minimal Corresponding Source\" for a Combined Work means the<br />Corresponding Source for the Combined Work, excluding any source code<br />for portions of the Combined Work that, considered in isolation, are<br />based on the Application, and not on the Linked Version.<br /><br />&nbsp; The \"Corresponding Application Code\" for a Combined Work means the<br />object code and/or source code for the Application, including any data<br />and utility programs needed for reproducing the Combined Work from the<br />Application, but excluding the System Libraries of the Combined Work.<br /><br />&nbsp; 1. Exception to Section 3 of the GNU GPL.<br /><br />&nbsp; You may convey a covered work under sections 3 and 4 of this License<br />without being bound by section 3 of the GNU GPL.<br /><br />&nbsp; 2. Conveying Modified Versions.<br /><br />&nbsp; If you modify a copy of the Library, and, in your modifications, a<br />facility refers to a function or data to be supplied by an Application<br />that uses the facility (other than as an argument passed when the<br />facility is invoked), then you may convey a copy of the modified<br />version:<br /><br />&nbsp;&nbsp; a) under this License, provided that you make a good faith effort to<br />&nbsp;&nbsp; ensure that, in the event an Application does not supply the<br />&nbsp;&nbsp; function or data, the facility still operates, and performs<br />&nbsp;&nbsp; whatever part of its purpose remains meaningful, or<br /><br />&nbsp;&nbsp; b) under the GNU GPL, with none of the additional permissions of<br />&nbsp;&nbsp; this License applicable to that copy.<br /><br />&nbsp; 3. Object Code Incorporating Material from Library Header Files.<br /><br />&nbsp; The object code form of an Application may incorporate material from<br />a header file that is part of the Library.&nbsp; You may convey such object<br />code under terms of your choice, provided that, if the incorporated<br />material is not limited to numerical parameters, data structure<br />layouts and accessors, or small macros, inline functions and templates<br />(ten or fewer lines in length), you do both of the following:<br /><br />&nbsp;&nbsp; a) Give prominent notice with each copy of the object code that the<br />&nbsp;&nbsp; Library is used in it and that the Library and its use are<br />&nbsp;&nbsp; covered by this License.<br /><br />&nbsp;&nbsp; b) Accompany the object code with a copy of the GNU GPL and this license<br />&nbsp;&nbsp; document.<br /><br />&nbsp; 4. Combined Works.<br /><br />&nbsp; You may convey a Combined Work under terms of your choice that,<br />taken together, effectively do not restrict modification of the<br />portions of the Library contained in the Combined Work and reverse<br />engineering for debugging such modifications, if you also do each of<br />the following:<br /><br />&nbsp;&nbsp; a) Give prominent notice with each copy of the Combined Work that<br />&nbsp;&nbsp; the Library is used in it and that the Library and its use are<br />&nbsp;&nbsp; covered by this License.<br /><br />&nbsp;&nbsp; b) Accompany the Combined Work with a copy of the GNU GPL and this license<br />&nbsp;&nbsp; document.<br /><br />&nbsp;&nbsp; c) For a Combined Work that displays copyright notices during<br />&nbsp;&nbsp; execution, include the copyright notice for the Library among<br />&nbsp;&nbsp; these notices, as well as a reference directing the user to the<br />&nbsp;&nbsp; copies of the GNU GPL and this license document.<br /><br />&nbsp;&nbsp; d) Do one of the following:<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 0) Convey the Minimal Corresponding Source under the terms of this<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; License, and the Corresponding Application Code in a form<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; suitable for, and under terms that permit, the user to<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; recombine or relink the Application with a modified version of<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; the Linked Version to produce a modified Combined Work, in the<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; manner specified by section 6 of the GNU GPL for conveying<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Corresponding Source.<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 1) Use a suitable shared library mechanism for linking with the<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Library.&nbsp; A suitable mechanism is one that (a) uses at run time<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; a copy of the Library already present on the user\'s computer<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; system, and (b) will operate properly with a modified version<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; of the Library that is interface-compatible with the Linked<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Version.<br /><br />&nbsp;&nbsp; e) Provide Installation Information, but only if you would otherwise<br />&nbsp;&nbsp; be required to provide such information under section 6 of the<br />&nbsp;&nbsp; GNU GPL, and only to the extent that such information is<br />&nbsp;&nbsp; necessary to install and execute a modified version of the<br />&nbsp;&nbsp; Combined Work produced by recombining or relinking the<br />&nbsp;&nbsp; Application with a modified version of the Linked Version. (If<br />&nbsp;&nbsp; you use option 4d0, the Installation Information must accompany<br />&nbsp;&nbsp; the Minimal Corresponding Source and Corresponding Application<br />&nbsp;&nbsp; Code. If you use option 4d1, you must provide the Installation<br />&nbsp;&nbsp; Information in the manner specified by section 6 of the GNU GPL<br />&nbsp;&nbsp; for conveying Corresponding Source.)<br /><br />&nbsp; 5. Combined Libraries.<br /><br />&nbsp; You may place library facilities that are a work based on the<br />Library side by side in a single library together with other library<br />facilities that are not Applications and are not covered by this<br />License, and convey such a combined library under terms of your<br />choice, if you do both of the following:<br /><br />&nbsp;&nbsp; a) Accompany the combined library with a copy of the same work based<br />&nbsp;&nbsp; on the Library, uncombined with any other library facilities,<br />&nbsp;&nbsp; conveyed under the terms of this License.<br /><br />&nbsp;&nbsp; b) Give prominent notice with the combined library that part of it<br />&nbsp;&nbsp; is a work based on the Library, and explaining where to find the<br />&nbsp;&nbsp; accompanying uncombined form of the same work.<br /><br />&nbsp; 6. Revised Versions of the GNU Lesser General Public License.<br /><br />&nbsp; The Free Software Foundation may publish revised and/or new versions<br />of the GNU Lesser General Public License from time to time. Such new<br />versions will be similar in spirit to the present version, but may<br />differ in detail to address new problems or concerns.<br /><br />&nbsp; Each version is given a distinguishing version number. If the<br />Library as you received it specifies that a certain numbered version<br />of the GNU Lesser General Public License \"or any later version\"<br />applies to it, you have the option of following the terms and<br />conditions either of that published version or of any later version<br />published by the Free Software Foundation. If the Library as you<br />received it does not specify a version number of the GNU Lesser<br />General Public License, you may choose any version of the GNU Lesser<br />General Public License ever published by the Free Software Foundation.<br /><br />&nbsp; If the Library as you received it specifies that a proxy can decide<br />whether future versions of the GNU Lesser General Public License shall<br />apply, that proxy\'s public statement of acceptance of any version is<br />permanent authorization for you to choose that version for the<br />Library.</p>','lisensi_slims',NULL,0,NULL,'2010-08-29 04:04:33','2010-11-12 22:15:43','1'),
(8,'Model Pengembangan Open Source','<p><!--intro_awal-->Sumber terbuka (Inggris: open source) adalah sistem pengembangan yang tidak dikoordinasi oleh suatu individu / lembaga pusat, tetapi oleh para pelaku yang bekerja sama dengan memanfaatkan kode sumber (source-code) yang tersebar dan tersedia bebas (biasanya menggunakan fasilitas komunikasi internet). Pola pengembangan ini mengambil model ala bazaar, sehingga pola Open Source ini memiliki ciri bagi komunitasnya yaitu adanya dorongan yang bersumber dari budaya memberi.<!--intro_akhir--><br /><br />Pola Open Source lahir karena kebebasan berkarya, tanpa intervensi berpikir dan mengungkapkan apa yang diinginkan dengan menggunakan pengetahuan dan produk yang cocok. Kebebasan menjadi pertimbangan utama ketika dilepas ke publik. Komunitas yang lain mendapat kebebasan untuk belajar, mengutak-ngatik, merevisi ulang, membenarkan ataupun bahkan menyalahkan, tetapi kebebasan ini juga datang bersama dengan tanggung jawab, bukan bebas tanpa tanggung jawab.<br /><br />Pada intinya konsep sumber terbuka adalah membuka \"kode sumber\" dari sebuah perangkat lunak. Konsep ini terasa aneh pada awalnya dikarenakan kode sumber merupakan kunci dari sebuah perangkat lunak. Dengan diketahui logika yang ada di kode sumber, maka orang lain semestinya dapat membuat perangkat lunak yang sama fungsinya. Sumber terbuka hanya sebatas itu. Artinya, dia tidak harus gratis. Definisi sumber terbuka yang asli adalah seperti tertuang dalam OSD (Open Source Definition)/Definisi sumber terbuka.</p>\r\n<p>Pengembangan Senayan awalnya diinisiasi oleh pengelola Perpustakaan Depdiknas. Tetapi sekarang komunitas pengembang Senayan (Senayan Developer Community) yang lebih banyak mengambil peran dalam mengembangkan Senayan. Beberapa hal dibawah ini merupakan kultur yang dibangun dalam mengembangkan Senayan:<br />1.&nbsp;&nbsp;&nbsp; Meritokrasi. Siapa saja bisa berkontribusi. Mereka yang banyak memberikan kontribusi, akan mendapatkan privilege lebih dibandingkan yang lain.<br />2.&nbsp;&nbsp;&nbsp; Minimal punya concern terhadap pengembangan perpustakaan. Contoh lain: berlatar belakang pendidikan ilmu perpustakaan dan informasi, bekerja di perpustakaan, mengelola perpustakaan, dan lain-lain. Diharapkan dengan kondisi ini, sense of librarianship melekat di tiap developer/pengguna Senayan. Sejauh ini, semua developer senayan merupakan pustakawan atau berlatarbelakang pendidikan kepustakawanan (Information and Librarianship).<br />3.&nbsp;&nbsp;&nbsp; Release early, release often, and listen to your customer. Release early artinya setiap perbaikan dan penambahan fitur, secepat mungkin dirilis ke publik. Diharapkan bugs yang ada, bisa cepat ditemukan oleh komunitas, dilaporkan ke developer, untuk kemudian dirilis perbaikannya. Release often, artinya sesering mungkin memberikan update perbaikan bugs dan penambahan fitur. Ini &ldquo;memaksa&rdquo; developer Senayan untuk terus kreatif menambahkan fitur Senayan. Release often juga membuat pengguna berkeyakinan bahwa Senayan punya sustainability yang baik dan terus aktif dikembangkan. Selain itu, release often juga mempunyai dampak pemasaran. Pengguna dan calon pengguna, selalu diingatkan tentang keberadaan Senayan. Tentunya dengan cara yang elegan, yaitu rilis-rilis Senayan. Sejak dirilis ke publi pertama kali November 2007 sampai Juli 2009 (kurang lebih 20 bulan) telah dirilis 18 rilis resmi Senayan. Listen to your customer. Developer Senayan selalu berusaha mengakomodasi kebutuhan pengguna baik yang masuk melalui report di mailing list, ataupun melalui bugs tracking system. Tentu tidak semua masukan diakomodasi, harus disesuaikan dengan desain dan roadmap pengembangan Senayan.<br />4.&nbsp;&nbsp;&nbsp; Dokumentasi. Developer Senayan meyakini pentingnya dokumentasi yang baik dalam mensukseskan implementasi Senayan dibanyak tempat. Karena itu pengembang Senayan mempunyai tim khusus yang bertanggungjawab yang mengembangkan dokumentasi Senayan agar terus uo-to-date mengikuti rilis terbaru.<br />5.&nbsp;&nbsp;&nbsp; Agar ada percepatan dalam pengembangan dan untuk mengakrabkan antar pengembang Senayan, minimal setahun sekali diadakan Senayan Developers Day yang mengumpulkan para developer Senayan dari berbagai kota, dan melakukan coding bersama-sama.</p>','opensource',NULL,0,NULL,'2010-08-29 04:05:16','2010-08-29 04:34:04','1');
/*!40000 ALTER TABLE `content` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `files`
--

DROP TABLE IF EXISTS `files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `files` (
  `file_id` int(11) NOT NULL AUTO_INCREMENT,
  `file_title` text NOT NULL,
  `file_name` text NOT NULL,
  `file_url` text DEFAULT NULL,
  `file_dir` text DEFAULT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `file_desc` text DEFAULT NULL,
  `file_key` text DEFAULT NULL,
  `uploader_id` int(11) NOT NULL,
  `input_date` datetime NOT NULL,
  `last_update` datetime NOT NULL,
  PRIMARY KEY (`file_id`),
  FULLTEXT KEY `file_name` (`file_name`),
  FULLTEXT KEY `file_dir` (`file_dir`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `files`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `files` WRITE;
/*!40000 ALTER TABLE `files` DISABLE KEYS */;
/*!40000 ALTER TABLE `files` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `files_read`
--

DROP TABLE IF EXISTS `files_read`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `files_read` (
  `filelog_id` int(11) NOT NULL AUTO_INCREMENT,
  `file_id` int(11) NOT NULL,
  `date_read` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `member_id` varchar(20) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `client_ip` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`filelog_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `files_read`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `files_read` WRITE;
/*!40000 ALTER TABLE `files_read` DISABLE KEYS */;
/*!40000 ALTER TABLE `files_read` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `fines`
--

DROP TABLE IF EXISTS `fines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `fines` (
  `fines_id` int(11) NOT NULL AUTO_INCREMENT,
  `fines_date` date NOT NULL,
  `member_id` varchar(20) NOT NULL,
  `debet` int(11) DEFAULT 0,
  `credit` int(11) DEFAULT 0,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`fines_id`),
  KEY `member_id` (`member_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fines`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `fines` WRITE;
/*!40000 ALTER TABLE `fines` DISABLE KEYS */;
/*!40000 ALTER TABLE `fines` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `group_access`
--

DROP TABLE IF EXISTS `group_access`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `group_access` (
  `group_id` int(11) NOT NULL,
  `module_id` int(11) NOT NULL,
  `menus` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`menus`)),
  `r` int(1) NOT NULL DEFAULT 0,
  `w` int(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`group_id`,`module_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `group_access`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `group_access` WRITE;
/*!40000 ALTER TABLE `group_access` DISABLE KEYS */;
INSERT INTO `group_access` VALUES
(1,1,NULL,1,1),
(1,2,NULL,1,1),
(1,3,NULL,1,1),
(1,4,NULL,1,1),
(1,5,NULL,1,1),
(1,6,NULL,1,1),
(1,7,NULL,1,1),
(1,8,NULL,1,1);
/*!40000 ALTER TABLE `group_access` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `holiday`
--

DROP TABLE IF EXISTS `holiday`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `holiday` (
  `holiday_id` int(11) NOT NULL AUTO_INCREMENT,
  `holiday_dayname` varchar(20) NOT NULL,
  `holiday_date` date DEFAULT NULL,
  `description` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`holiday_id`),
  UNIQUE KEY `holiday_dayname` (`holiday_dayname`,`holiday_date`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `holiday`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `holiday` WRITE;
/*!40000 ALTER TABLE `holiday` DISABLE KEYS */;
INSERT INTO `holiday` VALUES
(1,'Mon','2009-06-01','Tes Libur'),
(2,'Tue','2009-06-02','Tes Libur'),
(3,'Wed','2009-06-03','Tes Libur'),
(4,'Thu','2009-06-04','Tes Libur'),
(5,'Fri','2009-06-05','Tes Libur'),
(6,'Sat','2009-06-06','Tes Libur');
/*!40000 ALTER TABLE `holiday` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `index_documents`
--

DROP TABLE IF EXISTS `index_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `index_documents` (
  `document_id` int(11) NOT NULL,
  `word_id` bigint(20) NOT NULL,
  `location` varchar(50) NOT NULL,
  `hit_count` int(11) NOT NULL,
  PRIMARY KEY (`document_id`,`word_id`,`location`),
  KEY `document_id` (`document_id`),
  KEY `word_id` (`word_id`),
  KEY `location` (`location`),
  KEY `idx_word_location` (`word_id`,`location`,`hit_count`),
  KEY `idx_document_id` (`document_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `index_documents`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `index_documents` WRITE;
/*!40000 ALTER TABLE `index_documents` DISABLE KEYS */;
INSERT INTO `index_documents` VALUES
(1,1,'title',1),
(1,2,'title',1),
(1,3,'title',1),
(1,4,'author',1),
(1,5,'author',1),
(1,6,'subject',1),
(1,7,'subject',1),
(1,8,'isbn',1),
(1,9,'publisher',1),
(2,10,'title',1),
(2,11,'title',1),
(2,12,'author',1),
(2,13,'author',1),
(2,14,'author',1),
(2,15,'author',1),
(2,16,'author',1),
(2,17,'author',1),
(2,18,'author',1),
(2,19,'author',1),
(2,20,'author',1),
(2,21,'author',1),
(2,22,'subject',1),
(2,10,'subject',1),
(2,23,'subject',1),
(2,24,'subject',1),
(2,25,'isbn',1),
(2,26,'publisher',1),
(3,27,'title',1),
(3,28,'title',1),
(3,29,'title',1),
(3,30,'author',1),
(3,31,'author',1),
(3,32,'author',1),
(3,33,'author',1),
(3,34,'subject',1),
(3,6,'subject',1),
(3,35,'subject',1),
(3,36,'isbn',1),
(3,37,'publisher',1),
(4,38,'title',1),
(4,39,'title',1),
(4,40,'title',1),
(4,10,'title',1),
(4,41,'title',1),
(4,42,'title',1),
(4,43,'title',1),
(4,44,'title',1),
(4,45,'author',1),
(4,46,'author',1),
(4,10,'subject',1),
(4,41,'subject',1),
(4,42,'subject',1),
(4,47,'isbn',1),
(4,48,'isbn',1),
(4,26,'publisher',1),
(4,38,'notes',1),
(4,39,'notes',1),
(4,49,'notes',1),
(4,2,'notes',1),
(4,50,'notes',1),
(4,51,'notes',1),
(4,52,'notes',1),
(4,53,'notes',1),
(4,54,'notes',1),
(4,22,'notes',1),
(4,55,'notes',1),
(4,56,'notes',1),
(4,57,'notes',1),
(4,58,'notes',1),
(4,59,'notes',1),
(4,60,'notes',1),
(4,61,'notes',1),
(4,62,'notes',1),
(4,63,'notes',1),
(4,64,'notes',1),
(4,65,'notes',1),
(4,66,'notes',1),
(4,41,'notes',2),
(4,42,'notes',3),
(4,67,'notes',1),
(4,68,'notes',1),
(4,69,'notes',1),
(4,45,'notes',1),
(4,70,'notes',1),
(4,71,'notes',1),
(4,72,'notes',1),
(4,73,'notes',1),
(4,74,'notes',1),
(4,75,'notes',1),
(4,76,'notes',1),
(4,77,'notes',1),
(4,78,'notes',1),
(4,79,'notes',1),
(4,80,'notes',1),
(4,81,'notes',1),
(4,82,'notes',1),
(4,83,'notes',1),
(4,84,'notes',1),
(4,26,'notes',1),
(4,85,'notes',1),
(4,86,'notes',1),
(5,87,'title',1),
(5,41,'title',1),
(5,42,'title',1),
(5,77,'title',2),
(5,88,'title',1),
(5,89,'title',1),
(5,90,'title',1),
(5,91,'title',1),
(5,92,'title',1),
(5,93,'author',1),
(5,94,'author',1),
(5,41,'subject',1),
(5,42,'subject',1),
(5,92,'subject',1),
(5,95,'isbn',1),
(5,26,'publisher',1),
(5,64,'notes',1),
(5,96,'notes',1),
(6,97,'title',2),
(6,98,'title',1),
(6,28,'title',1),
(6,99,'title',1),
(6,6,'title',1),
(6,100,'title',1),
(6,101,'title',1),
(6,102,'author',2),
(6,103,'author',1),
(6,104,'author',1),
(6,6,'subject',1),
(6,35,'subject',1),
(6,105,'isbn',1),
(6,106,'publisher',1),
(6,97,'notes',4),
(6,107,'notes',1),
(6,108,'notes',2),
(6,109,'notes',1),
(6,41,'notes',1),
(6,42,'notes',1),
(6,34,'notes',2),
(6,98,'notes',1),
(6,110,'notes',1),
(6,111,'notes',1),
(6,112,'notes',1),
(6,28,'notes',1),
(6,59,'notes',2),
(6,113,'notes',1),
(6,114,'notes',1),
(6,115,'notes',1),
(6,116,'notes',1),
(6,117,'notes',1),
(6,118,'notes',1),
(6,119,'notes',1),
(6,120,'notes',1),
(6,121,'notes',1),
(6,122,'notes',1),
(6,123,'notes',1),
(6,124,'notes',1),
(6,125,'notes',1),
(6,126,'notes',1),
(6,127,'notes',1),
(7,128,'title',1),
(7,129,'title',1),
(7,130,'title',1),
(7,131,'title',1),
(7,132,'title',1),
(7,133,'title',1),
(7,134,'author',1),
(7,135,'author',1),
(7,136,'author',1),
(7,137,'author',1),
(7,138,'subject',1),
(7,7,'subject',1),
(7,139,'isbn',1),
(7,140,'publisher',1),
(7,9,'publisher',1),
(7,110,'notes',1),
(7,141,'notes',1),
(7,142,'notes',1),
(7,143,'notes',1),
(7,144,'notes',1),
(7,131,'notes',1),
(7,128,'notes',1),
(7,129,'notes',1),
(7,145,'notes',1),
(7,59,'notes',2),
(7,115,'notes',1),
(7,146,'notes',1),
(7,147,'notes',1),
(7,148,'notes',1),
(7,149,'notes',1),
(7,150,'notes',1),
(7,151,'notes',1),
(7,152,'notes',1),
(7,153,'notes',1),
(7,154,'notes',1),
(7,155,'notes',1),
(7,156,'notes',1),
(7,157,'notes',1),
(7,158,'notes',1),
(7,159,'notes',2),
(7,160,'notes',1),
(7,161,'notes',1),
(7,162,'notes',1),
(7,163,'notes',1),
(7,164,'notes',1),
(7,165,'notes',1),
(7,166,'notes',1),
(7,167,'notes',1),
(7,168,'notes',1),
(7,169,'notes',1),
(7,170,'notes',1),
(7,171,'notes',1),
(7,172,'notes',1),
(7,65,'notes',1),
(7,173,'notes',1),
(7,174,'notes',1),
(7,175,'notes',1),
(7,176,'notes',1),
(7,177,'notes',1),
(7,178,'notes',1),
(8,179,'title',1),
(8,180,'title',1),
(8,128,'title',1),
(8,181,'title',1),
(8,117,'title',1),
(8,182,'title',1),
(8,183,'title',1),
(8,184,'title',1),
(8,185,'author',1),
(8,186,'author',1),
(8,6,'subject',1),
(8,7,'subject',1),
(8,187,'isbn',1),
(8,188,'publisher',1),
(8,189,'publisher',1),
(8,190,'notes',2),
(8,179,'notes',2),
(8,191,'notes',2),
(8,192,'notes',2),
(8,193,'notes',1),
(8,128,'notes',2),
(8,194,'notes',2),
(8,117,'notes',1),
(8,195,'notes',1),
(8,196,'notes',1),
(8,197,'notes',1),
(8,198,'notes',1),
(8,199,'notes',1),
(8,41,'notes',2),
(8,42,'notes',2),
(8,77,'notes',1),
(8,200,'notes',1),
(8,2,'notes',2),
(8,201,'notes',1),
(8,202,'notes',1),
(8,203,'notes',1),
(8,111,'notes',1),
(8,204,'notes',1),
(8,205,'notes',1),
(8,28,'notes',1),
(8,206,'notes',1),
(8,207,'notes',1),
(8,208,'notes',1),
(8,209,'notes',1),
(9,210,'title',1),
(9,57,'title',1),
(9,211,'author',1),
(9,212,'author',1),
(9,57,'subject',1),
(9,213,'subject',1),
(9,210,'subject',1),
(9,214,'isbn',1),
(9,215,'publisher',1),
(9,216,'publisher',1),
(9,217,'notes',1),
(9,218,'notes',1),
(9,2,'notes',2),
(9,219,'notes',2),
(9,220,'notes',2),
(9,57,'notes',1),
(9,221,'notes',1),
(9,28,'notes',1),
(9,222,'notes',1),
(9,223,'notes',2),
(9,224,'notes',2),
(9,225,'notes',1),
(9,226,'notes',1),
(9,227,'notes',1),
(9,228,'notes',1),
(9,229,'notes',1),
(9,230,'notes',1),
(9,145,'notes',1),
(9,231,'notes',1),
(9,232,'notes',1),
(9,233,'notes',1),
(9,234,'notes',1),
(9,235,'notes',1),
(9,236,'notes',1),
(9,237,'notes',1),
(9,238,'notes',1),
(9,239,'notes',1),
(9,240,'notes',1),
(9,241,'notes',1),
(9,242,'notes',1),
(9,243,'notes',1),
(9,244,'notes',1),
(9,245,'notes',1),
(9,246,'notes',1),
(10,220,'title',1),
(10,57,'title',1),
(10,232,'title',1),
(10,236,'title',1),
(10,247,'author',1),
(10,248,'author',1),
(10,249,'author',1),
(10,15,'author',1),
(10,57,'subject',1),
(10,220,'subject',1),
(10,250,'isbn',1),
(10,215,'publisher',1),
(10,216,'publisher',1),
(11,57,'title',1),
(11,130,'title',1),
(11,2,'title',1),
(11,107,'title',1),
(11,251,'title',1),
(11,128,'title',2),
(11,164,'title',1),
(11,252,'title',1),
(11,253,'title',1),
(11,254,'title',1),
(11,255,'author',1),
(11,256,'author',1),
(11,257,'author',1),
(11,258,'author',1),
(11,259,'isbn',1),
(11,26,'publisher',1),
(11,57,'notes',1),
(11,130,'notes',2),
(11,2,'notes',4),
(11,107,'notes',1),
(11,251,'notes',1),
(11,128,'notes',2),
(11,53,'notes',1),
(11,260,'notes',1),
(11,131,'notes',1),
(11,220,'notes',1),
(11,261,'notes',1),
(11,262,'notes',2),
(11,138,'notes',1),
(11,263,'notes',1),
(11,7,'notes',1),
(11,264,'notes',1),
(11,265,'notes',1),
(11,99,'notes',1),
(11,244,'notes',1),
(11,266,'notes',1),
(11,267,'notes',1),
(11,268,'notes',1),
(11,269,'notes',1),
(11,270,'notes',1),
(11,271,'notes',1),
(11,272,'notes',1),
(11,273,'notes',1),
(11,274,'notes',1),
(11,275,'notes',1),
(11,276,'notes',1),
(11,277,'notes',1),
(11,278,'notes',1),
(11,279,'notes',1),
(11,280,'notes',1),
(11,281,'notes',1),
(11,282,'notes',1),
(11,283,'notes',1),
(11,284,'notes',1),
(11,285,'notes',1),
(11,286,'notes',1),
(12,287,'title',1),
(12,145,'title',1),
(12,288,'author',1),
(12,289,'author',1),
(12,287,'subject',1),
(12,145,'subject',1),
(12,290,'isbn',1),
(12,211,'publisher',1),
(12,291,'publisher',1),
(12,292,'publisher',1),
(12,293,'notes',1),
(12,294,'notes',1),
(12,59,'notes',1),
(12,295,'notes',1),
(12,296,'notes',1),
(12,297,'notes',1),
(12,298,'notes',1),
(12,299,'notes',1),
(12,300,'notes',1),
(12,287,'notes',1),
(12,301,'notes',1),
(12,302,'notes',1),
(12,303,'notes',1),
(12,304,'notes',1),
(12,305,'notes',1),
(13,287,'title',2),
(13,145,'title',1),
(13,306,'title',1),
(13,307,'title',1),
(13,308,'author',1),
(13,309,'author',1),
(13,287,'subject',1),
(13,310,'isbn',1),
(13,311,'publisher',1),
(13,312,'publisher',1),
(13,59,'notes',1),
(13,115,'notes',1),
(13,313,'notes',1),
(13,314,'notes',1),
(13,315,'notes',1),
(13,228,'notes',1),
(13,306,'notes',1),
(13,287,'notes',1),
(13,307,'notes',1),
(13,316,'notes',1),
(13,317,'notes',1),
(13,318,'notes',1),
(13,319,'notes',1),
(13,173,'notes',1),
(13,320,'notes',1),
(13,321,'notes',1),
(13,322,'notes',1),
(13,323,'notes',1),
(13,324,'notes',1),
(13,325,'notes',1),
(13,326,'notes',1),
(13,327,'notes',1),
(13,117,'notes',1),
(13,328,'notes',1),
(13,329,'notes',1),
(13,330,'notes',1),
(13,331,'notes',1),
(14,332,'title',1),
(14,333,'title',1),
(14,88,'title',1),
(14,334,'title',1),
(14,335,'title',1),
(14,336,'title',1),
(14,287,'title',1),
(14,337,'title',1),
(14,338,'title',1),
(14,339,'author',1),
(14,340,'author',1),
(14,341,'author',1),
(14,287,'subject',1),
(14,342,'isbn',1),
(14,343,'publisher',1),
(14,344,'publisher',1),
(15,345,'title',1),
(15,346,'title',1),
(15,347,'title',1),
(15,348,'title',1),
(15,287,'title',1),
(15,349,'title',1),
(15,350,'title',1),
(15,351,'title',1),
(15,352,'author',1),
(15,353,'author',1),
(15,287,'subject',1),
(15,346,'subject',1),
(15,354,'isbn',1),
(15,355,'publisher',1),
(15,356,'publisher',1),
(15,357,'publisher',1),
(15,345,'notes',1),
(15,346,'notes',1),
(15,358,'notes',1),
(15,359,'notes',1),
(15,360,'notes',1),
(15,265,'notes',1),
(15,361,'notes',1),
(15,362,'notes',1),
(15,350,'notes',2),
(15,363,'notes',1),
(15,364,'notes',1),
(15,365,'notes',1),
(15,366,'notes',1),
(15,367,'notes',1),
(15,53,'notes',1),
(15,368,'notes',1),
(15,369,'notes',1),
(15,81,'notes',1),
(15,108,'notes',1),
(15,370,'notes',1),
(15,371,'notes',1),
(15,372,'notes',1),
(15,373,'notes',1),
(15,118,'notes',1),
(15,374,'notes',1),
(15,375,'notes',1),
(15,376,'notes',1),
(15,377,'notes',1),
(15,162,'notes',1),
(15,378,'notes',1),
(15,379,'notes',1),
(15,380,'notes',1),
(15,381,'notes',1),
(15,382,'notes',1),
(15,383,'notes',1),
(15,384,'notes',1),
(15,385,'notes',1),
(15,386,'notes',1),
(15,387,'notes',1),
(16,388,'title',1),
(16,389,'title',1),
(16,390,'author',1),
(16,391,'author',1),
(16,392,'subject',1),
(16,393,'subject',1),
(16,394,'subject',1),
(16,395,'subject',1),
(16,396,'isbn',1),
(16,397,'publisher',1),
(16,398,'publisher',1),
(17,399,'title',1),
(17,400,'title',1),
(17,390,'author',1),
(17,391,'author',1),
(17,392,'subject',1),
(17,393,'subject',1),
(17,401,'subject',1),
(17,402,'isbn',1),
(17,403,'publisher',1),
(17,404,'publisher',1),
(18,405,'title',1),
(18,406,'author',1),
(18,407,'author',1),
(18,392,'subject',1),
(18,393,'subject',1),
(18,405,'subject',1),
(18,408,'isbn',1),
(18,403,'publisher',1),
(18,404,'publisher',1),
(19,409,'title',1),
(19,406,'author',1),
(19,407,'author',1),
(19,392,'subject',1),
(19,393,'subject',1),
(19,401,'subject',1),
(19,410,'isbn',1),
(19,411,'publisher',1),
(19,412,'publisher',1),
(20,413,'title',1),
(20,414,'title',1),
(20,415,'author',1),
(20,416,'author',1),
(20,392,'subject',1),
(20,393,'subject',1),
(20,394,'subject',1),
(20,405,'subject',1),
(20,417,'isbn',1),
(20,418,'publisher',1),
(21,419,'title',1),
(21,420,'title',1),
(21,421,'title',1),
(21,415,'author',1),
(21,416,'author',1),
(21,392,'subject',1),
(21,393,'subject',1),
(21,395,'subject',1),
(21,422,'isbn',1),
(21,423,'publisher',1),
(22,424,'title',1),
(22,425,'author',1),
(22,426,'author',1),
(22,392,'subject',1),
(22,393,'subject',1),
(22,394,'subject',1),
(22,395,'subject',1),
(22,427,'isbn',1),
(22,397,'publisher',1),
(22,398,'publisher',1),
(23,428,'title',1),
(23,429,'author',1),
(23,430,'author',1),
(23,392,'subject',1),
(23,393,'subject',1),
(23,395,'subject',1),
(23,431,'isbn',1),
(23,403,'publisher',1),
(23,404,'publisher',1),
(24,432,'title',1),
(24,433,'title',1),
(24,429,'author',1),
(24,430,'author',1),
(24,392,'subject',1),
(24,393,'subject',1),
(24,401,'subject',1),
(24,434,'isbn',1),
(24,411,'publisher',1),
(24,412,'publisher',1),
(25,435,'title',1),
(25,436,'title',1),
(25,437,'author',1),
(25,438,'author',1),
(25,439,'subject',1),
(25,440,'subject',1),
(25,395,'subject',1),
(25,441,'isbn',1),
(25,411,'publisher',1),
(25,412,'publisher',1),
(26,442,'title',1),
(26,443,'title',1),
(26,444,'author',1),
(26,445,'author',1),
(26,392,'subject',1),
(26,393,'subject',1),
(26,395,'subject',1),
(26,446,'isbn',1),
(26,447,'publisher',1),
(27,448,'title',1),
(27,395,'title',1),
(27,444,'author',1),
(27,445,'author',1),
(27,392,'subject',1),
(27,393,'subject',1),
(27,395,'subject',1),
(27,449,'isbn',1),
(27,447,'publisher',1),
(28,450,'title',1),
(28,451,'title',1),
(28,452,'author',1),
(28,453,'author',1),
(28,392,'subject',1),
(28,393,'subject',1),
(28,395,'subject',1),
(28,454,'isbn',1),
(28,455,'publisher',1),
(29,456,'title',1),
(29,457,'title',1),
(29,458,'title',1),
(29,459,'author',1),
(29,460,'author',1),
(29,392,'subject',1),
(29,393,'subject',1),
(29,395,'subject',1),
(29,461,'isbn',1),
(29,403,'publisher',1),
(29,404,'publisher',1),
(30,462,'title',1),
(30,459,'author',1),
(30,460,'author',1),
(30,392,'subject',1),
(30,393,'subject',1),
(30,401,'subject',1),
(30,463,'isbn',1),
(30,418,'publisher',1),
(31,464,'title',1),
(31,465,'author',1),
(31,466,'author',1),
(31,392,'subject',1),
(31,393,'subject',1),
(31,394,'subject',1),
(31,467,'subject',1),
(31,468,'isbn',1),
(31,397,'publisher',1),
(31,398,'publisher',1),
(32,469,'title',1),
(32,470,'author',1),
(32,471,'author',1),
(32,392,'subject',1),
(32,393,'subject',1),
(32,467,'subject',1),
(32,472,'isbn',1),
(32,403,'publisher',1),
(32,404,'publisher',1),
(33,464,'title',1),
(33,473,'author',1),
(33,474,'author',1),
(33,392,'subject',1),
(33,393,'subject',1),
(33,467,'subject',1),
(33,475,'isbn',1),
(33,411,'publisher',1),
(33,412,'publisher',1),
(34,476,'title',1),
(34,477,'author',1),
(34,478,'author',1),
(34,467,'subject',1),
(34,479,'subject',1),
(34,480,'isbn',1),
(34,455,'publisher',1),
(35,481,'title',1),
(35,482,'title',1),
(35,483,'author',1),
(35,484,'author',1),
(35,392,'subject',1),
(35,393,'subject',1),
(35,394,'subject',1),
(35,405,'subject',1),
(35,485,'isbn',1),
(35,397,'publisher',1),
(35,398,'publisher',1),
(36,486,'title',1),
(36,487,'title',1),
(36,488,'author',1),
(36,489,'author',1),
(36,490,'subject',1),
(36,393,'subject',1),
(36,491,'subject',1),
(36,492,'isbn',1),
(36,493,'publisher',1),
(37,494,'title',1),
(37,495,'title',1),
(37,488,'author',1),
(37,489,'author',1),
(37,490,'subject',1),
(37,393,'subject',1),
(37,491,'subject',1),
(37,496,'isbn',1),
(37,497,'publisher',1),
(38,498,'title',1),
(38,499,'title',1),
(38,500,'title',1),
(38,501,'author',1),
(38,502,'author',1),
(38,490,'subject',1),
(38,393,'subject',1),
(38,491,'subject',1),
(38,503,'isbn',1),
(38,497,'publisher',1),
(39,504,'title',1),
(39,505,'title',1),
(39,501,'author',1),
(39,502,'author',1),
(39,490,'subject',1),
(39,393,'subject',1),
(39,506,'isbn',1),
(39,497,'publisher',1),
(40,507,'title',1),
(40,508,'title',1),
(40,509,'title',1),
(40,510,'title',1),
(40,511,'author',1),
(40,512,'author',1),
(40,490,'subject',1),
(40,393,'subject',1),
(40,513,'subject',1),
(40,514,'isbn',1),
(40,515,'publisher',1),
(40,440,'publisher',1),
(41,507,'title',1),
(41,508,'title',1),
(41,516,'title',1),
(41,517,'title',1),
(41,511,'author',1),
(41,512,'author',1),
(41,490,'subject',1),
(41,393,'subject',1),
(41,513,'subject',1),
(41,518,'isbn',1),
(41,515,'publisher',1),
(41,440,'publisher',1),
(42,519,'title',1),
(42,520,'author',1),
(42,521,'author',1),
(42,490,'subject',1),
(42,393,'subject',1),
(42,513,'subject',1),
(42,522,'isbn',1),
(42,515,'publisher',1),
(42,440,'publisher',1),
(43,523,'title',1),
(43,524,'title',1),
(43,525,'author',1),
(43,526,'author',1),
(43,527,'author',1),
(43,528,'author',1),
(43,490,'subject',1),
(43,393,'subject',1),
(43,394,'subject',1),
(43,529,'isbn',1),
(43,530,'publisher',1),
(43,531,'publisher',1),
(44,532,'title',1),
(44,533,'title',1),
(44,534,'title',1),
(44,535,'author',1),
(44,536,'author',1),
(44,490,'subject',1),
(44,393,'subject',1),
(44,537,'isbn',1),
(44,497,'publisher',1),
(45,538,'title',1),
(45,539,'title',1),
(45,540,'title',1),
(45,541,'author',1),
(45,542,'author',1),
(45,490,'subject',1),
(45,393,'subject',1),
(45,394,'subject',1),
(45,543,'isbn',1),
(45,497,'publisher',1),
(46,544,'title',1),
(46,545,'title',1),
(46,546,'author',1),
(46,453,'author',1),
(46,490,'subject',1),
(46,393,'subject',1),
(46,491,'subject',1),
(46,547,'isbn',1),
(46,493,'publisher',1),
(47,548,'title',1),
(47,549,'title',1),
(47,550,'author',1),
(47,548,'subject',1),
(47,551,'isbn',1),
(47,552,'publisher',1),
(48,403,'title',1),
(48,553,'title',1),
(48,549,'title',1),
(48,550,'author',1),
(48,554,'subject',1),
(48,553,'subject',1),
(48,555,'isbn',1),
(48,552,'publisher',1),
(49,439,'title',1),
(49,556,'title',1),
(49,549,'title',1),
(49,550,'author',1),
(49,439,'subject',1),
(49,440,'subject',1),
(49,557,'isbn',1),
(49,552,'publisher',1),
(50,558,'title',1),
(50,559,'title',1),
(50,560,'title',1),
(50,549,'title',1),
(50,550,'author',1),
(50,558,'subject',1),
(50,561,'isbn',1),
(50,552,'publisher',1),
(51,442,'title',1),
(51,562,'title',1),
(51,549,'title',1),
(51,550,'author',1),
(51,442,'subject',1),
(51,563,'isbn',1),
(51,552,'publisher',1),
(52,564,'title',1),
(52,565,'title',1),
(52,549,'title',1),
(52,550,'author',1),
(52,564,'subject',1),
(52,566,'isbn',1),
(52,552,'publisher',1),
(53,403,'title',1),
(53,567,'title',1),
(53,568,'title',1),
(53,550,'author',1),
(53,568,'subject',1),
(53,569,'isbn',1),
(54,570,'title',1),
(54,568,'title',1),
(54,571,'title',1),
(54,553,'title',1),
(54,550,'author',1),
(54,568,'subject',1),
(54,572,'isbn',1),
(55,573,'title',1),
(55,403,'title',1),
(55,568,'title',1),
(55,550,'author',1),
(55,568,'subject',1),
(55,554,'subject',1),
(55,553,'subject',1),
(55,574,'isbn',1),
(55,575,'publisher',1),
(56,576,'title',1),
(56,440,'title',1),
(56,550,'author',1),
(56,576,'subject',1),
(56,439,'subject',1),
(56,440,'subject',1),
(56,577,'isbn',1),
(56,575,'publisher',1),
(57,578,'title',1),
(57,579,'title',1),
(57,550,'author',1),
(57,564,'subject',1),
(57,580,'isbn',1),
(57,575,'publisher',1),
(58,439,'title',1),
(58,440,'title',1),
(58,550,'author',1),
(58,439,'subject',1),
(58,440,'subject',1),
(58,581,'isbn',1),
(58,582,'publisher',1),
(58,583,'publisher',1),
(59,584,'title',1),
(59,585,'title',1),
(59,550,'author',1),
(59,584,'subject',1),
(59,586,'isbn',1),
(59,582,'publisher',1),
(59,583,'publisher',1),
(60,587,'title',1),
(60,439,'title',1),
(60,588,'title',1),
(60,550,'author',1),
(60,442,'subject',1),
(60,589,'isbn',1),
(60,582,'publisher',1),
(60,583,'publisher',1),
(61,439,'title',1),
(61,590,'title',1),
(61,420,'title',1),
(61,550,'author',1),
(61,439,'subject',1),
(61,440,'subject',1),
(61,591,'subject',1),
(61,592,'isbn',1),
(61,593,'publisher',1),
(62,594,'title',1),
(62,595,'title',1),
(62,596,'title',1),
(62,420,'title',1),
(62,550,'author',1),
(62,591,'subject',1),
(62,467,'subject',1),
(62,597,'isbn',1),
(62,593,'publisher',1),
(63,598,'title',1),
(63,599,'title',1),
(63,600,'title',1),
(63,550,'author',1),
(63,395,'subject',1),
(63,554,'subject',1),
(63,553,'subject',1),
(63,601,'isbn',1),
(63,455,'publisher',1),
(64,602,'title',1),
(64,603,'title',1),
(64,604,'author',1),
(64,605,'author',1),
(64,394,'subject',1),
(64,395,'subject',1),
(64,606,'isbn',1),
(64,455,'publisher',1),
(65,607,'title',1),
(65,608,'title',1),
(65,609,'title',1),
(65,610,'author',1),
(65,611,'author',1),
(65,612,'author',1),
(65,394,'subject',1),
(65,395,'subject',1),
(65,613,'isbn',1),
(66,614,'title',1),
(66,615,'title',1),
(66,616,'title',1),
(66,550,'author',1),
(66,591,'subject',1),
(67,614,'title',1),
(67,617,'title',1),
(67,616,'title',1),
(67,550,'author',1),
(67,439,'subject',1),
(67,440,'subject',1),
(67,591,'subject',1),
(68,388,'title',1),
(68,389,'title',1),
(68,618,'title',1),
(68,619,'title',1),
(68,620,'title',1),
(68,550,'author',1),
(68,621,'subject',1),
(68,593,'publisher',1),
(69,392,'title',1),
(69,397,'title',1),
(69,622,'title',1),
(69,550,'author',1),
(69,591,'subject',1),
(69,593,'publisher',1);
/*!40000 ALTER TABLE `index_documents` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `index_words`
--

DROP TABLE IF EXISTS `index_words`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `index_words` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `word` varchar(255) NOT NULL,
  `num_hits` int(11) NOT NULL,
  `doc_hits` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_word` (`word`(250)),
  KEY `idx_word_hits` (`word`(245),`num_hits`)
) ENGINE=MyISAM AUTO_INCREMENT=623 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `index_words`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `index_words` WRITE;
/*!40000 ALTER TABLE `index_words` DISABLE KEYS */;
INSERT INTO `index_words` VALUES
(1,'php',1,1),
(2,'for',11,6),
(3,'dummies',1,1),
(4,'valade',1,1),
(5,'janet',1,1),
(6,'programming',5,5),
(7,'website',4,4),
(8,'0764541668',1,1),
(9,'wiley',2,2),
(10,'linux',4,4),
(11,'nutshell',1,1),
(12,'figgins',1,1),
(13,'stephen',1,1),
(14,'love',1,1),
(15,'robert',2,2),
(16,'robbins',1,1),
(17,'arnold',1,1),
(18,'siever',1,1),
(19,'ellen',1,1),
(20,'weber',1,1),
(21,'aaron',1,1),
(22,'computer',2,2),
(23,'operating',1,1),
(24,'system',1,1),
(25,'9780596009304',1,1),
(26,'oreilly',5,5),
(27,'definitive',1,1),
(28,'guide',5,5),
(29,'mysql',1,1),
(30,'kofler',1,1),
(31,'michael',1,1),
(32,'kramer',1,1),
(33,'david',1,1),
(34,'database',3,2),
(35,'rdbms',2,2),
(36,'9781590595350',1,1),
(37,'apress',1,1),
(38,'cathedral',2,2),
(39,'bazaar',2,2),
(40,'musings',1,1),
(41,'open',9,7),
(42,'source',10,7),
(43,'accidental',1,1),
(44,'revolutionary',1,1),
(45,'raymond',2,2),
(46,'eric',1,1),
(47,'596',1,1),
(48,'00108',1,1),
(49,'must',1,1),
(50,'anyone',1,1),
(51,'who',1,1),
(52,'cares',1,1),
(53,'about',3,3),
(54,'future',1,1),
(55,'industry',1,1),
(56,'dynamics',1,1),
(57,'information',8,8),
(58,'economy',1,1),
(59,'this',7,5),
(60,'revised',1,1),
(61,'expanded',1,1),
(62,'paperback',1,1),
(63,'edition',1,1),
(64,'includes',2,2),
(65,'new',2,2),
(66,'material',1,1),
(67,'developments',1,1),
(68,'1999',1,1),
(69,'2000',1,1),
(70,'clear',1,1),
(71,'effective',1,1),
(72,'writing',1,1),
(73,'style',1,1),
(74,'accurately',1,1),
(75,'describing',1,1),
(76,'benefits',1,1),
(77,'software',4,3),
(78,'has',1,1),
(79,'been',1,1),
(80,'key',1,1),
(81,'its',2,2),
(82,'success',1,1),
(83,'http',1,1),
(84,'safari',1,1),
(85,'com',1,1),
(86,'0596001088',1,1),
(87,'producing',1,1),
(88,'how',2,2),
(89,'run',1,1),
(90,'successful',1,1),
(91,'free',1,1),
(92,'project',2,2),
(93,'fogel',1,1),
(94,'karl',1,1),
(95,'9780596007591',1,1),
(96,'index',1,1),
(97,'postgresql',6,2),
(98,'comprehensive',2,2),
(99,'building',2,2),
(100,'administering',1,1),
(101,'databases',1,1),
(102,'douglas',2,1),
(103,'korry',1,1),
(104,'susan',1,1),
(105,'0735712573',1,1),
(106,'sams',1,1),
(107,'world',3,3),
(108,'most',3,2),
(109,'advanced',1,1),
(110,'depth',2,2),
(111,'easy',2,2),
(112,'read',1,1),
(113,'award',1,1),
(114,'winning',1,1),
(115,'book',3,3),
(116,'starts',1,1),
(117,'with',4,4),
(118,'thorough',2,2),
(119,'overview',1,1),
(120,'sql',1,1),
(121,'description',1,1),
(122,'all',1,1),
(123,'data',1,1),
(124,'types',1,1),
(125,'complete',1,1),
(126,'explanation',1,1),
(127,'commands',1,1),
(128,'web',9,6),
(129,'application',2,2),
(130,'architecture',4,3),
(131,'principles',3,3),
(132,'protocols',1,1),
(133,'practices',1,1),
(134,'rosen',1,1),
(135,'richard',1,1),
(136,'shklar',1,1),
(137,'leon',1,1),
(138,'design',2,2),
(139,'0471486566',1,1),
(140,'john',1,1),
(141,'examination',1,1),
(142,'core',1,1),
(143,'concepts',1,1),
(144,'general',1,1),
(145,'development',5,5),
(146,'uses',1,1),
(147,'examples',1,1),
(148,'from',1,1),
(149,'specific',1,1),
(150,'technologies',1,1),
(151,'servlet',1,1),
(152,'api',1,1),
(153,'xsl',1,1),
(154,'without',1,1),
(155,'promoting',1,1),
(156,'endorsing',1,1),
(157,'particular',1,1),
(158,'platforms',1,1),
(159,'apis',2,1),
(160,'such',1,1),
(161,'knowledge',1,1),
(162,'critical',2,2),
(163,'when',1,1),
(164,'designing',2,2),
(165,'debugging',1,1),
(166,'complex',1,1),
(167,'systems',1,1),
(168,'conceptual',1,1),
(169,'understanding',1,1),
(170,'makes',1,1),
(171,'easier',1,1),
(172,'learn',1,1),
(173,'that',2,2),
(174,'arise',1,1),
(175,'rapidly',1,1),
(176,'changing',1,1),
(177,'internet',1,1),
(178,'environment',1,1),
(179,'ajax',3,2),
(180,'creating',1,1),
(181,'pages',1,1),
(182,'asynchronous',1,1),
(183,'javascript',1,1),
(184,'xml',1,1),
(185,'woychowsky',1,1),
(186,'edmond',1,1),
(187,'9780132272674',1,1),
(188,'prentice',1,1),
(189,'hall',1,1),
(190,'using',2,1),
(191,'you',2,1),
(192,'can',2,1),
(193,'build',1,1),
(194,'applications',2,1),
(195,'sophistication',1,1),
(196,'usability',1,1),
(197,'traditional',1,1),
(198,'desktop',1,1),
(199,'standards',1,1),
(200,'now',1,1),
(201,'first',1,1),
(202,'time',1,1),
(203,'there',1,1),
(204,'example',1,1),
(205,'driven',1,1),
(206,'every',1,1),
(207,'developer',1,1),
(208,'regardless',1,1),
(209,'experience',1,1),
(210,'organization',2,2),
(211,'taylor',2,2),
(212,'arlene',1,1),
(213,'metadata',1,1),
(214,'1563089769',1,1),
(215,'libraries',2,2),
(216,'unlimited',2,2),
(217,'basic',1,1),
(218,'textbook',1,1),
(219,'students',2,1),
(220,'library',5,4),
(221,'studies',1,1),
(222,'practicing',1,1),
(223,'school',2,1),
(224,'media',2,1),
(225,'specialists',1,1),
(226,'describes',1,1),
(227,'impact',1,1),
(228,'global',2,2),
(229,'forces',1,1),
(230,'district',1,1),
(231,'operation',1,1),
(232,'center',2,2),
(233,'technical',1,1),
(234,'human',1,1),
(235,'side',1,1),
(236,'management',2,2),
(237,'programmatic',1,1),
(238,'activities',1,1),
(239,'supportive',1,1),
(240,'services',1,1),
(241,'quality',1,1),
(242,'quantity',1,1),
(243,'resources',1,1),
(244,'available',2,2),
(245,'support',1,1),
(246,'programs',1,1),
(247,'moran',1,1),
(248,'barbara',1,1),
(249,'stueart',1,1),
(250,'9781591584063',1,1),
(251,'wide',2,2),
(252,'large',1,1),
(253,'scale',1,1),
(254,'sites',1,1),
(255,'morville',1,1),
(256,'peter',1,1),
(257,'rosenfeld',1,1),
(258,'louis',1,1),
(259,'9780596000356',1,1),
(260,'applying',1,1),
(261,'science',1,1),
(262,'site',2,1),
(263,'each',1,1),
(264,'like',1,1),
(265,'public',2,2),
(266,'tourists',1,1),
(267,'regulars',1,1),
(268,'alike',1,1),
(269,'breeze',1,1),
(270,'through',1,1),
(271,'their',1,1),
(272,'leisure',1,1),
(273,'job',1,1),
(274,'architect',1,1),
(275,'set',1,1),
(276,'framework',1,1),
(277,'make',1,1),
(278,'comfortable',1,1),
(279,'inviting',1,1),
(280,'people',1,1),
(281,'visit',1,1),
(282,'relax',1,1),
(283,'perhaps',1,1),
(284,'even',1,1),
(285,'return',1,1),
(286,'someday',1,1),
(287,'corruption',11,10),
(288,'robinson',1,1),
(289,'mark',1,1),
(290,'9780714649023',1,1),
(291,'francis',1,1),
(292,'inc',1,1),
(293,'articles',1,1),
(294,'assembled',1,1),
(295,'volume',1,1),
(296,'offer',1,1),
(297,'fresh',1,1),
(298,'approach',1,1),
(299,'analysing',1,1),
(300,'problem',1,1),
(301,'developing',1,1),
(302,'countries',1,1),
(303,'means',1,1),
(304,'tackle',1,1),
(305,'phenomenon',1,1),
(306,'anti',2,2),
(307,'campaigns',2,2),
(308,'bracking',1,1),
(309,'sarah',1,1),
(310,'0230525504',1,1),
(311,'palgrave',1,1),
(312,'macmillan',1,1),
(313,'provides',1,1),
(314,'multidisciplinary',1,1),
(315,'interrogation',1,1),
(316,'last',1,1),
(317,'ten',1,1),
(318,'years',1,1),
(319,'arguing',1,1),
(320,'while',1,1),
(321,'some',1,1),
(322,'positive',1,1),
(323,'change',1,1),
(324,'observable',1,1),
(325,'period',1,1),
(326,'also',1,1),
(327,'replete',1,1),
(328,'perverse',1,1),
(329,'consequences',1,1),
(330,'unintended',1,1),
(331,'outcomes',1,1),
(332,'pigs',1,1),
(333,'trough',1,1),
(334,'corporate',1,1),
(335,'greed',1,1),
(336,'political',1,1),
(337,'undermining',1,1),
(338,'america',1,1),
(339,'huffington',1,1),
(340,'arianna',1,1),
(341,'stassinopoulos',1,1),
(342,'1400047714',1,1),
(343,'crown',1,1),
(344,'publishers',1,1),
(345,'lords',2,2),
(346,'poverty',3,3),
(347,'power',1,1),
(348,'prestige',1,1),
(349,'international',1,1),
(350,'aid',3,2),
(351,'business',1,1),
(352,'hancock',1,1),
(353,'graham',1,1),
(354,'9780871134691',1,1),
(355,'atlantic',1,1),
(356,'monthly',1,1),
(357,'press',1,1),
(358,'case',1,1),
(359,'study',1,1),
(360,'betrayals',1,1),
(361,'trust',1,1),
(362,'shortcomings',1,1),
(363,'numerous',1,1),
(364,'serious',1,1),
(365,'enough',1,1),
(366,'raise',1,1),
(367,'questions',1,1),
(368,'viability',1,1),
(369,'practice',1,1),
(370,'fundamental',1,1),
(371,'levels',1,1),
(372,'hancocks',1,1),
(373,'report',1,1),
(374,'deeply',1,1),
(375,'shocking',1,1),
(376,'certain',1,1),
(377,'cause',1,1),
(378,'reevaluation',1,1),
(379,'governments',1,1),
(380,'motives',1,1),
(381,'giving',1,1),
(382,'foreign',1,1),
(383,'true',1,1),
(384,'needs',1,1),
(385,'our',1,1),
(386,'intended',1,1),
(387,'beneficiaries',1,1),
(388,'Под',2,2),
(389,'игото',2,2),
(390,'Иван',2,2),
(391,'Вазов',2,2),
(392,'Българска',19,19),
(393,'литература',29,29),
(394,'Класика',9,9),
(395,'Роман',13,13),
(396,'9789541805123',1,1),
(397,'Народна',5,5),
(398,'култура',4,4),
(399,'Немили',1,1),
(400,'недраги',1,1),
(401,'Повест',4,4),
(402,'9789544712045',1,1),
(403,'Български',8,8),
(404,'писател',5,5),
(405,'Разкази',4,4),
(406,'Елин',2,2),
(407,'Пелин',2,2),
(408,'9789544712121',1,1),
(409,'Гераците',1,1),
(410,'9789540907777',1,1),
(411,'Захарий',4,4),
(412,'Стоянов',4,4),
(413,'Старопланински',1,1),
(414,'легенди',1,1),
(415,'Йордан',2,2),
(416,'Йовков',2,2),
(417,'9789542608811',1,1),
(418,'Хермес',2,2),
(419,'Чифликът',1,1),
(420,'край',3,3),
(421,'границата',1,1),
(422,'9789542812345',1,1),
(423,'Сиела',1,1),
(424,'Тютюн',1,1),
(425,'Димитър',1,1),
(426,'Димов',1,1),
(427,'9789541805567',1,1),
(428,'Антихрист',1,1),
(429,'Емилиян',2,2),
(430,'Станев',2,2),
(431,'9789544712900',1,1),
(432,'Крадецът',1,1),
(433,'праскови',1,1),
(434,'9789540908888',1,1),
(435,'Време',1,1),
(436,'разделно',1,1),
(437,'Антон',1,1),
(438,'Дончев',1,1),
(439,'История',10,10),
(440,'България',11,11),
(441,'9789540901234',1,1),
(442,'Физика',4,4),
(443,'тъгата',1,1),
(444,'Георги',2,2),
(445,'Господинов',2,2),
(446,'9789544912223',1,1),
(447,'Жанет',2,2),
(448,'Естествен',1,1),
(449,'9789544911111',1,1),
(450,'Хайка',1,1),
(451,'вълци',1,1),
(452,'Ивайло',1,1),
(453,'Петров',2,2),
(454,'9789545291234',1,1),
(455,'Колибри',4,4),
(456,'Нощем',1,1),
(457,'белите',1,1),
(458,'коне',1,1),
(459,'Павел',2,2),
(460,'Вежинов',2,2),
(461,'9789544713333',1,1),
(462,'Бариерата',1,1),
(463,'9789542609999',1,1),
(464,'Стихотворения',2,2),
(465,'Христо',1,1),
(466,'Ботев',1,1),
(467,'Поезия',5,5),
(468,'9789541806000',1,1),
(469,'Безсъници',1,1),
(470,'Пейо',1,1),
(471,'Яворов',1,1),
(472,'9789544714000',1,1),
(473,'Димчо',1,1),
(474,'Дебелянов',1,1),
(475,'9789540902222',1,1),
(476,'Пътувания',1,1),
(477,'Блага',1,1),
(478,'Димитрова',1,1),
(479,'Пътепис',1,1),
(480,'9789545292345',1,1),
(481,'Бай',1,1),
(482,'Ганьо',1,1),
(483,'Алеко',1,1),
(484,'Константинов',1,1),
(485,'9789541806345',1,1),
(486,'Дядовата',1,1),
(487,'ръкавичка',1,1),
(488,'Ран',2,2),
(489,'Босилек',2,2),
(490,'Детска',11,11),
(491,'Приказки',4,4),
(492,'9789542720011',1,1),
(493,'Фют',2,2),
(494,'Патиланско',1,1),
(495,'царство',1,1),
(496,'9789546600022',1,1),
(497,'Пан',5,5),
(498,'Пипи',1,1),
(499,'Дългото',1,1),
(500,'чорапче',1,1),
(501,'Астрид',2,2),
(502,'Линдгрен',2,2),
(503,'9789546601111',1,1),
(504,'Емил',1,1),
(505,'Льонеберя',1,1),
(506,'9789546602222',1,1),
(507,'Хари',2,2),
(508,'Потър',2,2),
(509,'Философският',1,1),
(510,'камък',1,1),
(511,'Джоан',2,2),
(512,'Роулинг',2,2),
(513,'Фентъзи',3,3),
(514,'9789542704455',1,1),
(515,'Егмонт',3,3),
(516,'Стаята',1,1),
(517,'тайните',1,1),
(518,'9789542704462',1,1),
(519,'Хобитът',1,1),
(520,'Джон',1,1),
(521,'Толкин',1,1),
(522,'9789542705566',1,1),
(523,'Малкият',1,1),
(524,'принц',1,1),
(525,'Антоан',1,1),
(526,'дьо',1,1),
(527,'Сент',1,1),
(528,'Екзюпери',1,1),
(529,'9789545272233',1,1),
(530,'Дамян',1,1),
(531,'Яков',1,1),
(532,'Хвърчащата',1,1),
(533,'класна',1,1),
(534,'стая',1,1),
(535,'Ерих',1,1),
(536,'Кестнер',1,1),
(537,'9789546603333',1,1),
(538,'Приключенията',1,1),
(539,'Том',1,1),
(540,'Сойер',1,1),
(541,'Марк',1,1),
(542,'Твен',1,1),
(543,'9789546604444',1,1),
(544,'Бяла',1,1),
(545,'приказка',1,1),
(546,'Валери',1,1),
(547,'9789542720099',1,1),
(548,'Математика',2,2),
(549,'клас',6,6),
(550,'Колектив',21,21),
(551,'9789540136001',1,1),
(552,'Просвета',6,6),
(553,'език',5,5),
(554,'Чужд',3,3),
(555,'9789540136018',1,1),
(556,'цивилизации',1,1),
(557,'9789540135025',1,1),
(558,'Биология',2,2),
(559,'здравно',1,1),
(560,'образование',1,1),
(561,'9789540137032',1,1),
(562,'астрономия',1,1),
(563,'9789540138049',1,1),
(564,'География',3,3),
(565,'икономика',1,1),
(566,'9789540137056',1,1),
(567,'тълковен',1,1),
(568,'речник',6,6),
(569,'9789540200011',1,1),
(570,'Правописен',1,1),
(571,'българския',1,1),
(572,'9789548283022',1,1),
(573,'Английско',1,1),
(574,'9789545287733',1,1),
(575,'Труд',3,3),
(576,'Енциклопедия',2,2),
(577,'9789545280044',1,1),
(578,'Атлас',1,1),
(579,'света',1,1),
(580,'9789545289955',1,1),
(581,'9786191524001',1,1),
(582,'Изток',3,3),
(583,'Запад',3,3),
(584,'Философия',2,2),
(585,'Въведение',1,1),
(586,'9786191523018',1,1),
(587,'Кратка',1,1),
(588,'времето',1,1),
(589,'9786191524025',1,1),
(590,'родния',1,1),
(591,'Краезнание',5,5),
(592,'9786192660011',1,1),
(593,'Асеневци',4,4),
(594,'Народни',1,1),
(595,'песни',1,1),
(596,'нашия',1,1),
(597,'9786192660028',1,1),
(598,'old',1,1),
(599,'man',1,1),
(600,'sea',1,1),
(601,'9780684801223',1,1),
(602,'Престъпление',1,1),
(603,'наказание',1,1),
(604,'Фьодор',1,1),
(605,'Достоевски',1,1),
(606,'9789545293456',1,1),
(607,'Сто',1,1),
(608,'години',1,1),
(609,'самота',1,1),
(610,'Габриел',1,1),
(611,'Гарсия',1,1),
(612,'Маркес',1,1),
(613,'9789548227667',1,1),
(614,'Списание',2,2),
(615,'Читалище',1,1),
(616,'2025',2,2),
(617,'Родознание',1,1),
(618,'dvd',1,1),
(619,'игрален',1,1),
(620,'филм',1,1),
(621,'Драма',1,1),
(622,'музика',1,1);
/*!40000 ALTER TABLE `index_words` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `item`
--

DROP TABLE IF EXISTS `item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `item` (
  `item_id` int(11) NOT NULL AUTO_INCREMENT,
  `biblio_id` int(11) DEFAULT NULL,
  `call_number` varchar(50) DEFAULT NULL,
  `coll_type_id` int(3) DEFAULT NULL,
  `item_code` varchar(20) DEFAULT NULL,
  `inventory_code` varchar(200) DEFAULT NULL,
  `received_date` date DEFAULT NULL,
  `supplier_id` varchar(6) DEFAULT NULL,
  `order_no` varchar(20) DEFAULT NULL,
  `location_id` varchar(3) DEFAULT NULL,
  `order_date` date DEFAULT NULL,
  `item_status_id` char(3) DEFAULT NULL,
  `site` varchar(50) DEFAULT NULL,
  `source` int(1) NOT NULL DEFAULT 0,
  `invoice` varchar(20) DEFAULT NULL,
  `price` int(11) DEFAULT NULL,
  `price_currency` varchar(10) DEFAULT NULL,
  `invoice_date` date DEFAULT NULL,
  `input_date` datetime NOT NULL,
  `last_update` datetime DEFAULT NULL,
  `uid` int(11) DEFAULT NULL,
  PRIMARY KEY (`item_id`),
  UNIQUE KEY `item_code` (`item_code`),
  KEY `uid` (`uid`),
  KEY `item_references_idx` (`coll_type_id`,`location_id`,`item_status_id`),
  KEY `biblio_id_idx` (`biblio_id`)
) ENGINE=MyISAM AUTO_INCREMENT=109 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `item`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `item` WRITE;
/*!40000 ALTER TABLE `item` DISABLE KEYS */;
INSERT INTO `item` VALUES
(1,8,NULL,1,'B00001','INV/B00001',NULL,'0','','SL',NULL,'0','',1,'',500000,'Rupiah',NULL,'2008-12-26 22:11:10','2008-12-26 22:14:13',NULL),
(2,6,NULL,1,'B00002','INV/B00002',NULL,'0','','SL',NULL,'0','',1,'',700000,'Rupiah',NULL,'2008-12-26 22:11:45','2008-12-26 22:13:45',NULL),
(3,15,NULL,1,'B00003','INV/B00003',NULL,'0','','SL',NULL,'0','',1,'',300000,'Rupiah',NULL,'2008-12-26 22:15:09','2008-12-26 22:15:09',NULL),
(4,14,NULL,1,'B00004','INV/B00004',NULL,'0','','SL',NULL,'0','',1,'',250000,'Rupiah',NULL,'2008-12-26 22:15:49','2008-12-26 22:15:49',NULL),
(5,13,NULL,1,'B00005','INV/B00005',NULL,'0','','SL',NULL,'0','',2,'',0,NULL,NULL,'2008-12-26 22:17:04','2008-12-26 22:17:04',NULL),
(6,12,NULL,1,'B00006','INV/B00006',NULL,'0','','SL',NULL,'0','',1,'',350000,'Rupiah',NULL,'2008-12-26 22:17:52','2008-12-26 22:17:52',NULL),
(7,4,NULL,1,'B00007','INV/B00007',NULL,'0','','SL',NULL,'0','',1,'',450000,'Rupiah',NULL,'2008-12-26 22:18:29','2008-12-26 22:18:29',NULL),
(8,4,NULL,1,'B00008','INV/B00008',NULL,'0','','SL',NULL,'0','',2,'',0,NULL,NULL,'2008-12-26 22:18:51','2008-12-26 22:18:51',NULL),
(9,2,NULL,1,'B00009','INV/B00009',NULL,'0','','SL',NULL,'0','',1,'',630000,'Rupiah',NULL,'2008-12-26 22:19:28','2008-12-26 22:19:28',NULL),
(10,2,NULL,1,'B00010','INV/B00010',NULL,'0','','SL',NULL,'0','',1,'',630000,'Rupiah',NULL,'2008-12-26 22:19:57','2008-12-26 22:19:57',NULL),
(11,16,'886.7-31 ВАЗ',4,'CHT-000101',NULL,NULL,NULL,NULL,'ZV',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(12,16,'886.7-31 ВАЗ',4,'CHT-000102',NULL,NULL,NULL,NULL,'ZV',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(13,16,'886.7-31 ВАЗ',4,'CHT-000103',NULL,NULL,NULL,NULL,'ZV',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(14,17,'886.7-32 ВАЗ',4,'CHT-000104',NULL,NULL,NULL,NULL,'ZV',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(15,17,'886.7-32 ВАЗ',4,'CHT-000105',NULL,NULL,NULL,NULL,'ZV',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(16,18,'886.7-32 ПЕЛ',4,'CHT-000106',NULL,NULL,NULL,NULL,'ZV',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(17,18,'886.7-32 ПЕЛ',4,'CHT-000107',NULL,NULL,NULL,NULL,'ZV',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(18,19,'886.7-31 ПЕЛ',4,'CHT-000108',NULL,NULL,NULL,NULL,'KH',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(19,20,'886.7-32 ЙОВ',4,'CHT-000109',NULL,NULL,NULL,NULL,'ZV',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(20,20,'886.7-32 ЙОВ',4,'CHT-000110',NULL,NULL,NULL,NULL,'ZV',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(21,21,'886.7-31 ЙОВ',4,'CHT-000111',NULL,NULL,NULL,NULL,'ZV',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(22,22,'886.7-31 ДИМ',4,'CHT-000112',NULL,NULL,NULL,NULL,'ZV',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(23,22,'886.7-31 ДИМ',4,'CHT-000113',NULL,NULL,NULL,NULL,'ZV',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(24,22,'886.7-31 ДИМ',4,'CHT-000114',NULL,NULL,NULL,NULL,'ZV',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(25,23,'886.7-31 СТА',4,'CHT-000115',NULL,NULL,NULL,NULL,'KH',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(26,24,'886.7-32 СТА',4,'CHT-000116',NULL,NULL,NULL,NULL,'ZV',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(27,24,'886.7-32 СТА',4,'CHT-000117',NULL,NULL,NULL,NULL,'ZV',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(28,25,'886.7-31 ДОН',4,'CHT-000118',NULL,NULL,NULL,NULL,'ZV',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(29,25,'886.7-31 ДОН',4,'CHT-000119',NULL,NULL,NULL,NULL,'ZV',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(30,26,'886.7-31 ГОС',4,'CHT-000120',NULL,NULL,NULL,NULL,'ZV',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(31,26,'886.7-31 ГОС',4,'CHT-000121',NULL,NULL,NULL,NULL,'ZV',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(32,27,'886.7-31 ГОС',4,'CHT-000122',NULL,NULL,NULL,NULL,'ZV',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(33,28,'886.7-31 ПЕТ',4,'CHT-000123',NULL,NULL,NULL,NULL,'KH',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(34,29,'886.7-31 ВЕЖ',4,'CHT-000124',NULL,NULL,NULL,NULL,'ZV',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(35,30,'886.7-32 ВЕЖ',4,'CHT-000125',NULL,NULL,NULL,NULL,'ZV',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(36,30,'886.7-32 ВЕЖ',4,'CHT-000126',NULL,NULL,NULL,NULL,'ZV',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(37,31,'886.7-1 БОТ',4,'CHT-000127',NULL,NULL,NULL,NULL,'ZV',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(38,31,'886.7-1 БОТ',4,'CHT-000128',NULL,NULL,NULL,NULL,'ZV',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(39,32,'886.7-1 ЯВО',4,'CHT-000129',NULL,NULL,NULL,NULL,'ZV',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(40,33,'886.7-1 ДЕБ',4,'CHT-000130',NULL,NULL,NULL,NULL,'ZV',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(41,34,'886.7-1 ДИМ',4,'CHT-000131',NULL,NULL,NULL,NULL,'ZV',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(42,35,'886.7-32 КОН',4,'CHT-000132',NULL,NULL,NULL,NULL,'ZV',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(43,35,'886.7-32 КОН',4,'CHT-000133',NULL,NULL,NULL,NULL,'ZV',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(44,36,'087.5 БОС',5,'CHT-000134',NULL,NULL,NULL,NULL,'DO',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(45,36,'087.5 БОС',5,'CHT-000135',NULL,NULL,NULL,NULL,'DO',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(46,36,'087.5 БОС',5,'CHT-000136',NULL,NULL,NULL,NULL,'DO',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(47,37,'087.5 БОС',5,'CHT-000137',NULL,NULL,NULL,NULL,'DO',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(48,37,'087.5 БОС',5,'CHT-000138',NULL,NULL,NULL,NULL,'DO',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(49,38,'839.7-93 ЛИН',5,'CHT-000139',NULL,NULL,NULL,NULL,'DO',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(50,38,'839.7-93 ЛИН',5,'CHT-000140',NULL,NULL,NULL,NULL,'DO',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(51,39,'839.7-93 ЛИН',5,'CHT-000141',NULL,NULL,NULL,NULL,'DO',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(52,39,'839.7-93 ЛИН',5,'CHT-000142',NULL,NULL,NULL,NULL,'DO',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(53,40,'820-93 РОУ',5,'CHT-000143',NULL,NULL,NULL,NULL,'DO',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(54,40,'820-93 РОУ',5,'CHT-000144',NULL,NULL,NULL,NULL,'DO',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(55,40,'820-93 РОУ',5,'CHT-000145',NULL,NULL,NULL,NULL,'DO',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(56,41,'820-93 РОУ',5,'CHT-000146',NULL,NULL,NULL,NULL,'DO',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(57,41,'820-93 РОУ',5,'CHT-000147',NULL,NULL,NULL,NULL,'DO',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(58,42,'820-93 ТОЛ',5,'CHT-000148',NULL,NULL,NULL,NULL,'DO',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(59,42,'820-93 ТОЛ',5,'CHT-000149',NULL,NULL,NULL,NULL,'DO',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(60,43,'840-93 СЕН',5,'CHT-000150',NULL,NULL,NULL,NULL,'DO',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(61,43,'840-93 СЕН',5,'CHT-000151',NULL,NULL,NULL,NULL,'DO',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(62,43,'840-93 СЕН',5,'CHT-000152',NULL,NULL,NULL,NULL,'DO',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(63,44,'830-93 КЕС',5,'CHT-000153',NULL,NULL,NULL,NULL,'DO',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(64,45,'820(73)-93 ТВЕ',5,'CHT-000154',NULL,NULL,NULL,NULL,'DO',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(65,45,'820(73)-93 ТВЕ',5,'CHT-000155',NULL,NULL,NULL,NULL,'DO',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(66,46,'886.7-93 ПЕТ',5,'CHT-000156',NULL,NULL,NULL,NULL,'DO',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(67,46,'886.7-93 ПЕТ',5,'CHT-000157',NULL,NULL,NULL,NULL,'DO',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(68,47,'51(075.3) КОЛ',6,'CHT-000158',NULL,NULL,NULL,NULL,'ZV',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(69,47,'51(075.3) КОЛ',6,'CHT-000159',NULL,NULL,NULL,NULL,'ZV',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(70,47,'51(075.3) КОЛ',6,'CHT-000160',NULL,NULL,NULL,NULL,'ZV',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(71,47,'51(075.3) КОЛ',6,'CHT-000161',NULL,NULL,NULL,NULL,'ZV',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(72,48,'808.67(075.3) КОЛ',6,'CHT-000162',NULL,NULL,NULL,NULL,'ZV',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(73,48,'808.67(075.3) КОЛ',6,'CHT-000163',NULL,NULL,NULL,NULL,'ZV',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(74,48,'808.67(075.3) КОЛ',6,'CHT-000164',NULL,NULL,NULL,NULL,'ZV',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(75,48,'808.67(075.3) КОЛ',6,'CHT-000165',NULL,NULL,NULL,NULL,'ZV',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(76,49,'93(075.3) КОЛ',6,'CHT-000166',NULL,NULL,NULL,NULL,'ZV',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(77,49,'93(075.3) КОЛ',6,'CHT-000167',NULL,NULL,NULL,NULL,'ZV',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(78,49,'93(075.3) КОЛ',6,'CHT-000168',NULL,NULL,NULL,NULL,'ZV',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(79,50,'57(075.3) КОЛ',6,'CHT-000169',NULL,NULL,NULL,NULL,'ZV',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(80,50,'57(075.3) КОЛ',6,'CHT-000170',NULL,NULL,NULL,NULL,'ZV',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(81,50,'57(075.3) КОЛ',6,'CHT-000171',NULL,NULL,NULL,NULL,'ZV',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(82,51,'53(075.3) КОЛ',6,'CHT-000172',NULL,NULL,NULL,NULL,'ZV',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(83,51,'53(075.3) КОЛ',6,'CHT-000173',NULL,NULL,NULL,NULL,'ZV',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(84,51,'53(075.3) КОЛ',6,'CHT-000174',NULL,NULL,NULL,NULL,'ZV',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(85,52,'91(075.3) КОЛ',6,'CHT-000175',NULL,NULL,NULL,NULL,'ZV',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(86,52,'91(075.3) КОЛ',6,'CHT-000176',NULL,NULL,NULL,NULL,'ZV',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(87,53,'808.67(038) КОЛ',7,'CHT-000177',NULL,NULL,NULL,NULL,'CH',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(88,53,'808.67(038) КОЛ',7,'CHT-000178',NULL,NULL,NULL,NULL,'CH',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(89,54,'808.67(038) КОЛ',7,'CHT-000179',NULL,NULL,NULL,NULL,'CH',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(90,54,'808.67(038) КОЛ',7,'CHT-000180',NULL,NULL,NULL,NULL,'CH',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(91,55,'802.0-3(038) КОЛ',7,'CHT-000181',NULL,NULL,NULL,NULL,'CH',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(92,55,'802.0-3(038) КОЛ',7,'CHT-000182',NULL,NULL,NULL,NULL,'CH',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(93,56,'030(497.2) КОЛ',7,'CHT-000183',NULL,NULL,NULL,NULL,'CH',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(94,57,'912(100) КОЛ',7,'CHT-000184',NULL,NULL,NULL,NULL,'CH',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(95,58,'949.72 КОЛ',8,'CHT-000185',NULL,NULL,NULL,NULL,'KH',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(96,58,'949.72 КОЛ',8,'CHT-000186',NULL,NULL,NULL,NULL,'KH',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(97,59,'1(075) КОЛ',8,'CHT-000187',NULL,NULL,NULL,NULL,'KH',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(98,60,'52 КОЛ',8,'CHT-000188',NULL,NULL,NULL,NULL,'KH',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(99,61,'908(497.2) КОЛ',9,'CHT-000189',NULL,NULL,NULL,NULL,'KR',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(100,62,'398.8(497.2) КОЛ',9,'CHT-000190',NULL,NULL,NULL,NULL,'KR',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(101,63,'820(73)-31 КОЛ',11,'CHT-000191',NULL,NULL,NULL,NULL,'ZV',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(102,64,'882-31 ДОС',11,'CHT-000192',NULL,NULL,NULL,NULL,'ZV',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(103,64,'882-31 ДОС',11,'CHT-000193',NULL,NULL,NULL,NULL,'ZV',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(104,65,'860(861)-31 МАР',11,'CHT-000194',NULL,NULL,NULL,NULL,'ZV',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(105,66,'05 КОЛ',10,'CHT-000195',NULL,NULL,NULL,NULL,'CH',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(106,67,'05 КОЛ',10,'CHT-000196',NULL,NULL,NULL,NULL,'CH',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(107,68,'791.4 КОЛ',12,'CHT-000197',NULL,NULL,NULL,NULL,'CH',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL),
(108,69,'78(497.2) КОЛ',12,'CHT-000198',NULL,NULL,NULL,NULL,'CH',NULL,'',NULL,0,NULL,NULL,NULL,NULL,'2026-08-10 00:00:00','2026-08-10 00:00:00',NULL);
/*!40000 ALTER TABLE `item` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `item_custom`
--

DROP TABLE IF EXISTS `item_custom`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `item_custom` (
  `item_id` int(11) NOT NULL,
  PRIMARY KEY (`item_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci COMMENT='one to one relation with real item table';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `item_custom`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `item_custom` WRITE;
/*!40000 ALTER TABLE `item_custom` DISABLE KEYS */;
/*!40000 ALTER TABLE `item_custom` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `kardex`
--

DROP TABLE IF EXISTS `kardex`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `kardex` (
  `kardex_id` int(11) NOT NULL AUTO_INCREMENT,
  `date_expected` date NOT NULL,
  `date_received` date DEFAULT NULL,
  `seq_number` varchar(25) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `serial_id` int(11) DEFAULT NULL,
  `input_date` date NOT NULL,
  `last_update` date NOT NULL,
  PRIMARY KEY (`kardex_id`),
  KEY `fk_serial` (`serial_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kardex`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `kardex` WRITE;
/*!40000 ALTER TABLE `kardex` DISABLE KEYS */;
/*!40000 ALTER TABLE `kardex` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `loan`
--

DROP TABLE IF EXISTS `loan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `loan` (
  `loan_id` int(11) NOT NULL AUTO_INCREMENT,
  `item_code` varchar(20) DEFAULT NULL,
  `member_id` varchar(20) DEFAULT NULL,
  `loan_date` date NOT NULL,
  `due_date` date NOT NULL,
  `renewed` int(11) NOT NULL DEFAULT 0,
  `loan_rules_id` int(11) NOT NULL DEFAULT 0,
  `actual` date DEFAULT NULL,
  `is_lent` int(11) NOT NULL DEFAULT 0,
  `is_return` int(11) NOT NULL DEFAULT 0,
  `return_date` date DEFAULT NULL,
  `input_date` datetime DEFAULT NULL,
  `last_update` datetime DEFAULT NULL,
  `uid` int(11) DEFAULT NULL,
  PRIMARY KEY (`loan_id`),
  KEY `item_code` (`item_code`),
  KEY `member_id` (`member_id`),
  KEY `input_date` (`input_date`,`last_update`,`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `loan`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `loan` WRITE;
/*!40000 ALTER TABLE `loan` DISABLE KEYS */;
/*!40000 ALTER TABLE `loan` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `loan_history`
--

DROP TABLE IF EXISTS `loan_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `loan_history` (
  `loan_id` int(11) NOT NULL,
  `item_code` varchar(20) DEFAULT NULL,
  `biblio_id` int(11) NOT NULL,
  `title` varchar(300) DEFAULT NULL,
  `call_number` varchar(50) DEFAULT NULL,
  `classification` varchar(40) DEFAULT NULL,
  `gmd_name` varchar(30) DEFAULT NULL,
  `language_name` varchar(20) DEFAULT NULL,
  `location_name` varchar(100) DEFAULT NULL,
  `collection_type_name` varchar(100) DEFAULT NULL,
  `member_id` varchar(20) DEFAULT NULL,
  `member_name` varchar(100) DEFAULT NULL,
  `member_type_name` varchar(64) DEFAULT NULL,
  `loan_date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `renewed` int(11) NOT NULL DEFAULT 0,
  `is_lent` int(11) NOT NULL DEFAULT 0,
  `is_return` int(11) NOT NULL DEFAULT 0,
  `return_date` date DEFAULT NULL,
  `input_date` datetime DEFAULT NULL,
  `last_update` datetime DEFAULT NULL,
  PRIMARY KEY (`loan_id`),
  KEY `member_name` (`member_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `loan_history`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `loan_history` WRITE;
/*!40000 ALTER TABLE `loan_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `loan_history` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `member`
--

DROP TABLE IF EXISTS `member`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `member` (
  `member_id` varchar(20) NOT NULL,
  `member_name` varchar(100) NOT NULL,
  `gender` int(1) NOT NULL,
  `birth_date` date DEFAULT NULL,
  `member_type_id` int(6) DEFAULT NULL,
  `member_address` varchar(255) DEFAULT NULL,
  `member_mail_address` varchar(255) DEFAULT NULL,
  `member_email` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `inst_name` varchar(100) DEFAULT NULL,
  `is_new` int(1) DEFAULT NULL,
  `member_image` varchar(200) DEFAULT NULL,
  `pin` varchar(50) DEFAULT NULL,
  `member_phone` varchar(50) DEFAULT NULL,
  `member_fax` varchar(50) DEFAULT NULL,
  `member_since_date` date DEFAULT NULL,
  `register_date` date DEFAULT NULL,
  `expire_date` date NOT NULL,
  `member_notes` text DEFAULT NULL,
  `is_pending` smallint(1) NOT NULL DEFAULT 0,
  `mpasswd` varchar(64) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `last_login_ip` varchar(50) DEFAULT NULL,
  `input_date` date DEFAULT NULL,
  `last_update` date DEFAULT NULL,
  PRIMARY KEY (`member_id`),
  KEY `member_name` (`member_name`),
  KEY `member_type_id` (`member_type_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `member`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `member` WRITE;
/*!40000 ALTER TABLE `member` DISABLE KEYS */;
INSERT INTO `member` VALUES
('11322','Тест Мембер',1,'2007-11-01',1,'','','','','',NULL,NULL,'','','','2026-08-11','2026-08-11','2027-08-11','',0,NULL,NULL,NULL,'2026-08-11','2026-08-11');
/*!40000 ALTER TABLE `member` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `member_custom`
--

DROP TABLE IF EXISTS `member_custom`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `member_custom` (
  `member_id` varchar(20) NOT NULL,
  PRIMARY KEY (`member_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci COMMENT='one to one relation with real member table';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `member_custom`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `member_custom` WRITE;
/*!40000 ALTER TABLE `member_custom` DISABLE KEYS */;
/*!40000 ALTER TABLE `member_custom` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `mst_author`
--

DROP TABLE IF EXISTS `mst_author`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_author` (
  `author_id` int(11) NOT NULL AUTO_INCREMENT,
  `author_name` varchar(100) NOT NULL,
  `author_year` varchar(20) DEFAULT NULL,
  `authority_type` enum('p','o','c') DEFAULT 'p',
  `auth_list` varchar(20) DEFAULT NULL,
  `input_date` date NOT NULL,
  `last_update` date DEFAULT NULL,
  PRIMARY KEY (`author_id`),
  UNIQUE KEY `author_name` (`author_name`,`authority_type`)
) ENGINE=MyISAM AUTO_INCREMENT=52 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mst_author`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `mst_author` WRITE;
/*!40000 ALTER TABLE `mst_author` DISABLE KEYS */;
INSERT INTO `mst_author` VALUES
(1,'Valade, Janet',NULL,'p',NULL,'2007-11-29','2007-11-29'),
(2,'Siever, Ellen',NULL,'p',NULL,'2007-11-29','2007-11-29'),
(3,'Love, Robert',NULL,'p',NULL,'2007-11-29','2007-11-29'),
(4,'Robbins, Arnold',NULL,'p',NULL,'2007-11-29','2007-11-29'),
(5,'Figgins, Stephen',NULL,'p',NULL,'2007-11-29','2007-11-29'),
(6,'Weber, Aaron',NULL,'p',NULL,'2007-11-29','2007-11-29'),
(7,'Kofler, Michael',NULL,'p',NULL,'2007-11-29','2007-11-29'),
(8,'Kramer, David',NULL,'p',NULL,'2007-11-29','2007-11-29'),
(9,'Raymond, Eric',NULL,'p',NULL,'2007-11-29','2007-11-29'),
(10,'Fogel, Karl',NULL,'p',NULL,'2007-11-29','2007-11-29'),
(11,'Douglas, Korry',NULL,'p',NULL,'2007-11-29','2007-11-29'),
(12,'Douglas, Susan',NULL,'p',NULL,'2007-11-29','2007-11-29'),
(13,'Shklar, Leon',NULL,'p',NULL,'2007-11-29','2007-11-29'),
(14,'Rosen, Richard',NULL,'p',NULL,'2007-11-29','2007-11-29'),
(15,'Woychowsky, Edmond',NULL,'p',NULL,'2007-11-29','2007-11-29'),
(16,'Taylor, Arlene G.',NULL,'p',NULL,'2007-11-29','2007-11-29'),
(17,'Stueart, Robert D.',NULL,'p',NULL,'2007-11-29','2007-11-29'),
(18,'Moran, Barbara B.',NULL,'p',NULL,'2007-11-29','2007-11-29'),
(19,'Morville, Peter',NULL,'p',NULL,'2007-11-29','2007-11-29'),
(20,'Rosenfeld, Louis',NULL,'p',NULL,'2007-11-29','2007-11-29'),
(21,'Robinson, Mark',NULL,'p',NULL,'2007-11-29','2007-11-29'),
(22,'Bracking, Sarah',NULL,'p',NULL,'2007-11-29','2007-11-29'),
(23,'Huffington, Arianna Stassinopoulos',NULL,'p',NULL,'2007-11-29','2007-11-29'),
(24,'Hancock, Graham',NULL,'p',NULL,'2007-11-29','2007-11-29'),
(25,'Иван Вазов',NULL,'p',NULL,'2026-08-10',NULL),
(26,'Христо Ботев',NULL,'p',NULL,'2026-08-10',NULL),
(27,'Елин Пелин',NULL,'p',NULL,'2026-08-10',NULL),
(28,'Йордан Йовков',NULL,'p',NULL,'2026-08-10',NULL),
(29,'Пейо Яворов',NULL,'p',NULL,'2026-08-10',NULL),
(30,'Димчо Дебелянов',NULL,'p',NULL,'2026-08-10',NULL),
(31,'Алеко Константинов',NULL,'p',NULL,'2026-08-10',NULL),
(32,'Николай Хайтов',NULL,'p',NULL,'2026-08-10',NULL),
(33,'Ивайло Петров',NULL,'p',NULL,'2026-08-10',NULL),
(34,'Павел Вежинов',NULL,'p',NULL,'2026-08-10',NULL),
(35,'Блага Димитрова',NULL,'p',NULL,'2026-08-10',NULL),
(36,'Валери Петров',NULL,'p',NULL,'2026-08-10',NULL),
(37,'Емилиян Станев',NULL,'p',NULL,'2026-08-10',NULL),
(38,'Димитър Димов',NULL,'p',NULL,'2026-08-10',NULL),
(39,'Антон Дончев',NULL,'p',NULL,'2026-08-10',NULL),
(40,'Георги Господинов',NULL,'p',NULL,'2026-08-10',NULL),
(41,'Здравка Евтимова',NULL,'p',NULL,'2026-08-10',NULL),
(42,'Ран Босилек',NULL,'p',NULL,'2026-08-10',NULL),
(43,'Астрид Линдгрен',NULL,'p',NULL,'2026-08-10',NULL),
(44,'Джоан Роулинг',NULL,'p',NULL,'2026-08-10',NULL),
(45,'Джон Р. Р. Толкин',NULL,'p',NULL,'2026-08-10',NULL),
(46,'Антоан дьо Сент-Екзюпери',NULL,'p',NULL,'2026-08-10',NULL),
(47,'Ерих Кестнер',NULL,'p',NULL,'2026-08-10',NULL),
(48,'Марк Твен',NULL,'p',NULL,'2026-08-10',NULL),
(49,'Фьодор Достоевски',NULL,'p',NULL,'2026-08-10',NULL),
(50,'Габриел Гарсия Маркес',NULL,'p',NULL,'2026-08-10',NULL),
(51,'Колектив',NULL,'p',NULL,'2026-08-10',NULL);
/*!40000 ALTER TABLE `mst_author` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `mst_carrier_type`
--

DROP TABLE IF EXISTS `mst_carrier_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_carrier_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `carrier_type` varchar(100) NOT NULL,
  `code` varchar(5) NOT NULL,
  `code2` char(1) NOT NULL,
  `input_date` datetime NOT NULL,
  `last_update` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `media_type` (`carrier_type`),
  KEY `code` (`code`)
) ENGINE=MyISAM AUTO_INCREMENT=56 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mst_carrier_type`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `mst_carrier_type` WRITE;
/*!40000 ALTER TABLE `mst_carrier_type` DISABLE KEYS */;
INSERT INTO `mst_carrier_type` VALUES
(1,'audio cartridge','sg','g','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(2,'audio cylinder','se','e','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(3,'audio disc','sd','d','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(4,'sound track reel','si','i','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(5,'audio roll','sq','q','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(6,'audiocassette','ss','s','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(7,'audiotape reel','st','t','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(8,'other (audio)','sz','z','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(9,'computer card','ck','k','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(10,'computer chip cartridge','cb','b','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(11,'computer disc','cd','d','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(12,'computer disc cartridge','ce','e','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(13,'computer tape cartridge','ca','a','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(14,'computer tape cassette','cf','f','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(15,'computer tape reel','ch','h','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(16,'online resource','cr','r','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(17,'other (computer)','cz','z','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(18,'aperture card','ha','a','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(19,'microfiche','he','e','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(20,'microfiche cassette','hf','f','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(21,'microfilm cartridge','hb','b','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(22,'microfilm cassette','hc','c','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(23,'microfilm reel','hd','d','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(24,'microfilm roll','hj','j','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(25,'microfilm slip','hh','h','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(26,'microopaque','hg','g','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(27,'other (microform)','hz','z','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(28,'microscope slide','pp','p','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(29,'other (microscope)','pz','z','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(30,'film cartridge','mc','c','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(31,'film cassette','mf','f','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(32,'film reel','mr','r','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(33,'film roll','mo','o','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(34,'filmslip','gd','d','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(35,'filmstrip','gf','f','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(36,'filmstrip cartridge','gc','c','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(37,'overhead transparency','gt','t','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(38,'slide','gs','s','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(39,'other (projected image)','mz','z','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(40,'stereograph card','eh','h','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(41,'stereograph disc','es','s','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(42,'other (stereographic)','ez','z','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(43,'card','no','o','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(44,'flipchart','nn','n','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(45,'roll','na','a','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(46,'sheet','nb','b','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(47,'volume','nc','c','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(48,'object','nr','r','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(49,'other (unmediated)','nz','','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(50,'video cartridge','vc','','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(51,'videocassette','vf','','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(52,'videodisc','vd','','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(53,'videotape reel','vr','','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(54,'other (video)','vz','','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(55,'unspecified','zu','u','2026-08-10 18:09:33','2026-08-10 18:09:33');
/*!40000 ALTER TABLE `mst_carrier_type` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `mst_coll_type`
--

DROP TABLE IF EXISTS `mst_coll_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_coll_type` (
  `coll_type_id` int(3) NOT NULL AUTO_INCREMENT,
  `coll_type_name` varchar(30) NOT NULL,
  `input_date` date DEFAULT NULL,
  `last_update` date DEFAULT NULL,
  PRIMARY KEY (`coll_type_id`),
  UNIQUE KEY `coll_type_name` (`coll_type_name`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mst_coll_type`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `mst_coll_type` WRITE;
/*!40000 ALTER TABLE `mst_coll_type` DISABLE KEYS */;
INSERT INTO `mst_coll_type` VALUES
(1,'Reference','2007-11-29','2007-11-29'),
(2,'Textbook','2007-11-29','2007-11-29'),
(3,'Fiction','2007-11-29','2007-11-29'),
(4,'Художествена литература','2026-08-10',NULL),
(5,'Детска литература','2026-08-10',NULL),
(6,'Учебна литература','2026-08-10',NULL),
(7,'Справочна литература','2026-08-10',NULL),
(8,'Научна литература','2026-08-10',NULL),
(9,'Краезнание','2026-08-10',NULL),
(10,'Периодика','2026-08-10',NULL),
(11,'Чужди езици','2026-08-10',NULL),
(12,'Мултимедия','2026-08-10',NULL);
/*!40000 ALTER TABLE `mst_coll_type` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `mst_content_type`
--

DROP TABLE IF EXISTS `mst_content_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_content_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content_type` varchar(100) NOT NULL,
  `code` varchar(5) NOT NULL,
  `code2` char(1) NOT NULL,
  `input_date` datetime NOT NULL,
  `last_update` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `content_type` (`content_type`),
  KEY `code` (`code`)
) ENGINE=MyISAM AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mst_content_type`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `mst_content_type` WRITE;
/*!40000 ALTER TABLE `mst_content_type` DISABLE KEYS */;
INSERT INTO `mst_content_type` VALUES
(1,'cartographic dataset','crd','e','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(2,'cartographic image','cri','e','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(3,'cartographic moving image','crm','e','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(4,'cartographic tactile image','crt','e','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(5,'cartographic tactile three-dimensional form','crn','e','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(6,'cartographic three-dimensional form','crf','e','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(7,'computer dataset','cod','m','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(8,'computer program','cop','m','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(9,'notated movement','ntv','a','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(10,'notated music','ntm','c','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(11,'performed music','prm','j','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(12,'sounds','snd','i','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(13,'spoken word','spw','i','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(14,'still image','sti','k','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(15,'tactile image','tci','k','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(16,'tactile notated music','tcm','c','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(17,'tactile notated movement','tcn','a','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(18,'tactile text','tct','a','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(19,'tactile three-dimensional form','tcf','r','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(20,'text','txt','a','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(21,'three-dimensional form','tdf','r','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(22,'three-dimensional moving image','tdm','g','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(23,'two-dimensional moving image','tdi','g','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(24,'other','xxx','o','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(25,'unspecified','zzz','','2026-08-10 18:09:33','2026-08-10 18:09:33');
/*!40000 ALTER TABLE `mst_content_type` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `mst_custom_field`
--

DROP TABLE IF EXISTS `mst_custom_field`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_custom_field` (
  `field_id` int(11) NOT NULL AUTO_INCREMENT,
  `primary_table` varchar(100) DEFAULT NULL,
  `dbfield` varchar(50) NOT NULL,
  `label` varchar(80) NOT NULL,
  `type` enum('text','checklist','numeric','dropdown','longtext','choice','date') NOT NULL,
  `default` varchar(80) DEFAULT NULL,
  `max` int(11) DEFAULT NULL,
  `data` text DEFAULT NULL,
  `indexed` tinyint(1) DEFAULT NULL,
  `class` varchar(100) DEFAULT NULL,
  `is_public` tinyint(1) DEFAULT NULL,
  `width` int(5) DEFAULT 100,
  `note` text DEFAULT NULL,
  PRIMARY KEY (`dbfield`),
  UNIQUE KEY `field_id` (`field_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mst_custom_field`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `mst_custom_field` WRITE;
/*!40000 ALTER TABLE `mst_custom_field` DISABLE KEYS */;
/*!40000 ALTER TABLE `mst_custom_field` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `mst_frequency`
--

DROP TABLE IF EXISTS `mst_frequency`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_frequency` (
  `frequency_id` int(11) NOT NULL AUTO_INCREMENT,
  `frequency` varchar(25) NOT NULL,
  `language_prefix` varchar(5) DEFAULT NULL,
  `time_increment` smallint(6) DEFAULT NULL,
  `time_unit` enum('day','week','month','year') DEFAULT 'day',
  `input_date` date NOT NULL,
  `last_update` date NOT NULL,
  PRIMARY KEY (`frequency_id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mst_frequency`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `mst_frequency` WRITE;
/*!40000 ALTER TABLE `mst_frequency` DISABLE KEYS */;
INSERT INTO `mst_frequency` VALUES
(1,'Weekly','en',1,'week','2009-05-23','2009-05-23'),
(2,'Bi-weekly','en',2,'week','2009-05-23','2009-05-23'),
(3,'Fourth-Nightly','en',14,'day','2009-05-23','2009-05-23'),
(4,'Monthly','en',1,'month','2009-05-23','2009-05-23'),
(5,'Bi-Monthly','en',2,'month','2009-05-23','2009-05-23'),
(6,'Quarterly','en',3,'month','2009-05-23','2009-05-23'),
(7,'3 Times a Year','en',4,'month','2009-05-23','2009-05-23'),
(8,'Annualy','en',1,'year','2009-05-23','2009-05-23');
/*!40000 ALTER TABLE `mst_frequency` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `mst_gmd`
--

DROP TABLE IF EXISTS `mst_gmd`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_gmd` (
  `gmd_id` int(11) NOT NULL AUTO_INCREMENT,
  `gmd_code` varchar(3) DEFAULT NULL,
  `gmd_name` varchar(30) NOT NULL,
  `icon_image` varchar(100) DEFAULT NULL,
  `input_date` date NOT NULL,
  `last_update` date DEFAULT NULL,
  PRIMARY KEY (`gmd_id`),
  UNIQUE KEY `gmd_name` (`gmd_name`),
  UNIQUE KEY `gmd_code` (`gmd_code`)
) ENGINE=MyISAM AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mst_gmd`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `mst_gmd` WRITE;
/*!40000 ALTER TABLE `mst_gmd` DISABLE KEYS */;
INSERT INTO `mst_gmd` VALUES
(1,'TE','Text',NULL,'2026-08-10','2026-08-10'),
(2,'AR','Art Original',NULL,'2026-08-10','2026-08-10'),
(3,'CH','Chart',NULL,'2026-08-10','2026-08-10'),
(4,'CO','Computer Software',NULL,'2026-08-10','2026-08-10'),
(5,'DI','Diorama',NULL,'2026-08-10','2026-08-10'),
(6,'FI','Filmstrip',NULL,'2026-08-10','2026-08-10'),
(7,'FL','Flash Card',NULL,'2026-08-10','2026-08-10'),
(8,'GA','Game',NULL,'2026-08-10','2026-08-10'),
(9,'GL','Globe',NULL,'2026-08-10','2026-08-10'),
(10,'KI','Kit',NULL,'2026-08-10','2026-08-10'),
(11,'MA','Map',NULL,'2026-08-10','2026-08-10'),
(12,'MI','Microform',NULL,'2026-08-10','2026-08-10'),
(13,'MN','Manuscript',NULL,'2026-08-10','2026-08-10'),
(14,'MO','Model',NULL,'2026-08-10','2026-08-10'),
(15,'MP','Motion Picture',NULL,'2026-08-10','2026-08-10'),
(16,'MS','Microscope Slide',NULL,'2026-08-10','2026-08-10'),
(17,'MU','Music',NULL,'2026-08-10','2026-08-10'),
(18,'PI','Picture',NULL,'2026-08-10','2026-08-10'),
(19,'RE','Realia',NULL,'2026-08-10','2026-08-10'),
(20,'SL','Slide',NULL,'2026-08-10','2026-08-10'),
(21,'SO','Sound Recording',NULL,'2026-08-10','2026-08-10'),
(22,'TD','Technical Drawing',NULL,'2026-08-10','2026-08-10'),
(23,'TR','Transparency',NULL,'2026-08-10','2026-08-10'),
(24,'VI','Video Recording',NULL,'2026-08-10','2026-08-10'),
(25,'EQ','Equipment',NULL,'2026-08-10','2026-08-10'),
(26,'CF','Computer File',NULL,'2026-08-10','2026-08-10'),
(27,'CA','Cartographic Material',NULL,'2026-08-10','2026-08-10'),
(28,'CD','CD-ROM',NULL,'2026-08-10','2026-08-10'),
(29,'MV','Multimedia',NULL,'2026-08-10','2026-08-10'),
(30,'ER','Electronic Resource',NULL,'2026-08-10','2026-08-10'),
(31,'DVD','Digital Versatile Disc',NULL,'2026-08-10','2026-08-10');
/*!40000 ALTER TABLE `mst_gmd` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `mst_item_status`
--

DROP TABLE IF EXISTS `mst_item_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_item_status` (
  `item_status_id` char(3) NOT NULL,
  `item_status_name` varchar(30) NOT NULL,
  `rules` varchar(255) DEFAULT NULL,
  `no_loan` smallint(1) NOT NULL DEFAULT 0,
  `skip_stock_take` smallint(1) NOT NULL DEFAULT 0,
  `input_date` date DEFAULT NULL,
  `last_update` date DEFAULT NULL,
  PRIMARY KEY (`item_status_id`),
  UNIQUE KEY `item_status_name` (`item_status_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mst_item_status`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `mst_item_status` WRITE;
/*!40000 ALTER TABLE `mst_item_status` DISABLE KEYS */;
INSERT INTO `mst_item_status` VALUES
('R','Repair','a:1:{i:0;s:1:\"1\";}',1,0,'2026-08-10','2026-08-10'),
('NL','No Loan','a:1:{i:0;s:1:\"1\";}',1,0,'2026-08-10','2026-08-10'),
('MIS','Missing',NULL,1,1,'2026-08-10','2026-08-10');
/*!40000 ALTER TABLE `mst_item_status` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `mst_label`
--

DROP TABLE IF EXISTS `mst_label`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_label` (
  `label_id` int(11) NOT NULL AUTO_INCREMENT,
  `label_name` varchar(20) NOT NULL,
  `label_desc` varchar(50) DEFAULT NULL,
  `label_image` varchar(200) NOT NULL,
  `input_date` date NOT NULL,
  `last_update` date NOT NULL,
  PRIMARY KEY (`label_id`),
  UNIQUE KEY `label_name` (`label_name`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mst_label`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `mst_label` WRITE;
/*!40000 ALTER TABLE `mst_label` DISABLE KEYS */;
INSERT INTO `mst_label` VALUES
(1,'label-new','New Title','label-new.png','2026-08-10','2026-08-10'),
(2,'label-favorite','Favorite Title','label-favorite.png','2026-08-10','2026-08-10'),
(3,'label-multimedia','Multimedia','label-multimedia.png','2026-08-10','2026-08-10');
/*!40000 ALTER TABLE `mst_label` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `mst_language`
--

DROP TABLE IF EXISTS `mst_language`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_language` (
  `language_id` char(5) NOT NULL,
  `language_name` varchar(20) NOT NULL,
  `input_date` date DEFAULT NULL,
  `last_update` date DEFAULT NULL,
  PRIMARY KEY (`language_id`),
  UNIQUE KEY `language_name` (`language_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mst_language`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `mst_language` WRITE;
/*!40000 ALTER TABLE `mst_language` DISABLE KEYS */;
INSERT INTO `mst_language` VALUES
('id','Indonesia','2026-08-10','2026-08-10'),
('en','English','2026-08-10','2026-08-10'),
('es','Испански','2026-08-10',NULL),
('bg','Български','2026-08-10',NULL),
('ru','Руски','2026-08-10',NULL),
('de','Немски','2026-08-10',NULL),
('fr','Френски','2026-08-10',NULL);
/*!40000 ALTER TABLE `mst_language` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `mst_loan_rules`
--

DROP TABLE IF EXISTS `mst_loan_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_loan_rules` (
  `loan_rules_id` int(11) NOT NULL AUTO_INCREMENT,
  `member_type_id` int(11) NOT NULL DEFAULT 0,
  `coll_type_id` int(11) DEFAULT 0,
  `gmd_id` int(11) DEFAULT 0,
  `loan_limit` int(3) DEFAULT 0,
  `loan_periode` int(3) DEFAULT 0,
  `reborrow_limit` int(3) DEFAULT 0,
  `fine_each_day` int(3) DEFAULT 0,
  `grace_periode` int(2) DEFAULT 0,
  `input_date` date DEFAULT NULL,
  `last_update` date DEFAULT NULL,
  PRIMARY KEY (`loan_rules_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mst_loan_rules`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `mst_loan_rules` WRITE;
/*!40000 ALTER TABLE `mst_loan_rules` DISABLE KEYS */;
/*!40000 ALTER TABLE `mst_loan_rules` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `mst_location`
--

DROP TABLE IF EXISTS `mst_location`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_location` (
  `location_id` varchar(3) NOT NULL,
  `location_name` varchar(100) DEFAULT NULL,
  `input_date` date NOT NULL,
  `last_update` date NOT NULL,
  PRIMARY KEY (`location_id`),
  UNIQUE KEY `location_name` (`location_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mst_location`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `mst_location` WRITE;
/*!40000 ALTER TABLE `mst_location` DISABLE KEYS */;
INSERT INTO `mst_location` VALUES
('SL','My Library','2026-08-10','2026-08-10'),
('ZV','Заемна за възрастни','2026-08-10','2026-08-10'),
('DO','Детски отдел','2026-08-10','2026-08-10'),
('CH','Читалня','2026-08-10','2026-08-10'),
('KH','Книгохранилище','2026-08-10','2026-08-10'),
('KR','Краезнание','2026-08-10','2026-08-10');
/*!40000 ALTER TABLE `mst_location` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `mst_media_type`
--

DROP TABLE IF EXISTS `mst_media_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_media_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `media_type` varchar(100) NOT NULL,
  `code` varchar(5) NOT NULL,
  `code2` char(1) NOT NULL,
  `input_date` datetime NOT NULL,
  `last_update` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `media_type` (`media_type`),
  KEY `code` (`code`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mst_media_type`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `mst_media_type` WRITE;
/*!40000 ALTER TABLE `mst_media_type` DISABLE KEYS */;
INSERT INTO `mst_media_type` VALUES
(1,'audio','s','s','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(2,'computer','c','c','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(3,'microform','h','h','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(4,'microscopic','p','','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(5,'projected','g','g','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(6,'stereographic','e','','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(7,'unmediated','n','t','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(8,'video','v','v','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(9,'other','x','z','2026-08-10 18:09:33','2026-08-10 18:09:33'),
(10,'unspecified','z','z','2026-08-10 18:09:33','2026-08-10 18:09:33');
/*!40000 ALTER TABLE `mst_media_type` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `mst_member_type`
--

DROP TABLE IF EXISTS `mst_member_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_member_type` (
  `member_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `member_type_name` varchar(50) NOT NULL,
  `loan_limit` int(11) NOT NULL,
  `loan_periode` int(11) NOT NULL,
  `enable_reserve` int(1) NOT NULL DEFAULT 0,
  `reserve_limit` int(11) NOT NULL DEFAULT 0,
  `member_periode` int(11) NOT NULL,
  `reborrow_limit` int(11) NOT NULL,
  `fine_each_day` int(11) NOT NULL,
  `grace_periode` int(2) DEFAULT 0,
  `input_date` date NOT NULL,
  `last_update` date DEFAULT NULL,
  PRIMARY KEY (`member_type_id`),
  UNIQUE KEY `member_type_name` (`member_type_name`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mst_member_type`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `mst_member_type` WRITE;
/*!40000 ALTER TABLE `mst_member_type` DISABLE KEYS */;
INSERT INTO `mst_member_type` VALUES
(1,'Standard',2,7,1,2,365,1,0,0,'2026-08-10','2026-08-10');
/*!40000 ALTER TABLE `mst_member_type` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `mst_module`
--

DROP TABLE IF EXISTS `mst_module`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_module` (
  `module_id` int(3) NOT NULL AUTO_INCREMENT,
  `module_name` varchar(50) NOT NULL,
  `module_path` varchar(200) DEFAULT NULL,
  `module_desc` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`module_id`),
  UNIQUE KEY `module_name` (`module_name`,`module_path`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mst_module`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `mst_module` WRITE;
/*!40000 ALTER TABLE `mst_module` DISABLE KEYS */;
INSERT INTO `mst_module` VALUES
(1,'bibliography','bibliography','Manage your bibliographic/catalog and items/copies database'),
(2,'circulation','circulation','Module for doing library items circulation such as loan and return'),
(3,'membership','membership','Manage your library membership and membership type'),
(4,'master_file','master_file','Manage your referential data that will be used by other modules'),
(5,'stock_take','stock_take','Ease your pain in doing library stock opname process'),
(6,'system','system','Configure system behaviour, user and backups'),
(7,'reporting','reporting','Real time and dynamic report about library collections and circulation'),
(8,'serial_control','serial_control','Serial publication management');
/*!40000 ALTER TABLE `mst_module` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `mst_place`
--

DROP TABLE IF EXISTS `mst_place`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_place` (
  `place_id` int(11) NOT NULL AUTO_INCREMENT,
  `place_name` varchar(30) NOT NULL,
  `input_date` date DEFAULT NULL,
  `last_update` date DEFAULT NULL,
  PRIMARY KEY (`place_id`),
  UNIQUE KEY `place_name` (`place_name`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mst_place`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `mst_place` WRITE;
/*!40000 ALTER TABLE `mst_place` DISABLE KEYS */;
INSERT INTO `mst_place` VALUES
(1,'Hoboken, NJ','2007-11-29','2007-11-29'),
(2,'Sebastopol, CA','2007-11-29','2007-11-29'),
(3,'Indianapolis','2007-11-29','2007-11-29'),
(4,'Upper Saddle River, NJ','2007-11-29','2007-11-29'),
(5,'Westport, Conn.','2007-11-29','2007-11-29'),
(6,'Cambridge, Mass','2007-11-29','2007-11-29'),
(7,'London','2007-11-29','2007-11-29'),
(8,'New York','2007-11-29','2007-11-29'),
(9,'София','2026-08-10',NULL),
(10,'Пловдив','2026-08-10',NULL),
(11,'Варна','2026-08-10',NULL),
(12,'Велико Търново','2026-08-10',NULL),
(13,'Русе','2026-08-10',NULL);
/*!40000 ALTER TABLE `mst_place` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `mst_publisher`
--

DROP TABLE IF EXISTS `mst_publisher`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_publisher` (
  `publisher_id` int(11) NOT NULL AUTO_INCREMENT,
  `publisher_name` varchar(100) NOT NULL,
  `input_date` date DEFAULT NULL,
  `last_update` date DEFAULT NULL,
  PRIMARY KEY (`publisher_id`),
  UNIQUE KEY `publisher_name` (`publisher_name`)
) ENGINE=MyISAM AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mst_publisher`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `mst_publisher` WRITE;
/*!40000 ALTER TABLE `mst_publisher` DISABLE KEYS */;
INSERT INTO `mst_publisher` VALUES
(1,'Wiley','2007-11-29','2007-11-29'),
(2,'OReilly','2007-11-29','2007-11-29'),
(3,'Apress','2007-11-29','2007-11-29'),
(4,'Sams','2007-11-29','2007-11-29'),
(5,'John Wiley','2007-11-29','2007-11-29'),
(6,'Prentice Hall','2007-11-29','2007-11-29'),
(7,'Libraries Unlimited','2007-11-29','2007-11-29'),
(8,'Taylor & Francis Inc.','2007-11-29','2007-11-29'),
(9,'Palgrave Macmillan','2007-11-29','2007-11-29'),
(10,'Crown publishers','2007-11-29','2007-11-29'),
(11,'Atlantic Monthly Press','2007-11-29','2007-11-29'),
(12,'Народна култура','2026-08-10',NULL),
(13,'Български писател','2026-08-10',NULL),
(14,'Захарий Стоянов','2026-08-10',NULL),
(15,'Колибри','2026-08-10',NULL),
(16,'Сиела','2026-08-10',NULL),
(17,'Хермес','2026-08-10',NULL),
(18,'Изток-Запад','2026-08-10',NULL),
(19,'Труд','2026-08-10',NULL),
(20,'Просвета','2026-08-10',NULL),
(21,'Фют','2026-08-10',NULL),
(22,'Жанет 45','2026-08-10',NULL),
(23,'Дамян Яков','2026-08-10',NULL),
(24,'Егмонт България','2026-08-10',NULL),
(25,'Пан','2026-08-10',NULL),
(26,'Асеневци','2026-08-10',NULL);
/*!40000 ALTER TABLE `mst_publisher` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `mst_relation_term`
--

DROP TABLE IF EXISTS `mst_relation_term`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_relation_term` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `rt_id` varchar(11) NOT NULL,
  `rt_desc` varchar(50) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mst_relation_term`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `mst_relation_term` WRITE;
/*!40000 ALTER TABLE `mst_relation_term` DISABLE KEYS */;
INSERT INTO `mst_relation_term` VALUES
(1,'U','Use'),
(2,'UF','Use For'),
(3,'BT','Broader Term'),
(4,'NT','Narrower Term'),
(5,'RT','Related Term'),
(6,'SA','See Also');
/*!40000 ALTER TABLE `mst_relation_term` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `mst_servers`
--

DROP TABLE IF EXISTS `mst_servers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_servers` (
  `server_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `uri` text NOT NULL,
  `server_type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1 - p2p server; 2 - z3950; 3 - z3950  SRU',
  `input_date` datetime NOT NULL,
  `last_update` datetime DEFAULT NULL,
  PRIMARY KEY (`server_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mst_servers`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `mst_servers` WRITE;
/*!40000 ALTER TABLE `mst_servers` DISABLE KEYS */;
/*!40000 ALTER TABLE `mst_servers` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `mst_supplier`
--

DROP TABLE IF EXISTS `mst_supplier`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_supplier` (
  `supplier_id` int(11) NOT NULL AUTO_INCREMENT,
  `supplier_name` varchar(100) NOT NULL,
  `address` varchar(100) DEFAULT NULL,
  `postal_code` char(10) DEFAULT NULL,
  `phone` char(14) DEFAULT NULL,
  `contact` char(30) DEFAULT NULL,
  `fax` char(14) DEFAULT NULL,
  `account` char(12) DEFAULT NULL,
  `e_mail` char(80) DEFAULT NULL,
  `input_date` date DEFAULT NULL,
  `last_update` date DEFAULT NULL,
  PRIMARY KEY (`supplier_id`),
  UNIQUE KEY `supplier_name` (`supplier_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mst_supplier`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `mst_supplier` WRITE;
/*!40000 ALTER TABLE `mst_supplier` DISABLE KEYS */;
/*!40000 ALTER TABLE `mst_supplier` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `mst_topic`
--

DROP TABLE IF EXISTS `mst_topic`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_topic` (
  `topic_id` int(11) NOT NULL AUTO_INCREMENT,
  `topic` varchar(50) NOT NULL,
  `topic_type` enum('t','g','n','tm','gr','oc') NOT NULL,
  `auth_list` varchar(20) DEFAULT NULL,
  `classification` varchar(50) NOT NULL COMMENT 'Classification Code',
  `input_date` date DEFAULT NULL,
  `last_update` date DEFAULT NULL,
  PRIMARY KEY (`topic_id`),
  UNIQUE KEY `topic` (`topic`,`topic_type`)
) ENGINE=MyISAM AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mst_topic`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `mst_topic` WRITE;
/*!40000 ALTER TABLE `mst_topic` DISABLE KEYS */;
INSERT INTO `mst_topic` VALUES
(1,'Programming','t',NULL,'','2007-11-29','2007-11-29'),
(2,'Website','t',NULL,'','2007-11-29','2007-11-29'),
(3,'Operating System','t',NULL,'','2007-11-29','2007-11-29'),
(4,'Linux','t',NULL,'','2007-11-29','2007-11-29'),
(5,'Computer','t',NULL,'','2007-11-29','2007-11-29'),
(6,'Database','t',NULL,'','2007-11-29','2007-11-29'),
(7,'RDBMS','t',NULL,'','2007-11-29','2007-11-29'),
(8,'Open Source','t',NULL,'','2007-11-29','2007-11-29'),
(9,'Project','t',NULL,'','2007-11-29','2007-11-29'),
(10,'Design','t',NULL,'','2007-11-29','2007-11-29'),
(11,'Information','t',NULL,'','2007-11-29','2007-11-29'),
(12,'Organization','t',NULL,'','2007-11-29','2007-11-29'),
(13,'Metadata','t',NULL,'','2007-11-29','2007-11-29'),
(14,'Library','t',NULL,'','2007-11-29','2007-11-29'),
(15,'Corruption','t',NULL,'','2007-11-29','2007-11-29'),
(16,'Development','t',NULL,'','2007-11-29','2007-11-29'),
(17,'Poverty','t',NULL,'','2007-11-29','2007-11-29'),
(18,'Българска литература','t',NULL,'','2026-08-10',NULL),
(19,'Роман','t',NULL,'','2026-08-10',NULL),
(20,'Поезия','t',NULL,'','2026-08-10',NULL),
(21,'Разкази','t',NULL,'','2026-08-10',NULL),
(22,'Повест','t',NULL,'','2026-08-10',NULL),
(23,'Приказки','t',NULL,'','2026-08-10',NULL),
(24,'Детска литература','t',NULL,'','2026-08-10',NULL),
(25,'Класика','t',NULL,'','2026-08-10',NULL),
(26,'История на България','t',NULL,'','2026-08-10',NULL),
(27,'География','t',NULL,'','2026-08-10',NULL),
(28,'Биология','t',NULL,'','2026-08-10',NULL),
(29,'Математика','t',NULL,'','2026-08-10',NULL),
(30,'Физика','t',NULL,'','2026-08-10',NULL),
(31,'Философия','t',NULL,'','2026-08-10',NULL),
(32,'Речник','t',NULL,'','2026-08-10',NULL),
(33,'Енциклопедия','t',NULL,'','2026-08-10',NULL),
(34,'Чужд език','t',NULL,'','2026-08-10',NULL),
(35,'Фентъзи','t',NULL,'','2026-08-10',NULL),
(36,'Пътепис','t',NULL,'','2026-08-10',NULL),
(37,'Драма','t',NULL,'','2026-08-10',NULL),
(38,'Краезнание','t',NULL,'','2026-08-10',NULL);
/*!40000 ALTER TABLE `mst_topic` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `mst_visitor_room`
--

DROP TABLE IF EXISTS `mst_visitor_room`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_visitor_room` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `unique_code` varchar(5) NOT NULL COMMENT 'Code for identification each room',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_code_unq` (`unique_code`),
  KEY `unique_code_idx` (`unique_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mst_visitor_room`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `mst_visitor_room` WRITE;
/*!40000 ALTER TABLE `mst_visitor_room` DISABLE KEYS */;
/*!40000 ALTER TABLE `mst_visitor_room` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `mst_voc_ctrl`
--

DROP TABLE IF EXISTS `mst_voc_ctrl`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_voc_ctrl` (
  `vocabolary_id` int(11) NOT NULL AUTO_INCREMENT,
  `topic_id` int(11) NOT NULL,
  `rt_id` varchar(11) NOT NULL,
  `related_topic_id` varchar(250) NOT NULL,
  `scope` text DEFAULT NULL,
  PRIMARY KEY (`vocabolary_id`),
  UNIQUE KEY `idx_heading` (`topic_id`,`related_topic_id`(200))
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mst_voc_ctrl`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `mst_voc_ctrl` WRITE;
/*!40000 ALTER TABLE `mst_voc_ctrl` DISABLE KEYS */;
/*!40000 ALTER TABLE `mst_voc_ctrl` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `plugins`
--

DROP TABLE IF EXISTS `plugins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `plugins` (
  `id` varchar(32) NOT NULL,
  `path` text NOT NULL,
  `options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`options`)),
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `uid` int(11) NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `plugins`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `plugins` WRITE;
/*!40000 ALTER TABLE `plugins` DISABLE KEYS */;
/*!40000 ALTER TABLE `plugins` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `reserve`
--

DROP TABLE IF EXISTS `reserve`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `reserve` (
  `reserve_id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` varchar(20) NOT NULL,
  `biblio_id` int(11) NOT NULL,
  `item_code` varchar(20) NOT NULL,
  `reserve_date` datetime NOT NULL,
  PRIMARY KEY (`reserve_id`),
  KEY `references_idx` (`member_id`,`biblio_id`),
  KEY `item_code_idx` (`item_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reserve`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `reserve` WRITE;
/*!40000 ALTER TABLE `reserve` DISABLE KEYS */;
/*!40000 ALTER TABLE `reserve` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `search_biblio`
--

DROP TABLE IF EXISTS `search_biblio`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `search_biblio` (
  `biblio_id` int(11) NOT NULL,
  `title` text DEFAULT NULL,
  `edition` varchar(50) DEFAULT NULL,
  `isbn_issn` varchar(20) DEFAULT NULL,
  `author` text DEFAULT NULL,
  `topic` text DEFAULT NULL,
  `gmd` varchar(30) DEFAULT NULL,
  `publisher` varchar(100) DEFAULT NULL,
  `publish_place` varchar(30) DEFAULT NULL,
  `language` varchar(20) DEFAULT NULL,
  `classification` varchar(40) DEFAULT NULL,
  `spec_detail_info` text DEFAULT NULL,
  `carrier_type` varchar(100) NOT NULL,
  `content_type` varchar(100) NOT NULL,
  `media_type` varchar(100) NOT NULL,
  `location` text DEFAULT NULL,
  `publish_year` varchar(20) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `series_title` text DEFAULT NULL,
  `items` text DEFAULT NULL,
  `collection_types` text DEFAULT NULL,
  `call_number` varchar(50) DEFAULT NULL,
  `opac_hide` smallint(1) NOT NULL DEFAULT 0,
  `promoted` smallint(1) NOT NULL DEFAULT 0,
  `labels` text DEFAULT NULL,
  `collation` varchar(100) DEFAULT NULL,
  `image` varchar(100) DEFAULT NULL,
  `input_date` datetime DEFAULT NULL,
  `last_update` datetime DEFAULT NULL,
  UNIQUE KEY `biblio_id` (`biblio_id`),
  KEY `add_indexes` (`gmd`,`publisher`,`publish_place`,`language`,`classification`,`publish_year`,`call_number`),
  KEY `add_indexes2` (`opac_hide`,`promoted`),
  KEY `rda_indexes` (`carrier_type`,`media_type`,`content_type`),
  FULLTEXT KEY `title` (`title`,`series_title`),
  FULLTEXT KEY `author` (`author`),
  FULLTEXT KEY `topic` (`topic`),
  FULLTEXT KEY `location` (`location`),
  FULLTEXT KEY `items` (`items`),
  FULLTEXT KEY `collection_types` (`collection_types`),
  FULLTEXT KEY `labels` (`labels`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci COMMENT='index table for advance searching technique for SLiMS';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `search_biblio`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `search_biblio` WRITE;
/*!40000 ALTER TABLE `search_biblio` DISABLE KEYS */;
INSERT INTO `search_biblio` VALUES
(1,'PHP 5 for dummies','','0764541668','Valade, Janet','Programming - Website','Text','Wiley','Hoboken, NJ','English','005.13/3 22','','','','',NULL,'2004',NULL,'For dummies',NULL,NULL,'005.13/3-22 Jan p',0,0,NULL,'xiv, 392 p. : ill. ; 24 cm.','php5_dummies.jpg','2007-11-29 15:36:50','2007-11-29 16:26:59'),
(2,'Linux In a Nutshell','Fifth Edition','9780596009304','Siever, Ellen - Love, Robert - Robbins, Arnold - Figgins, Stephen - Weber, Aaron','Operating System - Linux - Computer','Text','OReilly','Sebastopol, CA','English','005.4/32 22','','','','','My Library','2005',NULL,'In a Nutshell','B00009 - B00010','Reference','005.4/32-22 Ell l',0,0,NULL,'xiv, 925 p. : ill. ; 23 cm.','linux_in_a_nutshell.jpg','2007-11-29 15:53:35','2007-11-29 16:26:10'),
(3,'The Definitive Guide to MySQL 5','','9781590595350','Kofler, Michael - Kramer, David','Programming - Database - RDBMS','Text','Apress','','English','005.75/85 22','','','','',NULL,'2005',NULL,'Definitive Guide Series',NULL,NULL,'005.75/85-22 Kof d',0,0,NULL,'784p.','mysql_def_guide.jpg','2007-11-29 16:01:08','2007-11-29 16:26:33'),
(4,'Cathedral and the Bazaar: Musings on Linux and Open Source by an Accidental Revolutionary','','0-596-00108-8','Raymond, Eric','Linux - Open Source','Text','OReilly','Sebastopol, CA','English','005.4/32 22','','','','','My Library','2001','The Cathedral & the Bazaar is a must for anyone who cares about the future of the computer industry or the dynamics of the information economy. This revised and expanded paperback edition includes new material on open source developments in 1999 and 2000. Raymond\\\'s clear and effective writing style accurately describing the benefits of open source software has been key to its success. (Source: http://safari.oreilly.com/0596001088)',NULL,'B00007 - B00008','Reference','005.4/3222 Ray c',0,0,NULL,'208p.','cathedral_bazaar.jpg','2007-11-29 16:14:44','2007-11-29 16:25:43'),
(5,'Producing open source software : how to run a successful free software project','1st ed.','9780596007591','Fogel, Karl','Open Source - Project','Text','OReilly','Sebastopol, CA','English','005.1 22','','','','',NULL,'2005','Includes index.',NULL,NULL,NULL,'005.1-22 Fog p',0,0,NULL,'xx, 279 p. ; 24 cm.','producing_oss.jpg','2007-11-29 16:20:45','2007-11-29 16:31:21'),
(6,'PostgreSQL : a comprehensive guide to building, programming, and administering PostgreSQL databases','1st ed.','0735712573','Douglas, Korry - Douglas, Susan','Programming - RDBMS','Text','Sams','Indianapolis','English','005.75/85 22','','','','','My Library','2003','PostgreSQL is the world\\\'s most advanced open-source database. PostgreSQL is the most comprehensive, in-depth, and easy-to-read guide to this award-winning database. This book starts with a thorough overview of SQL, a description of all PostgreSQL data types, and a complete explanation of PostgreSQL commands.','DeveloperÃ¢â‚¬â„¢s library','B00002','Reference','005.75/85-22 Kor p',0,0,NULL,'xvii, 790 p. : ill. ; 23cm.','postgresql.jpg','2007-11-29 16:29:33','2026-08-10 18:09:33'),
(7,'Web application architecture : principles, protocols, and practices','','0471486566','Shklar, Leon - Rosen, Richard','Website - Design','Text','John Wiley','Hoboken, NJ','English','005.7/2 21','','','','',NULL,'2003','An in-depth examination of the core concepts and general principles of Web application development.\\r\\nThis book uses examples from specific technologies (e.g., servlet API or XSL), without promoting or endorsing particular platforms or APIs. Such knowledge is critical when designing and debugging complex systems. This conceptual understanding makes it easier to learn new APIs that arise in the rapidly changing Internet environment.',NULL,NULL,NULL,'005.7/2-21 Leo w',0,0,NULL,'xi, 357 p. : ill. ; 23 cm.','webapp_arch.jpg','2007-11-29 16:41:57','2007-11-29 16:32:46'),
(8,'Ajax : creating Web pages with asynchronous JavaScript and XML','','9780132272674','Woychowsky, Edmond','Programming - Website','Text','Prentice Hall','Upper Saddle River, NJ','English','006.7/86 22','','','','','My Library','2007','Using Ajax, you can build Web applications with the sophistication and usability of traditional desktop applications and you can do it using standards and open source software. Now, for the first time, there\\\'s an easy, example-driven guide to Ajax for every Web and open source developer, regardless of experience.','Bruce PerensÃ¢â‚¬â„¢ Open Source series','B00001','Reference','006.7/86-22 Woy a',0,0,NULL,'xxii, 384 p. : ill. ; 24 cm.','ajax.jpg','2007-11-29 16:47:20','2026-08-10 18:09:33'),
(9,'The organization of information','2nd ed.','1563089769','Taylor, Arlene G.','Information - Organization - Metadata','Text','Libraries Unlimited','Westport, Conn.','English','025 22','','','','',NULL,'2004','A basic textbook for students of library and information studies, and a guide for practicing school library media specialists. Describes the impact of global forces and the school district on the development and operation of a media center, the technical and human side of management, programmatic activities, supportive services to students, and the quality and quantity of resources available to support programs.','Library and information science text series',NULL,NULL,'025-22 Tay o',0,0,NULL,'xxvii, 417 p. : ill. ; 27 cm.','organization_information.jpg','2007-11-29 16:54:12','2007-11-29 16:27:20'),
(10,'Library and Information Center Management','7th ed.','9781591584063','Stueart, Robert D. - Moran, Barbara B.','Information - Library','Text','Libraries Unlimited','Westport, Conn.','English','025.1 22','','','','',NULL,'2007',NULL,'Library and information science text series',NULL,NULL,'025.1-22 Stu l',0,0,NULL,'xxviii, 492 p. : ill. ; 27 cm.','library_info_center.JPG','2007-11-29 16:58:51','2007-11-29 16:27:40'),
(11,'Information Architecture for the World Wide Web: Designing Large-Scale Web Sites','2nd ed.','9780596000356','Morville, Peter - Rosenfeld, Louis',NULL,'Text','OReilly','Cambridge, Mass','English','006.7 22','','','','',NULL,'2002','Information Architecture for the World Wide Web is about applying the principles of architecture and library science to web site design. Each website is like a public building, available for tourists and regulars alike to breeze through at their leisure. The job of the architect is to set up the framework for the site to make it comfortable and inviting for people to visit, relax in, and perhaps even return to someday.',NULL,NULL,NULL,'006.7-22 Mor i',0,0,NULL,'500p.','information_arch.jpg','2007-11-29 17:26:14','2007-11-29 16:32:25'),
(12,'Corruption and development','','9780714649023','Robinson, Mark','Corruption - Development','Text','Taylor & Francis Inc.','London','English','364.1/322/091724 21','','','','','My Library','1998','The articles assembled in this volume offer a fresh approach to analysing the problem of corruption in developing countries and the k means to tackle the phenomenon.',NULL,'B00006','Reference','364.1 Rob c',0,0,NULL,'166 p. : ill. ; 22 cm.','corruption_development.jpg','2007-11-29 17:45:30','2007-11-29 16:20:53'),
(13,'Corruption and development : the anti-corruption campaigns','','0230525504','Bracking, Sarah','Corruption','Text','Palgrave Macmillan','New York','English','364.1/323091724 22','','','','','My Library','2007','This book provides a multidisciplinary interrogation of the global anti-corruption campaigns of the last ten years, arguing that while some positive change is observable, the period is also replete with perverse consequences and unintended outcomes',NULL,'B00005','Reference','364.1 Bra c',0,0,NULL,'310p.','corruption_development_anti_campaign.jpg','2007-11-29 17:49:49','2007-11-29 16:19:48'),
(14,'Pigs at the trough : how corporate greed and political corruption are undermining America','','1400047714','Huffington, Arianna Stassinopoulos','Corruption','Text','Crown publishers','New York','English','364.1323','','','','','My Library','2003',NULL,NULL,'B00004','Reference','364.1323 Huf p',0,0,NULL,'275 p. ; 22 cm.','pigs_at_trough.jpg','2007-11-29 17:56:00','2007-11-29 16:18:33'),
(15,'Lords of poverty : the power, prestige, and corruption of the international aid business','','9780871134691','Hancock, Graham','Corruption - Poverty','Text','Atlantic Monthly Press','New York','English','338.9/1/091724 20','','','','','My Library','1994','Lords of Poverty is a case study in betrayals of a public trust. The shortcomings of aid are numerous, and serious enough to raise questions about the viability of the practice at its most fundamental levels. Hancocks report is thorough, deeply shocking, and certain to cause critical reevaluation of the governments motives in giving foreign aid, and of the true needs of our intended beneficiaries.',NULL,'B00003','Reference','338.9 Han l',0,0,NULL,'xvi, 234 p. ; 22 cm.','lords_of_poverty.jpg','2007-11-29 18:08:13','2007-11-29 16:13:11'),
(16,'Под игото','','9789541805123','Иван Вазов','Роман - Българска литература - Класика','Text','Народна култура','София','Български','886.7-31','','','','','Заемна за възрастни','1894',NULL,NULL,'CHT-000101 - CHT-000102 - CHT-000103','Художествена литература','886.7-31 ВАЗ',0,1,NULL,'изд., 44 см','','2026-08-10 00:00:00','2026-08-10 00:00:00'),
(17,'Немили-недраги','','9789544712045','Иван Вазов','Повест - Българска литература','Text','Български писател','София','Български','886.7-32','','','','','Заемна за възрастни','1883',NULL,NULL,'CHT-000104 - CHT-000105','Художествена литература','886.7-32 ВАЗ',0,0,NULL,'изд., 47 см','','2026-08-10 00:00:00','2026-08-10 00:00:00'),
(18,'Разкази','','9789544712121','Елин Пелин','Разкази - Българска литература','Text','Български писател','София','Български','886.7-32','','','','','Заемна за възрастни','1904',NULL,NULL,'CHT-000106 - CHT-000107','Художествена литература','886.7-32 ПЕЛ',0,0,NULL,'изд., 49 см','','2026-08-10 00:00:00','2026-08-10 00:00:00'),
(19,'Гераците','','9789540907777','Елин Пелин','Повест - Българска литература','Text','Захарий Стоянов','София','Български','886.7-31','','','','','Книгохранилище','1911',NULL,NULL,'CHT-000108','Художествена литература','886.7-31 ПЕЛ',0,0,NULL,'изд., 51 см','','2026-08-10 00:00:00','2026-08-10 00:00:00'),
(20,'Старопланински легенди','','9789542608811','Йордан Йовков','Българска литература - Разкази - Класика','Text','Хермес','Пловдив','Български','886.7-32','','','','','Заемна за възрастни','1927',NULL,NULL,'CHT-000109 - CHT-000110','Художествена литература','886.7-32 ЙОВ',0,0,NULL,'изд., 52 см','','2026-08-10 00:00:00','2026-08-10 00:00:00'),
(21,'Чифликът край границата','','9789542812345','Йордан Йовков','Българска литература - Роман','Text','Сиела','София','Български','886.7-31','','','','','Заемна за възрастни','1934',NULL,NULL,'CHT-000111','Художествена литература','886.7-31 ЙОВ',0,0,NULL,'изд., 54 см','','2026-08-10 00:00:00','2026-08-10 00:00:00'),
(22,'Тютюн','','9789541805567','Димитър Димов','Българска литература - Роман - Класика','Text','Народна култура','София','Български','886.7-31','','','','','Заемна за възрастни','1951',NULL,NULL,'CHT-000112 - CHT-000113 - CHT-000114','Художествена литература','886.7-31 ДИМ',0,1,NULL,'изд., 55 см','','2026-08-10 00:00:00','2026-08-10 00:00:00'),
(23,'Антихрист','','9789544712900','Емилиян Станев','Българска литература - Роман','Text','Български писател','София','Български','886.7-31','','','','','Книгохранилище','1970',NULL,NULL,'CHT-000115','Художествена литература','886.7-31 СТА',0,0,NULL,'изд., 58 см','','2026-08-10 00:00:00','2026-08-10 00:00:00'),
(24,'Крадецът на праскови','','9789540908888','Емилиян Станев','Българска литература - Повест','Text','Захарий Стоянов','София','Български','886.7-32','','','','','Заемна за възрастни','1948',NULL,NULL,'CHT-000116 - CHT-000117','Художествена литература','886.7-32 СТА',0,0,NULL,'изд., 59 см','','2026-08-10 00:00:00','2026-08-10 00:00:00'),
(25,'Време разделно','','9789540901234','Антон Дончев','Роман - История на България','Text','Захарий Стоянов','София','Български','886.7-31','','','','','Заемна за възрастни','1964',NULL,NULL,'CHT-000118 - CHT-000119','Художествена литература','886.7-31 ДОН',0,0,NULL,'изд., 61 см','','2026-08-10 00:00:00','2026-08-10 00:00:00'),
(26,'Физика на тъгата','','9789544912223','Георги Господинов','Българска литература - Роман','Text','Жанет 45','Пловдив','Български','886.7-31','','','','','Заемна за възрастни','2011',NULL,NULL,'CHT-000120 - CHT-000121','Художествена литература','886.7-31 ГОС',0,1,NULL,'изд., 63 см','','2026-08-10 00:00:00','2026-08-10 00:00:00'),
(27,'Естествен роман','','9789544911111','Георги Господинов','Българска литература - Роман','Text','Жанет 45','Пловдив','Български','886.7-31','','','','','Заемна за възрастни','1999',NULL,NULL,'CHT-000122','Художествена литература','886.7-31 ГОС',0,0,NULL,'изд., 65 см','','2026-08-10 00:00:00','2026-08-10 00:00:00'),
(28,'Хайка за вълци','','9789545291234','Ивайло Петров','Българска литература - Роман','Text','Колибри','София','Български','886.7-31','','','','','Книгохранилище','1986',NULL,NULL,'CHT-000123','Художествена литература','886.7-31 ПЕТ',0,0,NULL,'изд., 66 см','','2026-08-10 00:00:00','2026-08-10 00:00:00'),
(29,'Нощем с белите коне','','9789544713333','Павел Вежинов','Българска литература - Роман','Text','Български писател','София','Български','886.7-31','','','','','Заемна за възрастни','1975',NULL,NULL,'CHT-000124','Художествена литература','886.7-31 ВЕЖ',0,0,NULL,'изд., 67 см','','2026-08-10 00:00:00','2026-08-10 00:00:00'),
(30,'Бариерата','','9789542609999','Павел Вежинов','Българска литература - Повест','Text','Хермес','София','Български','886.7-32','','','','','Заемна за възрастни','1976',NULL,NULL,'CHT-000125 - CHT-000126','Художествена литература','886.7-32 ВЕЖ',0,0,NULL,'изд., 68 см','','2026-08-10 00:00:00','2026-08-10 00:00:00'),
(31,'Стихотворения','','9789541806000','Христо Ботев','Българска литература - Поезия - Класика','Text','Народна култура','София','Български','886.7-1','','','','','Заемна за възрастни','1875',NULL,NULL,'CHT-000127 - CHT-000128','Художествена литература','886.7-1 БОТ',0,0,NULL,'изд., 70 см','','2026-08-10 00:00:00','2026-08-10 00:00:00'),
(32,'Безсъници','','9789544714000','Пейо Яворов','Българска литература - Поезия','Text','Български писател','София','Български','886.7-1','','','','','Заемна за възрастни','1907',NULL,NULL,'CHT-000129','Художествена литература','886.7-1 ЯВО',0,0,NULL,'изд., 72 см','','2026-08-10 00:00:00','2026-08-10 00:00:00'),
(33,'Стихотворения','','9789540902222','Димчо Дебелянов','Българска литература - Поезия','Text','Захарий Стоянов','София','Български','886.7-1','','','','','Заемна за възрастни','1920',NULL,NULL,'CHT-000130','Художествена литература','886.7-1 ДЕБ',0,0,NULL,'изд., 73 см','','2026-08-10 00:00:00','2026-08-10 00:00:00'),
(34,'Пътувания','','9789545292345','Блага Димитрова','Поезия - Пътепис','Text','Колибри','София','Български','886.7-1','','','','','Заемна за възрастни','1965',NULL,NULL,'CHT-000131','Художествена литература','886.7-1 ДИМ',0,0,NULL,'изд., 74 см','','2026-08-10 00:00:00','2026-08-10 00:00:00'),
(35,'Бай Ганьо','','9789541806345','Алеко Константинов','Българска литература - Разкази - Класика','Text','Народна култура','София','Български','886.7-32','','','','','Заемна за възрастни','1895',NULL,NULL,'CHT-000132 - CHT-000133','Художествена литература','886.7-32 КОН',0,0,NULL,'изд., 75 см','','2026-08-10 00:00:00','2026-08-10 00:00:00'),
(36,'Дядовата ръкавичка','','9789542720011','Ран Босилек','Приказки - Детска литература','Text','Фют','София','Български','087.5','','','','','Детски отдел','1936',NULL,NULL,'CHT-000134 - CHT-000135 - CHT-000136','Детска литература','087.5 БОС',0,0,NULL,'изд., 77 см','','2026-08-10 00:00:00','2026-08-10 00:00:00'),
(37,'Патиланско царство','','9789546600022','Ран Босилек','Приказки - Детска литература','Text','Пан','София','Български','087.5','','','','','Детски отдел','1938',NULL,NULL,'CHT-000137 - CHT-000138','Детска литература','087.5 БОС',0,0,NULL,'изд., 80 см','','2026-08-10 00:00:00','2026-08-10 00:00:00'),
(38,'Пипи Дългото чорапче','','9789546601111','Астрид Линдгрен','Приказки - Детска литература','Text','Пан','София','Български','839.7-93','','','','','Детски отдел','1945',NULL,NULL,'CHT-000139 - CHT-000140','Детска литература','839.7-93 ЛИН',0,0,NULL,'изд., 82 см','','2026-08-10 00:00:00','2026-08-10 00:00:00'),
(39,'Емил от Льонеберя','','9789546602222','Астрид Линдгрен','Детска литература','Text','Пан','София','Български','839.7-93','','','','','Детски отдел','1963',NULL,NULL,'CHT-000141 - CHT-000142','Детска литература','839.7-93 ЛИН',0,0,NULL,'изд., 84 см','','2026-08-10 00:00:00','2026-08-10 00:00:00'),
(40,'Хари Потър и Философският камък','','9789542704455','Джоан Роулинг','Детска литература - Фентъзи','Text','Егмонт България','София','Български','820-93','','','','','Детски отдел','1997',NULL,NULL,'CHT-000143 - CHT-000144 - CHT-000145','Детска литература','820-93 РОУ',0,1,NULL,'изд., 86 см','','2026-08-10 00:00:00','2026-08-10 00:00:00'),
(41,'Хари Потър и Стаята на тайните','','9789542704462','Джоан Роулинг','Детска литература - Фентъзи','Text','Егмонт България','София','Български','820-93','','','','','Детски отдел','1998',NULL,NULL,'CHT-000146 - CHT-000147','Детска литература','820-93 РОУ',0,0,NULL,'изд., 89 см','','2026-08-10 00:00:00','2026-08-10 00:00:00'),
(42,'Хобитът','','9789542705566','Джон Р. Р. Толкин','Детска литература - Фентъзи','Text','Егмонт България','София','Български','820-93','','','','','Детски отдел','1937',NULL,NULL,'CHT-000148 - CHT-000149','Детска литература','820-93 ТОЛ',0,0,NULL,'изд., 91 см','','2026-08-10 00:00:00','2026-08-10 00:00:00'),
(43,'Малкият принц','','9789545272233','Антоан дьо Сент-Екзюпери','Детска литература - Класика','Text','Дамян Яков','София','Български','840-93','','','','','Детски отдел','1943',NULL,NULL,'CHT-000150 - CHT-000151 - CHT-000152','Детска литература','840-93 СЕН',0,1,NULL,'изд., 93 см','','2026-08-10 00:00:00','2026-08-10 00:00:00'),
(44,'Хвърчащата класна стая','','9789546603333','Ерих Кестнер','Детска литература','Text','Пан','София','Български','830-93','','','','','Детски отдел','1933',NULL,NULL,'CHT-000153','Детска литература','830-93 КЕС',0,0,NULL,'изд., 96 см','','2026-08-10 00:00:00','2026-08-10 00:00:00'),
(45,'Приключенията на Том Сойер','','9789546604444','Марк Твен','Детска литература - Класика','Text','Пан','София','Български','820(73)-93','','','','','Детски отдел','1876',NULL,NULL,'CHT-000154 - CHT-000155','Детска литература','820(73)-93 ТВЕ',0,0,NULL,'изд., 97 см','','2026-08-10 00:00:00','2026-08-10 00:00:00'),
(46,'Бяла приказка','','9789542720099','Валери Петров','Приказки - Детска литература','Text','Фют','София','Български','886.7-93','','','','','Детски отдел','1978',NULL,NULL,'CHT-000156 - CHT-000157','Детска литература','886.7-93 ПЕТ',0,0,NULL,'изд., 99 см','','2026-08-10 00:00:00','2026-08-10 00:00:00'),
(47,'Математика за 7. клас','','9789540136001','Колектив','Математика','Text','Просвета','София','Български','51(075.3)','','','','','Заемна за възрастни','2018',NULL,NULL,'CHT-000158 - CHT-000159 - CHT-000160 - CHT-000161','Учебна литература','51(075.3) КОЛ',0,0,NULL,'изд., 101 см','','2026-08-10 00:00:00','2026-08-10 00:00:00'),
(48,'Български език за 7. клас','','9789540136018','Колектив','Чужд език','Text','Просвета','София','Български','808.67(075.3)','','','','','Заемна за възрастни','2018',NULL,NULL,'CHT-000162 - CHT-000163 - CHT-000164 - CHT-000165','Учебна литература','808.67(075.3) КОЛ',0,0,NULL,'изд., 25 см','','2026-08-10 00:00:00','2026-08-10 00:00:00'),
(49,'История и цивилизации за 8. клас','','9789540135025','Колектив','История на България','Text','Просвета','София','Български','93(075.3)','','','','','Заемна за възрастни','2017',NULL,NULL,'CHT-000166 - CHT-000167 - CHT-000168','Учебна литература','93(075.3) КОЛ',0,0,NULL,'изд., 29 см','','2026-08-10 00:00:00','2026-08-10 00:00:00'),
(50,'Биология и здравно образование за 9. клас','','9789540137032','Колектив','Биология','Text','Просвета','София','Български','57(075.3)','','','','','Заемна за възрастни','2018',NULL,NULL,'CHT-000169 - CHT-000170 - CHT-000171','Учебна литература','57(075.3) КОЛ',0,0,NULL,'изд., 32 см','','2026-08-10 00:00:00','2026-08-10 00:00:00'),
(51,'Физика и астрономия за 10. клас','','9789540138049','Колектив','Физика','Text','Просвета','София','Български','53(075.3)','','','','','Заемна за възрастни','2019',NULL,NULL,'CHT-000172 - CHT-000173 - CHT-000174','Учебна литература','53(075.3) КОЛ',0,0,NULL,'изд., 35 см','','2026-08-10 00:00:00','2026-08-10 00:00:00'),
(52,'География и икономика за 9. клас','','9789540137056','Колектив','География','Text','Просвета','София','Български','91(075.3)','','','','','Заемна за възрастни','2018',NULL,NULL,'CHT-000175 - CHT-000176','Учебна литература','91(075.3) КОЛ',0,0,NULL,'изд., 38 см','','2026-08-10 00:00:00','2026-08-10 00:00:00'),
(53,'Български тълковен речник','','9789540200011','Колектив','Речник','Text','','София','Български','808.67(038)','','','','','Читалня','2012',NULL,NULL,'CHT-000177 - CHT-000178','Справочна литература','808.67(038) КОЛ',0,0,NULL,'изд., 40 см','','2026-08-10 00:00:00','2026-08-10 00:00:00'),
(54,'Правописен речник на българския език','','9789548283022','Колектив','Речник','Text','','София','Български','808.67(038)','','','','','Читалня','2016',NULL,NULL,'CHT-000179 - CHT-000180','Справочна литература','808.67(038) КОЛ',0,0,NULL,'изд., 42 см','','2026-08-10 00:00:00','2026-08-10 00:00:00'),
(55,'Английско-български речник','','9789545287733','Колектив','Речник - Чужд език','Text','Труд','София','Български','802.0-3(038)','','','','','Читалня','2010',NULL,NULL,'CHT-000181 - CHT-000182','Справочна литература','802.0-3(038) КОЛ',0,0,NULL,'изд., 44 см','','2026-08-10 00:00:00','2026-08-10 00:00:00'),
(56,'Енциклопедия България','','9789545280044','Колектив','История на България - Енциклопедия','Text','Труд','София','Български','030(497.2)','','','','','Читалня','2005',NULL,NULL,'CHT-000183','Справочна литература','030(497.2) КОЛ',0,0,NULL,'изд., 46 см','','2026-08-10 00:00:00','2026-08-10 00:00:00'),
(57,'Атлас на света','','9789545289955','Колектив','География','Text','Труд','София','Български','912(100)','','','','','Читалня','2014',NULL,NULL,'CHT-000184','Справочна литература','912(100) КОЛ',0,0,NULL,'изд., 47 см','','2026-08-10 00:00:00','2026-08-10 00:00:00'),
(58,'История на България','','9786191524001','Колектив','История на България','Text','Изток-Запад','София','Български','949.72','','','','','Книгохранилище','2015',NULL,NULL,'CHT-000185 - CHT-000186','Научна литература','949.72 КОЛ',0,0,NULL,'изд., 48 см','','2026-08-10 00:00:00','2026-08-10 00:00:00'),
(59,'Философия. Въведение','','9786191523018','Колектив','Философия','Text','Изток-Запад','София','Български','1(075)','','','','','Книгохранилище','2013',NULL,NULL,'CHT-000187','Научна литература','1(075) КОЛ',0,0,NULL,'изд., 50 см','','2026-08-10 00:00:00','2026-08-10 00:00:00'),
(60,'Кратка история на времето','','9786191524025','Колектив','Физика','Text','Изток-Запад','София','Български','52','','','','','Книгохранилище','2014',NULL,NULL,'CHT-000188','Научна литература','52 КОЛ',0,0,NULL,'изд., 51 см','','2026-08-10 00:00:00','2026-08-10 00:00:00'),
(61,'История на родния край','','9786192660011','Колектив','История на България - Краезнание','Text','Асеневци','Велико Търново','Български','908(497.2)','','','','','Краезнание','2019',NULL,NULL,'CHT-000189','Краезнание','908(497.2) КОЛ',0,0,NULL,'изд., 52 см','','2026-08-10 00:00:00','2026-08-10 00:00:00'),
(62,'Народни песни от нашия край','','9786192660028','Колектив','Краезнание - Поезия','Text','Асеневци','Велико Търново','Български','398.8(497.2)','','','','','Краезнание','2017',NULL,NULL,'CHT-000190','Краезнание','398.8(497.2) КОЛ',0,0,NULL,'изд., 53 см','','2026-08-10 00:00:00','2026-08-10 00:00:00'),
(63,'The Old Man and the Sea','','9780684801223','Колектив','Чужд език - Роман','Text','Колибри','София','English','820(73)-31','','','','','Заемна за възрастни','1952',NULL,NULL,'CHT-000191','Чужди езици','820(73)-31 КОЛ',0,0,NULL,'изд., 54 см','','2026-08-10 00:00:00','2026-08-10 00:00:00'),
(64,'Престъпление и наказание','','9789545293456','Фьодор Достоевски','Роман - Класика','Text','Колибри','София','Руски','882-31','','','','','Заемна за възрастни','1866',NULL,NULL,'CHT-000192 - CHT-000193','Чужди езици','882-31 ДОС',0,0,NULL,'изд., 55 см','','2026-08-10 00:00:00','2026-08-10 00:00:00'),
(65,'Сто години самота','','9789548227667','Габриел Гарсия Маркес','Роман - Класика','Text','','София','Испански','860(861)-31','','','','','Заемна за възрастни','1967',NULL,NULL,'CHT-000194','Чужди езици','860(861)-31 МАР',0,0,NULL,'изд., 57 см','','2026-08-10 00:00:00','2026-08-10 00:00:00'),
(66,'Списание Читалище (2025, бр. 1-12)','','','Колектив','Краезнание','Text','','София','Български','05','','','','','Читалня','2025',NULL,NULL,'CHT-000195','Периодика','05 КОЛ',0,0,NULL,'изд., 58 см','','2026-08-10 00:00:00','2026-08-10 00:00:00'),
(67,'Списание Родознание (2025)','','','Колектив','Краезнание - История на България','Text','','София','Български','05','','','','','Читалня','2025',NULL,NULL,'CHT-000196','Периодика','05 КОЛ',0,0,NULL,'изд., 59 см','','2026-08-10 00:00:00','2026-08-10 00:00:00'),
(68,'Под игото (DVD, игрален филм)','','','Колектив','Драма','Digital Versatile Disc','Асеневци','София','Български','791.4','','','','','Читалня','2020',NULL,NULL,'CHT-000197','Мултимедия','791.4 КОЛ',0,0,NULL,'изд., 60 см','','2026-08-10 00:00:00','2026-08-10 00:00:00'),
(69,'Българска народна музика (CD)','','','Колектив','Краезнание','Multimedia','Асеневци','София','Български','78(497.2)','','','','','Читалня','2018',NULL,NULL,'CHT-000198','Мултимедия','78(497.2) КОЛ',0,0,NULL,'изд., 61 см','','2026-08-10 00:00:00','2026-08-10 00:00:00');
/*!40000 ALTER TABLE `search_biblio` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `serial`
--

DROP TABLE IF EXISTS `serial`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `serial` (
  `serial_id` int(11) NOT NULL AUTO_INCREMENT,
  `date_start` date NOT NULL,
  `date_end` date DEFAULT NULL,
  `period` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `biblio_id` int(11) DEFAULT NULL,
  `gmd_id` int(11) DEFAULT NULL,
  `input_date` date NOT NULL,
  `last_update` date NOT NULL,
  PRIMARY KEY (`serial_id`),
  KEY `fk_serial_biblio` (`biblio_id`),
  KEY `fk_serial_gmd` (`gmd_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `serial`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `serial` WRITE;
/*!40000 ALTER TABLE `serial` DISABLE KEYS */;
/*!40000 ALTER TABLE `serial` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `setting`
--

DROP TABLE IF EXISTS `setting`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `setting` (
  `setting_id` int(3) NOT NULL AUTO_INCREMENT,
  `setting_name` varchar(30) NOT NULL,
  `setting_value` text DEFAULT NULL,
  PRIMARY KEY (`setting_id`),
  UNIQUE KEY `setting_name` (`setting_name`)
) ENGINE=MyISAM AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `setting`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `setting` WRITE;
/*!40000 ALTER TABLE `setting` DISABLE KEYS */;
INSERT INTO `setting` VALUES
(1,'library_name','s:7:\"Senayan\";'),
(2,'library_subname','s:37:\"Open Source Library Management System\";'),
(3,'template','a:2:{s:5:\"theme\";s:7:\"default\";s:3:\"css\";s:26:\"template/default/style.css\";}'),
(4,'admin_template','a:2:{s:5:\"theme\";s:7:\"default\";s:3:\"css\";s:32:\"admin_template/default/style.css\";}'),
(5,'default_lang','s:5:\"bg_BG\";'),
(6,'opac_result_num','s:2:\"10\";'),
(7,'enable_promote_titles','N;'),
(8,'quick_return','b:1;'),
(9,'allow_loan_date_change','b:0;'),
(10,'loan_limit_override','b:0;'),
(11,'enable_xml_detail','b:1;'),
(12,'enable_xml_result','b:1;'),
(13,'allow_file_download','b:1;'),
(14,'session_timeout','s:4:\"7200\";'),
(15,'circulation_receipt','b:0;'),
(16,'barcode_encoding','s:7:\"code128\";'),
(17,'ignore_holidays_fine_calc','b:0;'),
(18,'barcode_print_settings','a:12:{s:19:\"barcode_page_margin\";d:0.200000000000000011102230246251565404236316680908203125;s:21:\"barcode_items_per_row\";i:3;s:20:\"barcode_items_margin\";d:0.1000000000000000055511151231257827021181583404541015625;s:17:\"barcode_box_width\";i:7;s:18:\"barcode_box_height\";i:5;s:27:\"barcode_include_header_text\";i:1;s:17:\"barcode_cut_title\";i:50;s:19:\"barcode_header_text\";s:0:\"\";s:13:\"barcode_fonts\";s:41:\"Arial, Verdana, Helvetica, \'Trebuchet MS\'\";s:17:\"barcode_font_size\";i:11;s:13:\"barcode_scale\";i:70;s:19:\"barcode_border_size\";i:1;}'),
(19,'label_print_settings','a:10:{s:11:\"page_margin\";d:0.200000000000000011102230246251565404236316680908203125;s:13:\"items_per_row\";i:3;s:12:\"items_margin\";d:0.05000000000000000277555756156289135105907917022705078125;s:9:\"box_width\";i:8;s:10:\"box_height\";d:3.29999999999999982236431605997495353221893310546875;s:19:\"include_header_text\";i:1;s:11:\"header_text\";s:0:\"\";s:5:\"fonts\";s:41:\"Arial, Verdana, Helvetica, \'Trebuchet MS\'\";s:9:\"font_size\";i:11;s:11:\"border_size\";i:1;}'),
(20,'membercard_print_settings','a:1:{s:5:\"print\";a:1:{s:10:\"membercard\";a:61:{s:11:\"card_factor\";s:12:\"37.795275591\";s:21:\"card_include_id_label\";i:1;s:23:\"card_include_name_label\";i:1;s:22:\"card_include_pin_label\";i:1;s:23:\"card_include_inst_label\";i:0;s:24:\"card_include_email_label\";i:0;s:26:\"card_include_address_label\";i:1;s:26:\"card_include_barcode_label\";i:1;s:26:\"card_include_expired_label\";i:1;s:14:\"card_box_width\";d:8.5999999999999996447286321199499070644378662109375;s:15:\"card_box_height\";d:5.4000000000000003552713678800500929355621337890625;s:9:\"card_logo\";s:8:\"logo.png\";s:21:\"card_front_logo_width\";s:0:\"\";s:22:\"card_front_logo_height\";s:0:\"\";s:20:\"card_front_logo_left\";s:0:\"\";s:19:\"card_front_logo_top\";s:0:\"\";s:20:\"card_back_logo_width\";s:0:\"\";s:21:\"card_back_logo_height\";s:0:\"\";s:19:\"card_back_logo_left\";s:0:\"\";s:18:\"card_back_logo_top\";s:0:\"\";s:15:\"card_photo_left\";s:0:\"\";s:14:\"card_photo_top\";s:0:\"\";s:16:\"card_photo_width\";d:1.5;s:17:\"card_photo_height\";d:1.8000000000000000444089209850062616169452667236328125;s:23:\"card_front_header1_text\";s:19:\"Library Member Card\";s:28:\"card_front_header1_font_size\";s:2:\"12\";s:23:\"card_front_header2_text\";s:10:\"My Library\";s:28:\"card_front_header2_font_size\";s:2:\"12\";s:22:\"card_back_header1_text\";s:10:\"My Library\";s:27:\"card_back_header1_font_size\";s:2:\"12\";s:22:\"card_back_header2_text\";s:35:\"My Library Full Address and Website\";s:27:\"card_back_header2_font_size\";s:1:\"5\";s:17:\"card_header_color\";s:7:\"#0066FF\";s:18:\"card_bio_font_size\";s:2:\"11\";s:20:\"card_bio_font_weight\";s:4:\"bold\";s:20:\"card_bio_label_width\";s:3:\"100\";s:9:\"card_city\";s:9:\"City Name\";s:10:\"card_title\";s:15:\"Library Manager\";s:14:\"card_officials\";s:14:\"Librarian Name\";s:17:\"card_officials_id\";s:12:\"Librarian ID\";s:15:\"card_stamp_file\";s:9:\"stamp.png\";s:19:\"card_signature_file\";s:13:\"signature.png\";s:15:\"card_stamp_left\";s:0:\"\";s:14:\"card_stamp_top\";s:0:\"\";s:16:\"card_stamp_width\";s:0:\"\";s:17:\"card_stamp_height\";s:0:\"\";s:13:\"card_exp_left\";s:0:\"\";s:12:\"card_exp_top\";s:0:\"\";s:14:\"card_exp_width\";s:0:\"\";s:15:\"card_exp_height\";s:0:\"\";s:18:\"card_barcode_scale\";i:100;s:17:\"card_barcode_left\";s:0:\"\";s:16:\"card_barcode_top\";s:0:\"\";s:18:\"card_barcode_width\";s:0:\"\";s:19:\"card_barcode_height\";s:0:\"\";s:10:\"card_rules\";s:120:\"<ul><li>This card is published by Library.</li><li>Please return this card to its owner if you found it.</li></ul>\";s:20:\"card_rules_font_size\";s:1:\"8\";s:12:\"card_address\";s:76:\"My Library<br />website: http://slims.web.id, email : librarian@slims.web.id\";s:22:\"card_address_font_size\";s:1:\"7\";s:17:\"card_address_left\";s:0:\"\";s:16:\"card_address_top\";s:0:\"\";}}}'),
(21,'enable_visitor_limitation','s:1:\"0\";'),
(22,'time_visitor_limitation','s:2:\"60\";'),
(23,'init_info','a:3:{s:7:\"version\";s:22:\"SLiMS 9 (Bulian D Ace)\";s:3:\"tag\";s:6:\"v9.8.0\";s:9:\"admin_url\";s:6:\"/admin\";}');
/*!40000 ALTER TABLE `setting` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `stock_take`
--

DROP TABLE IF EXISTS `stock_take`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `stock_take` (
  `stock_take_id` int(11) NOT NULL AUTO_INCREMENT,
  `stock_take_name` varchar(200) NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime DEFAULT NULL,
  `init_user` varchar(50) NOT NULL,
  `total_item_stock_taked` int(11) DEFAULT NULL,
  `total_item_lost` int(11) DEFAULT NULL,
  `total_item_exists` int(11) DEFAULT 0,
  `total_item_loan` int(11) DEFAULT NULL,
  `stock_take_users` mediumtext DEFAULT NULL,
  `is_active` int(1) NOT NULL DEFAULT 0,
  `report_file` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`stock_take_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_take`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `stock_take` WRITE;
/*!40000 ALTER TABLE `stock_take` DISABLE KEYS */;
/*!40000 ALTER TABLE `stock_take` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `stock_take_item`
--

DROP TABLE IF EXISTS `stock_take_item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `stock_take_item` (
  `stock_take_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `item_code` varchar(20) NOT NULL,
  `title` varchar(255) NOT NULL,
  `gmd_name` varchar(30) DEFAULT NULL,
  `classification` varchar(30) DEFAULT NULL,
  `coll_type_name` varchar(30) DEFAULT NULL,
  `call_number` varchar(50) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `status` enum('e','m','u','l') NOT NULL DEFAULT 'm',
  `checked_by` varchar(50) NOT NULL,
  `last_update` datetime DEFAULT NULL,
  PRIMARY KEY (`stock_take_id`,`item_id`),
  UNIQUE KEY `item_code` (`item_code`),
  KEY `status` (`status`),
  KEY `item_properties_idx` (`gmd_name`,`classification`,`coll_type_name`,`location`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_take_item`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `stock_take_item` WRITE;
/*!40000 ALTER TABLE `stock_take_item` DISABLE KEYS */;
/*!40000 ALTER TABLE `stock_take_item` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `system_log`
--

DROP TABLE IF EXISTS `system_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `system_log` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `log_type` enum('staff','member','system') NOT NULL DEFAULT 'staff',
  `id` varchar(50) DEFAULT NULL,
  `log_location` varchar(50) NOT NULL,
  `sub_module` varchar(50) DEFAULT NULL,
  `action` varchar(50) DEFAULT NULL,
  `log_msg` text NOT NULL,
  `log_date` datetime NOT NULL,
  PRIMARY KEY (`log_id`),
  KEY `log_type` (`log_type`),
  KEY `id` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_log`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `system_log` WRITE;
/*!40000 ALTER TABLE `system_log` DISABLE KEYS */;
INSERT INTO `system_log` VALUES
(1,'staff','admin','Login','','','Login success for user admin from address 192.168.65.1','2026-08-11 01:15:54'),
(2,'staff','admin','Login','','','Login success for user admin from address 192.168.65.1','2026-08-11 01:19:45'),
(3,'staff','1','system','','','Administrator Log Out from application from address 192.168.65.1','2026-08-11 01:21:24'),
(4,'staff','admin','Login','','','Login success for user admin from address 192.168.65.1','2026-08-11 01:22:04'),
(5,'member','','circulation','Loan','Started','Administrator start transaction with member ()','2026-08-11 01:22:42'),
(6,'member','а','circulation','Loan','Started','Administrator start transaction with member (а)','2026-08-11 01:22:55'),
(7,'staff','1','membership','Member Profile','Profile added','Administrator add new member (Тест Мембер) with ID (11322)','2026-08-11 01:29:28'),
(8,'member','11322','circulation','Loan','Started','Administrator start transaction with member (11322)','2026-08-11 01:29:43'),
(9,'member','11322','circulation','Loan','Add','Administrator insert new loan (CHT-000101) for member (11322)','2026-08-11 01:34:03'),
(10,'staff','1','system','','','Administrator Log Out from application from address 192.168.65.1','2026-08-11 02:11:20'),
(11,'member','admin','Login','','','Login FAILED for member admin from address 192.168.65.1','2026-08-11 02:12:29'),
(12,'staff','admin','Login','','','Login success for user admin from address 192.168.65.1','2026-08-11 02:13:03'),
(13,'staff','admin','Login','','','Login success for user admin from address 192.168.65.1','2026-08-11 02:14:35'),
(14,'staff','1','system','','','Administrator Log Out from application from address 192.168.65.1','2026-08-11 02:16:53'),
(15,'staff','admin','Login','','','Login success for user admin from address 192.168.65.1','2026-08-11 02:39:17');
/*!40000 ALTER TABLE `system_log` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `realname` varchar(100) NOT NULL,
  `passwd` varchar(64) NOT NULL,
  `2fa` text DEFAULT NULL,
  `email` varchar(200) DEFAULT NULL,
  `user_type` smallint(2) DEFAULT NULL,
  `user_image` varchar(250) DEFAULT NULL,
  `social_media` text DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `last_login_ip` varchar(50) DEFAULT NULL,
  `groups` varchar(200) DEFAULT NULL,
  `forgot` varchar(80) DEFAULT NULL,
  `admin_template` text DEFAULT NULL,
  `input_date` date DEFAULT NULL,
  `last_update` date DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`),
  KEY `realname` (`realname`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES
(1,'admin','Administrator','$2y$10$pG0dqMrd2r39zRTSFSyp8.Z7sMy4cY7s/18UDQsV50Vn0TnR6UORm',NULL,NULL,NULL,NULL,NULL,NULL,'127.0.0.1','a:1:{i:0;s:1:\"1\";}',NULL,'a:2:{s:5:\"theme\";s:7:\"default\";s:3:\"css\";s:32:\"admin_template/default/style.css\";}','2026-08-10','2026-08-10');
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `user_group`
--

DROP TABLE IF EXISTS `user_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_group` (
  `group_id` int(11) NOT NULL AUTO_INCREMENT,
  `group_name` varchar(30) NOT NULL,
  `input_date` date DEFAULT NULL,
  `last_update` date DEFAULT NULL,
  PRIMARY KEY (`group_id`),
  UNIQUE KEY `group_name` (`group_name`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_group`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `user_group` WRITE;
/*!40000 ALTER TABLE `user_group` DISABLE KEYS */;
INSERT INTO `user_group` VALUES
(1,'Administrator','2026-08-10','2026-08-10');
/*!40000 ALTER TABLE `user_group` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `user_tokens`
--

DROP TABLE IF EXISTS `user_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `selector` varchar(255) NOT NULL,
  `hashed_validator` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_tokens`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `user_tokens` WRITE;
/*!40000 ALTER TABLE `user_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_tokens` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `visitor_count`
--

DROP TABLE IF EXISTS `visitor_count`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `visitor_count` (
  `visitor_id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` varchar(20) DEFAULT NULL,
  `member_name` varchar(255) NOT NULL,
  `institution` varchar(100) DEFAULT NULL,
  `room_code` varchar(5) DEFAULT NULL,
  `checkin_date` datetime NOT NULL,
  PRIMARY KEY (`visitor_id`),
  KEY `member_id` (`member_id`),
  KEY `room_code` (`room_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `visitor_count`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `visitor_count` WRITE;
/*!40000 ALTER TABLE `visitor_count` DISABLE KEYS */;
/*!40000 ALTER TABLE `visitor_count` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Dumping events for database 'senayan'
--

--
-- Dumping routines for database 'senayan'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2026-08-11  5:27:42
