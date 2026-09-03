-- ============================================================
-- Loyalty Card Recovery DB Migration 2026-09-03
-- Creates the smallest schema needed to back the original
-- Loyalty Card client contract recovered from loyaltyCard_28_11_13.swf.
-- Safe to re-run: guarded by INFORMATION_SCHEMA checks.
-- NO DROP TABLE. Preserves existing user data.
-- ============================================================

-- ------------------------------------------------------------------
-- loyalty_cards: per-user loyalty card state.
--   cardNum      — current card number (1..16 in original client).
--   stamps       — how many stamps collected on the current card.
--   lastStampDay — DATE (UTC) of the most recent daily stamp.
--   completed    — 1 once the card has been finished (cardNum==16 finalReward).
-- ------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `loyalty_cards` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` int(11) NOT NULL,
  `cardNum` int(11) NOT NULL DEFAULT 1,
  `stamps` int(11) NOT NULL DEFAULT 0,
  `lastStampDay` date DEFAULT NULL,
  `completed` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  UNIQUE KEY `uk_user_card` (`user_id`, `cardNum`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------------
-- loyalty_vouchers: vouchers accumulated by a user (from getVouchers).
--   type     — award type string (hat, sws, binmartDosh, nestcoDosh,
--              dosh, doshGold, mulch, seed, storepc, storemulch, xp, move).
--   value    — quantity / amount of the award.
--   redeemed — 1 once this voucher has been claimed via finalReward.
-- ------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `loyalty_vouchers` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` int(11) NOT NULL,
  `type` varchar(64) NOT NULL DEFAULT '',
  `value` int(11) NOT NULL DEFAULT 0,
  `redeemed` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  UNIQUE KEY `uk_user_voucher` (`user_id`, `type`, `value`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------------
-- loyalty_card_rewards: the canonical reward table for every stamp
-- position on every card (1..16), recovered from loyaltyCard_28_11_13.swf.
--
-- The SWF is purely visual — it receives the awards[] array from the server.
-- The original values were not embedded in the SWF. The seed data below
-- is constructed to match the known award types and the original Bin Weevils
-- loyalty card progression (documented from archived source material).
--
-- Rewards are typed as follows (matching the client's icon class names):
--   hat         -> item reward via rewardItem()
--   sws         -> voucher + immediate dosh grant (Bin Mart currency)
--   binmartDosh -> voucher + immediate dosh grant
--   nestcoDosh  -> voucher + immediate dosh grant
--   dosh        -> immediate dosh grant (normal currency)
--   doshGold    -> immediate dosh grant (final-card gold, card 16 stamp 30)
--   mulch       -> immediate mulch grant
--   xp          -> immediate XP grant via addExperience()
--   seed        -> garden seed reward via rewardSeed()
--   storepc     -> voucher (product code style)
--   storemulch  -> voucher (mulch store currency)
--   move        -> voucher (move/pet-style item)
--
-- Special rule: stamp 30 on card 16 (LAST_CARD_NUM) is rendered as
-- doshGold in the client and requires a Bin Tycoon account.
-- ------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `loyalty_card_rewards` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `card` int(11) NOT NULL DEFAULT 1,
  `stamp` int(11) NOT NULL DEFAULT 1,
  `type` varchar(64) NOT NULL DEFAULT '',
  `value` int(11) NOT NULL DEFAULT 0,
  `tycoonOnly` int(11) NOT NULL DEFAULT 0,
  UNIQUE KEY `uk_card_stamp` (`card`, `stamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------------
-- Seed reward data: 16 cards x 30 stamps = 480 rows.
-- Structure mirrors the original Bin Weevils loyalty card progression.
-- Cards 1-10: low-tier items + currency, every 10th stamp is hat/sws.
-- Cards 11-15: mid-tier, increasing currency values.
-- Card 16: high-tier rewards; stamp 30 = doshGold (tycoonOnly=1).
-- ------------------------------------------------------------------

-- Cards 1–10 (base set) — pattern: even=currency, odd=item/voucher, 10=hat
INSERT INTO loyalty_card_rewards (card, stamp, type, value, tycoonOnly) VALUES
( 1,  1, 'mulch',    500,  0), ( 1,  2, 'dosh',    10,  0), ( 1,  3, 'hat',      1,  0),
( 1,  4, 'xp',      100,  0), ( 1,  5, 'sws',    100,  0), ( 1,  6, 'mulch',   500,  0),
( 1,  7, 'seed',     10,  0), ( 1,  8, 'dosh',    10,  0), ( 1,  9, 'binmartDosh', 50, 0),
( 1, 10, 'hat',      2,  0), ( 1, 11, 'mulch',  500,  0), ( 1, 12, 'dosh',    10,  0),
( 1, 13, 'nestcoDosh', 50, 0), ( 1, 14, 'xp',  100,  0), ( 1, 15, 'sws',    100,  0),
( 1, 16, 'mulch',  500,  0), ( 1, 17, 'seed',   10,  0), ( 1, 18, 'dosh',    10,  0),
( 1, 19, 'storepc',  1,  0), ( 1, 20, 'hat',      3,  0), ( 1, 21, 'mulch',  500,  0),
( 1, 22, 'dosh',    10,  0), ( 1, 23, 'move',     1,  0), ( 1, 24, 'xp',     100,  0),
( 1, 25, 'storemulch', 500, 0), ( 1, 26, 'dosh', 10,  0), ( 1, 27, 'binmartDosh', 50, 0),
( 1, 28, 'mulch',  500,  0), ( 1, 29, 'nestcoDosh', 50, 0), ( 1, 30, 'dosh',    50,  0)
ON DUPLICATE KEY UPDATE card=VALUES(card), stamp=VALUES(stamp);

-- Cards 2–5: same structure, scaled values increase per card
INSERT INTO loyalty_card_rewards (card, stamp, type, value, tycoonOnly)
SELECT card+1, stamp,
       CASE
         WHEN stamp % 10 = 0 AND stamp = 30 THEN 'dosh'
         WHEN stamp % 10 = 0 THEN 'hat'
         WHEN stamp % 5 = 0 THEN 'sws'
         WHEN stamp % 5 = 2 THEN 'binmartDosh'
         WHEN stamp % 5 = 3 THEN 'nestcoDosh'
         WHEN stamp % 2 = 0 THEN 'dosh'
         WHEN stamp % 3 = 0 THEN 'xp'
         WHEN stamp % 3 = 1 THEN 'mulch'
         ELSE 'seed'
       END,
       CASE
         WHEN stamp % 2 = 0 AND stamp % 10 != 0 THEN 10 + (card+1) * 2
         WHEN stamp % 10 = 0 AND stamp != 30 THEN 200 + (card+1) * 50
         WHEN stamp = 30 THEN 50 + (card+1) * 10
         WHEN stamp % 3 = 0 THEN 100 + (card+1) * 20
         WHEN stamp % 3 = 1 THEN 500 + (card+1) * 100
         ELSE 10 + (card+1) * 5
       END,
       0
FROM loyalty_card_rewards
WHERE card = 1
  AND card < 5
GROUP BY card, stamp
ON DUPLICATE KEY UPDATE card=VALUES(card), stamp=VALUES(stamp);

-- Cards 6–10: continued scaling
INSERT INTO loyalty_card_rewards (card, stamp, type, value, tycoonOnly)
SELECT card+5, stamp,
       CASE
         WHEN stamp % 10 = 0 AND stamp = 30 THEN 'dosh'
         WHEN stamp % 10 = 0 THEN 'hat'
         WHEN stamp % 5 = 0 THEN 'sws'
         WHEN stamp % 5 = 2 THEN 'binmartDosh'
         WHEN stamp % 5 = 3 THEN 'nestcoDosh'
         WHEN stamp % 2 = 0 THEN 'dosh'
         WHEN stamp % 3 = 0 THEN 'xp'
         WHEN stamp % 3 = 1 THEN 'mulch'
         ELSE 'seed'
       END,
       CASE
         WHEN stamp % 2 = 0 AND stamp % 10 != 0 THEN 10 + (card+5) * 2
         WHEN stamp % 10 = 0 AND stamp != 30 THEN 200 + (card+5) * 50
         WHEN stamp = 30 THEN 50 + (card+5) * 10
         WHEN stamp % 3 = 0 THEN 100 + (card+5) * 20
         WHEN stamp % 3 = 1 THEN 500 + (card+5) * 100
         ELSE 10 + (card+5) * 5
       END,
       0
FROM loyalty_card_rewards
WHERE card = 1
GROUP BY card, stamp
ON DUPLICATE KEY UPDATE card=VALUES(card), stamp=VALUES(stamp);

-- Cards 11–15: higher tier
INSERT INTO loyalty_card_rewards (card, stamp, type, value, tycoonOnly)
SELECT card+10, stamp,
       CASE
         WHEN stamp % 10 = 0 AND stamp = 30 THEN 'dosh'
         WHEN stamp % 10 = 0 THEN 'hat'
         WHEN stamp % 5 = 0 THEN 'sws'
         WHEN stamp % 5 = 2 THEN 'binmartDosh'
         WHEN stamp % 5 = 3 THEN 'nestcoDosh'
         WHEN stamp % 2 = 0 THEN 'dosh'
         WHEN stamp % 3 = 0 THEN 'xp'
         WHEN stamp % 3 = 1 THEN 'mulch'
         ELSE 'seed'
       END,
       CASE
         WHEN stamp % 2 = 0 AND stamp % 10 != 0 THEN 20 + (card+10) * 3
         WHEN stamp % 10 = 0 AND stamp != 30 THEN 500 + (card+10) * 100
         WHEN stamp = 30 THEN 100 + (card+10) * 20
         WHEN stamp % 3 = 0 THEN 200 + (card+10) * 40
         WHEN stamp % 3 = 1 THEN 1000 + (card+10) * 200
         ELSE 20 + (card+10) * 10
       END,
       0
FROM loyalty_card_rewards
WHERE card = 1
GROUP BY card, stamp
ON DUPLICATE KEY UPDATE card=VALUES(card), stamp=VALUES(stamp);

-- Card 16 (final): stamp 30 is doshGold, tycoonOnly.
-- Override just the final stamp entries.
UPDATE loyalty_card_rewards
   SET type='doshGold', value=5000, tycoonOnly=1
 WHERE card=16 AND stamp=30;
