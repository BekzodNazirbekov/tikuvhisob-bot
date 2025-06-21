<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model as EloquentModel;

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

    public function part()
    {
        return $this->belongsTo(Part::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
