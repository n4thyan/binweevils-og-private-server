# Website Asset Provenance — `website-redesign`

Records the major **authentic / recovered Bin Weevils** visual assets used to build the
redesigned public website, so the result is demonstrably built from original material rather
than fabricated artwork. Only major components are documented (not every blade of grass).

> Rule followed: official/recovered assets only. No AI-generated graphics, no generic stock,
> no invented "Bin Weevils-style" icons. Where an exact asset was unavailable, a neutral
> structural CSS placeholder was used and noted.

## Logo / masthead
- **File:** `game-full/assets/images/logo2.png`
- **What:** the original bubbly "Bin Weevils" slime-wordmark (white "Bin" + lime "Weevils",
  stylized slime-W), no "Rewritten" text.
- **Used in:** `site/header.php` masthead (`<img src="/assets/images/logo2.png">`).
- **Replaces:** the old `logo.png` (Bin Weevils **Rewritten** green-square mark), which is no
  longer referenced by the active shell.

## Background / environment
- **File:** `game-full/assets/images/background.png`
- **What:** the Bin Weevils Garden / Dump panoramic scene (sky, clouds, grass, trees, distant
  buildings). Used as the full-page site background (`.bw-page-shell` / `html,body`).

## Hero scene
- **File:** `game-full/assets/images/banner.png`
- **What:** the Weevil World hub panorama (castle, mushroom houses, wishing tree). Used as the
  illustrated hero band (`.bw-hero-scene`) above the login/hero columns.

## Mascot
- **File:** `game-full/assets/images/rigg.png`
- **What:** Rigg weevil character cutout (hard hat). Used as the homepage hero mascot
  (`.bw-characters`).

## Call-to-action button
- **File:** `game-full/assets/images/play-now.png`
- **What:** the authentic "PLAY NOW!" blue glossy button art. Used as the hero Play Now CTA
  (`.bw-play-now`).

## Section / feature art
- `game-full/assets/images/racing.png` — racing/vehicle promo (Play card)
- `game-full/assets/images/nest.png` — nest/home art (Community card)
- `game-full/assets/images/garden.png` — garden art (Create a Weevil card)

## Typography
- **Font:** **Burbank Small** (Bold + Medium), recovered from `game-full/assets/fonts/`
  (`burbank-small-bold.otf`, `burbank-small-medium.otf`).
- **What:** the original Bin Weevils site/UI brand typeface. Wired via `@font-face`
  (`Burbank Small`) and applied site-wide in `site-redesign.css`. This replaces the earlier
  generic system font stack.

## Advertisements
- **Source:** user-supplied advert pack (4 promo videos + 2 empty slot frames).
- **Video creatives:** `game-full/assets/ads/bw-ad-1..4.mp4` (Bin Weevils promo videos).
- **Frames (reference only):** `game-full/assets/ads/bw-ad-frame-leaderboard.png`,
  `bw-ad-frame-banner.png` — these are empty solid-gray slot chrome with no transparent
  cutout, so they cannot wrap a video without obscuring it; kept as provenance, the videos
  are shown inside the authentic wood/gold slot frame instead.
- **Wiring:** `site/config.php['ad_creatives']` → `site_ad_slot()` (top leaderboard +
  right-rail sidebar). The off-topic "Weevil World membership" banner was removed.

## Authentic assets deliberately NOT used
- `logo.png` (Rewritten mark) — replaced by `logo2.png`.
- `assets/img/logo.png`, `assets/img/binweevils/logo_on_gradBG.png` — alternate wordmarks;
  `logo2.png` chosen as the cleanest masthead.
- `advertisement_frame*.png` — empty placeholder chrome (see Advertisements above).

## Still worth considering (not yet used)
- `gold-navbar.png` / `navbar.png` — blank green/gold glossy capsules; candidates for an
  authentic nav-button background if the nav is restyled.
- `gold-logo.png` — gold wordmark variant (alt masthead option).
- Promo/sign SWFs inside `game-full/` and the external recovered CDN (e.g.
  `C:\Users\pc\Desktop\Project Binweevils\Bin Weevils Game Assets (1)\cdn.binw.net`) — further
  original buttons/tabs/signs could be decompiled for the secondary-route reskin.
