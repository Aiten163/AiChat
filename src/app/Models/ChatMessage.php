<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Orchid\Filters\Filterable;
use Orchid\Filters\Types\Like;
use Orchid\Filters\Types\Where;

class ChatMessage extends Model
{
    use HasFactory, Filterable;

    public $timestamps = false;
    protected $table = 'chatMessages';

    protected $fillable = [
        'role',
        'message',
        'chat_id',
        'created_at'
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    protected $allowedFilters = [
        'id'        => Where::class,
        'role'      => Where::class,
        'message'   => Like::class,
        'chat_id'   => Where::class,
        'created_at'=> Where::class,
    ];

    protected $allowedSorts = [
        'id',
        'role',
        'message',
        'chat_id',
        'created_at',
    ];

    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }
}
