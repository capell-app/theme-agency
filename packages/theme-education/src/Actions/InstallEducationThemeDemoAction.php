<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\Education\Actions;

use Capell\FoundationTheme\Contracts\InstallsThemeDemo;
use Capell\FoundationTheme\Data\ThemeDemoInstallData;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageInstaller;
use Capell\ThemeStudio\Education\Support\Demo\EducationDemoContent;
use Lorisleiva\Actions\Concerns\AsObject;

final class InstallEducationThemeDemoAction implements InstallsThemeDemo
{
    use AsObject;

    public function handle(ThemeDemoInstallData $data): int
    {
        $exitCode = ThemeDemoPageInstaller::run($data, 'education', 'Education', new EducationDemoContent);

        RebalanceEducationThemeDemoPagesAction::run();

        return $exitCode;
    }
}
