<?php

declare(strict_types=1);

use Capell\ThemeStudio\PropertyDeveloper\Support\Screenshots\PropertyDeveloperScreenshotRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get(
    '/screenshot-fixtures/theme-property-developer/{screen}',
    static fn (string $screen, PropertyDeveloperScreenshotRenderer $renderer): View => $renderer->render($screen),
);
