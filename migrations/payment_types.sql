USE `online_resibo`;

CREATE TABLE `payment_types` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_payment_types_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `payment_types` (`name`)
SELECT DISTINCT `payment_type`
FROM `payments`;

INSERT IGNORE INTO `payment_types` (`name`) VALUES
  ('Tuition Fee'),
  ('Miscellaneous'),
  ('Laboratory Fee'),
  ('School Uniform'),
  ('Books'),
  ('Other');

ALTER TABLE `payments`
  ADD KEY `idx_payments_payment_type` (`payment_type`),
  ADD CONSTRAINT `fk_payments_payment_type`
    FOREIGN KEY (`payment_type`) REFERENCES `payment_types` (`name`)
    ON UPDATE CASCADE
    ON DELETE RESTRICT;
