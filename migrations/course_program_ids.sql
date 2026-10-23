USE `online_resibo`;

ALTER TABLE `course_programs`
  ADD COLUMN `course_id` VARCHAR(100) DEFAULT NULL AFTER `id`;

UPDATE `course_programs`
SET `course_id` = `name`;

ALTER TABLE `course_programs`
  MODIFY COLUMN `course_id` VARCHAR(100) NOT NULL,
  ADD UNIQUE KEY `uq_course_programs_course_id` (`course_id`);
