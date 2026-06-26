<?php

declare(strict_types=1);

use Capell\ThemeStudio\PackagingSupplier\Support\Screenshots\PackagingSupplierScreenshotRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get(
    '/screenshot-fixtures/theme-packaging-supplier/{screen}',
    static fn (string $screen, PackagingSupplierScreenshotRenderer $renderer): View => $renderer->render($screen),
);
