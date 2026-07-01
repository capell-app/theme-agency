<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\NewsroomMagazine\Actions;

use Capell\FoundationTheme\Contracts\InstallsThemeDemo;
use Capell\FoundationTheme\Data\ThemeDemoInstallData;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageInstaller;
use Capell\ThemeStudio\NewsroomMagazine\Support\Demo\NewsroomMagazineDemoContent;
use Lorisleiva\Actions\Concerns\AsObject;

final class InstallNewsroomMagazineThemeDemoAction implements InstallsThemeDemo
{
    use AsObject;

    public function handle(ThemeDemoInstallData $data): int
    {
        return ThemeDemoPageInstaller::run($data, 'newsroom-magazine', 'Newsroom Magazine', new NewsroomMagazineDemoContent);
    }
}
