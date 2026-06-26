<?php

declare(strict_types=1);

use Capell\ThemeStudio\FintechTrust\Support\Screenshots\FintechTrustScreenshotRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get(
    '/screenshot-fixtures/theme-fintech-trust/{screen}',
    static fn (string $screen, FintechTrustScreenshotRenderer $renderer): View => $renderer->render($screen),
);
