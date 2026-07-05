<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\OneTake\Actions;

use Capell\FoundationTheme\Contracts\InstallsThemeDemo;
use Capell\FoundationTheme\Data\ThemeDemoInstallData;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageInstaller;
use Capell\ThemeStudio\OneTake\Support\Demo\OneTakeDemoContent;
use Lorisleiva\Actions\Concerns\AsObject;

final class InstallOneTakeThemeDemoAction implements InstallsThemeDemo
{
    use AsObject;

    public function handle(ThemeDemoInstallData $data): int
    {
        return ThemeDemoPageInstaller::run($data, 'one-take', 'One Take', new OneTakeDemoContent);
    }
}
