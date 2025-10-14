<?php

namespace App\Orchid\Screens;

use App\Models\Neural;
use App\Orchid\Layouts\Neural\NeuralTable;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class NeuralScreen extends Screen
{
    public function query(): iterable
    {
        return [
            'neurals' => Neural::filters()->defaultSort('id')->paginate()
        ];
    }

    public function name(): ?string
    {
        return 'Нейросети';
    }

    public function description(): ?string
    {
        return 'Управление нейросетями и их настройками';
    }

    public function commandBar(): array
    {
        return [
            ModalToggle::make("Добавить нейросеть")
                ->modal('createNeural')
                ->method('create')
                ->icon('plus'),
        ];
    }

    public function layout(): array
    {
        return [
            NeuralTable::class,

            Layout::modal('createNeural', Layout::rows([
                Input::make('name')
                    ->title('Системное название')
                    ->placeholder('Например: gpt-4')
                    ->help('Уникальное название для внутреннего использования')
                    ->required(),

                Input::make('show_name')
                    ->title('Отображаемое название')
                    ->placeholder('Например: ChatGPT 4')
                    ->help('Название, которое видят пользователи')
                    ->required(),

                Input::make('temperature')
                    ->title('Температура (0-100)')
                    ->type('number')
                    ->min(0)
                    ->max(100)
                    ->value(50)
                    ->help('Уровень креативности нейросети'),

                Input::make('countLastMessage')
                    ->title('Количество последних сообщений')
                    ->type('number')
                    ->min(1)
                    ->max(20)
                    ->value(5)
                    ->help('Сколько предыдущих сообщений учитывать в контексте'),

                TextArea::make('description')
                    ->title('Описание')
                    ->placeholder('Краткое описание нейросети')
                    ->rows(3)
                    ->maxlength(150)
                    ->help('Максимум 150 символов'),

            ]))->title("Добавить нейросеть")->applyButton('Добавить'),

            Layout::modal('editNeural', Layout::rows([
                Input::make('neural.id')->type('hidden'),

                Input::make('neural.name')
                    ->title('Системное название')
                    ->placeholder('Например: gpt-4')
                    ->help('Уникальное название для внутреннего использования')
                    ->required(),

                Input::make('neural.show_name')
                    ->title('Отображаемое название')
                    ->placeholder('Например: ChatGPT 4')
                    ->help('Название, которое видят пользователи')
                    ->required(),

                Input::make('neural.temperature')
                    ->title('Температура (0-100)')
                    ->type('number')
                    ->min(0)
                    ->max(100)
                    ->help('Уровень креативности нейросети'),

                Input::make('neural.countLastMessage')
                    ->title('Количество последних сообщений')
                    ->type('number')
                    ->min(1)
                    ->max(20)
                    ->help('Сколько предыдущих сообщений учитывать в контексте'),

                TextArea::make('neural.description')
                    ->title('Описание')
                    ->placeholder('Краткое описание нейросети')
                    ->rows(3)
                    ->maxlength(150)
                    ->help('Максимум 150 символов'),

            ]))->async('asyncGetNeural')->title('Редактировать нейросеть')->applyButton('Сохранить'),
        ];
    }

    public function asyncGetNeural(Neural $neural): array
    {
        return [
            'neural' => $neural
        ];
    }

    public function update(Request $request): void
    {
        $request->validate([
            'neural.name' => 'required|string|max:40',
            'neural.show_name' => 'required|string|max:40',
            'neural.temperature' => 'required|integer|min:0|max:100',
            'neural.countLastMessage' => 'required|integer|min:1|max:20',
            'neural.description' => 'required|string|max:150',
        ]);

        Neural::find($request->input('neural.id'))->update($request->neural);
        Toast::info('Нейросеть успешно обновлена');
    }

    public function delete(Request $request): void
    {
        Neural::find($request->neural)->delete();
        Toast::info('Нейросеть успешно удалена');
    }

    public function create(Request $request): void
    {
        $request->validate([
            'name' => 'required|string|max:40|unique:neurals,name',
            'show_name' => 'required|string|max:40',
            'temperature' => 'integer|min:0|max:100',
            'countLastMessage' => 'integer|min:1',
            'description' => 'string|nullable|max:150',
        ]);

        Neural::create($request->all());
        Toast::info('Нейросеть успешно добавлена');
    }
}
