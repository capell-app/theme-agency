<?php

declare(strict_types=1);

use Capell\ThemeStudio\DarkProductSystem\Support\Screenshots\DarkProductSystemScreenshotRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get(
    '/screenshot-fixtures/theme-dark-product-system/{screen}',
    static fn (string $screen, DarkProductSystemScreenshotRenderer $renderer): View => $renderer->render($screen),
);
