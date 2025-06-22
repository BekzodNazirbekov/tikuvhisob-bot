<?php

namespace App\Telegraph\Steps\AddUser;

use App\Models\User;
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

        $chat->html('Ishchining telefon raqamini kiriting?')->send();

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

            $phone = User::query()->where('phone', $cleaned)->exists();

            if ($phone) {

                $chat->message('bunday raqamli ishchi bor')->send();
                return;

            }

            $chat->storage()->set('adduser_phone', $cleaned);
            StepManager::next($chat);

        } else {
            $chat
                ->html('raqam formati xato')
                ->send();
        }
    }

    public function handleCallback(TelegraphChat $chat, string $data, $callbackQuery): void
    {
        $chat->message(false)->send();
    }
}
