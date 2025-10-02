<?php

namespace Tests\Feature;

use App\Services\Content\Contracts\ContentSyncServiceInterface;
use Mockery;

it('test_sync_all_providers_command',function (){
    $mockSyncService = Mockery::mock(ContentSyncServiceInterface::class);
    $mockSyncService->shouldReceive('syncAllProviders')
        ->once()
        ->andReturn([
            [
                'success' => true,
                'provider_id' => 1,
                'provider_name' => 'Test Provider',
                'synced_count' => 10,
                'created_count' => 5,
                'updated_count' => 5
            ]
        ]);

    $this->app->instance(ContentSyncServiceInterface::class, $mockSyncService);

    $this->artisan('providers:sync')
            ->expectsOutput('Starting provider content synchronization...')
            ->expectsOutput('Synchronization Results:')
            ->assertExitCode(0);
});
