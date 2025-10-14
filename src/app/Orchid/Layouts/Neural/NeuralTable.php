<?php

namespace App\Orchid\Layouts\Neural;

use App\Models\Neural;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class NeuralTable extends Table
{
    protected $target = 'neurals';

    protected function columns(): iterable
    {
        return [
            TD::make('id', 'ID')
                ->sort()
                ->filter(TD::FILTER_NUMERIC)
                ->width('50px')
                ->render(function (Neural $neural) {
                    return $neural->id;
                }),

            TD::make('name', 'Системное название')
                ->sort()
                ->filter()
                ->width('150px')
                ->render(function (Neural $neural) {
                    return $neural->name;
                }),

            TD::make('show_name', 'Отображаемое название')
                ->sort()
                ->filter()
                ->width('150px')
                ->render(function (Neural $neural) {
                    return $neural->show_name;
                }),

            TD::make('temperature', 'Температура')
                ->sort()
                ->filter(TD::FILTER_NUMERIC)
                ->width('50px')
                ->render(function (Neural $neural) {
                    return $neural->temperature . '%';
                }),

            TD::make('countLastMessage', 'Сообщений в контексте')
                ->sort()
                ->filter(TD::FILTER_NUMERIC)
                ->width('100px')
                ->render(function (Neural $neural) {
                    return $neural->countLastMessage;
                }),

            TD::make('description', 'Описание')
                ->width('200px')
                ->render(function (Neural $neural) {
                    return \Illuminate\Support\Str::limit($neural->description, 50);
                }),

            TD::make('action', 'Действия')
                ->alignRight()
                ->width('50px')
                ->render(function (Neural $neural) {
                    return ModalToggle::make('')
                        ->modal('editNeural')
                        ->icon('pencil')
                        ->method('update')
                        ->modalTitle('Редактирование нейросети: ' . $neural->show_name)
                        ->asyncParameters([
                            'neural' => $neural->id
                        ]);
                }),

            TD::make('delete', '')
                ->alignRight()
                ->width('50px')
                ->render(function (Neural $neural) {
                    return Button::make('')
                        ->icon('trash')
                        ->method('delete', [
                            'neural' => $neural->id
                        ])
                        ->confirm('Вы уверены, что хотите удалить эту нейросеть?');
                }),
        ];
    }
}
