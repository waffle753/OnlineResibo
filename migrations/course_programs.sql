USE `online_resibo`;

CREATE TABLE `course_programs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_course_programs_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `course_programs` (`name`)
SELECT DISTINCT COALESCE(NULLIF(TRIM(`course`), ''), 'Unspecified')
FROM `students`;

ALTER TABLE `students`
  ADD COLUMN `program_id` INT UNSIGNED DEFAULT NULL AFTER `last_name`;

UPDATE `students` s
JOIN `course_programs` cp
 ON cp.`name` = COALESCE(NULLIF(TRIM(s.`course`), ''), 'Unspecified')
SET s.`program_id` = cp.`id`;

ALTER TABLE `students`
  MODIFY COLUMN `program_id` INT UNSIGNED NOT NULL,
  DROP COLUMN `course`,
  ADD KEY `idx_students_program_id` (`program_id`),
  ADD CONSTRAINT `fk_students_course_program`
    FOREIGN KEY (`program_id`) REFERENCES `course_programs` (`id`)
    ON UPDATE CASCADE
    ON DELETE RESTRICT;
