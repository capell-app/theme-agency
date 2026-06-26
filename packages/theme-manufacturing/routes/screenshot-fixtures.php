<?php

declare(strict_types=1);

use Capell\ThemeStudio\Manufacturing\Support\Screenshots\ManufacturingScreenshotRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get(
    '/screenshot-fixtures/theme-manufacturing/{screen}',
    static fn (string $screen, ManufacturingScreenshotRenderer $renderer): View => $renderer->render($screen),
);
