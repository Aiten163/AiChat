<?php

namespace App\Orchid\Screens;

use App\Models\Neural;
use App\Models\Base_prompt;
use App\Orchid\Layouts\Neural\NeuralTable;
use Cloudstudio\Ollama\Facades\Ollama;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;
use Exception;

class NeuralScreen extends Screen
{
    private bool $ollamaAvailable = false;

    public function __construct()
    {
        $this->checkOllamaConnection();
    }

    public function query(): iterable
    {
        return [
            'neurals' => Neural::with('basePrompt')
                ->filters()
                ->defaultSort('id')
                ->paginate(),
            'ollama_available' => $this->ollamaAvailable,
            'base_prompts' => Base_prompt::all(),
        ];
    }

    public function name(): ?string
    {
        return 'Нейросети';
    }

    public function description(): ?string
    {
        if (!$this->ollamaAvailable) {
            return 'Ollama не подключена - управление нейросетями временно недоступно';
        }

        return 'Управление нейросетями и их настройками';
    }

    public function commandBar(): array
    {
        if (!$this->ollamaAvailable) {
            return [];
        }

        return [
            ModalToggle::make("Добавить нейросеть")
                ->modal('createNeural')
                ->method('create')
                ->icon('plus'),
        ];
    }

    public function layout(): array
    {
        $layouts = [
            NeuralTable::class,
        ];

        if ($this->ollamaAvailable) {
            $layouts[] = Layout::modal('createNeural', Layout::rows([
                Select::make('name')
                    ->options($this->getAvailableModels())
                    ->title('Системное название')
                    ->help('Уникальное название для внутреннего использования')
                    ->required()
                    ->empty('Не выбрано'),

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

                Select::make('base_prompt_id')
                    ->options($this->getBasePromptsOptions())
                    ->title('Базовый промт')
                    ->help('Выберите профиль промта для этой нейросети')
                    ->empty('Не выбран')
                    ->value(null),

                TextArea::make('description')
                    ->title('Описание')
                    ->placeholder('Краткое описание нейросети')
                    ->rows(3)
                    ->maxlength(150)
                    ->help('Максимум 150 символов'),
            ]))->title("Добавить нейросеть")->applyButton('Добавить');

            $layouts[] = Layout::modal('editNeural', Layout::rows([
                Input::make('neural.id')->type('hidden'),

                Input::make('neural.name')
                    ->title('Системное название')
                    ->disabled()
                    ->required(),

                Input::make('neural.show_name')
                    ->title('Отображаемое название')
                    ->required(),

                Input::make('neural.temperature')
                    ->title('Температура (0-100)')
                    ->type('number')
                    ->min(0)
                    ->max(100),

                Input::make('neural.countLastMessage')
                    ->title('Количество последних сообщений')
                    ->type('number')
                    ->min(1),

                Select::make('neural.base_prompt_id')
                    ->options($this->getBasePromptsOptions())
                    ->title('Базовый промт')
                    ->empty('Не выбран'),

                TextArea::make('neural.description')
                    ->title('Описание')
                    ->rows(3)
                    ->maxlength(150),
            ]))->async('asyncGetNeural')->title('Редактировать нейросеть')->applyButton('Сохранить');
        } else {
            $layouts[] = Layout::view('admin.ollama-unavailable');
        }

        return $layouts;
    }

    private function checkOllamaConnection(): void
    {
        try {
            $models = Ollama::models();
            $this->ollamaAvailable = isset($models['models']) && is_array($models['models']);
        } catch (Exception $e) {
            $this->ollamaAvailable = false;
        }
    }

    private function getAvailableModels(): array
    {
        if (!$this->ollamaAvailable) {
            return [];
        }

        try {
            $models = Ollama::models();
            return collect($models['models'])
                ->mapWithKeys(fn($model) => [$model['name'] => $model['name']])
                ->toArray();
        } catch (Exception $e) {
            Toast::error('Ошибка получения списка моделей');
            return [];
        }
    }

    private function getBasePromptsOptions(): array
    {
        try {
            return Base_prompt::all()
                ->mapWithKeys(function ($prompt) {
                    return [$prompt->id => $prompt->name ?: "Промт #{$prompt->id}"];
                })
                ->toArray();
        } catch (Exception $e) {
            return [];
        }
    }

    public function asyncGetNeural(Neural $neural): array
    {
        return [
            'neural' => $neural
        ];
    }

    public function update(Request $request): void
    {
        if (!$this->ollamaAvailable) {
            Toast::error('Ollama недоступна - изменение нейросетей временно заблокировано');
            return;
        }

        $request->validate([
            'neural.show_name' => 'required|string|max:40',
            'neural.temperature' => 'required|integer|min:0|max:100',
            'neural.countLastMessage' => 'required|integer|min:1|max:20',
            'neural.description' => 'nullable|string|max:150',
            'neural.base_prompt_id' => 'nullable|exists:base_prompts,id',
        ]);

        $neural = Neural::find($request->input('neural.id'));
        $neural->update($request->neural);

        Cache::tags(['neurals'])->flush();

        Toast::info('Нейросеть успешно обновлена');
    }

    public function delete(Request $request): void
    {
        if (!$this->ollamaAvailable) {
            Toast::error('Ollama недоступна - удаление нейросетей временно заблокировано');
            return;
        }

        $neural = Neural::find($request->neural);
        $neural->delete();

        Cache::tags(['neurals'])->flush();

        Toast::info('Нейросеть успешно удалена');
    }

    public function create(Request $request): void
    {
        if (!$this->ollamaAvailable) {
            Toast::error('Ollama недоступна - создание нейросетей временно заблокировано');
            return;
        }

        $request->validate([
            'name' => 'required|string|max:40|unique:neurals,name',
            'show_name' => 'required|string|max:40',
            'temperature' => 'integer|min:0|max:100',
            'countLastMessage' => 'integer|min:1',
            'description' => 'string|nullable|max:150',
            'base_prompt_id' => 'nullable|exists:base_prompts,id',
        ]);

        $neural = Neural::create($request->all());

        Cache::set('neural:' . $neural->id, $neural);

        Cache::tags(['neurals'])->flush();

        Toast::info('Нейросеть успешно добавлена');
    }
}
