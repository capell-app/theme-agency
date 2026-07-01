<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\Manufacturing\Actions;

use Capell\FoundationTheme\Contracts\InstallsThemeDemo;
use Capell\FoundationTheme\Data\ThemeDemoInstallData;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageInstaller;
use Capell\ThemeStudio\Manufacturing\Support\Demo\ManufacturingDemoContent;
use Lorisleiva\Actions\Concerns\AsObject;

final class InstallManufacturingThemeDemoAction implements InstallsThemeDemo
{
    use AsObject;

    public function handle(ThemeDemoInstallData $data): int
    {
        return ThemeDemoPageInstaller::run($data, 'manufacturing', 'Manufacturing', new ManufacturingDemoContent);
    }
}
