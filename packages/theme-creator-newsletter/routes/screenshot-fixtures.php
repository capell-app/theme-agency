<?php

declare(strict_types=1);

use Capell\ThemeStudio\CreatorNewsletter\Support\Screenshots\CreatorNewsletterScreenshotRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get(
    '/screenshot-fixtures/theme-creator-newsletter/{screen}',
    static fn (string $screen, CreatorNewsletterScreenshotRenderer $renderer): View => $renderer->render($screen),
);
