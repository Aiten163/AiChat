<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

abstract class DynamicNotification extends Notification
{
    protected function setDynamicMailConfig(): bool
    {
        try {
            if (Storage::disk('private')->exists('mail_settings.json')) {
                $mailSettings = json_decode(Storage::disk('private')->get('mail_settings.json'), true);

                config([
                    'mail.mailers.smtp.host' => $mailSettings['host'] ?? config('mail.mailers.smtp.host'),
                    'mail.mailers.smtp.port' => $mailSettings['port'] ?? config('mail.mailers.smtp.port'),
                    'mail.mailers.smtp.username' => $mailSettings['username'] ?? config('mail.mailers.smtp.username'),
                    'mail.mailers.smtp.password' => $mailSettings['password'] ?? config('mail.mailers.smtp.password'),
                    'mail.mailers.smtp.encryption' => $mailSettings['encryption'] ?? config('mail.mailers.smtp.encryption'),
                    'mail.from.address' => $mailSettings['from_address'] ?? config('mail.from.address'),
                    'mail.from.name' => $mailSettings['from_name'] ?? config('mail.from.name'),
                ]);

                return true;
            }
        } catch (\Exception $e) {
            Log::error('Failed to set dynamic mail config: ' . $e->getMessage());
        }

        return false;
    }

    protected function getMailSettings(): ?array
    {
        try {
            if (Storage::disk('private')->exists('mail_settings.json')) {
                return json_decode(Storage::disk('private')->get('mail_settings.json'), true);
            }
        } catch (\Exception $e) {
            Log::error('Failed to get mail settings: ' . $e->getMessage());
        }

        return null;
    }

    protected function hasDynamicConfig(): bool
    {
        return Storage::disk('private')->exists('mail_settings.json');
    }
}
