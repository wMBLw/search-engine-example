<?php

use App\Filters\KeywordFilter;
use App\Models\Content;
use App\Models\Provider;
use App\Services\Search\SearchCriteriaDTO;

beforeEach(function () {
    $this->provider = Provider::factory()->create();
});

it('filters content by keyword in title', function () {
    Content::factory()->create([
        'provider_id' => $this->provider->id,
        'title' => 'Laravel Tutorial for Beginners',
        'tags' => ['php', 'backend'],
    ]);

    Content::factory()->create([
        'provider_id' => $this->provider->id,
        'title' => 'PHP Advanced Techniques',
        'tags' => ['php', 'advanced'],
    ]);

    $criteria = new SearchCriteriaDTO(
        contentType: null,
        keyword: 'Laravel',
        sortBy: 'published_at',
        sortDirection: 'desc',
        perPage: 20,
        page: 1
    );

    $filter = new KeywordFilter($criteria);
    $query = Content::query();
    $result = $filter->handle($query, fn($q) => $q);

    expect($result->get())->toHaveCount(1);
    expect($result->first()->title)->toContain('Laravel');
});

it('does not filter when keyword is null', function () {
    Content::factory()->count(5)->create([
        'provider_id' => $this->provider->id,
        'tags' => ['php', 'testing'],
    ]);

    $criteria = new SearchCriteriaDTO(
        contentType: null,
        keyword: null,
        sortBy: 'published_at',
        sortDirection: 'desc',
        perPage: 20,
        page: 1
    );

    $filter = new KeywordFilter($criteria);
    $query = Content::query();
    $result = $filter->handle($query, fn($q) => $q);

    expect($result->get())->toHaveCount(5);
});

it('is case insensitive', function () {
    Content::factory()->create([
        'provider_id' => $this->provider->id,
        'title' => 'Laravel Tutorial',
        'tags' => ['php', 'backend'],
    ]);

    Content::factory()->create([
        'provider_id' => $this->provider->id,
        'title' => 'Python Basics',
        'tags' => ['python', 'beginner'],
    ]);

    $criteria = new SearchCriteriaDTO(
        contentType: null,
        keyword: 'laravel',
        sortBy: 'published_at',
        sortDirection: 'desc',
        perPage: 20,
        page: 1
    );

    $filter = new KeywordFilter($criteria);
    $query = Content::query();
    $result = $filter->handle($query, fn($q) => $q);

    expect($result->get())->toHaveCount(1);
});

it('returns empty when no match found', function () {
    Content::factory()->count(3)->create([
        'provider_id' => $this->provider->id,
        'title' => 'PHP Tutorial',
        'tags' => ['php', 'backend'],
    ]);

    $criteria = new SearchCriteriaDTO(
        contentType: null,
        keyword: 'JavaScript',
        sortBy: 'published_at',
        sortDirection: 'desc',
        perPage: 20,
        page: 1
    );

    $filter = new KeywordFilter($criteria);
    $query = Content::query();
    $result = $filter->handle($query, fn($q) => $q);

    expect($result->get())->toBeEmpty();
});
