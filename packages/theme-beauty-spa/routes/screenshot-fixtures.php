<?php

declare(strict_types=1);

use Capell\ThemeStudio\BeautySpa\Support\Screenshots\BeautySpaScreenshotRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get(
    '/screenshot-fixtures/theme-beauty-spa/{screen}',
    static fn (string $screen, BeautySpaScreenshotRenderer $renderer): View => $renderer->render($screen),
);
