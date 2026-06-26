<?php

declare(strict_types=1);

use Capell\ThemeStudio\NewsroomMagazine\Support\Screenshots\NewsroomMagazineScreenshotRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get(
    '/screenshot-fixtures/theme-newsroom-magazine/{screen}',
    static fn (string $screen, NewsroomMagazineScreenshotRenderer $renderer): View => $renderer->render($screen),
);
