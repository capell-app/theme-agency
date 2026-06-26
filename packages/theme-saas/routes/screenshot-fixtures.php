<?php

declare(strict_types=1);

use Capell\ThemeStudio\Saas\Support\Screenshots\SaasScreenshotRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get(
    '/screenshot-fixtures/theme-saas/{screen}',
    static fn (string $screen, SaasScreenshotRenderer $renderer): View => $renderer->render($screen),
);
