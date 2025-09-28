<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Chat extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
    ];

    /**
     * Get the user that owns the chat.
     */
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
        return $this->chatMessages()->latest()->limit($number)->get(['message', 'role']);
    }

}
