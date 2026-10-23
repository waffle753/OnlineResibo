USE `online_resibo`;

ALTER TABLE `users`
  ADD COLUMN `first_name` VARCHAR(80) DEFAULT NULL AFTER `id`,
  ADD COLUMN `middle_name` VARCHAR(80) DEFAULT NULL AFTER `first_name`,
  ADD COLUMN `last_name` VARCHAR(80) DEFAULT NULL AFTER `middle_name`;

UPDATE `users`
SET
  `first_name` = CASE
    WHEN LOCATE(',', `fullname`) > 0
      THEN SUBSTRING_INDEX(TRIM(SUBSTRING_INDEX(`fullname`, ',', -1)), ' ', 1)
    ELSE SUBSTRING_INDEX(TRIM(`fullname`), ' ', 1)
  END,
  `middle_name` = CASE
    WHEN LOCATE(',', `fullname`) > 0
      AND LOCATE(' ', TRIM(SUBSTRING_INDEX(`fullname`, ',', -1))) > 0 THEN
      NULLIF(TRIM(SUBSTRING(
        TRIM(SUBSTRING_INDEX(`fullname`, ',', -1)),
        LOCATE(' ', TRIM(SUBSTRING_INDEX(`fullname`, ',', -1))) + 1
      )), '')
    WHEN LOCATE(' ', TRIM(`fullname`)) > 0
      AND LOCATE(' ', TRIM(`fullname`)) <
        CHAR_LENGTH(TRIM(`fullname`)) - CHAR_LENGTH(SUBSTRING_INDEX(TRIM(`fullname`), ' ', -1))
      THEN NULLIF(TRIM(SUBSTRING(
        TRIM(`fullname`),
        LOCATE(' ', TRIM(`fullname`)) + 1,
        CHAR_LENGTH(TRIM(`fullname`)) - LOCATE(' ', TRIM(`fullname`))
          - CHAR_LENGTH(SUBSTRING_INDEX(TRIM(`fullname`), ' ', -1))
      )), '')
    ELSE NULL
  END,
  `last_name` = CASE
    WHEN LOCATE(',', `fullname`) > 0
      THEN TRIM(SUBSTRING_INDEX(`fullname`, ',', 1))
    ELSE SUBSTRING_INDEX(TRIM(`fullname`), ' ', -1)
  END;

ALTER TABLE `users`
  MODIFY COLUMN `first_name` VARCHAR(80) NOT NULL,
  MODIFY COLUMN `last_name` VARCHAR(80) NOT NULL,
  DROP COLUMN `fullname`;

ALTER TABLE `students`
  ADD COLUMN `first_name` VARCHAR(80) DEFAULT NULL AFTER `student_id`,
  ADD COLUMN `middle_name` VARCHAR(80) DEFAULT NULL AFTER `first_name`,
  ADD COLUMN `last_name` VARCHAR(80) DEFAULT NULL AFTER `middle_name`;

UPDATE `students`
SET
  `first_name` = CASE
    WHEN LOCATE(',', `fullname`) > 0
      THEN SUBSTRING_INDEX(TRIM(SUBSTRING_INDEX(`fullname`, ',', -1)), ' ', 1)
    ELSE SUBSTRING_INDEX(TRIM(`fullname`), ' ', 1)
  END,
  `middle_name` = CASE
    WHEN LOCATE(',', `fullname`) > 0
      AND LOCATE(' ', TRIM(SUBSTRING_INDEX(`fullname`, ',', -1))) > 0 THEN
      NULLIF(TRIM(SUBSTRING(
        TRIM(SUBSTRING_INDEX(`fullname`, ',', -1)),
        LOCATE(' ', TRIM(SUBSTRING_INDEX(`fullname`, ',', -1))) + 1
      )), '')
    WHEN LOCATE(' ', TRIM(`fullname`)) > 0
      AND LOCATE(' ', TRIM(`fullname`)) <
        CHAR_LENGTH(TRIM(`fullname`)) - CHAR_LENGTH(SUBSTRING_INDEX(TRIM(`fullname`), ' ', -1))
      THEN NULLIF(TRIM(SUBSTRING(
        TRIM(`fullname`),
        LOCATE(' ', TRIM(`fullname`)) + 1,
        CHAR_LENGTH(TRIM(`fullname`)) - LOCATE(' ', TRIM(`fullname`))
          - CHAR_LENGTH(SUBSTRING_INDEX(TRIM(`fullname`), ' ', -1))
      )), '')
    ELSE NULL
  END,
  `last_name` = CASE
    WHEN LOCATE(',', `fullname`) > 0
      THEN TRIM(SUBSTRING_INDEX(`fullname`, ',', 1))
    ELSE SUBSTRING_INDEX(TRIM(`fullname`), ' ', -1)
  END;

ALTER TABLE `students`
  MODIFY COLUMN `first_name` VARCHAR(80) NOT NULL,
  MODIFY COLUMN `last_name` VARCHAR(80) NOT NULL,
  DROP INDEX `idx_students_status_fullname`,
  DROP COLUMN `fullname`,
  ADD KEY `idx_students_status_name` (`status`, `last_name`, `first_name`);
