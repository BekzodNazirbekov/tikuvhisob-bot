<?php

namespace App\Telegraph\Steps\AddUser;

use App\Models\User;
use App\Telegraph\Managers\StateManager;
use App\Telegraph\State\StartState;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use Illuminate\Support\Facades\App;
use DefStudio\Telegraph\DTO\Message;
use App\Telegraph\Managers\StepManager;
use App\Telegraph\Contracts\StepInterface;
use DefStudio\Telegraph\Models\TelegraphChat;
use DefStudio\Telegraph\Exceptions\StorageException;

class AskAddUserStep implements StepInterface
{
    /**
     * @throws StorageException
     */
    public function ask(TelegraphChat $chat, bool $edit = false, int $messageId = null): void
    {

        App::setLocale($chat->storage()->get('language'));

        $keyboard = Keyboard::make()->buttons([

            Button::make('admin')->action('admin'),
            Button::make('ishchi')->action('worker')
        ]);

        $chat->html('Ishchining telefon raqamini kiriting?')->keyboard($keyboard)->send();

    }

    /**
     * @throws StorageException
     */
    public function handleMessage(TelegraphChat $chat, Message $message): void
    {
        $chat->reply($message->id())->message('bu user admin yoki ishchimi?')->send();
        $this->ask($chat);
    }

    /**
     * @throws StorageException
     */
    public function handleCallback(TelegraphChat $chat, string $data, $callbackQuery): void
    {
        $decoded = json_decode($data, true);

        if (!is_array($decoded) || !isset($decoded['action'])) {
            $chat->message(__('invalid_data'))->send();
            return;
        }

        $messageId = $callbackQuery->message()->id();

        $round = rand(111111, 999999);
        $role = 'ishchi';
        if ($decoded['action'] == 'admin') {
            User::query()->create([
                'name' => (string)$chat->storage()->get('adduser_name'),
                'phone' => (string)$chat->storage()->get('adduser_phone'),
                'role' => 'admin',
                'password' => $round
            ]);
            $role = 'admin';
        } else {
            User::query()->create([
                'name' => (string)$chat->storage()->get('adduser_name'),
                'phone' => (string)$chat->storage()->get('adduser_phone'),
                'password' => $round
            ]);
        }


        $text = "Add user\n";
        $text .= "name: {$chat->storage()->get('adduser_name')}\n";
        $text .= "phone: {$chat->storage()->get('adduser_phone')}\n";
        $text .= "password: {$round}\n";
        $text .= "role: {$role}";

        $chat->edit($messageId)->message($text)->send();

        StateManager::setState($chat, StartState::class);
    }
}
