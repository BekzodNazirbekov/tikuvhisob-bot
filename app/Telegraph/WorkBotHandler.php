<?php

namespace App\Telegraph;

use DefStudio\Telegraph\Handlers\WebhookHandler;
use DefStudio\Telegraph\Keyboard\ReplyButton;
use DefStudio\Telegraph\Keyboard\ReplyKeyboard;

class WorkBotHandler extends WebhookHandler
{
    public function start(): void
    {

        $keyboard = ReplyKeyboard::make()->row([
            ReplyButton::make('WebApp')->webApp('https://web.smartseh.uz')
        ]);

        $this->chat->message('Starting work bot')->replyKeyboard($keyboard)->send();
    }
}
