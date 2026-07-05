<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\ReelRoom\Actions;

use Capell\FoundationTheme\Contracts\InstallsThemeDemo;
use Capell\FoundationTheme\Data\ThemeDemoInstallData;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageInstaller;
use Capell\ThemeStudio\ReelRoom\Support\Demo\ReelRoomDemoContent;
use Lorisleiva\Actions\Concerns\AsObject;

final class InstallReelRoomThemeDemoAction implements InstallsThemeDemo
{
    use AsObject;

    public function handle(ThemeDemoInstallData $data): int
    {
        return ThemeDemoPageInstaller::run(
            $data,
            'reel-room',
            'Reel Room',
            new ReelRoomDemoContent,
        );
    }
}
