<?php

declare(strict_types=1);

use Capell\FoundationTheme\Data\ThemeDemoInstallData;
use Capell\Tests\Packages\Fixtures\ThemeDemoCommandRecorder;
use Capell\ThemeStudio\FrontRow\Actions\InstallFrontRowThemeDemoAction;
use Capell\ThemeStudio\InkPress\Actions\InstallInkPressThemeDemoAction;
use Capell\ThemeStudio\OpenStudio\Actions\InstallOpenStudioThemeDemoAction;
use Capell\ThemeStudio\WildCard\Actions\InstallWildCardThemeDemoAction;
use Capell\ThemeStudio\WildCard\Console\Commands\DemoCommand;
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
    'front row' => [Capell\ThemeStudio\FrontRow\Console\Commands\DemoCommand::class, InstallFrontRowThemeDemoAction::class],
    'ink press' => [Capell\ThemeStudio\InkPress\Console\Commands\DemoCommand::class, InstallInkPressThemeDemoAction::class],
    'open studio' => [Capell\ThemeStudio\OpenStudio\Console\Commands\DemoCommand::class, InstallOpenStudioThemeDemoAction::class],
    'wild card' => [DemoCommand::class, InstallWildCardThemeDemoAction::class],
]);
