<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'login'])->name('login');
Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
