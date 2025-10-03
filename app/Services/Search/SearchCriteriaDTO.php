<?php

namespace App\Services\Search;

use App\Enums\ContentType;

readonly final class SearchCriteriaDTO
{
    private ?string $keyword;
    private ?ContentType $contentType;
    private string $sortBy;
    private string $sortDirection;
    private int $perPage;
    private int $page;

    public function __construct(
        ?string $keyword = null,
        ?ContentType $contentType = null,
        string $sortBy = 'score',
        string $sortDirection = 'desc',
        int $perPage = 20,
        int $page = 1
    ) {
        $this->keyword = $keyword;
        $this->contentType = $contentType;
        $this->sortBy = $sortBy;
        $this->sortDirection = $sortDirection;
        $this->perPage = min($perPage, 100);
        $this->page = max($page, 1);
    }

    public function getKeyword(): ?string
    {
        return $this->keyword;
    }

    public function getContentType(): ?ContentType
    {
        return $this->contentType;
    }

    public function getSortBy(): string
    {
        return $this->sortBy;
    }

    public function getSortDirection(): string
    {
        return $this->sortDirection;
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function hasKeyword(): bool
    {
        return !empty($this->keyword);
    }

    public function hasContentType(): bool
    {
        return $this->contentType !== null;
    }
}

