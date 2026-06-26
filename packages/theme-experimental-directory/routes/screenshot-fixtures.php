<?php

declare(strict_types=1);

use Capell\ThemeStudio\ExperimentalDirectory\Support\Screenshots\ExperimentalDirectoryScreenshotRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get(
    '/screenshot-fixtures/theme-experimental-directory/{screen}',
    static fn (string $screen, ExperimentalDirectoryScreenshotRenderer $renderer): View => $renderer->render($screen),
);
