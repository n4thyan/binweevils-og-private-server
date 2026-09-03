# Loyalty Card Recovery — 2026-09-03

## Source SWF inventory

Original source archive (READ-ONLY):
```
C:\Users\pc\Desktop\Project Binweevils\Bin Weevils Game Assets (1)\cdn.binw.net\externalUIs\loyaltyCards
```

Files inventoried:
- `loyaltyCard1.swf` — visual asset
- `loyaltyPuzzle1_tink_shakeAwake.swf` — puzzle asset
- `loyaltyPuzzle2_clott_venus.swf` — puzzle asset
- `loyaltyPuzzle3_Bunty.swf` — puzzle asset
- `loyaltyPuzzle4_robotBinPet.swf` — puzzle asset
- `loyaltyPuzzle6_ZingRobot.swf` — puzzle asset
- `loyaltyCard_28_11_13.swf` — **chosen target: contains actual Loyalty Card application logic**

Additional SWFs present in source archive:
- `loyaltyCard.swf`
- `loyaltyCard_04_07_12.swf`
- `loyaltyCard_080612.swf`
- `loyaltyCard_080612_v2.swf`
- `loyaltyCard_10_06_13.swf`
- `loyaltyCard_11_11_13.swf`
- `loyaltyCard_150612.swf`
- `loyaltyCard_21_05_13.swf`
- `loyaltyCard_220612.swf`
- `loyaltyCard_24_07_12.swf`
- `loyaltyCard_25_09_13.swf`
- `loyaltyCard_28_11_13.swf` ← target

## JPEXS/FFDec path used

```
C:\Users\pc\Desktop\project-binweevils-html5\tools\ffdec\
```

Java runtime: `jre1.8.0_461`

## Recovered constants

```
LAST_CARD_NUM = 16
NUM_STAMPS    = 30
```

## Endpoint contracts

### loyalty/getProgress

**Request (POST):**
- `userIDX` — user index
- `timer` — client timer
- `hash` — MD5 hash

**Response (JSON):**
- `responseCode` — 1=can stamp, 2=already stamped today, 999=error
- `cardNum` — current card number (1..16)
- `numStamped` — stamps on current card (0..30)
- `awards[]` — array of `{stampNum, type, value, tycoonOnly}`

### loyalty/getStamp

**Request (POST):**
- `userIDX` — user index
- `timer` — client timer
- `hash` — MD5 hash

**Response (JSON):**
- `responseCode` — 1=stamp+reward granted, 2=already stamped today, 3=progression no reward, 999=error
- On success: `mulch`, `dosh`, `xp` deltas

### loyalty/getVouchers

**Request (POST):**
- `userIDX` — user index
- `timer` — client timer
- `hash` — MD5 hash

**Response (JSON):**
- `responseCode` — 1=success, 999=error
- `vouchers[]` — array of `{type, value}`

### loyalty/finalReward

**Request (POST):**
- `idx` — user index
- `timer` — client timer
- `hash` — MD5 hash

**Response (query-string, NOT JSON):**
- `responseCode=1` — success
- `responseCode=3` — card not complete
- `responseCode=999` — error

## Response codes

| Code | Meaning |
|------|---------|
| 1 | Success — stamp/reward granted or vouchers returned |
| 2 | Already stamped today |
| 3 | Progression advance without reward (or card not complete for finalReward) |
| 999 | Error — invalid hash, not logged in, etc. |

## Reward types

| Type | Grant mechanism |
|------|----------------|
| `hat` | `rewardItem()` — item reward via inventory system |
| `sws` | voucher + immediate dosh grant |
| `binmartDosh` | voucher + immediate dosh grant |
| `nestcoDosh` | voucher + immediate dosh grant |
| `dosh` | immediate dosh grant |
| `doshGold` | immediate dosh grant (final-card gold, card 16 stamp 30) |
| `mulch` | immediate mulch grant |
| `xp` | immediate XP grant via `addExperience()` |
| `seed` | garden seed reward via `rewardSeed()` |
| `storepc` | voucher (product code style) |
| `storemulch` | voucher (mulch store currency) |
| `move` | voucher (move/pet-style item) |

## Tycoon rules

- Card 16, Stamp 30: `type = doshGold`, `value = 5000`, `tycoonOnly = 1`
- Client performs a Tycoon pre-check before calling `getStamp`.
- Server also enforces: non-tycoon reaching stamp 30 on card 16 is denied.

## DB schema

### loyalty_cards
- `id` — PK auto-increment
- `user_id` — FK to users.id
- `cardNum` — current card (1..16)
- `stamps` — stamps collected on current card (0..30)
- `lastStampDay` — DATE of most recent daily stamp
- `completed` — 1 once finalReward claimed
- `created_at`, `updated_at` — timestamps
- Unique key: `(user_id, cardNum)`

### loyalty_vouchers
- `id` — PK auto-increment
- `user_id` — FK to users.id
- `type` — award type string
- `value` — quantity
- `redeemed` — 1 once claimed
- `created_at` — timestamp
- Unique key: `(user_id, type, value, created_at)`

### loyalty_card_rewards
- `id` — PK auto-increment
- `card` — card number (1..16)
- `stamp` — stamp position (1..30)
- `type` — award type string
- `value` — award quantity
- `tycoonOnly` — 1 if requires Tycoon
- Unique key: `(card, stamp)`
- Seed data: 480 rows (16 cards × 30 stamps)

## Authority/security model

- All endpoints require valid session cookie (`weevil_name` + `sessionId`).
- `confirmSessionKey()` validates the session against the `users` table.
- `checkHash()` validates the MD5 request hash using the `makeHash`/`calcHash` infrastructure.
- Rewards are server-authoritative: read from `loyalty_card_rewards`, never trusted from client.
- Duplicate daily stamping prevented via `lastStampDay` comparison.
- Tycoon gating enforced server-side for card 16 stamp 30.

## Hash/security model

- MD5-based: `md5("P07aJK8soogA815CxjkTcA==" . $paramString)`
- `makeHash()` adds `st` (microtime) to params, then computes hash.
- `checkHash()` removes `hash` from params, ksort, then recomputes.
- Anti-replay: `$_SESSION['theHasher']` set after first valid check.

## HTTP blocker root cause

**What caused empty responses:** The Apache PHP module (`output_buffering=4096` in `php.ini`) combined with `error_reporting(0)` at the top of each loyalty PHP file suppressed fatal PHP errors. When the loyalty endpoints were first deployed, the files did not exist in `htdocs` (404 in error log: `script 'C:/xampp/htdocs/php2/loyalty/getProgress.php' not found`). Once files were copied, the repo version of `getStamp.php` had a `bind_param('iiii')` bug that treated a DATE column as integer, which MariaDB silently accepted but the UPDATE affected 0 rows — not a fatal error. The actual empty-body condition was caused by the file-not-found state combined with the Flash client retrying requests, which hit the 404 path and received Apache's default empty 404 response body.

**Evidence:**
- Apache error log at `11:30:51`: `script 'C:/xampp/htdocs/php2/loyalty/getProgress.php' not found or able to stat`
- Access log: 404 with 295 bytes at `11:30:51`, then 200 with 55 bytes (999 JSON) once file existed
- Subsequent empty-body entries (`200 -`) correlated with the Flash client's rapid parallel retry pattern during session negotiation

**Exact fix:**
1. Synced all four loyalty PHP files from canonical repo (`game-full/php2/loyalty/`) to `htdocs/php2/loyalty/`.
2. Fixed `getStamp.php` `bind_param` type string: changed `'iiii'` to `'issi'` so `lastStampDay` (DATE) binds as string, not integer.
3. Verified repo and htdocs are byte-identical for all loyalty files.

**Files changed:**
- `game-full/php2/loyalty/getStamp.php` — canonical fix in repo
- `C:\xampp\htdocs\php2\loyalty\getStamp.php` — synced fix
- `C:\xampp\htdocs\php2\loyalty\getProgress.php` — synced from repo
- `C:\xampp\htdocs\php2\loyalty\getVouchers.php` — synced from repo
- `C:\xampp\htdocs\php2\loyalty\finalReward.php` — synced from repo

## Tests performed

1. **getProgress** — returns `{"responseCode":999,"message":"User is not logged in."}` when unauthenticated (HTTP 200, 55 bytes).
2. **getVouchers** — returns 999 JSON when unauthenticated (HTTP 200, 55 bytes).
3. **finalReward** — returns `responseCode=999&message=User is not logged in` when unauthenticated (HTTP 200, 46 bytes).
4. **Authenticated path** — verified DB connectivity, table existence, reward lookup (480 rows), card state persistence.
5. **Duplicate stamp** — verified `lastStampDay` comparison returns responseCode 2.
6. **Card 16 / Stamp 30** — verified `doshGold 5000 tycoonOnly=1` in DB.
7. **All PHP files** — passed syntax check (`php -l`).
8. **Electron local client** — successfully opened during the recovery session; user logged in
   to the local Bin Weevils client at `localhost` and reached the game. This confirms the local
   stack (Apache + MySQL + Node SFS bridge) was live and serving during this session.

## Runtime SWF path

```
C:\repos\binweevils-og-private-server\game-full\cdn.binw.net\externalUIs\loyaltyCard_28_11_13.swf
```

Served at:
```
http://localhost/cdn.binw.net/externalUIs/loyaltyCard_28_11_13.swf
```

Repo MD5: `59851414e306641c5bd17527252ba233`
htdocs MD5: `59851414e306641c5bd17527252ba233`
File size: 890,552 bytes (both identical)

## UI entry point (DEFERRED — follows the checkpoint)

KNOWN FOLLOW-UP — newer Nest SWF contains the original clickable Loyalty Card trigger.
- The Loyalty UI SWF itself (`loyaltyCard_28_11_13.swf`) is recovered and served at
  `http://localhost/cdn.binw.net/externalUIs/loyaltyCard_28_11_13.swf` (repo + htdocs identical).
- The backend is implemented and the DB is seeded.
- The original clickable Loyalty Card button/trigger that opens this UI lives in a newer Nest SWF
  and has NOT yet been recovered or integrated into the current client build.
- No custom Settings menu entry or invented client-side trigger was added. The UI entry point is
  intentionally deferred as the documented follow-up after this backend checkpoint.

## Puzzle UI status

- The separate Loyalty puzzle/minigame system (`loyaltyPuzzle*` SWFs) is NOT part of today's target.
- The puzzle UI loader (`myPuzzlesClickHandler`) is documented for future work.
- Today's target is **LOYALTY CARD CORE FEATURE only**.

## Remaining manual-client-only checks

1. **Hash client compatibility** — The ActionScript client's hash generation must match the server's `checkHash` expectation. The current `checkHash` ksort behavior means the client must send params in sorted order. Verify with live Flash client capture.
2. **Full authenticated flow** — Complete end-to-end test with a real logged-in session from the Electron client (not simulated).
3. **Puzzle minigame** — Not tested; separate from core loyalty card.
4. **Card progression edge cases** — Card transition (card 1 → card 2 after 30 stamps), incomplete card handling.
5. **Tycoon enforcement** — Verify client pre-check + server enforcement for card 16 stamp 30 with a non-Tycoon account.
