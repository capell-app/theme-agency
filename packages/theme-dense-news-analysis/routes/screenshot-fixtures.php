<?php

declare(strict_types=1);

use Capell\ThemeStudio\DenseNewsAnalysis\Support\Screenshots\DenseNewsAnalysisScreenshotRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get(
    '/screenshot-fixtures/theme-dense-news-analysis/{screen}',
    static fn (string $screen, DenseNewsAnalysisScreenshotRenderer $renderer): View => $renderer->render($screen),
);
