<?php

declare(strict_types=1);

use Awcodes\Mason\Http\Controllers\MasonController;
use Illuminate\Support\Facades\Route;

$authMiddleware = config('mason.guard')
    ? 'auth:' . config('mason.guard')
    : 'auth';

Route::middleware(['web', $authMiddleware])->group(function () {
    Route::post('/mason/preview', [MasonController::class, 'preview'])
        ->name('mason.preview');

    Route::post('/mason/entry', [MasonController::class, 'entry'])
        ->name('mason.entry');
});
