<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\Portfolio\Actions;

use Capell\FoundationTheme\Contracts\InstallsThemeDemo;
use Capell\FoundationTheme\Data\ThemeDemoInstallData;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageInstaller;
use Capell\ThemeStudio\Portfolio\Support\Demo\PortfolioDemoContent;
use Lorisleiva\Actions\Concerns\AsObject;

final class InstallPortfolioThemeDemoAction implements InstallsThemeDemo
{
    use AsObject;

    public function handle(ThemeDemoInstallData $data): int
    {
        return ThemeDemoPageInstaller::run($data, 'portfolio', 'Portfolio', new PortfolioDemoContent);
    }
}
