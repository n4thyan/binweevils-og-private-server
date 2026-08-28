# ROADMAP.md — Bin Weevils Private Server

Generated after all four checkpoints (A/B/C/D) were implemented and manually
confirmed by the developer. This is a FUTURE-WORK LIST. Nothing here has been
started. Each item carries the reason it was deferred during the backend
bring-up pass.

> **STATUS UPDATE (2026-08-27, night) — read this first.**
> The project is preserved on GitHub on **`main`** (tip `929b4eeb`). The earlier
> `feature/room-events-mushrooms` branch, plus `feature/nestco-catalogue-population`
> and `fix/live-server-drift-sync`, have all been **folded into `main` and the
> redundant branches deleted** for tidiness — `main` is now the single source of
> truth and already contains every file those branches contributed. The broken
> `Project Binweevils\Binweevils-main (1)` working copy referenced below is DEAD —
> do not use it. See `GITHUB-HANDOFF.md`.
>
> Completed since the 2026-08-26 baseline:
> - **Room events (§2): DONE** — Flum's Fountain (282), Figg's Cafe (287),
>   Dosh's Palace (265) re-added and **server-side deployment/dispatch/DB verified
>   at runtime** (live Node on :9339 loads from `server/`, `roomids.txt` loads the
>   three rooms, handlers wire to the `2#5` packet, mushroom DB provisioned).
>   Final in-client visual/gameplay test still PENDING (needs eyes in the client).
> - **Garden seed shop (§9.4): FIXED on `main`** (commit `1b948caa`) — restored the
>   two missing `usort` comparator classes; `/gardenshop/fetch` now returns valid
>   XML (24 items + 75 seeds). Visual client confirm still PENDING.
> - **XP accounting (§9.1/§9.2): DONE on `main`** (commit `8c2958f4`) — multi-level
>   catch-up, Prestige 0–13 cycle, prestige-aware trophies. §9.3 reward shop +
>   leaderboard deliberately DEFERRED (post-release, after website redesign).
> - Shop currency split (§8) and Nestco/BinMart catalogue work shipped on `main`.
>
> Still OPEN (not done): §9.3 XP reward shop + leaderboard, §9.8 login-key/hash
> design (blocked — needs the auth design chat), Nestco catalogue SWF, Bundles/
> Showroom UI lock SWF, `loungue` tag data fix, Bin Pets species-name placeholders,
> referral system, structural refactors (§1), and client visual confirms above.
>
> NOTE: the working tree is checked out on the old local `feature/room-events-
> mushrooms` label, but its content is fully on `main`; `git status` appears dirty
> because of that — it is not unsaved work. Edit against `game-full/` and commit to
> `main` via plumbing (LFS checkout hangs on this clone).

Working copy: `C:\Users\pc\Desktop\Project Binweevils\Binweevils-main (1)\Binweevils-main\`
Source baseline: KnowYourKnot/Binweevilsworks, used 1:1 (architecture preserved).
Two pristine reference copies + three ZIP archives remain UNTOUCHED.

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

**2026-08-28 morning harden (verified):** The Prestige-boundary catch-up was
audited end-to-end against the live DB. A regression was found: `levelWeevil()`
zeroed `xp1` on the Prestige transition, destroying banked overflow above the
consumed Level-80 threshold. Fixed in `fix(xp)` commit: prestige advance now
pays only the L80 threshold (`xp1 = xp1 - xp2`) and keeps remaining banked XP
to continue the new Prestige's cycle in the same call; the P13 L80 cap clamps
(`xp1 = xp1 - xp2`) without advancing or re-awarding. Verified Cases A–F on
throwaway weevils (cleaned up): ordinary catch-up, exact P-boundary, overflow-
across-boundary (overflow preserved), full duplicate-trophy set per Prestige
(L10 physical copies = 2 across P0/P1, idempotent on re-run), large cross-
boundary grant, and P13 cap stability. `prestige_trophies` UNIQUE(user,prestige,level)
enforces per-Prestige idempotency while allowing a fresh physical set each
Prestige.


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
