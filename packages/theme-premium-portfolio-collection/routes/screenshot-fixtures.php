<?php

declare(strict_types=1);

use Capell\ThemeStudio\PremiumPortfolioCollection\Support\Screenshots\PremiumPortfolioCollectionScreenshotRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get(
    '/screenshot-fixtures/theme-premium-portfolio-collection/{screen}',
    static fn (string $screen, PremiumPortfolioCollectionScreenshotRenderer $renderer): View => $renderer->render($screen),
);
