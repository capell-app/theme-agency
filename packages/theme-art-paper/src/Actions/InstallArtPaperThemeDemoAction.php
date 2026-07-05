<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\ArtPaper\Actions;

use Capell\FoundationTheme\Contracts\InstallsThemeDemo;
use Capell\FoundationTheme\Data\ThemeDemoInstallData;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageInstaller;
use Capell\ThemeStudio\ArtPaper\Support\Demo\ArtPaperDemoContent;
use Lorisleiva\Actions\Concerns\AsObject;

final class InstallArtPaperThemeDemoAction implements InstallsThemeDemo
{
    use AsObject;

    public function handle(ThemeDemoInstallData $data): int
    {
        return ThemeDemoPageInstaller::run($data, 'art-paper', 'Art Paper', new ArtPaperDemoContent);
    }
}
