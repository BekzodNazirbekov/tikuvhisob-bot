<?php

namespace App\Telegraph\Steps;

use DefStudio\Telegraph\Contracts\StepHandler;
use DefStudio\Telegraph\Models\TelegraphBot;
use DefStudio\Telegraph\Models\TelegraphChat;
use App\Models\WorkEntry;
use App\Models\User;
use Carbon\Carbon;

class EnterQuantityStep implements StepHandler
{
    public function __construct(private int $modelId, private int $partId)
    {
    }

    public function handle(TelegraphBot $bot, TelegraphChat $chat, mixed $payload = null): StepHandler|null
    {
        if ($payload !== null) {
            $quantity = (int)$payload;
            $user = User::firstWhere('telegram_id', $chat->chat_id);
            WorkEntry::create([
                'user_id' => $user?->id,
                'part_id' => $this->partId,
                'quantity' => $quantity,
                'date' => Carbon::today(),
            ]);
            $chat->message('Entry saved!')->send();
            return null;
        }

        $chat->message('Enter quantity:')->send();
        return $this;
    }
}
