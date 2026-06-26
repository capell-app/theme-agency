<?php

declare(strict_types=1);

use Capell\ThemeStudio\TravelTourism\Support\Screenshots\TravelTourismScreenshotRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get(
    '/screenshot-fixtures/theme-travel-tourism/{screen}',
    static fn (string $screen, TravelTourismScreenshotRenderer $renderer): View => $renderer->render($screen),
);
