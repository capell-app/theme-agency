<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\PodcastShow\Actions;

use Capell\FoundationTheme\Contracts\InstallsThemeDemo;
use Capell\FoundationTheme\Data\ThemeDemoInstallData;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageInstaller;
use Capell\ThemeStudio\PodcastShow\Support\Demo\PodcastShowDemoContent;
use Lorisleiva\Actions\Concerns\AsObject;

final class InstallPodcastShowThemeDemoAction implements InstallsThemeDemo
{
    use AsObject;

    public function handle(ThemeDemoInstallData $data): int
    {
        return ThemeDemoPageInstaller::run($data, 'podcast-show', 'Podcast Show', new PodcastShowDemoContent);
    }
}
