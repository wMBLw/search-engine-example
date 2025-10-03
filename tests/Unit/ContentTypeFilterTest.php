<?php

use App\Enums\ContentType;
use App\Filters\ContentTypeFilter;
use App\Models\Content;
use App\Models\Provider;
use App\Services\Search\SearchCriteriaDTO;

beforeEach(function () {
    $this->provider = Provider::factory()->create();
});

it('filters content by video type', function () {
    Content::factory()->count(5)->create([
        'provider_id' => $this->provider->id,
        'type' => ContentType::VIDEO,
    ]);

    Content::factory()->count(3)->create([
        'provider_id' => $this->provider->id,
        'type' => ContentType::ARTICLE,
    ]);

    $criteria = new SearchCriteriaDTO(
        contentType: ContentType::VIDEO,
        keyword: null,
        sortBy: 'published_at',
        sortDirection: 'desc',
        perPage: 20,
        page: 1
    );

    $filter = new ContentTypeFilter($criteria);
    $query = Content::query();
    $result = $filter->handle($query, fn($q) => $q);

    $contents = $result->get();
    expect($contents)->toHaveCount(5);
    $contents->each(fn($content) => expect($content->type)->toBe(ContentType::VIDEO));
});

it('filters content by article type', function () {
    Content::factory()->count(5)->create([
        'provider_id' => $this->provider->id,
        'type' => ContentType::VIDEO,
    ]);

    Content::factory()->count(3)->create([
        'provider_id' => $this->provider->id,
        'type' => ContentType::ARTICLE,
    ]);

    $criteria = new SearchCriteriaDTO(
        contentType: ContentType::ARTICLE,
        keyword: null,
        sortBy: 'published_at',
        sortDirection: 'desc',
        perPage: 20,
        page: 1
    );

    $filter = new ContentTypeFilter($criteria);
    $query = Content::query();
    $result = $filter->handle($query, fn($q) => $q);

    $contents = $result->get();
    expect($contents)->toHaveCount(3);
    $contents->each(fn($content) => expect($content->type)->toBe(ContentType::ARTICLE));
});

it('does not filter when type is null', function () {
    Content::factory()->count(5)->create([
        'provider_id' => $this->provider->id,
        'type' => ContentType::VIDEO,
    ]);

    Content::factory()->count(3)->create([
        'provider_id' => $this->provider->id,
        'type' => ContentType::ARTICLE,
    ]);

    $criteria = new SearchCriteriaDTO(
        contentType: null,
        keyword: null,
        sortBy: 'published_at',
        sortDirection: 'desc',
        perPage: 20,
        page: 1
    );

    $filter = new ContentTypeFilter($criteria);
    $query = Content::query();
    $result = $filter->handle($query, fn($q) => $q);

    expect($result->get())->toHaveCount(8);
});

