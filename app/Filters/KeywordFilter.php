<?php

namespace App\Filters;

use App\Services\Search\SearchCriteriaDTO;
use Closure;
use Illuminate\Database\Eloquent\Builder;

class KeywordFilter
{
    private SearchCriteriaDTO $criteria;
    public function __construct(SearchCriteriaDTO $criteria)
    {
        $this->criteria = $criteria;
    }

    public function handle(Builder $query, Closure $next)
    {
        if ($this->criteria->hasKeyword()) {
            $keyword = $this->criteria->getKeyword();

            $query->where(function ($subQuery) use ($keyword) {
                $subQuery->where('title', 'LIKE', "%{$keyword}%")
                    ->orWhereRaw('LOWER(tags) LIKE ?', ['%' . strtolower($keyword) . '%']);
            });
        }

        return $next($query);
    }
}

