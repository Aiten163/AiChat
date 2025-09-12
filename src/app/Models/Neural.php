<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Orchid\Filters\Filterable;
use Orchid\Filters\Types\Where;

class neural extends Model
{
    use Filterable;
    public $timestamps = false;
    protected $fillable = [
        'name',
        'link',
        'name_return',
    ];
    protected $allowedFilters=[
        'id'=> Where::class,
        'name'=> Where::class,
        'link'=> Where::class,
        'name_return'=> Where::class,
    ];
}
