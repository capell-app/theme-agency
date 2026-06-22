<?php

declare(strict_types=1);

use Capell\ThemeStudio\PremiumInfrastructure\Support\Screenshots\PremiumInfrastructureScreenshotRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get(
    '/screenshot-fixtures/theme-premium-infrastructure/{screen}',
    static fn (string $screen, PremiumInfrastructureScreenshotRenderer $renderer): View => $renderer->render($screen),
);
