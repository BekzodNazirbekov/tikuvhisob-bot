<?php

namespace App\Telegraph\Steps;

use App\Models\Part;
use DefStudio\Telegraph\DTO\Message;
use App\Telegraph\Managers\StepManager;
use DefStudio\Telegraph\Keyboard\Button;
use App\Telegraph\Contracts\StepInterface;
use DefStudio\Telegraph\Keyboard\Keyboard;
use DefStudio\Telegraph\Models\TelegraphChat;
use DefStudio\Telegraph\Exceptions\StorageException;

class ChoosePartStep implements StepInterface
{
    /**
     * @throws StorageException
     */
    public function ask(TelegraphChat $chat, bool $edit = false, int $messageId = null): void
    {

        $model_id = $chat->storage()->get('model-id');
        if (empty($model_id)) {
            $chat->message('Model id mavjut emas');
            StepManager::goToStep($chat, ChooseModelStep::class);
        }

        $keyboard = Keyboard::make();
        foreach (Part::where('model_id', $model_id)->get() as $part) {
            $keyboard->buttons(
                [
                    Button::make($part->name)->action('part-' . $part->id)->param('part-', $part->id)
                ]
            );
        }

        $chat->message('Select part:')->keyboard($keyboard)->send();
    }

    public function handleMessage(TelegraphChat $chat, Message $message): void
    {
        $chat->message("Iltimos, tugmalar orqali partni tanlang.")->send();
        $this->ask($chat);
    }

    public function handleCallback(TelegraphChat $chat, string $data, $callbackQuery): void
    {
        $decoded = json_decode($data, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            $chat->message('❌ Callback maʼlumot noto‘g‘ri!')->send();
            return;
        }

        $action = $decoded['action'] ?? '';

        if (!str_starts_with($action, 'part-')) {
            $chat->message('❌ Noto‘g‘ri action!')->send();
            return;
        }

        $partId = str_replace('part-', '', $action);

        $part = \App\Models\Part::find($partId);
        if (!$part) {
            $chat->message('❌ Model topilmadi!')->send();
            return;
        }

        $chat->storage()->set('part-id', $part->id);
        $chat->message("✅ Tanlangan part: {$part->name}")->send();

        \App\Telegraph\Managers\StepManager::next($chat);
    }
}
