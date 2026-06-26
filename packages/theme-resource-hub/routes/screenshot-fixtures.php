<?php

declare(strict_types=1);

use Capell\ThemeStudio\ResourceHub\Support\Screenshots\ResourceHubScreenshotRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get(
    '/screenshot-fixtures/theme-resource-hub/{screen}',
    static fn (string $screen, ResourceHubScreenshotRenderer $renderer): View => $renderer->render($screen),
);
