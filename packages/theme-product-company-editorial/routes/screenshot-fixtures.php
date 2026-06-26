<?php

declare(strict_types=1);

use Capell\ThemeStudio\ProductCompanyEditorial\Support\Screenshots\ProductCompanyEditorialScreenshotRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get(
    '/screenshot-fixtures/theme-product-company-editorial/{screen}',
    static fn (string $screen, ProductCompanyEditorialScreenshotRenderer $renderer): View => $renderer->render($screen),
);
