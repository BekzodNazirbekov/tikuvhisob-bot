<?php

namespace App\Telegraph\Commands;

use App\Telegraph\Managers\StateManager;
use App\Telegraph\State\StartState;
use DefStudio\Telegraph\DTO\Message;
use DefStudio\Telegraph\Models\TelegraphBot;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Support\Stringable;

class StartCommand
{
    public function __invoke(Stringable $text, TelegraphBot $bot, TelegraphChat $chat, Message $message): void
    {

        StateManager::setState($chat, StartState::class);

    }
}
