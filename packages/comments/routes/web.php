<?php

declare(strict_types=1);

use Capell\Comments\Http\Controllers\RenderCommentThreadController;
use Capell\Comments\Http\Controllers\VerifyCommentAuthorEmailController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'throttle:comments-verification'])
    ->prefix(config('capell-comments.route_prefix', 'capell/comments'))
    ->name('capell-comments.')
    ->group(function (): void {
        Route::get('thread', RenderCommentThreadController::class)->name('thread');
        Route::get('verify/{token}', [VerifyCommentAuthorEmailController::class, 'show'])->name('verify');
        Route::post('verify/{token}', [VerifyCommentAuthorEmailController::class, 'store'])->name('verify.store');
    });
