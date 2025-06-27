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
        'status',
        'count',
    ];

    public function model(): BelongsTo
    {
        return $this->belongsTo(Model::class);
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        if (!in_array($status, ['active', 'inactive'])) {
            throw new \InvalidArgumentException('Status must be either "active" or "inactive".');
        }
        $this->status = $status;
        $this->save();
    }
    public function getCount(): int
    {
        return $this->count;
    }
    public function setCount(int $count): void
    {
        if ($count < 0) {
            throw new \InvalidArgumentException('Count cannot be negative.');
        }
        $this->count = $count;
        $this->save();
    }
}
