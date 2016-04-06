DROP TABLE IF EXISTS `addressee_fields`;

CREATE TABLE `addressee_fields` (
  `cpar_no` varchar(13) NOT NULL,
  `accomplish_by` date DEFAULT NULL,
  `rad_action` text,
  `rad_implemented_by` int(255) DEFAULT NULL,
  `rad_implemented_date` date DEFAULT NULL,
  `rca_tools` varchar(255) DEFAULT NULL,
  `rca_tools_others` varchar(255) DEFAULT NULL,
  `rca_investigated_by` int(255) DEFAULT NULL,
  `rca_investigated_date_started` date DEFAULT NULL,
  `rca_investigated_date_ended` date DEFAULT NULL,
  `action` text,
  `proposed_by` int(255) DEFAULT NULL,
  `target_start_date` date DEFAULT NULL,
  `target_end_date` date DEFAULT NULL,
  `updated_by` int(255) DEFAULT NULL,
  `updated_date` date DEFAULT NULL,
  PRIMARY KEY (`cpar_no`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;