<?php

declare(strict_types=1);

use Capell\ThemeStudio\ConferenceEvent\Support\Screenshots\ConferenceEventScreenshotRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get(
    '/screenshot-fixtures/theme-conference-event/{screen}',
    static fn (string $screen, ConferenceEventScreenshotRenderer $renderer): View => $renderer->render($screen),
);
