<?php

namespace App\Repositories\Search;

use App\Filters\ContentTypeFilter;
use App\Filters\KeywordFilter;
use App\Filters\SortingFilter;
use App\Models\Content;
use App\Services\Search\SearchCriteriaDTO;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Cache;

class SearchRepository implements SearchRepositoryInterface
{
    public static function getStatisticsCacheKey(): string
    {
        return config('search.statistics.cache_key');
    }

    private function getStatisticsCacheTTL(): int
    {
        return config('search.statistics.cache_ttl');
    }

    public function search(SearchCriteriaDTO $criteria): LengthAwarePaginator
    {
        $query = Content::query()->with(['provider:id,name']);

        $query = app(Pipeline::class)
            ->send($query)
            ->through([
                new KeywordFilter($criteria),
                new ContentTypeFilter($criteria),
                new SortingFilter($criteria),
            ])
            ->thenReturn();

        return $query->paginate($criteria->getPerPage(), ['*'], 'page', $criteria->getPage());
    }

    public function getStatistics(): array
    {
        return Cache::remember(
            self::getStatisticsCacheKey(),
            $this->getStatisticsCacheTTL(),
            function () {

                //I know this can be a problem with large datasets.
                // We can't retrieve everything from the database at once.
                // I did this for the cache and observer example.
                $contents = Content::with('provider:id,name')->get();

                $now = now();
                $last24h = $now->copy()->subDay();
                $last7d = $now->copy()->subDays(7);
                $last30d = $now->copy()->subDays(30);

                $totalContents = $contents->count();
                $totalVideos = $contents->where('type', 'video')->count();
                $totalArticles = $contents->where('type', 'article')->count();
                $totalViews = $contents->sum('views');
                $totalLikes = $contents->sum('likes');
                $totalComments = $contents->sum('comments');

                $contentsByProvider = $contents->groupBy('provider_id')
                    ->map(fn($group) => [
                        'provider_name' => $group->first()->provider->name,
                        'count' => $group->count(),
                    ])
                    ->values()
                    ->toArray();

                $recentActivity = [
                    'last_24h' => $contents->where('created_at', '>=', $last24h)->count(),
                    'last_7d' => $contents->where('created_at', '>=', $last7d)->count(),
                    'last_30d' => $contents->where('created_at', '>=', $last30d)->count(),
                ];


                return [
                    'total_contents' => $totalContents,
                    'total_videos' => $totalVideos,
                    'total_articles' => $totalArticles,
                    'total_views' => $totalViews,
                    'total_likes' => $totalLikes,
                    'total_comments' => $totalComments,
                    'contents_by_provider' => $contentsByProvider,
                    'recent_activity' => $recentActivity,
                    'cached_at' => now()->toString(),
                ];
            }
        );
    }

}

