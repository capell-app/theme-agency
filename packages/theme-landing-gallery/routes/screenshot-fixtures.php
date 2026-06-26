<?php

declare(strict_types=1);

use Capell\ThemeStudio\LandingGallery\Support\Screenshots\LandingGalleryScreenshotRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get(
    '/screenshot-fixtures/theme-landing-gallery/{screen}',
    static fn (string $screen, LandingGalleryScreenshotRenderer $renderer): View => $renderer->render($screen),
);
