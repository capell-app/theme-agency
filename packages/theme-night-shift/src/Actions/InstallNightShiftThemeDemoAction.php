<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\NightShift\Actions;

use Capell\FoundationTheme\Contracts\InstallsThemeDemo;
use Capell\FoundationTheme\Data\ThemeDemoInstallData;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageInstaller;
use Capell\ThemeStudio\NightShift\Support\Demo\NightShiftDemoContent;
use Lorisleiva\Actions\Concerns\AsObject;

final class InstallNightShiftThemeDemoAction implements InstallsThemeDemo
{
    use AsObject;

    public function handle(ThemeDemoInstallData $data): int
    {
        return ThemeDemoPageInstaller::run($data, 'night-shift', 'Night Shift', new NightShiftDemoContent);
    }
}
