# Bin Weevils OG — Flash Private Server

Original (OG) Bin Weevils **Flash** private server — recovery baseline plus in-progress
re-application of post-baseline features and an active website redesign.

> Status: PUBLIC for review. This repo was made public so a friend can look it over.
> It is a work-in-progress recovery of a known-good 18 Aug 2026 backup, **not** a finished
> or guaranteed-running server. See `HANDOFF.md` for the current checkpoint and next steps.

## Branches

- **`main`** — the recovery baseline (tip `a7c792f2`) plus three verified backend fixes:
  - `83068432` — restore proper `getShopItems` hash validation (department-store)
  - `71c77f6c` — preserve XP progression overflow across the Prestige boundary
  - `a7c792f2` — docs recording the above
  - `main` is **immutable** except for incremental, tested fixes.
- **`website-redesign`** — the active website redesign (tip `082d329d`, well ahead of
  `main`). Rebuilds the public website around **authentic recovered Bin Weevils
  assets** (original logo wordmark, Burbank Small brand font, recovered advert creatives,
  garden/Dump scenery). Homepage, logged-in homepage, Play (embedded game + Fullscreen),
  My Weevil and the contained advert system are implemented. **NOT merged to `main`** and
  **NOT deployed to the VPS** — the visual design is still under review by the owner and
  awaits a full manual test pass before acceptance. Do not merge until the visual design is
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
The redesign is deployed as a copy of the website-redesign **root** tree (the site files live
at the repo root: `index.php`, `assets/`, `site/`, `settings/`, `game.php`, `register/`,
`community/`, `weevil-creator/`, …). `game-full/` inside the repo is the legacy original
site tree and is NOT what htdocs serves. The live `C:\xampp\htdocs` is a deployed copy of these
root paths.
```bash
# from the repo root, export the website root paths into the web root
git archive origin/website-redesign \
  index.php assets site settings game.php register community weevil-creator \
  | tar -C /c/xampp/htdocs --extract --strip-components=0 -f -
```
Then ensure `mainDEV663.swf` (the Flash client) is present at the htdocs root and the
localhost-only Weevil-preview runtime (`weevil-creator/`) is intact. Apache serves
`http://localhost/` as the redesign. The `main.swf` background bitmap swap (see HANDOFF.md)
is already applied in this tree.

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

## Website redesign — current state (2026-08-29)
Status markers: **DONE** = implemented + verified in headless QA; **NEEDS TEST** = implemented,
awaits your manual run in the real Electron/Flash client.

- **Design system** — DONE. Centred Bin Weevils shell, outdoor garden background, green nav/
  header, Burbank Small font, authentic/recovered artwork (logo `logo2.png`, `background.png`,
  `banner.png`, `rigg.png`). Page-specific compositions, not identical cards everywhere.
- **Public homepage** — DONE. Bin Bulletin, Welcome area, Returning Player login,
  Create a Weevil, feature CTAs, advert placements, authentic character art. The old
  "Game server / Weevils online / Build" diagnostic strip was removed; the online count
  ("● N Weevils online") now lives in the header (logged-in + logged-out), fed by the
  existing server-status poller.
- **Advert system** — DONE (headless-verified render/rotation). Creatives grouped by format:
  leaderboard/banner (top), MPU/rectangle (home rectangle), portrait/skyscraper (side desktop
  ads). Fixed slot dims, `object-fit: contain`, no layout shift, empty slots hidden, side ads
  hidden when viewport < 1723px. Game width beats ads on Play.
- **Logged-in homepage** — DONE. Personal "Welcome back" hero with the account's **rendered
  Weevil** (reused `weevil-creator` renderer; account `def` authoritative) + level/prestige +
  "XP to next level" + Play/My Weevil. Right "Your Weevil" panel: rendered Weevil, name (equipped
  colour), title badge, Mulch/Dosh/Prestige/Next-level. Server status NOT duplicated (owned by
  the status strip).
- **Play page** — DONE. Page is HEADER / GAME / FOOTER only. Removed the duplicated head block
  (eyebrow, "Enter the Bin", "Logged in as…", Desktop client / My Weevil / Fullscreen CTAs,
  renderer-dev note). Game embeds at native **940×653** with a snug dark frame (no blue letterbox);
  object fills the box via padding-top aspect-ratio. Discreet **⛶** fullscreen icon at the game
  frame's bottom-right corner. Fullscreen API targets the game wrapper only (100vw×100vh, explicit
  height so the Flash object fills, dark letterbox, ⛶ toggles enter/exit, Escape exits). Game UI untouched.
  (Fullscreen visual confirm in the real Electron/Flash client still recommended — headless QA browser
  blocks the fullscreen gesture, but the object-fill math is verified.)
- **My Weevil** — DONE. Player profile / progression page: prominent rendered Weevil, username,
  level, prestige, Mulch/Dosh (authentic icons), Lifetime/Banked XP, green progress bar + next
  threshold, compact XP Rewards (chips: name colour / title / profile background + Browse),
  preferences, change password. Raw definition hidden behind an "Advanced / Appearance data"
  disclosure (data + Copy intact).
- **Weevil renderer** — DONE. `assets/js/site-weevil-renderer.js` mounts every `[data-weevil-render]`
  from the account definition; hat fields zeroed only for the website wrapper (game untouched).
  Weevils now render **front-facing** (camera yaw 0); the header avatar is a cropped face/head-shot.
- **Live stats** — DONE. Authed pages poll `/site/account-state.php` every 20s and on window
  focus / tab visibility, updating every `[data-account-stat]` (Mulch, Dosh, level, prestige,
  Lifetime/Banked XP, next-XP) without reload. Source is the real account state; last-known values
  kept on failure. XP semantics (Lifetime never decreases, Banked is spendable) untouched.
- **main.swf background swap** — DONE (NEEDS FULL CLIENT TEST). `mainDEV663.swf` DefineBitsJPEG2
  (111) background replaced with authentic Garden art (1245×840). Backup `main.pre-background-swap-20260829-164658.swf`.
  See HANDOFF.md for hashes. No transparency experiment yet.
- **xat / Community** — NEXT / NOT IMPLEMENTED. `/community/` has the prepared design shell;
  the actual xat room embed is not wired. See ROADMAP § website section.

## Heads-up for reviewers
`CHECKPOINT_A_ADMIN_CREDS.txt` at the repo root holds **localhost-only** admin panel logins
for the private server (the DB itself stores bcrypt hashes). It is not a live/external secret,
but since this repo is now public, treat it as world-readable. Don't reuse those passwords
anywhere real.
