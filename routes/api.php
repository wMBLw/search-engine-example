<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/refresh-token', [AuthController::class, 'refreshToken']);

Route::group(['middleware' => 'auth:sanctum'], function () {

    Route::group(['prefix' => 'user'], function () {
        Route::get('/', [AuthController::class, 'loggedInUser']);
        Route::get('/logout', [AuthController::class, 'logout']);
    });

});
