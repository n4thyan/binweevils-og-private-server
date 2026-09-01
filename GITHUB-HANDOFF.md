
---

## 2026-09-01 Final Website Asset Cleanup & Simplification

### Completed
- Simplified XP Shop catalog from 5 name-colour presets to 1 "Custom Name Colour" unlock (100k Banked XP)
- Removed all profile_background rewards (deferred to future cosmetic feature)
- Settings username colour: added hex colour picker plus live preview with save/reset
- xp-reward-action.php: added colour_hex validation plus meta storage for per-user colours
- cosmetics.php: added meta JSON column support in site_cosmetic_equipped()
- Removed unused background assets (gang, garden, renovation, newsroom, golden, flat, background.jpg, background2.jpg, banner.jpg, logo2.png)
- Recovered/login artwork: Tink_Jump, Tink_Clott, returning-player, three-image-panel, rigg, mulch, dosh, weevil, weevil-tophat
- Register artwork diversified: Tink_Jump.png instead of repeating character assets
- 59 files changed, 801 insertions, 124 deletions vs parent 79f801af

### XP Accounting Safety
- Purchases deduct from Banked XP (xp1) only
- Lifetime XP (xp) never touched
- Schema ready for future lifetime-XP leaderboard

### Next Steps
- User inspection/merge decision on website-redesign branch
- Future: custom background gallery (deferred)
