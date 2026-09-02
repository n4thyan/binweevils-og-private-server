# Recovered core/client endpoint audit — 2 September 2026

## Summary

This pass used the recovered Flash client as the contract source and audited the current localhost backend before implementing anything.

- Current client source: `game-full/cdn.binw.net/core40.swf`, SHA-256 `28dbc37d21ba77d1d587603827f47cb61df755c77cf80ef7d7b0fd0802589d3a`.
- The repository, `C:\xampp\htdocs\cdn.binw.net\core40.swf`, and `C:\xampp\htdocs\core40.swf` are byte-identical.
- JPEXS exported 608 ActionScript files from core40. All were scanned.
- The recovered SWF corpus contains 16,165 SWFs (944,722,916 bytes). Every binary was searched read-only for endpoint/service strings; current representative endpoint-bearing external UIs were then decompiled for contracts.
- 156 unique fixed PHP/HTTP route identities were discovered. Of these, 106 were contract-resolved through full core40 or targeted external-UI decompilation (73 have a repository/live file; 33 are absent). The remaining 50 are corpus-string candidates from historical/data-driven SWFs (33 have a file; 17 are absent) and are marked `NEEDS MORE EVIDENCE`. Overall file presence is therefore 106/156 and absence is 50/156; file presence is not treated as proof of compatibility.
- Separately, core40 exposes 12 browser-WebSocket command routes, one AMFPHP gateway, SmartFox BlueBox HTTP fallback, and SmartFox upload transport. Those are listed separately and are not included in the 156 PHP/HTTP identity count.
- Two proven achievement status endpoints were restored and tested against localhost. No schema migration was needed.
- No Mulchtastic reward/scoring behavior, XP formula, currency amount, probability, cooldown, schema, or response code was guessed.

Coverage boundary: this is a broad whole-corpus scan plus full core40 decompilation and targeted decompilation of endpoint-bearing external UIs. It does not claim that all 16,165 historical/duplicate SWFs were individually decompiled. Dynamic AMF method names, data-driven URL paths, and unreachable historical variants remain explicitly unresolved.

## Source versions and transport rules

`core21.swf` through `core40.swf` are distinct binaries. `checkVersion.php` selects core40, and the served core40 hash matches the repository. Core40 is therefore primary; older cores are historical comparison material.

Transport proved from recovered helpers:

- `PHP2call("x")` -> `php2/x.php?rndVar=...`.
- PHP2 `sendAndAwaitResponse`/`fireAndForget` -> form POST; `awaitResponse` -> GET-like request.
- PHP2 `secure=true` adds `timer`, alphabetizes names/values, then adds `hash=MD5(salt + concatenated sorted values)`.
- PHP2 JSON mode reads text, JSON-decodes it, and calls `(object,event)`; normal mode reads URL variables.
- `PHPcall("x")` -> `php/x.php?rndVar=...`; `PHPcall("x", true)` -> extensionless service path `x?rndVar=...`.
- PHPcall `secure=true` adds `st` and hashes values in caller order; it does not alphabetize.
- `completedAchievements`, when present in any helper response, is dispatched globally to the badge-alert system.

## Whole-client endpoint inventory

Status legend: `IMPLEMENTED`, `MISSING`, `STUB`, `PARTIAL`, `SUSPICIOUS`, `CLIENT/BACKEND MISMATCH`, `LEGACY / PROBABLY UNUSED`, `NEEDS MORE EVIDENCE`.

### Account, login and player state

| Endpoint | Client contract | Backend status |
|---|---|---|
| `php2/login/activity.php` | POST, no fields, fire-and-forget while engaged | `STUB`/harmless acknowledgement |
| `php2/weevil/get-login-details.php` | POST; expects `userName,userIDX,tycoon,tycoonTV,loginKey` | `IMPLEMENTED` |
| `php2/smartfox/getActiveZones.php` | GET; expects `servers,ips,oo5` lists | `PARTIAL`; socket occupancy probe is disabled and `oo5` is fixed at zero |
| `php2/smartfox/getBuddyCount.php` | GET; expects `counts` | `IMPLEMENTED`; repo/live differ only by line endings |
| `php2/login/logoutClient.php` | GET; response ignored, then navigates to login | `IMPLEMENTED` |
| `php2/weevil/getData.php` | POST `id`, secure, JSON; object forwarded | `PARTIAL`; login/join dates and pet IDs are placeholder/null |
| `weevil/data` | POST `id`; object forwarded/cached | `PARTIAL`; `lastLog` is fixed at Unix epoch |
| `php/getWeevilDefinition.php` | POST `userID`; caller-supplied handler | `MISSING`; response schema needs a concrete caller before aliasing |
| `php2/weevil/getScaledWeevils.php` | POST; JSON `res,big,small` | `STUB` (hard-coded names) |
| `weevil/update-user-info` | POST `idx`, fire-and-forget | `MISSING`; intended persistence is not proved |
| `nest/get-weevil-stats` | POST `idx,key`, signed; exact stats plus response `st,hash` | `IMPLEMENTED` |
| `weevil/update-stats` | POST `food,fitness,happiness`, signed | `PARTIAL`; helper persists food but ignores fitness/happiness |
| `weevil/buy-food` | POST `cost,energyValue,type`, signed; expects `success,food,mulch` | `PARTIAL`; client-supplied price/value authority requires later hardening |
| `weevil/remaining-revenue` | GET; expects `res` | `STUB` |
| `site/server-time` | GET; expects `t` | `IMPLEMENTED` |
| `weevil/get-my-apparel` | GET; XML items `id,cat,rgb,worn` | `IMPLEMENTED` |
| `php/getSpecialMoves.php` | POST `userID`; expects `result` | `IMPLEMENTED` |

### Nest, furniture and tycoon

| Endpoint | Request / expected response | Status |
|---|---|---|
| `nest/get-nest-state` | POST `id`; `lastUpdate,fuel,score,xp` | `IMPLEMENTED` |
| `nest/getconfig` | POST `id,st,hash`; XML nest config | `IMPLEMENTED` |
| `php2/nest/getStoredItems.php` | POST `idx`; JSON `items[]` | `IMPLEMENTED` |
| `php2/nest/removeItemFromNest.php` | POST `itemID,nestID,userID`; JSON `responseCode,items` | `CLIENT/BACKEND MISMATCH`: success path references undefined `$userItem`/`$tagCache`, so callback contract is unreliable under PHP 8 |
| `php/addItemToNest.php` | POST item/location/frame fields; no response handler | `IMPLEMENTED` |
| `php/updateItemPosition.php` | POST position/frame/spot fields; no response handler | `IMPLEMENTED` |
| `php/setLocColour.php` | POST `nestID,locID,col`; no response handler | `IMPLEMENTED` |
| `nest/update-fuel` | POST `fuel`; no response handler | `IMPLEMENTED`, but client submits absolute fuel |
| `nest/rate-nest-room` | POST `rating,locID`; fire-and-forget | `MISSING`; no rating table/support |
| `nest/get-rooms-rated-today` | GET; expects comma-list `ratedToday` | `MISSING`; no rating table/support |
| `nest/level-up` | GET; signed response `level,mulch,xp,xp1,xp2` | `IMPLEMENTED`; progression remains subject to the separate manual stabilisation pass |
| `php2/nest/partyRoomDwell.php` | POST `visitorIdx,tycoonIdx,step`; `responseCode,visitorXp` | `IMPLEMENTED`; existing fixed reward schedule not changed |
| `tycoon/startSession.php` | POST `idx,id,nid,tyc,st,hash`; no response | `STUB` |
| `php/getTycoonRatings.php` | POST `idx`; expects `ratings` | `STUB`/empty |
| `php2/membership/getTycoonRevenue.php` | current tycoon SWF expects JSON business/revenue list | `IMPLEMENTED` |
| `php2/membership/collectTycoonEarnings.php` | current tycoon SWF collects earnings | `IMPLEMENTED`, authority review still advisable |
| `php/enterParty.php`, `php/incrPartyTime.php` | party entry/dwell side effects | `STUB`; no mutation or output |

### Achievements and badges

| Endpoint | Contract | Status |
|---|---|---|
| `php2/achievements/getAllAchievements.php` | GET JSON nested badge types/achievement metadata | `PARTIAL`; static catalogue, contract-shaped |
| `php2/achievements/getCompletedAchievements.php` | POST `idx`, secure; `responseCode,userCompletedAchievements,lastCompletedAchivement` | `IMPLEMENTED THIS PASS` |
| `php2/achievements/getNewAchievements.php` | POST own `idx`, secure; `responseCode,newAchievements` | `IMPLEMENTED THIS PASS` |

The misspelled `lastCompletedAchivement` field is intentional: that is what `binBadgesDisplay2.swf` reads.

### Missions, quests and challenges

| Endpoint | Contract | Status |
|---|---|---|
| `php2/mission/getMissionList.php` / `php/getMissionList.php` | mission catalogue lists | `IMPLEMENTED`, but core40 directly uses the service calls below |
| `php2/mission/getRoomHelp.php` | POST `idx,roomName`; JSON response forwarded | `MISSING` |
| `php2/mission/buyHelp.php` | POST `idx,helpId`, secure; success requires `newDosh` | `MISSING` |
| `php2/mission/buyMission.php` | POST `idx,questId,taskId,voucher`, secure; success requires post-purchase `dosh` | `MISSING` |
| `quests/get-quest-data` | GET; expects `tasks,itemList` | `IMPLEMENTED` |
| `quests/task-completed` | three POST variants; expects `xp,mulch,dosh,itemName,bundleName` | `PARTIAL`/multi-signature risk |
| `php/updateChallengeProgress.php` | POST `cID,prg,userID`, signed, used by Lotto/Spot Difference | `MISSING` |
| `php2/loyalty/getProgress.php` | loyalty-card progress JSON | `MISSING` |
| `php2/loyalty/getStamp.php` | POST `userIDX`, secure; stamp/reward response | `MISSING` |
| `php2/loyalty/finalReward.php` | loyalty-card final reward | `MISSING` |
| `php2/loyalty/getVouchers.php` | JSON vouchers array | `STUB` (always empty) |

The local `quests` table is empty while `questtasks` has 3,694 rows. Mission-help prices, voucher rules and authoritative purchase behavior are therefore not safe to invent.

### Social, friends, messages and moderation

| Endpoint/route | Contract | Status |
|---|---|---|
| `php2/social/getDefs.php` | POST `weevils`, secure JSON; `weevils[]` definitions | `IMPLEMENTED` |
| `php2/social/getPosts.php` | POST `idx,period`, secure JSON; `period,responseCode,posts` | `PARTIAL` |
| `php2/social/getBinPosts.php` | Weevil Post news JSON | `STUB` (single static post) |
| `php2/social/addEventPost.php` | game/social event post | `SUSPICIOUS`; source has variable-order/undefined-name defects |
| `buddy-messages/send-buddy-message` | POST `msg,recipIDX,hash` | `MISSING` |
| `buddy-messages/delete-no-from-buddy` | POST `ids`; expects message list shape | `MISSING` |
| `buddy-messages/delete` | POST conversation `id`, fire-and-forget | `MISSING` |
| `weevil/add-ignore-list` | POST `username` | `IMPLEMENTED` |
| `weevil/remove-ignore-list` | POST `username` | `IMPLEMENTED` |
| `php/getIgnoreList.php` | POST `userID`; expects comma-list `result` | `IMPLEMENTED` |
| `php2/crisp/reportWeevil.php` | POST reported identity/reason/comment | `IMPLEMENTED` |
| `php2/news/markRead.php` | POST `newsVersion`, fire-and-forget | `MISSING`; no per-user news-read schema |

Core40 also exposes 12 WebSocket command routes: `nest-news`, `weevil/get-notifications`, `friends/get-list`, `friends/get-weevil`, `friends/send-request`, `friends/handle-request`, `friends/delete`, `conversation/new`, `conversation/list`, `conversation/load`, `conversation/new-message`, and `conversation/delete-message`. The last two have no matching incoming `cn` case in core40. This audit did not modify the known port-2087 Buffer/string defect.

### Gardening

| Endpoint | Contract | Status |
|---|---|---|
| `garden/get-plant-configs` | POST `userID`; XML plants + `weevilHappiness` | `IMPLEMENTED` |
| `garden/get-harvest-cooldowns` | POST; `responseCode,peri,non-peri` | `IMPLEMENTED` |
| `garden/harvest-all-perishables` | POST; `responseCode,xp,mulch` | `IMPLEMENTED` |
| `garden/harvest-all-non-perishables` | POST; `responseCode,xp,mulch` | `IMPLEMENTED` |
| `garden/harvest-plant` | POST `plantID`; expects `plantID,mulch,xp` | `IMPLEMENTED` |
| `garden/remove-plant` | POST `plantID`, signed | `IMPLEMENTED` |
| `garden/water-plant` | POST `plantID` | `IMPLEMENTED` |

The garden endpoint shapes match core40, but reward/harvest authority deserves a later dedicated security pass. No garden formulas or stock were changed here.

### Pets

| Endpoint | Contract | Status |
|---|---|---|
| `php2/pets/getUserPets.php` | POST `idx`, secure JSON; `responseCode,pets[]` | `IMPLEMENTED THIS PASS` |
| `php2/pets/getPetSkills.php` | POST `petID,idx`, secure JSON; `skills[]` | `IMPLEMENTED` for existing seeded pet |
| `php2/pets/getAcquiredJugglingTricks.php` | POST `petID,idx`, secure JSON; `tricks[]` | `MISSING`; no proved juggling schema |
| `php2/pets/updateJugglingTrick.php` | POST trick aptitude/skill level, secure JSON | `MISSING`; no proved juggling schema |
| `php2/pets/updatePetSkill.php` | POST skill level/obedience, secure JSON | `MISSING` |
| `php2/pets/updatePetStats.php` | POST `petID,idx,fuel,mentalEnergy,fitness,experience`; expects `responseCode,fuel,mentalEnergy,fitness,experience` | `IMPLEMENTED THIS PASS` |
| `php2/pets/getPetProfile.php` | POST `petID`, secure JSON; `profile.id,rented,name,ownerId,adoptedDate,skills,bc,pp` | `MISSING` |

These were documented only. No Bin Pets implementation was started or modified.

### Shops and inventory

| Endpoint | Client | Status |
|---|---|---|
| Department-store stock/tag/level/featured/palette endpoints | Nestco/BinMart SWFs | `IMPLEMENTED`; current live `getStockItemsForLevel.php` exists |
| `php2/shop/departmentStore/buyItem.php` | Nestco/BinMart | `IMPLEMENTED`; no currency-rule change here |
| `php2/shop/buyDoshShopItem.php` | Halloween/current legacy shop | `IMPLEMENTED` |
| `php2/shop/buyTokenItem.php` | Prize Hut | `IMPLEMENTED` |
| `shop/buyitem` | Night Club shop | `IMPLEMENTED` |
| `php2/shop/getDoshShopItems.php` | legacy Dosh catalogue | `STUB` (hard-coded catalogue) |
| bundle/showroom endpoints | department-store variants | `PARTIAL`; recovered stock is incomplete and must not be fabricated |

### Additional corpus-only endpoint identities

The full binary scan found 50 more fixed route identities outside the 106 contract-resolved paths. These were not all individually decompiled, so the status is deliberately limited to file presence and `NEEDS MORE EVIDENCE`:

| System | Additional routes | File state |
|---|---|---|
| Garden shop | `gardenshop/fetch`, `gardenshop/buy-item` | present |
| Garden item control | `garden/add-item`, `add-plant`, `move-item`, `move-plant`, `remove-item` | present |
| Nest | `php2/nest/buyRoom`, `getGardenOfTheWeek` | present |
| Inspector tools | `nest/gardenInspectorSubmit`, `nest/nestInspector/submitDone`, `submitPic` | absent |
| Pets | `pets/buy`, `pets/validate-pet-name` | present |
| Pets | `pets/getPetForADay`, `pets/change` | absent |
| Mystery codes | `rewards/getCodes`, `rewards/submitCodes` | present |
| Haggle Hut | `shop/getHaggleItems2`, `getHagglePrices`, `sellHaggleItems` | present |
| Bundles/showroom | `shop/bundles/getBundles`, `getShowroom` | present |
| Bundles/showroom | `shop/bundles/getItem`, `buyBundle` | absent |
| Apparel/hat shops | `shop/buyHat`, `apparel-shop/get-hat-stock`, `buy-hat` | present |
| Social/leaderboards | `social/getAlerts`, `leaderboards/buddies`, `singlePlayerGame`, `weevilWheels` | present |
| Login messages | `loginMessages/getMessage`, `messageRead` | present |
| Invite status | `weevil/check-invite-status` | present stub |
| Legacy multiplayer | `game/getPlayerStats` | present |
| Pirate-party game | `game/has-the-user-played`, `game/save-game-stats` | absent |
| Christmas rewards | `rewards/collect-seeds`, `rewards/collect-xp` | present |
| Christmas rewards | `rewards/bingsGrotto` | absent |
| Legacy puzzles | `php/getPuzzleList.php` | present |
| Legacy puzzles | `php/winner.php` | absent |
| Activation/geo | `weevil/get-email`, `weevil/geo`, `weevil/geo-lookup` | absent |
| Ads | misspelled `php2/ads/recieveAdPaths.php` | present |
| Old Nest clients | `php/getNestConfig.php`, `getLastNestUpdate.php`, `sellItem.php` | absent |

### Minigames, leaderboards and collectables

| Endpoint | Client/system | Status |
|---|---|---|
| `game/brain-info` / `game/brain-submit` | Brain Strain | `IMPLEMENTED`; write path needs authority review |
| `php2/leaderboards/games/getPlayerHighScore.php` | Bin Pet Bounce/Digg | `IMPLEMENTED` |
| `php2/leaderboards/games/submitScore.php` | Bin Pet Bounce/Digg | `IMPLEMENTED`; server-authority review required |
| `game/start-race` / `game/submit-race` | Weevil Kart race | `IMPLEMENTED` |
| `game/submit-single` | Spot Difference | `IMPLEMENTED` |
| `game/submit-trial` | Weevil Kart time trial | `MISSING` |
| `php2/weevil-kart/submit-single-user-time.php` | Weevil Kart | `PARTIAL`/security-sensitive |
| `php2/weevilWheels/getTrackData.php` | Weevil Wheels | `MISSING` |
| `php2/weevilWheels/submitTrack.php` | Weevil Wheels | `MISSING` |
| `php2/weevilWheels/submitTrackModVerdict.php` | Weevil Wheels | `MISSING` |
| `php2/weevilWheels/submitTrackTime.php` | Weevil Wheels | `MISSING` |
| `php/getTreasureHunt.php` | core bootstrap | `STUB` (feature disabled/static failure) |
| `php2/userFoundTreasure.php` | Treasure Hunt external UI | `MISSING` |
| `php2/vod/getVODInfo.php` | VOD client | `STUB` static content |

### Mulchtastic / Lotto

Recovered location definitions prove that the object named `mulchtasticBooth` opens `externalUIs/lotto.swf`. The similarly named `weevilPost_mulch-tastic.swf` is only a three-class room/door shell and contains no backend call. Therefore the recoverable Mulchtastic backend subsystem is the legacy Lotto flow, not a score-submission minigame.

Flow proved by client code:

1. Core bootstrap `php/getMyLottoTicketsAndDrawDate.php` supplies `gotTicket,tickets,drawID,nextDraw`.
2. Lotto requests `php/getJackpotSize.php` with `drawID`.
3. Four selected digits are concatenated and sent to `php/addLottoTicket.php` with `drawID,ticket`.
4. History uses `php/getPastLottoDraws.php`.
5. Pending results use `php/getUncashedTickets.php`.
6. Draw details use `php/getLottoDrawWinners.php` with `drawID`.
7. Collection uses `php/cashInTickets.php` with `drawID,wins`; handler reads `winnings`.
8. Challenge progress also calls missing `php/updateChallengeProgress.php`.

Current state:

- `getMyLottoTicketsAndDrawDate.php` is a static 2020-era response (`STUB`).
- All six Lotto action/read endpoints and challenge-progress endpoint are `MISSING`.
- The database has no proved Lotto draw/ticket/winner schema.

Classification: `COMPLEX / NEEDS MORE EVIDENCE`. Implementing it would require inventing draw scheduling, ticket uniqueness, costs, winning calculation, jackpot/winnings, claim/replay rules and schema. Nothing was implemented.

## Implemented this pass

### `php2/achievements/getCompletedAchievements.php`

- System: achievements / public badge display.
- Client evidence: `binBadgesDisplay2.swf`, `BinBadgesDisplay.getCompletedAchievements`, secure POST `idx`; handler requires `responseCode`, comma-separated `userCompletedAchievements`, and misspelled `lastCompletedAchivement`.
- DB evidence: existing `achievementscompleted(id,idx,achievementId,completedDate,is_it_new)` table and existing row data.
- Behavior: authenticated/signed read for any viewed user; prepared query; newest completion reported as `lastCompletedAchivement`; duplicate IDs removed.
- Local result: existing user returned `responseCode=1&userCompletedAchievements=2&lastCompletedAchivement=2`; invalid/unauthenticated request returned `responseCode=999...`.

### `php2/achievements/getNewAchievements.php`

- System: login badge alerts.
- Client evidence: core40 `BinBadgesManager.loadNewAchievementsAlerts`, secure POST own `idx`; handler requires `responseCode` and comma-separated `newAchievements`.
- DB evidence: `achievementscompleted.is_it_new` already exists.
- Behavior: confirms session and own `idx`; transactionally selects pending rows, returns distinct IDs, and marks only those rows read.
- Local result: pending row returned `responseCode=1&newAchievements=2` and transitioned `is_it_new 1 -> 0`; the exact original DB flag was restored after the test (`DB_RESTORE_PASS`). Empty state returned `responseCode=1&newAchievements=`. Invalid/unauthenticated state returned `responseCode=999&newAchievements=`.

Both source files were copied to the actual XAMPP DocumentRoot, linted with XAMPP PHP, probed over `http://localhost`, and hash-verified byte-identical between source and served copies.

### `php2/pets/getUserPets.php`

- System: Bin Pets inventory.
- Client evidence: `core40/scripts/com/binweevils/engine3D/visuals/creatures/pets/Brain.as` and `Bin.as`; secure POST `idx`; handler expects JSON `responseCode,pets[]`.
- DB evidence: `pets(id,ownerID,name,bc,fuel,mentalEnergy,health,fitness,experience,...)` exists.
- Behavior: fixed identity check; prepared ownership-scoped query; returns `responseCode=1` with pet rows or `responseCode=3`.
- Local result: authenticated request for pet-owning account returned `responseCode=1` with populated `pets` array; invalid account returned `responseCode=3`.

### `php2/pets/updatePetStats.php`

- System: Bin Pets live stat persistence.
- Client evidence: `Brain.sendUpdate`, secure POST `petID,idx,fuel,mentalEnergy,fitness,experience`; expects `responseCode,fuel,mentalEnergy,fitness,experience`.
- DB evidence: `pets.mentalEnergy` column exists; previous handler used `health` instead.
- Behavior: ownership check via prepared statement; clamps values to 0-100 where appropriate; persists `fuel,mentalEnergy,fitness,experience`; returns exact expected fields.
- Local result: POST for owned pet returned `responseCode=1&fuel=...&mentalEnergy=...&fitness=...&experience=...`; unauthorized/missing returned `responseCode=999`.

## High-confidence missing / next work

Ranked by contract confidence and gameplay value, not by Mulchtastic mention:

1. `php/getWeevilDefinition.php` — likely a small read alias over existing user definition data, but first locate/decompile a concrete callback consumer to prove exact fields.
2. `nest/get-rooms-rated-today` + `nest/rate-nest-room` — exact request/UI contracts are known, but blocked until an original rating table/model is recovered.
3. `php2/pets/getPetProfile.php` — response contract is exact and current pet tables exist, but defer to the separate approved Bin Pets integration/review.
4. `php2/mission/getRoomHelp.php` — read-only and contract-simple, but mission/help data is not present/proved.
5. `buddy-messages/*` — meaningful social restoration, but current core also contains newer WebSocket conversation routes; choose one authoritative transport before implementing duplicates.
6. `game/submit-trial` — concrete caller exists, but reward/time authority must be traced before accepting client timing.

No remaining missing endpoint met every implementation gate (exact response + existing schema/data + clear authority + low risk) during this pass.

## Client/backend mismatches and important partials

- `php2/pets/getUserPets.php`: identity check uses assignment instead of comparison.
- `php2/pets/updatePetStats.php`: core40 sends unsigned JSON while backend follows a hash-dependent path.
- `quests/task-completed`: one PHP file receives three different client parameter signatures.
- `php2/social/addEventPost.php`: source references the username before assignment and mixes event/value conventions.
- `php2/weevil/getScaledWeevils.php`, Lotto bootstrap, Tycoon ratings/session, VOD, Weevil Post and loyalty vouchers are static/no-op stubs rather than live data implementations.
- `getAllAchievements.php` has the expected nested response contract but is a static catalogue disconnected from the otherwise existing achievement metadata tables.
- Several existing score/race/harvest/shop write endpoints accept client-supplied values. They require focused authority audits; they were not rewritten speculatively.
- Root/game-full/live PHP mirrors are otherwise coherent for this audit. `php2/smartfox/getBuddyCount.php` differs only by CRLF/LF line endings.

## Blocked / needs more evidence

- Mulchtastic/Lotto: no schema or authoritative draw/reward rules.
- Mission help/purchases: empty mission catalogue and no proved help/voucher model.
- Nest ratings: no rating schema.
- Buddy messages: old HTTP endpoints conflict with a newer WebSocket conversation API surface.
- Pet juggling/profile writes: schema/ownership/progression evidence incomplete and separate integration pending.
- Weevil Wheels/community tracks: track serialization, moderation and reward authority unresolved.
- AMFPHP: core creates `php/amfphp/Gateway.php` but contains no `NetConnection.call`; methods are external/data-driven.
- Treasure Hunt remoting: recovered UI exposes short service commands, but current service implementation and campaign state are absent.
- Campaign constants `campaignTasks/taskCompleted` and `campaignTasks/getCompletedTaskIds` are not called by core40 itself; likely external/historical.

## Safety and verification

- Branch created from actual integrated `website-redesign` HEAD, not stale `main`.
- No merge/reset and no changes to `feature/room-events-mushrooms`.
- No guessed formulas, rewards, schema, cooldowns, probabilities or response codes.
- No DB migration. The one data-changing achievement test restored its exact original flag.
- No website, advert, xat/community, SWF, Flash timeline, Electron, room-event, XP/prestige, shop-currency, or port-2087 code changed.
- No `mainDEV663.swf` or other binary changed.
- No known Buffer/string defect change.
- Localhost response shape, failure behavior, DB transition/restore, PHP syntax and served/source hashes were checked.
