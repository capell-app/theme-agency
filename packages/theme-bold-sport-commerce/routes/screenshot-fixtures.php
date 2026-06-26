<?php

declare(strict_types=1);

use Capell\ThemeStudio\BoldSportCommerce\Support\Screenshots\BoldSportCommerceScreenshotRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get(
    '/screenshot-fixtures/theme-bold-sport-commerce/{screen}',
    static fn (string $screen, BoldSportCommerceScreenshotRenderer $renderer): View => $renderer->render($screen),
);
