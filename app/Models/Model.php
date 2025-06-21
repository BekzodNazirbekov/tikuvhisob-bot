<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model as EloquentModel;

class Model extends EloquentModel
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function parts()
    {
        return $this->hasMany(Part::class);
    }
}
