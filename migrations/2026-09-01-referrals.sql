-- Additive launch referral schema. Existing users and gameinvites are preserved.

CREATE TABLE IF NOT EXISTS `referral_codes` (
    `user_id` INT NOT NULL,
    `code` VARCHAR(24) NOT NULL,
    `created_at` BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (`user_id`),
    UNIQUE KEY `uq_referral_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `referrals` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `inviter_user_id` INT NOT NULL,
    `referred_user_id` INT NOT NULL,
    `referral_code` VARCHAR(24) NOT NULL,
    `created_at` BIGINT UNSIGNED NOT NULL,
    `reward_state` ENUM('pending','granted') NOT NULL DEFAULT 'pending',
    `rewarded_at` BIGINT UNSIGNED NULL,
    `reward_mulch` INT UNSIGNED NOT NULL DEFAULT 0,
    `reward_dosh` INT UNSIGNED NOT NULL DEFAULT 0,
    `reward_xp` INT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_referral_referred_user` (`referred_user_id`),
    KEY `idx_referral_inviter_state` (`inviter_user_id`, `reward_state`),
    KEY `idx_referral_code` (`referral_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Give all existing accounts a stable public invite code without changing user rows.
INSERT IGNORE INTO `referral_codes` (`user_id`, `code`, `created_at`)
SELECT `id`, CONCAT('BW', UPPER(CONV(`id`, 10, 36)), UPPER(SUBSTRING(SHA2(CONCAT('bw-referral-v1:', `id`, ':', `username`), 256), 1, 7))), UNIX_TIMESTAMP()
FROM `users`;

-- Preserve any usable legacy invitedBy relationships as pending referral events.
INSERT IGNORE INTO `referrals` (`inviter_user_id`, `referred_user_id`, `referral_code`, `created_at`, `reward_state`)
SELECT inviter.`id`, referred.`id`, codes.`code`, referred.`createdAt`, 'pending'
FROM `users` referred
JOIN `users` inviter ON LOWER(inviter.`username`) = LOWER(TRIM(referred.`invitedBy`))
JOIN `referral_codes` codes ON codes.`user_id` = inviter.`id`
WHERE referred.`invitedBy` IS NOT NULL
  AND TRIM(referred.`invitedBy`) <> ''
  AND inviter.`id` <> referred.`id`;
