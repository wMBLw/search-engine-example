<?php

use Illuminate\Support\Facades\Schedule;


Schedule::command('providers:sync')
    ->everyFiveMinutes()
    ->withoutOverlapping(10)
    ->runInBackground()
    ->onSuccess(function () {
        \Illuminate\Support\Facades\Log::info('Provider sync completed successfully via scheduler');
    })
    ->onFailure(function () {
        \Illuminate\Support\Facades\Log::error('Provider sync failed via scheduler');
    });
