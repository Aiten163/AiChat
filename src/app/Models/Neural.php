<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use Orchid\Filters\Filterable;
use Orchid\Filters\Types\Like;
use Orchid\Filters\Types\Where;

class Neural extends Model
{
    use Filterable, HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'name',
        'show_name',
        'temperature',
        'description',
        'countLastMessage',
        'base_prompt_id',
    ];

    protected $casts = [
        'temperature' => 'integer',
        'countLastMessage' => 'integer',
    ];

    protected $allowedFilters = [
        'id' => Where::class,
        'name' => Like::class,
        'show_name' => Like::class,
        'temperature' => Where::class,
        'countLastMessage' => Where::class,
    ];

    protected $allowedSorts = [
        'id',
        'name',
        'show_name',
        'temperature',
        'countLastMessage',
    ];

    public function getOnName($neural_name): self
    {
        if(Cache::has('neurals')) {}
        $this->where('name', $neural_name)->first();
        return $this->getOnName;
    }

    public function neuralFilter()
    {
        return $this->hasMany(NeuralFilter::class);
    }

    public function basePrompt()
    {
        return $this->belongsTo(Base_prompt::class, 'base_prompt_id');
    }
}
