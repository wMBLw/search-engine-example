<?php

use App\Enums\ContentType;
use App\Services\Providers\Adapters\XmlProviderAdapter;
use App\Services\Providers\NormalizedContentDTO;
use Carbon\Carbon;
use Mockery\MockInterface;

it('xml provider adapter fetches and maps data correctly', function () {

    $xmlData = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<feed>
  <items>
    <item>
      <id>v1</id>
      <headline>Introduction to Docker</headline>
      <type>video</type>
      <stats>
        <views>22000</views>
        <likes>1800</likes>
        <duration>25:15</duration>
      </stats>
      <publication_date>2024-03-15</publication_date>
      <categories>
        <category>devops</category>
        <category>containers</category>
      </categories>
    </item>
    <item>
      <id>v2</id>
      <headline>Kubernetes for Beginners</headline>
      <type>video</type>
      <stats>
        <views>19500</views>
        <likes>1600</likes>
        <duration>28:45</duration>
      </stats>
      <publication_date>2024-03-14</publication_date>
      <categories>
        <category>devops</category>
        <category>kubernetes</category>
      </categories>
    </item>
  </items>
</feed>
XML;

    $adapter = Mockery::mock(XmlProviderAdapter::class, function (MockInterface $mock) use ($xmlData) {
        $mock->makePartial()->shouldAllowMockingProtectedMethods();
        $mock->shouldReceive('getApiData')->andReturn($xmlData);
    });

    $result = $adapter->fetchAll();

    expect($result)
        ->toBeArray()
        ->toHaveCount(2)
        ->each->toBeInstanceOf(NormalizedContentDTO::class);

    $firstItem = $result[0];
    expect($firstItem->getExternalId())->toBe('v1')
        ->and($firstItem->getTitle())->toBe('Introduction to Docker')
        ->and($firstItem->getType())->toBe(ContentType::VIDEO)
        ->and($firstItem->getViews())->toBe(22000)
        ->and($firstItem->getLikes())->toBe(1800)
        ->and($firstItem->getDuringSeconds())->toBe(1515) // 25*60 + 15
        ->and($firstItem->getPublishedAt())->toEqual(Carbon::parse('2024-03-15'))
        ->and((array)($firstItem->getTags()['category'] ?? []))->toBe(['devops', 'containers']);

    $secondItem = $result[1];
    expect($secondItem->getExternalId())->toBe('v2')
        ->and($secondItem->getTitle())->toBe('Kubernetes for Beginners')
        ->and($secondItem->getType())->toBe(ContentType::VIDEO)
        ->and($secondItem->getViews())->toBe(19500)
        ->and($secondItem->getLikes())->toBe(1600)
        ->and($secondItem->getDuringSeconds())->toBe(1725) // 28*60 + 45
        ->and($secondItem->getPublishedAt())->toEqual(Carbon::parse('2024-03-14'))
        ->and((array)($secondItem->getTags()['category'] ?? []))->toBe(['devops', 'kubernetes']);
});

it('xml provider adapter handles invalid xml', function () {

    $adapter = Mockery::mock(XmlProviderAdapter::class, function (MockInterface $mock) {
        $mock->makePartial()->shouldAllowMockingProtectedMethods();
        $mock->shouldReceive('getApiData')->andReturn('invalid xml');
    });

    $result = $adapter->fetchAll();

    expect($result)->toBeArray()->toBeEmpty();
});

it('xml provider adapter handles api exception', function () {

    $adapter = Mockery::mock(XmlProviderAdapter::class, function (MockInterface $mock) {
        $mock->makePartial()->shouldAllowMockingProtectedMethods();
        $mock->shouldReceive('getApiData')->andThrow(new Exception('API error'));
    });

    $result = $adapter->fetchAll();

    expect($result)->toBeArray()->toBeEmpty();
});
