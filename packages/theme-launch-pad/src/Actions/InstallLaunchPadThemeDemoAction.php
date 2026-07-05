<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\LaunchPad\Actions;

use Capell\FoundationTheme\Contracts\InstallsThemeDemo;
use Capell\FoundationTheme\Data\ThemeDemoInstallData;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageInstaller;
use Capell\ThemeStudio\LaunchPad\Support\Demo\LaunchPadDemoContent;
use Lorisleiva\Actions\Concerns\AsObject;

final class InstallLaunchPadThemeDemoAction implements InstallsThemeDemo
{
    use AsObject;

    public function handle(ThemeDemoInstallData $data): int
    {
        return ThemeDemoPageInstaller::run($data, 'launch-pad', 'Launch Pad', new LaunchPadDemoContent);
    }
}
