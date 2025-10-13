<?php

namespace App\Orchid\Screens\Analytics;

use App\Models\UserActivity;
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

                \App\Orchid\Layouts\Analytics\MessagesBarChart::make('charts', 'Сообщения по дням')
                    ->description('Столбчатая диаграмма сообщений'),
            ]),
        ];
    }

    /**
     * Получение данных для графика
     */
    private function getChartData(string $startDate, string $endDate): array
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        // Получаем данные из базы
        $data = UserActivity::whereNotNull('lastMessage')
            ->whereBetween('lastMessage', [$start, $end])
            ->selectRaw('DATE(lastMessage) as date, SUM(number_messages) as total_messages')
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
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        $totalMessages = UserActivity::whereBetween('lastMessage', [$start, $end])
            ->sum('number_messages');

        $activeUsers = UserActivity::whereBetween('lastMessage', [$start, $end])
            ->distinct('user_id')
            ->count('user_id');

        $avgMessages = $activeUsers > 0 ? $totalMessages / $activeUsers : 0;

        return [
            'total_messages' => $totalMessages,
            'active_users' => $activeUsers,
            'avg_messages_per_user' => round($avgMessages, 1),
        ];
    }
}
