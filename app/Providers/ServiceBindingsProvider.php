<?php
namespace App\Providers;

use App\Services\Auth\AuthService;
use App\Services\Auth\AuthServiceInterface;
use App\Services\Content\ContentSyncService;
use App\Services\Content\Contracts\ContentSyncServiceInterface;
use App\Services\Content\Contracts\DistributedLockInterface;
use App\Services\Content\RedisDistributedLock;
use Illuminate\Support\ServiceProvider;

class ServiceBindingsProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
        $this->app->bind(DistributedLockInterface::class, RedisDistributedLock::class);
        $this->app->bind(ContentSyncServiceInterface::class, ContentSyncService::class);
    }

    public function boot()
    {

    }
}
