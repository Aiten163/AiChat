<?php


namespace App\Orchid\Screens;

use App\Models\Neural;
use App\Orchid\Layouts\Neural\NeuralTable;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Orchid\Screen\Fields\Select;
use \Orchid\Support\Facades\Toast;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast as FacadesToast;

class NeuralScreen extends Screen
{

    public function query(): iterable
    {
        return [
            'neurals' => neural::filters()->defaultSort('id')->paginate()
        ];
    }


    public function name(): ?string
    {
        return 'Нейросети';
    }

    public function commandBar(): array
    {
        return [
            ModalToggle::make("Добавить нейросеть")->modal('createneural')->method('create'),
        ];
    }

    public function layout(): array
    {
        return
            [
                NeuralTable::class,
                Layout::modal('createneural', Layout::rows([
                    Input::make('name')->title('Название'),
                    Input::make('link')->title('API'),
                    Input::make('name_return')->title('Название ключа ответа'),
                ]))->title("Добавить нейросеть")->applyButton('Добавить'),

                Layout::modal("editneural", Layout::rows
                (
                    [
                        Input::make('neural.id')->type('hidden'),
                        Input::make('neural.name')->title('Название'),
                        Input::make('neural.link')->title('API'),
                        Input::make('neural.name_return')->title('Название ключа ответа'),
                    ]
                ))->async('asyncGetNeural')
            ];
    }

    public function asyncGetNeural(neural $neural): array
    {
        return [
            'neural' => $neural
        ];
    }

    public function update(Request $request)
    {
        neural::find($request->input('neural.id'))->update($request->neural);
        Toast::info('Успешно обновлено');
    }

    public function delete(Request $request)
    {
        neural::find($request->neural)->delete();
        Toast::info('Успешно удалено');
    }


    public function create(Request $request): void
    {
        neural::create($request->merge([
        ])->except('_token'));
        FacadesToast::info('Нейросеть успешно добавлена');
    }
}
