<?php

namespace App\Orchid\Layouts\Card;

use App\Models\Card;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;
class CardsTable extends Table
{
    /**
     * Data source.
     *
     * The name of the key to fetch it from the query.
     * The results of which will be elements of the table.
     *
     * @var string
     */
    protected $target = 'cards';

    /**
     * Get the table cells to be displayed.
     *
     * @return TD[]
     */
    protected function columns(): iterable
    {
        return [
            TD::make('id', 'ID')->sort()->filter(TD::FILTER_NUMERIC),
            TD::make('uid', 'Шифр')->sort()->filter(TD::FILTER_TEXT),
            TD::make('level', 'Уровень')->sort()->filter(),
            TD::make('mrsu_id', 'ID mrsu')->sort()->filter(),
            TD::make('action', '')->cantHide()->render(function (Card $Card)
            {
                return ModalToggle::make("")
                    ->modal('editcard')
                    ->icon('pen')
                    ->method('update')
                    ->modalTitle('Редактирование карточки '.$Card->id)
                    ->asyncParameters([
                            'card' =>$Card->id
                        ]
                    );
            }),
            TD::make('action','')->cantHide()
                ->render(function (Card $Card)
                {
                    return Button::make("")
                        ->icon('trash')
                        ->method('delete',[
                            'card'=>$Card->id
                        ]);
                })
        ];
    }
}
