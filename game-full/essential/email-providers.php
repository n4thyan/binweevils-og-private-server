<?php
/** Exact-domain allowlist for account activation. */
function activationEmailProviderDomains() {
    return [
        'gmail.com','googlemail.com',
        'outlook.com','hotmail.com','hotmail.co.uk','live.com','live.co.uk','msn.com',
        'outlook.co.uk','outlook.de','outlook.fr','hotmail.de','hotmail.fr','hotmail.it','hotmail.es',
        'live.de','live.fr','live.it','live.nl','live.ca','live.com.au',
        'yahoo.com','yahoo.co.uk','yahoo.ca','yahoo.com.au','yahoo.de','yahoo.fr','yahoo.it','yahoo.es',
        'yahoo.co.in','yahoo.in','ymail.com','rocketmail.com',
        'aol.com','aol.co.uk','aim.com',
        'icloud.com','me.com','mac.com',
        'proton.me','protonmail.com','pm.me',
        'fastmail.com','fastmail.fm',
        'gmx.com','gmx.co.uk','gmx.net','gmx.de',
        'tuta.com','tutanota.com','tutanota.de','tutamail.com','keemail.me',
        'mail.com','email.com','usa.com','myself.com','consultant.com','post.com','europe.com',
        'asia.com','engineer.com','accountant.com','dr.com','techie.com','writeme.com',
        'zohomail.com','zoho.com',
        'btinternet.com','btopenworld.com','sky.com','talktalk.net','tiscali.co.uk',
        'virginmedia.com','ntlworld.com','blueyonder.co.uk','virgin.net',
        'comcast.net','att.net','sbcglobal.net','bellsouth.net','verizon.net','cox.net'
    ];
}

function normalizeAllowedActivationEmail($rawEmail) {
    $email = strtolower(trim((string)$rawEmail));
    if(strlen($email) > 254 || !filter_var($email, FILTER_VALIDATE_EMAIL)) return null;
    $at = strrpos($email, '@');
    if($at === false) return null;
    $domain = substr($email, $at + 1);
    if(!in_array($domain, activationEmailProviderDomains(), true)) return null;
    return $email;
}
