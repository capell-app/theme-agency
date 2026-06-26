<?php

declare(strict_types=1);

use Capell\ThemeStudio\DeveloperInfrastructure\Support\Screenshots\DeveloperInfrastructureScreenshotRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get(
    '/screenshot-fixtures/theme-developer-infrastructure/{screen}',
    static fn (string $screen, DeveloperInfrastructureScreenshotRenderer $renderer): View => $renderer->render($screen),
);
