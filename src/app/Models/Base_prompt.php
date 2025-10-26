<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Base_prompt extends Model
{
    use HasFactory, Filterable, AsSource;
    public $timestamps = false;
    protected $table = 'base_prompts';

    protected $fillable = [
        'name',
        'prompt',
        'id'
    ];
    public function neurals()
    {
        return $this->hasMany(Neural::class, 'base_prompt_id');
    }
    protected $allowedFilters = [
        'name',
        'prompt',
        'id'
    ];

    protected $allowedSorts = [
        'id',
        'name',
        'id'
    ];
}
