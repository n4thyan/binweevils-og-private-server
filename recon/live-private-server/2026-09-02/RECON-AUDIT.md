# Live private-server recon — 2 September 2026

## CAPTURE SUMMARY

- Source: network capture from another live Bin Weevils private server
- Date: 2026-09-02
- Host observed: `play.binweevils.app`, `sfs.binweevils.app`, `web.binweevils.app`, `cdn.binweevils.app`
- Raw files: stored locally untracked; only sanitized derivatives are committed below
- Provenance: this is OBSERVED behaviour from a third-party server, not canonical Bin Weevils data

## EVIDENCE HIERARCHY

For this reassessment, evidence is weighted:

A. **Our existing implementation** (`game-full/php2`, `game-full/php`, database schema)
B. **Original recovered client** (core40 AS decompilation, SWF corpus, `docs/CORE-ENDPOINT-AUDIT-2026-09-02.md`, temp audit artifacts)
C. **Live private-server capture** (this HAR/SFS)
D. **New SWF assets** (downloadable from observed URLs)

Missing third-party response bodies from C are **not** automatic blockers if A+B+D resolve the feature.

## HAR STATISTICS

- Total entries: 911
- GET: 642
- POST: 268
- Unique hosts: 11
- Unique paths: 560
- SWF requests: 459
- XML requests: 29
- PHP requests: 119
- Image requests: 75
- JSON responses: 16
- Entries with embedded response body: 62
- Entries without embedded response body: 849

## SFS/WEBSOCKET STATISTICS

- Total packets: 3342 parsed (3346 raw)
- SENT: 1796
- RECV: 1546
- Unparsed: 4
- WebSocket endpoint: `wss://sfs.binweevils.app` (encrypted XML) and `wss://web.binweevils.app` (JSON)
- Login observed with username/nick, not email
- Room list returned ~190 room IDs including dedicated Bin Pets rooms
- XT RECV commands observed: `2#3`, `2#1`, `2#5`, `12#2`, `2#2`, `1#2`, `2#6`, `2#11`, `6#4`, `5#3`, `5#8`, `5#9`

## EVIDENCE REASSESSMENT AGAINST EXISTING PROJECT

### LEVEL STAR COLOUR SYSTEM

**OUR CURRENT SUPPORT:** None. `php2/weevil/setLevelColour.php` does not exist in our tree. No colour fields found in player schema audit.

**LIVE EVIDENCE ADDED:** Request contract proven: `POST /php2/weevil/setLevelColour.php` with `userIDX,level,timer,hash` (PHP2 secure, alphabetized). HAR request captured. Response body not captured.

**ORIGINAL CLIENT EVIDENCE:** The original core40 AS audit (`core-contracts.md`) does **not** list `setLevelColour.php`. The original recovered SWF corpus does not contain this endpoint. This suggests the level colour feature was added in a **later client build** (likely post-2016 or as a custom feature).

**NEW ASSETS/SWFS:** Observed SWF loads: `/assetsbackdrops/glowingStars.swf`, `/assetsbackdrops/level0.swf` through `/assetsbackdrops/level90.swf`. These are high-value assets. Download from `cdn.binweevils.app` returned redirect to HTML, so direct download is not currently possible.

**WHAT IS ACTUALLY MISSING:**
- Server endpoint implementation
- Response contract (from SWF decompilation)
- Colour ID/name list and unlock thresholds (from SWF or catalogue)
- Persistence column in player/weevil table
- How current colour is read back (likely from `getData` response)

**CAN IMPLEMENT WITHOUT LIVE RESPONSE?** Partial. Request contract is proven. Response contract can be recovered by decompiling `backdropUI_230425b.swf` and/or `glowingStars.swf` if downloaded. Without those, the exact success response shape is unknown.

**NEXT ACTION:** Attempt SWF download via alternate CDN/path. If downloadable, decompile `backdropUI_230425b.swf` to recover colour IDs, level thresholds, response field names, and UI behaviour.

---

### PLAYER-CARD BACKDROP SHOP

**OUR CURRENT SUPPORT:** None. No `php2/backdrops/*` routes exist in our tree. No backdrop cosmetic persistence found.

**LIVE EVIDENCE ADDED:** Three catalogue endpoints proven:
- `GET /php2/backdrops/getOwnedBackdrops.php`
- `GET /php2/backdrops/getShopItems.php`
- `GET /php2/backdrops/getUnlockableBackdrops.php`
- UI: `/externalUIs/shops/backdropUI_230425b.swf`

Response bodies not captured.

**ORIGINAL CLIENT EVIDENCE:** The original core40 audit does **not** list any backdrop shop endpoints. This is a newer/custom feature.

**NEW ASSETS/SWFS:** ~80 backdrop SWFs in `assetsbackdrops/`. UI SWF `backdropUI_230425b.swf` is the key decompilation target.

**WHAT IS ACTUALLY MISSING:**
- Server endpoint implementations
- Response contract for catalogue endpoints
- Purchase route and parameters
- Equip/select route and parameters
- Currency/pricing/requirements (not in client code)
- Persistence schema
- How equipped backdrop appears in public profile

**CAN IMPLEMENT WITHOUT LIVE RESPONSE?** Partial. Request contracts are trivial (GETs). Response contracts can be recovered from SWF decompilation if `backdropUI_230425b.swf` is available.

**NEXT ACTION:** Decompile `backdropUI_230425b.swf` to recover catalogue structure and field names. Implement catalogue endpoints as reads. Leave purchase/equip as blockers until pricing/auth is resolved.

---

### BIN PETS

**OUR CURRENT SUPPORT:** Substantial existing implementation:
- `php2/pets/getUserPets.php`
- `php2/pets/getPetSkills.php`
- `php2/pets/updatePetStats.php`
- `php2/pets/adoptPet.php`
- `php2/pets/buy.php`
- `php2/pets/feedPet.php`
- `php2/pets/validate-pet-name.php`
- Core40 AS proves: `getAcquiredJugglingTricks`, `updateJugglingTrick`, `updatePetSkill`, `getPetProfile`
- Database tables exist for pet state/skills

**LIVE EVIDENCE ADDED:** Confirms `getUserPets.php` exists and returns `{"pets":[],"responseCode":1}` for pet-less account. Room list confirms dedicated Bin Pets rooms (`binPetChanger`, `bppLandingRight`, `bppLandingLeft`, `BinPetsShop`, `BinPetsShop2`, `bppGym`). Pet furniture assets loaded.

**ORIGINAL CLIENT EVIDENCE:** Core40 AS contains complete pet contracts for `getUserPets`, `getPetSkills`, `getAcquiredJugglingTricks`, `updateJugglingTrick`, `updatePetSkill`, `updatePetStats`, `getPetProfile`, `adoptPet`, `feedPet`.

**NEW ASSETS/SWFS:** `weevilPet_assets_210225.swf`, `weevilPet_assets_200826.swf`, `bpp_changeroverlay.swf`, `bpp_studiooverlay.swf`, `binpetparadise_gym_21_05_14.swf`. These are supplementary to our existing archive.

**WHAT IS ACTUALLY MISSING:**
- Comparison against our existing code needs dedicated review after Bin Pets package arrives
- Some pet endpoints in the original AS (e.g., `getPetProfile`) may not have server implementations yet

**CAN IMPLEMENT WITHOUT LIVE RESPONSE?** Existing implementation covers most. New package will provide full integration.

**NEXT ACTION:** Defer to incoming Bin Pets package. Our existing server-side work is sufficient baseline.

---

### MULCHTASTIC / BRAIN STRAIN

**OUR CURRENT SUPPORT:** Partial. No `game/brain-info` or `game/brain-submit` routes in our server.

**LIVE EVIDENCE ADDED:**
- `GET /game/brain-info` — bootstrap request, no body captured
- `POST /game/brain-submit` with `score,levels,st,hash` — `levels` is comma/pipe serialized: `1|0,3|0,4|0...`
- `levels` format: `%2C1%7C0%2C3%7C0...` = `,1|0,3|0,4|0...` = levelID|passed for each level

**ORIGINAL CLIENT EVIDENCE:** Core40 AS decompilation contains Brain Strain references (`Brain_NPC.as`, `Brain_weevil_NPC.as`, `Brain.as`). The original client proves the score submission format and the existence of level/mode data.

**NEW ASSETS/SWFS:** `brainstrain_080225a.swf`, `braintrainingconfig_28_09_11.xml`, many question SWFs under `externaluis/brainstrainquestions/`.

**WHAT IS ACTUALLY MISSING:**
- Server route implementations for `brain-info` and `brain-submit`
- Response contract for reward fields (`mulchEarned`, `xpEarned`, `mulch`, `xp`, `modes`, `ave`, `high`, `levels`)
- Server authority for reward calculation

**CAN IMPLEMENT WITHOUT LIVE RESPONSE?** Request contract is proven. Response reward fields can be recovered from SWF decompilation of `brainstrain_080225a.swf`. Server authority (how rewards are calculated) must be inferred from client code or existing backend patterns.

**NEXT ACTION:** Decompile `brainstrain_080225a.swf` to recover exact response field names consumed by the client. Implement route stubs that pass through the response. Mark reward calculation as blocked until evidence is found.

---

### LOTTO

**OUR CURRENT SUPPORT:** Partial. `php/getMyLottoTicketsAndDrawDate.php` exists. Missing: `getUncashedTickets.php`, `getJackpotSize.php`, `addLottoTicket.php`, `getPastLottoDraws.php`, `getLottoDrawWinners.php`, `cashInTickets.php`.

**LIVE EVIDENCE ADDED:** Confirmed `getMyLottoTicketsAndDrawDate.php` returns `responseCode=1&nextDraw=2026-09-04 17:00:00&drawID=420&gotTicket=0&tickets=&b=r`. Also observed `getUncashedTickets.php`, `getJackpotSize.php`, `addLottoTicket.php` (no bodies).

**ORIGINAL CLIENT EVIDENCE:** Core40 AS contains `LottoData.as`, `LottoResults.as`, `LottoTicket.as`. Original client proves the existence of ticket/draw/result data structures.

**NEW ASSETS/SWFS:** `lotto_200423.swf`.

**WHAT IS ACTUALLY MISSING:**
- Server implementations for uncached tickets, jackpot, add ticket, past draws, winners, cash-in
- Response bodies for all endpoints
- Prize/reward formula

**CAN IMPLEMENT WITHOUT LIVE RESPONSE?** Request contracts are simple GETs/POSTs. Response contracts can be recovered from `lotto_200423.swf` decompilation. Prize logic remains blocked.

**NEXT ACTION:** Decompile `lotto_200423.swf` for response schema. Implement read endpoints as catalogue. Leave cash-in/reward as blocked.

---

### LOYALTY

**OUR CURRENT SUPPORT:** Files exist: `php2/loyalty/getProgress.php`, `php2/loyalty/getStamp.php`, `php2/loyalty/getVouchers.php`. Implementation status unknown without code review.

**LIVE EVIDENCE ADDED:** Requests confirmed: `getProgress.php`, `getStamp.php`, `getVouchers.php`. No bodies captured. SWF: `loyaltyCard_10_01_25.swf`, `loyaltyCards/loyaltyCard1.swf`.

**ORIGINAL CLIENT EVIDENCE:** Not found in core40 AS audit. This may be a newer feature.

**WHAT IS ACTUALLY MISSING:** Response bodies, final reward endpoint, implementation verification.

**CAN IMPLEMENT WITHOUT LIVE RESPONSE?** If local files are complete stubs, they need response contracts from SWF decompilation.

**NEXT ACTION:** Review existing local loyalty files. Decompile `loyaltyCard_10_01_25.swf` if response shapes are missing.

---

### HAGGLE HUT

**OUR CURRENT SUPPORT:** `php2/shop/getHaggleItems2.php`, `php2/shop/getHagglePrices.php`, `php2/shop/sellHaggleItems.php` all exist.

**LIVE EVIDENCE ADDED:** Confirms routes. Request contract for `getHagglePrices.php` observed: `items,seeds,gardenItems,timer,hash`. No bodies.

**ORIGINAL CLIENT EVIDENCE:** Core40 AS does not explicitly list Haggle Hut endpoints in the static contract inventory, but our local files exist.

**WHAT IS ACTUALLY MISSING:** Response bodies for item list, price list, sell response.

**CAN IMPLEMENT WITHOUT LIVE RESPONSE?** Likely yes. Our local files may already be functional; response contracts may be recoverable from `HaggleHut_04_08_25.swf` and `haggleHutOverlay_040825b.swf`.

**NEXT ACTION:** Verify local implementations against client. Decompile UI SWFs for response field names.

---

### MISSIONS / QUESTS

**OUR CURRENT SUPPORT:** `php2/mission/getMissionList.php` exists. Missing: `getRoomHelp.php`, `buyHelp.php`, `buyMission.php`.

**LIVE EVIDENCE ADDED:** No mission HTTP traffic observed in this session (account may have no active missions).

**ORIGINAL CLIENT EVIDENCE:** Core40 AS proves:
- `php2/mission/getRoomHelp.php`: `POST idx,roomName` — `responseCode` read
- `php2/mission/buyHelp.php`: `POST idx,helpId` — `responseCode==1` consumes `newDosh`
- `php2/mission/buyMission.php`: `POST idx,questId,taskId,voucher(-1)` — `responseCode==1` marks task, consumes `dosh`

**WHAT IS ACTUALLY MISSING:**
- Server implementations for `getRoomHelp`, `buyHelp`, `buyMission`
- Mission data schema (tasks, rewards)

**CAN IMPLEMENT WITHOUT LIVE RESPONSE?** Request contracts are proven from AS. Response contracts are partial (mostly `responseCode` guards). Can implement stubs that validate inputs and return `responseCode=1` for valid requests.

**NEXT ACTION:** Implement stubs for `getRoomHelp`, `buyHelp`, `buyMission` with proven request params and `responseCode` contract.

---

### REWARDS / CODES

**OUR CURRENT SUPPORT:** `php2/rewards/getCodes.php`, `php2/rewards/submitCodes.php` exist.

**LIVE EVIDENCE ADDED:** Confirms routes. `submitCodes.php` observed sending `code,userIDX,timer,hash,valid`. No bodies.

**ORIGINAL CLIENT EVIDENCE:** Core40 AS proves these endpoints exist.

**WHAT IS ACTUALLY MISSING:** Response bodies.

**CAN IMPLEMENT WITHOUT LIVE RESPONSE?** Yes, if local files are functional. Verify implementation.

---

### SOCIAL / PROFILE

**OUR CURRENT SUPPORT:** `php2/social/getAlerts.php`, `php2/social/getDefs.php`, `php2/social/getPosts.php`, `php2/social/getBinPosts.php`, `php2/social/addEventPost.php` all exist.

**LIVE EVIDENCE ADDED:** Confirms routes. No new routes observed. No response bodies.

**ORIGINAL CLIENT EVIDENCE:** Core40 AS proves complete social contracts (see `core-contracts.md`).

**WHAT IS ACTUALLY MISSING:** Response body verification for `getAlerts.php`.

**CAN IMPLEMENT WITHOUT LIVE RESPONSE?** Existing implementation is sufficient.

---

### PETS (extended)

**OUR CURRENT SUPPORT:** See Bin Pets section above. Additional endpoints from core40 AS not yet in our server: `getPetProfile.php`, `getAcquiredJugglingTricks.php`, `updateJugglingTrick.php`, `updatePetSkill.php`.

**LIVE EVIDENCE ADDED:** No pet traffic in this session.

**ORIGINAL CLIENT EVIDENCE:** Core40 AS proves all pet endpoint contracts.

**WHAT IS ACTUALLY MISSING:** Server implementations for `getPetProfile`, `getAcquiredJugglingTricks`, `updateJugglingTrick`, `updatePetSkill`.

**CAN IMPLEMENT WITHOUT LIVE RESPONSE?** Yes. Request contracts proven. Response contracts proven (mostly `responseCode` + known field names). Defer to Bin Pets package for full integration.

---

### TYCOON / DINER / MEMBERSHIP

**OUR CURRENT SUPPORT:** `php2/membership/getTycoonRevenue.php`, `php2/membership/collectTycoonEarnings.php`, `php/saveTycoonBusinessState.php`, `php/submitTycoonBusinessName.php`, `php/buyTycoonBusinessPremises.php`, `php/getTycoonRatings.php` all exist.

**LIVE EVIDENCE ADDED:** No tycoon HTTP traffic observed.

**ORIGINAL CLIENT EVIDENCE:** Core40 AS proves `tycoon/startSession`, `weevil/remaining-revenue`, `weevil/buy-food`, `weevil/update-stats`.

**CAN IMPLEMENT WITHOUT LIVE RESPONSE?** Existing files should be verified.

---

### GARDEN

**OUR CURRENT SUPPORT:** `garden/get-plant-configs`, `garden/add-plant`, `garden/harvest-plant`, `garden/water-plant`, `garden/remove-plant`, `garden/harvest-all-perishables`, `garden/harvest-all-non-perishables`, `garden/get-harvest-cooldowns` — all proven in core40 AS.

**LIVE EVIDENCE ADDED:** `GET /garden/get-plant-configs` observed. No bodies.

**WHAT IS ACTUALLY MISSING:** Implementation verification.

---

### NEST

**OUR CURRENT SUPPORT:** `nest/get-nest-state`, `nest/getconfig`, `nest/update-fuel`, `nest/level-up`, `nest/rate-nest-room`, `nest/get-rooms-rated-today`, `nest/get-weevil-stats` all exist or are proven.

**LIVE EVIDENCE ADDED:** Confirms `nest/getconfig` (sending `id,st,hash`), `nest/get-nest-state` (sending `id`), `nest/level-up` (GET).

**WHAT IS ACTUALLY MISSING:** Implementation verification.

---

### QUESTS / TASKS

**OUR CURRENT SUPPORT:** `quests/get-quest-data`, `quests/task-completed` proven in core40 AS.

**LIVE EVIDENCE ADDED:** `POST /quests/task-completed` observed sending `taskID=946,userID=mrslim,st=28694,hash=d43938f99e80381e966469ac9dd609cd`. No body captured.

**CONTRACT DIFFERENCE:** Our local `task-completed` implementation unconditionally reads `questID` instead of `taskID`. This is a confirmed mismatch from the original client contract.

**WHAT IS ACTUALLY MISSING:** Fix request param name from `questID` to `taskID` (per core40 AS proof).

---

### MISCELLANEOUS NEW ROUTES

Routes observed in HAR but not in core40 AS audit and not in our server:

- `php2/weevil/setPreferredMap.php` — request GET/POST with no params observed
- `php2/weevil/getStaffBadges.php` — GET, no params
- `php2/weevil/getTokens.php` — GET, no params
- `php2/smartfox/getActiveZones.php` — exists in our server
- `php2/smartfox/getBuddyCount.php` — exists in our server
- `php2/ads/getadpaths.php` — exists in our server
- `php2/login/getWeevilSub.php` — exists in our server
- `php2/login/logoutClient.php` — exists in our server
- `php2/nest/addItemToNest.php` — exists in our server
- `php2/nest/getStoredGardenItemsAndSeeds.php` — exists in our server
- `php2/nest/buyRoom.php` — exists in our server
- `php2/nest/getGardenOfTheWeek.php` — exists in our server
- `php2/shop/departmentStore/*` — exists in our server
- `php2/mushrooms/collect-mushroom.php` — exists in our server
- `php2/magazines/read/getRandomList.php` — NEW
- `php2/magazines/read/getIssueForDisplay.php` — NEW
- `php2/adcampaigns/competitions.php` — NEW
- `php/battleOfTheBin/getWinner.php` — NEW
- `php/battleOfTheBin/getMyWeeklyKOBStats.php` — NEW
- `php/battleOfTheBin/getWeeklyKOBStats.php` — NEW
- `php/getMultiplayerGameList.php` — NEW
- `php/getGameList.php` — NEW
- `php/getFile.php` — NEW
- `php/getDefaultLocID.php` — NEW
- `php/hardestTrackUnlocked.php` — NEW
- `php/isTrackUnlocked.php` — NEW
- `php/getWordSearchProgress.php` — NEW
- `php/saveWordSearchProgress.php` — NEW
- `php/getCrosswordProgress.php` — NEW
- `php/saveCrosswordProgress.php` — NEW
- `php/saveTycoonBusinessState.php` — exists
- `php/submitTycoonBusinessName.php` — exists
- `php/userFoundItem.php` — NEW
- `php/updateItemPosition.php` — NEW
- `php/setLocColour.php` — NEW
- `php/addItemToNest.php` — NEW
- `php/enterParty.php` — exists
- `php/incrPartyTime.php` — exists
- `php/getPuzzleList.php` — NEW
- `php/getSpecialMoves.php` — exists
- `php/getIgnoreList.php` — exists
- `php/getTreasureHunt.php` — exists
- `php/getTrackDetails.php` — exists
- `php2/weevil-kart/submit-single-user-time.php` — exists
- `php2/leaderboards/games/getPlayerHighScore.php` — exists
- `php2/leaderboards/games/submitScore.php` — exists
- `php2/vod/getVODInfo.php` — exists
- `php2/userFoundTreasure.php` — NEW
- `php2/weevil/getStaffBadges.php` — NEW
- `php2/weevil/getTokens.php` — NEW

## RESPONSE BODIES ACTUALLY CAPTURED

1. `POST /php2/pets/getUserPets.php` → `{"pets":[],"responseCode":1}`
2. `GET /php/getMyLottoTicketsAndDrawDate.php` → `responseCode=1&nextDraw=2026-09-04+17%3A00%3A00&drawID=420&gotTicket=0&tickets=&b=r`
3. `POST /php/incrPartyTime.php` → `responseCode=1`

## SAFETY CHECKS

- No raw cookies committed
- No session tokens committed
- No password hashes committed
- Raw encrypted SFS blobs stripped
- Sanitized derivative hashes recorded in SHA256SUMS.txt
- Raw downloads remain local/untracked

## IMPLEMENTATION CLASSIFICATION

### ALREADY PRESENT / NO ACTION

- `php2/social/getAlerts.php`, `getDefs.php`, `getPosts.php`, `getBinPosts.php`, `addEventPost.php`
- `php2/achievements/getCompletedAchievements.php`, `getNewAchievements.php`, `getAllAchievements.php`
- `php2/nest/getStoredItems.php`, `removeItemFromNest.php`, `getStoredGardenItemsAndSeeds.php`, `level-up.php`, `partyRoomDwell.php`, `buyRoom.php`, `getGardenOfTheWeek.php`
- `php2/pets/getUserPets.php`, `getPetSkills.php`, `updatePetStats.php`, `adoptPet.php`, `feedPet.php`, `buy.php`, `validate-pet-name.php`
- `php2/shop/getHaggleItems2.php`, `getHagglePrices.php`, `sellHaggleItems.php`
- `php2/rewards/getCodes.php`, `submitCodes.php`
- `php2/membership/getTycoonRevenue.php`, `collectTycoonEarnings.php`
- `php2/smartfox/getActiveZones.php`, `getBuddyCount.php`
- `php2/login/getScaledWeevils.php`, `getWeevilSub.php`, `logoutClient.php`
- `php2/nest/addItemToNest.php` (newer route, exists locally)
- `php2/shop/departmentStore/*` (all exist locally)
- `php2/mushrooms/collect-mushroom.php`
- `php/enterParty.php`, `php/incrPartyTime.php`, `php/getMyLottoTicketsAndDrawDate.php`
- `php/saveTycoonBusinessState.php`, `php/submitTycoonBusinessName.php`, `php/buyTycoonBusinessPremises.php`, `php/getTycoonRatings.php`
- `php/getSpecialMoves.php`, `php/getIgnoreList.php`, `php/getTreasureHunt.php`, `php/getTrackDetails.php`
- `php/weevil/data`, `php/weevil/remaining-revenue`, `php/weevil/buy-food`, `php/weevil/update-stats`, `php/weevil/add-ignore-list`, `php/weevil/remove-ignore-list`
- `php/nest/get-nest-state`, `php/nest/getconfig`, `php/nest/update-fuel`, `php/nest/rate-nest-room`, `php/nest/get-rooms-rated-today`, `php/nest/get-weevil-stats`, `php/nest/level-up`
- `php/quests/get-quest-data`, `php/quests/task-completed`
- `php/garden/*` (all routes)
- `php/site/server-time`
- `php2/weevil-kart/submit-single-user-time.php`, `php2/submit-single-user-time.php`
- `php2/leaderboards/games/*`, `php2/leaderboards/buddies.php`, `php2/leaderboards/singlePlayerGame.php`, `php2/leaderboards/weevilWheels.php`, `php2/leaderboards/getTop10Richest.php`
- `php2/vod/getVODInfo.php`

### NEEDS SMALL COMPLETION / COMPATIBILITY FIX

- `php2/quests/task-completed` — **PARAM NAME MISMATCH**: local code reads `questID`; original client and live server prove `taskID`. Fix param name.

### ASSET/UI RECOVERABLE NOW (needs SWF download)

- Level colour UI: `backdropUI_230425b.swf`, `glowingStars.swf`, `level*.swf` — download failed via `cdn.binweevils.app`; needs alternate URL/path
- Backdrop shop UI: `backdropUI_230425b.swf` — same download issue
- Brain Strain UI: `brainstrain_080225a.swf`, `braintrainingconfig_28_09_11.xml`
- Lotto UI: `lotto_200423.swf`
- Loyalty UI: `loyaltyCard_10_01_25.swf`, `loyaltyCards/loyaltyCard1.swf`
- Haggle Hut UI: `HaggleHut_04_08_25.swf`, `haggleHutOverlay_040825b.swf`, `pipeNest_haggleHut_290825.swf`
- Bin Pets UI: `weevilPet_assets_210225.swf`, `weevilPet_assets_200826.swf`, `bpp_changeroverlay.swf`, `bpp_studiooverlay.swf`, `binpetparadise_gym_21_05_14.swf`
- Social UI: `buddyfeed_120726.swf`, `buddypanel_17_11_25.swf`, `achievementalertsmanager4.swf`

### SAFE TO ADD NOW (request contract proven, response can be stubbed from client evidence)

- `php2/weevil/setLevelColour.php` — request proven, response needs SWF decompilation
- `php2/backdrops/getOwnedBackdrops.php` — GET, no params, response from SWF
- `php2/backdrops/getShopItems.php` — GET, no params, response from SWF
- `php2/backdrops/getUnlockableBackdrops.php` — GET, no params, response from SWF
- `php2/mission/getRoomHelp.php` — POST `idx,roomName`, response proven
- `php2/mission/buyHelp.php` — POST `idx,helpId`, response proven
- `php2/mission/buyMission.php` — POST `idx,questId,taskId,voucher`, response proven
- `php2/pets/getPetProfile.php` — POST `petID`, response proven
- `php2/pets/getAcquiredJugglingTricks.php` — POST `petID,idx`, response proven
- `php2/pets/updateJugglingTrick.php` — POST `petID,idx,trickID,aptitude,skillLevel`, response proven
- `php2/pets/updatePetSkill.php` — POST `petID,idx,skillID,skillLevel,obedience`, response proven
- `game/brain-info` — GET, no params, response from SWF
- `game/brain-submit` — POST `score,levels,st,hash`, response from SWF
- `php2/loyalty/getProgress.php` — verify existing
- `php2/loyalty/getStamp.php` — verify existing
- `php2/loyalty/getVouchers.php` — verify existing

### WAIT FOR BIN PETS PACKAGE

- Full Bin Pets integration: adoption flow, skill progression, tricks, feeding, fuel, happiness, experience, obedience, aptitude, cooldowns, reward formulas, pet inventory, pet shop purchases

### BLOCKED BY UNKNOWN SERVER AUTHORITY

- Mulchtastic reward formula (`mulchEarned`, `xpEarned`, `mulch`, `xp` values)
- Lotto prize/reward amounts, jackpot calculation, draw logic
- Backdrop purchase pricing, currency, requirements
- Level colour unlock thresholds, colour ID list
- Haggle Hut item prices, dynamic pricing formula
- Loyalty stamp/final reward values
- Mission task rewards, voucher consumption values

## REMAINING EVIDENCE GAPS

### RESOLVABLE FROM EXISTING PROJECT + ORIGINAL CLIENT

- Quest `task-completed` param name: fix from `questID` to `taskID` using core40 AS proof
- All social/achievement/nest/pet request/response contracts: already proven in core40 AS
- Tycoon/diner/garden contracts: already proven in core40 AS
- SmartFox zone/buddy count: already proven

### RESOLVABLE BY DOWNLOADING / DECOMPILING OBSERVED SWF

- Level colour: decompile `backdropUI_230425b.swf` or `glowingStars.swf`
- Backdrop shop: decompile `backdropUI_230425b.swf`
- Brain Strain: decompile `brainstrain_080225a.swf`
- Lotto: decompile `lotto_200423.swf`
- Loyalty: decompile `loyaltyCard_10_01_25.swf`
- Haggle Hut: decompile `HaggleHut_04_08_25.swf`
- Bin Pets: decompile `weevilPet_assets_210225.swf`
- Social: decompile `buddyfeed_120726.swf`, `buddypanel_17_11_25.swf`
- Rewards: decompile `levelUpRewards.swf`

**Note:** Direct download from `cdn.binweevils.app` returned redirect to HTML. Need alternate download path or permission from server operator.

### GENUINELY BLOCKED

- Mulchtastic reward formula (no client code proves server authority; SWF may show response field names but not calculation)
- Lotto prize/jackpot amounts (requires server-side randomness/authority)
- Backdrop purchase prices and currency (requires server-side catalogue)
- Level colour unlock thresholds (requires data not visible in HAR)

## RECOMMENDED IMPLEMENTATION ORDER

1. Fix `quests/task-completed` param name mismatch
2. Decompile `backdropUI_230425b.swf` → implement level colour + backdrop catalogue
3. Decompile `brainstrain_080225a.swf` → implement `brain-info`/`brain-submit`
4. Decompile `lotto_200423.swf` → implement remaining lotto endpoints
5. Decompile `loyaltyCard_10_01_25.swf` → verify/complete loyalty endpoints
6. Decompile `HaggleHut_04_08_25.swf` → verify haggle hut
7. Decompile `weevilPet_assets_210225.swf` → compare with incoming Bin Pets package
8. Implement mission stubs (`getRoomHelp`, `buyHelp`, `buyMission`)
9. Verify existing loyalty/mission/social files against client contracts
