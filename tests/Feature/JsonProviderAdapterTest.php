<?php

use App\Enums\ContentType;
use App\Services\Providers\Adapters\JsonProviderAdapter;
use App\Services\Providers\NormalizedContentDTO;
use Carbon\Carbon;
use Mockery\MockInterface;

it('json provider adapter fetches and maps data correctly', function () {

    $jsonData = <<<JSON
{
  "contents": [
    {
      "id": "v1",
      "title": "Go Programming Tutorial",
      "type": "video",
      "metrics": {
        "views": 15000,
        "likes": 1200,
        "duration": "15:30"
      },
      "published_at": "2024-03-15T10:00:00Z",
      "tags": ["programming", "tutorial"]
    },
    {
      "id": "v2",
      "title": "Advanced Go Concurrency Patterns",
      "type": "video",
      "metrics": {
        "views": 25000,
        "likes": 2100,
        "duration": "22:45"
      },
      "published_at": "2024-03-14T15:30:00Z",
      "tags": ["programming", "advanced", "concurrency"]
    }
  ]
}
JSON;

    $adapter = Mockery::mock(JsonProviderAdapter::class, function (MockInterface $mock) use ($jsonData) {
        $mock->makePartial()->shouldAllowMockingProtectedMethods();
        $mock->shouldReceive('getApiData')->andReturn($jsonData);
    });

    $result = $adapter->fetchAll();

    expect($result)
        ->toBeArray()
        ->toHaveCount(2)
        ->each->toBeInstanceOf(NormalizedContentDTO::class);

    $firstItem = $result[0];
    expect($firstItem->getExternalId())->toBe('v1')
        ->and($firstItem->getTitle())->toBe('Go Programming Tutorial')
        ->and($firstItem->getType())->toBe(ContentType::VIDEO)
        ->and($firstItem->getViews())->toBe(15000)
        ->and($firstItem->getLikes())->toBe(1200)
        ->and($firstItem->getDuringSeconds())->toBe(930) // 15*60 + 30
        ->and($firstItem->getPublishedAt())->toEqual(Carbon::parse('2024-03-15T10:00:00Z'))
        ->and($firstItem->getTags())->toBe(['programming', 'tutorial']);

    $secondItem = $result[1];
    expect($secondItem->getExternalId())->toBe('v2')
        ->and($secondItem->getTitle())->toBe('Advanced Go Concurrency Patterns')
        ->and($secondItem->getType())->toBe(ContentType::VIDEO)
        ->and($secondItem->getViews())->toBe(25000)
        ->and($secondItem->getLikes())->toBe(2100)
        ->and($secondItem->getDuringSeconds())->toBe(1365) // 22*60 + 45
        ->and($secondItem->getPublishedAt())->toEqual(Carbon::parse('2024-03-14T15:30:00Z'))
        ->and($secondItem->getTags())->toBe(['programming', 'advanced', 'concurrency']);
});

it('json provider adapter handles invalid json', function () {

    $adapter = Mockery::mock(JsonProviderAdapter::class, function (MockInterface $mock) {
        $mock->makePartial()->shouldAllowMockingProtectedMethods();
        $mock->shouldReceive('getApiData')->andReturn('invalid json');
    });

    $result = $adapter->fetchAll();

    expect($result)->toBeArray()->toBeEmpty();
});

it('json provider adapter handles api exception', function () {

    $adapter = Mockery::mock(JsonProviderAdapter::class, function (MockInterface $mock) {
        $mock->makePartial()->shouldAllowMockingProtectedMethods();
        $mock->shouldReceive('getApiData')->andThrow(new Exception('API error'));
    });

    $result = $adapter->fetchAll();

    expect($result)->toBeArray()->toBeEmpty();
});
