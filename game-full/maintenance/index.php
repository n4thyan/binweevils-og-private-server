<?php
include('../site/bootstrap.php');
$sitePageTitle = 'Maintenance';
$siteActive = '';
$siteShowTopAd = false;
include('../site/header.php');
?>

<section class="bw-panel bw-panel--orange bw-content-panel" style="max-width:760px;margin:60px auto;text-align:center;">
    <p class="bw-eyebrow">Bin maintenance</p>
    <h1 class="bw-section-title">The Bin is being tidied up</h1>
    <p class="bw-section-intro">The game or website is temporarily unavailable while maintenance is being carried out. Check the Bin Bulletin on the homepage when service returns.</p>
    <div class="bw-button-row" style="justify-content:center;">
        <a class="bw-button bw-button--green" href="/">Return home</a>
        <a class="bw-button bw-button--blue" href="/community/">Community</a>
    </div>
</section>

<?php include('../site/footer.php'); ?>
