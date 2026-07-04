<?php

declare(strict_types=1);

use Capell\FoundationTheme\Data\ThemeDemoInstallData;
use Capell\Tests\Packages\Fixtures\ThemeDemoCommandRecorder;
use Capell\ThemeStudio\CaseStudyPlatform\Actions\InstallCaseStudyPlatformThemeDemoAction;
use Capell\ThemeStudio\CaseStudyPlatform\Console\Commands\DemoCommand;
use Capell\ThemeStudio\DenseNewsAnalysis\Actions\InstallDenseNewsAnalysisThemeDemoAction;
use Capell\ThemeStudio\ExperimentalDirectory\Actions\InstallExperimentalDirectoryThemeDemoAction;
use Capell\ThemeStudio\PremiumPortfolioCollection\Actions\InstallPremiumPortfolioCollectionThemeDemoAction;
use Illuminate\Console\Command;
use Symfony\Component\Console\Tester\CommandTester;

it('passes theme demo command options into the package demo install action', function (string $commandClass, string $actionClass): void {
    $recorder = new ThemeDemoCommandRecorder;

    app()->instance($actionClass, new class($recorder)
    {
        public function __construct(private ThemeDemoCommandRecorder $recorder) {}

        public function handle(ThemeDemoInstallData $data): int
        {
            $this->recorder->records[] = $data;

            return 0;
        }
    });

    /** @var Command $command */
    $command = resolve($commandClass);
    $command->setLaravel(app());

    $tester = new CommandTester($command);

    $exitCode = $tester->execute([
        '--url' => 'https://demo.example.test',
        '--languages' => 'en, fr, ,cy',
        '--sites' => 'Main site, Knowledge base',
        '--force' => true,
    ]);

    expect($exitCode)->toBe(0)
        ->and($recorder->records)->toHaveCount(1)
        ->and($recorder->records[0]->baseUrl)->toBe('https://demo.example.test')
        ->and($recorder->records[0]->languageCodes)->toBe(['en', 'fr', 'cy'])
        ->and($recorder->records[0]->siteNames)->toBe(['Main site', 'Knowledge base'])
        ->and($recorder->records[0]->force)->toBeTrue();
})->with([
    'case study platform' => [DemoCommand::class, InstallCaseStudyPlatformThemeDemoAction::class],
    'dense news analysis' => [Capell\ThemeStudio\DenseNewsAnalysis\Console\Commands\DemoCommand::class, InstallDenseNewsAnalysisThemeDemoAction::class],
    'experimental directory' => [Capell\ThemeStudio\ExperimentalDirectory\Console\Commands\DemoCommand::class, InstallExperimentalDirectoryThemeDemoAction::class],
    'premium portfolio collection' => [Capell\ThemeStudio\PremiumPortfolioCollection\Console\Commands\DemoCommand::class, InstallPremiumPortfolioCollectionThemeDemoAction::class],
]);
