<?php

declare(strict_types=1);

use Capell\LiveChat\Actions\ApplyLiveChatCorsHeadersAction;
use Capell\LiveChat\Actions\GuardLiveChatInstallationOriginAction;
use Capell\LiveChat\Actions\ResolveLiveChatInstallationAction;
use Capell\LiveChat\Http\Controllers\LiveChatWidgetScriptController;
use Capell\LiveChat\Http\Controllers\RequestLiveChatHandoffController;
use Capell\LiveChat\Http\Controllers\ShowLiveChatWidgetController;
use Capell\LiveChat\Http\Controllers\StoreLiveChatConversationController;
use Capell\LiveChat\Http\Controllers\StoreLiveChatMessageController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

$configuredPrefix = config('capell-live-chat.public_path_prefix', 'live-chat');
$prefix = trim(is_string($configuredPrefix) ? $configuredPrefix : 'live-chat', '/');

Route::middleware(['web'])
    ->prefix($prefix)
    ->as('capell-live-chat.')
    ->group(function (): void {
        Route::get('/widget', ShowLiveChatWidgetController::class)->name('widget');
        Route::get('/widget.js', LiveChatWidgetScriptController::class)->name('widget.script');
        $preflight = function (Request $request): Response {
            $publicKey = $request->route('public_key');

            abort_unless(is_string($publicKey) && trim($publicKey) !== '', 404);

            $installation = ResolveLiveChatInstallationAction::run($publicKey) ?? abort(404);
            $origin = GuardLiveChatInstallationOriginAction::run($installation, $request);

            return app(ApplyLiveChatCorsHeadersAction::class)->preflight($origin);
        };

        Route::options('/api/{public_key}/conversations', $preflight)
            ->middleware('throttle:capell-live-chat')
            ->withoutMiddleware([VerifyCsrfToken::class])
            ->name('api.preflight');
        Route::options('/api/{public_key}/conversations/{conversation}/messages', $preflight)
            ->withoutMiddleware([VerifyCsrfToken::class]);
        Route::options('/api/{public_key}/conversations/{conversation}/handoff', $preflight)
            ->withoutMiddleware([VerifyCsrfToken::class]);
        Route::post('/api/{public_key}/conversations', StoreLiveChatConversationController::class)
            ->middleware('throttle:capell-live-chat')
            ->withoutMiddleware([VerifyCsrfToken::class])
            ->name('api.conversations.store');
        Route::post('/api/{public_key}/conversations/{conversation}/messages', StoreLiveChatMessageController::class)
            ->middleware('throttle:capell-live-chat')
            ->withoutMiddleware([VerifyCsrfToken::class])
            ->name('api.messages.store');
        Route::post('/api/{public_key}/conversations/{conversation}/handoff', RequestLiveChatHandoffController::class)
            ->middleware('throttle:capell-live-chat-handoff')
            ->withoutMiddleware([VerifyCsrfToken::class])
            ->name('api.handoff.store');
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
