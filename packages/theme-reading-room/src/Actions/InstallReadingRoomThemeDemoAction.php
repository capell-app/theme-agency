<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\ReadingRoom\Actions;

use Capell\FoundationTheme\Contracts\InstallsThemeDemo;
use Capell\FoundationTheme\Data\ThemeDemoInstallData;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageInstaller;
use Capell\ThemeStudio\ReadingRoom\Support\Demo\ReadingRoomDemoContent;
use Lorisleiva\Actions\Concerns\AsObject;

final class InstallReadingRoomThemeDemoAction implements InstallsThemeDemo
{
    use AsObject;

    public function handle(ThemeDemoInstallData $data): int
    {
        return ThemeDemoPageInstaller::run($data, 'reading-room', 'Reading Room', new ReadingRoomDemoContent);
    }
}
