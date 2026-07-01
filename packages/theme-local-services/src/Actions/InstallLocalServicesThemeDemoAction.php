<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\LocalServices\Actions;

use Capell\FoundationTheme\Contracts\InstallsThemeDemo;
use Capell\FoundationTheme\Data\ThemeDemoInstallData;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageInstaller;
use Capell\ThemeStudio\LocalServices\Support\Demo\LocalServicesDemoContent;
use Lorisleiva\Actions\Concerns\AsObject;

final class InstallLocalServicesThemeDemoAction implements InstallsThemeDemo
{
    use AsObject;

    public function handle(ThemeDemoInstallData $data): int
    {
        return ThemeDemoPageInstaller::run($data, 'local-services', 'Local Services', new LocalServicesDemoContent);
    }
}
