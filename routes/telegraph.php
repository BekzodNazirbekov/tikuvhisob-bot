<?php

use Illuminate\Support\Facades\Route;
use App\Telegraph\WorkBotHandler;

Route::telegraph('bot', WorkBotHandler::class);
