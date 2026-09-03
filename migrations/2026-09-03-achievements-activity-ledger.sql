-- ============================================================================
-- IMPLEMENTATION MIGRATION — Achievements Activity Ledger (V1)
-- ============================================================================
-- Status: Validated against disposable MariaDB test DB before live apply.
-- Purpose: Add activity ledger + reward config + idempotency guard.
--
-- SAFETY RULES:
--   - NO DROP TABLE
--   - NO TRUNCATE
--   - No changes to existing achievementscompleted data
--   - UNIQUE KEY addition is additive only
-- ============================================================================


-- ---------------------------------------------------------------------------
-- TABLE: achievement_activity
-- ---------------------------------------------------------------------------
-- Event log for achievement-relevant game actions.
-- Every legitimate successful action produces a row.
-- Distinct-day calculations are done in evaluator queries, NOT via schema.
--
CREATE TABLE IF NOT EXISTS `achievement_activity` (
  `id`           bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `userID`       int(11)      NOT NULL COMMENT 'users.id — matches achievementscompleted.idx',
  `activityType` varchar(64)  NOT NULL,
  `targetID`     int(11)      DEFAULT NULL,
  `value`        int(11)      NOT NULL DEFAULT 1,
  `occurredAt`   datetime(3)  NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
  `metadata`     json         DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_activity_time` (`userID`, `activityType`, `occurredAt`),
  KEY `idx_activity_type`      (`activityType`, `occurredAt`),
  KEY `idx_user_time`          (`userID`, `occurredAt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ---------------------------------------------------------------------------
-- TABLE: achievement_rewards_v1
-- ---------------------------------------------------------------------------
-- V1 reward configuration. One row per (achievementId, rewardType).
-- An achievement with a bundle reward (e.g. 500 Mulch + 100 XP) has multiple rows.
--
CREATE TABLE IF NOT EXISTS `achievement_rewards_v1` (
  `id`            int(11)     NOT NULL AUTO_INCREMENT,
  `achievementId` int(11)     NOT NULL COMMENT 'references achievements.id',
  `rewardType`    enum('mulch','dosh','xp') NOT NULL,
  `rewardValue`   int(11)     NOT NULL DEFAULT 1,
  `source`        enum('original','revival') NOT NULL DEFAULT 'revival',
  PRIMARY KEY (`id`),
  KEY `idx_ach_reward` (`achievementId`, `rewardType`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ---------------------------------------------------------------------------
-- INDEX: achievementscompleted one-completion-guard
-- ---------------------------------------------------------------------------
-- Additive UNIQUE KEY enforcing one completion per (idx, achievementId).
-- Pre-existing data: 1 row (idx=3, achievementId=2). No duplicates exist.
-- MariaDB supports UNIQUE KEY on mediumtext; the existing value '2' is consistent.
--
ALTER TABLE `achievementscompleted`
  ADD CONSTRAINT `uk_achievementscompleted_user_ach`
  UNIQUE KEY `uk_achievementscompleted_user_ach` (`idx`, `achievementId`);


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
