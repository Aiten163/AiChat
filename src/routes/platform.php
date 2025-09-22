<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Orchid\Screens\PlatformScreen;
use App\Orchid\Screens\UserListScreen;

// Главная группа с middleware
Route::middleware(['web', 'auth', 'platform'])->group(function () {

    // Main
    Route::screen('/main', PlatformScreen::class)
        ->name('platform.main');

    Route::screen('/neurals', \App\Orchid\Screens\NeuralScreen::class)
        ->name('platform.neurals');

    Route::screen('/users', UserListScreen::class)
        ->name('platform.users.list');

});
