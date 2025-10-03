<?php

namespace App\Services\Search\Strategies;

use App\Models\Content;
use App\Services\Search\Contracts\ScoringStrategyInterface;
class ArticleScoringStrategy implements ScoringStrategyInterface
{
    private const TYPE_COEFFICIENT = 1.0;
    private const ENGAGEMENT_MULTIPLIER = 5;

    /**
     * Calculate base score
     * reading_time + (reactions / 50)
     */
    public function calculateBaseScore(Content $content): float
    {
        $readingTime = $content->reading_time ?? 0;
        $reactions = $content->reactions ?? 0;

        $reactionsScore = $reactions / 50;

        return $readingTime + $reactionsScore;
    }

    /**
     * Content type coefficient
     * Article : 1.0
     */
    public function getTypeCoefficient(): float
    {
        return self::TYPE_COEFFICIENT;
    }

    /**
     * Calculate interaction score
     * Article: (reactions / reading_time) * 5
     */
    public function calculateEngagementScore(Content $content): float
    {
        $readingTime = $content->reading_time ?? 0;
        $reactions = $content->reactions ?? 0;

        // Check Division by zero
        if ($readingTime === 0) {
            return 0;
        }

        $engagementRate = $reactions / $readingTime;
        return $engagementRate * self::ENGAGEMENT_MULTIPLIER;
    }

    /**
     * Calculate final score
     * Final score = (Basic Score * Coefficient) + Timeliness + Interaction
     */
    public function calculateFinalScore(Content $content, float $freshnessScore): float
    {
        $baseScore = $this->calculateBaseScore($content);
        $coefficient = $this->getTypeCoefficient();
        $engagementScore = $this->calculateEngagementScore($content);

        $finalScore = ($baseScore * $coefficient) + $freshnessScore + $engagementScore;

        return round($finalScore, 2);
    }
}

