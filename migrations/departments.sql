CREATE TABLE IF NOT EXISTS `departments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(50) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_departments_code` (`code`),
  UNIQUE KEY `uq_departments_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `departments` (`code`, `name`) VALUES
  ('CS', 'Computer Science'),
  ('BA', 'Business Administration'),
  ('ENG', 'Engineering'),
  ('EDU', 'Education');

ALTER TABLE `students`
  ADD COLUMN `department_id` INT UNSIGNED NULL AFTER `program_id`,
  ADD KEY `idx_students_department_id` (`department_id`),
  ADD CONSTRAINT `fk_students_department`
    FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`)
    ON UPDATE CASCADE
    ON DELETE RESTRICT;
