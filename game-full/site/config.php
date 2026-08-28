<?php
return [
    'announcements' => [
        [
            'text' => 'Welcome to the Bin Weevils private server!',
            'href' => null,
            'urgent' => false,
        ],
        [
            'text' => 'Community chat is moving to xat.',
            'href' => '/community/',
            'urgent' => false,
        ],
        [
            'text' => 'Keep an eye on Nest News in-game for the latest Bin updates.',
            'href' => null,
            'urgent' => false,
        ],
    ],

    // Set this to the final xat embed URL/group once the room has been chosen.
    // Keeping it in one config file means the Community page never needs a redesign
    // just to change rooms.
    'xat_embed_url' => '',

    'build_label' => 'Development build',
];
