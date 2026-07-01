<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\OutdoorMission\Actions;

use Capell\FoundationTheme\Contracts\InstallsThemeDemo;
use Capell\FoundationTheme\Data\ThemeDemoInstallData;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageInstaller;
use Capell\ThemeStudio\OutdoorMission\Support\Demo\OutdoorMissionDemoContent;
use Lorisleiva\Actions\Concerns\AsObject;

final class InstallOutdoorMissionThemeDemoAction implements InstallsThemeDemo
{
    use AsObject;

    public function handle(ThemeDemoInstallData $data): int
    {
        return ThemeDemoPageInstaller::run($data, 'outdoor-mission', 'Outdoor Mission', new OutdoorMissionDemoContent);
    }
}
