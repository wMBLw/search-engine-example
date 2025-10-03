<?php

namespace App\Filters;

use App\Services\Search\SearchCriteriaDTO;
use Closure;
use Illuminate\Database\Eloquent\Builder;

class SortingFilter
{
    private SearchCriteriaDTO $criteria;
    public function __construct(SearchCriteriaDTO $criteria)
    {
        $this->criteria = $criteria;
    }

    public function handle(Builder $query, Closure $next)
    {
        $sortBy = $this->criteria->getSortBy();
        $direction = $this->criteria->getSortDirection();

        // Rankings other than Score are done at the database layer
        // The score ranking will be done in SearchService because it is calculated with Strategy Pattern
        if ($sortBy !== 'score') {
            $this->applySorting($query, $sortBy, $direction);
        }

        return $next($query);
    }

    private function applySorting(Builder $query, string $sortBy, string $direction): void
    {
        switch ($sortBy) {
            case 'views':
                $query->orderBy('views', $direction);
                break;

            case 'likes':
                $query->orderBy('likes', $direction);
                break;

            case 'published_at':
                $query->orderBy('published_at', $direction);
                break;

            case 'title':
                $query->orderBy('title', $direction);
                break;

            default:
                $query->orderBy('published_at', $direction);
                break;
        }
    }
}

