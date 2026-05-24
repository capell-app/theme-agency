<?php

declare(strict_types=1);

use Capell\Tests\Support\Concerns\TestingFrontend;
use Capell\ThemeStudio\Agency\Actions\InstallAgencyThemeDemoAction;
use Capell\ThemeStudio\Agency\AgencyThemeServiceProvider;

require_once __DIR__ . '/../../Support/ThemeDemoLayoutScreenshots.php';

uses(TestingFrontend::class);

it('captures agency demo page screenshots for the expected page types and layouts', function (): void {
    $pages = installThemeDemoScreenshotFixture(
        themeKey: AgencyThemeServiceProvider::THEME_KEY,
        packageName: AgencyThemeServiceProvider::$packageName,
        providerClass: AgencyThemeServiceProvider::class,
        installerClass: InstallAgencyThemeDemoAction::class,
    );

    assertThemeDemoLayoutScreenshots(AgencyThemeServiceProvider::THEME_KEY, $pages, [
        'homepage' => ['type' => 'home', 'layout' => 'home'],
        'directory' => ['type' => 'default', 'layout' => 'results'],
        'detail' => ['type' => 'default', 'layout' => 'default'],
        'contact' => ['type' => 'default', 'layout' => 'system'],
        'empty' => ['type' => 'default', 'layout' => 'default'],
        'not-found' => ['type' => 'error', 'layout' => 'system'],
        'maintenance' => ['type' => 'maintenance', 'layout' => 'system'],
        'system' => ['type' => 'system', 'layout' => 'system'],
        'cta' => ['type' => 'default', 'layout' => 'default'],
    ]);
});
