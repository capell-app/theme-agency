<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\Restaurant\Actions;

use Capell\FoundationTheme\Contracts\InstallsThemeDemo;
use Capell\FoundationTheme\Data\ThemeDemoInstallData;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageInstaller;
use Capell\ThemeStudio\Restaurant\Support\Demo\RestaurantDemoContent;
use Lorisleiva\Actions\Concerns\AsObject;

final class InstallRestaurantThemeDemoAction implements InstallsThemeDemo
{
    use AsObject;

    public function handle(ThemeDemoInstallData $data): int
    {
        return ThemeDemoPageInstaller::run($data, 'restaurant', 'Restaurant', new RestaurantDemoContent);
    }
}
