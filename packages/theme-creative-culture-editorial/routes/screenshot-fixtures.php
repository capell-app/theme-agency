<?php

declare(strict_types=1);

use Capell\ThemeStudio\CreativeCultureEditorial\Support\Screenshots\CreativeCultureEditorialScreenshotRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get(
    '/screenshot-fixtures/theme-creative-culture-editorial/{screen}',
    static fn (string $screen, CreativeCultureEditorialScreenshotRenderer $renderer): View => $renderer->render($screen),
);
