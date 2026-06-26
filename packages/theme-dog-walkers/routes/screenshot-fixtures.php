<?php

declare(strict_types=1);

use Capell\ThemeStudio\DogWalkers\Support\Screenshots\DogWalkersScreenshotRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get(
    '/screenshot-fixtures/theme-dog-walkers/{screen}',
    static fn (string $screen, DogWalkersScreenshotRenderer $renderer): View => $renderer->render($screen),
);
