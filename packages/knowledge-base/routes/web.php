<?php

declare(strict_types=1);

use Capell\Frontend\Support\Routing\FrontendRouteMiddlewareRegistry;
use Capell\KnowledgeBase\Http\Controllers\ShowKnowledgeBaseAiOutputController;
use Capell\KnowledgeBase\Http\Controllers\ShowKnowledgeBaseArticleController;
use Capell\KnowledgeBase\Http\Controllers\ShowKnowledgeBaseIndexController;
use Capell\KnowledgeBase\Http\Controllers\StoreKnowledgeBaseArticleFeedbackController;
use Illuminate\Support\Facades\Route;

if (config('capell-knowledge-base.public_routes_enabled', true) === true) {
    $prefix = trim((string) config('capell-knowledge-base.public_path_prefix', 'docs'), '/');
    $middleware = ['web'];

    if (class_exists(FrontendRouteMiddlewareRegistry::class) && app()->bound(FrontendRouteMiddlewareRegistry::class)) {
        $middleware = resolve(FrontendRouteMiddlewareRegistry::class)->all();
    }

    Route::middleware($middleware)
        ->prefix($prefix)
        ->as('capell-knowledge-base.')
        ->group(function (): void {
            Route::get('/', ShowKnowledgeBaseIndexController::class)->name('index');
            Route::get('/llms.txt', ShowKnowledgeBaseAiOutputController::class)->name('ai-output');
            Route::get('/{collectionSlug}/{articleSlug}', ShowKnowledgeBaseArticleController::class)
                ->where([
                    'collectionSlug' => '[A-Za-z0-9\\-]+',
                    'articleSlug' => '[A-Za-z0-9\\-]+',
                ])
                ->name('article');
            Route::post('/{collectionSlug}/{articleSlug}/feedback', StoreKnowledgeBaseArticleFeedbackController::class)
                ->middleware('throttle:' . config('capell-knowledge-base.feedback.throttle', '30,1'))
                ->where([
                    'collectionSlug' => '[A-Za-z0-9\\-]+',
                    'articleSlug' => '[A-Za-z0-9\\-]+',
                ])
                ->name('article.feedback');
        });
}
