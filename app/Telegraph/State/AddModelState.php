<?php

namespace App\Telegraph\State;

use App\Telegraph\Steps\AddModel\AskModelNameStep;
use DefStudio\Telegraph\DTO\Message;
use App\Telegraph\Managers\StepManager;
use App\Telegraph\Contracts\StateInterface;
use App\Telegraph\Steps\Register\AskPassStep;
use DefStudio\Telegraph\Models\TelegraphChat;
use App\Telegraph\Steps\Register\AskPhoneStep;

class AddModelState implements StateInterface
{
    public static function name(): string
    {
        return strtolower('AddModel');
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
            AskModelNameStep::class
        ];
    }
}
