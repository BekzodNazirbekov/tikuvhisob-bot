<?php

namespace App\Telegraph\Contracts;


use DefStudio\Telegraph\Models\TelegraphChat;
use DefStudio\Telegraph\DTO\Message;

interface StateInterface
{
    public static function name(): string;

    public function enter(TelegraphChat $chat): void;

    public function handleMessage(TelegraphChat $chat, Message $message): void;

    public function handleCallback(TelegraphChat $chat, string $data, $callbackQuery = null): void;

    public function steps(): array;
}
