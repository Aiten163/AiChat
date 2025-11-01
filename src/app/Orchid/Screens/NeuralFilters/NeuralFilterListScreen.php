<?php

namespace App\Orchid\Screens\NeuralFilters;

use App\Models\NeuralFilter;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class NeuralFilterListScreen extends Screen
{
    public function query(): iterable
    {
        return [
            'filters' => NeuralFilter::with('neural')
                ->latest('id')
                ->paginate(),
        ];
    }

    public function name(): ?string
    {
        return 'Нейронные фильтры';
    }

    public function commandBar(): array
    {
        return [
            Link::make('Добавить фильтр')
                ->icon('plus')
                ->route('platform.neural-filters.create'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('filters', [
                TD::make('id', 'ID')
                    ->sort()
                    ->width('80px'),

                TD::make('name', 'Название')
                    ->sort()
                    ->filter(TD::FILTER_TEXT),

                TD::make('neural.name', 'Нейросеть')
                    ->sort()
                    ->render(fn(NeuralFilter $filter) => $filter->neural?->show_name ?? '-'),

                TD::make('activePrompt', 'Активный промпт')
                    ->alignCenter()
                    ->width('150px')
                    ->render(fn(NeuralFilter $filter) => $filter->activePrompt
                        ? '<i class="text-success">●</i> Активен'
                        : '<i class="text-danger">●</i> Неактивен'),

                TD::make('activeSimple', 'Активный простой фильтр')
                    ->alignCenter()
                    ->width('180px')
                    ->render(fn(NeuralFilter $filter) => $filter->activeSimple
                        ? '<i class="text-success">●</i> Активен'
                        : '<i class="text-danger">●</i> Неактивен'),

                TD::make('actions', 'Действия')
                    ->alignRight()
                    ->width('100px')
                    ->render(fn(NeuralFilter $filter) =>
                    Link::make('Редактировать')
                        ->icon('pencil')
                        ->route('platform.neural-filters.edit', $filter)
                    ),
            ]),
        ];
    }
}
