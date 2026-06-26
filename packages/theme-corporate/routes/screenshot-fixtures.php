<?php

declare(strict_types=1);

use Capell\ThemeStudio\Corporate\Support\Screenshots\CorporateScreenshotRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get(
    '/screenshot-fixtures/theme-corporate/{screen}',
    static fn (string $screen, CorporateScreenshotRenderer $renderer): View => $renderer->render($screen),
);
