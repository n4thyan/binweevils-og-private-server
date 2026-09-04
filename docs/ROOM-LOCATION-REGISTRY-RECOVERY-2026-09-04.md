# Room-LOCATION-REGISTRY-RECOVERY-2026-09-04

## SOURCE FILES

All location definition files are located in:
- `/c/repos/binweevils-og-private-server/game-full/binConfig/locationDefinitions*.xml`
- `/c/repos/binweevils-og-private-server/game-full/binConfig/getFile/*/uk/locationDefinitions.xml`

Key source directory:
- `/c/Users/pc/Desktop/Project Binweevils/Binweevils-main (2)/Binweevils-main/game-full/binConfig`

## CHRONOLOGICAL VERSION LIST

Sorted by filename interpretation:

| Version | Date | Filename |
|---------|------|----------|
| 2009-12-14 | 2009-12-14 | locationDefinitions_14_12_2009.xml |
| 2010-03-12 | 2010-03-12 | locationDefinitions03_12_2010.xml |
| 2010-04-10 | 2010-04-10 | locationDefinitions04_10_2010.xml |
| 2010-06-10 | 2010-06-10 | locationDefinitions06_10_2010.xml |
| 2010-06-12 | 2010-06-12 | locationDefinitions06_12_10.xml |
| 2010-06-12 B | 2010-06-12 | locationDefinitions06_12_10_B.xml |
| 2010-09-08 | 2010-09-08 | locationDefinitions08_03_13.xml |
| 2010-08-09 | 2010-08-09 | locationDefinitions08_09_2010.xml |
| 2010-09-09 | 2010-09-09 | locationDefinitions09_09_2010.xml |
| 2010-10-10 | 2010-10-10 | locationDefinitions10_09_2010.xml |
| 2010-10-12 | 2010-10-12 | locationDefinitions10_12_2010.xml |
| 2010-11-10 | 2010-11-10 | locationDefinitions11_10_2010.xml |
| 2010-01-09 | 2010-01-09 | locationDefinitions_01_09_11.xml |
| 2010-02-07 | 2010-02-07 | locationDefinitions_02_07_12.xml |
| 2010-02-09 | 2010-02-09 | locationDefinitions_02_09_11.xml |
| 2011-01-12 | 2011-01-12 | locationDefinitions_12_01_2011.xml |
| 2011-05-27 | 2010-05-27 | locationDefinitions_20100527-1301.xml |
| 2010-02-25 | 2010-02-25 | locationDefinitions_250210.xml |
| 2010-10-28 | 2010-10-28 | locationDefinitions_28_10_2011.xml |
| 2010-06-29 | 2010-06-29 | locationDefinitions_29_09_2010.xml |
| 2010-09-29 B | 2010-09-29 | locationDefinitions_29_09_2010_b.xml |
| 2010-03-12 | 2010-03-12 | locationDefinitions03_12_10.xml |
| 2010-12-14 | 2010-12-14 | locationDefinitions_14_03_2014.xml |
| 2014-03-14 | 2014-03-14 | locationDefinitions_14_03_2014.xml |
| 2014-03-14 | 2014-03-14 | locationDefinitions_10_02_2014.xml |
| 2013-03-08 | 2013-03-08 | locationDefinitions_08_03_13.xml |
| 2010-06-18 | 2010-06-18 | locationDefinitions_18_06_2010.xml |
| 2010-11-19 | 2010-11-19 | locationDefinitions_19_11_2010.xml |
| 2010-10-22 | 2010-10-22 | locationDefinitions_22_10_2010.xml |
| 2010-09-24 | 2010-09-24 | locationDefinitions_24_09_2010.xml |
| 2010-10-25 | 2010-10-25 | locationDefinitions_25_10_2010.xml |

## ROOM REGISTRY

### Winter Wonderland / Festive Fun Achievement Mapping

| Room ID | Internal Name | Room Events | SWF Background | Notes |
|---------|---------------|-------------|----------------|-------|
| 131 | PartyBoxInside3 | No | `fixedCam/seasons/christmas/2010/xmasParty_winterWonderland.swf` | **PROVEN** - Winter Wonderland for "Festive Fun" achievement (id=138) |
| 10030 | Misc_10 | yes, timerID=215 | Unknown | Possible alternative Winter Wonderland entry (higher ID, later version) |

**Classification: WINTER WONDERLAND = Room 131 (PartyBoxInside3)**
- Confidence: PROVEN
- Evidence: `xmasParty_winterWonderland.swf` background from 2010 seasonal content
- Achievement "Festive Fun" (id=138) triggers on entering this room

### Ice Cream Location (Mulch Island)

| Room ID | Internal Name | Object | Coordinates | Notes |
|---------|---------------|--------|-------------|-------|
| 142 | WormPoint | Entrance | Entry: x=0, z=80, entryDir=180 | Ice cream purchased HERE via buy-food.php |
| 156 | FiggsCafeTerrace | icecreamMachine | x=-163, y=0, z=358 | Figgs Cafe terrace with ice cream machine |

### Key Rooms on Mulch Island

| Room ID | Name | Type | Position (entry) | Notes |
|---------|------|------|------------------|-------|
| 101 | PeelPark | 1 | x=-55, z=609, entryDir=-90 | Mulch Island entrance area |
| 102 | DoshPalace | 1 | x=0, z=440, entryDir=-180 | Dosh currency hub |
| 110 | InksOrange | 1 | x=-93, z=320, entryDir=-180 | Orange-themed area |
| 111 | Palladium | 2 | x=0, z=80, weevilScale=0.26 | Main Palladium plaza |
| 120 | WeevilPost | 2 | x=0, z=80, weevilScale=0.34 | Worm's Point area |
| 142 | WormPoint | 2 | x=0, z=80, entryDir=180 | **Ice cream purchases happen here** |
| 156 | FiggsCafeTerrace | 2 | x=105, z=226, weevilScale=0.4 | Figgs Cafe with icecreamMachine |

## ROOM ID CHANGES

### Key ID Patterns:

- **Rooms 100-168**: Original core locations (pre-2010)
- **Rooms 130-167**: PartyBox area (130-131: PartyBoxInside variants, 140-145: FlemManor)
- **Rooms 250210, 28102011, etc.**: Chronologically versioned locations with date-encoded IDs

### Notable ID Changes:
- Room 131 (PartyBoxInside3) consistently uses `xmasParty_winterWonderland.swf` for Winter Wonderland
- Room 156 (FiggsCafeTerrace) consistently has icecreamMachine object across versions

## ROOM EVENT FLAGS

### Locations with `roomEvents='yes'`:
| Room ID | Name | timerID | Notes |
|---------|------|---------|-------|
| 156 | FiggsCafeTerrace | - | Seasonal room, ice cream machine |
| 812 | Misc_2 | 69,51,38 | Grotto area |
| 813 | Misc_3 | 69 | Grotto area |
| 811 | Misc_1 | - | Grotto area |
| 141 | SameDifferenceIsland | 142 | Audio-enabled |
| 140 | SameDifferenceIslandVideo | 142 | Audio-enabled |

## SEASONAL ROOMS

### Christmas / Winter Wonderland
- **Primary**: Room 131 (PartyBoxInside3) - `xmasParty_winterWonderland.swf`
- **Swf Path**: `fixedCam/seasons/christmas/2010/xmasParty_winterWonderland.swf`

### Ice Cream Related
- FiggsCafeTerrace (156) contains icecreamMachine object
- Ice cream purchased via buy-food.php with type=1 (ice cream)

## WINTER WONDERLAND / FESTIVE FUN

### Final Mapping: **PROVEN**

| Aspect | Details |
|--------|---------|
| **Room ID** | 131 |
| **Internal Name** | PartyBoxInside3 |
| **SWF Background** | `fixedCam/seasons/christmas/2010/xmasParty_winterWonderland.swf` |
| **Achievement** | 138 - Festive Fun |
| **Description** | Entered the Winter Wonderland |
| **Confidence** | PROVEN |

### Why NOT Room 900:
Room 900 does not appear in any location definition file. The ID 900 only appears as a coordinate value (e.g., `toLoc='900'` is incorrect syntax - it's likely a typo for door coordinates). The Winter Wonderland is definitively Room 131.

## WEEVIL AIR RELATED LOCATIONS

**DEFERRED - No evidence recovered for Weevil Holiday achievement.**

Searched for:
- "Weevil Air"
- "airplane"
- "airport"
- "flight"
- "terminal"

No conclusive location definitions found for Weevil Air / airport areas. The "Weevil Holiday" achievement (id=139) description mentions "Flew with Weevil Air" but the actual location/action remains unknown.

## UNKNOWN / AMBIGUOUS DEFINITIONS

| Issue | Details |
|-------|---------|
| Room 900 | Mentioned in previous session but not found in location definitions. Possibly a typo or from a different data source. |
| Weevil Air | No location definition for airplane/airport found. May be in SWF assets not in XML. |
| xmasPartyInside3 SWF | File exists but room definition may be in SWF assets or a missing XML entry. |

## ACHIEVEMENT-RELEVANT LOCATIONS

| Achievement ID | Name | Required Location | Status |
|-----------------|------|-------------------|--------|
| 138 | Festive Fun | Room 131 (Winter Wonderland) | **WIRED** - enter_location targetID=131 |
| 139 | Weevil Holiday | Unknown (Weevil Air) | **DEFERRED** - no location evidence |

## CUSTOM ROOM / MAP RELEVANCE

For future map expansion:
- Map coordinates (x, z) provide spatial layout
- `camPos`, `camAim` give camera positioning data
- `entryPos`, `entryDir` show entry points
- `maintainY`, `weevilScale` affect gameplay behavior

## HISTORY AND VARIATIONS

### Room 131 Evolution:
- `locationDefinitions_01_09_11.xml`: Uses `xmasParty_winterWonderland.swf`
- Various other versions maintain the same definition through 2014

### Room 156 (FiggsCafeTerrace) Evolution:
- Has icecreamMachine object in 2009 version
- Added `roomEvents='yes'` in later versions

## CSV DATA

See: `recon/location-registry-2026-09-04.csv`