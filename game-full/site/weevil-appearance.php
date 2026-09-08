<?php
/**
 * Canonical Weevil appearance definition parser/builder.
 *
 * Historical definitions are 18 decimal digits:
 *   ht hc bt bc et ec lids at ac lc lt
 * where colour fields are two-digit indexes into the original Flash palettes.
 *
 * Extended definitions retain that exact 18-digit prefix for old-editor
 * compatibility, followed by five exact RGB values:
 *   <legacy18>~<head><body><eyes><antennae><legs>
 */

function weevil_colour_palette_1() {
    return [10027008,43520,153,10057472,8913032,11198463,26367,16750848,13421568,61166,13369548,16777215,16766429,11206400,16763904,15658496,16745604,2631720,10066329,16777145,15597568,26112,1184274,12733185,16736768,16425579,16767167,7620096,16771473,6394113,8899328,14548127,62720,11993014,25670,110971,61093,7011535,25219,50886,10289151,2797311,3014772,5243334,8334079,14138879,16729855,16756735,11338573,15597672,15952037,16757203];
}

function weevil_colour_palette_2() {
    return [52224,4474077,15610675,13421568,52428,13369548,8943360,2136473,11206400,16763904,15658496,16745604,10027008,15597568,16766429,12733185,16736768,16425579,16767167,7620096,16750848,16771473,10057472,16777145,6394113,8899328,11206400,14548127,26112,43520,62720,11993014,25670,110971,61093,7011535,25219,50886,61166,10289151,153,26367,2797311,11198463,3014772,5243334,8334079,14138879,8913032,16729855,16756735,11338573,15597672,15952037,16757203,10066329,16777215,2631720];
}

function weevil_antenna_types() {
    return [
        0 => 'No antennae', 1 => 'Single · small', 2 => 'Single · medium', 3 => 'Single · large',
        4 => 'Double · small', 5 => 'Double · medium', 6 => 'Double · large',
        7 => 'Triple · small', 8 => 'Triple · medium', 9 => 'Triple · large',
        10 => 'Super · original', 11 => 'Super · purple', 12 => 'Super · red and white',
        13 => 'Super · purple, yellow and blue', 14 => 'Super · Halloween', 15 => 'Super · fire',
        16 => 'Super · ice', 17 => 'Black and white', 18 => 'Black, blue and black',
        19 => 'Beano', 20 => 'Monty', 21 => 'HD custom', 22 => 'Red and black',
        23 => 'Lime green', 24 => 'Pink and aqua', 25 => 'Marie', 26 => 'Cabbage',
        27 => 'Bradaz', 28 => 'BB1', 29 => 'BB2', 30 => 'Pure black', 31 => 'Grey to black',
        32 => 'Black to grey', 33 => 'Springy', 34 => 'Doc 1', 35 => 'Doc 2',
        36 => 'Pale to yellow', 37 => 'Neon 1', 38 => 'Neon 2', 39 => 'Bandit',
        40 => 'Pure white', 41 => 'Make own', 42 => 'Competition winner 1',
        43 => 'Competition winner 2', 44 => 'Competition winner 3', 45 => 'Competition winner 4',
        49 => 'Alex', 50 => 'Bright KOTB', 51 => 'Connor 1', 52 => 'Connor 2',
        53 => 'Gold', 54 => 'Icy',
    ];
}

function weevil_leg_types() {
    return [
        0 => 'Normal', 1 => 'Stripy', 2 => 'Summer Fair', 3 => 'Super · original',
        4 => 'Super · purple, yellow and blue', 5 => 'Super · Halloween', 6 => 'Black and white',
        7 => 'Black, blue and black', 8 => 'Super · fire', 9 => 'Super · ice', 10 => 'Super · disco',
        11 => 'Beano', 12 => 'Monty', 13 => 'HD', 14 => 'Red and black', 15 => 'Lime green',
        16 => 'Pink and aqua', 17 => 'Marie', 18 => 'Cabbage', 19 => 'Bradaz', 20 => 'BB1',
        21 => 'BB2', 22 => 'Grey to black', 23 => 'Black to grey', 24 => 'Springy',
        25 => 'Doc 1', 26 => 'Doc 2', 27 => 'Pale to yellow', 28 => 'Neon 1', 29 => 'Neon 2',
        30 => 'Bandit', 31 => 'Competition winner 1', 32 => 'Competition winner 2',
        33 => 'Competition winner 3', 34 => 'Competition winner 4', 35 => 'Alex',
        36 => 'Bright KOTB', 37 => 'Connor 1', 38 => 'Connor 2', 39 => 'Gold', 40 => 'Icy',
    ];
}

function weevil_normalize_hex($raw) {
    if(!is_string($raw) && !is_numeric($raw)) return null;
    $value = trim((string)$raw);
    if(substr($value, 0, 1) === '#') $value = substr($value, 1);
    if(!preg_match('/^[0-9A-Fa-f]{6}$/', $value)) return null;
    return strtoupper($value);
}

function weevil_rgb_int_to_hex($value) {
    return strtoupper(str_pad(dechex((int)$value), 6, '0', STR_PAD_LEFT));
}

function weevil_advanced_update_allowed($prestige) {
    return is_numeric($prestige) && (int)$prestige >= 1;
}

function weevil_parse_definition($raw) {
    if(!is_string($raw) && !is_numeric($raw)) return null;
    $raw = trim((string)$raw);
    if(!preg_match('/^(\d{18})(?:~([0-9A-Fa-f]{6})([0-9A-Fa-f]{6})([0-9A-Fa-f]{6})([0-9A-Fa-f]{6})([0-9A-Fa-f]{6}))?$/', $raw, $matches)) return null;

    $legacy = $matches[1];
    $definition = [
        'head_type' => (int)substr($legacy, 0, 1),
        'head_colour_index' => (int)substr($legacy, 1, 2),
        'body_type' => (int)substr($legacy, 3, 1),
        'body_colour_index' => (int)substr($legacy, 4, 2),
        'eye_type' => (int)substr($legacy, 6, 1),
        'eye_colour_index' => (int)substr($legacy, 7, 2),
        'eyelids' => (int)substr($legacy, 9, 1),
        'antenna_type' => (int)substr($legacy, 10, 2),
        'antenna_colour_index' => (int)substr($legacy, 12, 2),
        'leg_colour_index' => (int)substr($legacy, 14, 2),
        'leg_type' => (int)substr($legacy, 16, 2),
        'extended' => isset($matches[2]) && $matches[2] !== '',
    ];

    $palette1 = weevil_colour_palette_1();
    $palette2 = weevil_colour_palette_2();
    if($definition['head_type'] < 1 || $definition['head_type'] > 4) return null;
    if($definition['body_type'] < 1 || $definition['body_type'] > 4) return null;
    if($definition['eye_type'] < 1 || $definition['eye_type'] > 6) return null;
    if($definition['eyelids'] < 0 || $definition['eyelids'] > 1) return null;
    if(!array_key_exists($definition['antenna_type'], weevil_antenna_types())) return null;
    if(!array_key_exists($definition['leg_type'], weevil_leg_types())) return null;
    if(!array_key_exists($definition['head_colour_index'], $palette1)) return null;
    if(!array_key_exists($definition['body_colour_index'], $palette1)) return null;
    if(!array_key_exists($definition['eye_colour_index'], $palette2)) return null;
    if(!array_key_exists($definition['antenna_colour_index'], $palette1)) return null;
    if(!array_key_exists($definition['leg_colour_index'], $palette1)) return null;

    if($definition['extended']) {
        $definition['head_colour'] = strtoupper($matches[2]);
        $definition['body_colour'] = strtoupper($matches[3]);
        $definition['eye_colour'] = strtoupper($matches[4]);
        $definition['antenna_colour'] = strtoupper($matches[5]);
        $definition['leg_colour'] = strtoupper($matches[6]);
    }
    else {
        $definition['head_colour'] = weevil_rgb_int_to_hex($palette1[$definition['head_colour_index']]);
        $definition['body_colour'] = weevil_rgb_int_to_hex($palette1[$definition['body_colour_index']]);
        $definition['eye_colour'] = weevil_rgb_int_to_hex($palette2[$definition['eye_colour_index']]);
        $definition['antenna_colour'] = weevil_rgb_int_to_hex($palette1[$definition['antenna_colour_index']]);
        $definition['leg_colour'] = weevil_rgb_int_to_hex($palette1[$definition['leg_colour_index']]);
    }

    return $definition;
}

function weevil_build_definition($definition, $forceExtended = null) {
    if(!is_array($definition)) return null;
    $required = ['head_type','head_colour_index','body_type','body_colour_index','eye_type','eye_colour_index','eyelids','antenna_type','antenna_colour_index','leg_colour_index','leg_type'];
    foreach($required as $key) if(!array_key_exists($key, $definition)) return null;

    $legacy = sprintf('%d%02d%d%02d%d%02d%d%02d%02d%02d%02d',
        (int)$definition['head_type'], (int)$definition['head_colour_index'],
        (int)$definition['body_type'], (int)$definition['body_colour_index'],
        (int)$definition['eye_type'], (int)$definition['eye_colour_index'],
        (int)$definition['eyelids'], (int)$definition['antenna_type'],
        (int)$definition['antenna_colour_index'], (int)$definition['leg_colour_index'],
        (int)$definition['leg_type']
    );

    $checked = weevil_parse_definition($legacy);
    if($checked === null) return null;
    $extended = $forceExtended === null ? !empty($definition['extended']) : (bool)$forceExtended;
    if(!$extended) return $legacy;

    $colours = [];
    foreach(['head_colour','body_colour','eye_colour','antenna_colour','leg_colour'] as $key) {
        if(!array_key_exists($key, $definition)) return null;
        $hex = weevil_normalize_hex($definition[$key]);
        if($hex === null) return null;
        $colours[] = $hex;
    }
    return $legacy . '~' . implode('', $colours);
}

function weevil_apply_definition_changes($currentDefinition, $changes) {
    $definition = weevil_parse_definition($currentDefinition);
    if($definition === null || !is_array($changes)) return null;

    $partRules = [
        'head_type' => [1, 2, 3, 4],
        'body_type' => [1, 2, 3, 4],
        'eye_type' => [1, 2, 3, 4, 5, 6],
        'eyelids' => [0, 1],
        'antenna_type' => array_keys(weevil_antenna_types()),
        'leg_type' => array_keys(weevil_leg_types()),
    ];
    $colourKeys = ['head_colour','body_colour','eye_colour','antenna_colour','leg_colour'];
    $knownKeys = array_merge(array_keys($partRules), $colourKeys);

    foreach($changes as $key => $value) {
        if(!in_array($key, $knownKeys, true)) return null;
        if(isset($partRules[$key])) {
            if(!is_scalar($value) || !preg_match('/^\d{1,2}$/', (string)$value)) return null;
            $part = (int)$value;
            if(!in_array($part, $partRules[$key], true)) return null;
            $definition[$key] = $part;
        }
        else {
            $hex = weevil_normalize_hex($value);
            if($hex === null) return null;
            $definition[$key] = $hex;
            $definition['extended'] = true;
        }
    }

    return weevil_build_definition($definition, !empty($definition['extended']));
}
?>
