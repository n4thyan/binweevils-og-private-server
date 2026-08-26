-- Checkpoint A / Option Y: wipe dev users + reseed 3 admin accounts.
-- Add modToken column (used by Option A moderator socket auth).
-- Clear user-scoped data to avoid orphans (no FK constraints in schema).
TRUNCATE TABLE users;
TRUNCATE TABLE buddylist;
TRUNCATE TABLE weevilitems;
TRUNCATE TABLE weevilhats;
TRUNCATE TABLE pets;
TRUNCATE TABLE `game-logs`;
TRUNCATE TABLE taskscompletedbyusers;
TRUNCATE TABLE questscompleted;
TRUNCATE TABLE development;
TRUNCATE TABLE gameinvites;

-- Insert 3 moderator/admin accounts with bcrypt-hashed passwords.
-- Passwords (local dev, generated): Rick=4ca57bfee477  Raving=3300bd16a1ed  Sludge=ed0df9e32928
INSERT INTO users
  (id, username, password, email, isModerator, sessionKey, loginKey, modToken, level, mulch, dosh, tycoon, def, xp, xp1, xp2, food, canSpeak, activated, lastLogin, curHat, invitedBy, active, bannedUntil, createdAt, loginIP, regIP)
VALUES
  (1, 'Rick',   '$2y$10$s3wVvX.ze4j5YJtQxiV.QuTydEymCdy7NtyzU2yT7vLDGQkuhedXu', '', 1, '', '', NULL, 1, 5000, 25, 1, '101101406100171700', 0, 0, 30, 100, '0', 0, 0, '|1:-140,-140,-140', NULL, 1, 0, UNIX_TIMESTAMP(), NULL, NULL),
  (2, 'Raving', '$2y$10$1zIzDEbfzTpWo1Cqq1w4AOtjsRe6e7xgyZ0xRZQrOL73zA/WIPI2K', '', 1, '', '', NULL, 1, 5000, 25, 1, '101101406100171700', 0, 0, 30, 100, '0', 0, 0, '|1:-140,-140,-140', NULL, 1, 0, UNIX_TIMESTAMP(), NULL, NULL),
  (3, 'Sludge', '$2y$10$4MeWgjmQmesVmnD1cIfLruKYlnoKnVyqbb0nO2upZvPSZkL9whfV6', '', 1, '', '', NULL, 1, 5000, 25, 1, '101101406100171700', 0, 0, 30, 100, '0', 0, 0, '|1:-140,-140,-140', NULL, 1, 0, UNIX_TIMESTAMP(), NULL, NULL);

SELECT id, username, isModerator, active, LEFT(password,12) AS pw FROM users;
