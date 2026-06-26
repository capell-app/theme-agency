<?php

declare(strict_types=1);

use Capell\ThemeStudio\LiquidGlass\Support\Screenshots\LiquidGlassScreenshotRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get(
    '/screenshot-fixtures/theme-liquid-glass/{screen}',
    static fn (string $screen, LiquidGlassScreenshotRenderer $renderer): View => $renderer->render($screen),
);
