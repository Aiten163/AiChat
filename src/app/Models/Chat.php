<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Chat extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'name',
        'show',
        'lastMessage',
    ];

    protected $casts = [
        'show' => 'boolean',
        'lastMessage' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function chatMessages()
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function getLastMessages($number = 1)
    {
        return $this->chatMessages()
            ->latest()
            ->limit($number)
            ->get(['message', 'role']);
    }

    public function isOwnedBy(int $userId): bool
    {
        return $this->user_id === $userId;
    }
}
