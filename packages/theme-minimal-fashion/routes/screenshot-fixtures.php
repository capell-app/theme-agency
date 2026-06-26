<?php

declare(strict_types=1);

use Capell\ThemeStudio\MinimalFashion\Support\Screenshots\MinimalFashionScreenshotRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get(
    '/screenshot-fixtures/theme-minimal-fashion/{screen}',
    static fn (string $screen, MinimalFashionScreenshotRenderer $renderer): View => $renderer->render($screen),
);
