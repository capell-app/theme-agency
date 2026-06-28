<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\QuietWebGallery\Actions;

use Capell\FoundationTheme\Contracts\InstallsThemeDemo;
use Capell\FoundationTheme\Data\ThemeDemoInstallData;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageInstaller;
use Capell\ThemeStudio\QuietWebGallery\Support\Demo\QuietWebGalleryDemoContent;
use Lorisleiva\Actions\Concerns\AsObject;

final class InstallQuietWebGalleryThemeDemoAction implements InstallsThemeDemo
{
    use AsObject;

    public function handle(ThemeDemoInstallData $data): int
    {
        return ThemeDemoPageInstaller::run($data, 'quiet-web-gallery', 'Quiet Web Gallery', new QuietWebGalleryDemoContent);
    }
}
