<?php

namespace App\Orchid\Screens;

use App\Models\Base_prompt as BasePrompt;
use Illuminate\Support\Facades\Cache;
use Orchid\Support\Facades\Toast;
use Orchid\Screen\Screen;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Fields\Group;
use Illuminate\Http\Request;

class BasePromptScreen extends Screen
{
    public $name = 'Промты для нейросетей';
    public $description = 'Добавление, редактирование и удаление промтов';
    public $permission = [];

    public function query(): array
    {
        return [
            'prompts' => BasePrompt::orderBy('id', 'desc')->paginate(),
        ];
    }

    public function commandBar(): array
    {
        return [
            ModalToggle::make('Добавить промт')
                ->icon('plus')
                ->modal('editPrompt')
                ->method('savePrompt')
                ->async('asyncGetPrompt'),
        ];
    }

    public function layout(): array
    {
        return [
            Layout::table('prompts', [
                TD::make('id', 'ID')->sort(),
                TD::make('name', 'Название'),
                TD::make('prompt', 'Промт')->render(function (BasePrompt $prompt) {
                    return \Illuminate\Support\Str::limit($prompt->prompt,200);
                }),
                TD::make('Действия')
                    ->align(TD::ALIGN_CENTER)
                    ->render(fn (BasePrompt $prompt) => Group::make([
                        ModalToggle::make('Редактировать')
                            ->icon('pencil')
                            ->modal('editPrompt')
                            ->method('savePrompt')
                            ->asyncParameters([
                                'prompt' => $prompt->id,
                            ]),
                        Button::make('Удалить')
                            ->icon('trash')
                            ->method('delete')
                            ->parameters([
                                'id' => $prompt->id,
                            ])
                            ->confirm('Вы уверены, что хотите удалить этот промт?'),

                    ])),
            ]),

            Layout::modal('editPrompt', Layout::rows([
                Input::make('id')->type('hidden'),
                Input::make('name')->title('Название')->required(),
                TextArea::make('prompt')->title('Текст промта')->rows(6)->required(),
            ]))
                ->title('Редактировать промт')
                ->applyButton('Сохранить')
                ->closeButton('Отмена')
                ->async('asyncGetPrompt'),

        ];
    }

    public function asyncGetPrompt(int $prompt = null): array
    {
        if ($prompt) {
            return BasePrompt::findOrFail($prompt)->toArray();
        }

        return ['id' => null, 'name' => '', 'prompt' => ''];
    }

    public function savePrompt(Request $request)
    {
        $data = $request->validate([
            'id' => ['nullable', 'integer'],
            'name' => ['required', 'string'],
            'prompt' => ['required', 'string'],
        ]);

        BasePrompt::updateOrCreate(
            ['id' => $data['id'] ?? null],
            [
                'name' => $data['name'],
                'prompt' => $data['prompt'],
            ]
        );
        Cache::set('base_prompt:' . $data['id'], $data['prompt']);

        Toast::info('Промт успешно сохранён!');
        return redirect()->route('platform.base-prompts');
    }

    public function delete(Request $request)
    {
        BasePrompt::findOrFail($request->get('id'))->delete();
        Cache::delete('base_prompt:' . $request->get('id'));

        Toast::info('Промт удалён!');
        return redirect()->route('platform.base-prompts');
    }
}
