CREATE DATABASE IF NOT EXISTS `online_resibo`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `online_resibo`;

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `first_name` VARCHAR(80) NOT NULL,
  `middle_name` VARCHAR(80) DEFAULT NULL,
  `last_name` VARCHAR(80) NOT NULL,
  `username` VARCHAR(100) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` VARCHAR(50) NOT NULL DEFAULT 'User',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `course_programs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `course_id` VARCHAR(100) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_course_programs_course_id` (`course_id`),
  UNIQUE KEY `uq_course_programs_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

CREATE TABLE IF NOT EXISTS `students` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_id` VARCHAR(50) NOT NULL,
  `first_name` VARCHAR(80) NOT NULL,
  `middle_name` VARCHAR(80) DEFAULT NULL,
  `last_name` VARCHAR(80) NOT NULL,
  `email` VARCHAR(254) DEFAULT NULL,
  `program_id` INT UNSIGNED NOT NULL,
  `department_id` INT UNSIGNED NULL,
  `year_level` VARCHAR(30) NOT NULL,
  `status` ENUM('Active', 'Inactive') NOT NULL DEFAULT 'Active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_students_student_id` (`student_id`),
  KEY `idx_students_status_name` (`status`, `last_name`, `first_name`),
  KEY `idx_students_program_id` (`program_id`),
  KEY `idx_students_department_id` (`department_id`),
  CONSTRAINT `fk_students_course_program`
    FOREIGN KEY (`program_id`) REFERENCES `course_programs` (`id`)
    ON UPDATE CASCADE
    ON DELETE RESTRICT,
  CONSTRAINT `fk_students_department`
    FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `payment_types` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(50) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_payment_types_code` (`code`),
  UNIQUE KEY `uq_payment_types_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `payment_types` (`code`, `name`) VALUES
  ('TUITION-FEE', 'Tuition Fee'),
  ('MISC', 'Miscellaneous'),
  ('LAB-FEE', 'Laboratory Fee'),
  ('UNIFORM', 'School Uniform'),
  ('BOOKS', 'Books'),
  ('OTHER', 'Other');

CREATE TABLE IF NOT EXISTS `payments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_id` INT UNSIGNED NOT NULL,
  `payment_type` VARCHAR(100) NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `payment_date` DATE NOT NULL,
  `payment_method` VARCHAR(50) NOT NULL,
  `status` ENUM('Paid', 'Pending', 'Cancelled') NOT NULL DEFAULT 'Paid',
  `reference_no` VARCHAR(32) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_payments_reference_no` (`reference_no`),
  KEY `idx_payments_student_id` (`student_id`),
  KEY `idx_payments_payment_date` (`payment_date`),
  KEY `idx_payments_payment_type` (`payment_type`),
  CONSTRAINT `fk_payments_student`
    FOREIGN KEY (`student_id`) REFERENCES `students` (`id`)
    ON UPDATE CASCADE
    ON DELETE RESTRICT,
  CONSTRAINT `fk_payments_payment_type`
    FOREIGN KEY (`payment_type`) REFERENCES `payment_types` (`name`)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
