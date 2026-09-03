-- ============================================================
-- Bin Pets DB Migration 2026-09-03
-- Run against local bwps before first Bin Pets playtest.
-- Safe to re-run: uses IF NOT EXISTS / MODIFY.
-- NO DROP TABLE.
-- ============================================================

-- pets: add columns that exist in the package but not locally.
ALTER TABLE `pets`
  ADD COLUMN IF NOT EXISTS `experience` int(11) NOT NULL DEFAULT 0 AFTER `fitness`,
  ADD COLUMN IF NOT EXISTS `lastStatChange` int(11) NOT NULL DEFAULT 0 AFTER `experience`,
  ADD COLUMN IF NOT EXISTS `rented` int(11) NOT NULL DEFAULT 0 AFTER `lastStatChange`,
  ADD COLUMN IF NOT EXISTS `adoptedDate` datetime NOT NULL DEFAULT current_timestamp() AFTER `rented`;

-- petacquiredskills: local has no id PK; add it if missing.
-- MariaDB's ALTER TABLE MODIFY for auto-inc PK is a bit picky; safest is:
--   if id already exists this is a no-op.
ALTER TABLE `petacquiredskills`
  ADD COLUMN IF NOT EXISTS `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST,
  MODIFY COLUMN IF NOT EXISTS `skillLevel` float NOT NULL;

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
