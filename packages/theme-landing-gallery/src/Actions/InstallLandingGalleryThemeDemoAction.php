<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\LandingGallery\Actions;

use Capell\FoundationTheme\Contracts\InstallsThemeDemo;
use Capell\FoundationTheme\Data\ThemeDemoInstallData;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageInstaller;
use Capell\ThemeStudio\LandingGallery\Support\Demo\LandingGalleryDemoContent;
use Lorisleiva\Actions\Concerns\AsObject;

final class InstallLandingGalleryThemeDemoAction implements InstallsThemeDemo
{
    use AsObject;

    public function handle(ThemeDemoInstallData $data): int
    {
        return ThemeDemoPageInstaller::run($data, 'landing-gallery', 'Landing Gallery', new LandingGalleryDemoContent);
    }
}
