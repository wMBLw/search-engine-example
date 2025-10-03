<?php

namespace App\Http\Controllers;

use App\Enums\ContentType;
use App\Http\Requests\SearchRequest;
use App\Http\Resources\SearchResultResource;
use App\Http\Resources\StatisticsResource;
use App\Services\Search\Contracts\SearchServiceInterface;
use App\Services\Search\SearchCriteriaDTO;

class SearchController extends Controller
{
    private SearchServiceInterface $searchService;

    public function __construct(SearchServiceInterface $searchService)
    {
        $this->searchService = $searchService;
    }

    public function search(SearchRequest $request): SearchResultResource
    {
        //Normally I like to do it with chain, but I created dto like this for a difference.
        $criteria = new SearchCriteriaDTO(
            contentType: $request->filled('type') ? ContentType::from($request->input('type')) : null,
            keyword: $request->input('keyword'),
            sortBy: $request->input('sort_by', 'score'),
            sortDirection: $request->input('sort_direction', 'desc'),
            perPage: $request->input('per_page', 20),
            page: $request->input('page', 1)
        );

        $result = $this->searchService->search($criteria);

        return new SearchResultResource(
            $result->getContents()
        );
    }

    public function statistics(): StatisticsResource
    {
        $stats = $this->searchService->getDashboardStatistics();

        return new StatisticsResource($stats);
    }
}

