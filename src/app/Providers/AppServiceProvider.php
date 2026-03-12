<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Chat\ChatProcessorService;
use App\Services\Chat\ChatTitleService;
use App\Services\Chat\ChatRepository;
use App\Services\Chat\MessageRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ChatTitleService::class);
        $this->app->singleton(ChatRepository::class);
        $this->app->singleton(MessageRepository::class);

        $this->app->singleton(ChatProcessorService::class, function ($app) {
            return new ChatProcessorService(
                $app->make(ChatTitleService::class),
                $app->make(ChatRepository::class),
                $app->make(MessageRepository::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
