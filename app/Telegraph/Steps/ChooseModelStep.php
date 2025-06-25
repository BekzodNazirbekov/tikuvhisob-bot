<?php

namespace App\Telegraph\Steps;

use App\Models\Model;
use App\Telegraph\Managers\StepManager;
use DefStudio\Telegraph\DTO\Message;
use App\Telegraph\Contracts\StepInterface;
use DefStudio\Telegraph\Models\TelegraphChat;
use DefStudio\Telegraph\Exceptions\StorageException;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;

class ChooseModelStep implements StepInterface
{
    public function ask(TelegraphChat $chat, bool $edit = false, int $messageId = null): void
    {
        $models = Model::all();

        if ($models->isEmpty()) {
            $chat->message("📦 Hech qanday model topilmadi. Iltimos, avval model qo‘shing.")->send();
            return;
        }

        $keyboard = Keyboard::make();
        foreach ($models as $model) {
            $keyboard->buttons([
                Button::make("📌 " . $model->name)->action("choose_model-{$model->id}")
            ]);
        }

        $text = "<b>📦 Qaysi modelni tikdingiz?</b>\nIltimos, quyidagi ro‘yxatdan tanlang:";

        $chat->html($text)->keyboard($keyboard)->send();
    }

    public function handleMessage(TelegraphChat $chat, Message $message): void
    {
        $chat->message("❗ Iltimos, modelni <b>tugmalar</b> orqali tanlang.")->send();
        $this->ask($chat);
    }

    /**
     * @throws StorageException
     */
    public function handleCallback(TelegraphChat $chat, string $data, $callbackQuery): void
    {
        $decoded = json_decode($data, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            $chat->message("❌ Callback maʼlumot noto‘g‘ri!")->send();
            return;
        }

        $action = $decoded['action'] ?? '';

        if (!str_starts_with($action, 'choose_model-')) {
            $chat->message("❌ Noto‘g‘ri amal tanlandi!")->send();
            return;
        }

        $modelId = (int)str_replace('choose_model-', '', $action);
        $model = Model::find($modelId);

        if (!$model) {
            $chat->message("❌ Tanlangan model topilmadi!")->send();
            return;
        }
        $messageId = $callbackQuery->message()->id() ?? null;
        $chat->deleteMessage($messageId)->send();

        $chat->storage()->set('model-id', $model->id);
        $chat->html("✅ Siz tanlagan model: <b>{$model->name}</b>")->send();

        StepManager::next($chat);
    }
}
