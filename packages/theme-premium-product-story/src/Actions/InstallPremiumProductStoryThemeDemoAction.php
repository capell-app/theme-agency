<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\PremiumProductStory\Actions;

use Capell\FoundationTheme\Contracts\InstallsThemeDemo;
use Capell\FoundationTheme\Data\ThemeDemoInstallData;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageInstaller;
use Capell\ThemeStudio\PremiumProductStory\Support\Demo\PremiumProductStoryDemoContent;
use Lorisleiva\Actions\Concerns\AsObject;

final class InstallPremiumProductStoryThemeDemoAction implements InstallsThemeDemo
{
    use AsObject;

    public function handle(ThemeDemoInstallData $data): int
    {
        return ThemeDemoPageInstaller::run($data, 'premium-product-story', 'Premium Product Story', new PremiumProductStoryDemoContent);
    }
}
