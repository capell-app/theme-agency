<?php

declare(strict_types=1);

use Capell\ThemeStudio\Knowledge\Support\Screenshots\KnowledgeScreenshotRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get(
    '/screenshot-fixtures/theme-knowledge/{screen}',
    static fn (string $screen, KnowledgeScreenshotRenderer $renderer): View => $renderer->render($screen),
);
