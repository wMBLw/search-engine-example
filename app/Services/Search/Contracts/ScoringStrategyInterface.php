<?php

namespace App\Services\Search\Contracts;

use App\Models\Content;
interface ScoringStrategyInterface
{
    /**
     * Calculate base score
     * Video: views / 1000 + (likes / 100)
     * Article: reading_time + (reactions / 50)
     */
    public function calculateBaseScore(Content $content): float;

    /**
     * Content type coefficient
     * Video: 1.5
     * Metin: 1.0
     */
    public function getTypeCoefficient(): float;

    /**
     * Calculate interaction score
     * Video: (likes / views) * 10
     * Metin: (reactions / reading_time) * 5
     */
    public function calculateEngagementScore(Content $content): float;

    /**
     * Calculate final score
     * Final score = (Basic Score * Coefficient) + Timeliness + Interaction
     */
    public function calculateFinalScore(Content $content, float $freshnessScore): float;
}

