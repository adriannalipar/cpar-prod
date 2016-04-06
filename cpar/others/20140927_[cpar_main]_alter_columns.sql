ALTER TABLE `cpar_main` 
CHANGE `title` `title` TEXT CHARSET latin1 COLLATE latin1_swedish_ci NULL, 
CHANGE `type` `type` INT(1) NULL, 
CHANGE `raised_as_a_result_of` `raised_as_a_result_of` INT(11) NULL, 
CHANGE `process` `process` INT(11) NULL, 
CHANGE `addressee` `addressee` INT(255) NULL, 
CHANGE `addressee_team` `addressee_team` INT(11) NULL, 
CHANGE `addressee_team_lead` `addressee_team_lead` INT(255) NULL, 
CHANGE `requestor` `requestor` INT(255) NULL, 
CHANGE `requestor_team` `requestor_team` INT(11) NULL, 
CHANGE `requestor_team_lead` `requestor_team_lead` INT(255) NULL, 
CHANGE `status` `status` INT(1) NULL, 
CHANGE `sub_status` `sub_status` VARCHAR(50) CHARSET latin1 COLLATE latin1_swedish_ci NULL, 
CHANGE `is_deleted` `is_deleted` TINYINT(1) DEFAULT 0 NULL;