<?php

declare(strict_types=1);

use Capell\ThemeStudio\LocalServices\Support\Screenshots\LocalServicesScreenshotRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get(
    '/screenshot-fixtures/theme-local-services/{screen}',
    static fn (string $screen, LocalServicesScreenshotRenderer $renderer): View => $renderer->render($screen),
);
