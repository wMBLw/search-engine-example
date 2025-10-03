<?php

use App\Enums\ContentType;
use App\Models\Content;
use App\Services\Search\Strategies\VideoScoringStrategy;

beforeEach(function () {
    $this->strategy = new VideoScoringStrategy();
});

it('calculates base score correctly for videos', function () {
    $content = new Content([
        'type' => ContentType::VIDEO,
        'views' => 10000,
        'likes' => 500,
    ]);

    $baseScore = $this->strategy->calculateBaseScore($content);

    // Base Score = views / 1000 + (likes / 100)
    // = 10000 / 1000 + (500 / 100)
    // = 10 + 5 = 15
    expect($baseScore)->toBe(15.0);
});

it('calculates base score with zero values', function () {
    $content = new Content([
        'type' => ContentType::VIDEO,
        'views' => 0,
        'likes' => 0,
    ]);

    $baseScore = $this->strategy->calculateBaseScore($content);

    expect($baseScore)->toBe(0.0);
});

it('returns correct type coefficient for videos', function () {
    $coefficient = $this->strategy->getTypeCoefficient();

    expect($coefficient)->toBe(1.5);
});

it('calculates engagement score correctly', function () {
    $content = new Content([
        'type' => ContentType::VIDEO,
        'views' => 1000,
        'likes' => 100,
    ]);

    $engagementScore = $this->strategy->calculateEngagementScore($content);

    // Engagement = (likes / views) * 10
    // = (100 / 1000) * 10
    // = 0.1 * 10 = 1.0
    expect($engagementScore)->toBe(1.0);
});

it('handles zero views in engagement calculation', function () {
    $content = new Content([
        'type' => ContentType::VIDEO,
        'views' => 0,
        'likes' => 100,
    ]);

    $engagementScore = $this->strategy->calculateEngagementScore($content);

    expect($engagementScore)->toBe(0.0);
});

it('calculates final score correctly', function () {
    $content = new Content([
        'type' => ContentType::VIDEO,
        'views' => 10000,
        'likes' => 500,
    ]);

    $freshnessScore = 5.0;
    $finalScore = $this->strategy->calculateFinalScore($content, $freshnessScore);

    // Final Score = (Base Score * Type Coefficient) + Freshness Score + Engagement Score
    // Base Score = 10000/1000 + 500/100 = 15
    // Type Coefficient = 1.5
    // Engagement Score = (500/10000) * 10 = 0.5
    // Final = (15 * 1.5) + 5 + 0.5 = 22.5 + 5 + 0.5 = 28
    expect($finalScore)->toBe(28.0);
});

it('rounds final score to 2 decimal places', function () {
    $content = new Content([
        'type' => ContentType::VIDEO,
        'views' => 3333,
        'likes' => 333,
    ]);

    $freshnessScore = 3.0;
    $finalScore = $this->strategy->calculateFinalScore($content, $freshnessScore);

    expect($finalScore)->toBeFloat();
    expect(strlen(substr(strrchr((string)$finalScore, "."), 1)))->toBeLessThanOrEqual(2);
});

