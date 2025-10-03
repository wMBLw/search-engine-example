<?php

namespace App\Services\Search\Strategies;

use App\Models\Content;
use App\Services\Search\Contracts\ScoringStrategyInterface;

class VideoScoringStrategy implements ScoringStrategyInterface
{
    //maybe retrieve data from database or other resource
    private const TYPE_COEFFICIENT = 1.5;
    private const ENGAGEMENT_MULTIPLIER = 10;

    /**
     * Calculate base score
     * Video: views / 1000 + (likes / 100)
     */
    public function calculateBaseScore(Content $content): float
    {
        $views = $content->views ?? 0;
        $likes = $content->likes ?? 0;

        $viewsScore = $views / 1000;
        $likesScore = $likes / 100;

        return $viewsScore + $likesScore;
    }

    /**
     * Content type coefficient
     * Video: 1.5
     */
    public function getTypeCoefficient(): float
    {
        return self::TYPE_COEFFICIENT;
    }

    /**
     * Calculate interaction score
     * Video: (likes / views) * 10
     */
    public function calculateEngagementScore(Content $content): float
    {
        $views = $content->views ?? 0;
        $likes = $content->likes ?? 0;

        // Check Division by zero
        if ($views === 0) {
            return 0;
        }

        $engagementRate = $likes / $views;
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

