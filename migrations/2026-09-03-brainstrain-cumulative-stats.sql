-- ============================================================================
-- ALTER MIGRATION — Add brainstrain_stats cumulative table (V1)
-- ============================================================================
-- Status: Validated against disposable MariaDB test DB.
-- Purpose: Add authoritative cumulative Brain Strain mulch-earned tracker.
--
-- SAFETY RULES:
--   - NO DROP TABLE
--   - NO TRUNCATE
--   - No changes to existing achievementscompleted data
-- ============================================================================

-- ---------------------------------------------------------------------------
-- TABLE: brainstrain_stats
-- ---------------------------------------------------------------------------
-- Authoritative cumulative Brain Strain statistics per user.
-- This table is the source of truth for Brain Strain achievement evaluation.
--
-- WHY A NEW TABLE: singleplayergames_stats lacks a cumulative mulchEarned column.
-- We cannot reliably reconstruct totals from it (only individual bestScore/averageScore).
-- We also cannot use users.mulch (current wallet balance decreases when spent).
--
-- Schema: one row per (userID), maintained incrementally on each accepted submission.
--
CREATE TABLE IF NOT EXISTS `brainstrain_stats` (
  `userID`          int(11)      NOT NULL COMMENT 'users.id',
  `totalMulchEarned` int(11)     NOT NULL DEFAULT 0,
  `totalPlays`      int(11)      NOT NULL DEFAULT 0,
  `last_played`     varchar(255) DEFAULT NULL,
  PRIMARY KEY (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
