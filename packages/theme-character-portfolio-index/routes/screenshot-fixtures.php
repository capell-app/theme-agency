<?php

declare(strict_types=1);

use Capell\ThemeStudio\CharacterPortfolioIndex\Support\Screenshots\CharacterPortfolioIndexScreenshotRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get(
    '/screenshot-fixtures/theme-character-portfolio-index/{screen}',
    static fn (string $screen, CharacterPortfolioIndexScreenshotRenderer $renderer): View => $renderer->render($screen),
);
