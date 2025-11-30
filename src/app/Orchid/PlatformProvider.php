<?php

declare(strict_types=1);

namespace App\Orchid;

use Orchid\Platform\Dashboard;
use Orchid\Platform\ItemPermission;
use Orchid\Platform\OrchidServiceProvider;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\Menu;
use Orchid\Support\Color;

class PlatformProvider extends OrchidServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @param Dashboard $dashboard
     *
     * @return void
     */
    public function boot(Dashboard $dashboard): void
    {
        parent::boot($dashboard);

        // ...
    }

    /**
     * Register the application menu.
     *
     * @return Menu[]
     */
    public function menu(): array
    {
        return [
            Menu::make('Вернуться')->
            route('home')
            ->icon('bs.arrow-left')
            ->active('home'),

            Menu::make('Настройка нейросетей')
                ->icon('bs.gear')
                ->list([
                    Menu::make('Нейросети')
                        ->icon('bs.robot')
                        ->route('platform.neurals'),

                    Menu::make('Фильтры')
                        ->icon('bs.filter-left')
                        ->route('platform.neural-filters.list'),
                    Menu::make('Базовые промпты')
                        ->icon('bs.file-earmark-text-fill')
                        ->route('platform.base-prompts'),
                ]),
            Menu::make(__('Пользователи'))
                ->icon('bs.people')
                ->route('platform.users.list'),

            Menu::make(__('История сообщений'))
                ->icon('bs.chat')
                ->route('platform.messages'),
            Menu::make('Аналитика')
                ->icon('bs.graph-up')
                ->list([
                    Menu::make('Сообщения')
                        ->icon('bs.chat-left')
                        ->route('platform.analytics.messages'),
                ]),
            Menu::make(__('Настройка почты'))
                ->icon('bs.envelope')
                ->route('platform.emailSettings'),
        ];
    }

    /**
     * Register permissions for the application.
     *
     * @return ItemPermission[]
     */
    public function permissions(): array
    {
        return [
            ItemPermission::group(__('System'))
                ->addPermission('platform.systems.roles', __('Roles'))
                ->addPermission('platform.systems.users', __('Users')),
        ];
    }
}
