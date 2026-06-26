<?php

declare(strict_types=1);

use Capell\ThemeStudio\FitnessWellness\Support\Screenshots\FitnessWellnessScreenshotRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get(
    '/screenshot-fixtures/theme-fitness-wellness/{screen}',
    static fn (string $screen, FitnessWellnessScreenshotRenderer $renderer): View => $renderer->render($screen),
);
