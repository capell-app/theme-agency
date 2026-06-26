<?php

declare(strict_types=1);

use Capell\ThemeStudio\GlobalCultureMagazine\Support\Screenshots\GlobalCultureMagazineScreenshotRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get(
    '/screenshot-fixtures/theme-global-culture-magazine/{screen}',
    static fn (string $screen, GlobalCultureMagazineScreenshotRenderer $renderer): View => $renderer->render($screen),
);
