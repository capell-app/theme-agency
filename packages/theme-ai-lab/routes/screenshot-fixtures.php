<?php

declare(strict_types=1);

use Capell\ThemeStudio\AiLab\Support\Screenshots\AiLabScreenshotRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get(
    '/screenshot-fixtures/theme-ai-lab/{screen}',
    static fn (string $screen, AiLabScreenshotRenderer $renderer): View => $renderer->render($screen),
);
