<?php

declare(strict_types=1);

use Capell\ThemeStudio\CaseStudyPlatform\Support\Screenshots\CaseStudyPlatformScreenshotRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get(
    '/screenshot-fixtures/theme-case-study-platform/{screen}',
    static fn (string $screen, CaseStudyPlatformScreenshotRenderer $renderer): View => $renderer->render($screen),
);
