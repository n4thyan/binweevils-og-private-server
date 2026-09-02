# Live private-server recon — 2 September 2026

## CAPTURE SUMMARY

- Source: network capture from another live Bin Weevils private server
- Date: 2026-09-02
- Host observed: `play.binweevils.app`, `sfs.binweevils.app`, `web.binweevils.app`, `cdn.binweevils.app`
- Raw files: stored locally untracked; only sanitized derivatives are committed below
- Provenance: this is OBSERVED behaviour from a third-party server, not canonical Bin Weevils data

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

## UNIQUE HTTP ENDPOINT INVENTORY

### New routes observed not in our previous audit

| Path | Method | Notes |
|---|---|---|
| `/php2/weevil/setLevelColour.php` | POST | LEVEL STAR COLOUR system |
| `/php2/backdrops/getOwnedBackdrops.php` | GET | Backdrop shop |
| `/php2/backdrops/getShopItems.php` | GET | Backdrop shop |
| `/php2/backdrops/getUnlockableBackdrops.php` | GET | Backdrop shop |
| `/php2/weevil/setPreferredMap.php` | GET/POST | Preferred map selection |
| `/php2/weevil/getStaffBadges.php` | GET | Staff badge display |
| `/php2/weevil/getTokens.php` | GET | Token/currency balance |
| `/php2/time.php` | GET | Server time variant |
| `/php2/rewards/submitCodes.php` | POST | Mystery code redemption |
| `/php2/rewards/getCodes.php` | GET | Mystery code list |
| `/php2/social/getAlerts.php` | POST | Social alerts feed |
| `/php2/ads/getadpaths.php` | POST | Ad paths |
| `/php2/smartfox/getActiveZones.php` | GET | Zone probe |
| `/php2/smartfox/getBuddyCount.php` | GET | Buddy count |
| `/php2/nest/addItemToNest.php` | POST | Nest item placement |
| `/php2/nest/getStoredGardenItemsAndSeeds.php` | POST | Garden storage |
| `/php2/nest/buyRoom.php` | POST | Room purchase |
| `/php2/nest/getGardenOfTheWeek.php` | GET | GOTW display |
| `/php2/shop/departmentStore/getAvailablePalettes.php` | GET | Department store UI |
| `/php2/shop/departmentStore/getShopItems.php` | GET | Department stock |
| `/php2/shop/departmentStore/buyItem.php` | POST | Department purchase |
| `/php2/loyalty/getProgress.php` | POST | Loyalty card progress |
| `/php2/loyalty/getStamp.php` | POST | Loyalty stamp |
| `/php2/loyalty/getVouchers.php` | GET | Loyalty vouchers |
| `/php2/shop/getHaggleItems2.php` | POST | Haggle Hut stock |
| `/php2/shop/getHagglePrices.php` | POST | Haggle Hut pricing |
| `/php2/shop/sellHaggleItems.php` | POST | Haggle Hut sell |
| `/php2/mushrooms/collect-mushroom.php` | POST | Mushroom collection |
| `/php2/magazines/read/getRandomList.php` | GET | Magazine system |
| `/php2/magazines/read/getIssueForDisplay.php` | GET | Magazine display |
| `/php2/adcampaigns/competitions.php` | GET | Competitions |
| `/php/battleOfTheBin/getWinner.php` | GET | Battle of the Bin |
| `/php/battleOfTheBin/getMyWeeklyKOBStats.php` | GET | Weekly KOB stats |
| `/php/battleOfTheBin/getWeeklyKOBStats.php` | GET | Weekly KOB global |
| `/php/getMultiplayerGameList.php` | GET | Multiplayer lobby |
| `/php/getGameList.php` | GET | Game list |
| `/php/getFile.php` | GET | Generic asset proxy |
| `/php/getDefaultLocID.php` | GET | Default location |
| `/php/hardestTrackUnlocked.php` | GET | Track unlock state |
| `/php/isTrackUnlocked.php` | GET | Track unlock check |
| `/php/getTreasureHunt.php` | GET | Treasure hunt bootstrap |
| `/php/getWordSearchProgress.php` | GET | Word search state |
| `/php/saveWordSearchProgress.php` | POST | Word search save |
| `/php/getCrosswordProgress.php` | GET | Crossword state |
| `/php/saveCrosswordProgress.php` | POST | Crossword save |
| `/php/saveTycoonBusinessState.php` | POST | Tycoon persistence |
| `/php/submitTycoonBusinessName.php` | POST | Tycoon naming |
| `/php/userFoundItem.php` | POST | Treasure/found item |
| `/php/updateItemPosition.php` | POST | Nest item position |
| `/php/setLocColour.php` | POST | Location colour |
| `/php/addItemToNest.php` | POST | Add nest item |
| `/php/enterParty.php` | POST | Party entry |
| `/php/incrPartyTime.php` | POST | Party timer |
| `/php/incrPartyTime.php` | POST | Party dwell |
| `/php/getPuzzleList.php` | GET | Puzzle list |
| `/php/getTycoonRatings.php` | GET | Tycoon ratings |
| `/php/getTrackDetails.php` | POST | Track metadata |
| `/php/getSpecialMoves.php` | POST | Special moves |
| `/php/getIgnoreList.php` | POST | Ignore list |
| `/php/weevil/data` | POST | Weevil data cache |
| `/weevil/remaining-revenue` | GET | Tycoon revenue |
| `/weevil/buy-food` | POST | Food purchase |
| `/weevil/update-stats` | POST | Stat update |
| `/weevil/add-ignore-list` | POST | Ignore add |
| `/weevil/remove-ignore-list` | POST | Ignore remove |
| `/nest/get-weevil-stats` | POST | Nest stats signed |
| `/nest/get-nest-state` | POST | Nest state |
| `/nest/getconfig` | POST | Nest config XML |
| `/nest/update-fuel` | POST | Fuel update |
| `/nest/level-up` | GET | Level up reward |
| `/nest/rate-nest-room` | POST | Room rating |
| `/nest/get-rooms-rated-today` | GET | Rated rooms |
| `/quests/get-quest-data` | GET | Quest data |
| `/quests/task-completed` | POST | Task completion |
| `/site/server-time` | GET | Server time |
| `/game/submit-single` | POST | Generic game submit |
| `/game/start-race` | POST | Kart race start |
| `/game/submit-race` | POST | Kart race result |
| `/game/submit-trial` | POST | Kart time trial |
| `/game/brain-info` | GET | Brain Strain bootstrap |
| `/game/brain-submit` | POST | Brain Strain score |
| `/php2/leaderboards/games/getPlayerHighScore.php` | GET | Leaderboard read |
| `/php2/leaderboards/games/submitScore.php` | POST | Leaderboard write |
| `/php2/weevil-kart/submit-single-user-time.php` | POST | Kart time |
| `/php2/vod/getVODInfo.php` | GET | VOD catalogue |
| `/php2/userFoundTreasure.php` | POST | Treasure Hunt |

## NEW ROUTES

Level colour, backdrop shop, preferred map, staff badges, tokens, magazines, Battle of the Bin, harder track unlocks, getGameList, getMultiplayerGameList, userFoundItem, saveTycoonBusinessState, submitTycoonBusinessName, getWordSearchProgress, saveWordSearchProgress, getCrosswordProgress, saveCrosswordProgress, getDefaultLocID, mushroom collect, ad campaign/competitions endpoints.

## ALREADY-KNOWN ROUTES CONFIRMED

Most previously identified routes were hit in this capture: login/logout, get-login-details, getData, getScaledWeevils, getUserPets, getPetSkills, getCompletedAchievements, getNewAchievements, getAllAchievements, getDefs, getBinPosts, getAlerts, reportWeevil, partyRoomDwell, getStoredItems, removeItemFromNest, addItemToNest, level-up, incrPartyTime, enterParty, getMyLottoTicketsAndDrawDate, getUncashedTickets, getJackpotSize, addLottoTicket, getPastLottoDraws, getLottoDrawWinners, cashInTickets, brain-info, brain-submit, submit-single, start-race, submit-race, submit-trial, getHaggleItems2, getHagglePrices, sellHaggleItems, getCodes, submitCodes, getVODInfo, getTrackDetails, getIgnoreList, getSpecialMoves, remaining-revenue, buy-food, update-stats, add-ignore-list, remove-ignore-list, server-time, get-my-apparel.

## CONTRACT DIFFERENCES VS OUR PROJECT

- `quests/task-completed`: observed sending `taskID,userID,st,hash` via PHPcall secure form. Our local file does not validate hash/timer/st and unconditionally reads `questID`. This is a confirmed mismatch.
- `nest/getconfig`: observed sending `id,st,hash` with manual hash. Our local implementation follows this.
- `game/brain-submit`: observed sending `score,levels,st,hash`. Levels is serialized `levels=%2C1%7C0%2C3%7C0...`. Our local file expects this.
- `php2/shop/getHagglePrices.php`: observed sending `items,seeds,gardenItems,timer,hash`. Our local file exists.
- `php2/rewards/submitCodes.php`: observed sending `code,userIDX,timer,hash,valid`. Our local file exists.
- `php2/weevil/setLevelColour.php`: NEW route, not in our local tree.
- `php2/backdrops/*`: NEW route family, not in our local tree.

## RESPONSE BODIES ACTUALLY CAPTURED

1. `POST /php2/pets/getUserPets.php` → `{"pets":[],"responseCode":1}`
2. `GET /php/getMyLottoTicketsAndDrawDate.php` → `responseCode=1&nextDraw=2026-09-04+17%3A00%3A00&drawID=420&gotTicket=0&tickets=&b=r`
3. `POST /php/incrPartyTime.php` → `responseCode=1`

## RESPONSE BODIES MISSING

All other priority endpoint response bodies were NOT captured in the HAR. This includes:
- setLevelColour success/failure response
- backdrop shop/owned/unlockable catalogue responses
- backdrop purchase/equip responses
- brain-submit reward response
- brain-info modes/levels response
- getAlerts response schema
- getHaggleItems2 / getHagglePrices responses
- getCodes / submitCodes responses
- getCompletedAchievements / getNewAchievements / getAllAchievements responses
- getData response shape on this server
- get-login-details response fields
- getQuestData response
- task-completed reward response
- submit-single / start-race / submit-race responses
- remaining-revenue response
- buy-food response

## SWFS / ASSETS DISCOVERED

### Level colour / stars
- `/assetsbackdrops/glowingStars.swf` (5 requests, no body)
- `/assetsbackdrops/level0.swf`, `level10.swf`, `level20.swf`, `level30.swf`, `level40.swf`, `level50.swf`, `level60.swf`, `level70.swf`, `level80.swf`, `level90.swf`

### Backdrops
- `/assetsbackdrops/default.swf`
- `/assetsbackdrops/glowingStars.swf`
- `/assetsbackdrops/doshpalace.swf`, `dosh.swf`, `doshgold.swf`
- `/assetsbackdrops/enchantedforest.swf`, `tetris.swf`, `spaceinvaders.swf`
- `/assetsbackdrops/scarletcurtainfortress.swf`, `ribbonsblue.swf`, `ribbonsred.swf`
- `/assetsbackdrops/icecream.swf`, `cupcake.swf`, `clubfling.swf`
- `/assetsbackdrops/tinksforestview.swf`, `nestswithmountains.swf`, `neststreetentrance.swf`
- `/assetsbackdrops/japaneselake.swf`, `starburst1.swf`, `space.swf`
- `/assetsbackdrops/pink.swf`, `coral.swf`, `underwater.swf`, `seafloor.swf`, `aquaticlife.swf`
- `/assetsbackdrops/fumstower.swf`, `islandscenery.swf`, `paintersstudiosplash.swf`
- `/assetsbackdrops/bluepower.swf`, `orangepower.swf`, `purplepower.swf`
- `/assetsbackdrops/shiningstars.swf`, `lushlandscape.swf`, `sweetnessredefined.swf`
- `/assetsbackdrops/shiningstarsred.swf`, `shiningstarspurple.swf`, `shiningstarspink.swf`
- `/assetsbackdrops/shiningstarsorange.swf`, `shiningstarsgreen.swf`, `shiningstarsblack.swf`, `shiningstarsblue.swf`
- `/assetsbackdrops/rainbowlake.swf`, `pride.swf`, `priderainbowstars2.swf`
- `/assetsbackdrops/tikiisland.swf`, `summersunset.swf`, `sunnyretreat.swf`, `beach.swf`
- `/assetsbackdrops/neststreetscenery.swf`, `nighttimetiki.swf`
- `/assetsbackdrops/flums.swf`, `flums_prev.swf`
- `/assetsbackdrops/binscape.swf`, `white.swf`, `black.swf`
- `/assetsbackdrops/jungle.swf`, `scribblesoldbg.swf`
- `/assetsbackdrops/doshpalaceinside.swf`, `rocknroll.swf`, `sws.swf`
- `/assetsbackdrops/nightpettemple.swf`, `mulchisland.swf`, `booster.swf`
- `/assetsbackdrops/bunty.swf`, `pumpkin1.swf`, `hex.swf`
- `/assetsbackdrops/tulip*.swf` (red, midnight, blue, orange, heart, polka, rainbow, gold, black, sunset, pink, violet, white, chocolate, green)
- `/assetsbackdrops/garden.swf`, `garden_prev.swf`, `mulchislandocean.swf`
- `/assetsbackdrops/labslab.swf`, `doshvault.swf`, `doshceleb.swf`
- `/assetsbackdrops/clott.swf`, `lab.swf`, `posh.swf`, `big_weevil.swf`, `bling.swf`, `blingwithered.swf`
- `/assetsbackdrops/bubblepink.swf`, `tinksblocks.swf`
- `/assetsbackdrops/bnfeatured.swf`, `gotwfeatured.swf`, `nestofthemonth.swf`, `botb.swf`
- `/assetsbackdrops/bwe_fling.swf`, `bwe_mudd.swf`, `bwe_posh.swf`
- `/assetsbackdrops/roses2.swf`, `petalperfection.swf`
- `/assetsbackdrops/hearts.swf`, `undertheocean.swf`
- `/assetsbackdrops/tinksinner.swf`, `deepsea.swf`
- `/externalUIs/shops/backdropUI_230425b.swf`
- `/assetsBackdrops/glowingStars.swf`

### Pets
- `/weevilPet_assets_210225.swf`
- `/weevilPet_assets_200826.swf`
- `/users/f_petBowl_pink.xml`, `.swf`, `_thumb.swf`
- `/users/f_petbasket2.xml`, `.swf`, `_thumb.swf`
- `/users/f_binpet_totem.xml`, `.swf`, `_thumb.swf`
- `/users/f_binpet_treatbox.xml`, `.swf`, `_thumb.swf`
- `/users/f_lightshapes_binpet.xml`, `.swf`, `_thumb.swf`
- `/users/f_tropical_scratching_post.xml`, `.swf`, `_thumb.swf`
- `/users/f_binpet_toyhouse1.xml`, `.swf`, `_thumb.swf`
- `/users/f_tropical_binpethut.xml`, `.swf`, `_thumb.swf`
- `/users/f_bppvipgold_woolmousetoy.xml`, `.swf`, `_thumb.swf`
- `/fixedcam/binpetparadise_landingarea_left.swf`
- `/fixedcam/binpetparadise_landingarea_right.swf`
- `/fixedcam/binpetparadise_gym_21_05_14.swf`
- `/overlayUIs/bpp_changeroverlay.swf`
- `/overlayUIs/bpp_studiooverlay.swf`
- `/fixedCam/weevilPost_020217.swf`

### Mulchtastic / Brain Strain
- `/multiplayergames/weevilkart/assets3d/mould3.swf`
- `/multiplayergames/weevilkart/assets3d/mouldonstick3.swf`
- `/multiplayergames/weevilkart/assets3d/rock3.swf`
- `/assets3d/mould3.swf`, `mouldonstick2.swf`, `mulch1.swf`, `mulch2.swf`, `mulch3.swf`, `mulch4.swf`
- `/assets3d/connectmulch.swf`, `flipmulch.swf`
- `/externaluis/brainstrain_080225a.swf`
- `/externaluis/braintrainingconfig_28_09_11.xml`
- `/externaluis/brainmenu_28_09_11.swf`
- `/externaluIs/brainstrainquestions/sounds/*.mp3` (many)
- `/externaluis/brainstrainquestions/*.swf` (many question modules)
- `/overlayUIs/leveluprewards.swf`

### Lotto
- `/externalUIs/lotto_200423.swf`

### Shops / Items
- `/externaluis/shops/nestco/nestco_290726.swf`
- `/externaluis/shops/nestco/detailnestitem_290924.swf`
- `/fixedcam/shoppingmall_exterior_081124a.swf`
- `/fixedcam/shoppingmall_inside_141123.swf`
- `/fixedcam/deptstore_nestco_03_04_21.swf`
- `/fixedcam/pipeNest_haggleHut_290825.swf`
- `/externaluis/shops/haggleHut_04_08_25.swf`
- `/overlayUIs/haggleHutOverlay_040825b.swf`
- `/fixedcam/figgscafe_terrace_050924a.swf`
- `/fixedcam/clubfling_exterior_041025.swf`

### Social / Buddy
- `/buddies/buddyfeed_120726.swf`
- `/buddies/buddypanel_17_11_25.swf`
- `/binbadges/achievementalertsmanager4.swf`
- `/binbadges/bb13.swf`, `bb12.swf`, `bb_codecracker.swf`
- `/news/web/*.png` (buddy news tablet adverts)

### Missions / Misc
- `/externaluis/gameintro/newuser_260622a.swf`
- `/newusertutorial/tutorial3_301224.swf`
- `/externaluis/changeweevil_11_03_2025b.swf`
- `/externaluis/comps/competitions_index.swf`
- `/externaluis/comps/gotw_gongs.swf`
- `/externaluis/magazineviewer_300625.swf`
- `/externaluis/photoshop_19_02_13.swf`
- `/externaluis/mysterycodemachine_14_12_25.swf`

### Maps / Rooms
- `/externaluis/map/map_2013_151125.swf`
- `/externaluis/map/map_2010_151125.swf`
- `/externaluis/map/map_2016_151125.swf`

### Tycoon / Diner
- `/assetstycoon/homecinema_110416.swf`
- `/fixedcam/diner_041121a.swf`

### Weevil Wheels / Kart
- `/multiplayergames/weevilkart/weevilwheelsloader_22_01_16.swf`
- `/multiplayergames/weevilkart/weevilkart_27_06_26.swf`
- `/multiplayergames/weevilkart/tracks/dirtvalley_1.swf`, `_bg.swf`, `_img.jpg`
- `/multiplayergames/weevilkart/assets3d/nut2.swf`
- `/externaluis/weevilwheels/*` implied by loader

### Misc assets
- `/assets3d/apparel_*.swf`, `nest.swf`, `fountainbase.swf`, `hill.swf`
- `/assets3d/columnorb.swf`, `squares.swf`, `rainbowcol.swf`, `rainbow.swf`
- `/assets3d/clubfling.swf`, `riggs.swf`, `figgs.swf`, `weevilpost.swf`
- `/assets3d/toiletbrush1.swf`, `toiletbrush2.swf`, `mulchmound1.swf`
- `/assets3d/orangesegment1.swf`, `orangesegment2.swf`
- `/assets3d/pooltable.swf`, `fountaincoltop.swf`
- `/assetsnest/nesthall_2016.swf`, `nestroom4_140116.swf`
- `/fixedcam/labslab_250826.swf`, `neststreet_090826.swf`
- `/fixedcam/flemmanor_dynamads_051121.swf`
- `/fixedcam/partybox/beachnew/partyboxopen_beachparty.swf`
- `/fixedcam/rumsairport_databaseads_200625.swf`
- `/fixedcam/dirtvalley1_dynamads_27_06_22.swf`
- `/fixedcam/tinkstree_221122a.swf`
- `/fixedcam/riggspalladium_270622a.swf`
- `/loadercontent/adcampaigns/loaderpage_labslab_02.swf`

## SWFS / ASSETS DOWNLOADED

None downloaded in this pass. All SWF assets in the HAR are URL references without embedded bodies. Follow-up download required.

## LEVEL STAR COLOUR SYSTEM

### ORIGINAL LIVE SERVER BEHAVIOUR

Observed endpoint: `POST /php2/weevil/setLevelColour.php`

Request parameters observed:
- `userIDX=392817`
- `level=1`
- `timer=387585`
- `hash=78a0df2fe251ae3f5b964c3848e30a74`

Hash appears to cover `userIDX,level,timer` in PHP2 alphabetised order.

Response body: NOT CAPTURED in HAR.

Additional evidence:
- Backdrop SWF `/assetsbackdrops/glowingStars.swf` requested repeatedly (5 times), suggesting it is the level-star colour asset.
- Backdrop SWFs `/assetsbackdrops/level0.swf` through `level90.swf` were also requested, indicating level-based unlocks or previews.

Unknowns:
- Exact success/failure response contract
- Available colour IDs/names
- Level thresholds for each colour
- How current colour is read back (likely via `getData` or similar)
- Whether colour change costs currency or is free

### POSSIBLE FUTURE XP-SHOP MAPPING

Separate from original behaviour. If mapped to XP shop later, colour unlocks could follow lifetime-XP thresholds. No implementation now.

## PLAYER-CARD BACKDROP SHOP

Observed endpoints:
- `GET /php2/backdrops/getOwnedBackdrops.php`
- `GET /php2/backdrops/getShopItems.php`
- `GET /php2/backdrops/getUnlockableBackdrops.php`
- `GET /externalUIs/shops/backdropUI_230425b.swf`

Request contracts: not fully captured. No POST purchase/equip endpoints observed in this session.

Backdrop SWF catalogue observed: 80+ distinct backdrop SWFs in `assetsbackdrops/`.

Unknowns:
- Purchase route and parameters
- Equip/select route and parameters
- Success/failure response codes
- Currency used (Dosh? Mulch? tokens?)
- Price/level/membership requirements
- How equipped backdrop appears in public profile/player card response

## BIN PETS

Observed:
- `POST /php2/pets/getUserPets.php` returned `{"pets":[],"responseCode":1}` for this account.
- Pet asset SWFs loaded: `weevilPet_assets_210225.swf`, `weevilPet_assets_200826.swf`
- Pet furniture items loaded: pet bowls, baskets, totems, treat boxes, scratching posts, toy houses, huts, woolly mouse toy
- Bin Pet Paradise rooms observed in SFS room list: `binPetChanger`, `bppLandingRight`, `bppLandingLeft`, `BinPetsShop`, `BinPetsShop2`, `bppGym`, `SummerFairRoom1`
- Pet-related fixedcams: `binpetparadise_landingarea_left`, `_right`, `_gym`
- Overlay UIs: `bpp_changeroverlay.swf`, `bpp_studiooverlay.swf`

No pet skill/trick/update traffic captured in this session. The account had no pets.

## MULCHTASTIC

Observed:
- `GET /game/brain-info` (no body)
- `POST /game/brain-submit` with `score=20, levels=%2C1%7C0%2C3%7C0..., st, hash`
- Mulch coin pile thumbnails loaded from user content
- Mulchtastic.png buddy news image

No reward response captured. Score submission observed but result body missing.

## LOTTO

Observed:
- `GET /php/getMyLottoTicketsAndDrawDate.php` → `responseCode=1&nextDraw=2026-09-04+17%3A00%3A00&drawID=420&gotTicket=0&tickets=&b=r`
- `GET /externalUIs/lotto_200423.swf`
- `GET /php/getUncashedTickets.php` (no body)
- `POST /php/getJackpotSize.php` (no body)
- `POST /php/addLottoTicket.php` (no body)

No ticket purchase, claim, or draw-result response captured.

## MISSIONS

No mission-related HTTP traffic captured in this session. The account may not have active missions.

## LOYALTY

Observed:
- `GET /externalUIs/loyaltyCard_10_01_25.swf`
- `POST /php2/loyalty/getProgress.php` (no body)
- `GET /externalUIs/loyaltyCards/loyaltyCard1.swf`
- `POST /php2/loyalty/getStamp.php` (no body)
- `GET /php2/loyalty/getVouchers.php` (no body)

No response bodies captured.

## HAGGLE HUT

Observed:
- `GET /fixedCam/pipeNest_haggleHut_290825.swf`
- `GET /externalUIs/shops/HaggleHut_04_08_25.swf`
- `GET /overlayUIs/haggleHutOverlay_040825b.swf`
- `POST /php2/shop/getHaggleItems2.php` (no body)
- `POST /php2/shop/getHagglePrices.php` with `items=8430248%2C8430228, timer, hash` (no body)
- `POST /php2/shop/sellHaggleItems.php` (no body)

## SOCIAL / PROFILE

Observed:
- `POST /php2/social/getAlerts.php` x23 (no body)
- `POST /php2/social/getDefs.php` (not separately confirmed in body)
- `GET /buddies/buddyfeed_120726.swf`
- `GET /buddies/buddypanel_17_11_25.swf`

No buddy message/conversation traffic observed in HTTP. WebSocket layer handles social commands.

## REWARDS / MINIGAMES

Observed:
- `GET /overlayUIs/levelUpRewards.swf`
- `GET /php2/rewards/getCodes.php` (no body)
- `POST /php2/rewards/submitCodes.php` with `code=63, userIDX=392817, timer, hash, valid=1` (no body)

## SAFE TO IMPLEMENT NOW

None. No new endpoint has both request AND response contract fully captured.

## READY WITH BLOCKER

- `php2/weevil/setLevelColour.php` — request contract exact, response body missing
- `php2/backdrops/getOwnedBackdrops.php` — endpoint confirmed, response body missing
- `php2/backdrops/getShopItems.php` — endpoint confirmed, response body missing
- `php2/backdrops/getUnlockableBackdrops.php` — endpoint confirmed, response body missing

## NEEDS LIVE RECON

- Level colour: capture success response, failure response, current-equipped readback
- Backdrop shop: capture buy/equip responses, currency field, requirements
- Brain Strain: capture reward response fields (`mulchEarned,xpEarned,mulch,xp,modes,ave,high,levels`)
- Lotto: capture jackpot, buy ticket, uncashed tickets, past draws, winners, claim
- Mulchtastic: capture full reward response on win/lose
- Loyalty: capture progress/stamp/voucher response bodies
- Haggle Hut: capture item list, price list, sell response
- Rewards: capture codes list and submit response
- Social: capture alerts response schema
- Missions: trigger mission help/purchase flow and capture
- Pets: capture profile/skills/tricks with a pet-owning account
- getData: capture full response on this server for comparison
- get-login-details: capture response body

## LIVE RECON FOLLOW-UP ACTIONS

### LEVEL COLOUR
1. Relog and capture `getData` / current colour field
2. Send `setLevelColour.php` with valid hash for unlocked colour; capture success response
3. Send `setLevelColour.php` for locked colour; capture failure response
4. Relog and verify colour persists

### BACKDROP SHOP
1. Open player card / profile and capture equipped backdrop identifier
2. Call `getOwnedBackdrops.php`; capture response
3. Call `getShopItems.php`; capture response
4. Call `getUnlockableBackdrops.php`; capture response
5. Buy an affordable backdrop; capture purchase response
6. Equip the backdrop; capture equip response
7. Relog, reopen player card, verify equipped value

### MULCHTASTIC
1. Start Brain Strain; capture `brain-info` response
2. Submit a low score; capture `brain-submit` reward response
3. Submit a high score; capture reward response
4. Repeat to capture cooldown/no-reward response

### LOTTO
1. Before ticket: capture `getMyLottoTicketsAndDrawDate.php`
2. Obtain ticket via `addLottoTicket.php`; capture response
3. After draw: capture `getUncashedTickets.php`
4. Claim via `cashInTickets.php`; capture response

### BIN PETS
1. Open pet profile; capture `getPetProfile` response
2. Adopt/feed pet; capture each response
3. Perform trick/skill update; capture each response
4. Relog; capture `getUserPets` and `getPetSkills`

### MISSIONS
1. Open mission panel; trigger `getRoomHelp.php`; capture response
2. Buy help; capture `buyHelp.php` response
3. Buy mission; capture `buyMission.php` response

### LOYALTY
1. Open loyalty card; capture `getProgress.php` response
2. Stamp; capture `getStamp.php` response
3. Claim final reward; capture `finalReward.php` response
4. Check vouchers; capture `getVouchers.php` response

## RECOMMENDED IMPLEMENTATION ORDER

1. Level colour (request proven, response one capture away)
2. Backdrop shop catalogue endpoints (request proven, response one capture away)
3. Brain Strain reward contract (one play capture away)
4. Lotto full flow (requires multiple captures)
5. Loyalty full flow
6. Haggle Hut full flow
7. Missions full flow
8. Bin Pets full integration after separate package review
