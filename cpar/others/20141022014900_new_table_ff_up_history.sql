CREATE TABLE `ff_up_history`
( `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, 
	`ff_date` DATE NOT NULL, 
	`next_ff_date` DATE NOT NULL, 
	`no_of_tasks` INT(11) NOT NULL, 
	`completed_tasks` INT(11) NOT NULL, 
	`pending_tasks` INT(11) NOT NULL, 
	`overdue_tasks` INT(11) NOT NULL, 
	`ff_result` INT(11) NOT NULL, 
	`remarks` TEXT, PRIMARY KEY (`id`) ); 

ALTER TABLE `ff_up_history` ADD COLUMN `ongoing_tasks` INT(11) NOT NULL AFTER `completed_tasks`;
ALTER TABLE `ff_up_history` ADD COLUMN `cpar_no` VARCHAR(13) NOT NULL AFTER `id`;