-- Additive hardening for Flum's Fountain mushroom reward claims.
-- Safe for existing users; no rows are deleted or balances changed.

ALTER TABLE `claimedmushrooms`
    MODIFY `lastClaimed` BIGINT UNSIGNED NOT NULL DEFAULT 0,
    ADD UNIQUE KEY `uq_claimedmushrooms_user_type` (`idx`, `mushroomType`);
