<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Filters\Filterable;
use Orchid\Filters\Types\Where;

class Neural extends Model
{
    use Filterable;
    use HasFactory;

    protected $fillable = [
        'name',
        'show_name',
        'temperature',
        'description',
        'countLastMessage',
    ];

    protected $casts = [
        'temperature' => 'integer',
        'countLastMessage' => 'integer',
    ];
}
