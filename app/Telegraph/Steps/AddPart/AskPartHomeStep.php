<?php

namespace App\Telegraph\Steps\AddPart;

use App\Models\Part;
use App\Telegraph\State\StartState;
use DefStudio\Telegraph\DTO\Message;
use App\Telegraph\Managers\StateManager;
use App\Telegraph\Contracts\StepInterface;
use DefStudio\Telegraph\Models\TelegraphChat;
use DefStudio\Telegraph\Exceptions\StorageException;

class AskPartCountStep implements StepInterface
{
    public function ask(TelegraphChat $chat, bool $edit = false, int $messageId = null): void
    {
        $chat->html("🔢 Qisim soni kiriting?")->send();
    }

    /**
     * @throws StorageException
     */
    public function handleMessage(TelegraphChat $chat, Message $message): void
    {
        $count = (int)trim($message->text());

        if ($count <= 0) {
            $chat->message("❌ Son noto‘g‘ri. Iltimos, musbat son kiriting.")->send();
            return;
        }

        $name = $chat->storage()->get('add_part_name');
        $model_id = $chat->storage()->get('add-part-model-id');
        $price = $chat->storage()->get('add-part-price');

        if (!$name || !$model_id) {
            $chat->message("❗ Kerakli ma’lumotlar topilmadi. Iltimos, qaytadan urinib ko‘ring.")->send();
            StateManager::setState($chat, StartState::class);
            return;
        }

        $part = Part::create([
            'name' => $name,
            'price' => $price,
            'model_id' => $model_id,
            'status' => 'active', // Default status
            'count' => $count, // Set the count
        ]);

        $formattedPrice = number_format($price, 0, '.', ' ');

        $text = "✅ Yangi partiya muvaffaqiyatli yaratildi:\n\n";
        $text .= "📦 Nomi: <b>{$part->name}</b>\n";
        $text .= "💰 Narxi: <b>{$formattedPrice} so‘m</b>\n";
        $text .= "🧵 Model ID: <b>{$part->model_id}</b>\n";
        $text .= "🔢 Qisim soni: <b>{$part->count}</b>";

        $chat->html($text)->send();

        // Toza qaytish
        StateManager::setState($chat, StartState::class);
    }

    public function handleCallback(TelegraphChat $chat, string $data, $callbackQuery): void
    {
        // Bu stepda callback kerak emas
        $chat->message("⛔ Bu yerda faqat matn kiritish kerak.")->send();
    }
}
