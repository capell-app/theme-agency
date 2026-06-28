<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\CreativeCultureEditorial\Actions;

use Capell\FoundationTheme\Contracts\InstallsThemeDemo;
use Capell\FoundationTheme\Data\ThemeDemoInstallData;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageInstaller;
use Capell\ThemeStudio\CreativeCultureEditorial\Support\Demo\CreativeCultureEditorialDemoContent;
use Lorisleiva\Actions\Concerns\AsObject;

final class InstallCreativeCultureEditorialThemeDemoAction implements InstallsThemeDemo
{
    use AsObject;

    public function handle(ThemeDemoInstallData $data): int
    {
        return ThemeDemoPageInstaller::run($data, 'creative-culture-editorial', 'Creative Culture Editorial', new CreativeCultureEditorialDemoContent);
    }
}
