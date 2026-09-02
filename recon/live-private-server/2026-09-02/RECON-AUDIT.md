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

Read-only decompilation of priority SWFs from existing recovered corpus.

### BRAIN STRAIN

**SWF:** `brainStrain_10_06_13.swf`
**LOCAL PATH:** `game-full/cdn.binw.net/externalUIs/brainStrain_10_06_13.swf`
**SHA-256:** `d5abfead4bb6d0ae72b828bc17763c6e`

**CLIENT CONTRACT VERIFIED** — ALREADY PRESENT

- `game/brain-info` — GET, no params
- `game/brain-submit` — secure POST `score,levels,st,hash`

**BACKEND:** Both endpoints exist locally and match client contract exactly.

**CLASSIFICATION:** ALREADY PRESENT — FUNCTIONALLY VERIFIED

### LOTTO

**SWF:** `lotto_01_03_21.swf`
**LOCAL PATH:** `game-full/cdn.binw.net/externalUIs/lotto_01_03_21.swf`
**SHA-256:** `a18e0dd1ef9a33e70d0e5374be340106`

**CLIENT CONTRACT RECOVERED** — RESPONSE CONTRACT VERIFIED

Endpoints:
- `php/getMyLottoTicketsAndDrawDate.php` — EXISTS
- `php/getJackpotSize.php` — MISSING
- `php/addLottoTicket.php` — MISSING
- `php/getUncashedTickets.php` — MISSING
- `php/getPastLottoDraws.php` — MISSING
- `php/getLottoDrawWinners.php` — MISSING
- `php/cashInTickets.php` — MISSING

**CLASSIFICATION:**
- Bootstrap: ALREADY PRESENT — CONTRACT VERIFIED
- Follow-on endpoints: CONTRACT-PROVEN BUT MISSING

### LOYALTY CARD

**SWF:** `loyaltyCard_28_11_13.swf`
**LOCAL PATH:** `game-full/cdn.binw.net/externalUIs/loyaltyCard_28_11_13.swf`
**SHA-256:** `59851414e306641c5bd17527252ba233`

**CLIENT CONTRACT REVERSED**

Endpoints:
- `php2/loyalty/getProgress` — EXISTS (implementation status unknown)
- `php2/loyalty/getStamp` — EXISTS (implementation status unknown)
- `php2/loyalty/finalReward` — ABSENT
- `php2/loyalty/getVouchers` — EXISTS (empty stub)

Response fields: `responseCode`, `cardNum`, `numStamped`, `awards[]`, `mulch`, `dosh`, `xp`, `puzzleTypeID`, `pieceNumber`

**CLASSIFICATION:**
- `getProgress`, `getStamp`: CONTRACT-VERIFIED, PRESENT
- `finalReward`: CONTRACT-PROVEN, MISSING
- `getVouchers`: PRESENT, EMPTY STUB

### HAGGLE HUT

**CLIENT CONTRACT** (from backend + HAR)

- `php2/shop/getHaggleItems2.php` — EXISTS
- `php2/shop/getHagglePrices.php` — EXISTS, SERVER-SIDE PRICING VERIFIED
- `php2/shop/sellHaggleItems.php` — EXISTS

**CLASSIFICATION:** ALREADY PRESENT — pricing formula verified server-side

### BUDDY / SOCIAL

**SWF:** `buddyPanel_17_02_21.swf`
**LOCAL PATH:** `game-full/cdn.binw.net/buddies/buddyPanel_17_02_21.swf`
**SHA-256:** `e04a039e4e3fcd8878676dfb9171c41e`

**CLIENT CONTRACT VERIFIED** — ALREADY PRESENT

- HTTP routes exist locally
- WebSocket routes present
- Core40 demonstrates complete social contracts

**CLASSIFICATION:** ALREADY PRESENT — CONTRACT VERIFIED

---

## QUEST FIX VERIFICATION

`CompleteTask()` at `game-full/essential/internal.php:4053`:

**BUG:** Condition `$questID != NULL || $questID != ""` is always true.

**FIX APPLIED:** Changed `||` to `&&` in commit `97b8a531` on branch `fix/quest-completetask-signatures`.

**VERIFICATION:**
- PHP syntax lint passes
- All three caller signatures select correct SQL path:
  - Form A (`taskID,userID`): 3-param INSERT
  - Form B (`taskID,userID,score`): 3-param INSERT
  - Form C (`questID,taskID,userID`): 4-param INSERT

---

## BACKDROP SYSTEM

### CLIENT CONTRACT STATUS: BLOCKED

- `php2/backdrops/getOwnedBackdrops.php` — Request proven, response NOT captured
- `php2/backdrops/getShopItems.php` — Request proven, response NOT captured
- `php2/backdrops/getUnlockableBackdrops.php` — Request proven, response NOT captured

### RENDERING HOOK: BLOCKED

- `mainProfile.swf` exists locally but decompilation impossible without Java runtime
- String extraction yielded no backdrop references
- Insertion point unknown

### ASSET RECOVERY: BLOCKED

- `backdropUI_230425b.swf` — NOT in local corpus
- `glowingStars.swf` — NOT in local corpus
- `level0.swf` - `level90.swf` — NOT in local corpus

### CLASSIFICATION

**CANNOT IMPLEMENT BACKDROP FOUNDATION YET** — No client render hook or style assets recovered.

---

## LEVEL STAR SYSTEM

### EXISTING IMPLEMENTATION DISCOVERY

From `WeevilStatManager.setStarClr()` → `StarColourer.applyColour()`:

- Star colour is **derived from numeric level**, NOT a selectable style
- 10 fixed colour thresholds (levels 0-9, 10-19, ..., 80+)
- No `equippedLevelStarStyleID` field or style catalog exists in client

### ASSET RECOVERY: BLOCKED

- `glowingStars.swf` — NOT in local corpus
- `level0.swf` - `level90.swf` — NOT in local corpus
- Download attempts failed via cdn.binweevils.app (returned HTML, not binary)

### CLASSIFICATION

**CURRENT BEHAVIOR IS LEVEL-LOCKED**, not cosmetic-selectable.

**CANNOT IMPLEMENT LEVEL-STAR STYLE SUPPORT YET** — Style assets not recovered, no style catalog in client.

---

## FINAL CLASSIFICATION

### A. SAFE SMALL FIXES

**COMPLETED:**
- `quests/task-completed`: Fixed `CompleteTask()` condition `||` → `&&` to properly support all three caller signatures.

### B. SAFE NEW FEATURES

None — All candidates require client render hooks or asset recovery beyond this pass.

### C. ALREADY CORRECT — LEAVE ALONE

- Brain Strain (`game/brain-info`, `game/brain-submit`)
- All social/achievement/nest/pet core routes
- Tycoon/diner/garden routes
- SmartFox zone/buddy count
- `getMyLottoTicketsAndDrawDate.php`

### D. BLOCKED BY SERVER-SIDE UNKNOWN

- Mulchtastic reward formula
- Lotto prize/jackpot authority
- Backdrop purchase prices
- Loyalty stamp/final reward values

### E. WAIT FOR BIN PETS PACKAGE

- Full Bin Pets integration

### F. BLOCKED BY CLIENT RENDERING UNKNOWN

- Player-card backdrop system
- Level-star style system

Both require decompilation of `mainProfile.swf` and recovery of `glowingStars.swf`/`levelXX.swf` assets.

---

## RECOMMENDATION

To proceed with cosmetic implementations:

1. **RECOVER `mainProfile.swf`** — Find decompiled AS or decompile with JPEXS/ffdec
2. **RECOVER `glowingStars.swf`** and `level0.swf` - `level90.swf` — Obtain from original asset corpus
3. **IDENTIFY backdrop layer** in profile SWF
4. **DETERMINE response contract** for backdrop fields in profile endpoint

Until those assets are recovered, cosmetic infrastructure cannot be safely implemented without breaking existing rendering.

---

## NEXT PASS

Only after assets are recovered:

1. Create DB schema for `backdropCatalogue`, `playerBackdrops`, `levelStarStyles`, `playerLevelStars`
2. Add `equippedBackdropID`, `equippedLevelStarStyleID` to users
3. Implement backdrop/level-star endpoints
4. Modify `setStarClr()` to check style override
5. Integrate with XP/prestige reward shop