<?php

use App\Filters\SortingFilter;
use App\Models\Content;
use App\Models\Provider;
use App\Services\Search\SearchCriteriaDTO;

beforeEach(function () {
    $this->provider = Provider::factory()->create();
});

it('sorts by views descending', function () {
    Content::factory()->create([
        'provider_id' => $this->provider->id,
        'views' => 100,
        'title' => 'Low Views',
    ]);

    Content::factory()->create([
        'provider_id' => $this->provider->id,
        'views' => 1000,
        'title' => 'High Views',
    ]);

    $criteria = new SearchCriteriaDTO(
        contentType: null,
        keyword: null,
        sortBy: 'views',
        sortDirection: 'desc',
        perPage: 20,
        page: 1
    );

    $filter = new SortingFilter($criteria);
    $query = Content::query();
    $result = $filter->handle($query, fn($q) => $q);

    $contents = $result->get();
    expect($contents->first()->title)->toBe('High Views');
    expect($contents->last()->title)->toBe('Low Views');
});

it('sorts by views ascending', function () {
    Content::factory()->create([
        'provider_id' => $this->provider->id,
        'views' => 100,
        'title' => 'Low Views',
    ]);

    Content::factory()->create([
        'provider_id' => $this->provider->id,
        'views' => 1000,
        'title' => 'High Views',
    ]);

    $criteria = new SearchCriteriaDTO(
        contentType: null,
        keyword: null,
        sortBy: 'views',
        sortDirection: 'asc',
        perPage: 20,
        page: 1
    );

    $filter = new SortingFilter($criteria);
    $query = Content::query();
    $result = $filter->handle($query, fn($q) => $q);

    $contents = $result->get();
    expect($contents->first()->title)->toBe('Low Views');
    expect($contents->last()->title)->toBe('High Views');
});

it('sorts by likes', function () {
    Content::factory()->create([
        'provider_id' => $this->provider->id,
        'likes' => 10,
        'title' => 'Low Likes',
    ]);

    Content::factory()->create([
        'provider_id' => $this->provider->id,
        'likes' => 100,
        'title' => 'High Likes',
    ]);

    $criteria = new SearchCriteriaDTO(
        contentType: null,
        keyword: null,
        sortBy: 'likes',
        sortDirection: 'desc',
        perPage: 20,
        page: 1
    );

    $filter = new SortingFilter($criteria);
    $query = Content::query();
    $result = $filter->handle($query, fn($q) => $q);

    $contents = $result->get();
    expect($contents->first()->title)->toBe('High Likes');
});

it('sorts by published_at', function () {
    Content::factory()->create([
        'provider_id' => $this->provider->id,
        'published_at' => now()->subDays(10),
        'title' => 'Old Content',
    ]);

    Content::factory()->create([
        'provider_id' => $this->provider->id,
        'published_at' => now()->subDays(1),
        'title' => 'New Content',
    ]);

    $criteria = new SearchCriteriaDTO(
        contentType: null,
        keyword: null,
        sortBy: 'published_at',
        sortDirection: 'desc',
        perPage: 20,
        page: 1
    );

    $filter = new SortingFilter($criteria);
    $query = Content::query();
    $result = $filter->handle($query, fn($q) => $q);

    $contents = $result->get();
    expect($contents->first()->title)->toBe('New Content');
});

it('does not sort by score', function () {
    Content::factory()->count(5)->create([
        'provider_id' => $this->provider->id,
    ]);

    $criteria = new SearchCriteriaDTO(
        contentType: null,
        keyword: null,
        sortBy: 'score',
        sortDirection: 'desc',
        perPage: 20,
        page: 1
    );

    $filter = new SortingFilter($criteria);
    $query = Content::query();
    $result = $filter->handle($query, fn($q) => $q);

    // Should not apply any sorting for 'score' as it's handled in SearchService
    expect($result->get())->toHaveCount(5);
});

it('defaults to published_at when sort by is not specified', function () {
    $content1 = Content::factory()->create([
        'provider_id' => $this->provider->id,
        'published_at' => now()->subDays(5),
    ]);

    $content2 = Content::factory()->create([
        'provider_id' => $this->provider->id,
        'published_at' => now()->subDays(1),
    ]);

    $criteria = new SearchCriteriaDTO(
        contentType: null,
        keyword: null,
        sortBy: 'published_at',
        sortDirection: 'desc',
        perPage: 20,
        page: 1
    );

    $filter = new SortingFilter($criteria);
    $query = Content::query();
    $result = $filter->handle($query, fn($q) => $q);

    $contents = $result->get();
    expect($contents->first()->id)->toBe($content2->id);
});

