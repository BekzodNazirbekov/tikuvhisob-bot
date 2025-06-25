<?php

namespace App\Telegraph\Steps\AddModel;

use App\Models\Model;
use App\Telegraph\Managers\StateManager;
use App\Telegraph\State\StartState;
use DefStudio\Telegraph\DTO\Message;
use App\Telegraph\Managers\StepManager;
use App\Telegraph\Contracts\StepInterface;
use DefStudio\Telegraph\Models\TelegraphChat;
use DefStudio\Telegraph\Exceptions\StorageException;

class AskModelNameStep implements StepInterface
{
    public function ask(TelegraphChat $chat, bool $edit = false, int $messageId = null): void
    {
        $chat->html("model nomini kiriting!")->send();
    }

    /**
     * @throws StorageException
     */
    public function handleMessage(TelegraphChat $chat, Message $message): void
    {
        $name = $message->text();
        $chat->storage()->set('add_model_name', $name);

        Model::query()->create([
            'name' => $name
        ]);

        $chat->message("Model yaratildi model nomi {$name}")->send();

        StateManager::setState($chat, StartState::class);
    }

    public function handleCallback(TelegraphChat $chat, string $data, $callbackQuery): void
    {
        $chat->message(false)->send();
    }
}
