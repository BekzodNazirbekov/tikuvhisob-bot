<?php

namespace App\Telegraph\Steps;

use App\Telegraph\Managers\StepManager;
use DefStudio\Telegraph\DTO\Message;
use App\Telegraph\Contracts\StepInterface;
use DefStudio\Telegraph\Exceptions\StorageException;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use DefStudio\Telegraph\Models\TelegraphBot;
use DefStudio\Telegraph\Models\TelegraphChat;
use App\Models\Model;

class ChooseModelStep implements StepInterface
{
    public function ask(TelegraphChat $chat, bool $edit = false, int $messageId = null): void
    {
        $keyboard = Keyboard::make();

        foreach (Model::all() as $model) {
            $keyboard->buttons([
                Button::make($model->name)->action('choose_model-' . $model->id)
            ]);
        }

        $chat->message('📦 Qaysi modelni tikdingiz?')->keyboard($keyboard)->send();
    }

    public function handleMessage(TelegraphChat $chat, Message $message): void
    {
        $chat->message("Iltimos, tugmalar orqali modelni tanlang.")->send();
        $this->ask($chat);
    }


    /**
     * @throws StorageException
     */
    public function handleCallback(TelegraphChat $chat, string $data, $callbackQuery): void
    {
        $decoded = json_decode($data, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            $chat->message('❌ Callback maʼlumot noto‘g‘ri!')->send();
            return;
        }

        $action = $decoded['action'] ?? '';

        if (!str_starts_with($action, 'choose_model-')) {
            $chat->message('❌ Noto‘g‘ri action!')->send();
            return;
        }

        $modelId = str_replace('choose_model-', '', $action);

        $model = \App\Models\Model::find($modelId);
        if (!$model) {
            $chat->message('❌ Model topilmadi!')->send();
            return;
        }

        $chat->storage()->set('model-id', $model->id);
        $chat->message("✅ Tanlangan model: {$model->name}")->send();

        \App\Telegraph\Managers\StepManager::next($chat);
    }

}
