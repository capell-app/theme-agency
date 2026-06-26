<?php

declare(strict_types=1);

use Capell\ThemeStudio\RoboticsHardware\Support\Screenshots\RoboticsHardwareScreenshotRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get(
    '/screenshot-fixtures/theme-robotics-hardware/{screen}',
    static fn (string $screen, RoboticsHardwareScreenshotRenderer $renderer): View => $renderer->render($screen),
);
