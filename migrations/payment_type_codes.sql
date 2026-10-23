USE `online_resibo`;

ALTER TABLE `payment_types`
  ADD COLUMN `code` VARCHAR(50) DEFAULT NULL AFTER `id`;

UPDATE `payment_types`
SET `code` = CONCAT('PT-', LPAD(`id`, 4, '0'));

ALTER TABLE `payment_types`
  MODIFY COLUMN `code` VARCHAR(50) NOT NULL,
  ADD UNIQUE KEY `uq_payment_types_code` (`code`);
