<?php

use App\Enums\ContentType;
use App\Models\Content;
use App\Models\Provider;
use App\Repositories\Search\SearchRepository;
use App\Services\Search\SearchCriteriaDTO;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    $this->repository = new SearchRepository();
    $this->provider = Provider::factory()->create(['name' => 'Test Provider']);
});

describe('search method', function () {
    
    it('returns paginated results', function () {
        Content::factory()->count(25)->create([
            'provider_id' => $this->provider->id,
        ]);

        $criteria = new SearchCriteriaDTO(
            contentType: null,
            keyword: null,
            sortBy: 'published_at',
            sortDirection: 'desc',
            perPage: 10,
            page: 1
        );

        $result = $this->repository->search($criteria);

        expect($result)->toBeInstanceOf(Illuminate\Contracts\Pagination\LengthAwarePaginator::class);
        expect($result->total())->toBe(25);
        expect($result->perPage())->toBe(10);
        expect($result->currentPage())->toBe(1);
    });

    it('filters by content type', function () {
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

        $result = $this->repository->search($criteria);

        $items = collect($result->items());
        expect($result->total())->toBe(5);
        $items->each(fn($item) => expect($item->type)->toBe(ContentType::VIDEO));
    });

    it('filters by keyword in title', function () {
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

        $result = $this->repository->search($criteria);

        expect($result->total())->toBe(1);
        expect($result->first()->title)->toContain('Laravel');
    });

    it('sorts by views', function () {
        Content::factory()->create([
            'provider_id' => $this->provider->id,
            'views' => 100,
            'title' => 'Low Views'
        ]);

        Content::factory()->create([
            'provider_id' => $this->provider->id,
            'views' => 1000,
            'title' => 'High Views'
        ]);

        $criteria = new SearchCriteriaDTO(
            contentType: null,
            keyword: null,
            sortBy: 'views',
            sortDirection: 'desc',
            perPage: 20,
            page: 1
        );

        $result = $this->repository->search($criteria);

        expect($result->first()->title)->toBe('High Views');
        expect($result->last()->title)->toBe('Low Views');
    });

    it('eager loads provider relationship', function () {
        Content::factory()->count(3)->create([
            'provider_id' => $this->provider->id,
        ]);

        $criteria = new SearchCriteriaDTO(
            contentType: null,
            keyword: null,
            sortBy: 'published_at',
            sortDirection: 'desc',
            perPage: 20,
            page: 1
        );

        $result = $this->repository->search($criteria);

        expect($result->first()->relationLoaded('provider'))->toBeTrue();
    });
});

describe('getStatistics method', function () {
    
    it('returns correct statistics structure', function () {
        Content::factory()->count(5)->create([
            'provider_id' => $this->provider->id,
            'type' => ContentType::VIDEO,
        ]);

        $stats = $this->repository->getStatistics();

        expect($stats)->toHaveKeys([
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
    });

    it('calculates total contents correctly', function () {
        Content::factory()->count(10)->create([
            'provider_id' => $this->provider->id,
        ]);

        Cache::forget(SearchRepository::getStatisticsCacheKey());
        $stats = $this->repository->getStatistics();

        expect($stats['total_contents'])->toBe(10);
    });

    it('separates videos and articles count', function () {
        Content::factory()->count(7)->create([
            'provider_id' => $this->provider->id,
            'type' => ContentType::VIDEO,
        ]);

        Content::factory()->count(3)->create([
            'provider_id' => $this->provider->id,
            'type' => ContentType::ARTICLE,
        ]);

        Cache::forget(SearchRepository::getStatisticsCacheKey());
        $stats = $this->repository->getStatistics();

        expect($stats['total_videos'])->toBe(7);
        expect($stats['total_articles'])->toBe(3);
    });

    it('calculates metrics sum correctly', function () {
        Content::factory()->create([
            'provider_id' => $this->provider->id,
            'views' => 1000,
            'likes' => 50,
            'comments' => 10,
        ]);

        Content::factory()->create([
            'provider_id' => $this->provider->id,
            'views' => 2000,
            'likes' => 100,
            'comments' => 20,
        ]);

        Cache::forget(SearchRepository::getStatisticsCacheKey());
        $stats = $this->repository->getStatistics();

        expect($stats['total_views'])->toBe(3000);
        expect($stats['total_likes'])->toBe(150);
        expect($stats['total_comments'])->toBe(30);
    });

    it('groups contents by provider', function () {
        $provider2 = Provider::factory()->create(['name' => 'Provider 2']);

        Content::factory()->count(3)->create(['provider_id' => $this->provider->id]);
        Content::factory()->count(2)->create(['provider_id' => $provider2->id]);

        Cache::forget(SearchRepository::getStatisticsCacheKey());
        $stats = $this->repository->getStatistics();

        expect($stats['contents_by_provider'])->toHaveCount(2);
        expect($stats['contents_by_provider'][0]['count'])->toBeIn([2, 3]);
    });

    it('caches statistics', function () {
        Content::factory()->count(5)->create([
            'provider_id' => $this->provider->id,
        ]);

        Cache::forget(SearchRepository::getStatisticsCacheKey());
        
        $stats1 = $this->repository->getStatistics();
        
        // Second call should use cache
        $stats2 = $this->repository->getStatistics();

        // Should return same cached value
        expect($stats1['total_contents'])->toBe($stats2['total_contents']);
        expect($stats1['cached_at'])->toBe($stats2['cached_at']);
        expect($stats1['total_contents'])->toBe(5);
    });

    it('tracks recent activity', function () {
        Content::factory()->create([
            'provider_id' => $this->provider->id,
            'created_at' => now()->subHours(12), // Within 24h
        ]);

        Content::factory()->create([
            'provider_id' => $this->provider->id,
            'created_at' => now()->subDays(5), // Within 7d
        ]);

        Content::factory()->create([
            'provider_id' => $this->provider->id,
            'created_at' => now()->subDays(20), // Within 30d
        ]);

        Cache::forget(SearchRepository::getStatisticsCacheKey());
        $stats = $this->repository->getStatistics();

        expect($stats['recent_activity']['last_24h'])->toBeGreaterThanOrEqual(1);
        expect($stats['recent_activity']['last_7d'])->toBeGreaterThanOrEqual(2);
        expect($stats['recent_activity']['last_30d'])->toBeGreaterThanOrEqual(3);
    });
});

