<?php

return [
    'modules' => [
        'ip2location' => [
            'download_token' => '',
            'download_database' => 'DB3LITE',

            // Spam filters
            'whitelist_countries' => ['US', 'CA'],
            'whitelist_score' => 0,
            'blacklist_countries' => ['CN', 'RU'],
            'blacklist_score' => 10,
        ],
    ],
    'jobs' => [
        'ip2location-update' => [
            'class' => Modules\IP2Location\Jobs\Update::class,
            'offset' => 10800, // 3 am server time
            'interval' => 1209600, // 14 days
            'max_threads' => 1,
        ],
    ],
];
