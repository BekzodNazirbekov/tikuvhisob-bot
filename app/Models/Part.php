<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Part extends EloquentModel
{
    use HasFactory;

    protected $fillable = [
        'model_id',
        'name',
        'price',
    ];

    public function model(): BelongsTo
    {
        return $this->belongsTo(Model::class);
    }
}
