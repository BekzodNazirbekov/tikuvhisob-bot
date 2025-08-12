<?php

use App\Http\Controllers\v1\WorksController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('/works', [WorksController::class, 'index']);
    Route::post('/works', [WorksController::class, 'store']);
    Route::get('/works/{id}', [WorksController::class, 'show']);
    Route::put('/works/{id}', [WorksController::class, 'update']);
    Route::delete('/works/{id}', [WorksController::class, 'destroy']);
});
