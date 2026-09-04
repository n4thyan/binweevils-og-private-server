# Player-Card + Level-Star Rendering Recovery

## 1. PLAYER CARD — mainProfile.swf ARCHITECTURE

**SWF:** `game-full/cdn.binw.net/externalUIs/charactersProfile/mainProfile.swf`
**SHA-256:** NOT RECOVERED (download attempts failed via cdn.binweevils.app)
**LOCAL PATH:** `game-full/cdn.binw.net/externalUIs/charactersProfile/mainProfile.swf`
**SIZE:** 704,875 bytes

### 1.1 SWF STATUS

- Binary exists in local corpus — exact filename match
- Decompilation REQUIRES external SWF tool (ffdec-cli.jar not runnable without Java)
- STRING EXTRACTION FAILED — binary contains no identifiable profile/backdrop strings
- No core40-baseline decompilation available at expected path

**BLOCKER:** Cannot decompile without Java runtime or JPEXS GUI.

### 1.2 ARCHITECTURAL INFERENCE FROM AVAILABLE FILES

Files in same directory:

```
game-full/cdn.binw.net/externalUIs/charactersProfile/
├── mainProfile.swf (704KB, profile UI)
├── mainProfile_05_09_14.swf (776KB, older variant)  
├── mainProfile_21_12_2012.swf (776KB, legacy variant)
├── charactersMain.xml (10.5KB, character list metadata)
├── charactersXML/*.xml (27 files, character definitions)
├── aboutMe/*.png (14 banner images)
├── photos/. (photo gallery directory)
└── family*.swf (14 files, ~25-40KB each, family portrait frames)
```

**Inference:**

- `charactersMain.xml` + `charactersXML/*.xml` suggest XML-driven character profile UI
- Photo directory and family SWFs suggest portrait/gallery component
- No standalone backdrop/background SWF found in profile directory

### 1.3 PLAYER CARD DATA FLOW (FROM LOCAL XML)

`charactersMain.xml` structure:

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

This XML is data-source driven. The `mainProfile.swf` likely:

1. Requests player data via PHP endpoint
2. Parses XML or object response
3. Renders character avatar + stats
4. Displays background via hard-coded or loaded asset

**UNRESOLVED:** Does the response include a backdrop field? Is there a backdrop layer?

### 1.4 PLAYER CARD RENDERING HOOKS (NEEDS DECOMPILATION)

Unable to determine from strings:

- Whether backdrop is rendered as fixed background or replaceable layer
- Asset path source (hard-coded vs dynamic)
- Whether `Loader`/`URLRequest` used for external backdrop
- Any `backdropID` field in character XML

**BLOCKER:** Decompilation required to answer.

## 2. LEVEL STAR — RECOVERED EVIDENCE

### 2.1 CLASS: `WeevilStatManager` (core40-baseline)

**File:** `scripts/com/binweevils/WeevilStatManager.as`

**Entry point:**
```actionscript
public function set level(param1:int) : void
{
    this._level = param1;
    this.levelNum_txt.text = String(this._level);
    this.setStarClr(this.levelStar_spr,this._level);  // <-- CALL SITE
    this.weevilActionsUI.setLevel(this.level);
}
```

**Call chain:**
```
set level() -> setStarClr(param1, param2) -> StarColourer.applyColour(param1, param2)
```

### 2.2 CLASS: `StarColourer` (core40-baseline)

**File:** `scripts/com/binweevils/utilities/StarColourer.as`

**Implementation:**
```actionscript
public static function applyColour(param1:Sprite, param2:int) : void
{
    if(param2 >= 80)
        param1.transform.colorTransform = new ColorTransform(1,1,1,1,-255,-141,-126,0);
    else if(param2 >= 70)
        param1.transform.colorTransform = new ColorTransform(1,1,1,1,-255,-83,255,0);
    // ... continues to level 0 with fixed color thresholds
    else
        param1.transform.colorTransform = new ColorTransform(1,1,1,1,20,-50,-120,0);
}
```

**KEY FINDING:** Star colour is **DERIVED FROM LEVEL**, not a separately selectable cosmetic.

- Level 0-9: Dark red-ish
- Level 10-19: Dark purple-ish  
- Level 20-29: Dark green-ish
- ...
- Level 80+: Bright red-ish

These are **fixed thresholds**, not a style catalog. The star sprite exists but its colour is programmatically set from the numeric level.

### 2.3 EXISTING ASSET ARTIFACTS

Found in `game-full/cdn.binw.net/assetsTycoon/`:

- `ps_backdrop_beach.swf` — 3,069 bytes
- `ps_backdrop_cny.swf` — 2,235 bytes
- `ps_backdrop_cny_fireworks.swf` — 2,745 bytes

These are **Tycoon store promotional backdrops**, NOT player-card backgrounds. They use the `ps_backdrop_` prefix which is part of the Tycoon shop item naming.

**NOT USEFUL FOR PLAYER CARD BACKDROPS.**

## 3. LEVEL STAR STYLE SYSTEM — FEASIBILITY

### 3.1 CURRENT BEHAVIOR

The star colour system is **level-driven, not style-driven**.

There is `levelStar_spr:Sprite` in `levelIndicator_spr`, and its colour is set via:

```actionscript
this.levelStar_spr = Sprite(this.levelIndicator_spr.getChildByName("levelStar_spr"));
```

Then `setStarClr(levelStar_spr, level)` applies one of 10 fixed colors based on level threshold.

### 3.2 PROPOSED COSMETIC INSERTION POINT

**Clean insertion:** Add `equippedLevelStarStyleID` field, then modify `setStarClr`:

```actionscript
// PSEUDOCODE - NOT IMPLEMENTED
public function setStarClr(param1:Sprite, param2:int) : void
{
    var styleID:int = this.bin.getEquippedLevelStarStyle(); // NEW
    if (styleID > 0 && this.levelStarStyles[styleID] != null) {
        // Load custom style asset
        var style:LevelStarStyle = this.levelStarStyles[styleID];
        param1.transform.colorTransform = style.colorTransform;
    } else {
        // ORIGINAL: derive from level
        this.applyLevelDerivedColour(param1, param2);
    }
}
```

**BUT:** Asset list and response contract for `glowingStars.swf`, `levelXX.swf` is NOT RECOVERED. These SWFs do NOT exist in local corpus.

## 4. RECOVERY GAPS

| Requirement | Local Corpus | Live HAR | Status |
|-------------|--------------|----------|--------|
| `mainProfile.swf` decompile | EXISTS (binary only) | Not captured | BLOCKED |
| `backdropUI_230425b.swf` | MISSING | Not in corpus | BLOCKED |
| `glowingStars.swf` | MISSING | Not in corpus | BLOCKED |
| `level0.swf` - `level90.swf` | MISSING | Not in corpus | BLOCKED |
| backdrop response contract | Not in AS | Response bodies missing | BLOCKED |
| level-star asset list | Hardcoded colors only | Not in HAR | BLOCKED |

## 5. FINDINGS SUMMARY

### PLAYER CARD
- **SWF architecture:** UNKNOWN (decompilation blocked)
- **Data flow:** XML-driven (`charactersMain.xml` + local `charactersXML/*.xml`)
- **Backdrop support:** UNRESOLVED (need decompilation)
- **Insertion point:** UNKNOWN (need decompilation to find backdrop layer)

### LEVEL STAR
- **Rendering class:** `WeevilStatManager.setStarClr()` → `StarColourer.applyColour()`
- **Current behavior:** Colour derived from numeric level (10 fixed thresholds)
- **Asset path:** Hardcoded colour transforms, no external SWFs
- **Cosmetic insertion:** Would require adding `equippedLevelStarStyleID` and conditional asset loading
- **Style assets:** `glowingStars.swf`, `levelXX.swf` NOT IN LOCAL CORPUS

### LOCAL ASSETS
- `ps_backdrop_beach/cny/fireworks.swf` — Tycoon promotional items, NOT player card backdrops
- No character profile backdrop assets found

## 6. CAN WE IMPLEMENT THE BACKDROP FOUNDATION SAFELY?

**NO** — Reason:

1. `mainProfile.swf` decompile required to determine backdrop rendering mechanism
2. Response contract for `getOwnedBackdrops.php`/`getShopItems.php` not proven from client
3. Client may have hard-coded backdrop fallback — need to verify
4. Level-star assets (`glowingStars.swf`, `levelXX.swf`) are NOT in local corpus — cannot implement style catalog

## 7. CAN WE IMPLEMENT LEVEL-STAR STYLE SUPPORT SAFELY?

**NO** — Reason:

1. While `WeevilStatManager.setStarClr()` is proven, it uses **level-derived colours**, not style IDs
2. Client shows NO evidence of reading a `levelStarStyleID` field
3. The SWFs for custom star styles (`glowingStars.swf`, etc.) are **not in local corpus**
4. No decompilation of `mainProfile.swf` or level SWFs to confirm style switching mechanism

## 8. RECOMMENDED NEXT STEPS

### Immediate blocker resolution:
1. **Download mainProfile.swf** from alternate CDN or archive source
2. **Find glowingStars.swf and level0-90.swf** in original asset corpus
3. **Obtain Java or ffdec-cli** for string extraction/decompile

### Once binaries recovered:
1. Decompile `mainProfile.swf` → identify backdrop layer
2. Decompile `glowingStars.swf` → identify style catalog
3. Trace profile data response parsing → identify backdrop/star fields

### Then possible implementation:
1. Backdrop: Add `equippedBackdropID`, modify profile response
2. Level Star: Add `equippedLevelStarStyleID`, modify `setStarClr()` to check style

---

**STATUS:** READ-ONLY CONTRACT RECOVERY. No implementation ready without asset decompilation.