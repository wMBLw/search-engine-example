<?php

namespace App\Repositories\Search;

use App\Filters\ContentTypeFilter;
use App\Filters\KeywordFilter;
use App\Filters\SortingFilter;
use App\Models\Content;
use App\Services\Search\SearchCriteriaDTO;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pipeline\Pipeline;

class SearchRepository implements SearchRepositoryInterface
{
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

}

