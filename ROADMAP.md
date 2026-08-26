# ROADMAP.md — Bin Weevils Private Server

Generated after all four checkpoints (A/B/C/D) were implemented and manually
confirmed by the developer. This is a FUTURE-WORK LIST. Nothing here has been
started. Each item carries the reason it was deferred during the backend
bring-up pass.

> **STATUS UPDATE (2026-08-26) — read this first.**
> The project is now preserved on GitHub as a known-good **recovery baseline**
> (`main`, baseline commit `e7cf4d1`). Work is done on the branch
> `feature/room-events-mushrooms`. The broken `Project Binweevils\Binweevils-main (1)`
> working copy referenced below is DEAD — do not use it. See `GITHUB-HANDOFF.md`.
>
> Completed since this roadmap was written:
> - **Room events (§2): DONE** — Flum's Fountain (282), Figg's Cafe (287),
>   Dosh's Palace (265) re-added on the feature branch. Caveat: statically
>   verified only, NOT runtime-tested (no Node/PHP/MySQL runtime available).
> - **Stress-test dev instrumentation removed** — "Stress Test" / "Stress Walk
>   Test" / "Stress Move" branches + the hard-coded `roomchange_debug.log` write
>   removed from `server/BinWeevils.js`.
> - **Mushroom event DB re-added** — `mushrooms` + `claimedmushrooms` tables in
>   `bwps.sql`, 5 helper functions in `internal.php`, `collect-mushroom.php`
>   endpoint. NOT runtime-tested.
>
> Still OPEN (not done): §8 shop split (Nestco=Mulch / BinMart=Dosh), Dosh
> furniture thumbnail fix, launcher `.bat` scripts, full runtime verification,
> and everything in §1/§3/§4/§5/§6/§7.

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

## 2. Room events — **DONE (2026-08-26, on `feature/room-events-mushrooms`)**

- ~~The developer has a SEPARATE room-event implementation to supply later.
  Do NOT write room-event logic now.~~ **Supplied and re-added.** Migrated into
  `BinWeevils.js` dispatch alongside the existing `2#5` (roomEvent) handler, covering
  Flum's Fountain (282), Figg's Cafe (287), Dosh's Palace (265); `becomeWaiter`/
  `isWaiter` + joinOK tray/plate state in `Weevil.js`; mushroom DB/endpoint on the
  same branch. **Caveat: statically verified only — not yet runtime-tested.**

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

## 8. BinMart/Nestco shop split + bulk purchasing (PARKED — investigation done, build not started)

PARKED 2026-08-18 after two investigation gates (no code written; both SWF copies
decompressed read-only in C:\Users\pc\shop_swf_investigation\, originals hashed).

INTENT:
  • BinMart → Dosh-only store. Nestco → Mulch-only store (split by currency).
  • Add legitimate bulk/multi-quantity purchase (one validated transaction, qty=N)
    with a running total in the SWF, server-authoritative price/currency/qty.

KEY FINDINGS (verified, do not re-derive):
  • Catalog endpoint: POST php2/shop/departmentStore/getShopItems.php (params
    tag, shopType, hash, timer). Backend getNestShopItems() filters
    itemtype WHERE category=? AND shopType=?.
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
