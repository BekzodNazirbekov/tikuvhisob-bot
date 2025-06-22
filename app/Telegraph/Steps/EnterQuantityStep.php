<?php

namespace App\Telegraph\Steps;

use App\Telegraph\Managers\StateManager;
use App\Telegraph\Managers\StepManager;
use App\Telegraph\State\StartState;
use Carbon\Carbon;
use App\Models\User;
use App\Models\WorkEntry;
use App\Telegraph\Contracts\StepInterface;
use DefStudio\Telegraph\DTO\Message;
use DefStudio\Telegraph\Exceptions\StorageException;
use DefStudio\Telegraph\Models\TelegraphChat;
use DefStudio\Telegraph\Models\TelegraphBot;

class EnterQuantityStep implements StepInterface
{

    public function ask(TelegraphChat $chat, bool $edit = false, int $messageId = null): void
    {
        $chat->message('Enter quantity:')->send();
    }

    /**
     * @throws StorageException
     */
    public function handleMessage(TelegraphChat $chat, Message $message): void
    {

        $quantity = (int)$message->text();

        WorkEntry::create([
            'user_id' => $chat->user?->id,
            'part_id' => $chat->storage()->get('part-id'),
            'quantity' => $quantity,
            'date' => Carbon::today(),
        ]);

        $chat->message('Entry saved!')->send();

        StateManager::setState($chat, StartState::class);

    }

    public function handleCallback(TelegraphChat $chat, string $data, $callbackQuery): void
    {
        // TODO: Implement handleCallback() method.
    }
}
