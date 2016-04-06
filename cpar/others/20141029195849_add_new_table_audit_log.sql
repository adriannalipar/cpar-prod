CREATE TABLE `audit_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cpar_no` varchar(13) NOT NULL,
  `action` text NOT NULL,
  `stage` int(1) NOT NULL,
  `sub_status` varchar(50) NOT NULL,
  `remarks` text NOT NULL,
  `notes` text,
  `created_by` int(255) NOT NULL,
  `created_date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8;