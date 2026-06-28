<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\ConferenceEvent\Actions;

use Capell\FoundationTheme\Contracts\InstallsThemeDemo;
use Capell\FoundationTheme\Data\ThemeDemoInstallData;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageInstaller;
use Capell\ThemeStudio\ConferenceEvent\Support\Demo\ConferenceEventDemoContent;
use Lorisleiva\Actions\Concerns\AsObject;

final class InstallConferenceEventThemeDemoAction implements InstallsThemeDemo
{
    use AsObject;

    public function handle(ThemeDemoInstallData $data): int
    {
        return ThemeDemoPageInstaller::run($data, 'conference-event', 'Conference Event', new ConferenceEventDemoContent);
    }
}
