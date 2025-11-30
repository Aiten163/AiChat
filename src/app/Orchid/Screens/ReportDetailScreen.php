<?php

namespace App\Orchid\Screens;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Orchid\Screen\Screen;
use Orchid\Screen\Actions\Link;
use Orchid\Support\Facades\Layout;

class ReportDetailScreen extends Screen
{
    /**
     * The notification data.
     */
    public array $report = [];

    /**
     * Fetch data to be displayed on the screen.
     */
    public function query(Request $request): array
    {
        $notificationId = $request->get('notification');

        if ($notificationId) {
            $notification = \Illuminate\Notifications\DatabaseNotification::find($notificationId);

            if ($notification) {
                $data = is_string($notification->data) ? json_decode($notification->data, true) : $notification->data;
                $reportData = $data['report_data'] ?? [];

                \Log::info('Report data debug', [
                    'notification_id' => $notificationId,
                    'report_data' => $reportData,
                    'has_image_path' => !empty($reportData['image_path']),
                    'image_path' => $reportData['image_path'] ?? null,
                ]);

                // Генерируем PUBLIC URL для изображения
                $imageUrl = null;
                if (!empty($reportData['image_path'])) {
                    // Просто получаем URL из public storage
                    $imageUrl = Storage::disk('public')->url($reportData['image_path']);

                    \Log::info('Generated image URL', [
                        'path' => $reportData['image_path'],
                        'url' => $imageUrl,
                        'file_exists' => Storage::disk('public')->exists($reportData['image_path'])
                    ]);
                }

                $this->report = [
                    'user_name' => $reportData['user_name'] ?? 'Анонимный пользователь',
                    'user_id' => $reportData['user_id'] ?? null,
                    'message' => $reportData['message'] ?? '',
                    'image_path' => $reportData['image_path'] ?? null,
                    'image_url' => $imageUrl, // Просто URL, не base64
                    'created_at' => $notification->created_at->format('d.m.Y H:i'),
                ];

                \Log::info('Final report data', [
                    'has_image_url' => !empty($imageUrl),
                    'image_url' => $imageUrl,
                    'report_keys' => array_keys($this->report)
                ]);
            }
        }

        return [
            'report' => $this->report,
        ];
    }

    public function name(): ?string
    {
        return 'Обращение в техподдержку';
    }

    public function commandBar(): array
    {
        return [
            Link::make('Назад')
                ->icon('arrow-left')
                ->route('platform.index'),
        ];
    }

    public function layout(): array
    {
        if (empty($this->report)) {
            return [
                Layout::view('admin.report-not-found'),
            ];
        }

        $layouts = [
            Layout::view('admin.report-detail', [
                'report' => $this->report,
            ]),
        ];

        // Всегда добавляем блок изображения (даже если его нет)
        $layouts[] = Layout::view('admin.report-image', [
            'report' => $this->report,
        ]);

        return $layouts;
    }
}
