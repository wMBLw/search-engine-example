<?php

namespace App\Services\Search;

use App\Models\Content;
use Carbon\Carbon;

/**
 * Helper class for common scoring utilities
 */
class ScoreCalculator
{
    /**
     * Calculate freshness score based on published date
     *
     * 1 week içinde: +5
     * 1 month içinde: +3
     * 3 month içinde: +1
     *  older: +0
     */
    public static function calculateFreshnessScore(Content $content): float
    {
        if (!$content->published_at) {
            return 0;
        }

        $now = Carbon::now();
        $publishedAt = Carbon::parse($content->published_at);
        $daysSincePublished = $publishedAt->diffInDays($now);

        if ($daysSincePublished <= 7) {
            return 5;
        }

        if ($daysSincePublished <= 30) {
            return 3;
        }

        if ($daysSincePublished <= 90) {
            return 1;
        }

        return 0;
    }
}

