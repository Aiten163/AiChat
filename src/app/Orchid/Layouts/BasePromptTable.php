<?php

namespace App\Orchid\Screens;

use App\Models\Base_prompt as BasePrompt;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class BasePromptScreen extends Screen
{
    /**
     * Query data.
     */
    public function query(): array
    {
        return [
            'basePrompts' => BasePrompt::orderBy('id')->get(),
        ];
    }

    /**
     * Display header name.
     */
    public function name(): string
    {
        return 'Управление промтами';
    }

    /**
     * Button commands.
     */
    public function commandBar(): array
    {
        return [
            Button::make('Добавить промт')
                ->icon('plus')
                ->method('createForm'),
        ];
    }

    /**
     * Views.
     */
    public function layout(): array
    {
        return [
            // Таблица
            Layout::table('basePrompts', [
                TD::make('id', 'ID')->width('50'),
                TD::make('name', 'Название'),
                TD::make('prompt', 'Промт')
                    ->render(function (BasePrompt $prompt) {
                        return \Illuminate\Support\Str::limit($prompt->prompt, 100);
                    }),
                TD::make('actions', 'Действия')
                    ->render(function (BasePrompt $prompt) {
                        return
                            Button::make('Редактировать')
                                ->method('editForm')
                                ->parameters(['id' => $prompt->id]) . ' ' .
                            Button::make('Удалить')
                                ->method('remove')
                                ->confirm('Удалить промт?')
                                ->parameters(['id' => $prompt->id]);
                    }),
            ]),

            // Форма создания
            Layout::modal('createModal', [
                Layout::rows([
                    Input::make('name')
                        ->title('Название')
                        ->required(),
                    TextArea::make('prompt')
                        ->title('Промт')
                        ->rows(10)
                        ->required(),
                ]),
            ])
                ->title('Создать промт')
                ->applyButton('Создать')
                ->closeButton('Отмена')
                ->method('create'),

            // Форма редактирования
            Layout::modal('editModal', [
                Layout::rows([
                    Input::make('id')->type('hidden'),
                    Input::make('name')
                        ->title('Название')
                        ->required(),
                    TextArea::make('prompt')
                        ->title('Промт')
                        ->rows(10)
                        ->required(),
                ]),
            ])
                ->title('Редактировать промт')
                ->applyButton('Сохранить')
                ->closeButton('Отмена')
                ->method('update'),
        ];
    }

    /**
     * Показать форму создания
     */
    public function createForm()
    {
        // Просто открывает модальное окно
    }

    /**
     * Показать форму редактирования
     */
    public function editForm(Request $request)
    {
        $id = $request->get('id');
        $prompt = BasePrompt::find($id);

        if ($prompt) {
            // Данные автоматически подставятся в форму через модальное окно
            return [];
        }

        Alert::error('Промт не найден');
        return [];
    }

    /**
     * Создать промт
     */
    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'prompt' => 'required',
        ]);

        BasePrompt::create([
            'name' => $request->name,
            'prompt' => $request->prompt,
        ]);

        Alert::success('Промт создан');
    }

    /**
     * Обновить промт
     */
    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'name' => 'required',
            'prompt' => 'required',
        ]);

        $prompt = BasePrompt::find($request->id);

        if ($prompt) {
            $prompt->update([
                'name' => $request->name,
                'prompt' => $request->prompt,
            ]);
            Alert::success('Промт обновлен');
        } else {
            Alert::error('Промт не найден');
        }
    }

    /**
     * Удалить промт
     */
    public function remove(Request $request)
    {
        $prompt = BasePrompt::find($request->id);

        if ($prompt) {
            $prompt->delete();
            Alert::success('Промт удален');
        } else {
            Alert::error('Промт не найден');
        }
    }
}
