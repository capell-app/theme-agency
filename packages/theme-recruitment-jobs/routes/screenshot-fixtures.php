<?php

declare(strict_types=1);

use Capell\ThemeStudio\RecruitmentJobs\Support\Screenshots\RecruitmentJobsScreenshotRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get(
    '/screenshot-fixtures/theme-recruitment-jobs/{screen}',
    static fn (string $screen, RecruitmentJobsScreenshotRenderer $renderer): View => $renderer->render($screen),
);
