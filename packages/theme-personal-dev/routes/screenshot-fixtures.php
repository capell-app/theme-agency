<?php

declare(strict_types=1);

use Capell\ThemeStudio\PersonalDev\Support\Screenshots\PersonalDevScreenshotRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get(
    '/screenshot-fixtures/theme-personal-dev/{screen}',
    static fn (string $screen, PersonalDevScreenshotRenderer $renderer): View => $renderer->render($screen),
);
