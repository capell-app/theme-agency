<?php

declare(strict_types=1);

use Capell\ThemeStudio\ConstructionTrades\Support\Screenshots\ConstructionTradesScreenshotRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get(
    '/screenshot-fixtures/theme-construction-trades/{screen}',
    static fn (string $screen, ConstructionTradesScreenshotRenderer $renderer): View => $renderer->render($screen),
);
