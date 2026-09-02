<?php
return [
    'announcements' => [
        [
            'text' => 'Welcome back to the Bin — the classic world is open again!',
            'href' => null,
            'urgent' => false,
        ],
        [
            'text' => 'Community chat is on xat.',
            'href' => '/community/',
            'urgent' => false,
        ],
        [
            'text' => 'Keep an eye on Nest News in-game for the latest Bin updates.',
            'href' => null,
            'urgent' => false,
        ],
    ],

    // Add the final xat embed URL/group here when the room has been chosen.
    'xat_embed_url' => '',

    // Public desktop-client downloads. Keep the URL out of page templates so a
    // release can be replaced without redesigning the site. On a VPS, these can
    // be supplied as environment variables instead of committed URLs.
    'client_downloads' => [
        'windows' => [
            'label' => 'Windows',
            'url' => getenv('BW_WINDOWS_CLIENT_URL') ?: '',
            'version' => getenv('BW_WINDOWS_CLIENT_VERSION') ?: 'Development build',
            'size' => getenv('BW_WINDOWS_CLIENT_SIZE') ?: '',
        ],
        'source_url' => 'https://github.com/n4thyan/binweevils-og-private-server/tree/main/electron',
    ],

    // Advert creatives, classified by FORMAT so incompatible aspect ratios are
    // NEVER rotated through the same slot. Each placement below draws ONLY from a
    // pool of the same shape as its slot:
    //   site-top      -> LEADERBOARD (wide/highizontal: ~728x90, 970x90, 970x250)
    //   home-rectangle-> MPU/RECTANGLE (approximately 300x250 / square)
    // Measured source dimensions (ffmpeg probe):
    //   bw-ad-1/2.mp4 = 1296x1080  (1.2:1  -> MPU/rectangle pool)
    //   bw-ad-3/4.mp4 = 1920x236   (8.1:1  -> leaderboard/banner pool)
    //   binweevils-banner-temp.png = 1280x270 (4.7:1 -> leaderboard pool)
    // The slot's CSS fixes the aspect ratio and uses object-fit:contain, so the
    // advert is letterboxed inside the slot and the SLOT NEVER resizes to fit the
    // creative. Rotating within a placement is therefore always safe.
    'ad_creatives' => [
        // Wide banner / leaderboard slot (top of homepage). Wide creatives only.
        'site-top' => [
            ['type' => 'image', 'src' => '/assets/ads/binweevils-banner-temp.png', 'href' => '', 'label' => 'Bin Weevils'],
            ['type' => 'video', 'src' => '/assets/ads/bw-ad-3.mp4', 'href' => '', 'label' => 'Bin Weevils', 'duration' => 14],
            ['type' => 'video', 'src' => '/assets/ads/bw-ad-4.mp4', 'href' => '', 'label' => 'Bin Weevils', 'duration' => 14],
        ],
        // Rectangle / MPU slot (content area). Square-ish creatives only.
        'home-rectangle' => [
            ['type' => 'image', 'src' => '/assets/ads/bw-ad-rectangle-static.png', 'href' => '', 'label' => 'Play games in Bin Weevils', 'duration' => 12],
            ['type' => 'video', 'src' => '/assets/ads/bw-ad-1.mp4', 'poster' => '/assets/ads/bw-ad-rectangle-static.png', 'href' => '', 'label' => 'Bin Weevils', 'duration' => 14],
            ['type' => 'video', 'src' => '/assets/ads/bw-ad-2.mp4', 'poster' => '/assets/ads/bw-ad-2-poster.png', 'href' => '', 'label' => 'Bin Weevils', 'duration' => 14],
        ],
    ],

    // Local-first play defaults. VPS deployment changes these in one place rather
    // than editing the Play page or its preserved Flash/WebSocket contract.
    'flash_movie' => '/mainDEV663.swf?ver=1',
    'flash_login_path' => 'http://localhost/',
    'websocket_url' => 'ws://localhost:2087',

    'build_label' => 'Development build',
];
