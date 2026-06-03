<?php

declare(strict_types=1);

use Capell\AgentDelivery\Http\Controllers\PageChunksController;
use Capell\AgentDelivery\Http\Controllers\PageIndexController;
use Capell\AgentDelivery\Http\Controllers\PageManifestController;
use Illuminate\Support\Facades\Route;

$configuredMiddleware = static function (mixed $middleware): array {
    if (in_array($middleware, [null, false, ''], true)) {
        return [];
    }

    if (is_string($middleware)) {
        return [$middleware];
    }

    if (! is_array($middleware)) {
        return [];
    }

    return array_values(array_filter(
        $middleware,
        static fn (mixed $middlewareName): bool => is_string($middlewareName) && $middlewareName !== '',
    ));
};

$apiMiddleware = [
    ...$configuredMiddleware(config('capell-agent-delivery.middleware', ['api'])),
    ...$configuredMiddleware(config('capell-agent-delivery.public_pages.auth_middleware')),
    ...$configuredMiddleware(config('capell-agent-delivery.public_pages.rate_limit_middleware')),
    ...$configuredMiddleware(config('capell-agent-delivery.public_pages.middleware', [])),
];

Route::middleware($apiMiddleware)
    ->prefix('api/capell/agent/v1')
    ->group(function (): void {
        Route::get('pages', PageIndexController::class)
            ->name('capell-agent-delivery.pages.index');

        Route::get('pages/manifest', PageManifestController::class)
            ->name('capell-agent-delivery.pages.manifest');

        Route::get('pages/chunks', PageChunksController::class)
            ->name('capell-agent-delivery.pages.chunks');
    });
