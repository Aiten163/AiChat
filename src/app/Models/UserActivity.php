<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserActivity extends Model
{
    use HasFactory;

    protected $table = 'userActivity';

    protected $fillable = [
        'user_id',
        'number_messages',
        'lastMessage',
        'lastLogin'
    ];

    protected $casts = [
        'lastMessage' => 'datetime',
        'lastLogin' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
