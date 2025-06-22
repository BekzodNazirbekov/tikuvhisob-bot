<?php

namespace App\Telegraph\Contracts;

use DefStudio\Telegraph\Models\TelegraphChat;
use DefStudio\Telegraph\DTO\Message;

interface StepInterface
{
    public function ask(TelegraphChat $chat, bool $edit = false, int $messageId = null): void;

    public function handleMessage(TelegraphChat $chat, Message $message): void;

    public function handleCallback(TelegraphChat $chat, string $data, $callbackQuery): void;
}
