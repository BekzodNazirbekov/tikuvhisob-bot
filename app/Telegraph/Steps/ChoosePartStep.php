<?php

namespace App\Telegraph\Steps;

use App\Models\Part;
use App\Telegraph\Managers\StepManager;
use App\Telegraph\Contracts\StepInterface;
use DefStudio\Telegraph\DTO\Message;
use DefStudio\Telegraph\Models\TelegraphChat;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use DefStudio\Telegraph\Exceptions\StorageException;

class ChoosePartStep implements StepInterface
{
    /**
     * @throws StorageException
     */
    public function ask(TelegraphChat $chat, bool $edit = false, int $messageId = null): void
    {
        $modelId = $chat->storage()->get('model-id');

        if (empty($modelId)) {
            $chat->message('⚠️ Avval model tanlanmagan.')->send();
            StepManager::goToStep($chat, ChooseModelStep::class);
            return;
        }

        $parts = Part::where('model_id', $modelId)->get();

        if ($parts->isEmpty()) {
            $chat->message("📭 Ushbu modelga hech qanday part biriktirilmagan.")->send();
            StepManager::goToStep($chat, ChooseModelStep::class);
            return;
        }

        $keyboard = Keyboard::make();
        foreach ($parts as $part) {
            $keyboard->buttons([
                Button::make("🔹 " . $part->name)->action("part-{$part->id}")
            ]);
        }

        $text = "<b>🧩 Qaysi qismni bajardingiz?</b>\nModelga tegishli qismlardan birini tanlang:";

        $chat->html($text)->keyboard($keyboard)->send();
    }

    public function handleMessage(TelegraphChat $chat, Message $message): void
    {
        $chat->message("❗ Iltimos, tugmalar orqali qismni tanlang.")->send();
        $this->ask($chat);
    }

    public function handleCallback(TelegraphChat $chat, string $data, $callbackQuery): void
    {
        $decoded = json_decode($data, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            $chat->message('❌ Callback noto‘g‘ri formatda keldi!')->send();
            return;
        }

        $action = $decoded['action'] ?? '';

        if (!str_starts_with($action, 'part-')) {
            $chat->message('❌ Noto‘g‘ri amal!')->send();
            return;
        }

        $partId = (int)str_replace('part-', '', $action);
        $part = Part::find($partId);

        if (!$part) {
            $chat->message("❌ Tanlangan part topilmadi.")->send();
            return;
        }
        $messageId = $callbackQuery->message()->id() ?? null;
        $chat->deleteMessage($messageId)->send();

        $chat->storage()->set('part-id', $part->id);
        $chat->html("✅ Tanlangan qism: <b>{$part->name}</b>")->send();

        StepManager::next($chat);
    }
}
