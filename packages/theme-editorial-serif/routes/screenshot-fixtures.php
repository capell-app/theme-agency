<?php

declare(strict_types=1);

use Capell\ThemeStudio\EditorialSerif\Support\Screenshots\EditorialSerifScreenshotRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get(
    '/screenshot-fixtures/theme-editorial-serif/{screen}',
    static fn (string $screen, EditorialSerifScreenshotRenderer $renderer): View => $renderer->render($screen),
);
