<?php

namespace App\Telegraph\State;

use App\Telegraph\Steps\AskHomeStep;
use DefStudio\Telegraph\DTO\Message;
use App\Telegraph\Steps\ChoosePartStep;
use App\Telegraph\Managers\StepManager;
use App\Telegraph\Steps\ChooseModelStep;
use App\Telegraph\Steps\EnterQuantityStep;
use App\Telegraph\Contracts\StateInterface;
use DefStudio\Telegraph\Models\TelegraphChat;

class StartState implements StateInterface
{
    public static function name(): string
    {
        return strtolower('Start');
    }

    public function enter(TelegraphChat $chat): void
    {
        StepManager::start($chat, $this->steps());
    }

    public function handleMessage(TelegraphChat $chat, Message $message): void
    {
        StepManager::handleMessage($chat, $message, $this->steps());
    }

    public function handleCallback(TelegraphChat $chat, string $data, $callbackQuery = null): void
    {
        StepManager::handleCallback($chat, $data, $this->steps(), $callbackQuery);
    }

    public function steps(): array
    {
        return [
            AskHomeStep::class,
            ChooseModelStep::class,
            ChoosePartStep::class,
            EnterQuantityStep::class
        ];
    }
}
