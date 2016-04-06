/*
SQLyog Community v12.0 (64 bit)
MySQL - 5.6.17 : Database - aboitiz-ci
*********************************************************************
*/


/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*Table structure for table `location` */

DROP TABLE IF EXISTS `location`;

CREATE TABLE `location` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

/*Data for the table `location` */

insert  into `location`(`id`,`name`) values (1,'Cebu'),(2,'Taguig'),(3,'Davao');

/*Table structure for table `team` */

DROP TABLE IF EXISTS `team`;

CREATE TABLE `team` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=latin1;

/*Data for the table `team` */

insert  into `team`(`id`,`name`) values (1,'Accounting (ACT)'),(2,'Corporate Administration Department (ADM) - Taguig'),(3,'Admin - Cebu'),(4,'Group Internal Audit (GIA)'),(5,'Executive Office Liason Team (EXOLT)'),(6,'Risk Management Team (RMT)'),(7,'Human Resource and Quality (HRQ)'),(8,'Investor Relations (IR)'),(9,'Computer Services Division (iCSD)'),(10,'Legal (LEX)'),(11,'Reputation Management Team (RMD)'),(12,'Strategy and Corporate Finance Group (SCFG)'),(13,'Treasury Services Group (TSG)'),(14,'Physical Asset Security Group (PASG)');

/*Table structure for table `user` */

DROP TABLE IF EXISTS `user`;

CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(255) NOT NULL,
  `middle_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) NOT NULL,
  `email_address` varchar(255) NOT NULL,
  `team` int(11) NOT NULL,
  `team_lead` int(11) NOT NULL,
  `position_title` varchar(255) NOT NULL,
  `location` int(11) NOT NULL,
  `ims_flag` tinyint(1) NOT NULL,
  `mr_flag` tinyint(1) NOT NULL,
  `access_level` tinyint(1) NOT NULL,
  `status` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQUE_EMAIL` (`email_address`)
) ENGINE=InnoDB AUTO_INCREMENT=254 DEFAULT CHARSET=latin1;

/*Data for the table `user` */

insert  into `user`(`id`,`first_name`,`middle_name`,`last_name`,`email_address`,`team`,`team_lead`,`position_title`,`location`,`ims_flag`,`mr_flag`,`access_level`,`status`) values (5,'Rey','Patigas','Libutan','reylibutan@gmail.com',3,0,'Software Developer',1,1,0,2,1),(18,'Jin','','Kazama','jin_kazama@mailinator.com',7,99,'Tekken Character',2,1,0,1,1),(19,'Heihachi','','Mishima','heihachi_mishima@mailinator.com',14,1,'4th Dan',2,1,0,2,0),(21,'Craig','','Marduk','craig_marduk@mailinator.com',5,5,'Wrestler',2,1,0,2,0),(36,'Jinpachi','','Mishima','jinpachi_mishima@mailinator.com',4,19,'Grandmaster',1,1,0,1,1),(37,'Miguel','','Caballero','miguel_caballero@mailinator.com',12,36,'Drunk Bastard',1,1,0,2,0),(38,'Christie','','Monteiro','christie_monteiro@mailinator.com',4,37,'Capoeira Master',1,0,0,2,0),(39,'Eddy','','Gordo','eddy_gordo@mailinator.com',12,38,'Capoeira Grandmaster',1,1,0,1,1),(40,'Alisa','','Boskonovitch','alisa_boskonovitch@mailinator.com',6,39,'Robot Invention',2,0,0,1,1),(41,'Asuka','','Kazama','asuka_kazama@mailinator.com',7,18,'Jin\'s cousin',2,0,0,2,0),(43,'Anna','','Williams','anna_williams@mailinator.com',7,41,'Female Assassin',2,1,0,2,1),(45,'Nina','','Williams','nina_williams@mailinator.com',7,41,'Female Assassin',2,1,0,2,1),(46,'Kuma and','','Panda','kuma_panda@mailinator.com',3,45,'Strongest Bears',2,1,0,2,1),(47,'Roger','','Jr','roger_jr@mailinator.com',6,46,'Fighting Kangaroo',1,0,0,2,1),(48,'Mokujin','','Tetsujin','mokujin_tetsujin@mailinator.com',9,47,'Mimicking Trunk',2,1,0,1,1),(50,'1','1','1','1@1.com',1,1,'Test Data',1,0,0,2,1),(51,'0','0','0','0@0.com',1,1,'Test Data',1,0,0,2,1),(53,'2','2','2','2@2.com',1,1,'Test Data',1,0,0,2,1),(54,'3','3','3','3@3.com',1,1,'Test Data',1,0,0,2,1),(55,'4','4','4','4@4.com',1,1,'Test Data',1,0,0,2,1),(56,'5','5','5','5@5.com',1,1,'Test Data',1,0,0,2,1),(57,'6','6','6','6@6.com',1,1,'Test Data',1,0,0,2,1),(58,'7','7','7','7@7.com',1,1,'Test Data',1,0,0,2,1),(59,'8','8','8','8@8.com',1,1,'Test Data',1,0,0,2,1),(60,'9','9','9','1',1,1,'Test Data',1,0,0,1,1),(61,'10','10','10','10@10.com',2,1,'Test Data',1,0,0,1,1),(62,'11','11','11','11@11.com',3,1,'Test Data',1,0,0,2,1),(63,'12','12','12','12@12.com',1,1,'Test Data',1,0,0,2,1),(64,'13','13','13','13@13.com',1,1,'Test Data',1,0,0,2,1),(65,'14','14','14','14@14.com',1,1,'Test Data',1,0,0,2,1),(66,'15','15','15','15@15.com',1,1,'Test Data',1,0,0,2,1),(67,'16','16','16','16@16.com',1,1,'Test Data',1,0,0,2,1),(68,'17','17','17','17@17.com',1,1,'Test Data',1,0,0,2,1),(69,'18','18','18','18@18.com',1,1,'Test Data',1,0,0,2,1),(70,'19','19','19','19@19.com',1,1,'Test Data',1,0,0,2,1),(71,'20','20','20','20@20.com',1,1,'Test Data',1,0,0,2,1),(72,'21','21','21','21@21.com',1,1,'Test Data',1,0,0,2,1),(73,'22','22','22','22@22.com',1,1,'Test Data',1,0,0,2,1),(74,'23','23','23','23@23.com',1,1,'Test Data',1,0,0,2,1),(75,'24','24','24','24@24.com',1,1,'Test Data',1,0,0,2,1),(76,'25','25','25','25@25.com',1,1,'Test Data',1,0,0,2,1),(77,'26','26','26','26@26.com',1,1,'Test Data',1,0,0,2,1),(78,'27','27','27','27@27.com',1,1,'Test Data',1,0,0,2,1),(79,'28','28','28','28@28.com',1,1,'Test Data',1,0,0,2,1),(80,'29','29','29','29@29.com',1,1,'Test Data',1,0,0,2,1),(81,'30','30','30','30@30.com',1,1,'Test Data',1,0,0,2,1),(82,'31','31','31','31@31.com',1,1,'Test Data',1,0,0,2,1),(83,'32','32','32','32@32.com',1,1,'Test Data',1,0,0,2,1),(84,'33','33','33','33@33.com',1,1,'Test Data',1,0,0,2,1),(85,'34','34','34','34@34.com',1,1,'Test Data',1,0,0,2,1),(86,'35','35','35','35@35.com',1,1,'Test Data',1,0,0,2,1),(87,'36','36','36','36@36.com',1,1,'Test Data',1,0,0,2,1),(88,'37','37','37','37@37.com',1,1,'Test Data',1,0,0,2,1),(89,'38','38','38','38@38.com',1,1,'Test Data',1,0,0,2,1),(90,'39','39','39','39@39.com',1,1,'Test Data',1,0,0,2,1),(91,'40','40','40','40@40.com',1,1,'Test Data',1,0,0,2,1),(92,'41','41','41','41@41.com',1,1,'Test Data',1,0,0,2,1),(93,'42','42','42','42@42.com',1,1,'Test Data',1,0,0,2,1),(94,'43','43','43','43@43.com',1,1,'Test Data',1,0,0,2,1),(95,'44','44','44','44@44.com',1,1,'Test Data',1,0,0,2,1),(96,'45','45','45','45@45.com',1,1,'Test Data',1,0,0,2,1),(97,'46','46','46','46@46.com',1,1,'Test Data',1,0,0,2,1),(98,'47','47','47','47@47.com',1,1,'Test Data',1,0,0,2,1),(99,'48','48','48','48@48.com',1,1,'Test Data',1,0,0,2,1),(100,'49','49','49','49@49.com',1,1,'Test Data',1,0,0,2,1),(101,'50','50','50','50@50.com',1,1,'Test Data',1,0,0,2,1),(102,'51','51','51','51@51.com',1,1,'Test Data',1,0,0,2,1),(103,'52','52','52','52@52.com',1,1,'Test Data',1,0,0,2,1),(104,'53','53','53','53@53.com',1,1,'Test Data',1,0,0,2,1),(105,'54','54','54','54@54.com',1,1,'Test Data',1,0,0,2,1),(106,'55','55','55','55@55.com',1,1,'Test Data',1,0,0,2,1),(107,'56','56','56','56@56.com',1,1,'Test Data',1,0,0,2,1),(108,'57','57','57','57@57.com',1,1,'Test Data',1,0,0,2,1),(109,'58','58','58','58@58.com',1,1,'Test Data',1,0,0,2,1),(110,'59','59','59','59@59.com',1,1,'Test Data',1,0,0,2,1),(111,'60','60','60','60@60.com',1,1,'Test Data',1,0,0,2,1),(112,'61','61','61','61@61.com',1,1,'Test Data',1,0,0,2,1),(113,'62','62','62','62@62.com',1,1,'Test Data',1,0,0,2,1),(114,'63','63','63','63@63.com',1,1,'Test Data',1,0,0,2,1),(115,'64','64','64','64@64.com',1,1,'Test Data',1,0,0,2,1),(116,'65','65','65','65@65.com',1,1,'Test Data',1,0,0,2,1),(117,'66','66','66','66@66.com',1,1,'Test Data',1,0,0,2,1),(118,'67','67','67','67@67.com',1,1,'Test Data',1,0,0,2,1),(119,'68','68','68','68@68.com',1,1,'Test Data',1,0,0,2,1),(120,'69','69','69','69@69.com',1,1,'Test Data',1,0,0,2,1),(121,'70','70','70','70@70.com',1,1,'Test Data',1,0,0,2,1),(122,'71','71','71','71@71.com',1,1,'Test Data',1,0,0,2,1),(123,'72','72','72','72@72.com',1,1,'Test Data',1,0,0,2,1),(124,'73','73','73','73@73.com',1,1,'Test Data',1,0,0,2,1),(125,'74','74','74','74@74.com',1,1,'Test Data',1,0,0,2,1),(126,'75','75','75','75@75.com',1,1,'Test Data',1,0,0,2,1),(127,'76','76','76','76@76.com',1,1,'Test Data',1,0,0,2,1),(128,'77','77','77','77@77.com',1,1,'Test Data',1,0,0,2,1),(129,'78','78','78','78@78.com',1,1,'Test Data',1,0,0,2,1),(130,'79','79','79','79@79.com',1,1,'Test Data',1,0,0,2,1),(131,'80','80','80','80@80.com',1,1,'Test Data',1,0,0,2,1),(132,'81','81','81','81@81.com',1,1,'Test Data',1,0,0,2,1),(133,'82','82','82','82@82.com',1,1,'Test Data',1,0,0,2,1),(134,'83','83','83','83@83.com',1,1,'Test Data',1,0,0,2,1),(135,'84','84','84','84@84.com',1,1,'Test Data',1,0,0,2,1),(136,'85','85','85','85@85.com',1,1,'Test Data',1,0,0,2,1),(137,'86','86','86','86@86.com',1,1,'Test Data',1,0,0,2,1),(138,'87','87','87','87@87.com',1,1,'Test Data',1,0,0,2,1),(139,'88','88','88','88@88.com',1,1,'Test Data',1,0,0,2,1),(140,'89','89','89','89@89.com',1,1,'Test Data',1,0,0,2,1),(141,'90','90','90','90@90.com',1,1,'Test Data',1,0,0,2,1),(142,'91','91','91','91@91.com',1,1,'Test Data',1,0,0,2,1),(143,'92','92','92','92@92.com',1,1,'Test Data',1,0,0,2,1),(144,'93','93','93','93@93.com',1,1,'Test Data',1,0,0,2,1),(145,'94','94','94','94@94.com',1,1,'Test Data',1,0,0,2,1),(146,'95','95','95','95@95.com',1,1,'Test Data',1,0,0,2,1),(147,'96','96','96','96@96.com',1,1,'Test Data',1,0,0,2,1),(148,'97','97','97','97@97.com',1,1,'Test Data',1,0,0,2,1),(149,'98','98','98','98@98.com',1,1,'Test Data',1,0,0,2,1),(150,'99','99','99','99@99.com',1,1,'Test Data',1,0,0,2,1),(151,'100','100','100','100@100.com',1,1,'Test Data',1,0,0,2,1),(152,'101','101','101','101@101.com',1,1,'Test Data',1,0,0,2,1),(153,'102','102','102','102@102.com',1,1,'Test Data',1,0,0,2,1),(154,'103','103','103','103@103.com',1,1,'Test Data',1,0,0,2,1),(155,'104','104','104','104@104.com',1,1,'Test Data',1,0,0,2,1),(156,'105','105','105','105@105.com',1,1,'Test Data',1,0,0,2,1),(157,'106','106','106','106@106.com',1,1,'Test Data',1,0,0,2,1),(158,'107','107','107','107@107.com',1,1,'Test Data',1,0,0,2,1),(159,'108','108','108','108@108.com',1,1,'Test Data',1,0,0,2,1),(160,'109','109','109','109@109.com',1,1,'Test Data',1,0,0,2,1),(161,'110','110','110','110@110.com',1,1,'Test Data',1,0,0,2,1),(162,'111','111','111','111@111.com',1,1,'Test Data',1,0,0,2,1),(163,'112','112','112','112@112.com',1,1,'Test Data',1,0,0,2,1),(164,'113','113','113','113@113.com',1,1,'Test Data',1,0,0,2,1),(165,'114','114','114','114@114.com',1,1,'Test Data',1,0,0,2,1),(166,'115','115','115','115@115.com',1,1,'Test Data',1,0,0,2,1),(167,'116','116','116','116@116.com',1,1,'Test Data',1,0,0,2,1),(168,'117','117','117','117@117.com',1,1,'Test Data',1,0,0,2,1),(169,'118','118','118','118@118.com',1,1,'Test Data',1,0,0,2,1),(170,'119','119','119','119@119.com',1,1,'Test Data',1,0,0,2,1),(171,'120','120','120','120@120.com',1,1,'Test Data',1,0,0,2,1),(172,'121','121','121','121@121.com',1,1,'Test Data',1,0,0,2,1),(173,'122','122','122','122@122.com',1,1,'Test Data',1,0,0,2,1),(174,'123','123','123','123@123.com',1,1,'Test Data',1,0,0,2,1),(175,'124','124','124','124@124.com',1,1,'Test Data',1,0,0,2,1),(176,'125','125','125','125@125.com',1,1,'Test Data',1,0,0,2,1),(177,'126','126','126','126@126.com',1,1,'Test Data',1,0,0,2,1),(178,'127','127','127','127@127.com',1,1,'Test Data',1,0,0,2,1),(179,'128','128','128','128@128.com',1,1,'Test Data',1,0,0,2,1),(180,'129','129','129','129@129.com',1,1,'Test Data',1,0,0,2,1),(181,'130','130','130','130@130.com',1,1,'Test Data',1,0,0,2,1),(182,'131','131','131','131@131.com',1,1,'Test Data',1,0,0,2,1),(183,'132','132','132','132@132.com',1,1,'Test Data',1,0,0,2,1),(184,'133','133','133','133@133.com',1,1,'Test Data',1,0,0,2,1),(185,'134','134','134','134@134.com',1,1,'Test Data',1,0,0,2,1),(186,'135','135','135','135@135.com',1,1,'Test Data',1,0,0,2,1),(187,'136','136','136','136@136.com',1,1,'Test Data',1,0,0,2,1),(188,'137','137','137','137@137.com',1,1,'Test Data',1,0,0,2,1),(189,'138','138','138','138@138.com',1,1,'Test Data',1,0,0,2,1),(190,'139','139','139','139@139.com',1,1,'Test Data',1,0,0,2,1),(191,'140','140','140','140@140.com',1,1,'Test Data',1,0,0,2,1),(192,'141','141','141','141@141.com',1,1,'Test Data',1,0,0,2,1),(193,'142','142','142','142@142.com',1,1,'Test Data',1,0,0,2,1),(194,'143','143','143','143@143.com',1,1,'Test Data',1,0,0,2,1),(195,'144','144','144','144@144.com',1,1,'Test Data',1,0,0,2,1),(196,'145','145','145','145@145.com',1,1,'Test Data',1,0,0,2,1),(197,'146','146','146','146@146.com',1,1,'Test Data',1,0,0,2,1),(198,'147','147','147','147@147.com',1,1,'Test Data',1,0,0,2,1),(199,'148','148','148','148@148.com',1,1,'Test Data',1,0,0,2,1),(200,'149','149','149','149@149.com',1,1,'Test Data',1,0,0,2,1),(201,'150','150','150','150@150.com',1,1,'Test Data',1,0,0,2,1),(202,'151','151','151','151@151.com',1,1,'Test Data',1,0,0,2,1),(203,'152','152','152','152@152.com',1,1,'Test Data',1,0,0,2,1),(204,'153','153','153','153@153.com',1,1,'Test Data',1,0,0,2,1),(205,'154','154','154','154@154.com',1,1,'Test Data',1,0,0,2,1),(206,'155','155','155','155@155.com',1,1,'Test Data',1,0,0,2,1),(207,'156','156','156','156@156.com',1,1,'Test Data',1,0,0,2,1),(208,'157','157','157','157@157.com',1,1,'Test Data',1,0,0,2,1),(209,'158','158','158','158@158.com',1,1,'Test Data',1,0,0,2,1),(210,'159','159','159','159@159.com',1,1,'Test Data',1,0,0,2,1),(211,'160','160','160','160@160.com',1,1,'Test Data',1,0,0,2,1),(212,'161','161','161','161@161.com',1,1,'Test Data',1,0,0,2,1),(213,'162','162','162','162@162.com',1,1,'Test Data',1,0,0,2,1),(214,'163','163','163','163@163.com',1,1,'Test Data',1,0,0,2,1),(215,'164','164','164','164@164.com',1,1,'Test Data',1,0,0,2,1),(216,'165','165','165','165@165.com',1,1,'Test Data',1,0,0,2,1),(217,'166','166','166','166@166.com',1,1,'Test Data',1,0,0,2,1),(218,'167','167','167','167@167.com',1,1,'Test Data',1,0,0,2,1),(219,'168','168','168','168@168.com',1,1,'Test Data',1,0,0,2,1),(220,'169','169','169','169@169.com',1,1,'Test Data',1,0,0,2,1),(221,'170','170','170','170@170.com',1,1,'Test Data',1,0,0,2,1),(222,'171','171','171','171@171.com',1,1,'Test Data',1,0,0,2,1),(224,'173','173','173','173@173.com',1,1,'Test Data',1,0,0,2,1),(225,'174','174','174','174@174.com',1,1,'Test Data',1,0,0,2,1),(226,'175','175','175','175@175.com',1,1,'Test Data',1,0,0,2,1),(227,'176','176','176','176@176.com',1,1,'Test Data',1,0,0,2,1),(228,'177','177','177','177@177.com',1,1,'Test Data',1,0,0,2,1),(229,'178','178','178','178@178.com',1,1,'Test Data',1,0,0,2,1),(230,'179','179','179','179@179.com',1,1,'Test Data',1,0,0,2,1),(231,'180','180','180','180@180.com',1,1,'Test Data',1,0,0,2,1),(232,'181','181','181','181@181.com',1,1,'Test Data',1,0,0,2,1),(233,'182','182','182','182@182.com',1,1,'Test Data',1,0,0,2,1),(234,'183','183','183','183@183.com',1,1,'Test Data',1,0,0,2,1),(235,'184','184','184','184@184.com',1,1,'Test Data',1,0,0,2,1),(236,'185','185','185','185@185.com',1,1,'Test Data',1,0,0,2,1),(237,'186','186','186','186@186.com',1,1,'Test Data',1,0,0,2,1),(238,'187','187','187','187@187.com',1,1,'Test Data',1,0,0,2,1),(239,'188','188','188','188@188.com',1,1,'Test Data',1,0,0,2,1),(240,'189','189','189','189@189.com',1,1,'Test Data',1,0,0,2,1),(241,'190','190','190','190@190.com',1,1,'Test Data',1,0,0,2,1),(242,'191','191','191','191@191.com',1,1,'Test Data',1,0,0,2,1),(243,'192','192','192','192@192.com',1,1,'Test Data',1,0,0,2,1),(244,'193','193','193','193@193.com',1,1,'Test Data',1,0,0,2,1),(245,'194','194','194','194@194.com',1,1,'Test Data',1,0,0,2,1),(246,'195','195','195','195@195.com',1,1,'Test Data',1,0,0,2,1),(248,'197','197','197','197@197.com',1,1,'Test Data',1,0,0,2,1),(251,'200','200','200','200_200@mailinator.com',7,5,'200',2,0,0,2,1),(252,'201','201','201','201_201@mailinator.com',1,251,'201',1,0,0,1,1),(253,'300','300','300','300_300@mailinator.com',7,171,'300',2,1,1,1,1);

/*Table structure for table `user_role` */

DROP TABLE IF EXISTS `user_role`;

CREATE TABLE `user_role` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(32) NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

/*Data for the table `user_role` */

insert  into `user_role`(`id`,`code`,`name`) values (1,'ADMIN','Administrator'),(2,'MGTREP','Management Representative'),(3,'IMSSPEC','IMS Specialist'),(4,'USER','Ordinary User');

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
