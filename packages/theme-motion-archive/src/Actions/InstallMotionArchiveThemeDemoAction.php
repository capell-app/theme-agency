<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\MotionArchive\Actions;

use Capell\FoundationTheme\Contracts\InstallsThemeDemo;
use Capell\FoundationTheme\Data\ThemeDemoInstallData;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageInstaller;
use Capell\ThemeStudio\MotionArchive\Support\Demo\MotionArchiveDemoContent;
use Lorisleiva\Actions\Concerns\AsObject;

final class InstallMotionArchiveThemeDemoAction implements InstallsThemeDemo
{
    use AsObject;

    public function handle(ThemeDemoInstallData $data): int
    {
        return ThemeDemoPageInstaller::run(
            $data,
            'motion-archive',
            'Motion Archive',
            new MotionArchiveDemoContent,
        );
    }
}
