<?php

declare(strict_types=1);

use Capell\ThemeStudio\Education\Support\Screenshots\EducationScreenshotRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get(
    '/screenshot-fixtures/theme-education/{screen}',
    static fn (string $screen, EducationScreenshotRenderer $renderer): View => $renderer->render($screen),
);
