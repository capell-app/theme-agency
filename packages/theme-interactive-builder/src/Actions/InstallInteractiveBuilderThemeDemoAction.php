<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\InteractiveBuilder\Actions;

use Capell\FoundationTheme\Contracts\InstallsThemeDemo;
use Capell\FoundationTheme\Data\ThemeDemoInstallData;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageInstaller;
use Capell\ThemeStudio\InteractiveBuilder\Support\Demo\InteractiveBuilderDemoContent;
use Lorisleiva\Actions\Concerns\AsObject;

final class InstallInteractiveBuilderThemeDemoAction implements InstallsThemeDemo
{
    use AsObject;

    public function handle(ThemeDemoInstallData $data): int
    {
        return ThemeDemoPageInstaller::run($data, 'interactive-builder', 'Interactive Builder', new InteractiveBuilderDemoContent);
    }
}
