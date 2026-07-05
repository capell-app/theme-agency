<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\OffGrid\Actions;

use Capell\FoundationTheme\Contracts\InstallsThemeDemo;
use Capell\FoundationTheme\Data\ThemeDemoInstallData;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageInstaller;
use Capell\ThemeStudio\OffGrid\Support\Demo\OffGridDemoContent;
use Lorisleiva\Actions\Concerns\AsObject;

final class InstallOffGridThemeDemoAction implements InstallsThemeDemo
{
    use AsObject;

    public function handle(ThemeDemoInstallData $data): int
    {
        return ThemeDemoPageInstaller::run($data, 'off-grid', 'Off Grid', new OffGridDemoContent);
    }
}
