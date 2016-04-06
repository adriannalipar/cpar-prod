ALTER TABLE `cpar_main` 
ADD COLUMN `addressee_team` INT(11) NOT NULL AFTER `addressee`, 
ADD COLUMN `addressee_team_lead` INT(255) NOT NULL AFTER `addressee_team`, 
ADD COLUMN `requestor_team` INT(11) NOT NULL AFTER `requestor`, 
ADD COLUMN `requestor_team_lead` INT(255) NOT NULL AFTER `requestor_team`; 