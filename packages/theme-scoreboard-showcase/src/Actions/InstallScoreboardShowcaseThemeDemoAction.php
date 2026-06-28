<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\ScoreboardShowcase\Actions;

use Capell\FoundationTheme\Contracts\InstallsThemeDemo;
use Capell\FoundationTheme\Data\ThemeDemoInstallData;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageInstaller;
use Capell\ThemeStudio\ScoreboardShowcase\Support\Demo\ScoreboardShowcaseDemoContent;
use Lorisleiva\Actions\Concerns\AsObject;

final class InstallScoreboardShowcaseThemeDemoAction implements InstallsThemeDemo
{
    use AsObject;

    public function handle(ThemeDemoInstallData $data): int
    {
        return ThemeDemoPageInstaller::run($data, 'scoreboard-showcase', 'Scoreboard Showcase', new ScoreboardShowcaseDemoContent);
    }
}
