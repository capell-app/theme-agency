<?php

declare(strict_types=1);

use Capell\ThemeStudio\EditorialCrm\Support\Screenshots\EditorialCrmScreenshotRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get(
    '/screenshot-fixtures/theme-editorial-crm/{screen}',
    static fn (string $screen, EditorialCrmScreenshotRenderer $renderer): View => $renderer->render($screen),
);
