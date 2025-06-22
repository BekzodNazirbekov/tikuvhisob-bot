<?php

namespace App\Telegraph;

use Throwable;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;
use Illuminate\Support\Facades\App;
use App\Telegraph\Managers\StateManager;
use DefStudio\Telegraph\Handlers\WebhookHandler;
use DefStudio\Telegraph\Exceptions\StorageException;
use DefStudio\Telegraph\Exceptions\TelegramWebhookException;

class WorkBotHandler extends WebhookHandler
{
    /**
     * @throws StorageException
     */
    protected function handleCommand(Stringable $text): void
    {
        App::setLocale($this->chat->storage()->get('language') ?? 'uz');

        $chatType = $this->message->chat()->type() ?? null;

        if ($chatType !== 'private') {
            logger()->info("Message ignored: non-private chat type [$chatType]");
            return;
        }

        $command = Str::of($text)->before(' ')->ltrim('/')->camel()->ucfirst(); // Masalan: /start => Start


        $class = "App\\Telegraph\\Commands\\{$command}Command";

        if (class_exists($class)) {
            app($class)($text,
                $this->bot,
                $this->chat,
                $this->message); // yoki ->__invoke($text, $this->chat, $this->bot) agar kerak bo'lsa
        } else {
            $this->reply("{} Noma'lum buyruq: /{$command}" . Str::of($this->bot->name)->studly());
        }
    }


    /**
     * @throws StorageException
     */
    protected function handleChatMessage(Stringable $text): void
    {
        App::setLocale($this->chat->storage()->get('language') ?? 'uz');

        StateManager::handleMessage($this->chat, $this->message);
    }

    /**
     * @throws TelegramWebhookException
     * @throws StorageException
     */
    protected function handleCallbackQuery(): void
    {
        $this->extractCallbackQueryData();

        App::setLocale($this->chat->storage()->get('language'));

        StateManager::handleCallback($this->chat, $this->callbackQuery->data(), $this->callbackQuery);
    }

    /**
     * @throws StorageException
     */
    /**
     * @throws StorageException
     */
    protected function onFailure(Throwable $throwable): void
    {

        App::setLocale($this->chat->storage()->get('language') ?? 'uz');

        $this->chat->message($throwable->getMessage())->send();

    }
}
