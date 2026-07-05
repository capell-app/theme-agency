<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\QuietType\Actions;

use Capell\FoundationTheme\Contracts\InstallsThemeDemo;
use Capell\FoundationTheme\Data\ThemeDemoInstallData;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageInstaller;
use Capell\ThemeStudio\QuietType\Support\Demo\QuietTypeDemoContent;
use Lorisleiva\Actions\Concerns\AsObject;

final class InstallQuietTypeThemeDemoAction implements InstallsThemeDemo
{
    use AsObject;

    public function handle(ThemeDemoInstallData $data): int
    {
        return ThemeDemoPageInstaller::run($data, 'quiet-type', 'Quiet Type', new QuietTypeDemoContent);
    }
}
