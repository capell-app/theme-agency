<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\WildCard\Actions;

use Capell\FoundationTheme\Contracts\InstallsThemeDemo;
use Capell\FoundationTheme\Data\ThemeDemoInstallData;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageInstaller;
use Capell\ThemeStudio\WildCard\Support\Demo\WildCardDemoContent;
use Lorisleiva\Actions\Concerns\AsObject;

final class InstallWildCardThemeDemoAction implements InstallsThemeDemo
{
    use AsObject;

    public function handle(ThemeDemoInstallData $data): int
    {
        return ThemeDemoPageInstaller::run($data, 'wild-card', 'Wild Card', new WildCardDemoContent);
    }
}
