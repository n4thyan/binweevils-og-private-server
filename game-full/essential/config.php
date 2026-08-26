<?php
// Centralized configuration / secrets.
// For production, set these as real environment variables (e.g. in the web server
// or a .env loader). The getenv() fallbacks keep a local XAMPP dev install working
// unchanged. DO NOT commit real production secrets; the fallbacks are dev defaults.
function bw_env($name, $default) {
    $v = getenv($name);
    return ($v === false || $v === '') ? $default : $v;
}

// Database (XAMPP local default)
define('DB_HOST', bw_env('BW_DB_HOST', 'localhost'));
define('DB_USER', bw_env('BW_DB_USER', 'root'));
define('DB_PASS', bw_env('BW_DB_PASS', ''));
define('DB_NAME', bw_env('BW_DB_NAME', 'bwps'));

// AES-256 passphrase used to obfuscate login error messages (obfuscation only, not secrecy)
define('AES_PASSPHRASE', bw_env('BW_AES_PASSPHRASE', 'hdjjsdarkkarecool'));

// Legacy "scramble" keys (used by deprecated helpers; replaced by password_hash for auth)
define('SCRAMBLE_PW_KEY', bw_env('BW_SCRAMBLE_PW_KEY', '?F5-#b$8M*e!5eR4'));
define('SCRAMBLE_EMAIL_KEY', bw_env('BW_SCRAMBLE_EMAIL_KEY', '?F5-#b$1B*e!1eR0'));

// reCAPTCHA (infrastructure prepared; disabled by default for local dev)
define('RECAPTCHA_SITE_KEY', bw_env('BW_RECAPTCHA_SITE_KEY', ''));
define('RECAPTCHA_SECRET', bw_env('BW_RECAPTCHA_SECRET', '6LcvFZAaAAAAAJzGrFPQpDqVFCNxsBZtJYRzgaWQ'));
define('RECAPTCHA_ENABLED', bw_env('BW_RECAPTCHA_ENABLED', 'false') === 'true');

// Secret used to hash (HMAC) visitor IPs for privacy-preserving logging
define('IP_HASH_SECRET', bw_env('BW_IP_HASH_SECRET', 'change-me-in-production'));
?>
