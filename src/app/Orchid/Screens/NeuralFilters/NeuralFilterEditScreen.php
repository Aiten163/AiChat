<?php

namespace App\Orchid\Screens\NeuralFilters;

use App\Models\Neural;
use App\Models\NeuralFilter;
use Illuminate\Http\Request;
use Nette\Utils\Html;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Quill;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class NeuralFilterEditScreen extends Screen
{
    public ?NeuralFilter $filter = null;

    public function query(NeuralFilter $filter): iterable
    {
        return [
            'filter' => $filter,
        ];
    }

    public function name(): ?string
    {
        return $this->filter->exists ? 'Редактировать фильтр' : 'Добавить фильтр';
    }

    public function commandBar(): array
    {
        return [
            Button::make('Сохранить')
                ->icon('check')
                ->method('save'),

            Button::make('Удалить')
                ->icon('trash')
                ->method('remove')
                ->canSee($this->filter->exists),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Input::make('filter.name')
                    ->title('Название фильтра')
                    ->placeholder('Введите название'),

                Relation::make('filter.neural_id')
                    ->title('Нейросеть для фильтрации')
                    ->fromModel(Neural::class, 'show_name'),

                TextArea::make('filter.prompt')
                    ->title('Промпт для нейросети')
                    ->rows(10)
                    ->placeholder('Введите промпт для фильтрации'),

                CheckBox::make('filter.activePrompt')
                    ->title('Активировать промпт')
                    ->sendTrueOrFalse(),

                TextArea::make('filter.simpleFilter')
                    ->title('Простой фильтр')
                    ->rows(3)
                    ->placeholder('Введите список запрещенных слов в запросе "договор, пароль, ip"'),

                CheckBox::make('filter.activeSimple')
                    ->title('Активировать простой фильтр')
                    ->sendTrueOrFalse()
                  ]),
        ];
    }

    public function save(NeuralFilter $filter, Request $request)
    {
        \Cache::forget('neuralFilter');
        $data = $request->get('filter');
        $neuralId = $data['neural_id'] ?? $filter->neural_id;

        // Проверяем, хочет ли пользователь активировать хотя бы один тип фильтра
        $wantsToActivate = (
            (isset($data['activePrompt']) && $data['activePrompt']) ||
            (isset($data['activeSimple']) && $data['activeSimple'])
        );

        if ($wantsToActivate) {
            // Деактивируем ВСЕ фильтры для этой нейросети (и промпт, и простой)
            NeuralFilter::where('neural_id', $neuralId)
                ->update([
                    'activePrompt' => false,
                    'activeSimple' => false
                ]);
        }

        // Сохраняем текущий фильтр с новыми значениями
        $filter->fill($data)->save();

        Alert::success('Фильтр успешно сохранён.');
        return redirect()->route('platform.neural-filters.list');
    }


    public function remove(NeuralFilter $filter)
    {
        \Cache::forget('neuralFilter');
        $filter->delete();

        Alert::success('Фильтр удален.');

        return redirect()->route('platform.neural-filters.list');
    }
}
