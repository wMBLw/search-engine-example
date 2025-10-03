<?php

namespace App\Filters;

use App\Services\Search\SearchCriteriaDTO;
use Closure;
use Illuminate\Database\Eloquent\Builder;

class ContentTypeFilter
{
    private SearchCriteriaDTO $criteria;
    public function __construct(SearchCriteriaDTO $criteria)
    {
        $this->criteria = $criteria;
    }

    public function handle(Builder $query, Closure $next)
    {
        if ($this->criteria->hasContentType()) {
            $query->where('type', $this->criteria->getContentType());
        }

        return $next($query);
    }
}

