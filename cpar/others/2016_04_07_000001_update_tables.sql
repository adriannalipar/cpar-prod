--
-- Update CHARACTER SET to utf8
--

ALTER TABLE `action_plan_details` CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE `addressee_fields` CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE `audit_log` CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE `cpar_main` CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE `ff_up_history` CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE `location` CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE `process` CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE `raised_as_a_result_of` CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE `rca_tools` CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE `review_history` CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE `team` CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE `user` CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE `user_role` CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;