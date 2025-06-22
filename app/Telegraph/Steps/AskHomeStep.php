<?php

namespace App\Telegraph\Steps;

use App\Telegraph\Managers\StateManager;
use App\Telegraph\State\AddUserState;
use DefStudio\Telegraph\DTO\Message;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use App\Telegraph\Contracts\StepInterface;
use DefStudio\Telegraph\Models\TelegraphChat;
use DefStudio\Telegraph\Exceptions\StorageException;

class AskHomeStep implements StepInterface
{
    public function ask(TelegraphChat $chat, bool $edit = false, int $messageId = null): void
    {
        $keyboard = Keyboard::make()->row([
            Button::make("Qo'shish")->action('plus'),
            Button::make("Mening hisobim")->action('balance'),
        ])->row([
            Button::make('⚙️ Sozlamalar')->action('menu')->param('action', 'settings'),
        ]);

        if ($chat->user->role == 'admin') {
            $keyboard->row([
                Button::make("Add User")->action('adduser'),
                Button::make("Add model")->action('balance'),
            ]);
        }

        $text = 'Quyidagilardan birini tanlang';

        if ($edit && $messageId) {
            $chat->edit($messageId)->message($text)->keyboard($keyboard)->send();
        } else {
            $chat->html($text)->keyboard($keyboard)->send();
        }
    }

    public function handleMessage(TelegraphChat $chat, Message $message): void
    {
        $chat->message('Quyidagilaridan birini tanlang?')->send();
        $this->ask($chat);
    }

    /**
     * @throws StorageException
     */
    public
    function handleCallback(TelegraphChat $chat, string $data, $callbackQuery): void
    {
        $decoded = json_decode($data, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            $chat->message('❌ Callback maʼlumot noto‘g‘ri!')->send();
            return;
        }

        $messageId = $callbackQuery->message()->id() ?? null;

        switch ($decoded['action']) {
            case 'adduser':
                {
                    StateManager::setState($chat, AddUserState::class);
                }
                break;
            case 'cancel':
                {
                    $chat->deleteMessage($messageId)->send();
                }
                break;
            case "back":
                {
                    $this->ask($chat, true, $messageId);
                }
                break;
            default:
                {
                    $chat->message('ssad')->send();
                }
                break;
        }

    }
}
