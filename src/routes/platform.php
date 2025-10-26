<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Orchid\Screens\PlatformScreen;
use App\Orchid\Screens\UserListScreen;
use \App\Orchid\Screens\BasePromptScreen;

// Главная группа с middleware
Route::middleware(['web', 'auth', 'platform'])->group(function () {

    Route::screen('/neurals', \App\Orchid\Screens\NeuralScreen::class)
        ->name('platform.neurals');

    Route::screen('/users', UserListScreen::class)
        ->name('platform.users.list');
    Route::screen('/messages', \App\Orchid\Screens\MessagesScreen::class)
        ->name('platform.messages');
    Route::screen('/analytics/messages', \App\Orchid\Screens\Analytics\MessagesChartScreen::class)
        ->name('platform.analytics.messages')
        ->breadcrumbs(function (\Tabuna\Breadcrumbs\Trail $trail) {
            return $trail
                ->parent('platform.index')
                ->push('Статистика сообщений', route('platform.analytics.messages'));
        });

    Route::screen('base-prompts', BasePromptScreen::class)
        ->name('platform.base-prompts');

    Route::post('base-prompts/update', [BasePromptScreen::class, 'update'])
        ->name('platform.base-prompts.update');
});
