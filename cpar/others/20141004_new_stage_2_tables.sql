/*Table structure for table `rca_tools` */
DROP TABLE IF EXISTS `rca_tools`;

CREATE TABLE `rca_tools` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Data for the table `rca_tools` */

insert  into `rca_tools`(`id`,`name`) values (1,'Cause and Effect (Fishbone) Diagram'),(2,'5 Why’s'),(3,'Pareto Analysis'),(4,'Cause and Effect Matrix'),(5,'Tree Diagram'),(6,'Brainstorming'),(7,'Others');