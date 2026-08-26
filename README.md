# Bin Weevils OG — Flash Private Server

Original (OG) Bin Weevils **Flash** private server — recovery baseline plus in-progress
re-application of post-baseline features.

> **Status: PUBLIC for review.** This repo was made public so a friend can look it over.
> It is a work-in-progress recovery of a known-good 18 Aug 2026 backup, **not** a finished
> or guaranteed-running server. See `GITHUB-HANDOFF.md` for the full picture.

## What this is
- `main` — the **immutable recovery baseline**: a faithful copy of the known-good OG Flash
  private server backup from **2026-08-18 13:30**, before later changes broke the project.
- `feature/room-events-mushrooms` — work branch where room events (Flum's Fountain 282,
  Figg's Cafe 287, Dosh's Palace 265) and the mushroom event DB have been re-added, and the
  old dev "stress test" instrumentation has been removed. **This is the branch to review.**

## Quick layout
- `game-full/` — Flash client / web front-end, PHP endpoints (`php/`, `php2/`), config (`binConfig/`), assets (SWFs, FLV, sounds, the `cdn.binw.net/` tree).
- `server/` — Node.js private-server source (entry point `server/server.js`).
- `electron/` — Electron client launcher (`npm start` → `electron .`).
- `bwps.sql` — MySQL database dump/schema (now also contains `mushrooms` + `claimedmushrooms` tables).
- `sql/` — standalone mushroom table dumps for reference.

## Local setup (from the original README)
1. Install XAMPP (Apache + MySQL) and Node.js.
2. Clone the repo.
3. Start Apache & MySQL in XAMPP.
4. Import `bwps.sql` into MySQL via phpMyAdmin.
5. Copy `game-full/` contents into your web root (e.g. `C:\xampp\htdocs`).
6. In `server/` and `electron/`: `npm install`.
7. Start the server: `node server/server.js` (from `server/`).
8. Start the client: `npm start` (from `electron/`).

## Review notes
- Stress-test/dev instrumentation ("Stress Test", "Stress Walk Test", "Stress Move", and a
  hard-coded desktop debug-log write) has been **removed** on the feature branch.
- Room events and the mushroom event database are **re-added** on the feature branch but
  **not yet runtime-tested** in this environment (no Node/PHP/MySQL runtime was available
  during the work). They need a real local stack to validate.
- See `GITHUB-HANDOFF.md` and `BASELINE-FILE-INVENTORY.md` for the full audit and inventory.

## Heads-up for reviewers
`CHECKPOINT_A_ADMIN_CREDS.txt` at the repo root holds **localhost-only** admin panel logins
for the private server (the DB itself stores bcrypt hashes). It is not a live/external secret,
but since this repo is now public, treat it as world-readable. Don't reuse those passwords
anywhere real.
