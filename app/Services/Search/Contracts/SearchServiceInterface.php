<?php

namespace App\Services\Search\Contracts;

use App\Services\Search\SearchCriteriaDTO;
use App\Services\Search\SearchResultDTO;

interface SearchServiceInterface
{
    public function search(SearchCriteriaDTO $criteria): SearchResultDTO;
    public function getDashboardStatistics(): array;
}

