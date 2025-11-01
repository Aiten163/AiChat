<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class NeuralFilter extends Model
{
    use HasFactory, AsSource, Filterable;
    public $timestamps = false;
    protected $table = 'neural_filters';
    protected $fillable = [
        'name',
        'prompt',
        'simpleFilter',
        'activeSimple',
        'activePrompt',
        'neural_id'
    ];
    protected $casts = [
        'activePrompt' => 'boolean',
        'activeSimple' => 'boolean',
    ];

    public function neural(): BelongsTo
    {
        return $this->belongsTo(Neural::class, 'neural_id');
    }
    public function scopeActivePrompt($query)
    {
        return $query->where('activePrompt', true);
    }

    public function scopeActiveSimple($query)
    {
        return $query->where('activeSimple', true);
    }
}
