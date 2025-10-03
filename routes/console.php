<?php

use Illuminate\Support\Facades\Schedule;
use \Illuminate\Support\Facades\Log;

Schedule::command('providers:sync')
    ->everyFiveMinutes()
    ->withoutOverlapping(10)
    ->runInBackground()
    ->onSuccess(function () {
        Log::info('Provider sync completed successfully via scheduler');
    })
    ->onFailure(function () {
        Log::error('Provider sync failed via scheduler');
    });
