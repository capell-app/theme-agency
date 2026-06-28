<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\ResourceHub\Actions;

use Capell\FoundationTheme\Contracts\InstallsThemeDemo;
use Capell\FoundationTheme\Data\ThemeDemoInstallData;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageInstaller;
use Capell\ThemeStudio\ResourceHub\Support\Demo\ResourceHubDemoContent;
use Lorisleiva\Actions\Concerns\AsObject;

final class InstallResourceHubThemeDemoAction implements InstallsThemeDemo
{
    use AsObject;

    public function handle(ThemeDemoInstallData $data): int
    {
        return ThemeDemoPageInstaller::run($data, 'resource-hub', 'Resource Hub', new ResourceHubDemoContent);
    }
}
