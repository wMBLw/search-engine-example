<?php
namespace App\Providers;

use App\Repositories\Auth\AuthRepository;
use App\Repositories\Auth\AuthRepositoryInterface;
use App\Services\Content\ContentRepository;
use App\Services\Content\Contracts\ContentRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(AuthRepositoryInterface::class, AuthRepository::class);
        $this->app->bind(ContentRepositoryInterface::class, ContentRepository::class);

    }

    public function boot()
    {

    }
}
