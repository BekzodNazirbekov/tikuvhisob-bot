<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkEntry extends EloquentModel
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'part_id',
        'quantity',
        'date',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
