<?php

declare(strict_types=1);

use Capell\ThemeStudio\Restaurant\Support\Screenshots\RestaurantScreenshotRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get(
    '/screenshot-fixtures/theme-restaurant/{screen}',
    static fn (string $screen, RestaurantScreenshotRenderer $renderer): View => $renderer->render($screen),
);
