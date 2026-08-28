<?php
return [
    'announcements' => [
        [
            'text' => 'Welcome to the Bin Weevils private server!',
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

    // Video and static creatives use the same rotation. Leave a placement empty
    // until its local media files exist under /assets/ads/. Example creative:
    // ['type' => 'video', 'src' => '/assets/ads/example.mp4', 'label' => 'Advertisement']
    // Static images use type=image and may also set duration (seconds) and href.
    'ad_creatives' => [
        'site-top' => [],
    ],

    // Local-first play defaults. VPS deployment changes these in one place rather
    // than editing the Play page or its preserved Flash/WebSocket contract.
    'flash_movie' => '/mainDEV663.swf?ver=1',
    'flash_login_path' => 'http://localhost/',
    'websocket_url' => 'ws://localhost:2087',

    'build_label' => 'Development build',
];
