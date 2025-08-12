<?php

use App\Http\Controllers\v1\ModelController;
use App\Http\Controllers\v1\PartsController;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('/models', [ModelController::class, 'index']);
    Route::get('/models/{id}', [ModelController::class, 'show']);
    Route::post('/models/', [ModelController::class, 'store']);
    Route::put('/models/{id}', [ModelController::class, 'update']);
    Route::delete('/models/{id}', [ModelController::class, 'destroy']);
});
