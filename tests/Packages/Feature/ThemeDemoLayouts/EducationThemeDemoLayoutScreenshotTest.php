<?php

declare(strict_types=1);

use Capell\Tests\Support\Concerns\TestingFrontend;
use Capell\ThemeStudio\Education\Actions\InstallEducationThemeDemoAction;
use Capell\ThemeStudio\Education\EducationThemeServiceProvider;

require_once __DIR__ . '/../../Support/ThemeDemoLayoutScreenshots.php';

uses(TestingFrontend::class);

it('captures education demo page screenshots for the expected page types and layouts', function (): void {
    $pages = installThemeDemoScreenshotFixture(
        themeKey: EducationThemeServiceProvider::THEME_KEY,
        packageName: EducationThemeServiceProvider::$packageName,
        providerClass: EducationThemeServiceProvider::class,
        installerClass: InstallEducationThemeDemoAction::class,
    );

    assertThemeDemoLayoutScreenshots(EducationThemeServiceProvider::THEME_KEY, $pages, [
        'homepage' => ['type' => 'home', 'layout' => 'home'],
        'directory' => ['type' => 'default', 'layout' => 'results'],
        'education-proof-review' => ['type' => 'default', 'layout' => 'default'],
        'education-listing-review' => ['type' => 'default', 'layout' => 'results'],
        'education-feature-review' => ['type' => 'default', 'layout' => 'default'],
        'detail' => ['type' => 'default', 'layout' => 'default'],
        'contact' => ['type' => 'default', 'layout' => 'system'],
        'empty' => ['type' => 'default', 'layout' => 'default'],
        'not-found' => ['type' => 'error', 'layout' => 'system'],
        'maintenance' => ['type' => 'maintenance', 'layout' => 'system'],
        'system' => ['type' => 'system', 'layout' => 'system'],
        'cta' => ['type' => 'default', 'layout' => 'default'],
    ]);
});
