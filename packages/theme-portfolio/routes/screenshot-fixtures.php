<?php

declare(strict_types=1);

use Capell\ThemeStudio\Portfolio\Support\Screenshots\PortfolioScreenshotRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get(
    '/screenshot-fixtures/theme-portfolio/{screen}',
    static fn (string $screen, PortfolioScreenshotRenderer $renderer): View => $renderer->render($screen),
);
