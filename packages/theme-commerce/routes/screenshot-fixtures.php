<?php

declare(strict_types=1);

use Capell\ThemeStudio\Commerce\Support\Screenshots\CommerceScreenshotRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get(
    '/screenshot-fixtures/theme-commerce/{screen}',
    static fn (string $screen, CommerceScreenshotRenderer $renderer): View => $renderer->render($screen),
);
