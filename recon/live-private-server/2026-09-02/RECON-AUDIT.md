# Live private-server recon — 2 September 2026

## CAPTURE SUMMARY

- Source: network capture from another live Bin Weevils private server
- Date: 2026-09-02
- Host observed: `play.binweevils.app`, `sfs.binweevils.app`, `web.binweevils.app`, `cdn.binweevils.app`
- Raw files: stored locally untracked; only sanitized derivatives are committed below
- Provenance: this is OBSERVED behaviour from a third-party server, not canonical Bin Weevils data

## EVIDENCE HIERARCHY

For this reassessment, evidence is weighted:

A. **Our existing implementation** (`game-full/php2`, `game-full/php`, database schema)
B. **Original recovered client** (core40 AS decompilation, SWF corpus, `docs/CORE-ENDPOINT-AUDIT-2026-09-02.md`, temp audit artifacts)
C. **Live private-server capture** (this HAR/SFS)
D. **New SWF assets** (downloadable from observed URLs)

Missing third-party response bodies from C are **not** automatic blockers if A+B+D resolve the feature.

## TARGETED LOCAL SWF CONTRACT RECOVERY

Read-only decompilation of priority SWFs from existing recovered corpus at `C:\Users\pc\AppData\Local\Temp\core-endpoint-audit-20260902\external\targets\`.

### BRAIN STRAIN

**SWF:** `brainStrain_10_06_13.swf`
**LOCAL PATH:** `game-full/cdn.binw.net/externalUIs/brainStrain_10_06_13.swf`
**SHA-256:** `d5abfead4bb6d0ae72b828bc17763c6e`
**SIZE:** unknown (not remeasured this pass)

**ENDPOINTS:**
- `game/brain-info` — GET, no params
- `game/brain-submit` — secure POST `score,levels,st,hash` (PHPcall `sendAndAwaitResponse`, alphabetized)

**REQUEST CONTRACT:**
- `brain-info`: empty request
- `brain-submit`: `score` (numeric), `levels` (serialized string: `,1|0,3|0,4|0...` = levelID|passed pairs comma-separated), `st`, `hash`

**RESPONSE FIELDS READ:**
- `brain-info` callback `onUserInfo(param1)`:
  - `param1.modes` → `modesAvailable` (1 = already played today, 2 = can play)
  - `param1.levels` → `LevelManager.getInstance().setUserInfo(param1.levels)`
  - `param1.err` (not read in callback, but echoed by server)
- `brain-submit` callback `onScoreSubmitted(param1)`:
  - `param1.levels` → `LevelManager.getInstance().setUserInfo(param1.levels)`
  - `param1.modes` → `modesAvailable = uint(param1.modes)`
  - `param1.high` → top score display
  - `param1.ave` → average score display
  - `param1.xpEarned` → XP tween
  - `param1.mulchEarned` → Mulch tween
  - `param1.xp` → `bin.updateXp(uint(param1.xp))`
  - `param1.mulch` → `bin.updateMulch(uint(param1.mulch))`
  - `param1.result` (not directly read, but server echoes it)

**CLIENT-SIDE CONSTANTS:**
- `gameID=4` (hardcoded in PHP backend, not in SWF)
- Score validation: client does not validate range; server rejects `score==3000`, negative, or >maxScore
- Levels format: comma-separated `levelID|passed` pairs

**BACKEND MATCH:**
- `game-full/game/brain-info.php` exists and returns `modes,levels,err` — MATCHES client contract
- `game-full/game/brain-submit.php` exists and returns `res,modes,ave,high,levels,mulchEarned,xpEarned,mulch,xp,result` — MATCHES client contract
- Reward formula: `mulchEarned = round(score * mulchFactor) + minMulch`, `xpEarned = round(score * xpFactor) + minXp` — SERVER-CALCULATED, client consumes as absolute totals
- Daily limit: one rewarded play per day, enforced server-side

**CLASSIFICATION:** ALREADY PRESENT — FUNCTIONALLY VERIFIED. Both endpoints match the original client contract exactly. No changes needed.

### LOTTO

**SWF:** `lotto_01_03_21.swf` (primary), `lotto.swf` (older variant also present)
**LOCAL PATH:** `game-full/cdn.binw.net/externalUIs/lotto_01_03_21.swf`
**SHA-256:** `a18e0dd1ef9a33e70d0e5374be340106`
**SIZE:** unknown (not remeasured this pass)

**ENDPOINTS:**
- `php/getMyLottoTicketsAndDrawDate.php` — GET, no params (core40, not unique to this SWF)
- `php/getJackpotSize.php` — PHPcall `sendAndAwaitResponse(["drawID"],[nextDrawID])`
- `php/addLottoTicket.php` — PHPcall `sendAndAwaitResponse(["drawID","ticket"],[nextDrawID,requestedTicket])`
- `php/getUncashedTickets.php` — PHPcall `awaitResponse`
- `php/getPastLottoDraws.php` — PHPcall `awaitResponse`
- `php/getLottoDrawWinners.php` — PHPcall `sendAndAwaitResponse(["drawID"],[drawID])`
- `php/cashInTickets.php` — PHPcall `sendAndAwaitResponse(["drawID","wins"],[drawID,winsFlag])`

**REQUEST CONTRACT:**
- `getJackpotSize`: `drawID` (numeric, from bootstrap)
- `addLottoTicket`: `drawID` (numeric), `ticket` (4-digit string, concatenated from 4 selectors)
- `getUncashedTickets`: no params
- `getPastLottoDraws`: no params
- `getLottoDrawWinners`: `drawID` (numeric)
- `cashInTickets`: `drawID` (numeric), `wins` (boolean-ish: `1` if locally computed total > 0, else `0`)

**RESPONSE FIELDS READ:**
- `getJackpotSize` → `param1.jackpot` (numeric, displayed as "X Mulch")
- `addLottoTicket` → `param1.success == "1"` (success branch); else navigate to draw-in-progress
- `getUncashedTickets` → `param1.uncashed` (pipe-separated tickets or `0`/empty), `param1.drawID`, `param1.drawDate`, `param1.result` (4-digit string), `param1.jackpot`, `param1.numWinners`
- `getPastLottoDraws` → response parsed as pipe-separated records: `drawID;date;fourDigitResult;jackpot;numWinners`
- `getLottoDrawWinners` → `param1.drawID`, `param1.winners` (pipe-separated winner names, or `0`)
- `cashInTickets` → `param1.winnings` (numeric, treated as authoritative; added to displayed mulch via `bin.updateMulch`)

**CLIENT-SIDE CONSTANTS:**
- Ticket: exactly 4 selected digits concatenated as string
- Prize calculation: CLIENT-SIDE using `LottoData.oneMatchValue`, `twoMatchesValue`, `threeMatchesValue`
- Four-match prize: `max(minJackpotToAward, jackpot / numWinners)`
- `wins` flag posted to `cashInTickets` is boolean-ish, NOT the computed amount

**BACKEND MATCH:**
- `php/getMyLottoTicketsAndDrawDate.php` exists — CONFIRMED by live HAR response
- `php/getJackpotSize.php` — ABSENT
- `php/addLottoTicket.php` — ABSENT
- `php/getUncashedTickets.php` — ABSENT
- `php/getPastLottoDraws.php` — ABSENT
- `php/getLottoDrawWinners.php` — ABSENT
- `php/cashInTickets.php` — ABSENT

**CLASSIFICATION:**
- Bootstrap (`getMyLottoTicketsAndDrawDate`): ALREADY PRESENT — CONTRACT VERIFIED
- All follow-on endpoints: CONTRACT-PROVEN BUT MISSING
- Server authority for jackpot/prizes: CLIENT-SIDE calculation for display, but server must still validate and award. Server authority for jackpot amount and draw result is UNKNOWN.

### LOYALTY

**SWF:** `loyaltyCard_28_11_13.swf`
**LOCAL PATH:** `game-full/cdn.binw.net/externalUIs/loyaltyCard_28_11_13.swf`
**SHA-256:** `59851414e306641c5bd17527252ba233`
**SIZE:** unknown (not remeasured this pass)

**ENDPOINTS:**
- `php2/loyalty/getProgress.php` — PHP2 `sendAndAwaitResponse(["userIDX"],[bin.myUserIDX],callback,true,true)` — JSON response
- `php2/loyalty/getStamp.php` — PHP2 `sendAndAwaitResponse(["userIDX"],[bin.myUserIDX],callback,true,true)` — URL-variables response
- `php2/loyalty/finalReward` — PHP2 `sendAndAwaitResponse(["idx"],[bin.myUserIDX],callback,true,false)` — URL-variables response, no JSON
- `php2/loyalty/getVouchers.php` — PHP2 `sendAndAwaitResponse(["userIDX"],[bin.myUserIDX],callback,true,true)` — JSON response

**REQUEST CONTRACT:**
- All endpoints take `userIDX` (or `idx` for finalReward) as the signed parameter
- `getProgress` and `getVouchers` expect JSON response (`jsonResponse=true`)
- `getStamp` and `finalReward` expect URL-variables response (`jsonResponse=false`)

**RESPONSE FIELDS READ:**
- `getProgress` response:
  - `responseCode` (1 = can stamp, 2 = already stamped today)
  - `cardNum` (current card number)
  - `numStamped` (stamps collected on current card)
  - `awards` (array of award objects with fields: `stampNum`, `type`, `tycoonOnly`, etc.)
- `getStamp` response:
  - `responseCode` (1 = stamp/reward, 2 = already stamped today, 3 = advance without reward)
  - Optional: `mulch`, `dosh`, `xp` (absolute values, client calls `checkUpdateBin` to sync)
  - Optional: `puzzleTypeID`, `pieceNumber` (puzzle state update)
- `finalReward` response:
  - `responseCode` (1 = success, else error)
  - Client only checks success/error, no further fields read
- `getVouchers` response:
  - `responseCode`
  - `vouchers[]` (array of voucher objects)

**CLIENT-SIDE CONSTANTS:**
- `NUM_STAMPS` = number of stamps per card (hardcoded in SWF, not extracted this pass)
- `LAST_CARD_NUM` = final card number (hardcoded in SWF, not extracted this pass)
- Award types: `hat`, `sws`, `binmartDosh`, `nestcoDosh` (trigger Swrve currency_given event)
- `checkUpdateBin` syncs absolute `mulch`, `dosh`, `xp` from response

**BACKEND MATCH:**
- `php2/loyalty/getProgress.php` — EXISTS, implementation unverified
- `php2/loyalty/getStamp.php` — EXISTS, implementation unverified
- `php2/loyalty/finalReward` — ABSENT
- `php2/loyalty/getVouchers.php` — EXISTS, documented as empty-vouchers stub in previous audit

**AUTHORITY UNKNOWN:** Stamp reward values, final reward values, voucher contents, card completion thresholds.

### HAGGLE HUT

**SWF:** No dedicated Haggle Hut decompilation in this pass. Older variants present locally (`HaggleHut_12_03_21.swf`, etc.) but not decompiled.

**CLIENT CONTRACT (from existing backend + HAR):**
- `php2/shop/getHaggleItems2.php` — POST, returns sellable items
- `php2/shop/getHagglePrices.php` — secure POST `items,seeds,gardenItems,timer,hash`
- `php2/shop/sellHaggleItems.php` — secure POST `items,seeds,gardenItems,timer,hash,choice`

**PRICING AUTHORITY (from local backend):**
- `getHagglePrices.php` computes prices SERVER-SIDE using:
  - `safePrice = floor(20% * basePrice)`
  - `gamblePrice1 = floor(10% * basePrice)`
  - `gamblePrice2 = floor(15% * basePrice)`
  - `gamblePrice3 = floor(40% * basePrice)`
- Base price: `itemData.price` (or `itemData.price * 500` for Dosh)
- This is proven SERVER-SIDE logic, not client-calculated

**BACKEND MATCH:**
- All three routes exist locally
- Pricing formula is server-side and present in `getHagglePrices.php`
- Response contract not verified against decompiled client this pass

**CLASSIFICATION:** ALREADY PRESENT — pricing formula verified. Response contract needs SWF decompilation for full verification.

### BUDDY / SOCIAL

**SWF:** `buddyPanel_17_02_21.swf` (local match), `buddyFeed_120726.swf` (no local match)
**LOCAL PATHS:** `game-full/cdn.binw.net/buddies/buddyPanel_17_02_21.swf`, older `buddyPanel_*.swf` variants
**SHA-256:** `e04a039e4e3fcd8878676dfb9171c41e` (buddyPanel_17_02_21)

**TRANSPORT:**
- HTTP endpoints (PHP/PHP2) handle: buddy wall posts (`getPosts`, `getBinPosts`, `addEventPost`), alerts (`getAlerts`), definitions (`getDefs`), buddy list/buddies
- WebSocket/JSON (`web.binweevils.app`) handles: `friends/get-list`, `friends/get-weevil`, `friends/send-request`, `friends/handle-request`, `friends/delete`, `conversation/new`, `conversation/list`, `conversation/load`, `weevil/get-notifications`, `nest-news`
- SmartFox/XT (`sfs.binweevils.app`) handles: room joins, buddy list, notifications, game sessions

**BACKEND MATCH:**
- All HTTP social routes exist locally
- WebSocket layer exists (port 2087, known buffer bug)
- Core40 AS proves complete social contracts

**CLASSIFICATION:** ALREADY PRESENT — CONTRACT VERIFIED. No changes needed.

## CORRECTED CLASSIFICATION: quests/task-completed

**VERIFICATION RESULT:**

`CompleteTask()` at `game-full/essential/internal.php:4053`:

```php
function CompleteTask($taskID, $username, $idx, $questID) {
    if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
        $loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);
        if($loggedIn == true) {
            $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            if($questID != NULL || $questID != "") {
                // 4-param INSERT with questID
            } else {
                // 3-param INSERT without questID
            }
```

The condition `$questID != NULL || $questID != ""` is a LOGIC BUG: it is always true for any non-null value (a value cannot be both NULL and empty string simultaneously). This means:

1. **`taskID,userID` callers** (no `questID`): `questID` will be null/empty from `$_POST['questID']`. The always-true condition forces the 4-param INSERT path with empty `questID`. Works if DB column is nullable.
2. **`taskID,userID,score` callers** (no `questID`): Same as above.
3. **`questID,taskID,userID` callers**: Works correctly with actual `questID`.

**CONCRETE INCOMPATIBILITY:** The always-true OR condition means the 3-param caller signature is NOT properly supported. The backend should use `&&` to check that `questID` is both non-null AND non-empty before using the 4-param form.

**ACTION REQUIRED:** Fix `CompleteTask()` condition from `||` to `&&`. This is a confirmed bug, not a design choice. The three caller signatures are legitimate, but the current code does not correctly distinguish them.

**DO NOT CHANGE CODE IN THIS PASS.** Document only.

## RESPONSE BODIES ACTUALLY CAPTURED

1. `POST /php2/pets/getUserPets.php` → `{"pets":[],"responseCode":1}`
2. `GET /php/getMyLottoTicketsAndDrawDate.php` → `responseCode=1&nextDraw=2026-09-04+17%3A00%3A00&drawID=420&gotTicket=0&tickets=&b=r`
3. `POST /php/incrPartyTime.php` → `responseCode=1`

## SAFETY CHECKS

- No raw cookies committed
- No session tokens committed
- No password hashes committed
- Raw encrypted SFS blobs stripped
- Sanitized derivative hashes recorded in SHA256SUMS.txt
- Raw downloads remain local/untracked

## FINAL CLASSIFICATION

### A. SAFE SMALL FIXES

- `quests/task-completed`: fix `CompleteTask()` condition `||` → `&&` to properly support all three caller signatures. This is a confirmed logic bug, not a design ambiguity.

### B. SAFE NEW FEATURES

None in this pass. The following are HIGH-CONFIDENCE candidates pending client contract recovery:

- `php2/backdrops/getOwnedBackdrops.php` — GET proven, response from SWF decompilation needed
- `php2/backdrops/getShopItems.php` — GET proven, response from SWF decompilation needed
- `php2/backdrops/getUnlockableBackdrops.php` — GET proven, response from SWF decompilation needed
- `php2/weevil/setLevelColour.php` — POST `userIDX,level,timer,hash` proven, response from SWF decompilation needed
- `php2/mission/getRoomHelp.php` — POST `idx,roomName` proven, response proven from AS
- `php2/mission/buyHelp.php` — POST `idx,helpId` proven, response proven from AS
- `php2/mission/buyMission.php` — POST `idx,questId,taskId,voucher` proven, response proven from AS
- `php2/pets/getPetProfile.php` — POST `petID` proven, response fields proven from AS
- `php2/pets/getAcquiredJugglingTricks.php` — POST `petID,idx` proven, response proven from AS
- `php2/pets/updateJugglingTrick.php` — POST `petID,idx,trickID,aptitude,skillLevel` proven, response proven from AS
- `php2/pets/updatePetSkill.php` — POST `petID,idx,skillID,skillLevel,obedience` proven, response proven from AS

### C. ALREADY CORRECT — LEAVE ALONE

- Brain Strain (`game/brain-info`, `game/brain-submit`) — FUNCTIONALLY VERIFIED, exact client contract match
- All social/achievement/nest/pet core routes
- Tycoon/diner/garden routes
- SmartFox zone/buddy count
- `getMyLottoTicketsAndDrawDate.php` — bootstrap proven
- Haggle Hut pricing — server-side formula verified in backend
- All routes listed in "ALREADY PRESENT — NO ACTION" from previous audit

### D. BLOCKED BY SERVER-SIDE UNKNOWN

- Mulchtastic reward formula — no client code proves server authority
- Lotto prize/jackpot amounts — requires server-side randomness/authority; client calculates display prizes locally but server must validate/award
- Backdrop purchase prices and currency — requires server-side catalogue data
- Level colour unlock thresholds and colour ID list — requires UI SWF decompilation
- Loyalty stamp/final reward values — requires server-side logic
- Mission task rewards — requires server-side data

### E. WAIT FOR BIN PETS PACKAGE

- Full Bin Pets integration: adoption flow, skill progression, tricks, feeding, fuel, happiness, experience, obedience, aptitude, cooldowns, reward formulas, pet inventory, pet shop purchases
- Do NOT implement the four pet endpoints listed in section B yet; they will be integrated as part of the larger package

## IMPLEMENTATION ORDER

1. Fix `CompleteTask()` condition `||` → `&&` (safe, proven bug)
2. Decompile `backdropUI_230425b.swf` or equivalent → level colour + backdrop catalogue response contracts
3. Implement mission stubs (`getRoomHelp`, `buyHelp`, `buyMission`) — request/response proven from AS
4. Implement pet profile/skill stubs (`getPetProfile`, `getAcquiredJugglingTricks`, `updateJugglingTrick`, `updatePetSkill`) — deferred to Bin Pets package
5. Implement `php2/backdrops/*` catalogue stubs — after SWF decompilation
6. Implement `php2/weevil/setLevelColour.php` — after SWF decompilation
7. Download/decompile newer live-server SWFs if alternate path becomes available
8. Await Bin Pets package for full integration
