<?php

namespace App\Telegraph\Managers;

use App\Telegraph\Contracts\StateInterface;
use DefStudio\Telegraph\Models\TelegraphChat;
use DefStudio\Telegraph\DTO\Message;

class StateManager
{
    public static function setState(TelegraphChat $chat, string $stateClass): void
    {
        cache()->put("state:{$chat->chat_id}", $stateClass);
        app($stateClass)->enter($chat);
    }

    public static function clearState(TelegraphChat $chat): void
    {
        cache()->forget("state:{$chat->chat_id}");
    }

    public static function getState(TelegraphChat $chat): ?StateInterface
    {
        $class = cache()->get("state:{$chat->chat_id}");

        return $class ? app($class) : null;
    }

    public static function handleMessage(TelegraphChat $chat, Message $message): void
    {
        $state = self::getState($chat);

        if ($state) {
            $state->handleMessage($chat, $message);
        } else {
            $chat->html("❓ Siz hech qanday jarayonda emassiz.")->send();
        }
    }

    public static function handleCallback(TelegraphChat $chat, string $data, $callbackQuery = null): void
    {
        $state = self::getState($chat);

        if ($state) {
            $state->handleCallback($chat, $data, $callbackQuery);
        } else {
            $chat->html("❓ Callback qayerga tegishli ekani noma'lum.")->send();
        }
    }
}

