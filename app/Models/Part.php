<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model as EloquentModel;

class Part extends EloquentModel
{
    use HasFactory;

    protected $fillable = [
        'model_id',
        'name',
        'price',
    ];

    public function model()
    {
        return $this->belongsTo(Model::class);
    }
}
