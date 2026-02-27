<?php

namespace App\Orchid\Screens\Analytics;

use App\Models\Chat;
use App\Models\ChatMessage;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\DateRange;
use Orchid\Screen\Layouts\Metric;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class MessagesChartScreen extends Screen
{
    /**
     * Query data.
     *
     * @return array
     */
    public function query(Request $request): iterable
    {
        $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        $chartData = $this->getChartData($startDate, $endDate);
        $periodStats = $this->getPeriodStats($startDate, $endDate);
        $generalStats = $this->getGeneralStats();

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
            'generalStats' => $generalStats,
            'periodStats' => $periodStats,
        ];
    }

    public function name(): ?string
    {
        return 'Аналитика сообщений';
    }

    public function description(): ?string
    {
        return 'График количества сообщений по дням и статистика';
    }

    public function commandBar(): array
    {
        return [
            Button::make('Применить')
                ->method('applyFilters')
                ->icon('check'),
            Button::make('Сбросить')
                ->method('resetFilters')
                ->icon('refresh'),
        ];
    }

    public function layout(): array
    {
        return [
            // Форма фильтров
            Layout::rows([
                DateRange::make('dateRange')
                    ->title('Диапазон дат')
                    ->value([
                        'start' => request('start_date', now()->subDays(30)->format('Y-m-d')),
                        'end' => request('end_date', now()->format('Y-m-d')),
                    ]),

                Button::make('Применить фильтры')
                    ->method('applyFilters')
                    ->icon('filter')
                    ->class('btn btn-primary'),
            ])->title('Фильтры'),

            // Общая статистика
            Layout::metrics([
                'Всего чатов' => 'generalStats.total_chats',
                'Всего сообщений' => 'generalStats.total_messages',
                'Уникальных пользователей' => 'generalStats.unique_users',
                'Чатов с сообщениями' => 'generalStats.chats_with_messages',
            ])->title('Общая статистика'),

            // Статистика за период
            Layout::metrics([
                'Сообщений за период' => 'periodStats.messages',
                'Активных чатов' => 'periodStats.active_chats',
                'Новых активных чатов' => 'periodStats.new_active_chats',
                'Среднее сообщений в день' => 'periodStats.avg_messages_per_day',
            ])->title('Статистика за выбранный период'),

            // График
            \App\Orchid\Layouts\Analytics\MessagesLineChart::make('charts')
                ->title('График сообщений по дням'),
        ];
    }

    /**
     * Применение фильтров
     */
    public function applyFilters(Request $request)
    {
        $dateRange = $request->input('dateRange', []);

        return redirect()->route('platform.analytics.messages', [
            'start_date' => $dateRange['start'] ?? now()->subDays(30)->format('Y-m-d'),
            'end_date' => $dateRange['end'] ?? now()->format('Y-m-d'),
        ]);
    }

    /**
     * Сброс фильтров
     */
    public function resetFilters()
    {
        return redirect()->route('platform.analytics.messages');
    }

    /**
     * Получение данных для графика
     */
    private function getChartData(string $startDate, string $endDate): array
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        $messagesData = ChatMessage::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as message_count')
        )
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $period = CarbonPeriod::create($startDate, $endDate);

        $labels = [];
        $values = [];

        foreach ($period as $date) {
            $dateString = $date->format('Y-m-d');
            $labels[] = $date->format('d.m.Y');
            $messageCount = $messagesData->firstWhere('date', $dateString);
            $values[] = $messageCount ? $messageCount->message_count : 0;
        }

        return [
            'labels' => $labels,
            'values' => $values
        ];
    }

    /**
     * Общая статистика
     */
    private function getGeneralStats(): array
    {
        return [
            'total_chats' => Chat::count(),
            'total_messages' => ChatMessage::count(),
            'unique_users' => Chat::distinct('user_id')->count('user_id'),
            'chats_with_messages' => Chat::whereNotNull('lastMessage')->count(),
        ];
    }

    /**
     * Статистика за выбранный период
     */
    private function getPeriodStats(string $startDate, string $endDate): array
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        // Количество дней в периоде
        $daysCount = $start->diffInDays($end) + 1;

        // Количество сообщений за период
        $messages = ChatMessage::whereBetween('created_at', [$start, $end])->count();

        // Активные чаты (у которых есть сообщения за период)
        $activeChats = Chat::whereHas('chatMessages', function ($query) use ($start, $end) {
            $query->whereBetween('created_at', [$start, $end]);
        })->count();

        // Новые активные чаты (у которых первое сообщение в этом периоде)
        $newActiveChats = Chat::whereHas('chatMessages', function ($query) use ($start, $end) {
            $query->whereBetween('created_at', [$start, $end])
                ->whereRaw('created_at = (
                      SELECT MIN(created_at)
                      FROM chatMessages
                      WHERE chat_id = chats.id
                  )');
        })->count();

        // Среднее количество сообщений в день
        $avgMessagesPerDay = $daysCount > 0 ? round($messages / $daysCount, 2) : 0;

        return [
            'messages' => $messages,
            'active_chats' => $activeChats,
            'new_active_chats' => $newActiveChats,
            'avg_messages_per_day' => $avgMessagesPerDay,
            'period_days' => $daysCount,
        ];
    }
}
