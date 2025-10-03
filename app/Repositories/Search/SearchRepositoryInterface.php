<?php

namespace App\Repositories\Search;

use App\Services\Search\SearchCriteriaDTO;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface SearchRepositoryInterface
{
    public function search(SearchCriteriaDTO $criteria): LengthAwarePaginator;
}

