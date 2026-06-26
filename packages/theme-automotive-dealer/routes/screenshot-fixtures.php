<?php

declare(strict_types=1);

use Capell\ThemeStudio\AutomotiveDealer\Support\Screenshots\AutomotiveDealerScreenshotRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get(
    '/screenshot-fixtures/theme-automotive-dealer/{screen}',
    static fn (string $screen, AutomotiveDealerScreenshotRenderer $renderer): View => $renderer->render($screen),
);
