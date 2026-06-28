<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\FintechTrust\Actions;

use Capell\FoundationTheme\Contracts\InstallsThemeDemo;
use Capell\FoundationTheme\Data\ThemeDemoInstallData;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageInstaller;
use Capell\ThemeStudio\FintechTrust\Support\Demo\FintechTrustDemoContent;
use Lorisleiva\Actions\Concerns\AsObject;

final class InstallFintechTrustThemeDemoAction implements InstallsThemeDemo
{
    use AsObject;

    public function handle(ThemeDemoInstallData $data): int
    {
        return ThemeDemoPageInstaller::run($data, 'fintech-trust', 'Fintech Trust', new FintechTrustDemoContent);
    }
}
