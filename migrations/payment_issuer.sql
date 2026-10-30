USE `online_resibo`;

ALTER TABLE `payments`
  ADD COLUMN `issued_by` VARCHAR(255) DEFAULT NULL AFTER `reference_no`;
