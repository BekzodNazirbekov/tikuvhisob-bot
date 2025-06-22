<?php

namespace App\Telegraph\Steps\Register;

use App\Telegraph\State\StartState;
use DefStudio\Telegraph\DTO\Message;
use App\Telegraph\Managers\StepManager;
use App\Telegraph\Contracts\StepInterface;
use DefStudio\Telegraph\Models\TelegraphChat;
use DefStudio\Telegraph\Exceptions\StorageException;

class AskPassStep implements StepInterface
{
    public function ask(TelegraphChat $chat, bool $edit = false, int $messageId = null): void
    {
        $chat->html("Iltimos sizga berilgan parolni kiriting")->send();
    }

    /**
     * @throws StorageException
     */
    public function handleMessage(TelegraphChat $chat, Message $message): void
    {
        $pass = $message->text();
        $phone = $chat->storage()->get('phone');

        if (!$phone) {
            $chat->message("❗ Avval telefon raqamni kiriting.")->send();
            return;
        }

        $user = \App\Models\User::where('phone', $phone)->first();

        if (!$user) {
            $chat->message("❌ Ushbu telefon raqamga tegishli foydalanuvchi topilmadi.")->send();
            return;
        }

        if (!\Illuminate\Support\Facades\Hash::check($pass, $user->password)) {
            $chat->message("🔐 Parol noto‘g‘ri. Iltimos, qaytadan urinib ko‘ring.")->send();
            return;
        }

        // ✅ Muvaffaqiyatli login
        $chat->message("👋 Xush kelibsiz, {$user->name}!")->send();

        // Foydalanuvchini chatga biriktiramiz (agar kerak bo‘lsa)
        $chat->update(['user_id' => $user->id]);

        // Istalgan state yoki keyingi stepga o'tkazish:
        \App\Telegraph\Managers\StateManager::setState($chat, StartState::class);
    }


    public function handleCallback(TelegraphChat $chat, string $data, $callbackQuery): void
    {
        $chat->message(false)->send();
    }
}
