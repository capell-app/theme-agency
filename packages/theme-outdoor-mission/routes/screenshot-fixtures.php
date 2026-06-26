<?php

declare(strict_types=1);

use Capell\ThemeStudio\OutdoorMission\Support\Screenshots\OutdoorMissionScreenshotRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get(
    '/screenshot-fixtures/theme-outdoor-mission/{screen}',
    static fn (string $screen, OutdoorMissionScreenshotRenderer $renderer): View => $renderer->render($screen),
);
