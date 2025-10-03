<?php
namespace App\Providers;

use App\Services\Auth\AuthService;
use App\Services\Auth\AuthServiceInterface;
use App\Services\Content\ContentSyncService;
use App\Services\Content\Contracts\ContentSyncServiceInterface;
use App\Services\Content\Contracts\DistributedLockInterface;
use App\Services\Content\RedisDistributedLock;
use App\Services\Search\Contracts\ScoreCalculatorInterface;
use App\Services\Search\Contracts\SearchServiceInterface;
use App\Services\Search\ScoreCalculator;
use App\Services\Search\SearchService;
use Illuminate\Support\ServiceProvider;

class ServiceBindingsProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
        $this->app->bind(DistributedLockInterface::class, RedisDistributedLock::class);
        $this->app->bind(ContentSyncServiceInterface::class, ContentSyncService::class);
        $this->app->bind(SearchServiceInterface::class, SearchService::class);
        $this->app->bind(ScoreCalculatorInterface::class, ScoreCalculator::class);
    }

    public function boot()
    {

    }
}
