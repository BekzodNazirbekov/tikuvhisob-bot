<?php

namespace App\Telegraph\Steps\Register;

use Illuminate\Support\Facades\App;
use DefStudio\Telegraph\DTO\Message;
use App\Telegraph\Managers\StepManager;
use App\Telegraph\Contracts\StepInterface;
use DefStudio\Telegraph\Models\TelegraphChat;
use DefStudio\Telegraph\Exceptions\StorageException;

class AskPhoneStep implements StepInterface
{
    /**
     * @throws StorageException
     */
    public function ask(TelegraphChat $chat, bool $edit = false, int $messageId = null): void
    {

        App::setLocale($chat->storage()->get('language'));

        $chat->html('telefon raqamingizni yuboring')->send();

    }

    /**
     * @throws StorageException
     */
    public function handleMessage(TelegraphChat $chat, Message $message): void
    {

        $contactPhone = $message->contact()?->phoneNumber();
        $input = $contactPhone ?: $message->text();
        $cleaned = preg_replace('/\D+/', '', $input);

        if (preg_match("/^998([1-9][0-9])\d{7}$/", $cleaned)) {

            $chat->storage()->set('phone', $cleaned);

            StepManager::next($chat);

        } else {

            $chat->html("format notog'ri")->send();

        }

    }

    public function handleCallback(TelegraphChat $chat, string $data, $callbackQuery): void
    {
        $chat->message(false)->send();
    }
}
