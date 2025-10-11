<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Orchid\Filters\Filterable;

class ChatMessage extends Model
{
    use HasFactory;
    use Filterable;

    protected $table = 'chatMessages';
    protected $fillable = [
        'role',
        'message',
        'chat_id',
    ];

    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }
}
