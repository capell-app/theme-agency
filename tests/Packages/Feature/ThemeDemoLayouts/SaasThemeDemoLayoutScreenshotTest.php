<?php

declare(strict_types=1);

use Capell\ThemeStudio\Saas\Actions\InstallSaasThemeDemoAction;
use Capell\ThemeStudio\Saas\SaasThemeServiceProvider;

require_once __DIR__ . '/../../Support/ThemeDemoLayoutScreenshots.php';

it('captures saas demo page screenshots for the expected page types and layouts', function (): void {
    $pages = installThemeDemoScreenshotFixture(
        themeKey: SaasThemeServiceProvider::THEME_KEY,
        packageName: SaasThemeServiceProvider::$packageName,
        providerClass: SaasThemeServiceProvider::class,
        installerClass: InstallSaasThemeDemoAction::class,
    );

    assertThemeDemoLayoutScreenshots(SaasThemeServiceProvider::THEME_KEY, $pages, [
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
