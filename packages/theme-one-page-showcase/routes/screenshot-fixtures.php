<?php

declare(strict_types=1);

use Capell\ThemeStudio\OnePageShowcase\Support\Screenshots\OnePageShowcaseScreenshotRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get(
    '/screenshot-fixtures/theme-one-page-showcase/{screen}',
    static fn (string $screen, OnePageShowcaseScreenshotRenderer $renderer): View => $renderer->render($screen),
);
