<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\FilterGallery\Actions;

use Capell\FoundationTheme\Contracts\InstallsThemeDemo;
use Capell\FoundationTheme\Data\ThemeDemoInstallData;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageInstaller;
use Capell\ThemeStudio\FilterGallery\Support\Demo\FilterGalleryDemoContent;
use Lorisleiva\Actions\Concerns\AsObject;

final class InstallFilterGalleryThemeDemoAction implements InstallsThemeDemo
{
    use AsObject;

    public function handle(ThemeDemoInstallData $data): int
    {
        return ThemeDemoPageInstaller::run($data, 'filter-gallery', 'Filter Gallery', new FilterGalleryDemoContent);
    }
}
