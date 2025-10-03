<?php

use App\Models\Content;
use App\Models\Provider;
use App\Repositories\Search\SearchRepository;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    $this->provider = Provider::factory()->create();
    Cache::flush();
});

it('clears statistics cache when content is created', function () {
    $repository = new SearchRepository();
    $stats = $repository->getStatistics();

    expect(Cache::has(SearchRepository::getStatisticsCacheKey()))->toBeTrue();

    Content::factory()->create(['provider_id' => $this->provider->id]);

    expect(Cache::has(SearchRepository::getStatisticsCacheKey()))->toBeFalse();
});

it('clears statistics cache when content is updated', function () {
    $content = Content::factory()->create(['provider_id' => $this->provider->id]);

    $repository = new SearchRepository();
    $stats = $repository->getStatistics();

    expect(Cache::has(SearchRepository::getStatisticsCacheKey()))->toBeTrue();

    $content->update(['views' => 9999]);

    expect(Cache::has(SearchRepository::getStatisticsCacheKey()))->toBeFalse();
});

it('clears statistics cache when content is deleted', function () {
    $content = Content::factory()->create(['provider_id' => $this->provider->id]);

    $repository = new SearchRepository();
    $stats = $repository->getStatistics();

    expect(Cache::has(SearchRepository::getStatisticsCacheKey()))->toBeTrue();

    $content->delete();

    expect(Cache::has(SearchRepository::getStatisticsCacheKey()))->toBeFalse();
});

it('allows statistics to be re-cached after invalidation', function () {
    $repository = new SearchRepository();

    $stats1 = $repository->getStatistics();
    expect(Cache::has(SearchRepository::getStatisticsCacheKey()))->toBeTrue();

    Content::factory()->create(['provider_id' => $this->provider->id]);
    expect(Cache::has(SearchRepository::getStatisticsCacheKey()))->toBeFalse();

    $stats2 = $repository->getStatistics();
    expect(Cache::has(SearchRepository::getStatisticsCacheKey()))->toBeTrue();

    expect($stats2['total_contents'])->toBeGreaterThan($stats1['total_contents']);
});

