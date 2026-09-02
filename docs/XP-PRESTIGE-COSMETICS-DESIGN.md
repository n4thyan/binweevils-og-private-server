# XP / Prestige Cosmetics — Design Document

Date: 2026-09-02
Status: READ-ONLY DESIGN — no schema, endpoints, or renderers implemented yet

## 1. PURPOSE

Define a clean cosmetic layer for:

- PLAYER-CARD BACKDROPS
- LEVEL-STAR COLOURS / STYLES

This layer will eventually integrate with the lifetime-XP / prestige reward shop.

This document separates:

COSMETIC MECHANISM (catalogue, ownership, equip state, rendering)

from:

XP/PRESTIGE ACQUISITION (shop, costs, unlocks, progression)

so cosmetics can be built and tested before the final storefront exists.

## 2. CURRENT PROJECT STATE

Database `users` table already contains:

- `level` — current gameplay level
- `prestige_count` — prestige level
- `prestige_xp_base` — prestige base XP
- `xp` — lifetime XP
- `xp1` — banked/spendable XP
- `mulch`, `dosh` — currencies

No existing cosmetic/profile/backdrop/level-star columns were found in `users` during this read-only audit.

Backend already has:

- `game-full/php2/weevil/setLevelColour.php` — NOT present yet; planned
- `game-full/php2/backdrops/*` — NOT present yet; planned
- Live-recon evidence proves observed request contracts only

Recovered client evidence:

- `brainStrain_10_06_13.swf` decompilation confirms Brain Strain endpoints match local backend exactly — ALREADY CORRECT
- `lotto_01_03_21.swf` decompilation proves 6 Lotto endpoint contracts; bootstrap present, follow-on handlers absent
- `loyaltyCard_28_11_13.swf` decompilation proves `getProgress`, `getStamp`, `finalReward`, `getVouchers` contracts
- No decompilation of `backdropUI_230425b.swf` was possible because it is not in the local recovered corpus

## 3. RECOVERED LIVE EVIDENCE

From HAR/SFS capture 2026-09-02:

- `POST /php2/weevil/setLevelColour.php` with `userIDX,level,timer,hash`
- `GET /php2/backdrops/getOwnedBackdrops.php`
- `GET /php2/backdrops/getShopItems.php`
- `GET /php2/backdrops/getUnlockableBackdrops.php`
- Asset URLs: `assetsbackdrops/default.swf`, `assetsbackdrops/backdropUI_230425b.swf`, `externaluis/glowingStars.swf`, `level*.swf` (level0-90 range observed)

Response bodies for these routes were NOT captured.

## 4. ORIGINAL CLIENT EVIDENCE

- No decompilation of backdrop/level-colour SWFs was completed in this pass because:
  - `backdropUI_230425b.swf` is not in the local recovered corpus
  - `cdn.binweevils.app` download attempts returned HTML/redirect pages, not SWF binaries
  - No alternate download path was available

This means the exact client-consumed response fields for backdrop and level-colour endpoints remain UNKNOWN until SWF recovery is possible.

## 5. BACKDROP ARCHITECTURE

### 5.1 DATA MODEL (PROPOSED — NOT CREATED)

**Table: `backdropCatalogue`**

- `backdropID` INT PK — stable identifier
- `name` VARCHAR — display name
- `assetPath` VARCHAR — path to SWF/image asset
- `thumbPath` VARCHAR — thumbnail path
- `enabled` TINYINT(1) — whether available
- `sortOrder` INT — display order
- `metadata` JSON — optional: rarity, category, source

**Table: `playerBackdrops`**

- `playerID` INT FK → `users.id`
- `backdropID` INT FK → `backdropCatalogue.backdropID`
- `acquiredAt` DATETIME
- PRIMARY KEY (`playerID`, `backdropID`)

**Field in `users` (or equivalent player state):**

- `equippedBackdropID` INT NULL — FK to `backdropCatalogue.backdropID`
- NULL or 0 = default backdrop

### 5.2 DEFAULT BEHAVIOUR

If `equippedBackdropID` is NULL/0, the player-card renderer uses a deterministic default backdrop (e.g. `default.swf` or a built-in background).

### 5.3 VALIDATION RULE

Server MUST reject equip/purchase requests for backdrops the player does not own.

Future XP shop purchase flow:

1. Client requests reward purchase
2. Server verifies reward exists, enabled, not owned, meets requirement
3. Server grants ownership (`playerBackdrops` INSERT)
4. Client may then equip cosmetic

## 6. LEVEL STAR ARCHITECTURE

### 6.1 DATA MODEL (PROPOSED — NOT CREATED)

**Table: `levelStarStyles`**

- `styleID` INT PK
- `name` VARCHAR
- `assetPath` VARCHAR — star SWF or texture
- `enabled` TINYINT(1)
- `sortOrder` INT
- `metadata` JSON — optional unlock requirements, rarity

**Table: `playerLevelStars`**

- `playerID` INT FK → `users.id`
- `styleID` INT FK → `levelStarStyles.styleID`
- `acquiredAt` DATETIME
- PRIMARY KEY (`playerID`, `styleID`)

**Field in `users`:**

- `equippedLevelStarStyleID` INT NULL — FK to `levelStarStyles.styleID`
- NULL or 0 = default star appearance

### 6.2 ENDPOINT COMPATIBILITY

The recovered client sends:

`POST /php2/weevil/setLevelColour.php` with `userIDX,level,timer,hash`

The field name `level` in this request is a CLIENT CONTRACT NAME, NOT a gameplay level change.

Our endpoint implementation MUST translate this client value to our internal `equippedLevelStarStyleID`.

Example translation:

- Client sends `level=5` → server maps to `styleID=5` in `levelStarStyles`
- Server updates `users.equippedLevelStarStyleID = 5`

The server must validate that `styleID` is owned by the player before equipping.

## 7. DB MODEL OPTIONS

Option A — New dedicated tables (`backdropCatalogue`, `playerBackdrops`, `levelStarStyles`, `playerLevelStars`)

- Cleanest separation
- Minimal migration risk
- Easy to extend with new reward types

Option B — Generic reward/cosmetic tables

- More flexible but adds abstraction overhead now
- Recommended only if many reward types are imminent

**RECOMMENDATION: Option A** for now. Keep it simple until more cosmetic types are confirmed.

## 8. ENDPOINT/API MODEL (PROPOSED — NOT IMPLEMENTED)

### Backdrop

- `GET  /php2/backdrops/getCatalogue.php` — all enabled backdrops
- `GET  /php2/backdrops/getOwnedBackdrops.php` — player's owned set
- `POST /php2/backdrops/equipBackdrop.php` — secure `userIDX,backdropID,timer,hash`
- `POST /php2/backdrops/unequipBackdrop.php` — optional; or set NULL directly

### Level Star

- `GET  /php2/weevil/getLevelStarCatalogue.php` — all enabled styles
- `GET  /php2/weevil/getOwnedLevelStars.php` — player's owned set
- `POST /php2/weevil/setLevelColour.php` — secure `userIDX,level,timer,hash` (client name preserved)

All catalogue endpoints should be GET (or auth-optional JSON). Equip endpoints must be secure POST with hash.

## 9. PLAYER-CARD RENDERING HOOK

Current player-card/profile UI evidence:

- `game-full/cdn.binw.net/externalUIs/charactersProfile/mainProfile.swf` exists locally
- `charactersMain.xml` and `charactersXML/*.xml` define character data
- No existing PHP endpoint was found that returns profile card data with a backdrop field

**Rendering requirements:**

A. The player-card Flash UI must be able to load an external SWF or image as the card background.
B. The backend must supply the equipped backdrop asset path (or default) in the player-card data response.
C. If no new backend field is added, the client may request the backdrop asset independently after reading equipped state.

**Unknown:** Whether `mainProfile.swf` already supports external backdrops. This requires decompilation, which was not possible this pass.

## 10. LEVEL-STAR RENDERING HOOK

Current level rendering:

- `users.level` is the gameplay level
- The HUD/level display is in `core40.swf` and related UI SWFs
- `glowingStars.swf` and `level0-90.swf` are referenced by the live server but NOT in the local corpus

**Rendering requirements:**

A. The level-star cosmetic must render independently of `users.level`.
B. The client needs a way to select the equipped style ID and load the corresponding asset.
C. Server sends `equippedLevelStarStyleID` alongside level data.

**Unknown:** Exact rendering path in core40 or external SWFs. Requires SWF decompilation.

## 11. OWNERSHIP VALIDATION

Common pattern for both cosmetics:

```php
function canEquipCosmetic($playerID, $catalogueID, $table) {
    return ownsCosmetic($playerID, $catalogueID, $table);
}
```

Equip endpoint:

1. Verify session
2. Verify cosmetic is enabled in catalogue
3. Verify player owns it
4. Update equipped field
5. Return success + current equipped state

Purchase endpoint (future XP shop):

1. Verify session
2. Verify reward exists, enabled
3. Verify player does not already own it
4. Verify player meets lifetime XP/prestige requirement
5. Grant ownership
6. Return success

## 12. FUTURE XP/PRESTIGE SHOP INTEGRATION

The XP/prestige shop will:

- Reference a `rewardType` enum: `BACKDROP`, `LEVEL_STAR_STYLE`, ...
- Reference a `rewardRefID` FK to the appropriate catalogue table
- Check `users.xp` or `users.prestige_count` against `rewardRequirements`
- Grant ownership on purchase

The cosmetics layer does NOT need to know about XP costs. The shop layer handles pricing.

## 13. MIGRATION/COMPATIBILITY

- New columns in `users` (`equippedBackdropID`, `equippedLevelStarStyleID`) should default to NULL/0
- Default backdrop/star should be handled client-side or via a server-side constant
- No existing data migration required for phased rollout
- Old players without equipped values see defaults

## 14. TEST PLAN

Once implemented:

### Backdrop
1. Create catalogue entries in `backdropCatalogue`
2. Grant ownership via direct DB insert or admin endpoint
3. Equip via `equipBackdrop.php` — verify success
4. Attempt equip of unowned backdrop — verify rejection
5. Request player-card data — verify equipped backdrop path returned
6. Render player card in client — verify backdrop loads
7. Unequip — verify default restores

### Level Star
1. Create styles in `levelStarStyles`
2. Grant ownership
3. Call `setLevelColour.php` with valid owned style — verify success
4. Call with unowned style — verify rejection
5. Request profile data — verify equipped style returned
6. Render level HUD — verify style changes

### XP Shop Integration (later)
1. Create reward definitions linking backdrops/stars to XP costs
2. Verify purchase deducts `xp1` only, not `xp`
3. Verify `xp` (lifetime) never decreases
4. Verify ownership granted on purchase
5. Verify cosmetics can be equipped after purchase

## 15. IMPLEMENTATION PHASES

### PHASE A — Cosmetic Foundations (NO implementation this pass)
- DB tables: `backdropCatalogue`, `playerBackdrops`, `levelStarStyles`, `playerLevelStars`
- `users` columns: `equippedBackdropID`, `equippedLevelStarStyleID`
- Admin/test mechanism to grant cosmetics

### PHASE B — Backdrop Rendering
- Backend: `getOwnedBackdrops`, `equipBackdrop`
- Player-card response includes equipped backdrop
- Client renders backdrop asset

### PHASE C — Level-Star Rendering
- Backend: `getOwnedLevelStars`, `setLevelColour`
- Profile/HUD response includes equipped style
- Client renders level-star asset

### PHASE D — Admin/Test Grant
- Simple admin endpoint or direct DB insert path
- Allows testing cosmetics without XP shop

### PHASE E — XP/Prestige Shop (post-release)
- Reward catalogue
- Purchase flow
- Lifetime XP/prestige requirements
- Cost calculation and deduction

## 16. OPEN QUESTIONS

1. **SWF recovery**: Can `backdropUI_230425b.swf` and `glowingStars.swf` be obtained from the original asset corpus or alternate source? Decompilation is blocked until binaries are available.
2. **Client render path**: Does `mainProfile.swf` already support external backdrops? Requires decompilation.
3. **Level-star ID space**: What IDs/styles actually exist in the original client? Requires SWF recovery.
4. **Prestige interaction**: Does prestige unlock additional star styles or backdrops? Unknown until client evidence is recovered.
5. **Default asset**: What is the deterministic default backdrop/star for new players? Likely `default.swf` and default core40 star.
6. **HAR response contract**: What exact JSON/variables do the observed endpoints return? Missing response bodies must be recovered from SWF decompilation before implementation.
