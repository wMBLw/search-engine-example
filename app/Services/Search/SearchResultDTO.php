<?php

namespace App\Services\Search;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

readonly final class SearchResultDTO
{
    private LengthAwarePaginator $contents;

    public function __construct(LengthAwarePaginator $contents)
    {
        $this->contents = $contents;
    }

    public function getContents(): LengthAwarePaginator
    {
        return $this->contents;
    }
}

