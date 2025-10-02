<?php

namespace App\Console\Commands;

use App\Services\Content\Contracts\ContentSyncServiceInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncProvidersCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'providers:sync';

    /**
     * The console command description.
     */
    protected $description = 'Sync content from all active providers or a specific provider';

    public function __construct(private readonly ContentSyncServiceInterface $contentSyncService) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting provider content synchronization...');

        $startTime = microtime(true);

        try {
            $results = $this->contentSyncService->syncAllProviders();

            $this->displayResults($results);

            $executionTime = round(microtime(true) - $startTime, 2);
            $this->info("Synchronization completed in {$executionTime} seconds");

            return Command::SUCCESS;

        } catch (\Throwable $e) {
            $this->error('Synchronization failed: ' . $e->getMessage());

            Log::error('Provider sync command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return Command::FAILURE;
        }
    }
    private function displayResults(array $results): void
    {
        if (empty($results)) {
            $this->warn('No providers to sync');
            return;
        }

        $this->newLine();
        $this->info('Synchronization Results:');
        $this->newLine();

        $totalSynced = 0;
        $totalCreated = 0;
        $totalUpdated = 0;
        $successCount = 0;
        $failureCount = 0;

        foreach ($results as $result) {
            $status = $result['success'] ? 'ok' : 'fail';
            $providerName = $result['provider_name'];

            if ($result['success']) {
                $successCount++;
                $totalSynced += $result['synced_count'];
                $totalCreated += $result['created_count'];
                $totalUpdated += $result['updated_count'];

                $this->line("{$status} {$providerName}: {$result['synced_count']} items (Created: {$result['created_count']}, Updated: {$result['updated_count']})");
            } else {
                $failureCount++;
                $error = $result['error'] ?? 'Unknown error';
                $this->line("{$status} {$providerName}: Failed - {$error}");
            }
        }

        $this->newLine();
        $this->info("Summary:");
        $this->line("   • Successful providers: {$successCount}");
        $this->line("   • Failed providers: {$failureCount}");
        $this->line("   • Total items synced: {$totalSynced}");
        $this->line("   • Total items created: {$totalCreated}");
        $this->line("   • Total items updated: {$totalUpdated}");

        if ($failureCount > 0) {
            $this->newLine();
            $this->warn("{$failureCount} provider(s) failed to sync.");
        }
    }
}
