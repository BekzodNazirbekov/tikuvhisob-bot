<?php

namespace App\Telegraph\Steps;

use DefStudio\Telegraph\Contracts\StepHandler;
use DefStudio\Telegraph\Keyboard\Keyboard;
use DefStudio\Telegraph\Models\TelegraphBot;
use DefStudio\Telegraph\Models\TelegraphChat;
use App\Models\Model;

class ChooseModelStep implements StepHandler
{
    public function handle(TelegraphBot $bot, TelegraphChat $chat, mixed $payload = null): StepHandler|null
    {
        if ($payload !== null) {
            return new ChoosePartStep((int)$payload);
        }

        $keyboard = Keyboard::make();
        foreach (Model::all() as $model) {
            $keyboard->button($model->name)->param('payload', $model->id)->row();
        }

        $chat->message('Select model:')->keyboard($keyboard)->send();

        return null;
    }
}
