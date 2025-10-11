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

    public static function updateActivity($user_id)
    {
        $activity = UserActivity::where('user_id', $user_id)>-get();
        $activity->increment('number_messages');
        $activity->lastMessage = now();
        $activity->save();
    }

    public static function updateLastLogin($user_id)
    {
        $activity = UserActivity::where('user_id', $user_id)>-get();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
