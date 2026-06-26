<?php

declare(strict_types=1);

use Capell\ThemeStudio\MotionArchive\Support\Screenshots\MotionArchiveScreenshotRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get(
    '/screenshot-fixtures/theme-motion-archive/{screen}',
    static fn (string $screen, MotionArchiveScreenshotRenderer $renderer): View => $renderer->render($screen),
);
