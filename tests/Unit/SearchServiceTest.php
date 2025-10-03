<?php

use App\Enums\ContentType;
use App\Models\Content;
use App\Models\Provider;
use App\Repositories\Search\SearchRepositoryInterface;
use App\Services\Search\SearchCriteriaDTO;
use App\Services\Search\SearchResultDTO;
use App\Services\Search\SearchService;
use Illuminate\Pagination\LengthAwarePaginator;

beforeEach(function () {
    $this->searchRepository = Mockery::mock(SearchRepositoryInterface::class);
    $this->searchService = new SearchService($this->searchRepository);
    $this->provider = Provider::factory()->make(['id' => 1, 'name' => 'Test Provider']);
});

it('enriches content with scores', function () {
    $content = Content::factory()->make([
        'id' => 1,
        'type' => ContentType::VIDEO,
        'views' => 10000,
        'likes' => 500,
        'published_at' => now()->subDays(3),
    ]);
    $content->setRelation('provider', $this->provider);

    $paginator = new LengthAwarePaginator(
        collect([$content]),
        1,
        20,
        1
    );

    $criteria = new SearchCriteriaDTO(
        contentType: null,
        keyword: null,
        sortBy: 'published_at',
        sortDirection: 'desc',
        perPage: 20,
        page: 1
    );

    $this->searchRepository
        ->shouldReceive('search')
        ->with($criteria)
        ->once()
        ->andReturn($paginator);

    $result = $this->searchService->search($criteria);

    expect($result)->toBeInstanceOf(SearchResultDTO::class);

    $enrichedContent = $result->getContents()->first();
    expect($enrichedContent->getAttribute('score'))->not->toBeNull();
    expect($enrichedContent->getAttribute('base_score'))->not->toBeNull();
    expect($enrichedContent->getAttribute('freshness_score'))->toBe(5.0);
});

it('delegates statistics to repository', function () {
    $stats = [
        'total_contents' => 100,
        'total_videos' => 60,
        'total_articles' => 40,
    ];

    $this->searchRepository
        ->shouldReceive('getStatistics')
        ->once()
        ->andReturn($stats);

    $result = $this->searchService->getDashboardStatistics();

    expect($result)->toBe($stats);
});

it('handles empty search results', function () {
    $paginator = new LengthAwarePaginator(
        collect([]),
        0,
        20,
        1
    );

    $criteria = new SearchCriteriaDTO(
        contentType: null,
        keyword: 'nonexistent',
        sortBy: 'published_at',
        sortDirection: 'desc',
        perPage: 20,
        page: 1
    );

    $this->searchRepository
        ->shouldReceive('search')
        ->with($criteria)
        ->once()
        ->andReturn($paginator);

    $result = $this->searchService->search($criteria);

    expect($result->getContents())->toBeEmpty();
});

it('correctly calculates scores for video content', function () {
    $content = Content::factory()->make([
        'type' => ContentType::VIDEO,
        'views' => 10000,
        'likes' => 500,
        'published_at' => now()->subDays(3),
    ]);
    $content->setRelation('provider', $this->provider);

    $paginator = new LengthAwarePaginator(collect([$content]), 1, 20, 1);

    $criteria = new SearchCriteriaDTO(null, null, 'score', 'desc', 20, 1);

    $this->searchRepository
        ->shouldReceive('search')
        ->once()
        ->andReturn($paginator);

    $result = $this->searchService->search($criteria);
    $enrichedContent = $result->getContents()->first();

    // Base Score = 10000/1000 + 500/100 = 15
    // Type Coefficient = 1.5
    // Engagement = (500/10000) * 10 = 0.5
    // Freshness = 5 (within 7 days)
    // Total = (15 * 1.5) + 5 + 0.5 = 28
    expect($enrichedContent->getAttribute('base_score'))->toBe(15.0);
    expect($enrichedContent->getAttribute('type_coefficient'))->toBe(1.5);
    expect($enrichedContent->getAttribute('freshness_score'))->toBe(5.0);
    expect($enrichedContent->getAttribute('engagement_score'))->toBe(0.5);
    expect($enrichedContent->getAttribute('score'))->toBe(28.0);
});

it('correctly calculates scores for article content', function () {
    $content = Content::factory()->make([
        'type' => ContentType::ARTICLE,
        'reading_time' => 10,
        'reactions' => 250,
        'published_at' => now()->subDays(15),
    ]);
    $content->setRelation('provider', $this->provider);

    $paginator = new LengthAwarePaginator(collect([$content]), 1, 20, 1);

    $criteria = new SearchCriteriaDTO(null, null, 'score', 'desc', 20, 1);

    $this->searchRepository
        ->shouldReceive('search')
        ->once()
        ->andReturn($paginator);

    $result = $this->searchService->search($criteria);
    $enrichedContent = $result->getContents()->first();

    // Base Score = 10 + 250/50 = 15
    // Type Coefficient = 1.0
    // Engagement = (250/10) * 5 = 125
    // Freshness = 3 (within 30 days)
    // Total = (15 * 1.0) + 3 + 125 = 143
    expect($enrichedContent->getAttribute('base_score'))->toBe(15.0);
    expect($enrichedContent->getAttribute('type_coefficient'))->toBe(1.0);
    expect($enrichedContent->getAttribute('freshness_score'))->toBe(3.0);
    expect($enrichedContent->getAttribute('engagement_score'))->toBe(125.0);
    expect($enrichedContent->getAttribute('score'))->toBe(143.0);
});

