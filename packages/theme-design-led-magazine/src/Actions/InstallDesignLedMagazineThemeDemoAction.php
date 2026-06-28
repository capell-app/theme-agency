<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\DesignLedMagazine\Actions;

use Capell\FoundationTheme\Contracts\InstallsThemeDemo;
use Capell\FoundationTheme\Data\ThemeDemoInstallData;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageInstaller;
use Capell\ThemeStudio\DesignLedMagazine\Support\Demo\DesignLedMagazineDemoContent;
use Lorisleiva\Actions\Concerns\AsObject;

final class InstallDesignLedMagazineThemeDemoAction implements InstallsThemeDemo
{
    use AsObject;

    public function handle(ThemeDemoInstallData $data): int
    {
        return ThemeDemoPageInstaller::run($data, 'design-led-magazine', 'Design Led Magazine', new DesignLedMagazineDemoContent);
    }
}
