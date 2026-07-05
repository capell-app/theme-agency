<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\GoldRush\Actions;

use Capell\FoundationTheme\Contracts\InstallsThemeDemo;
use Capell\FoundationTheme\Data\ThemeDemoInstallData;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageInstaller;
use Capell\ThemeStudio\GoldRush\Support\Demo\GoldRushDemoContent;
use Lorisleiva\Actions\Concerns\AsObject;

final class InstallGoldRushThemeDemoAction implements InstallsThemeDemo
{
    use AsObject;

    public function handle(ThemeDemoInstallData $data): int
    {
        return ThemeDemoPageInstaller::run($data, 'gold-rush', 'Gold Rush', new GoldRushDemoContent);
    }
}
