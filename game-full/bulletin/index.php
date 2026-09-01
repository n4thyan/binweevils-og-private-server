<?php
include('../site/bootstrap.php');
include_once('../site/news.php');

$sitePageTitle = 'Bin Bulletin';
$siteActive = '';
$articles = site_news_articles(25);
include('../site/header.php');
?>

<section class="bw-panel bw-panel--container bw-bulletin-head">
    <div class="bw-bulletin-head-copy">
        <p class="bw-eyebrow">Nest News</p>
        <h1 class="bw-section-title">Bin Bulletin</h1>
        <p class="bw-section-intro">News from around the Bin, published from the same source used by Nest News in the game.</p>
        <div class="bw-button-row">
            <a class="bw-button bw-button--green bw-button--small" href="<?php echo $siteLoggedIn ? '/game.php' : '/#login'; ?>">Open Bin Weevils</a>
            <a class="bw-button bw-button--blue bw-button--small" href="/">Back home</a>
        </div>
    </div>
    <img class="bw-bulletin-character" src="/assets/images/bulletin-drummer.webp" alt="" aria-hidden="true">
</section>

<section class="bw-news-list" aria-label="Nest News articles">
<?php if(empty($articles)): ?>
    <div class="bw-panel bw-content-panel">
        <h2 class="bw-card-title">No news just yet</h2>
        <p class="bw-muted">Check back soon for the latest from the Bin.</p>
    </div>
<?php else: ?>
    <?php foreach($articles as $article): ?>
        <?php $links = site_news_links((int)$article['id']); ?>
        <article class="bw-panel bw-panel--container bw-news-article<?php echo !empty($article['is_top_story']) ? ' is-top-story' : ''; ?>">
            <p class="bw-eyebrow"><?php echo site_e($article['author'] ?: 'Bin Weevils Team'); ?> · <?php echo date('j M Y', (int)$article['published_at']); ?></p>
            <h2><?php echo site_e($article['title']); ?></h2>
            <div class="bw-news-body"><?php echo nl2br(site_e(trim(strip_tags((string)$article['body'])))); ?></div>
            <?php if(!empty($links)): ?>
                <div class="bw-button-row">
                <?php foreach($links as $link): ?>
                    <?php
                    $target = trim((string)$link['link_target']);
                    if(!preg_match('~^(?:https?://|/)~i', $target)) continue;
                    $label = trim((string)$link['label']);
                    if($label === '') $label = 'Read more';
                    ?>
                    <a class="bw-button bw-button--blue bw-button--small" href="<?php echo site_e($target); ?>"><?php echo site_e($label); ?></a>
                <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </article>
    <?php endforeach; ?>
<?php endif; ?>
</section>

<?php include('../site/footer.php'); ?>
