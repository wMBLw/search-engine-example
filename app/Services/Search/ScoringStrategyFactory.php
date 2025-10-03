<?php

namespace App\Services\Search;

use App\Enums\ContentType;
use App\Services\Search\Contracts\ScoringStrategyInterface;
use App\Services\Search\Strategies\ArticleScoringStrategy;
use App\Services\Search\Strategies\VideoScoringStrategy;
class ScoringStrategyFactory
{
    public static function create(ContentType $contentType): ScoringStrategyInterface
    {
        return match ($contentType) {
            ContentType::VIDEO => new VideoScoringStrategy(),
            ContentType::ARTICLE => new ArticleScoringStrategy(),
        };
    }
}

