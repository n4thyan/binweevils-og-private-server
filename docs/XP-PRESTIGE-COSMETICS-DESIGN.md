# XP / Prestige Cosmetics — Design Document

Date: 2026-09-02
Status: READ-ONLY DESIGN — no schema, endpoints, or renderers implemented yet
UPDATED: Added SECTION 13 — Rendering Recovery Findings

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

## 3. KEY FINDING: STAR COLOUR IS LEVEL-DERIVED, NOT COSMETIC

From decompiled `StarColourer.as`:

```actionscript
public static function applyColour(param1:Sprite, param2:int) : void
{
    if(param2 >= 80) { /* colour 1 */ }
    else if(param2 >= 70) { /* colour 2 */ }
    // ... 10 fixed thresholds ...
    else { /* default colour */ }
}
```

**IMPORTANT:** Current implementation applies colour based on numeric level thresholds. This is NOT a cosmetic selection system.

### Proposed Change Required

To support level-star style cosmetics, `setStarClr()` must be modified to:

1. Check for `equippedLevelStarStyleID` on player profile
2. If style ID present and valid, load custom asset
3. Otherwise, fall back to original level-derivative colour

This is a **breaking change** to the UI rendering path and requires recovered assets (`glowingStars.swf`, `levelXX.swf`) to implement.

## 4. BACKDROP ARCHITECTURE

### 4.1 Data Model (PROPOSED — NOT CREATED)

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

### 4.2 BLOCKER: Player-Card Rendering Mechanism Unknown

`mainProfile.swf` exists in local corpus but:

- Decompilation requires Java runtime (ffdec-cli available but unusable)
- String extraction yielded no profile/backdrop references
- Cannot determine if backdrop is hard-coded or replacement layer

**REQUIRED BEFORE IMPLEMENTATION:**

1. Decompile `mainProfile.swf` or obtain decompiled AS
2. Identify backdrop/background display object
3. Confirm render path accepts external asset or dynamic fill

See `RENDERING-RECOVERY.md` for full analysis.

## 5. LEVEL STAR ARCHITECTURE

### 5.1 Data Model (PROPOSED — NOT CREATED)

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
- NULL or 0 = default level-derivative star

### 5.2 BLOCKER: Level-Star Style Assets Not Recovered

Assets `glowingStars.swf`, `level0.swf` through `level90.swf`:

- NOT in local recovered corpus
- Download attempts to cdn.binweevils.app returned HTML/redirect, not SWF binary
- No decompiled evidence of style catalog exists

**REQUIRED BEFORE IMPLEMENTATION:**

1. Obtain actual level-star style SWFs from recovered Bin Weevils assets
2. Decompile to extract asset paths and style IDs
3. Verify client has style-switching logic

## 6. PLAYER CARD DATA FLOW

### 6.1 Current Local Evidence

`charactersMain.xml` exists and defines character data:

```xml
<characters>
  <character>
    <img>images.xml</img>
    <class>avatarClass</class>
    <name>username</name>
    <level>1</level>
    <location>roomID</location>
    <weevil>
      <avatar>avatarID</avatar>
      <weevilName>weevilName</weevilName>
    </weevil>
  </character>
</characters>
```

### 6.2 Unknowns (NEED SWF DECOMPILATION)

- How `mainProfile.swf` requests and parses player data
- Whether backdrop/background is in the response
- How backdrop asset path is used (Loader, frame, replace, etc.)
- Whether backdrop field exists: `backdropID`, `backgroundID`, etc.

## 7. ENDPOINT/API MODEL

### Backdrop (PROPOSED — NOT IMPLEMENTED)

- `GET  /php2/backdrops/getCatalogue.php` — all enabled backdrops
- `GET  /php2/backdrops/getOwnedBackdrops.php` — player's owned set
- `POST /php2/backdrops/equipBackdrop.php` — secure `userIDX,backdropID,timer,hash`

### Level Star (PROPOSED — NOT IMPLEMENTED)

- `GET  /php2/weevil/getLevelStarCatalogue.php` — all enabled styles
- `GET  /php2/weevil/getOwnedLevelStars.php` — player's owned set
- `POST /php2/weevil/setLevelColour.php` — secure `userIDX,level,timer,hash`

**NOTE:** `setLevelColour.php` request name preserves client contract, but server must translate `level` to `equippedLevelStarStyleID`.

## 8. OWNERSHIP VALIDATION

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

## 9. FUTURE XP/PRESTIGE SHOP INTEGRATION

The XP/prestige shop will:

- Reference a `rewardType` enum: `BACKDROP`, `LEVEL_STAR_STYLE`, ...
- Reference a `rewardRefID` FK to the appropriate catalogue table
- Check `users.xp` or `users.prestige_count` against `rewardRequirements`
- Grant ownership on purchase

The cosmetics layer does NOT need to know about XP costs. The shop layer handles pricing.

## 10. MIGRATION/COMPATIBILITY

- New columns in `users` (`equippedBackdropID`, `equippedLevelStarStyleID`) should default to NULL/0
- Default backdrop/star should render when NULL/0 (either deterministic default or level-derived fallback for stars)
- No existing data migration required for phased rollout
- Old players without equipped values see defaults

## 11. TEST PLAN

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

## 12. IMPLEMENTATION PHASES

### PHASE A — Cosmetic Foundations (BLOCKED)
- DB tables: `backdropCatalogue`, `playerBackdrops`, `levelStarStyles`, `playerLevelStars`
- `users` columns: `equippedBackdropID`, `equippedLevelStarStyleID`
- **BLOCKER:** Requires recovered client SWFs for render hook integration

### PHASE B — Backdrop Implementation (BLOCKED)
- Backend: `getCatalogue`, `getOwned`, `equipBackdrop`
- Player-card response includes equipped backdrop
- Client renders backdrop asset
- **BLOCKER:** `mainProfile.swf` decompile required

### PHASE C — Level-Star Implementation (BLOCKED)
- Backend: `getCatalogue`, `getOwned`, `setLevelColour` translation
- Profile/HUD response includes equipped style
- Client renders star style asset
- **BLOCKER:** `glowingStars.swf`, `levelXX.swf` not recovered

### PHASE D — Admin/Test Grant (BLOCKED)
- Admin endpoint or direct DB insert path
- Allows testing cosmetics without XP shop
- **BLOCKER:** Schema not created

### PHASE E — XP/Prestige Shop (deferred)
- Reward catalogue
- Purchase flow
- Lifetime XP/prestige requirements
- Cost calculation and deduction

## 13. RENDERING RECOVERY FINDINGS

### BLOCKERS BEFORE IMPLEMENTATION

#### Player Card Backdrop
| Requirement | Status | Notes |
|-------------|--------|-------|
| `mainProfile.swf` decompilation | **BLOCKED** | Java runtime unavailable, ffdec-cli JAR exists but no Java |
| Backdrop insertion layer | **UNKNOWN** | Cannot determine from string extraction |
| Response contract field | **UNKNOWN** | Need SWF to confirm `backdropID` etc. |

#### Level-Star Styles
| Requirement | Status | Notes |
|-------------|--------|-------|
| `glowingStars.swf` | **MISSING** | Not in local corpus, download failed |
| `level0-90.swf` | **MISSING** | Not in local corpus, download failed |
| Style catalog decompile | **BLOCKED** | Assets not available |

#### Existing Assets
- `ps_backdrop_beach/cny/fireworks.swf` are **Tycoon promotional items**, NOT player-card backgrounds
- No character profile backdrop assets found locally

### CAN WE IMPLEMENT BACKDROP FOUNDATION NOW?

**NO** — The rendering hook (`mainProfile.swf`) has not been decompiled. We cannot:

1. Know if the profile UI supports replaceable backgrounds
2. Know how to pass backdrop asset path to the client
3. Implement non-breaking player-card changes

### CAN WE IMPLEMENT LEVEL-STAR STYLE SUPPORT NOW?

**NO** — The style assets (`glowingStars.swf`, level SWFs) do not exist in our recovered corpus. The current `StarColourer` implementation is **level-derivative**, not style-based. To add cosmetic support:

1. Obtain actual star style SWFs
2. Modify `WeevilStatManager.setStarClr()` to check for style override
3. Load custom asset based on `equippedLevelStarStyleID`

### REQUIRED NEXT STEPS

1. **Asset recovery**: Find/obtain `mainProfile.swf` (decompiled), `glowingStars.swf`, `levelXX.swf`
2. **Decompilation**: Use ffdec, JPEXS, or swfstrings to extract ActionScript contracts
3. **Render hook identification**: Locate backdrop/background layer in profile SWF
4. **Response field discovery**: Confirm backdrop/star style fields in profile endpoint response

Only then can the cosmetic foundation be safely implemented without breaking the existing rendering system.