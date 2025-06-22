<?php

namespace App\Models;

use DefStudio\Telegraph\Models\TelegraphChat as BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Chat extends BaseModel
{
    protected $fillable = [
        'name',
        'chat_id',
        'user_id',
        'telegraph_bot_id',
    ];

    /**
     * Get the user that owns the chat.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
