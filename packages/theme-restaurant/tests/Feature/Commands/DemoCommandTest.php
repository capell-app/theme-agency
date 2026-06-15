<?php

declare(strict_types=1);

use Capell\FoundationTheme\Contracts\InstallsThemeDemo;
use Capell\FoundationTheme\Data\ThemeDemoInstallData;
use Capell\ThemeStudio\Restaurant\Actions\InstallRestaurantThemeDemoAction;
use Capell\ThemeStudio\Restaurant\Console\Commands\DemoCommand;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

it('delegates restaurant theme demo installation to the action', function (): void {
    $action = Mockery::mock(InstallsThemeDemo::class);
    $action
        ->shouldReceive('handle')
        ->once()
        ->with(Mockery::on(function (ThemeDemoInstallData $data): bool {
            expect($data->siteNames)->toBe(['Main Site', 'Sub Site'])
                ->and($data->languageCodes)->toBe(['en', 'cy'])
                ->and($data->baseUrl)->toBe('https://restaurant.test')
                ->and($data->force)->toBeTrue();

            return true;
        }))
        ->andReturn(Command::FAILURE);

    app()->instance(InstallRestaurantThemeDemoAction::class, $action);
    Artisan::registerCommand(new DemoCommand);

    $exitCode = Artisan::call('capell:theme-restaurant-demo', [
        '--url' => 'https://restaurant.test/',
        '--languages' => 'en, CY',
        '--sites' => 'Main Site, Sub Site',
        '--force' => true,
    ]);

    expect($exitCode)->toBe(Command::FAILURE);
});

it('uses the configured app url when restaurant theme demo url is omitted', function (): void {
    config()->set('app.url', 'https://fallback-restaurant.test/');

    $action = Mockery::mock(InstallsThemeDemo::class);
    $action
        ->shouldReceive('handle')
        ->once()
        ->with(Mockery::on(function (ThemeDemoInstallData $data): bool {
            expect($data->siteNames)->toBe([])
                ->and($data->languageCodes)->toBe([])
                ->and($data->baseUrl)->toBe('https://fallback-restaurant.test')
                ->and($data->force)->toBeFalse();

            return true;
        }))
        ->andReturn(Command::SUCCESS);

    app()->instance(InstallRestaurantThemeDemoAction::class, $action);
    Artisan::registerCommand(new DemoCommand);

    expect(Artisan::call('capell:theme-restaurant-demo'))->toBe(Command::SUCCESS);
});
