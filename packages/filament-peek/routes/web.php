<?php

declare(strict_types=1);

use Capell\FilamentPeek\Http\Controllers\PagePreviewController;
use Illuminate\Support\Facades\Route;

Route::middleware(config('capell-filament-peek.preview.middleware', ['web', 'signed']))
    ->prefix((string) config('capell-filament-peek.preview.route_prefix', 'capell-filament-peek'))
    ->name('capell-filament-peek.')
    ->group(function (): void {
        Route::get('/preview/{token}', PagePreviewController::class)
            ->name('preview');
    });
