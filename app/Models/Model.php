<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Model extends EloquentModel
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function parts(): HasMany
    {
        return $this->hasMany(Part::class);
    }
}
