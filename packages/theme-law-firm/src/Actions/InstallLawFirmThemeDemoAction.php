<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\LawFirm\Actions;

use Capell\FoundationTheme\Contracts\InstallsThemeDemo;
use Capell\FoundationTheme\Data\ThemeDemoInstallData;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageInstaller;
use Capell\ThemeStudio\LawFirm\Support\Demo\LawFirmDemoContent;
use Lorisleiva\Actions\Concerns\AsObject;

final class InstallLawFirmThemeDemoAction implements InstallsThemeDemo
{
    use AsObject;

    public function handle(ThemeDemoInstallData $data): int
    {
        return ThemeDemoPageInstaller::run($data, 'law-firm', 'Law Firm', new LawFirmDemoContent);
    }
}
