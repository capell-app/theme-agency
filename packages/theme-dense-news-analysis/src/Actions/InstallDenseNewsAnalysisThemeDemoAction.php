<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\DenseNewsAnalysis\Actions;

use Capell\FoundationTheme\Contracts\InstallsThemeDemo;
use Capell\FoundationTheme\Data\ThemeDemoInstallData;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageInstaller;
use Capell\ThemeStudio\DenseNewsAnalysis\Support\Demo\DenseNewsAnalysisDemoContent;
use Lorisleiva\Actions\Concerns\AsObject;

final class InstallDenseNewsAnalysisThemeDemoAction implements InstallsThemeDemo
{
    use AsObject;

    public function handle(ThemeDemoInstallData $data): int
    {
        return ThemeDemoPageInstaller::run($data, 'dense-news-analysis', 'Dense News Analysis', new DenseNewsAnalysisDemoContent);
    }
}
