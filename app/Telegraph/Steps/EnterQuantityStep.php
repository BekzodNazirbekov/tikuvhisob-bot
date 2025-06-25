<?php

namespace App\Telegraph\Steps;

use App\Models\Part;
use App\Models\WorkEntry;
use Carbon\Carbon;
use App\Telegraph\Contracts\StepInterface;
use App\Telegraph\Managers\StateManager;
use App\Telegraph\State\StartState;
use DefStudio\Telegraph\DTO\Message;
use DefStudio\Telegraph\Models\TelegraphChat;
use DefStudio\Telegraph\Exceptions\StorageException;

class EnterQuantityStep implements StepInterface
{
    public function ask(TelegraphChat $chat, bool $edit = false, int $messageId = null): void
    {
        $chat->html("✏️ <b>Nechta dona tikdingiz?</b>\n\nFaqat son kiriting:")->send();
    }

    /**
     * @throws StorageException
     */
    public function handleMessage(TelegraphChat $chat, Message $message): void
    {
        $text = trim($message->text());

        if (!is_numeric($text) || (int)$text <= 0) {
            $chat->message("🚫 Noto‘g‘ri qiymat. Iltimos, faqat musbat son kiriting.")->send();
            return;
        }

        $quantity = (int)$text;
        $partId = $chat->storage()->get('part-id');
        $userId = $chat->user?->id;

        if (!$partId || !$userId) {
            $chat->message("⚠️ Xatolik! Part yoki foydalanuvchi aniqlanmadi.")->send();
            StateManager::setState($chat, StartState::class);
            return;
        }

        $part = Part::find($partId);
        if (!$part) {
            $chat->message("❌ Part topilmadi.")->send();
            StateManager::setState($chat, StartState::class);
            return;
        }

        WorkEntry::create([
            'user_id' => $userId,
            'part_id' => $partId,
            'quantity' => $quantity,
            'date' => Carbon::today(),
        ]);

        $sum = number_format($part->price * $quantity, 0, '.', ' ');

        $chat->html("✅ <b>Yozuv saqlandi!</b>\n\n🧩 Qism: <b>{$part->name}</b>\n🔢 Miqdor: <b>{$quantity} dona</b>\n💰 Umumiy: <b>{$sum} so'm</b>")->send();

        StateManager::setState($chat, StartState::class);
    }

    public function handleCallback(TelegraphChat $chat, string $data, $callbackQuery): void
    {
        // Ushbu step callback kutmaydi.
    }
}
