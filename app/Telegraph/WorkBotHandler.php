<?php

namespace App\Telegraph;

use DefStudio\Telegraph\Handlers\WebhookHandler;
use App\Telegraph\Steps\ChooseModelStep;

class WorkBotHandler extends WebhookHandler
{
    public function start(): void
    {
        $this->chat->message('Welcome to the work bot')->send();
    }

    public function report(): void
    {
        $this->nextStep(new ChooseModelStep());
    }
}
