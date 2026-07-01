<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\QuietLuxuryRetail\Actions;

use Capell\FoundationTheme\Contracts\InstallsThemeDemo;
use Capell\FoundationTheme\Data\ThemeDemoInstallData;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageInstaller;
use Capell\ThemeStudio\QuietLuxuryRetail\Support\Demo\QuietLuxuryRetailDemoContent;
use Lorisleiva\Actions\Concerns\AsObject;

final class InstallQuietLuxuryRetailThemeDemoAction implements InstallsThemeDemo
{
    use AsObject;

    public function handle(ThemeDemoInstallData $data): int
    {
        return ThemeDemoPageInstaller::run($data, 'quiet-luxury-retail', 'Quiet Luxury Retail', new QuietLuxuryRetailDemoContent);
    }
}
