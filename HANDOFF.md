# HANDOFF.md — Bin Weevils OG Private Server (checkpoint 2026-08-29)

End-of-day checkpoint. Written so tomorrow's session can resume from the **repository + served
`C:\xampp\htdocs`** without relying on compressed conversation memory.

## GIT
- **Active website branch:** `website-redesign`
- **Final polish commit:** `3fd5cc5fc7c405478da419fc418c1cedce929eec`
  - message: `fix(web): fullscreen scaling, front-facing Weevil poses, live stat refresh, CTA/online-count cleanup`
- **Pushed:** yes — `origin/website-redesign` == local (`3fd5cc5f`).
- **Prior commits this evening:** `06311be0` (polish docs), `9c507690` (final polish pass), `082d329d`, `008748f7`, `6fb021e8` (main.swf swap), `b56740e1` (weevil-creator), `4873b1b9`, `d3a91a48`, `5056e564`, `fb2324b4`, `6725384d`.
- **Subtrees intact:** `game-full/` 22675, `server/` 35, `electron/` 10.
- **`main`:** untouched.
- **`feature/room-events-mushrooms`:** deliberately left alone (staged/uncommitted work not touched). Only the `website-redesign` ref was moved by overlay commits.

## WHERE THINGS LIVE
- Repo website source is tracked at the **repo root** on `website-redesign`: `index.php`,
  `assets/`, `site/`, `settings/`, `game.php`, `register/`, `community/`, `weevil-creator/`,
  `mainDEV663.swf`.
- `C:\xampp\htdocs` is a deployed copy of that root tree (the live site).
- `game-full/` in-repo is the **legacy** original site and is NOT what htdocs serves.
- All key htdocs files were verified byte-identical to `website-redesign` at checkpoint time —
  no orphan finished work remained uncommitted.

## CURRENTLY IMPLEMENTED (website redesign)
| Area | Status |
|------|--------|
| Design system (centred shell, garden bg, green nav, Burbank Small, authentic art) | DONE |
| Public homepage | DONE |
| Advert system (leaderboard/rectangle/skyscraper, no layout shift, side ads <1723px) | DONE (headless-verified) |
| Logged-in homepage (rendered Weevil, "Your Weevil" panel, no server dup) | DONE |
| Play page (HEADER/GAME/FOOTER, native 940×653, snug dark frame, ⛶ fullscreen icon bottom-right) | DONE |
| Fullscreen (100vw×100vh, explicit height so Flash object fills, dark letterbox, wrapper-only, ⛶ toggles enter/exit) | DONE (code) — NEEDS real-client visual confirm |
| Weevil renderer — front-facing pose (yaw 0) for hero/panel/My Weevil; header avatar = head-shot crop | DONE (verified headless) |
| Dynamic stats (poll /site/account-state.php 20s + focus + visibilitychange; updates all [data-account-stat]) | DONE (endpoint verified) — NEEDS real gameplay delta test |
| CTA cleanup (homepage 'Your Weevil' panel dup buttons removed; single 'View My Weevil' link) | DONE |
| Online count (header right cluster, real /site/server-status.php source) | DONE |
| Public copy audit (no dev wording; 'private server' removed from Bin Bulletin) | DONE |
| Advert system (top leaderboard, home rectangle, side skyscrapers 300×600; page banners on Create a Weevil + Download) | DONE (creatives serve 200; side rails visible >=1724px) |
| Weevil renderer (reused `weevil-creator`, account def authoritative) | DONE |
| main.swf background bitmap swap (DefineBitsJPEG2 111 → Garden art) | DONE — NEEDS full client test |
| xat / Community | NEXT / NOT IMPLEMENTED (shell only) |
| Flash transparency | OPTIONAL EXPERIMENT / NOT IMPLEMENTED |
| Lifetime-XP leaderboard | POST-RELEASE (after website redesign) |
| Account ↔ xat cosmetic identity | ROADMAP / FUTURE |

## MAIN SWF BACKGROUND CHANGE (record for tomorrow)
- Live SWF: `mainDEV663.swf` (htdocs root).
- JPEXS/FFDec target: `DefineBitsJPEG2 (characterId 111)`, native bitmap **1245×840**.
- Replacement: authentic Garden background `assets/images/background.png` resized to 1245×840.
- Backup (untouched): `C:\xampp\htdocs\main.pre-background-swap-20260829-164658.swf`
  SHA-256 `62fe3ac2001f60ca1c2b02492a578eb24f0f8df9904cccaf3c9aea0a7263069f` (775,949 bytes).
- Edited SWF SHA-256 `33176e9fe9e497f2b7546f7e0a2c4b57dd9eb4f46bad64878821c6330a5b84b8`
  (649,017 bytes). Tag count 1696 unchanged; only bitmap payload differs. Commit `6fb021e8`.
- Do NOT reopen the transparency experiment yet.

## NEEDS TESTING TOMORROW (manual, in real Electron/Flash client)
1. Homepage logged out · 2. adverts render · 3. adverts rotate w/o layout shift · 4. Create a
Weevil · 5. registration · 6. existing-account login · 7. Remember Weevil Name · 8. auth header ·
9. logged-in homepage · 10. account Weevil renders · 11. My Weevil · 12. XP/currencies correct ·
13. Settings · 14. Play page · 15. game viewport sizing · 16. Fullscreen button · 17. exit
fullscreen · 18. server selector · 19. Mulch server · 20. enter game · 21. nest/home room ·
22. game controls/navigation · 23. logout · 24. login again · 25. responsive/narrow check.

Fix only genuine observed failures — no speculative changes.

## NEXT
1. xat / Community Chat embed (configure the intended xat room, fit site design, keep auth nav).
2. Test xat.

## ROADMAP LATER
- Account ↔ xat cosmetic identity (name colour / title / level / prestige badge from the account).
- Lifetime-XP leaderboard (ranks by **lifetime** XP, not spendable Banked XP) — POST-RELEASE.
- Optional Flash transparency experiment — on a COPY of the known-good SWF only.

## SAFETY (confirmed at checkpoint)
- Backend/game/server branches (`main`, `server/`, `game-full/`, `electron/`) untouched by
  website work.
- No mass deletions: every website commit used the safe `read-tree` overlay; subtree counts intact.
- htdocs/code state represented on GitHub (`website-redesign`, pushed).
- `feature/room-events-mushrooms` staged work left exactly as found.

## LOCAL STACK (for tomorrow's test)
- Apache :80, MySQL :3306, Node TCP :9339 + WS :2087 (all persistent).
- Electron relaunched as needed: `cd electron && "...\node.exe" node_modules/electron/cli.js .`
- Test login: seeded account `Rick` (sessionKey validated against DB `users.sessionKey`).
- Weevil renderer runtime at `weevil-creator/` must be present in htdocs for My Weevil to draw.
