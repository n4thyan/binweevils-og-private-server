# ROADMAP.md — Bin Weevils Private Server

## Authoritative status: end of 1 September 2026

This section supersedes older status notes later in this historical roadmap.

- Active checkpoint branch: `website-redesign`
- Repository: `C:\repos\binweevils-og-private-server`
- Served local site: `C:\xampp\htdocs`
- Localhost remains the source of truth before any VPS deployment.
- `main` remains untouched at `a7c792f2970c9a6937ff22a8c270c90d4444e24c`.
- The separate HTML5 project is outside this work and must not be modified.
- The project is **not release-candidate clean**.

The 1 September checkpoint preserves the repaired website, Weevil renderer, XP
Rewards and Custom Username Colour, Bulletin/Nest News, advertisements, local
stack recovery, Nestco catalogue work, room-event corrections, referrals,
network bridges and additive migrations. Do not roll these changes back merely
because further manual gameplay testing is required.

## Priority 0: FINAL LOCAL STABILISATION PASS

Start the next session here. Do not begin another feature pass first.

Manual gameplay testing exposed an unresolved XP, level and progression
integration concern. Synthetic verification proved individual contracts, but it
did not prove the full Flash gameplay path. The working rule is:

**MANUAL FAILURE FIRST -> reproduce -> trace the actual request, packet and data
path -> fix the root cause -> verify manually again.**

### A. XP, level and prestige

- Reproduce XP earning in the real Electron/PepperFlash client.
- Confirm `users.xp` is lifetime XP and `users.xp1` is banked/spendable XP.
- Confirm earning XP updates both values appropriately.
- Confirm XP Rewards purchases subtract only from `xp1`.
- Audit level thresholds, multi-level catch-up and level-up triggering.
- Audit the Nest level-up flow and trophy state.
- Trace level/prestige packets from Node to Flash.
- Compare the database, Flash HUD and website account values after every step.
- Test Prestige 0 through 13 interactions without rebalancing them speculatively.
- Exercise every active XP reward source and XP Shop deduction.

Required trace:

`game event -> server award -> database xp/xp1 -> level calculation -> prestige calculation -> Flash packet -> HUD display -> website account display`

**Warning:** lifetime `xp` must never decrease because of an XP Rewards purchase.

### B. Currencies

- Mulch earnings and deductions
- Dosh earnings and deductions
- Shop purchases
- Room-event rewards
- Referral rewards
- Confirm rewards never become accidental deductions or duplicate grants

### C. Stores

- Nestco Featured
- Nest Items
- Nestige
- Bundles
- Showroom
- Nestco remains Mulch-only
- BinMart remains Dosh-only
- Level, prestige and category filtering
- Do not fabricate missing Bundles or Showroom records

### D. Room events

- Flum mushrooms
- Figg waiter and tray state
- Dosh room event
- Join and rejoin state
- Claim replay and cooldown protection
- Reward persistence after reconnect

### E. Referrals and invites

- Registration referral code and prefilled link
- Inviter/referred relationship
- Nest Hall popup only when a persisted reward is pending
- One-time reward grant with no replay farming
- Mulch, Dosh and XP reward values
- No accidental deduction
- Website referral status and history

### F. Nest News

- Bulletin database records
- Website article rendering
- Flash-compatible XML
- Real Nest News SWF rendering
- Links and content formatting

### G. Website

- Stored-definition Weevil renderer
- Logged-out and logged-in homepage
- Play page
- Settings
- XP Rewards and Custom Username Colour
- Advertisements at desktop, 1920x1080 and Electron 800x600 widths
- Online count from the runtime status bridge
- Responsive layouts and horizontal overflow

### H. Network and local client

- SmartFox TCP 9339
- websockify 3993 to 9339
- Authenticated local WebSocket 2087
- Electron/PepperFlash client
- Local CDN routing
- Required hosts entry

## Homepage follow-up: Progress & Rewards

Do not implement until the stabilisation pass is green.

The logged-in homepage currently duplicates the account Weevil and basic stats in
both the left welcome panel and the right "Your Weevil" panel. Keep the left panel
as the main identity/progression surface. Replace the right panel with a concise
**Progress & Rewards** panel that does not repeat the same Weevil, username, level
or currencies.

Candidate contents:

- Banked XP
- Equipped title
- XP Rewards shortcut
- Latest achievement
- Achievement completion summary
- Referral count
- Next meaningful progression milestone

## Future audit: original achievement system

Do not implement achievements before auditing what already exists in the Flash
client, SmartFox/Node server, PHP and database. Inventory original achievement
IDs, tables, packets and UI behavior. Reuse the original framework if viable.
Create a new server-authoritative framework only if the recovered system cannot
support the private server safely.

Potential later achievements include:

- First XP Shop Purchase
- Custom Username Colour unlocked
- First title unlocked
- Referral milestones
- First Prestige and later Prestige milestones
- Room-event and mushroom milestones
- Future Bin Pet adoption/training milestones
- Restored-content milestones

Achievement summaries may later feed the homepage Progress & Rewards panel.

## Bin Pets integration note

Additional Bin Pet assets, code and work are available from another contributor.
Do not rebuild or integrate them before the final stabilisation pass. Treat the
supplied work as an integration and verification task: inspect provenance and
contracts, compare it with the current client/server/database model, merge it
carefully, then test it manually.

## VPS deployment: DEFERRED

Do not deploy the current checkpoint. The required order is:

1. Final local stabilisation
2. Real-client manual regression pass
3. Clean checkpoint review
4. Schema and migration review
5. VPS deployment
6. Live smoke test
7. Minor production polish only

## Historical roadmap below

The following sections preserve earlier planning and investigation context. Any
old branch, path, completion or deployment statement that conflicts with the
authoritative status above is superseded.

---

## Checkpoint completion record

| Checkpoint | Scope | Status |
|------------|-------|--------|
| A | Auth / account security | CONFIRMED |
| B | Invalid-packet / kick refactor | CONFIRMED |
| C | Chat filtering (asterisks + `]]>` CDATA fix) | CONFIRMED |
| D | Hardening / quality (incl. A7/A8 + D8–D11 gaps) | CONFIRMED |

D gaps closed this pass: A7 create-weevil rate limit, A8 web-login rate limit,
D8 unified moderation + reason logging, D9 ~200-packet history, D10 generic
per-action rate limit, D11 movement/coordinate validation. D12/D13 (broader
upsert + single-userID fetch) deliberately NOT done — over-scoped against the
1:1 source; flagged for the refactors below instead.

---

## 1. Structural refactors (deferred)

- **Module splitting of `server/Weevil.js`**
  Reason: `Weevil.js` is ~2520 lines as a single class (auth, chat, movement,
  moderation, inventory, game logic all in one file). Splitting per-concern
  improves maintainability. Net-new structure, NOT required for backend
  stability — defer until the core server spine is confirmed in real multiplayer.
- **Sockets-as-Maps**
  Reason: `BinWeevils.js` tracks `weevils` / `socketIdList` as plain objects.
  Converting to `Map` is a refactor, not a fix. Defer.
- **XML builder/parser unification**
  Reason: chat/room packets are hand-built strings plus `fast-xml`/`xml2js`.
  A single safe builder reduces `]]>`/CDATA regressions (the C-class issue).
  Net-new, defer until after room-events land so the builder matches real traffic.
- **Graceful disconnect packets**
  Reason: moderation/quit currently `socket.end()`/`destroy()` hard. A logout
  notification packet is polish, not stability. Defer.

## 2. Room events — **DONE (2026-08-27, on `main` — server-side runtime-verified)**

- ~~The developer has a SEPARATE room-event implementation to supply later.
  Do NOT write room-event logic now.~~ **Supplied and re-added.** Migrated into
  `BinWeevils.js` dispatch alongside the existing `2#5` (roomEvent) handler, covering
  Flum's Fountain (282), Figg's Cafe (287), Dosh's Palace (265); `becomeWaiter`/
  `isWaiter` + joinOK tray/plate state in `Weevil.js`; mushroom DB/endpoint.
  **Server-side deployment/dispatch/DB verified at runtime** (live Node on :9339
  serves from `server/`, `roomids.txt` loads the three rooms, handlers wire to the
  `2#5` packet, `mushrooms` table provisioned). Final in-client visual/gameplay
  test still PENDING (needs eyes in the live client).

## 3. File cleanup (deferred — audit before delete)

- `game-full/gameTOBECHANGED.php` — present, name implies obsolete/to-be-changed.
  Action: determine if referenced; if not, propose deletion for approval. NOT
  deleted yet (spec: never delete by filename alone).
- The earlier-referenced `registerNEW` is NOT present in this working copy
  (only `game-full/register/`). No action.
- Reason: cleanup is safe only after the server spine is confirmed stable.

## 4. main.swf cleanup / migration (deferred — SOURCE AUDIT REQUIRED)

Present in `game-full/`:
  `mainDEV661.swf`, `mainDEV662.swf`, `mainDEV663.swf`
  `main_22_12_20.swf`, `main_22_12_20_christmas.swf`, `main_22_12_20_halloween.swf`
  `main_24_01_21.swf`, `main_25_05_21.swf`
  (NO plain `main.swf` found — authoritative SWF is currently UNIDENTIFIED.)

Action: identify the authoritative main.swf; diff each DEV/versioned SWF for
custom fixes; migrate any needed changes; only then remove obsolete DEVs.
Reason: source-grounded migration, not blind deletion (spec section 13).

## 5. New features (deferred — net-new, not backend stability)

- **Extra Bin Pets** — net-new gameplay; defer.
- **Extra nest floors** — net-new gameplay; defer.
- **Wheelspin / spin actions** — D10 already reserves a rate-limit slot
  (`spin`); the feature itself is net-new; defer.
- **Hat/expression/nest action expansion** — D10 already rate-limits `move`;
  broader high-frequency actions can be added incrementally; defer.
- **Movement/level-gate validation (beyond D11)** — D11 covers coordinate
  bounds + rate; ability/zone/level-gated powers can be added once game logic
  is better understood from real traffic; defer.

## 6. Deployment (future)

- **VPS move** — only after local is confirmed and the admin panel remains
  modToken-locked (Checkpoint D already enforces this). Game endpoints are
  public-read; admin panel is mods-only. Developer plans a real multi-user VPS
  test with a friend. Not started.

---

## Bin Pets — status: COMPLETE (core adoption + bowl/bed rewards + pet skills, 2026-08-18)

STATUS: COMPLETE for the core feature. Adoption works end-to-end, bowl+bed are
rewarded with the CORRECT per-colour items, and the pet skill tree is seeded so
getPetSkills returns the correct contract. Developer live-retest pending (Rick
reset to level 1 / 5000 mulch / 25 dosh / no pet for clean manual test).

DONE:
  [2026-08-17 pass]
  • Seeded `itemtype` with 6 Bin Pet shop rows (IDs 2846–2851, shopType=binPetShop,
    category 61). Species renamed to documented Bin Pet personality types
    (Cool/Cute/Scary/Funny/Sporty,Silly) — NAMES ARE PLACEHOLDER.
  • Added php2/pets/adoptPet.php — INSERTs a pets row, deducts currency, grants
    XP + "Adopt a Bin Pet" achievement. Auth = checkHash. Rate-limited.
  • Added php2/pets/feedPet.php — feed/play/train, updates pet stats, clamped,
    rate-limited.
  • Mirrored 597/597 Binpet SWFs into game-full/cdn.binw.net (incl. BinPetsShop
    location SWF: externalUIs/shops/departmentStores/binPetsShop_06_01_14.swf).
  [2026-08-18 pass — core adoption fixed]
  • buy.php root-caused + fixed: bind_param type-count mismatch (was 9 for 11
    values → ArgumentCountError → empty 200 → SWF hang); grantRewardItem was
    called before its definition (fatal swallowed by error_reporting(0) → empty
    body); wrong response contract (now emits res=1&completedAchievements=2 as the
    SWF expects, not error=0&petID=…); achievement INSERT targeted a non-existent
    `userachievements` table (now guarded + writes to achievementscompleted);
    mulch was deducted before the INSERT succeeded (now deducted only after
    confirmed insert, so a failed adoption never eats mulch).
  • Verified live: BUY returns res=1&completedAchievements=2, pets row inserted,
    mulch deducted, achievement recorded. Live in-client adoption + persistent
    pet confirmed by developer screenshot.
  • 1-pet-per-weevil cap is ACTIVE by design.
  [2026-08-18 pass — finishing touches, unparked by developer]
  • FIXED bowl/bed reward bug: SWF sends bowlItemTypeId as a bowl-TYPE code
    (20,33–40) or the raw item (2625–2633); buy.php was granting that raw value
    as the weevilitems itemId (wrong). Added resolvePetBowlBed() mapping
    type-code → correct per-colour bowl item (2625–2633) AND the matching bed
    (2855–2863). Verified all 9 colours + raw-item form resolve correctly.
  • ADDED pet skill-tree seeding: at adoption, inserts the 16 default skills
    (skillIDs 1–8,10–17; skillID 9 intentionally absent) into petacquiredskills
    with the exact obedience/skillLevel the petBuilder SWF expects
    (1:20/0 … 3–5:100/0 … 6:20/2 … 10:20/19 … 15:30/5,16:30/10,17:30/1).
    getPetSkills() now returns responseCode=1 with the correct skills payload
    (was null → bad response before seeding). Verified via temp script (scrubbed).
  • Rick test account reset to level 1 / 5000 mulch / 25 dosh / 0 pets / 0 skills
    / 0 adopt-achievement for a clean manual retest.
  • buy.php linted clean; verifier temp files removed.

OPEN / PARKED (deferred, not in scope of this pass):
  • Bin Pet Shop entry routing: the shop is an IN-WORLD location
    (<location id="134" name="BinPetsShop">, CTA → binPetsShop_06_01_14.swf),
    entered by walking into it — NOT the department-store UI. Currently
    BinMart/Nestco route to the same SWF and don't open BinPetsShop. Fix = wire
    the BinPetsShop location CTA to load the binPetsShop SWF (the asset exists
    locally). This is what makes in-client adoption reachable.
  • Featured-tab-only shop population: only the Featured tab of the shop returns
    items; other tabs (e.g. category-filtered) return empty. Likely the
    getShopItems tag/category mapping. Separate from Bin Pets; park.
  • Shop split suggestion (developer): make one store dosh-only, one normal —
    future, not blocking Bin Pets.
  • Bin Pet species names: need the real species/types from the SWF (or
    developer) to replace the placeholder personality-type names.
  • updatePetStats.php returns responseCode=999 on an invalid/missing client
    hash (same checkHash path as feedPet.php). Core adoption works; pet-stat
    live sync is parked for a later pass (developer deferred 2026-08-18).

PARKED THIS SESSION (2026-08-18):
  • "You were invited and received rewards!" nest popup — client-side Flash
    SharedObject overlay, NOT server-driven (no server flag: users.invitedBy is
    the only invite column, check-invite-status returns hasInvite=0, no
    login-message table). Cannot be suppressed from PHP without reverse-eng the
    SWF. Repurpose into the referral/invite system below.

## 7. Referral / Invite system (PARKED — future website redesign)

Developer decision (2026-08-18): the referral/invite system should live on the
WEBSITE, which is due for a redesign in future. Therefore PARK this on the
Roadmap and do NOT build it inside the game client / Flash SWFs now.

Context:
  • The nest "You were invited and received rewards!" popup is the legacy
    invite-reward UI. When the website redesign lands, build a proper
    referral flow there (invite codes → users.invitedBy, reward grants),
    and gate/remove the Flash popup as part of that work.
  • `users.invitedBy` column already exists (text, nullable) — reuse it.
  • No server-side invite/login-message scaffolding exists yet; build fresh
    on the website side during the redesign.

## 8. BinMart/Nestco shop split + bulk purchasing (SHOP-CURRENCY-SPLIT DONE 2026-08-27 — bulk purchasing still PARKED)

SERVER-SIDE CURRENCY SPLIT COMPLETE 2026-08-27 (see "BUILD LOG" below). Bulk/multi-qty
purchase remains PARKED (separate SWF work; not started).

PARKED 2026-08-18 after two investigation gates (no code written; both SWF copies
decompressed read-only in C:\Users\pc\shop_swf_investigation\, originals hashed).

### BUILD LOG — server-side currency split (2026-08-27)
- Changed `getNestShopItems()` + `getPopularNestShopItems()` in
  `game-full/essential/internal.php` to split the two department stores by currency.
- IMPORTANT CORRECTION to the original "KEY FINDINGS": the SWFs ALREADY self-identify —
  `Binmart.setStoreName` sets literal `"binmart"`, `Nestco.setStoreName` sets `"nestco"`
  (confirmed by disassembling both SWFs and reading the deployed binaries). The ROADMAP's
  old "open item: which shopType the SWF POSTs is INFERRED" is RESOLVED: it is explicit.
- The DB has NO `binmart` shopType value — every department-store item is
  `shopType='nestco'`, distinguished by `currency` (dosh/mulch). So the filter maps
  `storeName -> (shopType='nestco', currency)`: binmart=>dosh, nestco=>mulch.
  (First attempt wrongly bound `shopType=$storeName`; since no row has shopType='binmart'
  that returned ZERO items — caught by ad-hoc simulation against the real itemtype dump.)
- binPetShop path is exempt (returns all its stock).
- NO SWF edit required for the split (SWFs already send the right storeName). NO DB schema
  change. `buyItem.php` already branches on currency, so purchase logic is untouched.
- Ad-hoc verification (no PHP/MySQL runtime here): simulated the exact WHERE predicate
  against the real 1,353-row `bwps.sql` itemtype dump -> Binmart(dosh)=462, Nestco(mulch)=706,
  old mixed=1,168, lossless (462+706=1,168). Binmart cat-9 returns dosh items only.
- Full runtime test still pending (no PHP/MySQL on this machine — known blocker).
- Catalog endpoint the SWFs actually call is `getStockItemsForTag.php` (NOT the stale
  `getShopItems.php` the old findings named). `getStockItemsForLevel.php` is referenced by
  the SWF but does NOT exist in the repo — that's why non-default tabs return empty.

INTENT:
  • BinMart → Dosh-only store. Nestco → Mulch-only store (split by currency).
  • Add legitimate bulk/multi-quantity purchase (one validated transaction, qty=N)
    with a running total in the SWF, server-authoritative price/currency/qty.

KEY FINDINGS (verified, do not re-derive):
  • Catalog endpoint: POST php2/shop/departmentStore/getShopItems.php (params
    tag, shopType, hash, timer). Backend getNestShopItems() filters
    itemtype WHERE category=? AND shopType=?... .
  • Purchase endpoint (LIVE path, both stores): POST php2/shop/departmentStore/buyItem.php
    (params itemTypeID, userIDX, colour; NO qty today; cookie-session only, no hash).
    Parallel buyDoshShopItem.php exists but is NOT the live path.
  • itemtype has shopType (values: nestco, binPetShop, popUpShop) + currency
    (mulch/dosh). nestco currently holds 935 dosh + 1371 mulch (MIXED — split not
    enforced). NO binmart shopType exists. Recommended split = server-side currency
    filter on top of existing shopType; do NOT add a binmart DB category.
  • SWF classes: departmentStores.Binmart / .Nestco; DepartmentStoreColours
    (colour picker, reuse), DepartmentStoreBuyResponse (response handler),
    DepartmentStoreItemsPanel/Paging. Currency+price display = itemcurrencymc.
  • NO existing shop quantity UI — the `quantity` token in both SWFs is Swrve
    analytics (swrve_payload), not purchasing. Quantity stepper must be ADDED.
  • rateLimit() exists in essential/backbone.php but has ZERO callers in shop
    endpoints — there is currently NO active purchase limiter. Bulk does not
    "bypass" one; wire rateLimit('shop_buy',N,W) per REQUEST (1 hit per bulk buy).
  • DB layer = mysqli (new mysqli + prepare/execute). Supports begin_transaction/
    commit/rollback — bulk can be atomic (deduct total ONCE after inserts, mirror
    the Bin Pets fix). No new DB library needed.
  • weevilitems = one row per item instance (no qty column) → qty=N = N rows.

OPEN ITEM: literal shopType value each SWF POSTs is INFERRED = nestco (no binmart
  shopType exists; stores work). Confirm via live getShopItems.php POST body
  (Electron DevTools Network tab) before implementation.

PROPOSED (for approval later):
  • Max qty proposal = 50 (under 800 ownership cap; trivial MySQL; abuse-shaped
    beyond). Final value to be approved separately.
  • Files to modify when unparked: php2/shop/departmentStore/getShopItems.php,
    essential/internal.php (getNestShopItems + shared purchaseItem()),
    php2/shop/departmentStore/buyItem.php (+ optionally buyDoshShopItem.php for
    parity), and the two SWFs (binmart_15_01_14.swf, nestco_15_01_14.swf) for the
    qty stepper + running total. NO DB schema change. Bin Pets stays PARKED/untouched.

## Next-step suggestions (unassigned)

1. Commit the four checkpoint passes as scoped commits (see git note below).
2. Resolve `main.swf` audit (section 4) before any client cleanup.
3. Pick the first roadmap item to actually build (recommend: module splitting
   OR room-events migration, whichever the developer supplies first).

### Git note
The working tree's git root is the user HOME directory, not this project
folder. Scoped `git add` of ONLY the project paths is required to avoid staging
the whole profile. Commit strategy (per-checkpoint vs single "checkpoints A–D"
commit) to be confirmed with the developer before any `git commit` runs.

---

## 9. Roadmap additions agreed 2026-08-27

These are the current agreed gameplay, world, economy and security additions for the OG private server. They were discussed after the older roadmap above and should be treated as the up-to-date feature list.

### 9.1 Automatic multi-level catch-up + Prestige 0–13

- Fix the current behaviour where enough banked XP for several levels still requires repeated nest exit/re-entry.
- One progression reconciliation should grant every legitimately earned missing level in sequence and award every corresponding trophy/reward.
- Preserve the original Level 1–80 XP curve for Prestige 0.
- Add Prestige 1 through Prestige 13; maximum prestige is 13.
- Prestige difficulty is additive, using `1 + (prestige * 0.5)`: P0=1.0x, P1=1.5x, P2=2.0x ... P13=7.5x.
- After first reaching Level 80, the visible Level 80 badge remains permanently; prestige uses a fresh internal 1–80 progression/reward cycle behind that badge.
- At the end of each prestige cycle, award that prestige's Level 80 reward before advancing to the next prestige.
- Allow a fresh set of the original level trophies to be earned once per prestige. Trophy ownership must therefore be prestige-aware while remaining idempotent inside one prestige.
- Add a `prestige` field/column in the appropriate player table (default 0), unless audit shows an existing equivalent.
- Prestige 13 completed through internal Level 80 is the progression cap; XP earning itself can continue beyond the cap.

### 9.2 Lifetime XP vs banked/progression XP

Use two linked XP concepts:

- **Lifetime XP** = total XP ever earned. It only increases, never decreases, and is the value reserved for lifetime statistics and the later leaderboard.
- **Banked/progression XP** = currently usable XP that drives the progress bar toward the next level/prestige milestone and may later be spent.
- When 500 XP has been earned and 300 banked XP is eventually spent, lifetime XP remains 500 while banked/progression XP becomes 200.
- Spending banked XP must never de-level a player, reduce an already-earned prestige, or remove trophies/rewards already earned. It only reduces progress toward the next unearned milestone.
- All legitimate XP rewards should go through one canonical award path so lifetime XP and banked XP stay consistent.
- The accounting/model can be introduced alongside progression if useful, but **the XP reward shop itself is POST-RELEASE work and must not block launch**.

**STATUS (2026-08-27, night): §9.1 + §9.2 PRE-RELEASE ACCOUNTING — DONE on `main` (commit `8c2958f4`).**
- Audit found the schema already carried the needed columns, so no schema change was required: `users.xp` (lifetime), `users.xp1`/`xp2` (cycle progress / next-level cost), `users.prestige_count`, `users.prestige_xp_base`, the `levels` 1–80 curve, and a `prestige_trophies` table. The work was wiring the logic.
- `addExperience*` now advances both lifetime `xp` and cycle `xp1`; `levelWeevil()` loops to grant every banked level in one call (catch-up), carrying overflow; at L80 it awards the prestige reward and (below the prestige-13 cap) increments `prestige_count`, snapshots `prestige_xp_base`, resets to a fresh L1 cycle with difficulty `1 + prestige*0.5`. `rewardUserTrophy()` records each award in `prestige_trophies` (prestige-aware, idempotent per prestige).
- Verified against the live DB (throwaway test weevil, cleaned up): +5M banked XP leveled 1→65 in one call carrying overflow; a larger grant drove to L80, awarded the prestige reward, and incremented `prestige_count` to 1 with a fresh L1 cycle (xp2 = 30×1.5 = 45).
- **Deferred per roadmap: §9.3 (XP reward shop + lifetime-XP leaderboard) remains POST-RELEASE, after the website redesign.** No code for it was written.

### 9.3 POST-RELEASE: XP reward shop + lifetime-XP leaderboard — after website redesign

This entire feature is deliberately deferred until **after the initial game release and after the website redesign is complete**. It should be treated as one of the final roadmap additions, not a pre-release blocker.

- Build an XP reward shop that spends **banked/progression XP**, never lifetime XP.
- Candidate rewards include coloured names, titles, badges, cosmetic effects, selected cosmetics and harmless command privileges/command packs.
- Support both permanent unlocks and temporary XP sinks where useful.
- Prestige may unlock higher reward-shop tiers, while purchases still cost banked XP.
- Never allow XP purchases to grant moderation/admin powers such as ban, kick, mute, currency modification, XP modification or staff status.
- Add/restore a leaderboard ranked by **lifetime XP** after the redesigned website is ready to present/manage it properly.
- Spending banked XP must never reduce the lifetime-XP leaderboard value or position.
- If the original/current game already contains a suitable XP leaderboard, audit/reuse it rather than building a competing system.

### 9.4 Garden seed shop population bug

- The Garden seed shop opens but its product shelves/catalogue do not populate.
- Audit the seed-shop identity, stock endpoint, PHP/database response, level/category/currency filters and client response contract.
- Restore the intended seed catalogue and verify it in the real client without disturbing BinMart/Nestco.

### 9.5 Nestco remaining catalogue bug

Current live state after the currency split:

- BinMart works as intended and populates its Dosh catalogue.
- Nestco Featured populates Mulch items, but the normal categories do not populate.
- Featured currently has duplicated entries that must be traced to the correct backend/config/client layer.
- `getBundles.php` is observed returning 404 when the Bundles tab is selected and must be recovered/implemented according to the client contract rather than stubbed.
- Use working BinMart as the control path and compare endpoint, parameters, response body and SWF parsing against Nestco before making further speculative edits.
- Final requirement: Nestco must populate its complete valid Mulch catalogue while BinMart remains Dosh-only.

### 9.6 Nest teleporter expansion

- Expand the nest teleporter's destination pool toward every valid/recoverable room/location in the game.
- Build the pool from verified room/location definitions rather than guessed IDs.
- Normal public locations can use normal weighting.
- Historical, hidden, seasonal and event-only locations should be **extremely rare** teleporter outcomes.
- Only include unusual rooms after verifying they load safely and have a safe exit; exclude broken/test/system/soft-lock rooms.
- Include the Old Bin as a supported legacy teleporter destination.
- Keep destination IDs, names, rarity weights and exclusions data-driven/documented.

### 9.7 Out-of-bounds detection using BinConfig `localDefinitions`

- Extend movement validation so normal players are checked against the authoritative room coordinate/boundary data in BinConfig `localDefinitions` wherever available.
- Do not use one generic hardcoded room rectangle when room-specific definitions exist.
- Correct an out-of-bounds player to the last known valid/safe position without unnecessary room changes.
- Avoid false positives at valid doors, transitions, ramps, event mechanics and special movement zones.
- Rooms with missing/ambiguous boundary metadata should be handled conservatively and documented.
- **Admins are exempt** from out-of-bounds enforcement for debugging/exploration; exemption must come from trusted server-side permissions, never a client flag.

### 9.8 Login-key replay/session exploit hardening — security blocker

Before wider public deployment, harden the web-login -> game-login key flow:

- Treat game login keys as short-lived, single-use credentials rather than reusable bearer values.
- Generate keys using a cryptographically secure random source with adequate entropy.
- Bind a key to the intended account and authenticated web/session context.
- Consume it atomically on the first successful game-server login; replay must fail.
- Reject expired, used, malformed, wrong-account and wrong-session keys.
- Rotate/invalidate outstanding keys on fresh login, logout and relevant session/security resets.
- Do not treat client-side packet encryption or any client-embedded secret as proof of identity; client secrets are recoverable.
- Rate-limit failed game-login attempts and log suspicious replay attempts without logging raw credentials.
- Regression-test: fresh key works once, replay fails, expiry fails, logout invalidates, and one account/session cannot use another account's key.

### 9.9 Mulchtastic implementation

- Restore/implement the Mulchtastic attraction rather than leaving it as non-functional scenery.
- Audit existing Mulchtastic SWFs, room/object definitions, scripts, endpoints, DB data and server handlers first.
- Recover the original interaction flow from preserved assets/source where possible rather than inventing behaviour.
- Keep rewards/currency/XP server-authoritative and validate any client-reported result.
- Add only the persistence/cooldowns needed by the real mechanic and protect it from replay/reward abuse.
- Verify entry/activation/result/reward end-to-end in the real client before marking complete.

### 9.10 One canonical 2014-era map + Old Bin via teleporter

- Replace the two-map arrangement with one canonical main map from the 2014 era.
- Audit available 2014-era map SWFs and choose one whose normal navigation does not depend on the currently broken Summer Fair.
- Verify every visible destination/button on the selected map before making it canonical.
- Remove/disable the redundant second-map switching path only after the selected map is proven stable.
- Preserve the Old Bin as legacy content and access it through the nest teleporter rather than a second main map.

### 9.11 Summer Fair restoration

- Summer Fair currently fails to load and should be restored as a separate task instead of blocking the main map.
- Audit its room/location definitions, SWFs, dependencies, PHP endpoints, server handlers and map hooks to identify the actual failure.
- Recover original assets/logic where available.
- Verify entry, room loading, exits, interactive objects, event mechanics and rewards in the real client.
- Only reconnect Summer Fair to the live map after the destination is stable.

### 9.12 Expanded Secret/Mystery Code system

Extend the existing code system so codes can be configured as:

- one-time per account (existing/default behaviour);
- X redemptions per account per calendar day;
- X redemptions per account per calendar month;
- X redemptions per account per calendar year;
- optionally a code-wide/global redemption cap for limited community drops.

Requirements:

- Reuse/extend the existing code tables/endpoints/reward logic rather than creating a disconnected second system.
- Store code string, enabled state, reward(s), optional start/end dates, redemption mode, per-period limit and optional global cap as data/configuration.
- Track successful redemptions by account and server timestamp.
- Use server-side calendar windows; never trust the client's clock/date.
- Validation and reward granting must be atomic so concurrent submissions cannot exceed limits or duplicate rewards.
- Preserve old one-time codes unless deliberately migrated.
- Support existing valid reward types such as Mulch, Dosh, XP and items.
- Provide clear existing-compatible failure responses for expired, disabled, exhausted and over-limit codes.
- Make codes admin-maintainable/configurable without hardcoding a new PHP path for each event.

### 9.13 Suggested order for these additions

1. Finish the current Nestco catalogue population fix without regressing BinMart.
2. Harden the login-key replay/session weakness before public release.
3. Implement automatic multi-level catch-up + Prestige 0–13 and prestige-aware trophy history; establish the lifetime/banked XP accounting needed by progression.
4. Fix the Garden seed shop catalogue.
5. Select/verify the canonical 2014-era main map.
6. Expand the nest teleporter, including Old Bin and ultra-rare safe event/hidden destinations.
7. Restore Summer Fair independently.
8. Add BinConfig `localDefinitions`-based out-of-bounds enforcement for non-admins.
9. Restore/implement Mulchtastic.
10. Expand Secret/Mystery Code redemption modes.
11. Finish release-critical stability/security work and ship the initial release.
12. **POST-RELEASE:** complete the website redesign.
13. **LAST:** after the website redesign, implement the XP reward shop and lifetime-XP leaderboard.

---

## 10. Website redesign — current state (2026-08-29)

Branch: **`website-redesign`** (tip `082d329d`, pushed to origin). Live copy: `C:\xampp\htdocs`
(root tree; `game-full/` in-repo is legacy and not served). Not merged to `main`, not on VPS.

Status legend: **DONE** = built + headless-QA-verified · **NEEDS TEST** = built, awaiting the
owner's manual run in the real Electron/PepperFlash client.

### 10.1 What is built
- **Design system (DONE)** — centred Bin Weevils shell, outdoor garden background, green
  nav/header, Burbank Small brand font, authentic/recovered artwork only (no AI/fabricated
  graphics). Page-specific compositions rather than identical cards everywhere.
- **Public homepage (DONE)** — Bin Bulletin + live server status, Welcome area, Returning Player
  login, Create a Weevil, feature CTAs (Enter the Bin / xat Chat / My Weevil), advert placements,
  authentic character art (`rigg.png`).
- **Logged-in homepage (DONE)** — personal "Welcome back, <user>!" hero with the account's
  **rendered Weevil** (reused `weevil-creator` runtime; account `def` authoritative) + level/prestige
  + "XP to next level" + Play/My Weevil. Right "Your Weevil" panel: rendered Weevil, name (equipped
  colour), title badge, Mulch/Dosh/Prestige/Next-level. Server status intentionally NOT duplicated
  (the status strip owns it). Deep XP stats live on My Weevil.
- **Play page (DONE)** — page is HEADER / GAME / FOOTER only. Removed the duplicated head block
  (eyebrow, "Enter the Bin", "Logged in as…", Desktop client / My Weevil / Fullscreen CTAs,
  renderer-dev note). Game embeds at native **940×653** in a snug dark frame, no blue letterbox.
  Discreet **⛶** fullscreen icon at the game frame's **bottom-right** corner (no giant CTA).
- **Fullscreen (DONE, real-client visual confirm still recommended)** — ROOT CAUSE of the earlier
  break: the fullscreen viewport had `height:auto` from `aspect-ratio` only, so the Flash `<object>`
  (`height:100%`) collapsed to a postage-stamp inside the black wrapper. FIX: the viewport now gets an
  EXPLICIT `height: min(100vh, 100vw*653/940)` plus `width: min(100vw, 100vh*940/653)` and the object
  stays `position:absolute; inset:0`, so it fills the box. Wrapper 100vw×100vh, centred, dark letterbox,
  never stretched/squashed. The ⛶ control toggles (click to enter AND exit); Escape also exits; returning
  restores the embedded layout. No SWF change.
- **My Weevil (DONE)** — player profile / progression page: large rendered Weevil, username, level,
  prestige, Mulch/Dosh (authentic `mulch.png`/`dosh.png` icons), Lifetime/Banked XP, green progress
  bar + next threshold, compact XP Rewards (chips: name colour / title / profile background + Browse),
  preferences (reduce-motion / compact-layout), change password. Raw definition hidden behind an
  "Advanced / Appearance data" `<details>` disclosure (data + Copy intact).
- **Weevil renderer (DONE)** — `assets/js/site-weevil-renderer.js` mounts every `[data-weevil-render]`
  from the saved definition; hat fields zeroed only for the website wrapper (game/Flash untouched).
  **Poses fixed this pass:** camera yaw was `302` (side/back) for every render → changed to `0` (front-facing)
  for the hero, account panel and My Weevil. The header avatar uses `data-weevil-crop="head"` + a CSS zoom
  into the head region, giving a face/head-shot instead of a squeezed full body.
- **Advert system (DONE, verified)** — creatives grouped by compatible format: leaderboard/banner
  (top of homepage + page banners on Create a Weevil & Download), MPU/rectangle (home rectangle),
  portrait/skyscraper (desktop side gutters, 300×600, exact creative match). Fixed slot dims +
  `object-fit: contain`, no layout shift, empty slots hidden. **Side rails are hidden below 1723px by design**
  (visible at ≥1724px, e.g. 1920) so they never overlap the 1100px shell. All creative assets serve HTTP 200
  at the slot's native dimensions. The Download page's old orange "Important" panel was replaced with a
  Sponsor banner slot.
- **Homepage dev-status strip (DONE)** — the "Game server / Weevils online / Build" diagnostic strip was
  removed. Bin Bulletin kept. The online count ("● N Weevils online") is now a small header element
  (logged-in and logged-out) fed by the existing `/site/server-status.php` poller. No fabricated counts.
- **Public copy audit (DONE)** — developer-facing text removed across all public pages (no "Development
  build", "restored classic client", "canonical download location", "OG private-server client", internal
  rate-limiting/architecture notes). Footer keeps the intentional "Fan-made preservation project" disclaimer.

### 10.2 main.swf background bitmap swap (DONE, NEEDS FULL CLIENT TEST)
- **Live SWF:** `mainDEV663.swf` (htdocs root; `site/config.php['flash_movie']`).
- **JPEXS/FFDec target:** `DefineBitsJPEG2 (characterId 111)`, native bitmap **1245×840**.
- **Replacement:** authentic recovered Bin Weevils Garden background (`assets/images/background.png`,
  resized to 1245×840 so no movie-clip transforms change). Method: `ffdec -replace` on tag 111.
- **Backup (untouched):** `C:\xampp\htdocs\main.pre-background-swap-20260829-164658.swf`
  (SHA-256 `62fe3ac2001f60ca1c2b02492a578eb24f0f8df9904cccaf3c9aea0a7263069f`, 775,949 bytes).
- **Edited SWF SHA-256:** `33176e9fe9e497f2b7546f7e0a2c4b57dd9eb4f46bad64878821c6330a5b84b8`
  (649,017 bytes). Tag count unchanged (1696); only the bitmap payload differs.
- **Commit:** `6fb021e8` on `website-redesign`.
- **Transparency:** NOT done. Future optional experiment only (see §10.4).

### 10.3 xat / Community Chat — NEXT / NOT IMPLEMENTED
- `/community/` has the prepared website design shell; the actual xat room is **not** embedded yet.
- Next phase: configure/embed the intended xat room, fit it to the website design, preserve
  login/account nav, test responsive behaviour.

### 10.4 Flash transparency — OPTIONAL EXPERIMENT / NOT IMPLEMENTED
- Possible future: blend the Flash game into the site background (alpha-capable bitmap/tag,
  transparent stage, `wmode="transparent"`, check extra opaque layers, PepperFlash compositing).
- Must be done on a **COPY** of the known-good `mainDEV663.swf`; the safe bitmap swap above stays
  the baseline. Not a pre-release requirement.

### 10.5 Account ↔ xat cosmetic identity — ROADMAP / FUTURE
The Bin Weevils **account stays authoritative** for equipped public identity. xat/community should
later reflect account cosmetics where technically possible (equipped name colour, title, level,
prestige badge). Do **not** build a separate xat cosmetics economy. Flow:
`Bin Weevils account → equipped identity → website → My Weevil → community/xat → other public surfaces`.

### 10.6 Tomorrow's manual test checklist (start from current committed build)
1. Homepage (logged out) · 2. adverts render · 3. adverts rotate w/o layout shift · 4. Create a
Weevil · 5. account registration · 6. existing-account login · 7. Remember Weevil Name · 8. auth
header · 9. logged-in homepage · 10. account Weevil renders · 11. My Weevil · 12. XP/currencies
correct · 13. Settings · 14. Play page · 15. game viewport sizing · 16. Fullscreen button · 17. exit
fullscreen · 18. server selector · 19. Mulch server · 20. enter game · 21. nest/home room · 22. game
controls · 23. logout · 24. login again · 25. responsive/narrow check.

### 10.7 Next development order
A. Manual end-to-end website/game test → B. fix only genuine failures → C. final Play/embed
adjust if real testing requires → D. xat Community Chat → E. test xat → F. account↔xat cosmetic
identity → G. optional Flash transparency on a COPY only. Transparency stays after stable
functionality.
