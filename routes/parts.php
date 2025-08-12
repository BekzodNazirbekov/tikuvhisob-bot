<?php

use App\Http\Controllers\v1\PartsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('/parts', [PartsController::class, 'index']);
    Route::post('/parts', [PartsController::class, 'store']);
    Route::get('/parts/{id}', [PartsController::class, 'show']);
    Route::put('/parts/{id}', [PartsController::class, 'update']);
    Route::delete('/parts/{id}', [PartsController::class, 'destroy']);
});
