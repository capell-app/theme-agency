<?php

declare(strict_types=1);

use Capell\LiveChat\Http\Controllers\LiveChatWidgetScriptController;
use Capell\LiveChat\Http\Controllers\RequestLiveChatHandoffController;
use Capell\LiveChat\Http\Controllers\ShowLiveChatWidgetController;
use Capell\LiveChat\Http\Controllers\StoreLiveChatConversationController;
use Capell\LiveChat\Http\Controllers\StoreLiveChatMessageController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;

$prefix = trim((string) config('capell-live-chat.public_path_prefix', 'live-chat'), '/');

Route::middleware(['web'])
    ->prefix($prefix)
    ->as('capell-live-chat.')
    ->group(function (): void {
        Route::get('/widget', ShowLiveChatWidgetController::class)->name('widget');
        Route::get('/widget.js', LiveChatWidgetScriptController::class)->name('widget.script');
        Route::post('/conversations', StoreLiveChatConversationController::class)
            ->middleware('throttle:capell-live-chat')
            ->withoutMiddleware([VerifyCsrfToken::class])
            ->name('conversations.store');
        Route::post('/conversations/{conversation}/messages', StoreLiveChatMessageController::class)
            ->middleware('throttle:capell-live-chat')
            ->withoutMiddleware([VerifyCsrfToken::class])
            ->name('messages.store');
        Route::post('/conversations/{conversation}/handoff', RequestLiveChatHandoffController::class)
            ->middleware('throttle:capell-live-chat-handoff')
            ->withoutMiddleware([VerifyCsrfToken::class])
            ->name('handoff.store');
    });
