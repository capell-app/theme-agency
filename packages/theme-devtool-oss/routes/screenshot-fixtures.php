<?php

declare(strict_types=1);

use Capell\ThemeStudio\DevtoolOss\Support\Screenshots\DevtoolOssScreenshotRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get(
    '/screenshot-fixtures/theme-devtool-oss/{screen}',
    static fn (string $screen, DevtoolOssScreenshotRenderer $renderer): View => $renderer->render($screen),
);
