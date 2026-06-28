<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\AiAgent\Actions;

use Capell\FoundationTheme\Contracts\InstallsThemeDemo;
use Capell\FoundationTheme\Data\ThemeDemoInstallData;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageInstaller;
use Capell\ThemeStudio\AiAgent\Support\Demo\AiAgentDemoContent;
use Lorisleiva\Actions\Concerns\AsObject;

final class InstallAiAgentThemeDemoAction implements InstallsThemeDemo
{
    use AsObject;

    public function handle(ThemeDemoInstallData $data): int
    {
        return ThemeDemoPageInstaller::run($data, 'ai-agent', 'AI Agent', new AiAgentDemoContent);
    }
}
