<?php

declare(strict_types=1);

use Capell\ThemeStudio\FinancialAdvisory\Support\Screenshots\FinancialAdvisoryScreenshotRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get(
    '/screenshot-fixtures/theme-financial-advisory/{screen}',
    static fn (string $screen, FinancialAdvisoryScreenshotRenderer $renderer): View => $renderer->render($screen),
);
