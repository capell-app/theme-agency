<?php

declare(strict_types=1);

use Capell\ThemeStudio\RawIndex\Support\Screenshots\RawIndexScreenshotRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get(
    '/screenshot-fixtures/theme-raw-index/{screen}',
    static fn (string $screen, RawIndexScreenshotRenderer $renderer): View => $renderer->render($screen),
);
