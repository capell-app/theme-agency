<?php

declare(strict_types=1);

use Capell\ThemeStudio\AiAgent\Support\Screenshots\AiAgentScreenshotRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get(
    '/screenshot-fixtures/theme-ai-agent/{screen}',
    static fn (string $screen, AiAgentScreenshotRenderer $renderer): View => $renderer->render($screen),
);
