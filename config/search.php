<?php

return [
    'statistics' => [
        'cache_key' => env('SEARCH_STATISTICS_CACHE_KEY', 'search_statistics'),
        'cache_ttl' => env('SEARCH_STATISTICS_CACHE_TTL', 1800), // 30 minutes in seconds
    ],
];
