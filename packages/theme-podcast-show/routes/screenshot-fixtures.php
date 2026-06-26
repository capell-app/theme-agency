<?php

declare(strict_types=1);

use Capell\ThemeStudio\PodcastShow\Support\Screenshots\PodcastShowScreenshotRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get(
    '/screenshot-fixtures/theme-podcast-show/{screen}',
    static fn (string $screen, PodcastShowScreenshotRenderer $renderer): View => $renderer->render($screen),
);
