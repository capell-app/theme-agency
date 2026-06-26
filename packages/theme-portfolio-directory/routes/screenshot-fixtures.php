<?php

declare(strict_types=1);

use Capell\ThemeStudio\PortfolioDirectory\Support\Screenshots\PortfolioDirectoryScreenshotRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get(
    '/screenshot-fixtures/theme-portfolio-directory/{screen}',
    static fn (string $screen, PortfolioDirectoryScreenshotRenderer $renderer): View => $renderer->render($screen),
);
