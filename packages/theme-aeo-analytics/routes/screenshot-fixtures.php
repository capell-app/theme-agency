<?php

declare(strict_types=1);

use Capell\ThemeStudio\AeoAnalytics\Support\Screenshots\AeoAnalyticsScreenshotRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get(
    '/screenshot-fixtures/theme-aeo-analytics/{screen}',
    static fn (string $screen, AeoAnalyticsScreenshotRenderer $renderer): View => $renderer->render($screen),
);
