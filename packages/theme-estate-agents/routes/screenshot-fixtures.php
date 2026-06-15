<?php

declare(strict_types=1);

use Capell\ThemeStudio\EstateAgents\Support\Screenshots\EstateAgentsScreenshotRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get(
    '/screenshot-fixtures/theme-estate-agents/{screen}',
    static fn (string $screen, EstateAgentsScreenshotRenderer $renderer): View => $renderer->render($screen),
);
