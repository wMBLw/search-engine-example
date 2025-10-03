<?php
namespace App\Providers;

use App\Repositories\Auth\AuthRepository;
use App\Repositories\Auth\AuthRepositoryInterface;
use App\Services\Content\ContentRepository;
use App\Services\Content\Contracts\ContentRepositoryInterface;
use App\Services\Search\Contracts\SearchRepositoryInterface;
use App\Services\Search\SearchRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(AuthRepositoryInterface::class, AuthRepository::class);
        $this->app->bind(ContentRepositoryInterface::class, ContentRepository::class);
        $this->app->bind(SearchRepositoryInterface::class, SearchRepository::class);
    }

    public function boot()
    {

    }
}
