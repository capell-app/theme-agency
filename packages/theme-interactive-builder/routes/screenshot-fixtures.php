<?php

declare(strict_types=1);

use Capell\ThemeStudio\InteractiveBuilder\Support\Screenshots\InteractiveBuilderScreenshotRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get(
    '/screenshot-fixtures/theme-interactive-builder/{screen}',
    static fn (string $screen, InteractiveBuilderScreenshotRenderer $renderer): View => $renderer->render($screen),
);
