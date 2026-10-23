USE `online_resibo`;

ALTER TABLE `students`
  ADD COLUMN `email` VARCHAR(254) DEFAULT NULL AFTER `last_name`;
