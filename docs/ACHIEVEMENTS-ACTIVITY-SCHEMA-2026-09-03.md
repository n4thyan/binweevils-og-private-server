# Achievements Activity Ledger — Design & Recovery  
**Date:** 2026-09-03  
**Branch:** feature/achievements-activity-ledger  
**Status:** Architecture / recovery phase ONLY — no live DB changes, no implementation

---

## CURRENT ACHIEVEMENT IMPLEMENTATION

### Existing DB tables (PROVEN — bwps.sql schema dump)

| Table                  | Columns                                                                 | Current live row count |
|------------------------|-------------------------------------------------------------------------|------------------------|
| `achievementcounters`     | id, idx, counterId, counter, lastUpdated                                    | 0                      |
| `achievements`           | id, name, typeId, order, module, descriptionForMe, descriptionForVisitors, counterValue | 0 |
| `achievementscompleted`   | id, idx, achievementId (mediumtext, default '1'), completedDate, is_it_new | 1 (idx=3, achievementId=2) |
| `achievementtags`        | id, tags                                                                  | 0                      |
| `achievementtypes`       | id, order, name, colour, imageName, description, isLive                   | 0                      |
| `achievementtypetags`    | id, typeId, tagId                                                          | 0                      |

**Live DB state confirmed** via `mysql -u root` (C:\xampp\mysql\bin\mysql.exe):
- `achievements` = empty (0 rows)
- `achievementtypes` = empty (0 rows)
- `achievementcounters` = empty (0 rows)
- `achievementtags` / `achievementtypetags` = empty (0 rows)
- `achievementscompleted` = 1 row: `{id=5, idx=3, achievementId=2, completedDate=2026-08-19, is_it_new=1}`
  - Per existing table schema, `idx` maps to `users.id` and `achievementId` maps to `achievements.id`.

### Existing PHP endpoints (PROVEN — present in repo and htdocs, byte-identical)

| File                          | Role                                                                                     |
|-------------------------------|------------------------------------------------------------------------------------------|
| `getAllAchievements.php`      | **Static JSON file** (NOT executable PHP — starts with `{`). Contains the full 103-achievement catalogue as the client contract. |
| `getCompletedAchievements.php`| Returns completed achievement IDs for a user. Queries `achievementscompleted WHERE idx = ?`. Uses `confirmSessionKey` + `checkHash`. Response: `responseCode=...&userCompletedAchievements=<csv>&lastCompletedAchivement=<id>` (note the original misspelling "Achivement"). |
| `getNewAchievements.php`      | Returns achievements completed but not yet shown (`is_it_new=1`), then atomically sets `is_it_new=0` within a transaction (`FOR UPDATE` + `UPDATE`). Response: `responseCode=...&newAchievements=<csv>`. |
| `index.php` (htdocs only)     | "sorry, nothing here for you." (standard placeholder, NOT in repo) |

**Repo ↔ htdocs sync:** All three `.php` files are byte-identical (SHA-256 verified).

### Existing JS server (server/Main.js, server/BinWeevils.js, server/BinWeevilsWeb.js)

- `Main.js` starts two servers: `BinWeevils` on TCP port 9339, `BinWeevilsWeb` on port 2087.
- `server/server.js` runs a WebSocket status bridge on port 10843.
- **No achievement-specific logic currently exists in the Node server.** Achievement
  completion is currently driven only by PHP endpoints (no `recordAchievement` call
  found in any `.js` file under `server/`).
- `internal.php` references an achievement-related string at line 526:
  `completedAchievements=` (in the `changeDefinition` response) — this is a vestigial
  empty field (the original client likely populated it from its own state).

### Reward helpers in internal.php (PROVEN)

| Function                  | Lines    | Mechanism                              |
|---------------------------|----------|----------------------------------------|
| `addMulchByName($name, $total)` | 1331 | `UPDATE users SET mulch = mulch + ? WHERE username = ?` |
| `addMulchByNameMod($name, $total)` | 1351 | Same as above, NO session/auth check — used for server-initiated grants. |
| `addDoshByName($name, $total)` | 1365 | `UPDATE users SET dosh = dosh + ? WHERE username = ?` |
| `addExperienceByName($name, $total)` | 1293 | `UPDATE users SET xp = xp + ?, xp1 = xp1 + ? WHERE username = ?` |
| `addExperienceByNameMod($name, $total)` | ~1315 | Session-less XP grant. |
| `rewardUserTrophy($weevilname, $userIDX, $level)` | 1580 | Inserts into `usertrophies` table. |
| `sendAlert(...)` | 3261 | Sends a Flash alert to the player. |
| `rewardItem(...)` | 2930 | Rewards an inventory item. |
| `rewardGardenItem(...)` | — | Rewards a garden item. |
| `rewardSpecialMoves(...)` | — | Used by task-completed.php for special moves. |

These helpers are the authoritative V1 reward path. The achievement evaluator must
call `addMulchByName`, `addDoshByName`, `addExperienceByName` — NOT write direct
balance mutations.

---

## RECOVERED CLIENT CONTRACT

### Full achievement catalogue (103 achievements, 27 type groups)

Source: `game-full/php2/achievements/getAllAchievements.php` — static JSON.
This is the PROVEN client contract for what achievements exist, their display
metadata, and their progression structure.

**Achievement Types (27 groups):**

| Type ID | TypeName                        | ImageName         | Tags                          | Description                          |
|---------|---------------------------------|-------------------|-------------------------------|--------------------------------------|
| 1       | Top Tycoon                      | BB1               | featured,new,home             | Became a Tycoon                      |
| 2       | Perfect Pet                     | BB2               | new,home,binPet               | Adopted a Bin Pet                    |
| 3       | Style Icon                    | BB3               | new,home                      | Rocked 50 different looks            |
| 4       | Bin Around                      | BB4               | new,home                      | 1 Year on Bin Weevils                |
| 5       | Super Fan                       | BB5               | new,home                      | I'm a loyal fan                      |
| 6       | Mulch Maniac                    | BB6               | new,home,binterior            | Spent loads of Mulch                 |
| 7       | Dosh Galore                     | BB7               | new,home,binterior            | Spent loads of Dosh                  |
| 8       | Decorator                       | BB8               | home,new,binterior            | Bought 1000 nest items               |
| 9       | Hip Hatter                      | BB9               | home,new                      | Collected 100 hats                   |
| 10      | Green Thumb                     | BB10              | new,home,garden               | Bought 5000 seeds                    |
| 11      | Great Garden                    | BB11              | home,new,garden               | Bought cool garden stuff             |
| 12      | Ice Cream Monster!              | BB12              | new,home,explorer             | Purchased an ice cream on 50 different days |
| 13      | Bin Genuis                      | BB13              | new,home,games                | Earned 25000 Mulch in the Brain Strain |
| 14      | Best Nest                       | BB14              | home,new,binterior            | Earned 15+ trophies                  |
| 22      | Weevily Welcome                 | BB22              | new,home                      | Joined Bin Weevils                   |
| 30      | SWS Agent                       | BB15              | sws                           | Joined the SWS                       |
| 31      | Raiders of the Lost Bin Pet     | BB_SWS_RLBP       | sws                           | Mission completed with an A grade    |
| 32      | The Hunt for Weevil X           | BB_SWS_HWX        | sws                           | Mission completed with an A grade    |
| 33      | Festive Fun                     | BB17              | explorer                      | Entered the Winter Wonderland        |
| 34      | Danger at Dosh's Palace         | BB_SWS_DDP        | sws                           | Mission completed with an A grade    |
| 35      | Showdown at Tycoon TV Towers    | BB_SWS_TTT        | sws                           | Mission completed with an A grade    |
| 36      | Laboratory Lockdown             | BB_SWS_LLD        | sws                           | Mission completed with an A grade    |
| 37      | Case File 1: Good vs WeEvil     | BB37              | sws                           | Case File 1 completed                |
| 38      | Case File 2 Micro Mayhem        | BB38              | sws                           | Case File 2 completed                |
| 39      | Case File 3: Scribbles...        | BB39              | sws                           | Case File 3 Completed                |
| 40      | Weevil Holiday                  | BB_weevilAir      | explorer                      | Flew with Weevil Air                 |
| 41      | Best Garden                     | BB_gardenTrophy   | garden                        | Earned 15+ trophies                  |

**Individual achievements (103 total):**

Full list with IDs, names, categories, and query type inferred from the JSON text.
Key progression series:

- **Welcome (id 39):** Single "Welcome" — EXISTS check on login.
- **Become a Bin Tycoon (id 1):** Single — Tycoon status check (`users.tycoon=1`).
- **Adopt a Bin Pet (id 2):** Single — adoptPet endpoint.
- **Change your look (ids 3,4,6,7,8):** id 3 = COUNT(activity), ids 4/6/7/8 = COUNT DISTINCT DATE(activity). Same activity type `change_look`.
- **Play for N months/years (ids 9-13):** COUNT DISTINCT DATE of `login` activity (months/years inferred from name).
- **Super Fan login days (ids 14-18):** COUNT DISTINCT DATE of `login` (3, 7, 30, 100, 300 days).
- **Mulch spending (ids 19-23):** SUM `value` of `spend_mulch_single_item` activity WHERE value >= threshold (500, 1000, 1500, 3000, 5000).
- **Dosh spending (ids 24-28):** SUM `value` of `spend_dosh_single_item` activity WHERE value >= threshold (1, 3, 5, 15, 20).
- **Decorator purchase (ids 29-33):** COUNT of `buy_item` activity (3, 20, 100, 500, 1000).
- **Hip Hatter (ids 34-38):** COUNT DISTINCT of `buy_hat` targets (3, 10, 25, 50, 100 distinct hats).
- **Green Thumb seed purchases (ids 40-44):** COUNT of `buy_seed` activity (20, 100, 500, 1000, 5000).
- **Great Garden purchases (ids 45-49):** COUNT of `buy_garden_item` activity (1, 3, 5, 10, 20).
- **Ice Cream Monster (ids 50-54):** id 50 = COUNT (any), ids 51-54 = COUNT DISTINCT DATE (5, 10, 25, 50 days).
- **Brain Strain mulch (ids 55-59):** SUM `value` of `brain_strain_earn` activity (>= 50, 500, 1500, 5000, 10000 mulch).
- **Best Nest trophies (ids 64-68):** COUNT DISTINCT of `earn_trophy` targets (1, 3, 5, 10, 15).
- **SWS Agents missions (ids 100-104, 105-109, 115-119, 120-124, 125-129):** Per-mission per-grade completion. COUNT of `mission_complete` with metadata.grade matching or exceeding the threshold grade.
- **Case Files (ids 130-136):** Per-case-file star rating. COUNT of `case_file_complete` with metadata.stars >= threshold (1, 2, 3).
- **Weevily Welcome (id 39):** EXISTS on `login`.
- **Join the SWS (id 137):** EXISTS on `join_sws`.
- **Festive Fun (id 138):** EXISTS on `enter_winter_wonderland`.
- **Weevil Holiday (id 139):** EXISTS on `fly_weevil_air`.

### Client SWF badge assets (PROVEN — present in repo)

All badge SWFs exist in `game-full/cdn.binw.net/binBadges/`:
- `BB1.swf` through `BB15.swf`, `BB17.swf`, `BB22.swf`, `BB37.swf`, `BB38.swf`, `BB39.swf`
- `BB_weevilAir.swf`, `BB_gardenTrophy.swf`
- `BB_SWS_RLBP.swf`, `BB_SWS_HWX.swf`, `BB_SWS_DDP.swf`, `BB_SWS_TTT.swf`, `BB_SWS_LLD.swf`
- Each has a `_gold.swf` variant (the "completed" / gold badge icon)
- `AchievementAlertsManager4.swf` — the client-side alert manager (111 KB)
- `binBadgesDisplay2.swf`, `binBadgesDisplay3.swf`, `binBadgesDisplay4.swf` — display renderers

These are served at `http://localhost/cdn.binw.net/binBadges/<name>.swf` and are already
synced in htdocs (SHA-256 verified for AchievementAlertsManager4.swf; all BB_ badges
present in htdocs).

---

## EXISTING DATABASE TABLES

(See "CURRENT ACHIEVEMENT IMPLEMENTATION" above for schema + live row counts.)

The live `bwps` database has all 6 achievement tables present but 5 of them are
empty. Only `achievementscompleted` has 1 row (the Bin Pet adoption for user idx=3).

**Do NOT drop or alter these tables in the proposed migration.** The V1 plan
reuses `achievementscompleted` as the completion record (adding only a UNIQUE
constraint for idempotency) and introduces `achievement_activity` and
`achievement_rewards_v1` as new tables.

---

## RECOVERED ACHIEVEMENT CATALOGUE

(103 achievements summarized above. Full per-achievement field breakdown:)

Field provenance for each catalogue field:

| Field               | Source              | Provenance |
|---------------------|---------------------|------------|
| achievementId (JSON sub-key) | getAllAchievements.php | PROVEN |
| name               | getAllAchievements.php  | PROVEN |
| descriptionForMe   | getAllAchievements.php  | PROVEN |
| descriptionForVisitors | getAllAchievements.php | PROVEN |
| order              | getAllAchievements.php  | PROVEN |
| typeId / typeName  | getAllAchievements.php (parent key) | PROVEN |
| imageName          | getAllAchievements.php  | PROVEN |
| TAGS               | getAllAchievements.php  | PROVEN |
| typeColour         | getAllAchievements.php  | PROVEN |
| typeOrder          | getAllAchievements.php  | PROVEN |
| counterValue       | bwps.sql schema         | PROVEN schema / UNKNOWN fill |
| module             | bwps.sql schema         | PROVEN schema / UNKNOWN fill |
| rewardType / rewardValue | NOT present in JSON, NOT in SWFs | UNKNOWN |
| requirement threshold | INFERRED from achievement name text | INFERRED |

---

## ACTIVITY LEDGER SCHEMA

Proposed table: `achievement_activity` (see `migrations/proposed-achievements-activity-ledger.sql`
for full DDL). Summary:

```sql
CREATE TABLE IF NOT EXISTS `achievement_activity` (
  `id`           bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `userID`       int(11)      NOT NULL,  -- users.id, matches achievementscompleted.idx
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
```

**Index rationale:**

| Index                        | Serves query patterns                                             |
|------------------------------|-------------------------------------------------------------------|
| `idx_user_activity_time`     | COUNT by user+type, COUNT DISTINCT DATE by user+type, first/last lookup, new-achievement polling loop. PRIMARY operational index. |
| `idx_activity_type`          | Analytics/leaderboard: "who played Brain Strain this week?" — avoids full PK scan for type-scoped aggregation. |
| `idx_user_time`              | Login-day streak counting and active-on-date checks without scanning a specific type column. |

---

## ACTIVITY TYPES

(All INFERRED from achievement names + endpoint contracts. To be confirmed
against the next SWF decompilation if available.)

| Activity Type            | Trigger Action (authoritative endpoint/handler)        | targetID | value    | metadata              |
|--------------------------|--------------------------------------------------------|----------|----------|-----------------------|
|| `login`                  | successful `verifyUser` in `login/login.php` — ONE row per genuine successful password-verified login | NULL | 1 | NULL |
| `change_look`            | successful `changeDefinition` / appearance-save endpoint | NULL | 1 | NULL |
| `eat_ice_cream`          | successful ice-cream purchase/consume endpoint | ice-cream item ID | 1 | NULL |
| `buy_item`               | successful `buyItem.php` (department store) | nest item ID | 1 | NULL |
| `buy_hat`                | successful `buyHat.php` | hat item ID | 1 | NULL |
| `buy_seed`               | successful seed purchase endpoint | seed ID | 1 | NULL |
| `buy_garden_item`        | successful garden item purchase | garden item ID | 1 | NULL |
| `spend_mulch_single_item`| successful purchase where mulch is spent on ONE item | item ID | mulch spent | NULL |
| `spend_dosh_single_item` | successful purchase where dosh is spent on ONE item | item ID | dosh spent | NULL |
| `brain_strain_earn`      | successful `brain-submit.php` with score > previous best | game ID (4) | mulch earned | {"mulch":N, "xp":N} |
| `earn_trophy`            | successful trophy reward (rewardUserTrophy) | trophy level (80) | 1 | NULL |
| `task_complete`          | successful `task-completed.php` CompleteTask | task ID | 1 | {"questID":N} |
| `mission_complete`       | SWS mission completion (server JS handler) | mission ID | 1 | {"grade":"A"} |
| `case_file_complete`     | SWS case file completion with star rating | case file ID | 1 | {"stars":N} |
| `join_sws`               | SWS enrollment endpoint | NULL | 1 | NULL |
| `enter_location`         | location teleport (Winter Wonderland / Weevil Air) | location ID | 1 | NULL |

**Activity-type coverage:** All 103 achievements map to one or more of these activity types.
- Type A (COUNT single action): `login`, `change_look`, `buy_item`, `task_complete`, etc.
- Type B (COUNT DISTINCT DATE): `login` (Super Fan days), `change_look` (Style Icon days), `eat_ice_cream` (Ice Cream Monster days).
- Type C (SUM value): `spend_mulch_single_item`, `spend_dosh_single_item`, `brain_strain_earn`.
- Type D (COUNT DISTINCT target): `buy_hat` (Hip Hatter), `earn_trophy` (Best Nest).
- Type E (EXISTS): `join_sws`, `enter_location`, first `login` (Welcome).
- Type F (mission/grade with metadata): `mission_complete`, `case_file_complete`.

---

## ENDPOINT / SERVER-HANDLER MAPPING

| Achievement(s)         | Authoritative Action Source         | Endpoint / Handler                  | Activity Type(s)               |
|------------------------|-------------------------------------|-------------------------------------|--------------------------------|
| id 39 (Welcome)        | Login path                          | Any authenticated PHP hit           | `login`                        |
| id 1 (Bin Tycoon)      | `users.tycoon` flag                 | User status check                   | (EXISTS on `users.tycoon=1`)   |
| id 2 (Bin Pet)         | Pet adoption                        | `php2/pets/adoptPet.php`            | `adopt_pet`                    |
| ids 3,4,6,7,8          | Appearance save                     | `changeDefinition` in internal.php  | `change_look`                  |
| ids 9-13 (months/years)| Login                               | `login/login.php` verifyUser success           | `login`                        |
| ids 14-18 (login days) | Login                               | `login/login.php` verifyUser success           | `login`                        |
| ids 19-23 (mulch)      | Nest item purchase                  | `php2/shop/departmentStore/buyItem.php` | `spend_mulch_single_item` |
| ids 24-28 (dosh)       | Nest item purchase                  | `php2/shop/departmentStore/buyItem.php` | `spend_dosh_single_item`  |
| ids 29-33 (buy items)  | Nest item purchase                  | `php2/shop/departmentStore/buyItem.php` | `buy_item`                  |
| ids 34-38 (hats)       | Hat purchase                        | `php2/shop/buyHat.php`              | `buy_hat`                      |
| ids 40-44 (seeds)      | Seed purchase                       | Garden/shop seed endpoint           | `buy_seed`                     |
| ids 45-49 (garden)     | Garden item purchase                | Garden shop endpoint                | `buy_garden_item`              |
| ids 50-54 (ice cream)  | Ice cream purchase                  | Shop/ice-cream endpoint             | `eat_ice_cream`                |
| ids 55-59 (brain strain)| Brain Strain game completion       | `game/brain-submit.php`             | `brain_strain_earn`            |
| ids 64-68 (trophies)   | Trophy reward                       | `rewardUserTrophy` in internal.php  | `earn_trophy`                  |
| ids 100-129 (SWS)      | SWS mission / case file completion  | `server/Weevil.js` or `Main.js`     | `mission_complete`/`case_file_complete` |
| ids 137 (SWS agent)    | SWS enrollment                      | SWS join endpoint                   | `join_sws`                     |
| id 138 (Winter Wonder) | Location visit                      | Location teleport                   | `enter_location`               |
| id 139 (Weevil Air)    | Location visit                      | Location teleport                   | `enter_location`               |

**Unknowns in event wiring:**
- The exact endpoint for ice cream purchase is UNKNOWN (a shop endpoint, but which one — PROVEN that
  an endpoint exists, UNKNOWN which specific URL).
- The exact endpoint for SWS mission completion is UNKNOWN (likely handled in
  server-side JS `Weevil.js` — UNPROVEN which handler).
- SWS case file star rating: the client sends this, but the authoritative server
  handler is UNKNOWN (INFERED from JSON achievement structure).

---

## TASK / MISSION / TUTORIAL INTEGRATION

### Quest/task system (PROVEN — taskscompletedbyusers table + task-completed.php)

DB tables (live, confirmed):
- `task-completed`
- `task-completed2`
- `taskscompletedbyusers` (id, weevilName, tasks, questID, isComplete)
- `quests`
- `questscompleted`
- `questtasks`

`game-full/quests/task-completed.php` flow (PROVEN):
1. Receives `$_POST['taskID']`, `$_POST['questID']`
2. Calls `GetTaskDetails($taskID)` → returns `canReward`, `itemNameRewarded`, `gardenItemNameRewarded`, `mulchRewarded`, `xpRewarded`, `doshRewarded`
3. Calls `HasUserCompletedTask($taskID, $username, $idx)` → dedupe guard (prevents duplicate completion)
4. Calls `CompleteTask($taskID, $username, $idx, $questID)` → persists completion
5. Rewards: `rewardItem`, `rewardGardenItem`, `addMulchByName`, `addExperienceByName`, `addDoshByName`, optionally `rewardSpecialMoves`
6. Response includes `completedAchievements=` (empty — vestigial field, PROVEN)

**Integration model for Phase 7 (NOT to be implemented yet):**
- When `CompleteTask` succeeds (returning true, after the `HasUserCompletedTask` false check),
  the evaluator should record a `task_complete` activity row with `targetID = taskID`
  and `metadata = {"questID": questID}`.
- The `HasUserCompletedTask` dedupe check already prevents double-completion at the task level.
  The activity ledger will ADD a further idempotency layer (see section 5).
- Quest completion (`questscompleted`) is a separate higher-level construct and
  is NOT directly tied to individual achievements (no quest-based achievement
  was found in the catalogue).

### SWS missions / case files

- The SWS mission achievements (ids 100-129) are grade-based. The client
  displays per-grade sub-achievements (D/C/B/A). The activity type `mission_complete`
  with `metadata.grade` captures the grade. One activity row per (mission, grade)
  played. This is INFERRED from the JSON structure (each grade = separate achievementId).
- Case files (ids 130-136) use a star-rating (1/2/3 stars). Activity type
  `case_file_complete` with `metadata.stars`. INFERRED from JSON names.
- The authoritative server handler for SWS missions is likely in `server/Weevil.js`
  (SWS mission logic) — but no achievement-related code was found there
  (UNPROVEN). The exact handler needs recovery before Phase 6/7.

---

## COMPLETION MODEL

### Existing completion table: `achievementscompleted`

| Column         | Type            | Notes                                              |
|----------------|-----------------|----------------------------------------------------|
| id             | int(11) PK AI   | Auto-increment                                      |
| idx            | int(11)         | Maps to `users.id` (PROVEN from endpoint code)      |
| achievementId  | mediumtext      | Maps to `achievements.id` (default '1', UNPROVEN)  |
| completedDate  | date            | DATE of completion (DEFAULT current_timestamp())   |
| is_it_new      | int(11)         | 1 = not yet shown to client; 0 = shown (PROVEN)    |

**Current behavior (PROVEN):**
- No UNIQUE constraint on (idx, achievementId) — duplicates are possible at the DB level,
  but the application logic in `getNewAchievements.php` dedupes in-memory via a `$seen` array.
- `completedDate` is a DATE (not DATETIME) — means sub-day precision is lost in completion
  timestamps. The activity ledger's `occurredAt` (datetime(3)) retains full precision.

### Proposed completion model (Phase 4)

- Continue using `achievementscompleted` as the permanent completion record.
- ADD UNIQUE KEY `uk_achievementscompleted_user_ach (idx, achievementId)` in the proposed
  migration — this enforces idempotency at the DB level so a replayed completion insert
  is rejected rather than creating a duplicate row.
- The completion INSERT is wrapped in a transaction with the reward grant:
  1. `SELECT ... FOR UPDATE` on achievementscompleted for this (idx, achievementId)
  2. If not present: `INSERT` completion row (is_it_new=1)
  3. Load reward rows from `achievement_rewards_v1`
  4. Grant via `addMulchByName` / `addDoshByName` / `addExperienceByName`
  5. COMMIT
- On the INSERT failing due to the UNIQUE constraint, the evaluator treats it as "already
  completed" and returns no reward (idempotent).

---

## GETNEWACHIEVEMENTS SEMANTICS

(RECOVERED from `getNewAchievements.php` — PROVEN, not guessed)

- "New" = rows in `achievementscompleted` where `idx = <user id>` AND `is_it_new = 1`.
- The endpoint acquires a write lock (`SELECT ... FOR UPDATE`) on those rows.
- It collects the `achievementId` values into a deduplicated list (`$seen` array).
- It then `UPDATE achievementscompleted SET is_it_new = 0 WHERE id IN (...)` for all
  returned rows.
- The whole operation is in a transaction (`begin_transaction` / `commit`), with a
  `rollback` on any error.
- Response: `responseCode=1&newAchievements=<csv of achievementIds>` (empty list if none new).

**Semantics classification (PROVEN):** "New" = completed but not yet acknowledged by the client.
NOT "completed since last login" or "completed since last poll" (though in practice it
behaves similarly because the client polls and marks them seen). The `is_it_new` flag is
explicitly set to 0 by the server upon retrieval — this is an explicit unread/unseen state
managed server-side, not a client-driven ack.

**Implication for V1 design:** The activity ledger does NOT replace this mechanism.
`achievementscompleted.is_it_new` continues to serve the `getNewAchievements` read path.
The activity ledger is the source of truth for *progress*; `achievementscompleted` is the
source of truth for *completion + unread state*.

---

## MULCH / DOSH / XP REWARD MODEL

### V1 reward types (restricted per task brief)

Only THREE reward types are in scope for V1:

| rewardType | Grant helper          | DB column updated            |
|------------|-----------------------|------------------------------|
| `mulch`    | `addMulchByName($n,$t)` | `users.mulch += t`           |
| `dosh`     | `addDoshByName($n,$t)`   | `users.dosh += t`            |
| `xp`       | `addExperienceByName($n,$t)` | `users.xp += t`, `users.xp1 += t` |

Excluded from V1 (explicitly per task brief): hats, nest items, seeds, badges,
cosmetics, other inventory rewards, trophies. These remain part of the original
achievement data but are NOT granted by the V1 achievement evaluator.

### Reward configuration table: `achievement_rewards_v1`

```sql
CREATE TABLE IF NOT EXISTS `achievement_rewards_v1` (
  `id`            int(11)     NOT NULL AUTO_INCREMENT,
  `achievementId` int(11)     NOT NULL,
  `rewardType`    enum('mulch','dosh','xp') NOT NULL,
  `rewardValue`   int(11)     NOT NULL DEFAULT 1,
  `source`        enum('original','revival') NOT NULL DEFAULT 'revival',
  PRIMARY KEY (`id`),
  KEY `idx_ach_reward` (`achievementId`, `rewardType`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

- The `source` column lets the evaluator surface provenance: `'original'` means the
  reward value was recovered from original game data; `'revival'` means it is a
  custom V1 proposal.
- An achievement with a bundle reward (e.g. 500 mulch + 100 XP) has multiple rows.
- No reward rows are inserted in the proposed migration — the schedule is documented
  below and must be reviewed/approved before the live grant flow is wired up (Phase 4).

---

## PROVEN ORIGINAL REWARDS

**NONE RECOVERED.**

The static JSON in `getAllAchievements.php` does NOT contain reward amounts or reward types.
The `bwps.sql` schema for `achievementcounters` has a `counter` column but no reward
columns. No achievement reward data was found in:
- `getAllAchievements.php` (JSON — no reward fields)
- The `bwps.sql` schema (no reward table beyond what's described above)
- The binBadges SWFs (badge icon assets only — no reward metadata strings found)
- `internal.php` (reward helpers exist but no achievement-specific reward mapping)
- `brain-submit.php`, `task-completed.php` (task/game rewards, not achievement rewards)

The `achievementcounters` table (empty in live DB) has columns `counterId`, `counter`,
`lastUpdated` — these track the per-achievement counter progress, but there is NO
reward configuration stored anywhere.

**Status: All 103 achievements have UNKNOWN reward values.** The full schedule is
REVIVAL-CUSTOM PROPOSAL (see next section).

---

## PROPOSED REVIVAL REWARDS

(All values are REVIVAL-CUSTOM PROPOSAL — NOT PROVEN originals. Must be reviewed
before Phase 4.)

### Reward schedule by category

| Category                  | Achievements (IDs)     | Proposed Reward  |
|---------------------------|------------------------|------------------|
| Welcome / single          | 39, 137                | 500 mulch        |
| Bin Tycoon                | 1                      | 1000 dosh        |
| Bin Pet adoption          | 2                      | 750 mulch        |
| Style Icon (count)        | 3                      | 200 mulch        |
| Style Icon (days 5/10/20/50) | 4,6,7,8            | 200/500/1000/2000 mulch respectively |
| Bin Around (months/years) | 9-13                   | 500/1000/2000/4000/8000 mulch respectively |
| Super Fan (login days)    | 14-18                  | 100/300/1000/3000/10000 mulch respectively |
| Mulch Maniac (spending)   | 19-23                  | 100/250/500/1000/2500 mulch respectively |
| Dosh Galore (spending)    | 24-28                  | 10/25/50/150/500 dosh respectively |
| Decorator (buy count)     | 29-33                  | 100/250/500/1000/2500 mulch respectively |
| Hip Hatter (hats)         | 34-38                  | 100/250/500/1000/2500 mulch respectively |
| Green Thumb (seeds)       | 40-44                  | 100/250/500/1000/2500 mulch respectively |
| Great Garden (garden)     | 45-49                  | 100/250/500/1000/2500 mulch respectively |
| Ice Cream Monster (1 + days) | 50-54               | 50/100/250/500/1000 mulch + 5/10/25/50/100 xp respectively |
| Brain Strain (mulch earn) | 55-59                  | 100/250/500/1000/2500 mulch + 50/100/250/500/1000 xp respectively |
| Best Nest (trophies)      | 64-68                  | 200/500/1000/2000/5000 mulch + 50/100/250/500/1000 xp respectively |
| SWS Missions (per grade)  | 100-129                | 500 mulch + 250 xp per achievement |
| Case Files (per star)     | 130-136                | 500 mulch + 250 xp per achievement |
| Location visits           | 138, 139               | 250 mulch each |
| Tycoon-only check         | 1                      | (covered above — 1000 dosh, tycoonOnly=1 enforcement) |

**Bundle reward example** (Brain Strain id 55):
```
INSERT INTO achievement_rewards_v1 (achievementId, rewardType, rewardValue, source)
VALUES (55, 'mulch', 100, 'revival'),
       (55, 'xp', 50, 'revival');
```

**No rewards are inserted into the live DB during this phase.** The schedule lives
only in this documentation and must be approved before Phase 4.

---

## ACTIVITY LEDGER SEMANTICS

### No same-day deduplication

The achievement_activity table is an EVENT LOG. Every legitimate successful action
produces an activity row, even if the same activity type already has rows from the
same day.

Example:

User logs in twice today:
  login | 2026-09-03 10:00
  login | 2026-09-03 18:00

BOTH ROWS EXIST.

If an achievement asks "log in on X different days" the QUERY performs the
distinct-day calculation:

  SELECT COUNT(DISTINCT DATE(occurredAt))
  FROM achievement_activity
  WHERE activityType = ? AND userID = ?;

Or when only the count is required:

  SELECT COUNT(*)
  FROM achievement_activity
  WHERE activityType = ? AND userID = ?;

This distinction is CRITICAL. The same activity type supports multiple achievements
with different query rules. The ledger records the actions; the evaluator chooses:

  COUNT(*)
  COUNT(DISTINCT DATE(...))
  COUNT(DISTINCT targetID)
  SUM(value)
  EXISTS

No UNIQUE constraint on (userID, activityType, DATE) is enforced.

### Retry/replay protection

Retry/replay protection is handled by the AUTHORITATIVE ENDPOINT's own mechanisms:

- `checkHash` anti-replay via `$_SESSION['theHasher']`
- `HasUserCompletedTask` duplicate guard
- `brain-submit.php` daily limit (`last_played` date check)
- Atomic DB transactions for purchases (currency deducted + item granted)

The activity row is recorded AFTER the underlying action genuinely succeeds. A
replayed request that the endpoint rejects does NOT produce an activity row.

### Duplicate-hook guard for second-resolution events

For activity types where the Flash client may retry the exact same request within
the same second (e.g. `buy_item`, `buy_hat`, `spend_mulch_single_item`), the
record helper performs a same-second check before INSERT:

```sql
SELECT id FROM achievement_activity
 WHERE userID = ? AND activityType = ? AND targetID = ?
   AND occurredAt >= DATE_SUB(NOW(3), INTERVAL 1 SECOND)
 LIMIT 1
```

If a row already exists within the same second, no duplicate is inserted. This
protects against Flash retry storms while preserving distinct-day semantics for
day-count evaluations.

### Natural dedupe coverage

| Event                  | Why naturally safe / retry guard                            |
|------------------------|-------------------------------------------------------------|
| `login`                | `verifyUser` is called once per password attempt; activity row is emitted only after successful password_verify + session key generation. |
| `change_look`          | `checkHash` rejects replay; `affected_rows == 1` confirms single DB update. |
| `task_complete`        | `HasUserCompletedTask` prevents duplicate task completion.  |
| `brain_strain_earn`    | Daily `last_played` guard (result=3 if already played).     |
| `eat_ice_cream`        | Ice cream purchase is a single atomic transaction; retry would fail if item already consumed. |
| `earn_trophy`          | Trophy is a discrete item; awarded once per level.          |
| `buy_item`/`buy_hat`/`buy_seed`/`buy_garden_item` | Same-second guard before activity INSERT.       |
| `spend_mulch_single_item`/`spend_dosh_single_item` | Same-second guard + authoritative endpoint atomicity. |

---

## INDEXES / QUERY MODEL

### Real recovered query patterns (all 7 types)

| Pattern ID | Query Type          | Example Achievement     | SQL Pattern                                                        | Covered By Index          |
|------------|---------------------|-------------------------|--------------------------------------------------------------------|---------------------------|
| Q1         | COUNT total         | 3 (change_look ×1)      | `COUNT(*) WHERE userID=? AND activityType=?`                        | idx_user_activity_time    |
| Q2         | COUNT DISTINCT DATE | 4 (change_look 5 days)  | `COUNT(DISTINCT DATE(occurredAt)) WHERE userID=? AND activityType=?` | idx_user_activity_time    |
| Q3         | COUNT DISTINCT target | 34 (hats, 3 distinct) | `COUNT(DISTINCT targetID) WHERE userID=? AND activityType=?`        | idx_user_activity_time    |
| Q4         | SUM value           | 19 (spend 500 mulch)    | `SUM(value) WHERE userID=? AND activityType=?`                      | idx_user_activity_time    |
| Q5         | First occurrence    | 39 (first login)        | `MIN(occurredAt) WHERE userID=? AND activityType=?`                 | idx_user_activity_time    |
| Q6         | Last occurrence     | 54 (last ice cream)     | `MAX(occurredAt) WHERE userID=? AND activityType=?`                 | idx_user_activity_time    |
| Q7         | SUM with target filter | 55 (brain strain mulch) | `SUM(value) WHERE userID=? AND activityType=? AND targetID=?`     | idx_user_activity_time    |

All 7 patterns are served by the single composite index `idx_user_activity_time`.
The other two indexes (`idx_activity_type`, `idx_user_time`) serve analytics and
login-streak checks respectively.

---

## UNKNOWNS

| Item                                          | Classification |
|-----------------------------------------------|----------------|
| Exact URL for ice cream purchase endpoint     | UNKNOWN (PROVEN: an endpoint exists; UNKNOWN: which URL) |
| Exact server handler for SWS mission completion | UNKNOWN (INFERED: likely in server/Weevil.js) |
| SWS case file star-rating mechanism (client→server) | UNKNOWN (INFERED from JSON achievement structure) |
| All achievement reward values/types           | UNKNOWN (none recovered from any source) |
| `achievements.module` fill value for revived rows | UNKNOWN (column is PROVEN but value is not) |
| `achievements.counterValue` fill value        | UNKNOWN (column is PROVEN but value is not) |
| Whether `achievementcounters.counterId` maps 1:1 to `achievements.id` | INFERRED |
| Whether the original game populated `achievementcounters` at all | UNKNOWN (table is empty in live DB) |

---

## IMPLEMENTATION ROADMAP

**PHASE 1 — Apply reviewed schema**
- Files: `migrations/proposed-achievements-activity-ledger.sql`
- DB objects: `achievement_activity`, `achievement_rewards_v1`, UNIQUE constraint on `achievementscompleted(idx, achievementId)`
- Dependencies: none
- Risks: The UNIQUE constraint on `achievementscompleted` could fail if pre-existing duplicates exist (current live DB has 0 duplicates — safe)
- PASS criteria: migration applies cleanly with no errors; `achievement_activity` table exists and is empty; `achievement_rewards_v1` table exists and is empty; `achievementscompleted` has the new UNIQUE constraint

**PHASE 2 — Add central `recordAchievementActivity()` helper**
- Files: `game-full/essential/internal.php` (new function)
- DB objects: none
- Dependencies: Phase 1 (table must exist)
- The helper takes `(userID, activityType, targetID, value, metadata)` and inserts
  into `achievement_activity`, applying same-day dedupe for day-count events.
- Risks: dedupe logic must match the activity-type rules in section 5
- PASS criteria: helper exists, accepts all activity types, applies dedupe correctly, returns insert ID

**PHASE 3 — Add achievement evaluator**
- Files: new `game-full/essential/achievements-evaluator.php` (or a class)
- DB objects: none (reads from `achievement_activity` + `achievements` + `achievement_rewards_v1`)
- Dependencies: Phase 2 (activity data must be flowing)
- The evaluator parses the achievement criteria from `getAllAchievements.php` JSON
  (or a future `achievement_criteria` table — Phase 2.5) and runs the 7 query patterns
- Risks: criteria parsing from achievement names is fragile; a `achievement_criteria` table
  is the long-term fix but requires a seed step
- PASS criteria: all 103 achievements can be evaluated; correct query type matches each
  achievement's requirement; no false positives/negatives

**PHASE 4 — Completion + one-time Mulch/Dosh/Xp reward grant**
- Files: extend `getCompletedAchievements.php` or add a new `evaluateAchievements.php` that runs after key actions
- DB objects: relies on Phase 1 UNIQUE constraint for idempotency
- Dependencies: Phase 3 (evaluator), Phase 1 (completion table unique key)
- Workflow: evaluate → SELECT FOR UPDATE achievementscompleted → INSERT if new → load rewards → grant via addMulchByName/addDoshByName/addExperienceByName → set is_it_new=1 → COMMIT
- Risks: reward grants must be atomic with completion insert; transaction rollback must un-grant rewards (which the current addMulchByName does NOT support — needs a transactional DB approach or careful ordering)
- PASS criteria: completing an action that satisfies an achievement inserts exactly one completion row; reward is granted exactly once; repeated calls are idempotent (no duplicate grant, no duplicate completion row)

**PHASE 5 — Update getCompletedAchievements / getNewAchievements**
- Files: `getCompletedAchievements.php`, `getNewAchievements.php`
- DB objects: none (these already work against `achievementscompleted`)
- Dependencies: Phase 4 (completion rows must exist)
- The existing endpoints already query `achievementscompleted` correctly — no changes needed
  unless the evaluator writes completion rows with a different `achievementId` format.
- PASS criteria: client-facing endpoints return correct completed/new lists; unchanged
  behavior for pre-existing completion rows (e.g. idx=3, achievementId=2)

**PHASE 6 — Wire the simple authoritative endpoints**
- Files: each PHP endpoint that should record activity
- Endpoints to wire: login path, changeDefinition, ice cream purchase, buyHat, buyItem, buySeed, buyGardenItem, brain-submit
- For each: add a `recordAchievementActivity()` call after the existing reward logic
- Dependencies: Phase 2 (helper), Phase 4 (evaluator + grant)
- Risks: must NOT duplicate reward grants that the endpoint already does independently
  (e.g. task-completed.php already calls addMulchByName for the task reward)
- PASS criteria: each wired endpoint records exactly one activity row per successful action;
  no double-counting from Flash retries

**PHASE 7 — Wire quest/task/mission/tutorial progression**
- Files: `quests/task-completed.php`, server JS SWS handlers
- Activity types: `task_complete`, `mission_complete`, `case_file_complete`, `join_sws`
- Dependencies: Phase 6
- For task-complete: add `recordAchievementActivity()` after `CompleteTask()` returns true
  (the HasUserCompletedTask guard already prevents duplicates)
- For SWS missions: identify the authoritative handler (UNKNOWN — needs recovery) before wiring
- PASS criteria: task completion is recorded; mission completion is recorded; no duplicate
  activity rows for replayed endpoints

**PHASE 8 — Automated DB/endpoint testing**
- Files: test harness scripts
- Dependencies: all phases complete
- Tests: insert activity rows → run evaluator → verify completion + reward
- PASS criteria: all 103 achievements evaluate correctly against synthetic activity data;
  idempotency verified via repeated calls

**PHASE 9 — Manual Flash gameplay test**
- Files: none
- Dependencies: Phase 8 green
- Test: play through Electron client, verify achievement unlocks in real client
- PASS criteria: achievement badges appear in client; rewards are granted; getNewAchievements
  returns the right IDs; no duplicate grants on reconnect

---

## FILES IN THIS PROPOSAL (PROPOSED MIGRATION + DOCS ONLY)

- `migrations/proposed-achievements-activity-ledger.sql` — proposed schema (NOT applied)
- `docs/ACHIEVEMENTS-ACTIVITY-SCHEMA-2026-09-03.md` — this document
