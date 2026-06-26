<?php

declare(strict_types=1);

use Capell\ThemeStudio\CryptoDefi\Support\Screenshots\CryptoDefiScreenshotRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get(
    '/screenshot-fixtures/theme-crypto-defi/{screen}',
    static fn (string $screen, CryptoDefiScreenshotRenderer $renderer): View => $renderer->render($screen),
);
