<?php
include('../site/bootstrap.php');
$sitePageTitle = 'Legal & Terms';
$siteActive = '';
include('../site/header.php');
?>
<?php if(site_has_ads('site-top')): ?>
<section class="bw-ad-row bw-ad-row--top" aria-label="Sponsor">
    <?php site_ad_slot('site-top', 'leaderboard'); ?>
</section>
<?php endif; ?>

<section class="bw-panel bw-panel--green bw-content-panel">
    <p class="bw-eyebrow">Legal</p>
    <h1 class="bw-section-title">Terms of use</h1>
    <p class="bw-section-intro">Bin Weevils Rewritten is a fan-made and independent recreation of the original Bin Weevils and is not affiliated with 55Pixels Ltd. Please read these terms carefully before using the site.</p>
</section>

<section class="bw-content-panel bw-legal-body" aria-label="Terms of use">
    <h2 class="bw-card-title">Agreement</h2>
    <p>By using any of our Services or by clicking a box that states that you accept or agree to these terms, you signify your agreement to these terms of use and the Bin Weevils Rewritten Rules. If you do not agree to this legal disclaimer or the Rewritten Rules, you may not use this website or any of our other Services.</p>
    <p>If you are a parent or guardian and you provide your consent to your child's registration with the site, you agree to be bound by these terms of use in respect of their use of the site.</p>

    <h2 class="bw-card-title">Accounts</h2>
    <p>Some areas on the site require you to create an account to participate or to secure additional benefits. By creating an account you confirm and agree that you are 13 years of age or older, and if you are between 13 and the age of majority in your jurisdiction your legal guardian has reviewed and agrees to these terms.</p>
    <p>You shall not impersonate any person or entity or misrepresent your identity or affiliation with any person or entity, including using another person's username, password or other account information, or another person's name, likeness, voice, image or photograph.</p>
    <p>You acknowledge that you may not sublicense, transfer, sell, or assign an account. Any attempt to sublicense, transfer, auction, sell or assign the account is void and will result in immediate termination of the account.</p>

    <h2 class="bw-card-title">Conduct</h2>
    <p>Engaging in targeted abuse or harassment on the sites and/or communication areas will not be tolerated, and any such behaviour may result in action against the individuals responsible.</p>
    <ul>
        <li>Being defamatory, abusive, harassing, or threatening towards another person.</li>
        <li>Spreading bigoted, hateful, violent, vulgar, obscene, sexually explicit or otherwise offensive communications on the site.</li>
        <li>Participating in a conspiracy to commit any criminal activity.</li>
    </ul>

    <h2 class="bw-card-title">Ownership</h2>
    <p>Except where stated explicitly in terms of copyright or other licensing, the contents of the sites are the sole property of the project, and may not be copied, distributed, displayed, altered, modified, reproduced, or transmitted in any form without prior written permission.</p>

    <h2 class="bw-card-title">Termination</h2>
    <p>These terms of use are effective until terminated by either you or us. We may immediately terminate these terms of use with respect to you, including your access to the site, in our absolute discretion, including if you breach or fail to comply with any material term or provision of these terms of use. Upon termination, you must cease use of the site.</p>
</section>

<?php include('../site/footer.php'); ?>
