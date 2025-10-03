<?php

namespace App\Listeners;

use App\Events\UserLoggedIn;
use App\Jobs\LogUserLoginJob;

class LogUserLogin
{
    public function handle(UserLoggedIn $event): void
    {
        LogUserLoginJob::dispatch(
            userId: $event->user->id,
            ipAddress: $event->ipAddress,
            userAgent: $event->userAgent,
        )->onQueue('login-logs');
    }
}
