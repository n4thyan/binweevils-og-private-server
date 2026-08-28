# Bin Weevils OG — Flash Private Server

Original (OG) Bin Weevils **Flash** private server — recovery baseline plus in-progress
re-application of post-baseline features and an active website redesign.

> **Status: PUBLIC for review.** This repo was made public so a friend can look it over.
> It is a work-in-progress recovery of a known-good 18 Aug 2026 backup, **not** a finished
> or guaranteed-running server. See `GITHUB-HANDOFF.md` for the full picture.

## Branches

- **`main`** — the recovery baseline (tip `a7c792f2`) plus three verified backend fixes:
  - `83068432` — restore proper `getShopItems` hash validation (department-store)
  - `71c77f6c` — preserve XP progression overflow across the Prestige boundary
  - `a7c792f2` — docs recording the above
  - `main` is **immutable** except for incremental, tested fixes.
- **`website-redesign`** — the active website redesign (tip `42bdbd0f`, 75 commits ahead of
  `main`, 0 behind). Rebuilds the public website around **authentic recovered Bin Weevils
  assets** (original logo wordmark, Burbank Small brand font, recovered advert creatives,
  garden/Dump scenery). **NOT merged to `main`** and **NOT deployed to the VPS** — the
  visual design is still under review by the owner. Do not merge until the visual design is
  accepted and a full test pass completes.

## Quick layout
- `game-full/` — Flash client / web front-end, PHP endpoints (`php/`, `php2/`), config (`binConfig/`), assets (SWFs, FLV, sounds, the `cdn.binw.net/` tree).
- `server/` — Node.js private-server source (entry point `server/Main.js`; raw TCP game on `9339`, WebSocket bridge on `2087`).
- `electron/` — Electron client launcher (`npm start` → `electron .`; loads `http://localhost`).
- `bwps.sql` — MySQL database dump/schema.
- `sql/` — standalone table dumps for reference.

## Local stack (how it actually runs)
1. XAMPP: start **Apache** + **MySQL (MariaDB)**.
2. Import `bwps.sql` into MySQL.
3. Node game server (background, use the absolute node path in non-login shells):
   `cd server && "C:/Program Files/nodejs/node.exe" Main.js`
   → listens on raw TCP `9339` + WebSocket `2087`.
4. Electron client (project launcher, PepperFlash):
   `cd electron && "C:/Program Files/nodejs/node.exe" node_modules/electron/cli.js .`
   → loads `http://localhost` → `game.php` → `mainDEV663.swf`, WS `ws://localhost:2087`.

## Deploying the website-redesign to localhost (XAMPP htdocs)
The redesign is deployed by exporting the `game-full/` tree (NOT the whole repo) into the
web root — the working tree is intentionally left on another branch:
```
git archive origin/website-redesign game-full | tar -C /c/xampp/htdocs --strip-components=1 -xf -
```
Then copy the localhost-only Weevil-preview runtime (gitignored, never committed):
`game-full/weevil-creator/`. Apache then serves `http://localhost/` as the redesign.

## Review notes
- Stress-test/dev instrumentation has been **removed**.
- Room events + mushroom DB are on `main` (server-side verified) but still need an in-client
  visual confirm.
- The `website-redesign` homepage uses **only official/recovered Bin Weevils artwork**
  (logo `logo2.png`, `background.png`, `banner.png`, `rigg.png`, `play-now.png`, the
  Burbank Small font, and the user-supplied video adverts). See
  `docs/WEBSITE-ASSET-PROVENANCE.md`.
- All prior website functionality is preserved (login, register, account state, XP rewards,
  server status, body-only Weevil preview, logout). No game systems were modified.

## Heads-up for reviewers
`CHECKPOINT_A_ADMIN_CREDS.txt` at the repo root holds **localhost-only** admin panel logins
for the private server (the DB itself stores bcrypt hashes). It is not a live/external secret,
but since this repo is now public, treat it as world-readable. Don't reuse those passwords
anywhere real.
