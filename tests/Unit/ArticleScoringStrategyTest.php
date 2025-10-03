<?php

use App\Enums\ContentType;
use App\Models\Content;
use App\Services\Search\Strategies\ArticleScoringStrategy;

beforeEach(function () {
    $this->strategy = new ArticleScoringStrategy();
});

it('calculates base score correctly for articles', function () {
    $content = new Content([
        'type' => ContentType::ARTICLE,
        'reading_time' => 8,
        'reactions' => 450,
    ]);

    $baseScore = $this->strategy->calculateBaseScore($content);

    // Base Score = reading_time + (reactions / 50)
    // = 8 + (450 / 50)
    // = 8 + 9 = 17
    expect($baseScore)->toBe(17.0);
});

it('calculates base score with zero values', function () {
    $content = new Content([
        'type' => ContentType::ARTICLE,
        'reading_time' => 0,
        'reactions' => 0,
    ]);

    $baseScore = $this->strategy->calculateBaseScore($content);

    expect($baseScore)->toBe(0.0);
});

it('returns correct type coefficient for articles', function () {
    $coefficient = $this->strategy->getTypeCoefficient();

    expect($coefficient)->toBe(1.0);
});

it('calculates engagement score correctly', function () {
    $content = new Content([
        'type' => ContentType::ARTICLE,
        'reading_time' => 8,
        'reactions' => 450
    ]);

    $engagementScore = $this->strategy->calculateEngagementScore($content);

    // Engagement = (reactions / reading_time) * 5
    // = (450 / 8) * 5
    // = 56.25 * 5 = 281.25
    expect($engagementScore)->toBe(281.25);
});

it('handles zero reading time in engagement calculation', function () {
    $content = new Content([
        'type' => ContentType::ARTICLE,
        'reading_time' => 0,
        'reactions' => 450,
    ]);

    $engagementScore = $this->strategy->calculateEngagementScore($content);

    expect($engagementScore)->toBe(0.0);
});

it('calculates final score correctly', function () {
    $content = new Content([
        'type' => ContentType::ARTICLE,
        'reading_time' => 8,
        'reactions' => 450,
    ]);

    $freshnessScore = 3.0;
    $finalScore = $this->strategy->calculateFinalScore($content, $freshnessScore);

    // Final Score = (Base Score * Type Coefficient) + Freshness Score + Engagement Score
    // Base Score = 8 + (450 / 50) = 17
    // Type Coefficient = 1.0
    // Engagement Score =  56.25 * 5 = 281.25
    // Final = (17 * 1.0) + 3 + 281.25 = 301.25
    expect($finalScore)->toBe(301.25);
});

it('handles high engagement articles', function () {
    $content = new Content([
        'type' => ContentType::ARTICLE,
        'reading_time' => 5,
        'reactions' => 1000,
    ]);

    $freshnessScore = 5.0;
    $finalScore = $this->strategy->calculateFinalScore($content, $freshnessScore);

    // Base Score = 5 + 1000/50 = 25
    // Engagement = (1000/5) * 5 = 1000
    // Final = 25 + 5 + 1000 = 1030
    expect($finalScore)->toBe(1030.0);
    expect($finalScore)->toBeGreaterThan(1000);
});

