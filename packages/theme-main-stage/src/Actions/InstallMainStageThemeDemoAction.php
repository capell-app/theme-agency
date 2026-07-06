<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\MainStage\Actions;

use Capell\FoundationTheme\Contracts\InstallsThemeDemo;
use Capell\FoundationTheme\Data\ThemeDemoInstallData;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageInstaller;
use Capell\ThemeStudio\MainStage\Support\Demo\MainStageDemoContent;
use Lorisleiva\Actions\Concerns\AsObject;

final class InstallMainStageThemeDemoAction implements InstallsThemeDemo
{
    use AsObject;

    public function handle(ThemeDemoInstallData $data): int
    {
        return ThemeDemoPageInstaller::run($data, 'main-stage', 'Main Stage', new MainStageDemoContent);
    }
}
