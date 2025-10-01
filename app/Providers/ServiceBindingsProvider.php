<?php
namespace App\Providers;

use App\Services\AuthServiceInterface;
use App\Services\AuthService;
use Illuminate\Support\ServiceProvider;

class ServiceBindingsProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
    }

    public function boot()
    {

    }
}
