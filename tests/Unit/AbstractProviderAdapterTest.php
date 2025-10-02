<?php

use App\Models\Provider;
use App\Services\Providers\Adapters\JsonProviderAdapter;
use App\Services\Providers\AbstractProviderAdapter;

function callDurationToSeconds(string $duration): int
{

    $mockProvider = new Provider(['name' => 'test', 'endpoint' => 'https://example.com']);
    $adapter = new JsonProviderAdapter($mockProvider);

    $reflection = new ReflectionMethod(AbstractProviderAdapter::class, 'durationToSeconds');

    return $reflection->invoke($adapter, $duration);
}

it('it converts MM:SS format to seconds correctly', function (string $duration, int $expectedSeconds) {
    expect(callDurationToSeconds($duration))->toBe($expectedSeconds);
})->with([
    'standard time' => ['15:30', 930],      // 15 * 60 + 30
    'zero minutes'  => ['00:45', 45],
    'zero seconds'  => ['10:00', 600],
    'long minutes'  => ['90:10', 5410],     // 90 * 60 + 10
    'zero time'     => ['00:00', 0],
]);

it('it converts HH:MM:SS format to seconds correctly', function (string $duration, int $expectedSeconds) {
    expect(callDurationToSeconds($duration))->toBe($expectedSeconds);
})->with([
    'standard time' => ['01:15:30', 4530], // 1 * 3600 + 15 * 60 + 30
    'zero hours'    => ['00:22:45', 1365], // 22 * 60 + 45
    'long hours'    => ['10:00:00', 36000],
    'zero time'     => ['00:00:00', 0],
]);

it('it handles plain seconds string correctly', function (string $duration, int $expectedSeconds) {
    expect(callDurationToSeconds($duration))->toBe($expectedSeconds);
})->with([
    ['930', 930],
    ['60', 60],
    ['0', 0],
]);
