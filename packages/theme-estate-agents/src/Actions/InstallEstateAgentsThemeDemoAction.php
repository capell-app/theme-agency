<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\EstateAgents\Actions;

use Capell\FoundationTheme\Contracts\InstallsThemeDemo;
use Capell\FoundationTheme\Data\ThemeDemoInstallData;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageInstaller;
use Capell\ThemeStudio\EstateAgents\Support\Demo\EstateAgentsDemoContent;
use Lorisleiva\Actions\Concerns\AsObject;

final class InstallEstateAgentsThemeDemoAction implements InstallsThemeDemo
{
    use AsObject;

    public function handle(ThemeDemoInstallData $data): int
    {
        return ThemeDemoPageInstaller::run($data, 'estate-agents', 'Estate Agents', new EstateAgentsDemoContent);
    }
}
