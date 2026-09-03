-- ============================================================
-- Bin Pets DB Migration 2026-09-03
-- Run against local bwps before first Bin Pets playtest.
-- Safe to re-run: guarded by INFORMATION_SCHEMA checks.
-- NO DROP TABLE.
-- ============================================================

-- pets: add columns that exist in the package but not locally.
-- (already executed on first run; IF NOT EXISTS guards no-op)
ALTER TABLE `pets`
  ADD COLUMN IF NOT EXISTS `experience` int(11) NOT NULL DEFAULT 0 AFTER `fitness`,
  ADD COLUMN IF NOT EXISTS `lastStatChange` int(11) NOT NULL DEFAULT 0 AFTER `experience`,
  ADD COLUMN IF NOT EXISTS `rented` int(11) NOT NULL DEFAULT 0 AFTER `lastStatChange`,
  ADD COLUMN IF NOT EXISTS `adoptedDate` datetime NOT NULL DEFAULT current_timestamp() AFTER `rented`;

-- petacquiredskills: ensure id PK present and skillLevel is float.
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'petacquiredskills' AND COLUMN_NAME = 'id';

SET @skill_exists = 0;
SELECT COUNT(*) INTO @skill_exists FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'petacquiredskills' AND COLUMN_NAME = 'skillLevel';

SET @sql1 = IF(@col_exists = 0,
    "ALTER TABLE `petacquiredskills` ADD COLUMN `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST",
    "SELECT 1");
PREPARE stmt1 FROM @sql1;
EXECUTE stmt1;
DEALLOCATE PREPARE stmt1;

SET @sql2 = IF(@skill_exists > 0,
    "ALTER TABLE `petacquiredskills` MODIFY COLUMN `skillLevel` float NOT NULL",
    "SELECT 1");
PREPARE stmt2 FROM @sql2;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;

-- petacquiredtricks: create if missing.
CREATE TABLE IF NOT EXISTS `petacquiredtricks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `aptitude` float NOT NULL,
  `numBalls` int(11) NOT NULL,
  `pattern` varchar(255) NOT NULL,
  `difficulty` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `ownerID` varchar(255) NOT NULL,
  `petID` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- petfood: create if missing.
CREATE TABLE IF NOT EXISTS `petfood` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `weevilName` varchar(255) NOT NULL,
  `feeds` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `weevilName` (`weevilName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
