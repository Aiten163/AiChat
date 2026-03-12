<?php

namespace App\Models;

use Orchid\Filters\Filterable;
use Orchid\Filters\Types\Like;
use Orchid\Filters\Types\Where;
use Orchid\Filters\Types\WhereDateStartEnd;
use Orchid\Platform\Models\User as Authenticatable;

class User extends Authenticatable
{
    use Filterable;

    protected $fillable = [
        'name',
        'email',
        'is_admin',
    ];

    protected $hidden = [
        'remember_token',
    ];

    protected $allowedFilters = [
        'id' => Where::class,
        'name' => Like::class,
        'is_admin' => Where::class,
    ];

    protected $allowedSorts = [
        'id',
        'name',
        'is_admin',
    ];

    protected static function boot()
    {
        parent::boot();

        static::created(function ($user) {
            $user->userActivity()->create();
        });
    }

    public function userActivity()
    {
        return $this->hasOne(UserActivity::class);
    }

    public function chats()
    {
        return $this->hasMany(Chat::class);
    }

    public function hasAccess(string $permission, $cache = true): bool
    {
        return $this->is_admin;
    }

    public function username()
    {
        return 'login';
    }

    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    public function getAuthPassword()
    {
        return '';
    }

    public static function getAdmins()
    {
        return self::where('is_admin', true)->get();
    }
}
