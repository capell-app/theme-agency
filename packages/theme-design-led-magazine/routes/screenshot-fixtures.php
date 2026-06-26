<?php

declare(strict_types=1);

use Capell\ThemeStudio\DesignLedMagazine\Support\Screenshots\DesignLedMagazineScreenshotRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get(
    '/screenshot-fixtures/theme-design-led-magazine/{screen}',
    static fn (string $screen, DesignLedMagazineScreenshotRenderer $renderer): View => $renderer->render($screen),
);
