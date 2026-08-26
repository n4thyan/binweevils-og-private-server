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
- Visibility: **PRIVATE**
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

## Security note
No VPS private keys, `.pem` SSH keys, API tokens, or external-service credentials were
found. Local private-server DB configuration (localhost/root-style) is normal and is
intentionally retained as part of the project.

## WARNING
Do not merge later broken project files into this baseline wholesale. Apply future fixes
incrementally and test each one. This baseline exists so we always have a known-good
starting point to return to.
