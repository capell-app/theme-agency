<?php

declare(strict_types=1);

use Capell\KnowledgeBase\Http\Controllers\ShowKnowledgeBaseArticleController;
use Capell\KnowledgeBase\Http\Controllers\ShowKnowledgeBaseIndexController;
use Capell\KnowledgeBase\Http\Controllers\StoreKnowledgeBaseArticleFeedbackController;
use Illuminate\Support\Facades\Route;

if (config('capell-knowledge-base.public_routes_enabled', true) === true) {
    $prefix = trim((string) config('capell-knowledge-base.public_path_prefix', 'docs'), '/');

    Route::middleware(['web'])
        ->prefix($prefix)
        ->as('capell-knowledge-base.')
        ->group(function (): void {
            Route::get('/', ShowKnowledgeBaseIndexController::class)->name('index');
            Route::get('/{collectionSlug}/{articleSlug}', ShowKnowledgeBaseArticleController::class)
                ->where([
                    'collectionSlug' => '[A-Za-z0-9\\-]+',
                    'articleSlug' => '[A-Za-z0-9\\-]+',
                ])
                ->name('article');
            Route::post('/{collectionSlug}/{articleSlug}/feedback', StoreKnowledgeBaseArticleFeedbackController::class)
                ->where([
                    'collectionSlug' => '[A-Za-z0-9\\-]+',
                    'articleSlug' => '[A-Za-z0-9\\-]+',
                ])
                ->name('article.feedback');
        });
}
