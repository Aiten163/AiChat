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
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'is_admin',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'remember_token',
    ];


    /**
     * The attributes for which you can use filters in url.
     *
     * @var array
     */
    protected $allowedFilters = [
        'id' => Where::class,
        'name' => Like::class,
        'is_admin' => Where::class,
    ];

    /**
     * The attributes for which can use sort in url.
     *
     * @var array
     */
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

    public function validateForPassportPasswordGrant($password)
    {
        return true;
    }
}
