<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\ReportNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ReportService
{
    public function sendReport($user, string $message, ?UploadedFile $image = null): void
    {
        $imagePath = null;

        if ($image && $image->isValid()) {
            $fileName = 'reports/' . Str::uuid() . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('private', $fileName);
        }

        $admins = User::where('is_admin', 1)->get();
        \Notification::send($admins, new ReportNotification($user, $message, $imagePath));
    }
}
