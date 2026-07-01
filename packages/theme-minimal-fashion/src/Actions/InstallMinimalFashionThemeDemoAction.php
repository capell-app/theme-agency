<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\MinimalFashion\Actions;

use Capell\FoundationTheme\Contracts\InstallsThemeDemo;
use Capell\FoundationTheme\Data\ThemeDemoInstallData;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageInstaller;
use Capell\ThemeStudio\MinimalFashion\Support\Demo\MinimalFashionDemoContent;
use Lorisleiva\Actions\Concerns\AsObject;

final class InstallMinimalFashionThemeDemoAction implements InstallsThemeDemo
{
    use AsObject;

    public function handle(ThemeDemoInstallData $data): int
    {
        return ThemeDemoPageInstaller::run($data, 'minimal-fashion', 'Minimal Fashion', new MinimalFashionDemoContent);
    }
}
