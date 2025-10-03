<?php

use App\Models\Content;
use App\Services\Search\ScoreCalculator;
use Carbon\Carbon;

it('calculates freshness score for content published within 7 days', function () {
    $content = new Content([
        'published_at' => Carbon::now()->subDays(3),
    ]);

    $freshnessScore = ScoreCalculator::calculateFreshnessScore($content);

    expect($freshnessScore)->toBe(5.0);
});

it('calculates freshness score for content published within 30 days', function () {
    $content = new Content([
        'published_at' => Carbon::now()->subDays(15),
    ]);

    $freshnessScore = ScoreCalculator::calculateFreshnessScore($content);

    expect($freshnessScore)->toBe(3.0);
});

it('calculates freshness score for content published within 90 days', function () {
    $content = new Content([
        'published_at' => Carbon::now()->subDays(45),
    ]);

    $freshnessScore = ScoreCalculator::calculateFreshnessScore($content);

    expect($freshnessScore)->toBe(1.0);
});

it('calculates freshness score for old content', function () {
    $content = new Content([
        'published_at' => Carbon::now()->subDays(120),
    ]);

    $freshnessScore = ScoreCalculator::calculateFreshnessScore($content);

    expect($freshnessScore)->toBe(0.0);
});

it('returns zero for content without published date', function () {
    $content = new Content([
        'published_at' => null,
    ]);

    $freshnessScore = ScoreCalculator::calculateFreshnessScore($content);

    expect($freshnessScore)->toBe(0.0);
});

it('handles edge case of exactly 7 days', function () {
    $content = new Content([
        'published_at' => Carbon::now()->subDays(7),
    ]);

    $freshnessScore = ScoreCalculator::calculateFreshnessScore($content);

    // 7 days exactly is > 7 days threshold, so it should be 3.0
    expect($freshnessScore)->toBe(3.0);
});

it('handles edge case of exactly 30 days', function () {
    $content = new Content([
        'published_at' => Carbon::now()->subDays(30),
    ]);

    $freshnessScore = ScoreCalculator::calculateFreshnessScore($content);

    // 30 days exactly is > 30 days threshold, so it should be 1.0
    expect($freshnessScore)->toBe(1.0);
});

it('handles edge case of exactly 90 days', function () {
    $content = new Content([
        'published_at' => Carbon::now()->subDays(90),
    ]);

    $freshnessScore = ScoreCalculator::calculateFreshnessScore($content);

    // 90 days exactly is > 90 days threshold, so it should be 0.0
    expect($freshnessScore)->toBe(0.0);
});

