<?php

namespace App\Services\Report;

use App\Models\User;
use App\Notifications\ReportNotification;
use App\Services\Storage\FileStorageService;
use Illuminate\Http\UploadedFile;

class ReportService
{
    public function __construct(
        private readonly FileStorageService $storageService,
        private readonly AdminNotificationService $notificationService
    ) {}

    public function sendReport(User $user, string $message, ?UploadedFile $image = null): void
    {
        $imagePath = null;

        if ($image) {
            $imagePath = $this->storageService->storeReportImage($image);
        }

        $this->notificationService->notifyAdmins(
            new ReportNotification($user, $message, $imagePath)
        );
    }
}
