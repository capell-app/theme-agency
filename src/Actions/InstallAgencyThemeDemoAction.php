<?php

declare(strict_types=1);

namespace Capell\ThemeAgency\Actions;

use Capell\FoundationTheme\Contracts\InstallsThemeDemo;
use Capell\FoundationTheme\Data\ThemeDemoInstallData;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageInstaller;
use Capell\ThemeAgency\Support\Demo\AgencyDemoContent;
use Lorisleiva\Actions\Concerns\AsObject;

final class InstallAgencyThemeDemoAction implements InstallsThemeDemo
{
    use AsObject;

    public function handle(ThemeDemoInstallData $data): int
    {
        return ThemeDemoPageInstaller::run($data, 'agency', 'Agency', new AgencyDemoContent);
    }
}
