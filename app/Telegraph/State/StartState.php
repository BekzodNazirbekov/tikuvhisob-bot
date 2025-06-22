<?php

namespace App\Telegraph\State;

use App\Telegraph\Contracts\StateInterface;
use App\Telegraph\Managers\StepManager;
use App\Telegraph\Steps\AskHomeStep;
use App\Telegraph\Steps\AskLangStep;
use App\Telegraph\Steps\AskNameStep;
use App\Telegraph\Steps\AskPhoneStep;
use App\Telegraph\Steps\ChooseModelStep;
use App\Telegraph\Steps\ChoosePartStep;
use App\Telegraph\Steps\EnterQuantityStep;
use DefStudio\Telegraph\Models\TelegraphChat;
use DefStudio\Telegraph\DTO\Message;

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
            ChooseModelStep::class,
            ChoosePartStep::class,
            EnterQuantityStep::class
        ];
    }
}
