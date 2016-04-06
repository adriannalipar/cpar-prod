CREATE TABLE `review_history`( `cpar_no` VARCHAR(13) NOT NULL, `action` VARCHAR(255) NOT NULL, `role` VARCHAR(255) NOT NULL, `stage` INT(1) NOT NULL, `sub_status` VARCHAR(50) NOT NULL, `remarks` TEXT NOT NULL, `reviewed_by` INT(255) NOT NULL, `reviewed_date` DATE NOT NULL, `due_date` DATE NOT NULL ); 

ALTER TABLE `review_history` CHANGE `reviewed_date` `reviewed_date` DATETIME NOT NULL;