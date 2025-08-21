<?php

use App\Http\Controllers\Web\WorkflowController;
use Illuminate\Support\Facades\Route;

Route::name('workflows.')->group(function () {
    Route::get('/workflows', [WorkflowController::class, 'index'])->name('index');
    Route::get('/workflows/{workflow}', [WorkflowController::class, 'show'])->name('show');
});

Route::get('/', fn() => redirect()->route('workflows.index'));
