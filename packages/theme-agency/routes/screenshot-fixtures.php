<?php

declare(strict_types=1);

use Capell\ThemeStudio\Agency\Support\Screenshots\AgencyScreenshotRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get(
    '/screenshot-fixtures/theme-agency/{screen}',
    static fn (string $screen, AgencyScreenshotRenderer $renderer): View => $renderer->render($screen),
);
