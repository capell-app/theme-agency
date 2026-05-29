<?php

declare(strict_types=1);

use Capell\Core\Models\Page;
use Capell\FoundationTheme\Contracts\InstallsThemeDemo;
use Capell\FoundationTheme\Data\ThemeDemoInstallData;
use Capell\Tests\Packages\PackagesTestCase;
use Capell\ThemeStudio\Portfolio\Actions\InstallPortfolioThemeDemoAction;
use Capell\ThemeStudio\Portfolio\Console\Commands\DemoCommand;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

uses(PackagesTestCase::class);

it('delegates portfolio theme demo installation to the action', function (): void {
    $action = Mockery::mock(InstallsThemeDemo::class);
    $action
        ->shouldReceive('handle')
        ->once()
        ->with(Mockery::on(function (ThemeDemoInstallData $data): bool {
            expect($data->siteNames)->toBe(['Main Site', 'Sub Site'])
                ->and($data->languageCodes)->toBe(['en', 'cy'])
                ->and($data->baseUrl)->toBe('https://portfolio.test')
                ->and($data->force)->toBeTrue();

            return true;
        }))
        ->andReturn(Command::FAILURE);

    app()->instance(InstallPortfolioThemeDemoAction::class, $action);
    Artisan::registerCommand(new DemoCommand);

    $exitCode = Artisan::call('capell:theme-portfolio-demo', [
        '--url' => 'https://portfolio.test/',
        '--languages' => 'en, CY',
        '--sites' => 'Main Site, Sub Site',
        '--force' => true,
    ]);

    expect($exitCode)->toBe(Command::FAILURE);
});

it('uses the configured app url when portfolio theme demo url is omitted', function (): void {
    config()->set('app.url', 'https://fallback-portfolio.test/');

    $action = Mockery::mock(InstallsThemeDemo::class);
    $action
        ->shouldReceive('handle')
        ->once()
        ->with(Mockery::on(function (ThemeDemoInstallData $data): bool {
            expect($data->siteNames)->toBe([])
                ->and($data->languageCodes)->toBe([])
                ->and($data->baseUrl)->toBe('https://fallback-portfolio.test')
                ->and($data->force)->toBeFalse();

            return true;
        }))
        ->andReturn(Command::SUCCESS);

    app()->instance(InstallPortfolioThemeDemoAction::class, $action);
    Artisan::registerCommand(new DemoCommand);

    expect(Artisan::call('capell:theme-portfolio-demo'))->toBe(Command::SUCCESS);
});

it('installs portfolio theme demo pages idempotently with https media urls', function (): void {
    Artisan::registerCommand(new DemoCommand);

    $parameters = [
        '--url' => 'https://demo.test',
        '--sites' => 'Demo',
        '--languages' => 'en',
        '--force' => true,
    ];

    expect(Artisan::call('capell:theme-portfolio-demo', $parameters))->toBe(Command::SUCCESS);

    $pageCount = Page::query()->count();

    expect($pageCount)->toBeGreaterThanOrEqual(7);

    expect(Artisan::call('capell:theme-portfolio-demo', $parameters))->toBe(Command::SUCCESS)
        ->and(Page::query()->count())->toBe($pageCount);

    $payload = json_encode(Page::query()->with('translations')->get()->toArray(), JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);

    expect($payload)->toContain('https://images.unsplash.com/')
        ->and($payload)->toContain('theme_demo');
});
