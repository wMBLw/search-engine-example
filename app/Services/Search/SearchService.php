<?php

namespace App\Services\Search;

use App\Models\Content;
use App\Repositories\Search\SearchRepositoryInterface;
use App\Services\Search\Contracts\SearchServiceInterface;
use Illuminate\Support\Collection;

class SearchService implements SearchServiceInterface
{
    private SearchRepositoryInterface $searchRepository;

    public function __construct(SearchRepositoryInterface $searchRepository)
    {
        $this->searchRepository = $searchRepository;
    }

    public function search(SearchCriteriaDTO $criteria): SearchResultDTO
    {
        $paginatedContents = $this->searchRepository->search($criteria);

        $contentsWithScores = $paginatedContents->getCollection()->map(function (Content $content) use ($criteria) {
            return $this->enrichContentWithScores($content, $criteria->getKeyword());
        });

        if ($criteria->getSortBy() === 'score') {
            $contentsWithScores = $this->sortByScore($contentsWithScores, $criteria->getSortDirection());
        }
        $paginatedContents->setCollection($contentsWithScores);

        return new SearchResultDTO($paginatedContents);
    }

    public function getDashboardStatistics(): array
    {
        $stats = $this->searchRepository->getStatistics();

        return $stats;
    }

    private function enrichContentWithScores(Content $content, ?string $keyword = null): Content
    {
        $scoringStrategy = ScoringStrategyFactory::create($content->type);

        $freshnessScore = ScoreCalculator::calculateFreshnessScore($content);

        $baseScore = $scoringStrategy->calculateBaseScore($content);
        $engagementScore = $scoringStrategy->calculateEngagementScore($content);
        $totalScore = $scoringStrategy->calculateFinalScore($content, $freshnessScore);

        $content->setAttribute('score', $totalScore);
        $content->setAttribute('base_score', round($baseScore, 2));
        $content->setAttribute('type_coefficient', $scoringStrategy->getTypeCoefficient());
        $content->setAttribute('freshness_score', round($freshnessScore, 2));
        $content->setAttribute('engagement_score', round($engagementScore, 2));

        return $content;
    }

    private function sortByScore(Collection $contents, string $direction = 'desc'): Collection
    {
        return $contents->sortBy(function (Content $content) {
            return $content->getAttribute('score');
        },SORT_REGULAR,$direction);
    }
}

