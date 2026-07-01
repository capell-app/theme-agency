<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\GlobalCultureMagazine\Actions;

use Capell\FoundationTheme\Contracts\InstallsThemeDemo;
use Capell\FoundationTheme\Data\ThemeDemoInstallData;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageInstaller;
use Capell\ThemeStudio\GlobalCultureMagazine\Support\Demo\GlobalCultureMagazineDemoContent;
use Lorisleiva\Actions\Concerns\AsObject;

final class InstallGlobalCultureMagazineThemeDemoAction implements InstallsThemeDemo
{
    use AsObject;

    public function handle(ThemeDemoInstallData $data): int
    {
        return ThemeDemoPageInstaller::run($data, 'global-culture-magazine', 'Global Culture Magazine', new GlobalCultureMagazineDemoContent);
    }
}
