<?php

declare(strict_types=1);

use Capell\ThemeStudio\MinimalCurationFeed\Support\Screenshots\MinimalCurationFeedScreenshotRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get(
    '/screenshot-fixtures/theme-minimal-curation-feed/{screen}',
    static fn (string $screen, MinimalCurationFeedScreenshotRenderer $renderer): View => $renderer->render($screen),
);
