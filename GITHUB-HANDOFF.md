# GitHub Handoff — OG Bin Weevils Flash Private Server (Recovery Baseline)

## Project
OG original Flash Bin Weevils private server.

## Purpose
Known-good pre-breakage recovery baseline. This repository is an **immutable recovery
baseline** for the original (OG) Bin Weevils Flash private server. It was captured BEFORE
later changes that broke the working project.

**Do not merge later broken project files into this baseline wholesale.** Apply future
fixes incrementally and test each one against this baseline.

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
