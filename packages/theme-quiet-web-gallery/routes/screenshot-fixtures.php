<?php

declare(strict_types=1);

use Capell\ThemeStudio\QuietWebGallery\Support\Screenshots\QuietWebGalleryScreenshotRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get(
    '/screenshot-fixtures/theme-quiet-web-gallery/{screen}',
    static fn (string $screen, QuietWebGalleryScreenshotRenderer $renderer): View => $renderer->render($screen),
);
