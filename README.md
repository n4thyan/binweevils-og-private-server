# Bin Weevils OG Flash Private Server

Local-first preservation, recovery and extension of the original Bin Weevils Flash stack.

## Current status — 9 September 2026

The complete known-working local development history has been consolidated for promotion to `main`. The previous `main` checkpoint from 28 August is historical; do not use old feature branches as newer sources of truth.

Authoritative locations:

- Repository: `C:\repos\binweevils-og-private-server`
- Release branch after consolidation: `main`
- Local Apache DocumentRoot: `C:\xampp\htdocs`
- Website source: repository-root paths such as `index.php`, `assets/`, `site/`, `settings/`, `register/` and `weevil-creator/`
- Legacy game/PHP/CDN source: `game-full/`
- Node/SmartFox server: `server/`
- Electron/PepperFlash client: `electron/`
- Additive database migrations: `migrations/`

The same served path can have a repository-root copy and a `game-full/` counterpart. Do not overwrite either tree wholesale. Compare exact counterparts and preserve the newer proven implementation.

No VPS deployment has been performed. Promotion to `main` records the local working checkpoint; it is not by itself a public-release declaration.

## Preserved systems

The September checkpoint includes the accumulated website, backend, client and preservation work from the former development branches, including:

- Restored responsive website and authenticated account surfaces
- Local font and artwork dependencies used by the website
- Website Weevil rendering and advanced arbitrary-RGB appearance support
- Local XAMPP, MySQL, Node/SmartFox, websockify and Electron integration
- Recovered endpoint, shop, quest, loyalty-card and live-recon work
- Bin Pets integration and later pet-state/nest-inventory corrections
- XP/level reconciliation and Prestige-aware progression fixes
- Achievement catalogue, activity-ledger and implementation work
- Current map and server-authoritative Nest teleporter work
- Account activation and logged-in homepage cleanup

Historical implementation and investigation detail remains in `ROADMAP.md` and `docs/`. Old dates and branch names in explicitly historical sections are evidence, not current instructions.

## Verification boundaries

A clean commit and passing syntax/contract tests prove repository integrity, not every visual gameplay path. Existing manual-test gates and open issues remain explicit in `HANDOFF.md` and `ROADMAP.md`. Do not mark a gameplay system complete solely from PHP lint, Node syntax, direct HTTP probes or isolated harnesses.

Known policy:

- `users.xp` is lifetime XP and must not decrease.
- `users.xp1` is banked/spendable progression XP.
- XP purchases must not remove earned levels, Prestige or trophies.
- Nestco remains Mulch-only and BinMart remains Dosh-only.
- Missing authoritative inventory, rewards or room contracts must not be fabricated.
- VPS deployment remains deferred pending a deliberate release/security review.

## Local stack

### Apache and MySQL

Start the existing XAMPP Apache and MySQL services.

Expected listeners:

- Apache: `80`
- MySQL/MariaDB: `3306`

### Node game server

Run from `server/`; its data paths are relative to that directory:

```bash
cd '/c/repos/binweevils-og-private-server/server'
'/c/Program Files/nodejs/node.exe' Main.js
```

Expected listeners:

- SmartFox TCP: `9339`
- Authenticated local website WebSocket: `2087` (`ws://` locally)

### websockify

```bash
'/c/Users/pc/AppData/Local/Programs/Python/Python313/Scripts/websockify.exe' 3993 127.0.0.1:9339
```

### Electron/PepperFlash

```bash
cd '/c/repos/binweevils-og-private-server/electron'
'./node_modules/electron/dist/electron.exe' .
```

The client loads `http://localhost`. Do not automatically launch or replace the user's browser profile.

## Database safety

Migrations are additive records of schema work. Before applying one to another environment:

1. Inspect the target schema and migration history.
2. Back up affected tables.
3. Confirm the migration is applicable and idempotent.
4. Never reset, drop or reimport the working database merely to test a feature.

## Where to continue

- `HANDOFF.md` — current checkpoint and verification boundary
- `ROADMAP.md` — current priorities followed by preserved historical detail
- `docs/` — dated audits, designs, recovery notes and evidence

Begin future work from freshly fetched `origin/main`. Keep any retained historical branch only when its unique context is intentionally required; do not merge a stale branch over newer `main` content.
