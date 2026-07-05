<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\DeepBench\Actions;

use Capell\FoundationTheme\Contracts\InstallsThemeDemo;
use Capell\FoundationTheme\Data\ThemeDemoInstallData;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageInstaller;
use Capell\ThemeStudio\DeepBench\Support\Demo\DeepBenchDemoContent;
use Lorisleiva\Actions\Concerns\AsObject;

final class InstallDeepBenchThemeDemoAction implements InstallsThemeDemo
{
    use AsObject;

    public function handle(ThemeDemoInstallData $data): int
    {
        return ThemeDemoPageInstaller::run($data, 'deep-bench', 'Deep Bench', new DeepBenchDemoContent);
    }
}
