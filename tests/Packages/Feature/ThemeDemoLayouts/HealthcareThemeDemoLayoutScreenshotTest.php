<?php

declare(strict_types=1);

use Capell\Tests\Support\Concerns\TestingFrontend;
use Capell\ThemeStudio\Healthcare\Actions\InstallHealthcareThemeDemoAction;
use Capell\ThemeStudio\Healthcare\HealthcareThemeServiceProvider;

require_once __DIR__ . '/../../Support/ThemeDemoLayoutScreenshots.php';

uses(TestingFrontend::class);

it('captures healthcare demo page screenshots for the expected page types and layouts', function (): void {
    $pages = installThemeDemoScreenshotFixture(
        themeKey: HealthcareThemeServiceProvider::THEME_KEY,
        packageName: HealthcareThemeServiceProvider::$packageName,
        providerClass: HealthcareThemeServiceProvider::class,
        installerClass: InstallHealthcareThemeDemoAction::class,
    );

    assertThemeDemoLayoutScreenshots(HealthcareThemeServiceProvider::THEME_KEY, $pages, [
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
