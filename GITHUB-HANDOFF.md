# GitHub Handoff — OG Bin Weevils Flash Private Server (Recovery Baseline)

## Project
OG original Flash Bin Weevils private server.

## Purpose
Known-good pre-breakage recovery baseline. This repository is an **immutable recovery
baseline** for the original (OG) Bin Weevils Flash private server. It was captured BEFORE
later changes that broke the working project.

**Do not merge later broken project files into this baseline wholesale.** Apply future
fixes incrementally and test each one against this baseline.

## Current state — 2026-08-28 (read this before touching anything)

### Branches
- **`main`** (tip `a7c792f2`) — immutable recovery baseline + 3 verified backend fixes:
  - `83068432` fix(shop): restore proper `getShopItems` hash validation
  - `71c77f6c` fix(xp): preserve progression overflow across the Prestige boundary
  - `a7c792f2` docs: record the shop + XP fixes
- **`website-redesign`** (tip `42bdbd0f`) — **75 commits ahead of `main`, 0 behind**. Active
  rebuild of the public website around **authentic recovered Bin Weevils assets**. This is
  the branch under active visual development. **Do NOT merge to `main` and do NOT deploy to
  the VPS** until the owner accepts the visual design and a full test pass completes.

### What is done on `website-redesign`
- Homepage + shared shell recomposed from official assets: original `Bin Weevils` wordmark
  (`logo2.png`, replacing the old "Rewritten" logo), garden/Dump background (`background.png`),
  Weevil World hero scene (`banner.png`), Rigg mascot (`rigg.png`), authentic `PLAY NOW`
  button (`play-now.png`), section art (`racing.png`/`nest.png`/`garden.png`).
- Authentic **Burbank Small** brand font wired via `@font-face` (`game-full/assets/fonts/`).
- Recovered **Bin Weevils video adverts** (user-supplied) wired through the existing ad system
  (`site_ad_slot`): top leaderboard + a right-rail sidebar slot. The off-topic Weevil World
  membership banner was removed.
- All existing website functionality preserved (login, register, account state, XP rewards,
  server status, body-only Weevil preview, logout). No game systems touched.
- `game-full/weevil-creator/` runtime is gitignored (localhost-only preview renderer) and is
  NOT committed.

### Open / not-yet-done on `website-redesign`
- Visual design **not yet accepted** by the owner — further homepage polish + propagation of
  the shared shell to secondary routes (Register, Settings, XP Rewards, Download, Community,
  Help/About/Rules/Privacy/Credits, Play wrapper) is pending approval.
- In-client visual confirm of room events / mushroom DB still pending (needs eyes in the client).

### Local stack (for manual testing)
Apache + MariaDB (XAMPP) · Node game server `server/Main.js` (TCP 9339 + WS 2087) ·
Electron client `electron/` loads `http://localhost`. Deploy the site to htdocs with:
`git archive origin/website-redesign game-full | tar -C /c/xampp/htdocs --strip-components=1 -xf -`
then copy the gitignored `game-full/weevil-creator/` runtime.

### Source of truth
GitHub is the source of truth. Hermes does local runtime testing/deployment and verifies the
**actually-served** artifact (curl localhost + marker bytes), not just the edited file. Binary
SWF editing / decompile / recompile remains a local Hermes task when needed.

## Source folder (physical origin of this backup)
```
C:\Users\pc\Desktop\binweevils-backup-20260818-1330
```

## Backup timestamp
2026-08-18 13:30

## GitHub repository
- Name: `binweevils-og-private-server`
- Owner: `n4thyan`
- URL: `https://github.com/n4thyan/binweevils-og-private-server`
- Visibility: **PUBLIC** (made public 2026-08-26 for friend review)
- Default branch: `main`

## Baseline commit
- SHA: `e7cf4d120315e47d2fdde64c6823962e6ba8283d`
- Message: `baseline: known-good OG Flash backup 2026-08-18 1330`

## Working copy location used for this upload
A faithful mirror was created at:
```
C:\repos\binweevils-og-private-server
```
This mirror was initialized as a fresh Git repository (the original Desktop folder lives
inside `C:\Users\pc`, which is itself an unrelated Git repo; to avoid nesting, the backup
was copied to a clean path before `git init`). The original Desktop folder was NOT modified.

## Size / contents
- Tracked files: 22,681 (22,680 legitimate project files + this handoff + the file inventory)
- Approx. repository size: ~1.42 GB (excluding `node_modules`)
- Git LFS: **not required** — no single file exceeds GitHub's 100 MB hard limit
  (largest file is a 43.96 MB `.flv` video; 16,165 SWF files total, all under ~6 MB).

## What is excluded (and why)
- `node_modules/` (npm install artifacts under `electron/`, `server/`, and
  `game-full/848fjogfndsl/panel/`) — regenerable from package.json; not source.
- OS / editor junk: `.DS_Store`, `Thumbs.db`, `*.tmp`, `*.cache`.
- Temporary logs / crash dumps: `*.log` (e.g. `server/roomchange_debug.log`).
- `NUL` — a Windows reserved-device-name redirect artifact (37 KB HTML) at the backup
  root; runtime junk, not part of the game.

All SWFs, PHP, JS, XML, SQL, game configuration, source assets, room/location data, the
Electron client, and binary game resources (`.flv`, `.dll` Flash players, audio, images)
are intentionally retained — they are essential parts of the private server.

`CHECKPOINT_A_ADMIN_CREDS.txt` (plaintext local-dev admin panel logins) is **tracked** —
it is ordinary private-server configuration (the DB stores bcrypt hashes), not a live
external secret, so it is preserved as part of the known-good baseline.

## Basic directory map (as present in this baseline)
- `game-full/` — the Flash game client / web front-end
  - `binConfig/` — client/game configuration
  - `php/`, `php2/` — PHP endpoints
  - `cdn.binw.net/`, `externalUIs/`, `assets/`, `sounds/` — original/local SWFs, FLV videos, assets
  - `848fjogfndsl/panel/` — admin panel (its `node_modules` is excluded)
- `server/` — Node.js private-server source
  - `server.js` — server entry point
  - `BanBuilder/`, `ProfanityFilter/` — server modules
  - `static/` — static assets
- `electron/` — Electron client launcher
  - `package.json` (`start` script: `electron .`)
  - `plugins/` (includes `pepflashplayer` Flash player DLLs)
- `bwps.sql` — database dump / schema
- `reset_admins_checkpointA.sql` — admin reseed SQL
- `README.md`, `ROADMAP.md`, `HANDOFF_2026-08-17.md` — project docs from the backup
- `CHECKPOINT_A_ADMIN_CREDS.txt` — local-dev admin logins (see note above)
- `BASELINE-FILE-INVENTORY.md` — machine-readable file inventory

## How to launch locally (only what is identifiable from the backup)
These are launch procedures identified from files present in the backup. They assume a
local development environment with the relevant runtimes installed; this document does not
guarantee they run as-is and does not modify anything.

- Client launcher (Electron):
  From `electron/` run `npm install` then `npm start` (per `electron/package.json`
  `scripts.start = "electron ."`).
- Server (Node):
  Entry point is `server/server.js`. `server/package.json` lists the dependencies
  (mysql, express, discord.js, etc.). A typical local start is `node server/server.js`
  after `npm install` in `server/` — confirm against `server/server.js` before running.
- Database:
  `bwps.sql` is the schema/dump for the local MySQL instance used by the server.

## Development model (going forward)
- GitHub is the **source of truth**.
- Code/config changes (PHP, JS, XML, SQL, config) should preferably be prepared and
  reviewed through GitHub, so another coding agent can work directly from the repo.
- Hermes performs local runtime testing / deployment and verifies the actually-served artifact.
- Binary SWF editing / decompile / recompile remains a local Hermes task when necessary.

## Progress since the baseline (feature branch)
All post-baseline reapply work is isolated on the branch **`feature/room-events-mushrooms`**
so `main` stays the pristine recovery snapshot. Current state on that branch:

- **Re-added (not yet runtime-tested here):** room events for Flum's Fountain (282),
  Figg's Cafe (287), Dosh's Palace (265) — dispatchers in `server/BinWeevils.js`,
  `becomeWaiter`/`isWaiter` + joinOK tray/plate state in `server/Weevil.js`, 5 mushroom
  helper functions appended to `game-full/essential/internal.php`, new
  `game-full/php2/mushroom/collect-mushroom.php` endpoint, and `mushrooms` +
  `claimedmushrooms` tables added to `bwps.sql` (standalone dumps in `sql/`).
- **Removed:** the old dev "stress test" instrumentation — "Stress Test" / "Stress Walk Test"
  / "Stress Move" branches and a hard-coded `appendFileSync(...roomchange_debug.log...)`
  write in `server/BinWeevils.js`. The `fs` import is retained (used for `roomids.txt`).
- **Not done yet:** shop split (Nestco=Mulch / BinMart=Dosh), Dosh furniture thumbnail fix,
  launcher `.bat` scripts, and full runtime verification of the above.

> Verification note: the edits were statically checked (balanced, no duplicate methods,
> diff-scoped to the intended changes) but NOT executed — this environment had no Node/PHP/
> MySQL runtime. Runtime testing is still required.

## Security note
No VPS private keys, `.pem` SSH keys, API tokens, or external-service credentials were
found. Local private-server DB configuration (localhost/root-style) is normal and is
intentionally retained as part of the project.

**Now that the repo is PUBLIC:** `CHECKPOINT_A_ADMIN_CREDS.txt` (plaintext localhost admin
panel logins) is world-readable. It is not a live/external secret (the DB stores bcrypt
hashes), but reviewers should not reuse those passwords anywhere real. If you'd prefer it
removed from the public repo, say so — it can be `git rm`'d (and, if needed, purged from
history) without touching the rest of the baseline.

## WARNING
Do not merge later broken project files into this baseline wholesale. Apply future fixes
incrementally and test each one. This baseline exists so we always have a known-good
starting point to return to.

## Continue tomorrow (session state as of 2026-08-26)

**Repo is PUBLIC** for friend review. Both branches are in sync at commit `f9b38ad`.

**Done this session (all on `feature/room-events-mushrooms`, merged into `main`):**
- Room events re-added: Flum's Fountain (282), Figg's Cafe (287), Dosh's Palace (265)
  — dispatchers in `server/BinWeevils.js`; `becomeWaiter`/`isWaiter` + joinOK tray/plate
  state in `server/Weevil.js`.
- Mushroom event DB re-added: `mushrooms` + `claimedmushrooms` tables in `bwps.sql`,
  5 helper functions in `game-full/essential/internal.php`, new
  `game-full/php2/mushroom/collect-mushroom.php`.
- Stress-test dev instrumentation REMOVED from `server/BinWeevils.js` (Stress Test /
  Stress Walk Test / Stress Move + the hard-coded `roomchange_debug.log` write). `fs`
  import kept (used for `roomids.txt`).
- Docs updated: README, GITHUB-HANDOFF, ROADMAP ticked (§2 room events DONE w/ caveat).

**NOT done yet (next-session candidates):**
1. Shop split — Nestco = Mulch-only, BinMart = Dosh-only (server-side currency filter
   + `getStockItemsForLevel.php`); see ROADMAP §8 (still PARKED/investigated-only).
2. Dosh furniture thumbnail rendering fix (thumbnails didn't render before).
3. Launcher `.bat` scripts (Apache/MySQL check → Node → Electron) + diagnostic script.
4. **Runtime verification** of everything above — see blocker below.

**OPEN DECISION (security):** `CHECKPOINT_A_ADMIN_CREDS.txt` is now world-readable
(public repo). It's localhost-only admin logins (DB stores bcrypt hashes), not a live
secret, but say the word if you want it `git rm`'d or history-purged.

**KNOWN GOTCHAS for the next agent:**
- Room IDs per the supplied code: **265 = Dosh's Palace, 282 = Flum's Fountain,
  287 = Figg's Cafe.** (An earlier audit had 265/287 inverted — trust the code.)
- Baseline `Weevil.js` uses `this.nickname` (not `this.username`). Supplied snippets
  used `this.username` — already adapted during integration.
- `server/db.js` is the original baseline version (exports `query(sql, params, cb)`);
  do NOT rewrite it.
- The broken `Project Binweevils\Binweevils-main (1)` working copy is DEAD — work only
  from the GitHub clone / this mirror at `C:\repos\binweevils-og-private-server`.

**BLOCKER (environment):** This machine has **no `node`, `php`, or MySQL** runtime, so
nothing was executed — only static/ad-hoc verification (balanced delimiters, defs-once,
diff-scoped to intended changes). Full validation requires a machine with the runtimes:
`npm install` in `server/` + `electron/`, import `bwps.sql` into MySQL, start
`node server/server.js`, exercise rooms 265/282/287, POST `collect-mushroom.php`.

## Continue (session state as of 2026-08-27)

**Branch:** work is on `feature/room-events-mushrooms` (then synced to `main`).

**DONE this session:**
1. **Shop currency split (ROADMAP §8 server half) — COMPLETE.**
   - `getNestShopItems()` + `getPopularNestShopItems()` in `game-full/essential/internal.php`
     now split Binmart (dosh-only) / Nestco (mulch-only) by currency.
   - KEY correction: SWFs ALREADY self-identify — `Binmart.setStoreName` => `"binmart"`,
     `Nestco.setStoreName` => `"nestco"` (confirmed via SWF disassembly + deployed binaries).
     So NO SWF edit needed for the split.
   - DB has no `binmart` shopType; all dept-store items are `shopType='nestco'` distinguished
     by `currency`. Filter maps `storeName -> (shopType='nestco', currency)`.
   - Ad-hoc-verified against real `bwps.sql` itemtype dump: Binmart(dosh)=462, Nestco(mulch)=706,
     lossless vs old mixed 1,168. (First attempt bound `shopType=$storeName` and would have
     returned ZERO for Binmart — caught by the sim, fixed.)
   - Catalog endpoint actually used is `getStockItemsForTag.php` (not the stale `getShopItems.php`).
   - NOT runtime-tested (no PHP/MySQL here — known blocker).

2. **Binmart thumbnail diagnosis (item 2) — INVESTIGATED, NOT YET FIXED.**
   - Thumbnail URL = `IMG_PATH("users/") + data.img + "_thumb.swf"`
     (e.g. `cdn.binw.net/users/f_castlegam_slimefall_thumb.swf`), loaded by
     `DepartmentStoreItem` via `URLhandler.loadFromCDN`.
   - Those `_thumb.swf` assets DO exist for both dosh AND mulch items (3,618 under
     `cdn.binw.net/users/`), and both stores share the identical loader code. So this is
     very unlikely a missing-asset or SWF-code bug.
   - Symptom report ("none of the Binmart thumbnails load") predates the user standing the
     server up. Most likely environmental (server not running / Apache DocumentRoot / CDN
     path). Needs the live screenshot + a `curl -I
     http://localhost/cdn.binw.net/users/f_castlegam_slimefall_thumb.swf` (expect 200)
     before any code change. HOLDING edits until that evidence arrives.

**OPEN / NEXT:**
- Item 2 fix pending live evidence (screenshot + curl 200 check above).
- Bulk/multi-qty purchase still PARKED (separate SWF change).
- Launcher `.bat` scripts (Apache/MySQL check -> Node -> Electron) + diagnostic — not started.
- Full runtime verification of the shop split + rooms/mushrooms — needs PHP/MySQL/Node box.

## Continue (session state as of 2026-08-27, NIGHT)

**The runtime blocker is GONE.** This machine now HAS a full live stack:
Apache DocumentRoot `C:/xampp/htdocs`, MySQL `bwps` (root, no password), Node
serving the game on `:9339` (pid 12848, launched from `C:/repos/binweevils-og-private-server/server`),
WebSocket buddy server on `:2087`. The Electron client is launched from
`~/Desktop/binweevils-og-backup-20260827-1000/electron` and connects to
`http://localhost`. So "verify the actually-served artifact" is now real, not
simulated. The earlier handoff's "no node/php/MySQL" note is HISTORICAL — ignore it.

**Repo reality check:** the working tree is on `feature/room-events-mushrooms`
(585eecda); `main` (1b948caa) is ahead and is the branch that actually holds the
shipped fixes. `git status` shows shop/garden files as "modified" only because
HEAD is the feature branch — they are already committed on `main`. Don't be
alarmed by that status; trust `origin/main`.

### DONE since the previous continue block

1. **Shop work preserved cleanly to `main`** (commit `bb57d0ae`).
   - BinMart = Dosh, works. Nestco = Mulch, Featured works but normal catalogue
     still incomplete (OPEN). BinMart room doors now explicitly load
     `binmart_15_01_14.swf` (Nestco's own room wiring untouched).
   - Files: `getShopItems.php` (storeName param), `getStockItemsForTag.php`
     (img field + currency resolver), `getStockItemsForLevel.php` (recovered),
     `getFeatured.php` (name dedupe + valid JSON), `getBundles.php` (new, was
     404), `internal.php` (currency split), both `locationDefinitions.xml`.
   - **SECURITY FLAG (not a fix):** `getShopItems.php` still has a
     `checkHash()` bypass (`if(isset($tag) && isset($storeName))`) carried from
     earlier work. Required for local play (SWF secret != build secret) but MUST
     be resolved before any public/VPS deploy. Same root cause as the §9.8
     login-key work. `updatePetStats.php` has the same hash-999 class of bug.

2. **Room events — SERVER-SIDE verified** (task #1, runtime this session).
   - Confirmed the LIVE Node server (pid 12848) is the repo `server/` at `main`:
     `git diff origin/main -- server/` is empty.
   - Packet `2#5` dispatches 265->handleDoshs, 282->handleFlums, 287->handleFiggs
     (BinWeevils.js 683-695); handlers fully implemented + input-validated;
     `becomeWaiter`/`isWaiter` in Weevil.js complete; `roomids.txt` has all three
     rooms (server parsed it at startup, so they're live); `mushrooms` table has
     13 type rows.
   - **NOT a raw-socket fire-and-forget test** — no blind login script. Static +
     data proof only. FINAL IN-CLIENT VISUAL/GAMEPLAY TEST still PENDING: walk
     into Flum's Fountain (282), Figg's Cafe (287), Dosh's Palace (265) and
     confirm mechanics animate/pay out. That's the developer's eyes, not mine.

3. **Garden seed shop fixed** (commit `1b948caa`, tonight).
   - Root cause: `getItems2()`/`getPlants2()` call `usort()` with
     `Weevils_Gardenshop_Helper::compareItems` and `Weevils_Models_Itemtype::
     compareItems` — NEITHER CLASS EXISTED anywhere (not in repo, not in either
     Desktop checkout). PHP 8 fatal TypeError swallowed by `error_reporting(0)`
     => `GET /gardenshop/fetch` returned 200 + 0 bytes.
   - Fix: restored both as plain stable comparators (level asc, then price asc).
     Reconstruction of the sort order — the original classes were never
     recovered, so exact original ordering is unknown. No DB/schema/store change.
   - Verified: `php -l` clean; live fetch now 17,062 bytes, well-formed XML,
     24 `<item>` + 75 `<seed>` rows, no leaked errors.
   - FINAL VISUAL CLIENT TEST pending (SWF renders shelves/prices) — dev's eyes.

### OPEN SHOP BUGS (not fixed, deliberately)
- Nestco normal catalogue still doesn't populate in-client (Featured does).
  Server probes returned correct mulch-only rows for all 19 categories, so the
  remaining fault is most likely client-side/SWF, not PHP.
- Bundles/Showroom tabs glitch the store UI, panels blank, no way back to items.
  Bundles is data-empty by proof (no bundle table, collectionID 0/NULL,
  internalCategory NULL on all 1182 mulch rows) — so this is a UI-robustness
  defect, not missing data.
- `loungue` typo tag: 39 mulch + 29 dosh lounge rows invisible in both stores.

### KNOWN GOTCHAS (carried + new)
- Room IDs: 265=Dosh's Palace, 282=Flum's Fountain, 287=Figg's Cafe.
- `server/db.js` is baseline version (`query(sql,params,cb)`) — don't rewrite.
- **ROADMAP.md working copy is DRIFTED:** it is the 269-line version missing the
  entire §9 (2026-08-27 additions: prestige, login-key blocker, garden-shop bug,
  map/teleporter/summer-fair, code-system, and the explicit post-release ordering
  of website-redesign THEN xp-shop/leaderboard). The `main` ROADMAP.md (448 lines)
  is authoritative. Do NOT commit the working-copy ROADMAP.md — it would clobber
  §9. (Workspace ROADMAP.md was synced to origin/main this session to defuse it.)
- The two earlier branches `feature/nestco-catalogue-population` and
  `fix/live-server-drift-sync` (pushed from the OTHER clone) are now redundant —
  `main` already contains everything. Safe to delete.

### WHERE TO PICK UP TONIGHT
1. (Visual, dev-only) Confirm room events + garden shop in the live client.
2. Decide the client-hash/secret story (§9.8 + getShopItems bypass + updatePetStats
   999) — needs the design chat, it's auth. Don't do blind.
3. Then, if wanted: Nestco catalogue (SWF) / Bundles-Showroom UI lock (SWF) /
   loungue tag (5-min data fix) / Garden visual confirm.

---

## Continue — XP accounting pass (2026-08-27, night)

User approved the PRE-RELEASE XP accounting only (§9.1/§9.2), explicitly leaving
the §9.3 reward shop + leaderboard deferred. Done on `main` (commit `8c2958f4`).

**What changed**
- `game-full/essential/internal.php` — `addExperience*` advance lifetime `xp` AND
  cycle `xp1`; `levelWeevil()` rewritten to loop (multi-level catch-up, overflow
  carried), award L80 prestige reward at cap, and begin the next prestige cycle
  (prestige_count++, prestige_xp_base snapshot, reset to L1, difficulty
  `1 + prestige*0.5`); `rewardUserTrophy()` records in `prestige_trophies`
  (prestige-aware, idempotent per prestige).
- `game-full/php2/nest/level-up.php` — no longer double-awards; `levelWeevil()`
  handles per-level trophy/alert; endpoint just echoes final state + same hash.

**Key audit finding:** the XP schema already existed (xp/xp1/xp2/prestige_count/
prestige_xp_base/levels 1–80/prestige_trophies). No schema change. At prestige 0
the leveling formula is unchanged from before except it now catches up multiple
levels in one call.

**Verified against live bwps DB:** throwaway test weevil, cleaned up after. +5M
banked → L1→L65 in one call (overflow carried); bigger grant → L80, prestige
reward awarded, prestige_count→1, fresh L1 cycle (xp2=45=30×1.5). `php -l` clean;
no debug instrumentation; temp scripts removed.

**NOT done (per user scope):** §9.3 XP reward shop + lifetime leaderboard
(post-release, after website redesign). No client/Flash visual test of the
prestige UI was possible (can't see Flash) — the server model is correct but the
L80 badge + prestige-pip rendering is a client item to eyeball later.

**Open / next when resumed**
- Client visual confirm of prestige cycle (badge stays L80, prestige pips increment).
- §9.8 login-key + getShopItems hash bypass + updatePetStats 999 (the auth design
  chat — still blocked, needs user decision).
- Remaining shop bugs: Nestco catalogue (SWF), Bundles/Showroom UI lock (SWF),
  loungue tag (data fix).
- Post-release per §9.13 order: website redesign → LAST XP reward shop + leaderboard.
