<?php

declare(strict_types=1);

use Capell\ThemeStudio\LawFirm\Support\Screenshots\LawFirmScreenshotRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get(
    '/screenshot-fixtures/theme-law-firm/{screen}',
    static fn (string $screen, LawFirmScreenshotRenderer $renderer): View => $renderer->render($screen),
);
