<?php

declare(strict_types=1);

use Capell\ThemeStudio\Healthcare\Support\Screenshots\HealthcareScreenshotRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get(
    '/screenshot-fixtures/theme-healthcare/{screen}',
    static fn (string $screen, HealthcareScreenshotRenderer $renderer): View => $renderer->render($screen),
);
