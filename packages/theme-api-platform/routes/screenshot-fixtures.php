<?php

declare(strict_types=1);

use Capell\ThemeStudio\ApiPlatform\Support\Screenshots\ApiPlatformScreenshotRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get(
    '/screenshot-fixtures/theme-api-platform/{screen}',
    static fn (string $screen, ApiPlatformScreenshotRenderer $renderer): View => $renderer->render($screen),
);
