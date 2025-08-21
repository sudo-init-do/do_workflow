<?php

use App\Http\Controllers\Api\TriggerController;
use Illuminate\Support\Facades\Route;

Route::post('/trigger/{secret}', [TriggerController::class, 'triggerBySecret']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/workflows/{workflow}/trigger', [TriggerController::class, 'triggerAuth']);
});
