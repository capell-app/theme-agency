<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\DarkProductSystem\Actions;

use Capell\FoundationTheme\Contracts\InstallsThemeDemo;
use Capell\FoundationTheme\Data\ThemeDemoInstallData;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageInstaller;
use Capell\ThemeStudio\DarkProductSystem\Support\Demo\DarkProductSystemDemoContent;
use Lorisleiva\Actions\Concerns\AsObject;

final class InstallDarkProductSystemThemeDemoAction implements InstallsThemeDemo
{
    use AsObject;

    public function handle(ThemeDemoInstallData $data): int
    {
        return ThemeDemoPageInstaller::run($data, 'dark-product-system', 'Dark Product System', new DarkProductSystemDemoContent);
    }
}
