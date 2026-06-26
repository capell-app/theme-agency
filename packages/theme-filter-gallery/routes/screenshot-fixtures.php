<?php

declare(strict_types=1);

use Capell\ThemeStudio\FilterGallery\Support\Screenshots\FilterGalleryScreenshotRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get(
    '/screenshot-fixtures/theme-filter-gallery/{screen}',
    static fn (string $screen, FilterGalleryScreenshotRenderer $renderer): View => $renderer->render($screen),
);
