<?php

namespace App\Telegraph\Steps\AddPart;

use App\Models\Part;
use App\Telegraph\Managers\StepManager;
use App\Telegraph\State\StartState;
use DefStudio\Telegraph\DTO\Message;
use App\Telegraph\Managers\StateManager;
use App\Telegraph\Contracts\StepInterface;
use DefStudio\Telegraph\Models\TelegraphChat;
use DefStudio\Telegraph\Exceptions\StorageException;

class AskPartPriceStep implements StepInterface
{
    public function ask(TelegraphChat $chat, bool $edit = false, int $messageId = null): void
    {
        $chat->html("💰 qisim narxini kiriting:")->send();
    }

    /**
     * @throws StorageException
     */
    public function handleMessage(TelegraphChat $chat, Message $message): void
    {
        $price = (int)trim($message->text());

        if ($price <= 0) {
            $chat->message("❌ Narx noto‘g‘ri. Iltimos, musbat son kiriting.")->send();
            return;
        }

        $name = $chat->storage()->get('add_part_name');
        $model_id = $chat->storage()->get('add-part-model-id');

        if (!$name || !$model_id) {
            $chat->message("❗ Kerakli ma’lumotlar topilmadi. Iltimos, qaytadan urinib ko‘ring.")->send();
            StateManager::setState($chat, StartState::class);
            return;
        }


        // Saqlash
        $chat->storage()->set('add-part-price', $price);
        // Keyingi stepgа o‘tish
        StepManager::next($chat);
    }

    public function handleCallback(TelegraphChat $chat, string $data, $callbackQuery): void
    {
        // Bu stepda callback kerak emas
        $chat->message("⛔ Bu yerda faqat matn kiritish kerak.")->send();
    }
}
