<?php

use App\Enums\ContentType;
use App\Http\Resources\ContentResource;
use App\Models\Content;
use App\Models\Provider;

it('transforms content to array correctly', function () {
    $provider = Provider::factory()->make([
        'id' => 1,
        'name' => 'Test Provider',
    ]);

    $content = Content::factory()->make([
        'id' => 1,
        'external_id' => 'ext-123',
        'title' => 'Test Content',
        'type' => ContentType::VIDEO,
        'views' => 1000,
        'likes' => 50,
        'comments' => 10,
        'reactions' => 5,
        'reading_time' => null,
        'during_seconds' => 300,
        'published_at' => '2024-01-01 10:00:00',
        'tags' => ['php', 'laravel'],
    ]);

    $content->setRelation('provider', $provider);

    // Simulate score enrichment
    $content->setAttribute('score', 25.5);
    $content->setAttribute('base_score', 15.0);
    $content->setAttribute('type_coefficient', 1.5);
    $content->setAttribute('freshness_score', 5.0);
    $content->setAttribute('engagement_score', 0.5);

    $resource = new ContentResource($content);
    $array = $resource->toArray(request());

    expect($array)->toHaveKeys([
        'id',
        'external_id',
        'title',
        'type',
        'provider',
        'metrics',
        'scores',
        'reading_time',
        'during_seconds',
        'published_at',
        'tags',
        'created_at',
        'updated_at',
    ]);

    expect($array['id'])->toBe(1);
    expect($array['external_id'])->toBe('ext-123');
    expect($array['title'])->toBe('Test Content');
    expect($array['type'])->toBe('video');
});

it('includes provider information', function () {
    $provider = Provider::factory()->make([
        'id' => 1,
        'name' => 'Test Provider',
    ]);

    $content = Content::factory()->make(['type' => ContentType::VIDEO]);
    $content->setRelation('provider', $provider);

    $resource = new ContentResource($content);
    $array = $resource->toArray(request());

    expect($array['provider'])->toBeArray();
    expect($array['provider']['id'])->toBe(1);
    expect($array['provider']['name'])->toBe('Test Provider');
});

it('includes metrics correctly', function () {
    $content = Content::factory()->make([
        'type' => ContentType::VIDEO,
        'views' => 1000,
        'likes' => 50,
        'comments' => 10,
        'reactions' => 5,
    ]);

    $content->setRelation('provider', Provider::factory()->make());

    $resource = new ContentResource($content);
    $array = $resource->toArray(request());

    expect($array['metrics'])->toBe([
        'views' => 1000,
        'likes' => 50,
        'comments' => 10,
        'reactions' => 5,
    ]);
});

it('includes score breakdown', function () {
    $content = Content::factory()->make(['type' => ContentType::VIDEO]);
    $content->setRelation('provider', Provider::factory()->make());
    
    $content->setAttribute('score', 25.5);
    $content->setAttribute('base_score', 15.0);
    $content->setAttribute('type_coefficient', 1.5);
    $content->setAttribute('freshness_score', 5.0);
    $content->setAttribute('engagement_score', 0.5);

    $resource = new ContentResource($content);
    $array = $resource->toArray(request());

    expect($array['scores'])->toBe([
        'total' => 25.5,
        'base_score' => 15.0,
        'type_coefficient' => 1.5,
        'freshness_score' => 5.0,
        'engagement_score' => 0.5,
    ]);
});

it('handles tags as array', function () {
    $content = Content::factory()->make([
        'type' => ContentType::VIDEO,
        'tags' => ['php', 'laravel', 'testing'],
    ]);

    $content->setRelation('provider', Provider::factory()->make());

    $resource = new ContentResource($content);
    $array = $resource->toArray(request());

    expect($array['tags'])->toBe(['php', 'laravel', 'testing']);
});

