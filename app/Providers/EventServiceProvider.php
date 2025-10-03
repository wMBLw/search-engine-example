<?php

namespace App\Providers;

use App\Events\UserLoggedIn;
use App\Listeners\LogUserLogin;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(UserLoggedIn::class, LogUserLogin::class);
    }
}
