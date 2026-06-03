<?php

declare(strict_types=1);

use Capell\FoundationTheme\Data\ThemeDemoInstallData;
use Capell\Tests\Packages\Fixtures\ThemeDemoCommandRecorder;
use Capell\ThemeStudio\Education\Actions\InstallEducationThemeDemoAction;
use Capell\ThemeStudio\Education\Console\Commands\DemoCommand;
use Capell\ThemeStudio\Knowledge\Actions\InstallKnowledgeThemeDemoAction;
use Capell\ThemeStudio\LocalServices\Actions\InstallLocalServicesThemeDemoAction;
use Capell\ThemeStudio\Nonprofit\Actions\InstallNonprofitThemeDemoAction;
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
    'education' => [DemoCommand::class, InstallEducationThemeDemoAction::class],
    'knowledge' => [Capell\ThemeStudio\Knowledge\Console\Commands\DemoCommand::class, InstallKnowledgeThemeDemoAction::class],
    'local services' => [Capell\ThemeStudio\LocalServices\Console\Commands\DemoCommand::class, InstallLocalServicesThemeDemoAction::class],
    'nonprofit' => [Capell\ThemeStudio\Nonprofit\Console\Commands\DemoCommand::class, InstallNonprofitThemeDemoAction::class],
]);
