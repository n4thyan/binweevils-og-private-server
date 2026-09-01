-- Minimal shared Nest News / website bulletin source.

CREATE TABLE IF NOT EXISTS `news_articles` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(180) NOT NULL,
    `body` MEDIUMTEXT NOT NULL,
    `category` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `image_path` VARCHAR(255) NULL,
    `author` VARCHAR(80) NULL,
    `published_at` BIGINT UNSIGNED NOT NULL,
    `expires_at` BIGINT UNSIGNED NULL,
    `is_published` TINYINT(1) NOT NULL DEFAULT 0,
    `is_top_story` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` BIGINT UNSIGNED NOT NULL,
    `updated_at` BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_news_published` (`is_published`, `published_at`),
    KEY `idx_news_expiry` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `news_article_links` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `article_id` INT UNSIGNED NOT NULL,
    `display_order` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `link_target` VARCHAR(500) NOT NULL,
    `link_type` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `tracking_id` VARCHAR(100) NULL,
    PRIMARY KEY (`id`),
    KEY `idx_news_link_article` (`article_id`, `display_order`),
    CONSTRAINT `fk_news_link_article` FOREIGN KEY (`article_id`) REFERENCES `news_articles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `news_articles`
    (`title`, `body`, `category`, `image_path`, `author`, `published_at`, `expires_at`, `is_published`, `is_top_story`, `created_at`, `updated_at`)
SELECT
    'PRIVATE SERVER TESTING IS UNDERWAY',
    'Welcome back to the Bin! The private server is now in local launch testing. Explore, try the restored shops and room activities, and report anything that does not behave like the original game.',
    1,
    NULL,
    'Bin Weevils Team',
    UNIX_TIMESTAMP('2026-09-01 00:00:00'),
    NULL,
    1,
    1,
    UNIX_TIMESTAMP(),
    UNIX_TIMESTAMP()
WHERE NOT EXISTS (
    SELECT 1 FROM `news_articles` WHERE `title` = 'PRIVATE SERVER TESTING IS UNDERWAY'
);
