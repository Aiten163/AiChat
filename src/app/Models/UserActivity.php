<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;
use Orchid\Filters\Filterable;
use Orchid\Filters\Types\Like;
use Orchid\Filters\Types\Where;
use Orchid\Filters\Types\WhereDateStartEnd;

class UserActivity extends Model
{
    use Filterable;
    public $timestamps = false;

    protected $table = 'userActivity';

    protected $hidden = [
        'remember_token',
    ];

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
    protected $allowedFilters = [
        'id' => Where::class,
        'name' => Like::class,
        'is_admin' => Where::class,
        'number_messages' => Where::class,
        'lastLogin' => WhereDateStartEnd::class,
        'lastMessage' => WhereDateStartEnd::class,
    ];

    /**
     * Available sorts for the model
     */
    protected $allowedSorts = [
        'id',
        'name',
        'is_admin',
        'number_messages',
        'lastLogin',
        'lastMessage',
    ];
    public static function updateActivity($user_id)
    {
        $activity = UserActivity::where('user_id', $user_id)->first();
        $activity->increment('number_messages');
        $activity->lastMessage = now();
        $activity->save();
    }

    public static function updateLastLogin($user_id)
    {
        UserActivity::where('user_id', $user_id)->update(['lastLogin' => now()]);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
