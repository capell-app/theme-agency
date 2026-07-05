<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\SoftFocus\Actions;

use Capell\FoundationTheme\Contracts\InstallsThemeDemo;
use Capell\FoundationTheme\Data\ThemeDemoInstallData;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageInstaller;
use Capell\ThemeStudio\SoftFocus\Support\Demo\SoftFocusDemoContent;
use Lorisleiva\Actions\Concerns\AsObject;

final class InstallSoftFocusThemeDemoAction implements InstallsThemeDemo
{
    use AsObject;

    public function handle(ThemeDemoInstallData $data): int
    {
        return ThemeDemoPageInstaller::run($data, 'soft-focus', 'Soft Focus', new SoftFocusDemoContent);
    }
}
