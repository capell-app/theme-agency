<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\AiLab\Actions;

use Capell\FoundationTheme\Contracts\InstallsThemeDemo;
use Capell\FoundationTheme\Data\ThemeDemoInstallData;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageInstaller;
use Lorisleiva\Actions\Concerns\AsObject;

final class InstallAiLabThemeDemoAction implements InstallsThemeDemo
{
    use AsObject;

    public function handle(ThemeDemoInstallData $data): int
    {
        return ThemeDemoPageInstaller::run($data, 'ai-lab', 'AI Lab');
    }
}
