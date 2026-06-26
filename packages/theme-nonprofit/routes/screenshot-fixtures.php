<?php

declare(strict_types=1);

use Capell\ThemeStudio\Nonprofit\Support\Screenshots\NonprofitScreenshotRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get(
    '/screenshot-fixtures/theme-nonprofit/{screen}',
    static fn (string $screen, NonprofitScreenshotRenderer $renderer): View => $renderer->render($screen),
);
