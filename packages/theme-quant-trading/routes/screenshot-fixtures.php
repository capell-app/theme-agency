<?php

declare(strict_types=1);

use Capell\ThemeStudio\QuantTrading\Support\Screenshots\QuantTradingScreenshotRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get(
    '/screenshot-fixtures/theme-quant-trading/{screen}',
    static fn (string $screen, QuantTradingScreenshotRenderer $renderer): View => $renderer->render($screen),
);
