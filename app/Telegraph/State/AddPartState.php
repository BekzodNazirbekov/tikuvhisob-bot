<?php

namespace App\Telegraph\State;

use App\Telegraph\Steps\AddPart\AskPartNameStep;
use App\Telegraph\Steps\AddPart\AskPartPriceStep;
use App\Telegraph\Steps\AddPart\AskPartCountStep;
use DefStudio\Telegraph\DTO\Message;
use App\Telegraph\Managers\StepManager;
use App\Telegraph\Contracts\StateInterface;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Console\View\Components\Ask;

class AddPartState implements StateInterface
{
    public static function name(): string
    {
        return strtolower('AddPart');
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
            AskPartNameStep::class,
            AskPartPriceStep::class,
            AskPartCountStep::class
        ];
    }
}
