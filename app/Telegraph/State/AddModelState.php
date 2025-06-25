<?php

namespace App\Telegraph\State;

use DefStudio\Telegraph\DTO\Message;
use App\Telegraph\Managers\StepManager;
use App\Telegraph\Contracts\StateInterface;
use App\Telegraph\Steps\Register\AskPassStep;
use DefStudio\Telegraph\Models\TelegraphChat;
use App\Telegraph\Steps\Register\AskPhoneStep;

class RegisterState implements StateInterface
{
    public static function name(): string
    {
        return strtolower('Register');
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
            AskPhoneStep::class,
            AskPassStep::class
        ];
    }
}
