<?php

namespace App\Telegraph\Managers;

use DefStudio\Telegraph\Models\TelegraphChat;
use DefStudio\Telegraph\DTO\Message;

class StepManager
{
    public static function start(TelegraphChat $chat, array $steps): void
    {
        $firstStep = $steps[0];
        cache()->put("step:{$chat->chat_id}", 0);
        app($firstStep)->ask($chat);
    }

    public static function next(TelegraphChat $chat): void
    {
        $index = cache()->get("step:{$chat->chat_id}", 0);
        $index++;

        $stateClass = cache()->get("state:{$chat->chat_id}");
        if (!$stateClass) {
            $chat->html("❌ Holat aniqlanmadi.")->send();
            return;
        }

        $steps = (new $stateClass)->steps();

        if (!isset($steps[$index])) {
            cache()->forget("step:{$chat->chat_id}");
            cache()->forget("state:{$chat->chat_id}");
            return;
        }

        cache()->put("step:{$chat->chat_id}", $index);
        app($steps[$index])->ask($chat);
    }

    public static function handleMessage(TelegraphChat $chat, Message $message, array $steps): void
    {
        $index = cache()->get("step:{$chat->chat_id}", 0);
        app($steps[$index])->handleMessage($chat, $message);
    }

    public static function handleCallback(TelegraphChat $chat, string $data, array $steps, $callbackQuery): void
    {
        $index = cache()->get("step:{$chat->chat_id}", 0);
        app($steps[$index])->handleCallback($chat, $data, $callbackQuery);
    }

    public static function goToStep(TelegraphChat $chat, string $stepClass, $edit = false, $messageId = null, $callbackQueryId = null): void
    {
        $stateClass = cache()->get("state:{$chat->chat_id}");
        if (!$stateClass) return;

        $steps = (new $stateClass)->steps();
        $index = array_search($stepClass, $steps);

        if ($index === false) return;

        cache()->put("step:{$chat->chat_id}", $index);
        app($stepClass)->ask($chat, $edit, $messageId, $callbackQueryId);
    }

}
