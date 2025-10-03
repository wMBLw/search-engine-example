<?php

use App\Enums\ContentType;
use App\Services\Search\ScoringStrategyFactory;
use App\Services\Search\Strategies\ArticleScoringStrategy;
use App\Services\Search\Strategies\VideoScoringStrategy;

it('creates video scoring strategy for video content', function () {
    $strategy = ScoringStrategyFactory::create(ContentType::VIDEO);

    expect($strategy)->toBeInstanceOf(VideoScoringStrategy::class);
});

it('creates article scoring strategy for article content', function () {
    $strategy = ScoringStrategyFactory::create(ContentType::ARTICLE);

    expect($strategy)->toBeInstanceOf(ArticleScoringStrategy::class);
});

it('creates new strategy instances each time', function () {
    $strategy1 = ScoringStrategyFactory::create(ContentType::VIDEO);
    $strategy2 = ScoringStrategyFactory::create(ContentType::VIDEO);

    // Factory creates new instances, not singletons
    expect($strategy1)->toBeInstanceOf(VideoScoringStrategy::class);
    expect($strategy2)->toBeInstanceOf(VideoScoringStrategy::class);
});

it('returns different strategy instances for different types', function () {
    $videoStrategy = ScoringStrategyFactory::create(ContentType::VIDEO);
    $articleStrategy = ScoringStrategyFactory::create(ContentType::ARTICLE);

    expect($videoStrategy)->not->toBe($articleStrategy);
    expect($videoStrategy)->toBeInstanceOf(VideoScoringStrategy::class);
    expect($articleStrategy)->toBeInstanceOf(ArticleScoringStrategy::class);
});

