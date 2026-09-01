# Bin Weevils OG Flash Private Server

Local-first recovery and extension of the original Bin Weevils Flash private server.

## Status at end of 1 September 2026

The current development checkpoint is on `website-redesign`. It contains the restored website, local server integration work and additive database migrations completed during the 1 September session.

This project is **not release-candidate clean**. Manual gameplay testing exposed an unresolved XP, level and progression integration concern. The next session must begin with a full local stabilisation pass and trace real gameplay failures through the server, database, Flash packets, HUD and website before any further feature work.

`main` remains unchanged at `a7c792f2970c9a6937ff22a8c270c90d4444e24c` and must not be used for this checkpoint.

No VPS deployment has been performed.

## Source of truth

Localhost is authoritative until the final local regression pass is complete:

- Repository: `C:\repos\binweevils-og-private-server`
- Active checkpoint branch: `website-redesign`
- Served website: `C:\xampp\htdocs`
- Website source: repository root paths such as `index.php`, `assets/`, `site/`, `settings/`, `register/` and `weevil-creator/`
- Legacy game/PHP tree: `game-full/`
- Node server: `server/`
- Electron/PepperFlash client: `electron/`

The same logical PHP endpoint may have a repository-root website copy, a `game-full/` copy, or both. Do not overwrite one whole tree with another. Compare and synchronize only the intended counterpart files.

## What this checkpoint preserves

### Website

- Repaired website redesign and previously truncated CSS/Settings files
- Original and supplementary Bin Weevils font integration
- Responsive homepage, Play, Settings and XP Rewards layouts
- Logged-in Weevil renderer using the stored account definition
- Custom Username Colour with strict `#RRGGBB` validation
- Prestige 0 and 1 XP Rewards catalogue
- DB-backed Bin Bulletin and shared Nest News source
- Format-aware local advertisement rotation and visible placements
- Electron 11 advert-size compatibility fallbacks
- Selective supplied character artwork on the Bulletin page
- Production-only Electron viewport with DevTools gated to explicit development mode

### Game and server integration

- Local Apache, MySQL, Node, SmartFox and Electron stack recovery
- SmartFox TCP on 9339
- websockify bridge on 3993 to 9339
- Authenticated local WebSocket service on 2087
- Runtime website status output into the actual XAMPP DocumentRoot
- Nestco and BinMart catalogue request/store corrections
- Flum, Figg and Dosh room-event corrections and replay-safe reward support
- Durable referral/invite registration and one-time reward lifecycle
- Flash-compatible Nest News XML from the same database source as the website Bulletin
- Local CDN/core client routing
- Additive migrations under `migrations/`

These systems are preserved, but several still require real Electron/Flash gameplay regression testing. Passing syntax, HTTP, database probes or isolated harnesses is not equivalent to passing the real client flow.

## XP semantics and open warning

Intended accounting:

- `users.xp`: lifetime XP earned. It must only increase.
- `users.xp1`: current banked/spendable XP.
- Earning XP should normally increase both values appropriately.
- XP Rewards purchases should decrease only `xp1`.
- A purchase must never reduce lifetime `xp`, an earned level, prestige or trophy.

Manual testing on 1 September showed that the displayed XP/progression state can behave inconsistently. Do not make speculative fixes. The next pass must trace:

`game event -> server award -> database xp/xp1 -> level calculation -> prestige calculation -> Flash packet -> HUD display -> website account display`

See `HANDOFF.md` and the high-priority section at the top of `ROADMAP.md`.

## Local stack

Use the repository and XAMPP paths exactly.

### Apache and MySQL

Start the existing XAMPP Apache and MySQL services. The active DocumentRoot is `C:\xampp\htdocs`.

Expected listeners:

- Apache: 80
- MySQL/MariaDB: 3306

### Node game server

The working directory is required because server data files are relative to `server/`:

```bash
cd '/c/repos/binweevils-og-private-server/server'
'/c/Program Files/nodejs/node.exe' Main.js
```

Expected listeners:

- SmartFox TCP: 9339
- Authenticated local website WebSocket: 2087 using plain `ws://`

Public TLS, when deployed later, belongs at a reverse proxy and must forward to the local Node service.

### websockify

```bash
'/c/Users/pc/AppData/Local/Programs/Python/Python313/Scripts/websockify.exe' 3993 127.0.0.1:9339
```

### Electron/PepperFlash

```bash
cd '/c/repos/binweevils-og-private-server/electron'
'./node_modules/electron/dist/electron.exe' .
```

The client loads `http://localhost`. DevTools opens only when `NODE_ENV=development`.

## Database migrations

The local database received these additive migrations during the 1 September session:

1. `migrations/2026-09-01-room-event-claims.sql`
   - Widens `claimedmushrooms.lastClaimed`
   - Adds unique replay protection on `(idx, mushroomType)`
2. `migrations/2026-09-01-referrals.sql`
   - Adds `referral_codes` and `referrals`
   - Preserves existing users, `users.invitedBy` and `gameinvites`
3. `migrations/2026-09-01-nest-news.sql`
   - Adds `news_articles` and `news_article_links`
   - Inserts the current private-server testing story if absent

Do not reapply migrations blindly. Review the target schema and backups first. VPS deployment remains deferred until local stabilisation and schema review are complete.

## Verification boundaries

Observed manually or in the real local client:

- The repository Electron/PepperFlash client launches against localhost.
- Website homepage, Play and Settings surfaces were visually inspected.
- Advertisements render in Electron after the Electron 11 size fallback.
- Manual gameplay exposed the unresolved XP/progression concern.

Verified only through syntax checks, HTTP/database probes or isolated harnesses:

- PHP and JavaScript syntax
- Catalogue response counts and currency separation
- Room-event packet/state harnesses and reward claim probes
- Referral registration/status probes
- Bulletin and Nest News XML responses
- TCP 9339, WebSocket 3993 and authenticated WebSocket 2087 connectivity
- Served/source hash coherence for the files covered by the checkpoint review

Every gameplay-facing path remains subject to the next real-client regression pass.

## Next action

Begin with the `FINAL LOCAL STABILISATION PASS` at the top of `ROADMAP.md`:

1. Reproduce the XP/level/HUD mismatch manually.
2. Capture the real request, packet and database transition.
3. Trace the complete progression path.
4. Fix only the demonstrated root cause.
5. Repeat the same manual flow.

Do not start achievements, additional Bin Pets integration, homepage feature changes or VPS deployment before that pass is complete.
