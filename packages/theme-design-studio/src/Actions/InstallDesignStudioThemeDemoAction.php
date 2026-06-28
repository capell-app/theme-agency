<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\DesignStudio\Actions;

use Capell\FoundationTheme\Contracts\InstallsThemeDemo;
use Capell\FoundationTheme\Data\ThemeDemoInstallData;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageInstaller;
use Capell\ThemeStudio\DesignStudio\Support\Demo\DesignStudioDemoContent;
use Lorisleiva\Actions\Concerns\AsObject;

final class InstallDesignStudioThemeDemoAction implements InstallsThemeDemo
{
    use AsObject;

    public function handle(ThemeDemoInstallData $data): int
    {
        return ThemeDemoPageInstaller::run($data, 'design-studio', 'Design Studio', new DesignStudioDemoContent);
    }
}
