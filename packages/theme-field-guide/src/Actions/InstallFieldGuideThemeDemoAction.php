<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\FieldGuide\Actions;

use Capell\FoundationTheme\Contracts\InstallsThemeDemo;
use Capell\FoundationTheme\Data\ThemeDemoInstallData;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageInstaller;
use Capell\ThemeStudio\FieldGuide\Support\Demo\FieldGuideDemoContent;
use Lorisleiva\Actions\Concerns\AsObject;

final class InstallFieldGuideThemeDemoAction implements InstallsThemeDemo
{
    use AsObject;

    public function handle(ThemeDemoInstallData $data): int
    {
        return ThemeDemoPageInstaller::run($data, 'field-guide', 'Field Guide', new FieldGuideDemoContent);
    }
}
