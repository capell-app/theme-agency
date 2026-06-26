<?php

declare(strict_types=1);

use Capell\ThemeStudio\ScoreboardShowcase\Support\Screenshots\ScoreboardShowcaseScreenshotRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get(
    '/screenshot-fixtures/theme-scoreboard-showcase/{screen}',
    static fn (string $screen, ScoreboardShowcaseScreenshotRenderer $renderer): View => $renderer->render($screen),
);
