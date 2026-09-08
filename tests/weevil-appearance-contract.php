<?php
require_once __DIR__ . '/../game-full/site/weevil-appearance.php';

$failures = 0;
$checks = 0;

function check_same($expected, $actual, $label) {
    global $failures, $checks;
    $checks++;
    if($expected !== $actual) {
        $failures++;
        fwrite(STDERR, "FAIL: {$label}\n  expected: " . var_export($expected, true) . "\n  actual:   " . var_export($actual, true) . "\n");
    }
}

foreach([
    '000000' => '000000',
    'ffffff' => 'FFFFFF',
    '#FF0000' => 'FF0000',
    '12ABEF' => '12ABEF',
] as $input => $expected) {
    check_same($expected, weevil_normalize_hex($input), "valid RGB {$input}");
}

foreach(['FFF', 'GG0000', '1234567', '', 'javascript:alert(1)'] as $input) {
    check_same(null, weevil_normalize_hex($input), "invalid RGB {$input}");
}

check_same(false, weevil_advanced_update_allowed(0), 'Prestige 0 rejected');
check_same(true, weevil_advanced_update_allowed(1), 'Prestige 1 accepted');

$legacy = '401135129001323200';
$parsedLegacy = weevil_parse_definition($legacy);
check_same(true, is_array($parsedLegacy), 'valid existing definition accepted');
check_same($legacy, weevil_build_definition($parsedLegacy, false), 'legacy definition round trip');

$custom = weevil_apply_definition_changes($legacy, [
    'head_colour' => '#12ABEF',
    'body_colour' => '7f3acc',
    'eye_colour' => '000000',
    'antenna_colour' => 'FFFFFF',
    'leg_colour' => 'FF0000',
]);
check_same('401135129001323200~12ABEF7F3ACC000000FFFFFFFF0000', $custom, 'custom colours stored canonically');
check_same(true, is_array(weevil_parse_definition($custom)), 'valid definition plus custom colours accepted');

check_same(null, weevil_apply_definition_changes($legacy, ['antenna_type' => '99']), 'unknown antenna rejected');
check_same(null, weevil_apply_definition_changes($legacy, ['leg_type' => '41']), 'unknown leg rejected');
check_same(null, weevil_parse_definition('not-a-definition'), 'malformed definition rejected');
check_same(null, weevil_parse_definition('401135129001323200~12ABEFGG0000000000FFFFFFFF0000'), 'malformed extended colour rejected');

$parts = weevil_apply_definition_changes($custom, [
    'head_type' => '2',
    'body_type' => '3',
    'eye_type' => '6',
    'eyelids' => '1',
    'antenna_type' => '41',
    'leg_type' => '40',
]);
check_same('201335629141323240~12ABEF7F3ACC000000FFFFFFFF0000', $parts, 'body parts update while colours are preserved');

if($failures > 0) {
    fwrite(STDERR, "{$failures} of {$checks} checks failed.\n");
    exit(1);
}

echo "PASS: {$checks} Weevil appearance contract checks.\n";
