-- ============================================================================
-- PROPOSED MIGRATION — Achievements Activity Ledger (V1)
-- ============================================================================
-- Status: PROPOSAL ONLY — NOT APPLIED TO LIVE DB.
--
-- Purpose:
--   Introduce an activity ledger that records authoritative game actions, so
--   that achievement progress can be evaluated from first principles ("what did
--   the player actually do") rather than maintaining 103 separate counters.
--
-- Design goals:
--   - Preserve all existing achievement data (achievementcounters,
--     achievementscompleted, achievementtypes, etc.). These tables are NOT
--     dropped or altered. The existing read path used by
--     getCompletedAchievements.php / getNewAchievements.php continues to work
--     unchanged during Phase 1.
--   - Introduce `achievement_activity` as the new source of truth for raw
--     actions. The existing `achievementcounters` table remains as-is; a future
--     phase may migrate it to reads against `achievement_activity`.
--   - Introduce `achievement_rewards_v1` as the reward configuration table.
--     This is NOT merged into the live reward-grant flow until Phase 4.
--   - Reuse the existing `achievementscompleted` table for completion state
--     (it already has idx, achievementId, completedDate, is_it_new with a
--     primary key on `id`). We ADD a UNIQUE INDEX on (idx, achievementId) to
--     enforce the one-completion-per-user-per-achievement rule that Phase 4
--     relies on. This is additive only.
--
-- User identifier convention (PROVEN from bwps.sql):
--   The `users` table uses `id` int(11) as the primary key. The existing
--   achievement endpoints receive `idx` via POST, which maps 1:1 to users.id
--   (confirmed in getCompletedAchievements.php and getNewAchievements.php).
--   Therefore achievement_activity.userID mirrors the int(11) users.id
--   convention, consistent with achievementscompleted.idx and
--   achievementcounters.idx.
--
-- NO DROP TABLE anywhere in this file.
-- NO destructive reset.
-- ============================================================================


-- ---------------------------------------------------------------------------
-- TABLE: achievement_activity
-- ---------------------------------------------------------------------------
-- The activity ledger. Every row = one authoritative game action that an
-- achievement might care about. Achievements are evaluated by querying this
-- table, not by inspecting a separate per-achievement counter.
--
-- Column rationale:
--   id           PK auto-increment. Needed for stable ordering when multiple
--                activities share the same (userID, activityType, occurredAt)
--                timestamp (e.g. two tasks completed in the same second).
--   userID       int(11), matches users.id. Mirrors achievementscompleted.idx
--                and achievementcounters.idx. NOT nullable — every activity
--                belongs to a real user.
--   activityType varchar(64). The canonical action enum (see ACTIVITY TYPES
--                section below). NOT nullable. Example: 'login',
--                'eat_ice_cream', 'change_look'.
--   targetID     int(11) unsigned DEFAULT NULL. The specific entity the action
--                acted upon, when relevant. Examples:
--                  - eat_ice_cream: the ice-cream item ID purchased
--                  - change_look: NULL (the whole look definition is the action)
--                  - buy_item: the nest-item item ID
--                  - task_complete: the task ID
--                  - mission_complete: the mission ID (grade not stored here;
--                    grade progression is handled separately because the same
--                    mission can be "completed" at D, C, B, A — see dedup notes)
--                Nullable because not every activity type has a target.
--   value        int(11) DEFAULT 1. Quantity or scalar associated with the
--                action. Defaults to 1 so count-based achievements can simply
--                SUM(value) without special-casing. Examples:
--                  - brain_strain_earn: the mulch earned in that session
--                  - buy_item: quantity (always 1 for single-item purchases)
--   occurredAt   datetime(3). Millisecond precision helps order same-second
--                events and distinguishes retries from real rapid actions.
--                DEFAULT CURRENT_TIMESTAMP(3).
--   metadata     json DEFAULT NULL. Optional structured context that does not
--                fit the typed columns. Examples:
--                  - mission_complete: {"grade": "A"}
--                  - task_complete: {"questID": 7}
--                  - For V1 this column is optional and unused by core queries.
--                Included because retrofitting it later would require a table
--                rebuild; it is cheap to add now and kept nullable.
--
-- Indexes (chosen to match the real recovered query patterns):
--   PRIMARY KEY (id)
--   KEY idx_user_activity_time (userID, activityType, occurredAt)
--       -> Serves: COUNT/SUM by user+type, DISTINCT DATE by user+type,
--          first/last occurrence, and ordered iteration for "new" checks.
--          This is the PRIMARY operational index. All 7 achievement query
--          patterns in section 13 are covered by it.
--   KEY idx_activity_type (activityType, occurredAt)
--       -> Serves: admin/analytics and leaderboard-style aggregation across
--          all users for a given activity type (e.g. "who played Brain Strain
--          this week"). Exists to avoid a full scan of the PK for type-scoped
--          analytics.
--   KEY idx_user_time (userID, occurredAt)
--       -> Serves: login-day streak counting and "active on date X"
--          membership checks without scanning a specific type.
--
-- Storage: InnoDB (project default per bwps.sql). Charset utf8mb4 to match
-- the users table.
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
-- INDEX: achievementscompleted one-completion-guard
-- ---------------------------------------------------------------------------
-- The existing achievementscompleted table has no uniqueness constraint, so a
-- buggy or replayed completion could insert the same (idx, achievementId)
-- twice. Phase 4 (completion + reward grant) requires idempotency, so we
-- propose adding this UNIQUE KEY as part of the V1 rollout. It is additive
-- and does NOT drop or alter any existing column.
--
-- Pre-existing data must be de-duplicated before this can be applied if
-- duplicates exist. Current state: achievementscompleted has 1 row, so this
-- is safe against the current live DB.
--
ALTER TABLE `achievementscompleted`
  ADD CONSTRAINT `uk_achievementscompleted_user_ach`
  UNIQUE KEY `uk_achievementscompleted_user_ach` (`idx`, `achievementId`);


-- ---------------------------------------------------------------------------
-- TABLE: achievement_rewards_v1
-- ---------------------------------------------------------------------------
-- V1 reward configuration. One row per (achievementId, rewardType). An
-- achievement with a bundle reward (e.g. 500 Mulch + 100 XP) has multiple rows.
--
-- Columns:
--   id           PK auto-increment.
--   achievementId int(11), NOT NULL. References achievements.id (PROVEN from
--                bwps.sql schema). In Phase 4 the achievement evaluator will
--                look up rewards here rather than hardcoding amounts in PHP.
--   rewardType   enum('mulch','dosh','xp'). Restricted to the three V1 reward
--                types per the task brief. Other reward types (hats, seeds,
--                nest items, trophies) are explicitly EXCLUDED from V1.
--   rewardValue  int(11), NOT NULL. Quantity granted on completion.
--   source       enum('original','revival'). 'original' = recovered from live
--                game data; 'revival' = custom proposal for the revival. This
--                lets the operator review balances before going live and lets
--                the evaluator surface provenance at runtime.
--
-- Indexes:
--   PRIMARY KEY (id)
--   KEY idx_ach_reward (achievementId, rewardType)
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

-- NOTE: No reward rows are inserted here. The revival reward schedule is
-- documented in docs/ACHIEVEMENTS-ACTIVITY-SCHEMA-2026-09-03.md and must be
-- reviewed/approved before any INSERT into achievement_rewards_v1.


-- ---------------------------------------------------------------------------
-- SEED: achievement catalogue into `achievements` + `achievementtypes`
-- ---------------------------------------------------------------------------
-- The PROVEN catalogue (103 achievements across 27 type groups) currently
-- lives as a static JSON file at game-full/php2/achievements/getAllAchievements.php.
-- The live `achievements` and `achievementtypes` tables are EMPTY (0 rows).
--
-- Phase 2 will parse that JSON and INSERT the rows. This migration does NOT
-- perform that insert — it only creates the schema. The seed step is called
-- out here as a dependency note so the operator knows the tables will start
-- empty until the seed runs.
--
-- Seed plan (NOT executed here):
--   INSERT INTO achievementtypes (order, name, colour, imageName, description, isLive)
--     SELECT typeOrder, typeName, typeColour, imageName, description, 1
--     FROM (parsed getAllAchievements.php JSON).
--   INSERT INTO achievements (typeId, name, order, module, descriptionForMe,
--       descriptionForVisitors, counterValue)
--     SELECT ... per sub-achievement.
--


-- ---------------------------------------------------------------------------
-- UNRESOLVED ASSUMPTIONS / RISKS
-- ---------------------------------------------------------------------------
-- 1. `module` column in `achievements`: PROVEN schema, UNKNOWN fill value.
--    The JSON does not carry a module string. Proposal: set module = '' (empty)
--    for all revived rows, matching the original schema default (NOT NULL text,
--    no DEFAULT). REQUIRES operator approval before insert.
--
-- 2. `counterValue` in `achievements`: PROVEN schema, UNKNOWN whether the
--    original game populated it. The JSON `getAllAchievements.php` response does
--    not include per-achievement counter thresholds. Proposal: leave NULL (the
--    column is nullable per bwps.sql). The activity-ledger evaluator will
--    derive thresholds from the achievement name/description at Phase 3 and
--    persist them into a future `achievement_criteria` table (Phase 2.5).
--
-- 3. mission_complete grading: achievements 100-104, 105-109, etc. are
--    per-grade. The activity ledger records `mission_complete` with
--    metadata.grade. Dedup: one activity row per mission+grade (same mission
--    repeated at a higher grade is a DISTINCT action). This is PROVEN from the
--    JSON (each grade is a separate achievementId).
--
-- 4. `change_look` DISTINCT DAY vs COUNT: achievement 3 is "Change your look"
--    (COUNT — any single change), achievements 4/6/7/8 are "on N different
--    days" (COUNT DISTINCT DATE). The activity type is the same ('change_look')
--    but the query rule differs. This distinction is RECOVERED from the JSON
--    names and is handled in the evaluator, NOT in the schema.
--
-- 5. `login` achievement (39, Welcome): PROVEN from JSON. Activity type
--    'login'. Query: COUNT(DISTINCT DATE(occurredAt)) for days-based, or
--    EXISTS for the single "Welcome" achievement.
--
-- 6. Reward values for ALL achievements are UNDEFINED (UNKNOWN). No original
--    mulch/dosh/xp amounts were recovered from the static JSON or SWFs. The
--    full proposal schedule lives in docs/ACHIEVEMENTS-ACTIVITY-SCHEMA-2026-09-03.md.
--
-- 7. The `achievement_activity.userID` uses int(11) to match users.id and the
--    existing idx convention. If users.id is ever migrated to bigint, this
--    column must be updated too.
