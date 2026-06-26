<?php

declare(strict_types=1);

use Capell\ThemeStudio\CreativeMarketplace\Support\Screenshots\CreativeMarketplaceScreenshotRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get(
    '/screenshot-fixtures/theme-creative-marketplace/{screen}',
    static fn (string $screen, CreativeMarketplaceScreenshotRenderer $renderer): View => $renderer->render($screen),
);
