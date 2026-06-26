<?php

declare(strict_types=1);

use Capell\ThemeStudio\DesignStudio\Support\Screenshots\DesignStudioScreenshotRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get(
    '/screenshot-fixtures/theme-design-studio/{screen}',
    static fn (string $screen, DesignStudioScreenshotRenderer $renderer): View => $renderer->render($screen),
);
