<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\FarField\Actions;

use Capell\FoundationTheme\Contracts\InstallsThemeDemo;
use Capell\FoundationTheme\Data\ThemeDemoInstallData;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageInstaller;
use Capell\ThemeStudio\FarField\Support\Demo\FarFieldDemoContent;
use Lorisleiva\Actions\Concerns\AsObject;

final class InstallFarFieldThemeDemoAction implements InstallsThemeDemo
{
    use AsObject;

    public function handle(ThemeDemoInstallData $data): int
    {
        return ThemeDemoPageInstaller::run($data, 'far-field', 'Far Field', new FarFieldDemoContent);
    }
}
