<?php

use App\Http\Resources\StatisticsResource;

it('transforms statistics to array correctly', function () {
    $stats = [
        'total_contents' => 100,
        'total_videos' => 60,
        'total_articles' => 40,
        'total_views' => 50000,
        'total_likes' => 2500,
        'total_comments' => 500,
        'contents_by_provider' => [
            ['provider_name' => 'Provider 1', 'count' => 60],
            ['provider_name' => 'Provider 2', 'count' => 40],
        ],
        'recent_activity' => [
            'last_24h' => 10,
            'last_7d' => 50,
            'last_30d' => 100,
        ],
        'cached_at' => '2024-01-01 10:00:00',
    ];

    $resource = new StatisticsResource($stats);
    $array = $resource->toArray(request());

    expect($array)->toHaveKeys([
        'total_contents',
        'total_videos',
        'total_articles',
        'total_views',
        'total_likes',
        'total_comments',
        'contents_by_provider',
        'recent_activity',
        'cached_at',
    ]);

    expect($array['total_contents'])->toBe(100);
    expect($array['total_videos'])->toBe(60);
    expect($array['total_articles'])->toBe(40);
});

it('includes provider breakdown', function () {
    $stats = [
        'total_contents' => 100,
        'total_videos' => 60,
        'total_articles' => 40,
        'total_views' => 0,
        'total_likes' => 0,
        'total_comments' => 0,
        'contents_by_provider' => [
            ['provider_name' => 'Provider 1', 'count' => 60],
            ['provider_name' => 'Provider 2', 'count' => 40],
        ],
        'recent_activity' => [
            'last_24h' => 0,
            'last_7d' => 0,
            'last_30d' => 0,
        ],
        'cached_at' => now()->toString(),
    ];

    $resource = new StatisticsResource($stats);
    $array = $resource->toArray(request());

    expect($array['contents_by_provider'])->toHaveCount(2);
    expect($array['contents_by_provider'][0])->toHaveKeys(['provider_name', 'count']);
});

it('includes recent activity metrics', function () {
    $stats = [
        'total_contents' => 100,
        'total_videos' => 60,
        'total_articles' => 40,
        'total_views' => 0,
        'total_likes' => 0,
        'total_comments' => 0,
        'contents_by_provider' => [],
        'recent_activity' => [
            'last_24h' => 10,
            'last_7d' => 50,
            'last_30d' => 100,
        ],
        'cached_at' => now()->toString(),
    ];

    $resource = new StatisticsResource($stats);
    $array = $resource->toArray(request());

    expect($array['recent_activity'])->toHaveKeys(['last_24h', 'last_7d', 'last_30d']);
    expect($array['recent_activity']['last_24h'])->toBe(10);
    expect($array['recent_activity']['last_7d'])->toBe(50);
    expect($array['recent_activity']['last_30d'])->toBe(100);
});

