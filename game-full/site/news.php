<?php
function site_news_articles($limit = 10) {
    $limit = max(1, min(50, (int)$limit));
    $now = time();
    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $q = $db->prepare("SELECT id, title, body, category, image_path, author, published_at, expires_at, is_top_story FROM news_articles WHERE is_published = 1 AND published_at <= ? AND (expires_at IS NULL OR expires_at >= ?) ORDER BY is_top_story DESC, published_at DESC, id DESC LIMIT ?");
    $q->bind_param('iii', $now, $now, $limit);
    $q->execute();
    return $q->get_result()->fetch_all(MYSQLI_ASSOC);
}

function site_news_links($articleId) {
    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $q = $db->prepare('SELECT link_target, link_type, tracking_id FROM news_article_links WHERE article_id = ? ORDER BY display_order, id');
    $articleId = (int)$articleId;
    $q->bind_param('i', $articleId);
    $q->execute();
    return $q->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>
