<?php

namespace App\Orchid\Layouts\Neural;

use App\Models\Neural;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;
class NeuralTable extends Table
{
    /**
     * Data source.
     *
     * The name of the key to fetch it from the query.
     * The results of which will be elements of the table.
     *
     * @var string
     */
    protected $target = 'neural';

    /**
     * Get the table cells to be displayed.
     *
     * @return TD[]
     */
    protected function columns(): iterable
    {
        return [
            TD::make('id', 'ID')->sort()->filter(TD::FILTER_NUMERIC),
            TD::make('name', 'Название')->sort()->filter(),
            TD::make('link', 'API')->sort()->filter(),
            TD::make('action', '')->cantHide()->render(function (Neural $neural)
            {
                return ModalToggle::make("")
                    ->modal('editneural')
                    ->icon('pen')
                    ->method('update')
                    ->modalTitle('Редактирование'.$neural->id)
                    ->asyncParameters([
                            'neural' =>$neural->id
                        ]
                    );
            }),
            TD::make('action','')->cantHide()
                ->render(function (Neural $neural)
                {
                    return Button::make("")
                        ->icon('trash')
                        ->method('delete',[
                            'neural'=>$neural->id
                        ]);
                })
        ];
    }
}
