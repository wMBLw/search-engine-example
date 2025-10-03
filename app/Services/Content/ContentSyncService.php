<?php

namespace App\Services\Content;

use App\Models\Provider;
use App\Services\Content\Contracts\ContentRepositoryInterface;
use App\Services\Content\Contracts\ContentSyncServiceInterface;
use App\Services\Content\Contracts\DistributedLockInterface;
use App\Services\Providers\ProviderAdapterFactory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ContentSyncService implements ContentSyncServiceInterface
{
    private const MAX_CONSECUTIVE_FAILURES = 3;
    private const FAILURE_TIMEOUT_MINUTES = 30;

    public function __construct(
        private readonly ContentRepositoryInterface $contentRepository,
        private readonly DistributedLockInterface $distributedLock
    ) {}

    public function syncProvider(Provider $provider): array
    {
        $lockKey = "provider_sync_{$provider->id}";

        try {

            return $this->distributedLock->executeWithLock($lockKey, function () use ($provider){
                return $this->performProviderSync($provider);
            },300);

        } catch (Throwable $e) {

            Log::error('Provider sync failed', [
                'provider_id' => $provider->id,
                'provider_name' => $provider->name,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->handleProviderFailure($provider);

            return [
                'success' => false,
                'provider_id' => $provider->id,
                'provider_name' => $provider->name,
                'error' => $e->getMessage(),
                'synced_count' => 0,
                'created_count' => 0,
                'updated_count' => 0
            ];
        }
    }

    public function syncAllProviders(): array
    {
        $providers = Provider::byNonDisabledUntil()->get();

        $results = [];

        foreach ($providers as $provider) {
            $results[] = $this->syncProvider($provider);
        }

        return $results;
    }

    private function performProviderSync(Provider $provider): array
    {
        $adapter = ProviderAdapterFactory::make($provider);

        $contents = $adapter->fetchAll();

        if (empty($contents)) {
            Log::warning('No content fetched from provider', [
                'provider_id' => $provider->id,
                'provider_name' => $provider->name
            ]);

            $this->updateProviderSyncStatus($provider, true);

            return [
                'success' => true,
                'provider_id' => $provider->id,
                'provider_name' => $provider->name,
                'synced_count' => 0,
                'created_count' => 0,
                'updated_count' => 0
            ];
        }

        $createdCount = 0;
        $updatedCount = 0;

        DB::transaction(function () use ($provider, $contents, &$createdCount, &$updatedCount) {

            foreach ($contents as $contentDto) {

                $existingContent = $this->contentRepository->findByProviderAndExternalId(
                    $provider,
                    $contentDto->getExternalId()
                );

                $this->contentRepository->createOrUpdateFromDto($provider, $contentDto);

                if ($existingContent) {
                    $updatedCount++;
                } else {
                    $createdCount++;
                }
            }
        },5); // Provides automatic retry in case of DB deadlock

        $this->updateProviderSyncStatus($provider, true);

        return [
            'success' => true,
            'provider_id' => $provider->id,
            'provider_name' => $provider->name,
            'synced_count' => count($contents),
            'created_count' => $createdCount,
            'updated_count' => $updatedCount
        ];
    }

    private function updateProviderSyncStatus(Provider $provider, bool $success): void
    {
        if ($success) {
            $provider->update([
                'last_synced_at' => Carbon::now(),
                'consecutive_failures' => 0,
                'disabled_until' => null,
            ]);
        } else {
            $this->handleProviderFailure($provider);
        }
    }

    private function handleProviderFailure(Provider $provider): void
    {
        $consecutiveFailures = $provider->consecutive_failures + 1;

        $updateData = [
            'consecutive_failures' => $consecutiveFailures,
        ];

        // Circuit breaker
        if ($consecutiveFailures >= self::MAX_CONSECUTIVE_FAILURES) {
            $updateData['disabled_until'] = Carbon::now()->addMinutes(self::FAILURE_TIMEOUT_MINUTES);

            Log::warning('Provider disabled due to consecutive failures', [
                'provider_id' => $provider->id,
                'provider_name' => $provider->name,
                'consecutive_failures' => $consecutiveFailures,
                'disabled_until' => $updateData['disabled_until']
            ]);
        }

        $provider->update($updateData);
    }
}
