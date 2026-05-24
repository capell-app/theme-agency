<?php

declare(strict_types=1);

use Capell\ThemeStudio\Commerce\Actions\InstallCommerceThemeDemoAction;
use Capell\ThemeStudio\Commerce\CommerceThemeServiceProvider;

require_once __DIR__ . '/../../Support/ThemeDemoLayoutScreenshots.php';

it('captures commerce demo page screenshots for the expected page types and layouts', function (): void {
    $pages = installThemeDemoScreenshotFixture(
        themeKey: CommerceThemeServiceProvider::THEME_KEY,
        packageName: CommerceThemeServiceProvider::$packageName,
        providerClass: CommerceThemeServiceProvider::class,
        installerClass: InstallCommerceThemeDemoAction::class,
    );

    assertThemeDemoLayoutScreenshots(CommerceThemeServiceProvider::THEME_KEY, $pages, [
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
