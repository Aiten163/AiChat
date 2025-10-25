<?php

namespace App\Orchid\Screens\Analytics;

use App\Models\ChatMessage;
use Illuminate\Support\Facades\Log;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MessagesChartScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Request $request): iterable
    {
        $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        $chartData = $this->getChartData($startDate, $endDate);

        return [
            'charts' => [
                [
                    'name'   => 'Количество сообщений по дням',
                    'values' => $chartData['values'],
                    'labels' => $chartData['labels'],
                ],
            ],
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'stats' => $this->getStats($startDate, $endDate),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return 'Статистика сообщений';
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return 'График количества сообщений пользователей за период времени';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            // Фильтры
            Layout::view('admin.analytics.filters'),

            // Статистика
            Layout::view('admin.analytics.stats'),

            // Графики
            Layout::columns([
                \App\Orchid\Layouts\Analytics\MessagesLineChart::make('charts', 'График сообщений')
                    ->description('Динамика сообщений по дням'),
            ]),
        ];
    }

    /**
     * Получение данных для графика
     */
    private function getChartData(string $startDate, string $endDate): array
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        // Получаем данные из таблицы chatMessages
        $data = ChatMessage::whereBetween('created_at', [$start, $end])
            ->where('role', 'user')
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total_messages')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Подготавливаем массивы для графика
        $labels = [];
        $values = [];

        // Заполняем все даты в промежутке
        $currentDate = $start->copy();
        while ($currentDate <= $end) {
            $dateString = $currentDate->format('Y-m-d');
            $labels[] = $currentDate->format('d.m.Y');

            $found = $data->firstWhere('date', $dateString);
            $values[] = $found ? (int)$found->total_messages : 0;

            $currentDate->addDay();
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    /**
     * Статистика за период
     */
    private function getStats(string $startDate, string $endDate): array
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        // Общее количество сообщений
        $totalMessages = ChatMessage::whereBetween('created_at', [$start, $end])->where('role', 'user')->count();

        // Количество уникальных пользователей (через связь с чатами)
        $activeUsers = ChatMessage::whereBetween('created_at', [$start, $end])
            ->whereHas('chat', function($query) {
                $query->whereNotNull('user_id');
            })
            ->join('chats', 'chatMessages.chat_id', '=', 'chats.id')
            ->distinct('chats.user_id')
            ->count('chats.user_id');

        // Среднее количество сообщений на пользователя
        $avgMessages = $activeUsers > 0 ? $totalMessages / $activeUsers : 0;

        return [
            'total_messages' => $totalMessages,
            'active_users' => $activeUsers,
            'avg_messages_per_user' => round($avgMessages, 1),
        ];
    }
}
