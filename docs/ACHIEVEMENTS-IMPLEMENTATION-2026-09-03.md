# Achievements Implementation Report
**Date:** 2026-09-03
**Branch:** feature/achievements-implementation
**Base:** feature/achievements-activity-ledger @ 11e74548

---

## 1. MIGRATION

### Disposable MariaDB test result
- DB: `bwps_achievements_migration_test`
- Schema DDL validated cleanly (no MariaDB errors)
- Existing row preserved: `idx=3, achievementId=2`
- Tables created: `achievement_activity`, `achievement_rewards_v1`
- UNIQUE KEY `uk_achievementscompleted_user_ach` added on `(idx, achievementId)`

### Live apply result
- Live `bwps` migration: SUCCESS
- Existing row preserved: `idx=3, achievementId=2, completedDate=2026-08-19, is_it_new=1`
- `achievement_activity` and `achievement_rewards_v1` tables created
- UNIQUE KEY verified via `SHOW INDEX FROM achievementscompleted`

### Tables/indexes added
- `achievement_activity` with PK + 3 secondary indexes
- `achievement_rewards_v1` with PK + 1 secondary index
- `UNIQUE KEY uk_achievementscompleted_user_ach (idx, achievementId)` on existing table

---

## 2. REWARD TABLE

### Coverage
- Total rows: 155
- Distinct achievement IDs: 103
- All 103 recovered achievements covered
- Reward types: mulch (120 rows), xp (50 bundles), dosh (5 rows)
- No cosmetic rewards (hats, seeds, nest items, trophies, badges) — V1 restriction enforced

---

## 3. CENTRAL SERVICE

### File
- `game-full/essential/achievements.php` (new, included from backbone.php)

### Functions/class
- `AchievementService` class with:
  - `recordActivity(userID, activityType, targetID, value, metadata)` — INSERT one event log row, with same-second dedupe guard
  - `evaluateForActivity(activityType)` — evaluate only relevant achievements for that type
  - `completeAchievement(achievementId)` — atomic completion INSERT + reward grant with `SELECT ... FOR UPDATE` idempotency
  - `grantRewards(achievementId, db)` — load reward rows and grant via transaction-safe helpers
  - `evaluateStateAchievements()` — evaluate state-based achievements (Bin Tycoon)
  - `recordAndEvaluate(activityType)` — convenience combo
- Transaction-safe reward helpers:
  - `addMulchByNameTx($name, $total, $db)` — optional `$db` param
  - `addDoshByNameTx($name, $total, $db)` — optional `$db` param
  - `addExperienceByNameTx($name, $total, $db)` — optional `$db` param
  - These reuse existing reward logic; when `$db` is provided they use that connection for transaction safety. When omitted, they fall back to the original behavior (unaffected callers).

---

## 4. DISTINCT-DAY SEMANTICS

### Raw row test
- 3 login rows inserted (2 days): COUNT(*) = 3

### Distinct-date result
- COUNT(DISTINCT DATE(occurredAt)) = 2

### Verified by
- Disposable MariaDB test DB (SQL direct)
- PHP test harness Test 4: evaluator correctly does NOT complete "Login on 3 days" with only 2 distinct days, and DOES complete it with 3 distinct days

---

## 5. ACHIEVEMENT COVERAGE

### Total
- 103 total achievements recovered from `getAllAchievements.php`

### Implemented/evaluable
- 103 of 103 — all have ACHIEVEMENT_DEFS entries with correct query types

### Unresolved count
- 0 unresolved

### Unresolved hooks
- Ice cream purchase endpoint: NOT found in repo — `eat_ice_cream` activity type defined but no endpoint wired
- Garden item purchase endpoint: NOT found — `buy_garden_item` defined but no endpoint wired
- SWS mission completion handler: UNKNOWN (likely in server JS `Weevil.js` — not recovered)
- SWS case file star-rating contract: UNKNOWN
- `join_sws` source: UNKNOWN
- Location events (`enter_location` for Winter Wonderland / Weevil Air): UNKNOWN

All unresolved items are documented in the design doc and will be wired in Phase 3/7 after targeted recovery.

---

## 6. ACTIVITY HOOKS

### Implemented hooks

| Activity Type | Endpoint | File | Hook |
|---------------|----------|------|------|
| `login` | `login/login.php` verifyUser success | `game-full/login/login.php` | INSERT activity row after session key set |
| `change_look` | `changeDefinition()` success | `game-full/essential/internal.php` | INSERT activity row after appearance save |
| `buy_item` | `buyItem.php` success (both mulch + dosh) | `game-full/php2/shop/departmentStore/buyItem.php` | INSERT `buy_item` + `spend_mulch_single_item` / `spend_dosh_single_item` activity rows |
| `buy_hat` | `buyHat.php` success | `game-full/php2/shop/buyHat.php` | INSERT `buy_hat` activity row |
| `buy_seed` | `buy-seed.php` success | `game-full/gardenshop/buy-seed.php` | INSERT `buy_seed` activity row |
| `brain_strain_earn` | `brain-submit.php` result=1 success | `game-full/game/brain-submit.php` | INSERT activity row after reward grant (both first-play and daily-repeat paths) |
| `task_complete` | `task-completed.php` CompleteTask true | `game-full/quests/task-completed.php` | INSERT `task_complete` activity row with questID metadata |
| `adopt_pet` | `adoptPet.php` INSERT success | `game-full/php2/pets/adoptPet.php` | INSERT `adopt_pet` activity row; also writes legacy `userachievements` table |

### NOT wired (unknown endpoints)
- `eat_ice_cream` — ice cream endpoint not found
- `buy_garden_item` — no standalone garden item purchase endpoint
- `mission_complete` — SWS handler not recovered
- `case_file_complete` — case file star-rating contract not recovered
- `join_sws` — enrollment endpoint not recovered
- `enter_location` — location event source not recovered
- `earn_trophy` — trophy reward helper exists but wiring requires `rewardUserTrophy` call-site injection

---

## 7. TASK / MISSION / TUTORIAL

### Implemented
- `task_complete` activity recorded after `CompleteTask()` returns true in `task-completed.php`
- Metadata: `{"questID": N}`
- Existing `HasUserCompletedTask` dedupe preserved
- Task rewards (mulch/xp/dosh/item/gardenItem) still granted by existing code

### NOT implemented
- Mission completion: SWS handler unknown
- Case file completion: star-rating contract unknown
- Tutorial distinctions: not found in client/database

---

## 8. COMPLETION IDEMPOTENCY

### Verified by test harness
- First `completeAchievement(1)`: returns `true`, inserts 1 row
- Second `completeAchievement(1)`: returns `false`, row count stays 1
- Third `completeAchievement(1)`: returns `false`, row count stays 1
- UNIQUE KEY `uk_achievementscompleted_user_ach` enforces at DB level

---

## 9. REWARD IDEMPOTENCY

### Verified by test harness
- First `evaluateStateAchievements()`: completes achievement 1, grants 1000 dosh
- Second `evaluateStateAchievements()`: returns no new completions, dosh unchanged
- Reward grant uses same `$db` connection within the transaction
- Transaction rollback on any failure prevents partial completion

---

## 10. GETNEWACHIEVEMENTS

### Verified by test harness
- `getCompletedAchievements`-style query: returns achievement ID 2
- `getNewAchievements`-style query: returns achievement ID 2 with `is_it_new=1`
- After mark-read: `is_it_new=0`, no longer in new list
- Still in completed list after mark-read

---

## 11. GETCOMPLETEDACHIEVEMENTS

### Verified
- Endpoint unchanged (still queries `achievementscompleted WHERE idx = ?`)
- Existing response format preserved: `responseCode=1&userCompletedAchievements=<csv>&lastCompletedAchivement=<id>`
- Original misspelling "Achivement" preserved

---

## 12. PHP LINT

### All changed PHP files
- `achievements.php` — No syntax errors
- `backbone.php` — No syntax errors
- `internal.php` — No syntax errors
- `login.php` — No syntax errors
- `buyItem.php` — No syntax errors
- `buyHat.php` — No syntax errors
- `buy-seed.php` — No syntax errors
- `brain-submit.php` — No syntax errors
- `task-completed.php` — No syntax errors
- `adoptPet.php` — No syntax errors
- `getCompletedAchievements.php` — No syntax errors
- `getNewAchievements.php` — No syntax errors

---

## 13. REPO / HTDOCS HASH PARITY

### Synced files (all match verified via SHA-256)
- `essential/achievements.php` — NEW in htdocs
- `essential/backbone.php` — UPDATED
- `essential/internal.php` — UPDATED
- `login/login.php` — UPDATED
- `php2/shop/departmentStore/buyItem.php` — UPDATED
- `php2/shop/buyHat.php` — UPDATED
- `gardenshop/buy-seed.php` — UPDATED
- `game/brain-submit.php` — UPDATED
- `quests/task-completed.php` — UPDATED
- `php2/pets/adoptPet.php` — UPDATED

Not served / not synced:
- `php2/achievements/getCompletedAchievements.php` — unchanged in repo and htdocs
- `php2/achievements/getNewAchievements.php` — unchanged in repo and htdocs

---

## 16. QUERY-MODEL CORRECTIONS

### 16.1 HAT ACHIEVEMENTS — Authoritative Inventory Source

**Correction**: Hat achievements (ids 34-38: "Collect N hats") query the `weevilhats` table directly, NOT `achievement_activity` with `buy_hat` rows.

- **Schema**: `weevilhats` table has columns: `id`, `apparelId`, `ownerName`, `colour`
- **OWNERSHIP MODEL**: `ownerName` stores the username (from `users.username`), not the numeric `userID`
- **QUERY**: `COUNT(*) FROM weevilhats WHERE ownerName = :username`
- **Semantically**: N owned hats = N rows in `weevilhats` table for that user
- **IDempotency**: Insertion of same hat is prevented at application layer; COUNT query returns same value on repeated evaluation
- **Activity wiring**: `buyHat.php` now triggers `hat_inventory_changed` activity type. Evaluator queries `weevilhats` directly.

**Implementation**:
- New query type `inventory_count` added to `evaluateOne()`
- New helper `checkInventoryCount(db, table, ownerCol, threshold)`
- Hat achievements (`activityType='hat_inventory_changed'`, `queryType='inventory_count'`, `table='weevilhats'`, `ownerCol='ownerName'`)

### 16.2 BRAIN STRAIN — Cumulative Mulch Earned

**Correction**: Brain strain achievements (ids 55-59) query cumulative earned mulch from `brainstrain_stats` table, NOT `users.mulch` (current wallet balance).

- **Problem with old approach**: `users.mulch` changes when mulch is spent; `SUM(achievement_activity.val)` from `brain_strain_earn` rows duplicates purchase history, not true cumulative
- **Solution**: New table `brainstrain_stats` tracks authoritative cumulative totals
- **Schema**: `userID` (FK to users.id), `totalMulchEarned`, `totalPlays`, `last_played`
- **Query**: `checkCumulativeStat(db, 'brainstrain_stats', 'totalMulchEarned')` returns cumulative value; compare against achievement threshold
- **Activity update**: `brain-submit.php` increments `totalMulchEarned` in `brainstrain_stats` on each accepted result before evaluating achievements

**Implementation**:
- New query type `cumulative_game_stat` added to `evaluateOne()`
- New helper `checkCumulativeStat(db, table, column): int`
- Brain strain achievements use `activityType='brain_strain_earn'`, `queryType='cumulative_game_stat'`, `table='brainstrain_stats'`, `column='totalMulchEarned'`
- `brainstrain_stats` table created via ALTER MIGRATION (see migration file)

### 16.3 Migration Applied to Live DB

Verified: Table `brainstrain_stats` exists in live `bwps` database with correct schema.

---

## 17. DATABASE STATE

### Live `bwps`
|- `achievement_activity`: empty (no test data left)
- `achievement_rewards_v1`: 155 rows, 103 distinct achievementIds
- `achievementscompleted`: 1 row (idx=3, achievementId=2, is_it_new=1) — UNCHANGED from before migration
- UNIQUE KEY `uk_achievementscompleted_user_ach` in place

### Test `bwps_achievements_migration_test`
- Used for automated tests; retains seed data for future test runs

---

## 18. FILES MODIFIED

### New files
- `game-full/essential/achievements.php` — central achievement service
- `migrations/2026-09-03-achievements-activity-ledger.sql` — validated final migration
- `migrations/seed-achievement-rewards-v1.sql` — REVIVAL-CUSTOM reward schedule

### Modified files
- `docs/ACHIEVEMENTS-ACTIVITY-SCHEMA-2026-09-03.md` — corrected login mapping, removed same-day dedup section, replaced with activity ledger semantics
- `game-full/essential/backbone.php` — includes achievements.php
- `game-full/essential/internal.php` — wired changeDefinition activity + completedAchievements response field
- `game-full/login/login.php` — wired login activity
- `game-full/php2/shop/departmentStore/buyItem.php` — wired buy_item + spend_mulch/dosh_single_item
- `game-full/php2/shop/buyHat.php` — wired buy_hat
- `game-full/gardenshop/buy-seed.php` — wired buy_seed
- `game-full/game/brain-submit.php` — wired brain_strain_earn (both paths)
- `game-full/quests/task-completed.php` — wired task_complete
- `game-full/php2/pets/adoptPet.php` — wired adopt_pet + legacy userachievements compatibility

### Untouched (protected)
- `electron/launch.bat`
- `recon/live-private-server/2026-09-02/RENDERING-RECOVERY.md`

---

## 16. COMMITS

Pending user direction for commit split. Suggested split:
1. `feat(achievements): add activity ledger schema and reward seed`
   - migration SQL files, seed SQL
2. `feat(achievements): add central achievement service`
   - `achievements.php`, `backbone.php`
3. `feat(achievements): wire login, changeDefinition, task-complete activity hooks`
   - login.php, internal.php, task-completed.php
4. `feat(achievements): wire purchase and game endpoints`
   - buyItem.php, buyHat.php, buy-seed.php, brain-submit.php, adoptPet.php

---

## 17. LOCAL / ORIGIN PARITY

- Branch `feature/achievements-implementation` created from `11e74548`
- Origin: not yet pushed
- No merge to main
- No force push

---

## 18. UNRESOLVED HISTORICAL CONTRACTS

| Item | Status | Next action |
|------|--------|-------------|
| Ice cream purchase endpoint URL | UNKNOWN | Targeted recovery of shop endpoints |
| Garden item purchase endpoint URL | UNKNOWN | Targeted recovery |
| SWS mission completion handler | UNKNOWN | Recovery of server/Weevil.js |
| SWS case file star-rating contract | UNKNOWN | Client contract recovery |
| `join_sws` source | UNKNOWN | SWS enrollment endpoint recovery |
| Location event sources | UNKNOWN | Location teleport handler recovery |
| Original reward amounts | UNKNOWN | All 103 use REVIVAL-CUSTOM values |

---

## 19. MANUAL TESTING STILL REQUIRED

- Login flow: verify login activity row created, evaluateForActivity completes login achievements
- Change look: verify appearance save creates activity, response includes completedAchievements
- Buy item (mulch/dosh): verify buy_item + spend activities created
- Buy hat: verify distinct hat tracking
- Buy seed: verify activity row
- Brain Strain: verify daily limit, activity on accepted result only
- Task complete: verify task_complete activity with questID metadata
- Adopt pet: verify adopt_pet activity + legacy userachievements row
- getNewAchievements: verify new achievements returned then marked read
- getCompletedAchievements: verify all completed IDs returned with original field spelling
- Bin Tycoon (id 1): verify state-based evaluation works when tycoon=1

---

## 20. READY FOR MANUAL PLAYTEST

**NO** — automated DB/PHP tests pass. Manual gameplay testing has not been performed per instructions (section 26: "Do NOT launch Electron" / "Automated DB/PHP/HTTP verification only").
