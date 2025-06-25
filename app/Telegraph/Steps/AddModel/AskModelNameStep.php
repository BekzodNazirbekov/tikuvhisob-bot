<?php

namespace App\Telegraph\Steps\AddModel;

use App\Models\Model;
use App\Telegraph\State\StartState;
use DefStudio\Telegraph\DTO\Message;
use App\Telegraph\Managers\StateManager;
use App\Telegraph\Contracts\StepInterface;
use DefStudio\Telegraph\Models\TelegraphChat;
use DefStudio\Telegraph\Exceptions\StorageException;

class AskModelNameStep implements StepInterface
{
    public function ask(TelegraphChat $chat, bool $edit = false, int $messageId = null): void
    {
        $chat->html("🧵 Iltimos, yangi model nomini kiriting:")->send();
    }

    /**
     * @throws StorageException
     */
    public function handleMessage(TelegraphChat $chat, Message $message): void
    {
        $name = trim($message->text());

        if (mb_strlen($name) < 2) {
            $chat->message("❗ Model nomi kamida 2 ta belgidan iborat bo‘lishi kerak.")->send();
            return;
        }

        // Avval saqlangan bo‘lsa, yaratmaslik
        $exists = Model::where('name', $name)->exists();
        if ($exists) {
            $chat->message("⚠️ Bunday nomli model allaqachon mavjud. Iltimos, boshqa nom kiriting.")->send();
            return;
        }

        // Modelni yaratish
        $model = Model::create(['name' => $name]);

        // Javob yuborish
        $chat->message("✅ Model yaratildi!\n📦 Nomi: <b>{$model->name}</b>")->send();

        // Boshlang‘ich statega qaytish
        StateManager::setState($chat, StartState::class);
    }

    public function handleCallback(TelegraphChat $chat, string $data, $callbackQuery): void
    {
        $chat->message("⛔ Faqat matn yuboring.")->send();
    }
}
