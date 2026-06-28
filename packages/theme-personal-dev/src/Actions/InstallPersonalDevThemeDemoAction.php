<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\PersonalDev\Actions;

use Capell\FoundationTheme\Contracts\InstallsThemeDemo;
use Capell\FoundationTheme\Data\ThemeDemoInstallData;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageInstaller;
use Capell\ThemeStudio\PersonalDev\Support\Demo\PersonalDevDemoContent;
use Lorisleiva\Actions\Concerns\AsObject;

final class InstallPersonalDevThemeDemoAction implements InstallsThemeDemo
{
    use AsObject;

    public function handle(ThemeDemoInstallData $data): int
    {
        return ThemeDemoPageInstaller::run($data, 'personal-dev', 'Personal Dev', new PersonalDevDemoContent);
    }
}
