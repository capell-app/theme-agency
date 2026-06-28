<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\QuantTrading\Actions;

use Capell\FoundationTheme\Contracts\InstallsThemeDemo;
use Capell\FoundationTheme\Data\ThemeDemoInstallData;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageInstaller;
use Capell\ThemeStudio\QuantTrading\Support\Demo\QuantTradingDemoContent;
use Lorisleiva\Actions\Concerns\AsObject;

final class InstallQuantTradingThemeDemoAction implements InstallsThemeDemo
{
    use AsObject;

    public function handle(ThemeDemoInstallData $data): int
    {
        return ThemeDemoPageInstaller::run($data, 'quant-trading', 'Quant Trading', new QuantTradingDemoContent);
    }
}
