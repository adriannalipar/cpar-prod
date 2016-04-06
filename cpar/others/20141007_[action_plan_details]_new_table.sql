DROP TABLE IF EXISTS `action_plan_details`;

CREATE TABLE `action_plan_details` (
  `cpar_no` varchar(13) NOT NULL,
  `task` text NOT NULL,
  `responsible_person` varchar(255) NOT NULL,
  `due_date` date NOT NULL,
  `completed_date` date DEFAULT NULL,
  `status` int(11) NOT NULL,
  `remarks_addr` text,
  `remarks_ims` text,
  PRIMARY KEY (`cpar_no`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `action_plan_details` DROP PRIMARY KEY; 
