<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\DeveloperInfrastructure\Actions;

use Capell\FoundationTheme\Contracts\InstallsThemeDemo;
use Capell\FoundationTheme\Data\ThemeDemoInstallData;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageInstaller;
use Lorisleiva\Actions\Concerns\AsObject;

final class InstallDeveloperInfrastructureThemeDemoAction implements InstallsThemeDemo
{
    use AsObject;

    public function handle(ThemeDemoInstallData $data): int
    {
        return ThemeDemoPageInstaller::run($data, 'developer-infrastructure', 'Developer Infrastructure');
    }
}
