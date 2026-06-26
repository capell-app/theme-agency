<?php

declare(strict_types=1);

use Capell\ThemeStudio\ProductStudio\Support\Screenshots\ProductStudioScreenshotRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get(
    '/screenshot-fixtures/theme-product-studio/{screen}',
    static fn (string $screen, ProductStudioScreenshotRenderer $renderer): View => $renderer->render($screen),
);
