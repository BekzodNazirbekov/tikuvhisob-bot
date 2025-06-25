<?php

namespace App\Telegraph\Steps\AddUser;

use DefStudio\Telegraph\DTO\Message;
use App\Telegraph\Managers\StepManager;
use App\Telegraph\Contracts\StepInterface;
use DefStudio\Telegraph\Models\TelegraphChat;
use DefStudio\Telegraph\Exceptions\StorageException;

class AskNameStep implements StepInterface
{
    public function ask(TelegraphChat $chat, bool $edit = false, int $messageId = null): void
    {
        $chat->html("Ishnining ismini kiriting!")->send();
    }

    /**
     * @throws StorageException
     */
    public function handleMessage(TelegraphChat $chat, Message $message): void
    {
        $name = $message->text();
        $chat->storage()->set('adduser_name', $name);
        StepManager::next($chat);
    }

    public function handleCallback(TelegraphChat $chat, string $data, $callbackQuery): void
    {

    }
}
