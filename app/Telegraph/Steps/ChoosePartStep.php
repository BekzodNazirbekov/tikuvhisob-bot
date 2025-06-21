<?php

namespace App\Telegraph\Steps;

use DefStudio\Telegraph\Contracts\StepHandler;
use DefStudio\Telegraph\Keyboard\Keyboard;
use DefStudio\Telegraph\Models\TelegraphBot;
use DefStudio\Telegraph\Models\TelegraphChat;
use App\Models\Part;

class ChoosePartStep implements StepHandler
{
    public function __construct(private int $modelId)
    {
    }

    public function handle(TelegraphBot $bot, TelegraphChat $chat, mixed $payload = null): StepHandler|null
    {
        if ($payload !== null) {
            return new EnterQuantityStep($this->modelId, (int)$payload);
        }

        $keyboard = Keyboard::make();
        foreach (Part::where('model_id', $this->modelId)->get() as $part) {
            $keyboard->button($part->name)->param('payload', $part->id)->row();
        }

        $chat->message('Select part:')->keyboard($keyboard)->send();

        return null;
    }
}
