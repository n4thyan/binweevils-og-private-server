<?php
include('../site/bootstrap.php');
$sitePageTitle = 'Credits & Preservation';
$siteActive = '';
include('../site/header.php');
?>

<section class="bw-panel bw-panel--green bw-content-panel">
    <p class="bw-eyebrow">Preservation</p>
    <h1 class="bw-section-title">Credits &amp; Preservation</h1>
    <p class="bw-section-intro">This private server is a fan-made preservation project built to restore and study the classic Bin Weevils experience.</p>
</section>

<section class="bw-tips-strip" aria-label="Credits">
    <div class="bw-tip">
        <p class="bw-eyebrow">Original game</p>
        <h3 class="bw-card-title">Bin Weevils</h3>
        <p>Original characters, names, artwork, sounds and game assets belong to their respective rights holders. The project does not claim ownership of them.</p>
    </div>
    <div class="bw-tip bw-tip--accent">
        <p class="bw-eyebrow">Project</p>
        <h3 class="bw-card-title">Private server restoration</h3>
        <p>The server, compatibility work, recovered-data wiring, website restoration and preservation tooling are community restoration work.</p>
    </div>
    <div class="bw-tip">
        <p class="bw-eyebrow">Goal</p>
        <h3 class="bw-card-title">Keep the classic experience intact</h3>
        <p>The project favours recovered official assets and original client behaviour instead of replacing Bin Weevils with an unrelated new visual identity.</p>
    </div>
</section>

<section class="bw-panel bw-content-panel" style="margin-top:27px;">
    <p class="bw-eyebrow">Acknowledgement</p>
    <h2 class="bw-card-title">Community preservation</h2>
    <p class="bw-muted">The restoration benefits from archived game files, historical reference code, research and testing contributed by people who preserved pieces of Bin Weevils after the original service disappeared.</p>
    <p class="bw-muted">This website is part of that same approach: recovered Bin Weevils artwork is reused wherever practical, while modern HTML/CSS is used for layout, accessibility and responsive behaviour.</p>
</section>

<?php include('../site/footer.php'); ?>
