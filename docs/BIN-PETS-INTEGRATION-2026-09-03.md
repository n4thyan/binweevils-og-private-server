# Bin Pets Integration — 2026-09-03

## Package provenance
- Source: `Bin Pets.zip` (supplied by Bin Pets developer, 2026-09-03).
- Extracted to `/tmp/binpets_pkg/Bin Pets/`.

## Package file inventory
- SQL: `pets.sql`, `petacquiredskills.sql`, `petacquiredtricks.sql`, `petfood.sql`
- Essential: `essential/internal.php`, `essential/petJugglingData.csv`
- PHP: `php/getMyPetFoodStock.php`
- PHP2 pets: `buy-food.php`, `buy.php`, `change.php`, `getAcquiredJugglingTricks.php`,
  `getPetForADay.php`, `getPetProfile.php`, `getPetSkills.php`, `getUserPets.php`,
  `updateJugglingTrick.php`, `updatePetSkill.php`, `updatePetStats.php`, `validate-pet-name.php`
- Server: `BinWeevils.js`, `Weevil.js`, `Main.js`, `db.js`, `rest.js`, `ExtensionHelper.js`

## DB reconciliation
- Local `bwps` already contained partial `pets` + `petacquiredskills` schema.
- `petacquiredtricks` and `petfood` were **MISSING LOCALLY**.
- `pets` table locally lacked: `experience`, `lastStatChange`, `rented`, `adoptedDate` columns.
- Migration: `migrations/bin-pets-2026-09-03.sql`
  - Adds missing columns idempotently.
  - Adds auto-inc PK to `petacquiredskills`.
  - Creates `petacquiredtricks` and `petfood` if absent.
- **No data was destroyed.**

## Endpoint integration table
| Endpoint | Action | Final result |
|---|---|---|
| `php/getMyPetFoodStock.php` | ADDED | package baseline |
| `php2/pets/buy-food.php` | ADDED | package baseline |
| `php2/pets/buy.php` | KEPT EXISTING | local newer contract preserved |
| `php2/pets/change.php` | ADDED | package baseline |
| `php2/pets/getAcquiredJugglingTricks.php` | ADDED | package baseline |
| `php2/pets/getPetForADay.php` | ADDED | package baseline |
| `php2/pets/getPetProfile.php` | ADDED | package baseline |
| `php2/pets/getPetSkills.php` | KEPT EXISTING | known-good contract |
| `php2/pets/getUserPets.php` | KEPT EXISTING | local known-good contract |
| `php2/pets/updateJugglingTrick.php` | ADDED | package baseline |
| `php2/pets/updatePetSkill.php` | ADDED | package baseline |
| `php2/pets/updatePetStats.php` | KEPT EXISTING | local newer ownership + rate limit |
| `php2/pets/validate-pet-name.php` | KEPT EXISTING | local newer contract |

## Internal helper integration
Added to `game-full/essential/internal.php`:
- `buyPet`
- `insertPetSkills`
- `insertPetJugglingTricks`
- `getPetProfile`
- `buyPetFood`
- `feedPet`
- `getPetJugglingTricks`
- `updatePetStats`
- `updatePetSkill`
- `updateJugglingTrick`
- `changePetDef`

Preserved existing:
- `getUserPets`
- `getPetSkills`
- `CompleteTask` optional `questID` fix
- `buyFood` (legacy weevil energy)
- all other unrelated helpers

## Server handler integration
- `server/BinWeevils.js` — added `6#1`..`6#7` pet command handlers.
- `server/Weevil.js` — added pet state helpers:
  - `myPet` property in constructor
  - `applyPetState`
  - `toRawTarget`
  - `sendPetAction`
  - `sendPetExpression`
  - `sendPetGotBall`
  - `sendPetHome`
  - `sendPetNestDoor`
  - `sendPetJoinNestLoc`
  - `sendPetCommand`
- `petJugglingData.csv` copied to `game-full/essential/`

## NPM dependency changes
- `package.json` unchanged.
- `ws` was already present in `node_modules` and `package-lock.json`.
- No new dependencies required.

## XAMPP canonical sync
- Synced `game-full/essential/internal.php` → `C:/xampp/htdocs/essential/internal.php`
- Synced `game-full/php2/pets/*` → `C:/xampp/htdocs/php2/pets/`
- Synced `php/getMyPetFoodStock.php` → `C:/xampp/htdocs/php/getMyPetFoodStock.php`
- Synced `game-full/essential/petJugglingData.csv` → `C:/xampp/htdocs/essential/petJugglingData.csv`

## Verification performed
- PHP lint: all changed PHP files pass.
- JS syntax: unable to run Node syntax verification in this session (Node not installed in current shell).
- DB: migration SQL written and committed; must be executed against local `bwps` before playtest.
- Node startup: not executed (no local node runtime available in current shell).
- Endpoint smoke test: not executed (no live HTTP probe performed).
- Flash gameplay: not executed (manual playtest deferred to user).

## Manual testing still required
1. Run `migrations/bin-pets-2026-09-03.sql` against local `bwps`.
2. Restart Node server from project root with its real launch method.
3. Test each endpoint with valid session + test account.
4. Flash-client pet spawn/follow/room transition/ball/commands.

## Known limitations
- `rVarsUpdate`-driven `petDef`/`petState` relay in `Weevil.js` (ownership-aware echo in the package's larger `setRvars` rewrite) was **NOT merged**; only the action handlers above were merged.
- Node syntax verification was not executed due to missing local node binary.
- Live endpoint smoke tests were not executed.
