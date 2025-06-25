<?php

namespace App\Telegraph\Steps\AddPart;

use App\Telegraph\Managers\StepManager;
use App\Telegraph\Contracts\StepInterface;
use DefStudio\Telegraph\DTO\Message;
use DefStudio\Telegraph\Models\TelegraphChat;
use DefStudio\Telegraph\Exceptions\StorageException;

class AskPartNameStep implements StepInterface
{
    public function ask(TelegraphChat $chat, bool $edit = false, int $messageId = null): void
    {
        $chat->html("🔤 Yangi qisim nomini kiriting:")->send();
    }

    /**
     * @throws StorageException
     */
    public function handleMessage(TelegraphChat $chat, Message $message): void
    {
        $name = trim($message->text());

        if (mb_strlen($name) < 2) {
            $chat->message("❌ Partiya nomi juda qisqa. Kamida 2 ta belgi bo‘lishi kerak.")->send();
            return;
        }

        // Saqlash
        $chat->storage()->set('add_part_name', $name);

        // Keyingi stepgа o‘tish
        StepManager::next($chat);
    }

    public function handleCallback(TelegraphChat $chat, string $data, $callbackQuery): void
    {
        // Callbacklar bu stepda ishlatilmaydi
        $chat->message("⛔ Iltimos, faqat matn yuboring.")->send();
    }
}
