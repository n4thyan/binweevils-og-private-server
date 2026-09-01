# HANDOFF - End of 1 September 2026

## Read this first

The 1 September local development work is preserved on branch `website-redesign`.
The checkpoint is the commit containing this handoff; its parent is `fbe399048c7e5aaeeee18c6fadae182e5c7e3410`. Use `git log -1 --oneline` for the final checkpoint SHA.

`main` and `origin/main` remain untouched at:

`a7c792f2970c9a6937ff22a8c270c90d4444e24c`

Repository:

`C:\repos\binweevils-og-private-server`

Served website:

`C:\xampp\htdocs`

Localhost is the source of truth before VPS deployment. Do not touch the separate HTML5 project.

## Release status

**NOT RELEASE-CANDIDATE CLEAN.**

Manual gameplay testing exposed an unresolved XP, level and progression integration concern. Do not assume that passing syntax checks, direct HTTP probes or isolated server harnesses proves the real Flash flow.

The next session must start with the `FINAL LOCAL STABILISATION PASS` at the top of `ROADMAP.md`. Do not start achievements, Bin Pets integration, another room event, progression rebalancing, packet experiments, a redesign or VPS deployment first.

## Critical XP/progression warning

Intended semantics:

- `users.xp` is lifetime XP earned.
- `users.xp1` is current banked/spendable XP.
- Earning XP should normally increase both values appropriately.
- XP Rewards purchases should decrease only `xp1`.
- Lifetime `xp` must never decrease because of a purchase.

Observed gameplay suggested some combination of:

- displayed XP not matching expected progression state
- inconsistent level or level-up flow
- divergence between Flash HUD, database/server and website state
- inconsistent updates from different XP reward sources

No diagnosis or behavior change was attempted during this nightly wrap-up.

Tomorrow's mandatory trace is:

`game event -> server award -> database xp/xp1 -> level calculation -> prestige calculation -> Flash packet -> HUD display -> website account display`

## What the 1 September checkpoint contains

### Website

- Repaired redesign and restored previously truncated stylesheet/Settings content
- Original and supplementary font integration
- Responsive layout and overflow corrections
- Logged-in Weevil renderer using each account's stored definition
- Reduced website atlas loading and reliable header/head crop
- Custom Username Colour with unlock gating and strict hex validation
- Seven-item Prestige 0 and 1 XP Rewards catalogue
- XP Rewards purchases using banked `xp1` only
- Homepage Nest News article-preview removal
- Dedicated database-backed `/bulletin/` page
- Shared website and Flash Nest News source
- Format-strict advertisements with visible labels
- Shared 728x90 leaderboard, homepage 300x250 rectangle and wide-screen 300x600 rails
- Static posters for local ad videos
- Electron 11 explicit ad-height fallbacks
- Selective `201_Bin_Weevils03.webp` use as Bulletin artwork
- Electron DevTools gated to explicit development mode

### Game/server and local integration

- Local XAMPP/Node/Electron stack recovery
- SmartFox TCP 9339
- websockify 3993 to 9339
- Authenticated local website WebSocket 2087
- Runtime status output to the actual XAMPP DocumentRoot
- Nestco/BinMart read-only catalogue contract and category/tag handling
- Flum mushroom state, claim window and replay protection
- Figg waiter/tray state corrections
- Dosh room-event synchronization corrections
- Durable referral codes, registration attachment and one-time reward claim
- DB-backed Nest News and legacy Flash XML URL
- Dynamic legacy server-time endpoint
- Local CDN/core routing retained
- Additive room-event, referral and Nest News migrations

Do not roll this work back merely because further gameplay testing is required.

## Manual evidence

The following involved real visual/manual interaction rather than only source assertions:

- Repository Electron/PepperFlash client launched against `http://localhost`.
- Actual 800x600 Electron viewport was captured and inspected.
- Website homepage, Bulletin, logged-in Play and logged-in Settings were visually inspected.
- 1920x1080, 900px and narrower layouts were inspected for ad placement and overflow.
- The stored account Weevil rendered on logged-in website surfaces.
- The Play leaderboard remained above and separate from the Flash viewport.
- Manual gameplay testing by the developer exposed the open XP/progression concern.

This evidence does **not** establish that all stores, room events, referrals, news or progression flows work end to end.

## Synthetic and ad-hoc evidence

The following passed isolated checks during the session:

- PHP syntax across the touched served/source files
- JavaScript syntax for changed scripts
- Source/live hash checks for the covered synchronized files
- Nestco/BinMart catalogue HTTP and database probes
- Room-event source harness and reward claim/replay probes
- Referral registration, invalid-code and no-pending-status probes
- Bulletin and Flash Nest News XML HTTP parsing
- Dynamic server-time response
- Authenticated WebSocket 2087 probe
- TCP 9339 and WebSocket 3993 policy probes
- Temporary website verification harnesses, which self-deleted

Representative results recorded during the session:

- `AD_HOC_VERIFY_PASS`
- `ROOM_EVENT_HARNESS_PASS`
- `WS2087_AUTH_PASS`
- `TCP9339_POLICY_PASS`
- `WS3993_POLICY_PASS`
- `FINAL_PHP_LINT_PASS 33`
- `FINAL_JS_CHECK_PASS`
- `ELECTRON_MAIN_CHECK_PASS`
- `SERVED_SOURCE_HASH_PASS 16 files x 3 copies`

There is no known canonical repository test-suite command. Do not report the project as suite-green.

## Changes after the latest gameplay failure

The nightly wrap-up intentionally made no gameplay or progression behavior changes.

After the latest manual gameplay observation, the only preservation changes were:

- copied the already-running live `settings/xp-rewards.php` into repository root and `game-full`
- copied the already-running live `settings/xp-reward-action.php` into repository root and `game-full`
- updated `README.md`, `ROADMAP.md` and `HANDOFF.md`
- sanitized the local Git remote URL so an access token was not embedded in `.git/config`

The copied XP Rewards files were already the live XAMPP behavior. They still require regression testing as part of the wider XP audit.

## Required manual regression tomorrow

### XP, level and prestige

- Every active XP earning source
- `xp` and `xp1` database transitions
- Level thresholds and multi-level catch-up
- Nest level-up flow and trophies
- Level/prestige packets
- Flash HUD and website values
- Prestige 0 through 13 behavior
- XP Rewards deductions and equip/reset behavior

### Currencies and stores

- Mulch and Dosh earnings/deductions
- Referral and room-event rewards
- Nestco Featured, Nest Items, Nestige, Bundles and Showroom
- BinMart
- Nestco Mulch-only and BinMart Dosh-only separation
- Category, level and prestige filtering

Known content limitations remain explicit:

- Bundles has no authoritative recovered stock records.
- Showroom has only part of the referenced authoritative item data.
- Do not fabricate missing stock to make tabs appear complete.

### Room events

- Flum mushrooms, growth, claims, replay and reconnect state
- Figg waiter/tray lifecycle and room rejoin state
- Dosh room event and reward persistence

### Referrals

- Referral-prefilled registration
- Inviter/referred relationship
- Nest Hall popup only for a pending persisted reward
- One-time Mulch/Dosh/XP grant
- No replay farming or accidental deduction
- Website referral count/history

### Nest News

- Bulletin website article
- Flash XML
- Real Nest News SWF rendering and formatting

### Website and network

- Logged-out and logged-in homepage
- Play, Settings and XP Rewards
- Stored Weevil renderer
- Ads and responsive layout
- Online count/runtime status
- TCP 9339
- websockify 3993
- authenticated WebSocket 2087
- Electron/PepperFlash
- local CDN and hosts entry

## Source/live coherence at checkpoint review

The wrap-up compared the intended counterparts by SHA-256 rather than overwriting whole trees.

Confirmed coherent across the relevant live, repository-root and `game-full` copies:

- Homepage and shared website shell
- Site CSS and Weevil renderer JavaScript
- Advertisement configuration, helper, CSS and local assets
- Bulletin, Nest News and server-time endpoints
- Referral registration/site/status files
- Settings and XP Rewards files
- Room-event PHP endpoints and shared reward helper
- Nestco catalogue endpoint
- `weevil-creator` atlas loader

The repository contains no intended `.swf` modification from this session. No separate HTML5 project path is present in the checkpoint.

## Database migrations

The local database already received these additive migrations. They are included in the repository for later schema review and VPS deployment:

### `migrations/2026-09-01-room-event-claims.sql`

- Changes `claimedmushrooms.lastClaimed` to `BIGINT UNSIGNED`
- Adds unique key `uq_claimedmushrooms_user_type (idx, mushroomType)`
- Apply once after checking for duplicate legacy rows and existing indexes

### `migrations/2026-09-01-referrals.sql`

- Creates `referral_codes`
- Creates `referrals`
- Generates stable codes for existing accounts
- Preserves usable legacy `users.invitedBy` relationships as pending events

### `migrations/2026-09-01-nest-news.sql`

- Creates `news_articles`
- Creates `news_article_links`
- Seeds the current testing Bulletin story if absent

Do not reset, reimport or drop the database. Do not apply these blindly to VPS. Review target schema, existing indexes and a fresh backup first.

## Local stack

### Apache/MySQL

- Apache: port 80, serving `C:\xampp\htdocs`
- MySQL/MariaDB: port 3306

Start them through the existing XAMPP installation.

### SmartFox/Node

Working directory matters:

```bash
cd '/c/repos/binweevils-og-private-server/server'
'/c/Program Files/nodejs/node.exe' Main.js
```

Expected:

- SmartFox TCP: 9339
- Authenticated plain local WebSocket: 2087

### websockify

```bash
'/c/Users/pc/AppData/Local/Programs/Python/Python313/Scripts/websockify.exe' 3993 127.0.0.1:9339
```

### Electron/PepperFlash

```bash
cd '/c/repos/binweevils-og-private-server/electron'
'./node_modules/electron/dist/electron.exe' .
```

PepperFlash DLL:

`electron\plugins\pepflashplayer64_23_0_0_162.dll`

The normal client no longer opens DevTools automatically. Use `NODE_ENV=development` only when DevTools is deliberately required.

At the beginning of the next session, verify listeners and the actual Electron window rather than assuming old process identifiers remain valid.

## Roadmap additions

### Progress & Rewards homepage panel

After stabilisation, replace the duplicate right-hand logged-in "Your Weevil" panel with a concise Progress & Rewards panel. The left welcome panel remains the identity/Weevil/level surface. Candidate right-panel content:

- Banked XP
- Equipped title
- XP Rewards shortcut
- Latest achievement
- Achievement completion summary
- Referral count
- Next meaningful progression milestone

### Achievements

Audit the original Flash, SmartFox/Node, PHP and database achievement system before implementing anything. Reuse original IDs, tables, packets and UI if viable. Build a new server-authoritative framework only if required.

### Bin Pets

Additional Bin Pet work is available from another contributor. Treat it as a future integration and verification task, not a rebuild. Inspect it only after local stabilisation is complete.

## VPS deployment

**DEFERRED.**

Required order:

1. Final local stabilisation
2. Manual real-client regression
3. Clean checkpoint
4. Migration/schema review
5. Deploy
6. Live smoke test
7. Minor production polish

## First action tomorrow

Launch the known local stack, reproduce one XP/level mismatch in Electron/PepperFlash, record the exact pre/post `users.xp`, `users.xp1`, level and prestige values, and trace that single event through Node, the database, the outgoing Flash packet, HUD and website before changing code.
