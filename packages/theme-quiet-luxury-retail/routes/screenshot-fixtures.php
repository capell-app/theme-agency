<?php

declare(strict_types=1);

use Capell\ThemeStudio\QuietLuxuryRetail\Support\Screenshots\QuietLuxuryRetailScreenshotRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get(
    '/screenshot-fixtures/theme-quiet-luxury-retail/{screen}',
    static fn (string $screen, QuietLuxuryRetailScreenshotRenderer $renderer): View => $renderer->render($screen),
);
