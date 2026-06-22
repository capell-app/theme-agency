<?php

declare(strict_types=1);

use Capell\ThemeStudio\PremiumProductStory\Support\Screenshots\PremiumProductStoryScreenshotRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get(
    '/screenshot-fixtures/theme-premium-product-story/{screen}',
    static fn (string $screen, PremiumProductStoryScreenshotRenderer $renderer): View => $renderer->render($screen),
);
