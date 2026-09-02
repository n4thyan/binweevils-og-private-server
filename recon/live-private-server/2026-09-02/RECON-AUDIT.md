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

## LOCAL CORPUS RECOVERY

Searched local recovered asset corpus at `game-full/cdn.binw.net/` (15,772 SWFs).

### LEVEL STAR COLOUR

- Live filenames: `backdropUI_230425b.swf`, `glowingStars.swf`, `level0.swf`–`level90.swf`
- Local matches: **NONE**
- Provenance: Unknown if original or private-server variant
- Decompilation status: Not possible without SWF binary
- Client contract status: Request proven from HAR. Response contract unknown until UI SWF is recovered.
- Backend exists: No `php2/weevil/setLevelColour.php` in our tree.
- Actually implementable now: **NO** — missing response contract and persistence field.

### PLAYER-CARD BACKDROP SHOP

- Live filenames: `backdropUI_230425b.swf`, plus ~80 `assetsbackdrops/*.swf`
- Local matches: **NONE** for `backdropUI_230425b.swf`. Local corpus has older private-server backdrop assets (`ps_backdrop_beach.swf`, `ps_backdrop_cny.swf`) which are likely private-server-created, not original.
- Provenance: Unknown
- Decompilation status: Not possible without UI SWF
- Client contract status: Request proven (GET catalogue endpoints). Response contract unknown.
- Backend exists: No `php2/backdrops/*` routes.
- Actually implementable now: **NO** — missing response contract, purchase/equip routes, pricing.

### BIN PETS

- Live filenames: `weevilPet_assets_210225.swf`, `weevilPet_assets_200826.swf`, `bpp_changeroverlay.swf`, `bpp_studiooverlay.swf`, `binpetparadise_gym_21_05_14.swf`
- Local matches: **Older variants exist**: `weevilPet_assets_14_03_17.swf`, `weevilPet_assets_28_09_16.swf`, `bpp_ChangerOverlay.swf`, `bpp_StudioOverlay.swf`. Exact observed filenames not present.
- Provenance: Likely original Bin Weevils assets (older date stamps match known release patterns).
- Decompilation status: Not performed in this pass.
- Client contract status: Core40 AS already proves all pet endpoint contracts.
- Backend exists: Substantial — `getUserPets`, `getPetSkills`, `updatePetStats`, `adoptPet`, `feedPet`, `buy`, `validate-pet-name`.
- Actually implementable now: Existing pieces are sufficient baseline. Full integration deferred to incoming package.

### MULCHTASTIC / BRAIN STRAIN

- Live filenames: `brainstrain_080225a.swf`, `braintrainingconfig_28_09_11.xml`, many `brainstrainquestions/*.swf`
- Local matches: `brainStrain_10_06_13.swf` (older variant)
- Provenance: Likely original
- Decompilation status: Not performed
- Client contract status: Core40 AS proves score submission format (`score,levels` where `levels=%2C1%7C0%2C3%7C0...`). Response reward fields unknown.
- Backend exists: No `game/brain-info` or `game/brain-submit` routes.
- Actually implementable now: **PARTIAL** — request proven, response fields blocked, reward formula blocked.

### LOTTO

- Live filenames: `lotto_200423.swf`
- Local matches: `lotto_01_03_21.swf`, `lotto_30_10_14_2.swf`, `lotto.swf` (older variants)
- Provenance: Likely original
- Decompilation status: Not performed
- Client contract status: `getMyLottoTicketsAndDrawDate.php` response proven from HAR: `responseCode=1&nextDraw=2026-09-04 17:00:00&drawID=420&gotTicket=0&tickets=&b=r`. Other endpoints unproven.
- Backend exists: Only `getMyLottoTicketsAndDrawDate.php`.
- Actually implementable now: **PARTIAL** — one response proven, remaining endpoints need SWF decompilation, prize logic blocked.

### LOYALTY

- Live filenames: `loyaltyCard_10_01_25.swf`, `loyaltyCards/loyaltyCard1.swf`
- Local matches: `loyaltyCard_28_11_13.swf`, `loyaltyCards/loyaltyPuzzle4_robotBinPet.swf` (older variants)
- Provenance: Likely original
- Decompilation status: Not performed
- Client contract status: Request proven, response unknown.
- Backend exists: `php2/loyalty/getProgress.php`, `getStamp.php`, `getVouchers.php` — implementation status unknown without code review.
- Actually implementable now: **BLOCKED** — response bodies and reward values unknown.

### HAGGLE HUT

- Live filenames: `haggleHut_04_08_25.swf`, `haggleHutOverlay_040825b.swf`, `pipeNest_haggleHut_290825.swf`
- Local matches: Multiple older variants (`HaggleHut_12_03_21*.swf`, `HaggleHut_03_04_13.swf`, `haggleHutOverlay_12_03_21*.swf`, `pipeNest_haggleHut.swf`, `pipeNest_haggleHut_30_03_19.swf`)
- Provenance: Likely original
- Decompilation status: Not performed
- Client contract status: Request proven (`getHaggleItems2`, `getHagglePrices` with `items,seeds,gardenItems,timer,hash`, `sellHaggleItems`). Response unknown.
- Backend exists: All three routes exist locally.
- Actually implementable now: **PARTIAL** — routes exist, response contracts and pricing formula unknown.

### MISSIONS / QUESTS

- Live filenames: None observed in this session
- Local matches: `missionsIndex_01_03_13.swf`, `missionsIndex_20_05_21.swf`, `missions_HeliScreen_24_08_12.swf`, `missionIndex_nest_01_03_13.swf`
- Provenance: Likely original
- Decompilation status: Not performed
- Client contract status: Core40 AS proves `getRoomHelp`, `buyHelp`, `buyMission`. Local `task-completed` exists with multi-signature support already.
- Backend exists: `quests/task-completed.php` exists. Mission routes partially missing.
- Actually implementable now: **PARTIAL** — request contracts proven, response contracts partially proven.

### REWARDS / CODES

- Live filenames: `levelUpRewards.swf`
- Local matches: Not found in corpus
- Provenance: Unknown
- Client contract status: Core40 AS proves `getCodes`, `submitCodes`. Local files exist.
- Backend exists: Yes.
- Actually implementable now: **YES** — request/response contracts proven from AS.

### SOCIAL / PROFILE

- Live filenames: `buddyFeed_120726.swf`, `buddyPanel_17_11_25.swf`, `achievementalertsmanager4.swf`
- Local matches: `buddyFeed_05_12_11.swf`, `buddyFeed_080916v2.swf`, `buddyPanel_*` variants, `AchievementAlertsManager4.swf` (exact match!)
- Provenance: Likely original
- Client contract status: Core40 AS proves all social contracts.
- Backend exists: All routes exist.
- Actually implementable now: **YES** — fully proven.

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

### ALREADY PRESENT — NO ACTION

- `php2/social/getAlerts.php`, `getDefs.php`, `getPosts.php`, `getBinPosts.php`, `addEventPost.php`
- `php2/achievements/getCompletedAchievements.php`, `getNewAchievements.php`, `getAllAchievements.php`
- `php2/nest/getStoredItems.php`, `removeItemFromNest.php`, `getStoredGardenItemsAndSeeds.php`, `level-up.php`, `partyRoomDwell.php`, `buyRoom.php`, `getGardenOfTheWeek.php`
- `php2/pets/getUserPets.php`, `getPetSkills.php`, `updatePetStats.php`, `adoptPet.php`, `feedPet.php`, `buy.php`, `validate-pet-name.php`
- `php2/shop/getHaggleItems2.php`, `getHagglePrices.php`, `sellHaggleItems.php`
- `php2/rewards/getCodes.php`, `submitCodes.php`
- `php2/membership/getTycoonRevenue.php`, `collectTycoonEarnings.php`
- `php2/smartfox/getActiveZones.php`, `getBuddyCount.php`
- `php2/login/getScaledWeevils.php`, `getWeevilSub.php`, `logoutClient.php`
- `php2/nest/addItemToNest.php`
- `php2/shop/departmentStore/*`
- `php2/mushrooms/collect-mushroom.php`
- `php/enterParty.php`, `php/incrPartyTime.php`, `php/getMyLottoTicketsAndDrawDate.php`
- `php/saveTycoonBusinessState.php`, `php/submitTycoonBusinessName.php`, `php/buyTycoonBusinessPremises.php`, `php/getTycoonRatings.php`
- `php/getSpecialMoves.php`, `php/getIgnoreList.php`, `php/getTreasureHunt.php`, `php/getTrackDetails.php`
- `php/weevil/data`, `php/weevil/remaining-revenue`, `php/weevil/buy-food`, `php/weevil/update-stats`, `php/weevil/add-ignore-list`, `php/weevil/remove-ignore-list`
- `php/nest/get-nest-state`, `php/nest/getconfig`, `php/nest/update-fuel`, `php/nest/rate-nest-room`, `php/nest/get-rooms-rated-today`, `php/nest/get-weevil-stats`, `php/nest/level-up`
- `php/quests/get-quest-data`, `php/quests/task-completed` (multi-signature support already present)
- `php/garden/*`
- `php/site/server-time`
- `php2/weevil-kart/submit-single-user-time.php`, `php2/submit-single-user-time.php`
- `php2/leaderboards/games/*`, `php2/leaderboards/buddies.php`, `php2/leaderboards/singlePlayerGame.php`, `php2/leaderboards/weevilWheels.php`, `php2/leaderboards/getTop10Richest.php`
- `php2/vod/getVODInfo.php`
- `php2/ads/getadpaths.php`, `php2/login/getWeevilSub.php`, `php2/login/logoutClient.php`, `php2/time.php`
- `php2/weevil/getData.php`, `get-login-details.php`, `getScaledWeevils.php`
- `php2/smartfox/getActiveZones.php`, `getBuddyCount.php`
- `php2/weevil/change-definition.php`, `changeWeevilDef.php`

### NOW FULLY PROVEN — READY TO IMPLEMENT

These have exact request/response contracts proven from original client AS decompilation:

- `php2/rewards/getCodes.php` — response proven in core40 AS
- `php2/rewards/submitCodes.php` — request/response proven in core40 AS
- `php2/social/*` — all routes proven in core40 AS
- `php2/achievements/*` — all routes proven in core40 AS
- `php2/nest/*` — all routes proven in core40 AS
- `php2/pets/getUserPets.php`, `getPetSkills.php`, `updatePetStats.php` — proven in core40 AS
- `php2/mission/getRoomHelp.php` — proven in core40 AS
- `php2/mission/buyHelp.php` — proven in core40 AS
- `php2/mission/buyMission.php` — proven in core40 AS
- `php/quests/task-completed` — multi-signature support already in local code; core40 AS proves all three caller variants
- `php/weevil/buy-food` — proven in core40 AS
- `php/weevil/update-stats` — proven in core40 AS
- `php/nest/level-up` — proven in core40 AS
- `php/nest/get-nest-state` — proven in core40 AS
- `php/nest/getconfig` — proven in core40 AS
- `php/garden/*` — all proven in core40 AS

### HIGH-CONFIDENCE IMPLEMENTATION CANDIDATE — CLIENT CONTRACT RECOVERY PENDING

Request contract proven from HAR and/or core40 AS. Response contract needs SWF decompilation or additional client evidence before implementation:

- `php2/weevil/setLevelColour.php` — request proven (`userIDX,level,timer,hash`). Response unknown. No local UI SWF. Backend missing.
- `php2/backdrops/getOwnedBackdrops.php` — GET, no params proven. Response unknown. No local UI SWF. Backend missing.
- `php2/backdrops/getShopItems.php` — GET, no params proven. Response unknown. No local UI SWF. Backend missing.
- `php2/backdrops/getUnlockableBackdrops.php` — GET, no params proven. Response unknown. No local UI SWF. Backend missing.
- `php2/pets/getPetProfile.php` — request proven (`petID`). Response fields proven in AS (`profile.id,rented,name,ownerId,adoptedDate,skills,bc,pp`). Backend missing.
- `php2/pets/getAcquiredJugglingTricks.php` — request proven (`petID,idx`). Response proven (`responseCode,skills`). Backend missing.
- `php2/pets/updateJugglingTrick.php` — request proven (`petID,idx,trickID,aptitude,skillLevel`). Response proven (`responseCode`). Backend missing.
- `php2/pets/updatePetSkill.php` — request proven (`petID,idx,skillID,skillLevel,obedience`). Response proven (`responseCode`). Backend missing.
- `game/brain-info` — GET proven. Response unknown. Backend missing.
- `game/brain-submit` — POST `score,levels,st,hash` proven. Response reward fields unknown. Backend missing.
- `php2/loyalty/getProgress.php` — request proven. Response unknown. Backend exists but unverified.
- `php2/loyalty/getStamp.php` — request proven. Response unknown. Backend exists but unverified.
- `php2/loyalty/getVouchers.php` — request proven. Response unknown. Backend exists but unverified.
- `php2/shop/getHaggleItems2.php` — request proven. Response unknown. Backend exists.
- `php2/shop/getHagglePrices.php` — request proven (`items,seeds,gardenItems,timer,hash`). Response unknown. Backend exists.
- `php2/shop/sellHaggleItems.php` — request proven. Response unknown. Backend exists.

### UI/ASSETS/CONTRACT PARTIALLY RECOVERED — SMALL BLOCKER

- Level colour: request proven, no response contract, no UI SWF locally, no backend
- Backdrop shop: requests proven, no response contract, no UI SWF locally, no backend, purchase/equip routes unknown
- Brain Strain: request proven, response reward fields unknown, no backend
- Lotto: one response proven (`getMyLottoTicketsAndDrawDate`), remaining endpoints unproven, no UI SWF locally
- Loyalty: requests proven, responses unknown, implementations unverified
- Haggle Hut: requests proven, responses unknown, pricing formula unknown
- Bin Pets: core contracts proven, some endpoints missing backend, full integration deferred to package

### GENUINELY UNKNOWN

- Mulchtastic reward formula (`mulchEarned`, `xpEarned`, `mulch`, `xp` values) — no client code proves server authority; SWF may show field names but not calculation
- Lotto prize/jackpot amounts — requires server-side randomness/authority
- Backdrop purchase prices and currency — requires server-side catalogue data
- Level colour unlock thresholds and colour ID list — requires UI SWF decompilation
- Haggle Hut dynamic pricing formula — requires server-side logic
- Loyalty stamp/final reward values — requires server-side logic
- Mission task rewards — requires server-side data

## CORRECTED CLASSIFICATION: quests/task-completed

**DO NOT BLINDLY RENAME `questID` to `taskID`.**

Original core40 AS proves THREE legitimate caller signatures:
1. `taskID,userID`
2. `taskID,userID,score`
3. `questID,taskID,userID`

Local PHP at `game-full/quests/task-completed.php` already reads both `taskID` and `questID`, and passes `questID` to `CompleteTask($taskID, $username, $idx, $questID)`. This suggests the local backend already supports the multi-signature pattern.

**Action required:** Verify `CompleteTask()` in `backbone.php` accepts all three signatures and that `questID` is optional/nullable. Do not change param names until that verification is complete.

## REMOVING PREVIOUS "SAFE TO ADD NOW" OVERSTATEMENT

The previous audit incorrectly classified several endpoints as "SAFE TO ADD NOW" based solely on proven request contracts, while simultaneously noting that response contracts require SWF decompilation that has not been performed.

These are reclassified as **HIGH-CONFIDENCE IMPLEMENTATION CANDIDATE — CLIENT CONTRACT RECOVERY PENDING** until:
1. The relevant UI SWF is decompiled, OR
2. Response contracts are proven from existing recovered AS/SWF corpus, OR
3. The endpoint is verified against our existing backend

## REMAINING EVIDENCE GAPS

### RESOLVABLE FROM EXISTING PROJECT + ORIGINAL CLIENT

- Quest `task-completed` multi-signature verification
- All social/achievement/nest/pet request/response contracts: already proven in core40 AS
- Tycoon/diner/garden contracts: already proven in core40 AS
- SmartFox zone/buddy count: already proven

### RESOLVABLE BY DOWNLOADING / DECOMPILING OBSERVED SWF

- Level colour: need `backdropUI_230425b.swf` or equivalent
- Backdrop shop: need `backdropUI_230425b.swf`
- Brain Strain: need `brainstrain_080225a.swf` (older `brainStrain_10_06_13.swf` exists locally)
- Lotto: need `lotto_200423.swf` (older variants exist locally)
- Loyalty: need `loyaltyCard_10_01_25.swf` (older `loyaltyCard_28_11_13.swf` exists locally)
- Haggle Hut: need `haggleHut_04_08_25.swf` (older variants exist locally)
- Bin Pets: need `weevilPet_assets_210225.swf` (older variants exist locally)
- Social: `buddyFeed_120726.swf` not found locally, but older variants exist; `AchievementAlertsManager4.swf` exists locally (exact match)

**Note:** Direct download from `cdn.binweevils.app` returned redirect to HTML. Need alternate download path or permission from server operator. Older local variants may still contain useful contract data.

### GENUINELY BLOCKED

- Mulchtastic reward formula (no client code proves server authority; SWF may show response field names but not calculation)
- Lotto prize/jackpot amounts (requires server-side randomness/authority)
- Backdrop purchase prices and currency (requires server-side catalogue)
- Level colour unlock thresholds (requires data not visible in HAR or local corpus)

## RECOMMENDED IMPLEMENTATION ORDER

1. Verify `quests/task-completed` multi-signature support in `CompleteTask()` — no code change unless broken
2. Decompile high-value local SWFs (older variants) for response contracts:
   - `brainStrain_10_06_13.swf` → Brain Strain response fields
   - `lotto_01_03_21.swf` → Lotto response schema
   - `HaggleHut_12_03_21.swf` → Haggle Hut response fields
   - `loyaltyCard_28_11_13.swf` → Loyalty response fields
   - `buddyPanel_17_02_21.swf` → Social/profile response fields
3. Implement stubs for proven endpoints with missing backend:
   - `php2/pets/getPetProfile.php`
   - `php2/pets/getAcquiredJugglingTricks.php`
   - `php2/pets/updateJugglingTrick.php`
   - `php2/pets/updatePetSkill.php`
4. Implement `php2/mission/getRoomHelp.php`, `buyHelp.php`, `buyMission.php` (request/response proven from AS)
5. Implement `game/brain-info` and `game/brain-submit` stubs (request proven, response fields from SWF)
6. Implement `php2/backdrops/*` catalogue stubs (request proven, response from SWF)
7. Implement `php2/weevil/setLevelColour.php` (request proven, response from SWF)
8. Await Bin Pets package for full integration
9. Download/decompile newer live-server SWFs if alternate path becomes available
